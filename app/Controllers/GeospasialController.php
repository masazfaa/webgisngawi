<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\GeospasialGrupModel;
use App\Models\GeospasialPdfModel;
use App\Models\PoligonModel;
use App\Models\LineModel;
use App\Models\PointModel;
use JsonMachine\Items; // Pastikan library ini tetap ada untuk import
use JsonMachine\JsonDecoder\ExtJsonDecoder;

class GeospasialController extends BaseController
{
    protected $grupModel;
    protected $poligonModel;
    protected $lineModel;
    protected $pointModel;
    protected $pdfModel;
    protected $db;

    public function __construct()
    {
        $this->grupModel    = new GeospasialGrupModel();
        $this->poligonModel = new PoligonModel();
        $this->lineModel    = new LineModel();
        $this->pointModel   = new PointModel();
        $this->pdfModel     = new GeospasialPdfModel();
        $this->db           = \Config\Database::connect();
    }

    public function geospasial()
    {
        // Default tab polygon
        $activeTab = $this->request->getGet('tab') ?? 'polygon';

        $grupPolygon = [];
        $grupLine    = [];
        $grupPoint   = [];

        // Load hanya data yang dibutuhkan sesuai Tab
        switch ($activeTab) {
            case 'line':
                $grupLine = $this->_getGrupData('Line');
                break;
            case 'point':
                $grupPoint = $this->_getGrupData('Point');
                break;
            default: // polygon
                $grupPolygon = $this->_getGrupData('Polygon');
                break;
        }

        return view('template/v_wrapper', [
            'title'       => 'Manajemen Geospasial',
            'isi'         => 'v_data', // Sesuaikan path view Anda
            'activeTab'   => $activeTab,
            'grupPolygon' => $grupPolygon,
            'grupLine'    => $grupLine,
            'grupPoint'   => $grupPoint,
        ]);
    }

    /**
     * PRIVATE HELPER: Mengambil List Data (VERSI RINGAN/LITE)
     * PERBAIKAN: Kita membuang 'data_geospasial' dari select agar RAM aman.
     */
    private function _getGrupData($jenis)
    {
        if ($jenis == 'Line') {
            $model = $this->lineModel;
            $fk    = 'line_id';
        } elseif ($jenis == 'Point') {
            $model = $this->pointModel;
            $fk    = 'point_id';
        } else {
            $model = $this->poligonModel;
            $fk    = 'poligon_id';
        }

        // Ambil Daftar Grup
        $grups = $this->grupModel->where('jenis_peta', $jenis)->findAll();

        foreach ($grups as &$grup) {
            // --- KUNCI PERBAIKAN MEMORY EXHAUSTED ---
            // Kita HANYA select kolom penting untuk tabel. 
            // Kolom 'data_geospasial' (yang berat) TIDAK DIAMBIL disini.
            $items = $model->select('id, id_dg, nama_dg, atribut_tambahan')
                           ->where('id_dg', $grup['id_dg'])
                           ->findAll();

            foreach ($items as &$item) {
                // A. Logic Label Dinamis
                $item['nama_display'] = $item['nama_dg']; 

                if (!empty($grup['label_column'])) {
                    $attrs = json_decode($item['atribut_tambahan'], true);
                    if ($attrs) {
                        foreach ($attrs as $at) {
                            if ($at['label'] == $grup['label_column'] && !empty($at['value'])) {
                                $item['nama_display'] = $at['value'];
                                break;
                            }
                        }
                    }
                }

                // B. Ambil PDF (Ringan, jadi aman diambil)
                $item['daftar_pdf'] = $this->pdfModel->where($fk, $item['id'])->findAll();
            }
            $grup['items'] = $items;
        }

        return $grups;
    }

    // =========================================================================
    // AJAX GET DETAIL (Ini baru load data_geospasial karena cuma 1 row)
    // =========================================================================
    public function getDetail($type, $id)
    {
        if (!$this->request->isAJAX()) return $this->response->setStatusCode(400);

        switch (strtolower($type)) {
            case 'line': $model = $this->lineModel; $fk = 'line_id'; break;
            case 'point': $model = $this->pointModel; $fk = 'point_id'; break;
            default: $model = $this->poligonModel; $fk = 'poligon_id'; break;
        }

        // Disini kita pakai find() biasa, otomatis SELECT * (termasuk data_geospasial)
        // Karena cuma 1 data, RAM server pasti kuat.
        $data = $model->find($id);

        if (!$data) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data tidak ditemukan'])->setStatusCode(404);
        }

        $data['daftar_pdf'] = $this->pdfModel->where($fk, $id)->findAll();

        if (empty($data['atribut_tambahan'])) {
            $data['atribut_tambahan'] = json_encode([]);
        }

        return $this->response->setJSON($data);
    }

    // =========================================================================
    // SAVE GRUP
    // =========================================================================
    public function saveGrup()
    {
        $idGrup      = $this->request->getPost('id_dg');
        $labelColumn = $this->request->getPost('label_column');
        
        $jenisPeta = $this->request->getPost('jenis_peta');
        if($idGrup && empty($jenisPeta)) {
            $existing = $this->grupModel->find($idGrup);
            $jenisPeta = $existing['jenis_peta'];
        }
        if(empty($jenisPeta)) $jenisPeta = 'Polygon';

        $templates = [];
        $tempInput = $this->request->getPost('template_attr');
        if ($tempInput) {
            foreach ($tempInput as $t) {
                if (!empty($t)) $templates[] = ['label' => $t, 'type' => 'text'];
            }
        }

        $dataGrup = [
            'nama_grup'       => $this->request->getPost('nama_grup'),
            'label_column'    => $labelColumn,
            'jenis_peta'      => $jenisPeta,
            'color'           => $this->request->getPost('color'),
            'weight'          => $this->request->getPost('weight'),
            'opacity'         => $this->request->getPost('opacity'),
            'fillColor'       => $this->request->getPost('fillColor'),
            'fillOpacity'     => $this->request->getPost('fillOpacity'),
            'dashArray'       => $this->request->getPost('dashArray'),
            'atribut_default' => json_encode($templates)
        ];

        if (empty($idGrup)) {
            $this->grupModel->insert($dataGrup);
        } else {
            $this->grupModel->update($idGrup, $dataGrup);
            
            // Sync Label (Optional logic)
            if (!empty($labelColumn)) {
                if ($jenisPeta == 'Line') $modelTarget = $this->lineModel;
                elseif ($jenisPeta == 'Point') $modelTarget = $this->pointModel;
                else $modelTarget = $this->poligonModel;

                // Warning: Ini bisa berat jika datanya ribuan. 
                // Jika server crash disini, nonaktifkan fitur sync ini.
                $items = $modelTarget->select('id, atribut_tambahan')->where('id_dg', $idGrup)->findAll();
                
                foreach ($items as $item) {
                    $attrs = json_decode($item['atribut_tambahan'], true);
                    if ($attrs) {
                        foreach ($attrs as $at) {
                            if ($at['label'] == $labelColumn && !empty($at['value'])) {
                                $modelTarget->update($item['id'], ['nama_dg' => $at['value']]);
                                break;
                            }
                        }
                    }
                }
            }
        }

        return redirect()->to('geospasial?tab=' . strtolower($jenisPeta))->with('success', 'Grup berhasil disimpan.');
    }

    // =========================================================================
    // SAVE DATA
    // =========================================================================
    public function save($tipe)
    {
        switch (strtolower($tipe)) {
            case 'line': $model = $this->lineModel; $pdfFk = 'line_id'; break;
            case 'point': $model = $this->pointModel; $pdfFk = 'point_id'; break;
            default: $model = $this->poligonModel; $pdfFk = 'poligon_id'; break;
        }

        $id    = $this->request->getPost('id');
        $id_dg = $this->request->getPost('id_dg');

        $keys = $this->request->getPost('attr_key');
        $vals = $this->request->getPost('attr_val');
        $atributJson = [];
        if (!empty($keys)) {
            foreach ($keys as $k => $label) {
                if (!empty($label)) $atributJson[] = ['label' => $label, 'value' => $vals[$k] ?? ''];
            }
        }

        $dataSimpan = [
            'id_dg'            => $id_dg,
            'nama_dg'          => $this->request->getPost('nama_dg'),
            'data_geospasial'  => $this->request->getPost('data_geospasial'),
            'atribut_tambahan' => json_encode($atributJson),
        ];

        if ($id) {
            $model->update($id, $dataSimpan);
            $parentId = $id;
        } else {
            $model->insert($dataSimpan);
            $parentId = $model->getInsertID();
        }

        $files = $this->request->getFiles();
        if ($files && isset($files['file_pdf'])) {
            foreach ($files['file_pdf'] as $file) {
                if ($file->isValid() && !$file->hasMoved()) {
                    $newName = $file->getRandomName();
                    $file->move('uploads/pdf', $newName);
                    $this->pdfModel->insert([
                        $pdfFk      => $parentId,
                        'judul_pdf' => $file->getClientName(),
                        'file_path' => $newName
                    ]);
                }
            }
        }

        return redirect()->to('geospasial?tab=' . strtolower($tipe))->with('success', 'Data berhasil disimpan');
    }

    // =========================================================================
    // IMPORT (Menggunakan Stream agar RAM Aman)
    // =========================================================================
    public function importGeoJSONGrup()
    {
        ini_set('memory_limit', '-1'); 
        set_time_limit(0);
        
        $file = $this->request->getFile('file_geojson');
        $namaGrup = $this->request->getPost('nama_grup');
        $colMap   = $this->request->getPost('column_name_map');

        if ($file && $file->isValid()) {
            
            // Cek Header manual untuk deteksi jenis
            $handle = fopen($file->getTempName(), 'r');
            $header = fread($handle, 1000); 
            fclose($handle);

            $jenisPeta = 'Polygon'; 
            if (stripos($header, 'LineString') !== false) $jenisPeta = 'Line';
            if (stripos($header, 'Point') !== false) $jenisPeta = 'Point';

            $this->db->transStart();

            $this->grupModel->insert([
                'nama_grup'   => $namaGrup,
                'jenis_peta'  => $jenisPeta,
                'label_column'=> $colMap,
                'color'       => $this->request->getPost('color'),
                'weight'      => $this->request->getPost('weight'),
                'opacity'     => $this->request->getPost('opacity'),
                'fillColor'   => $this->request->getPost('fillColor'),
                'fillOpacity' => $this->request->getPost('fillOpacity'),
                'dashArray'   => $this->request->getPost('dashArray'),
            ]);
            $idGrup = $this->grupModel->getInsertID();

            if ($jenisPeta == 'Line') $targetModel = $this->lineModel;
            elseif ($jenisPeta == 'Point') $targetModel = $this->pointModel;
            else $targetModel = $this->poligonModel;

            // STREAMING PARSE
            $features = Items::fromFile($file->getTempName(), [
                'pointer' => '/features',
                'decoder' => new ExtJsonDecoder(true)
            ]);

            $batchData = [];
            $chunkSize = 100;
            $totalCount = 0;

            foreach ($features as $feature) {
                if (!is_array($feature)) continue;

                $props = $feature['properties'] ?? [];
                $namaDg = $props[$colMap] ?? ($props['nama'] ?? 'Tanpa Nama');
                
                $attrs = [];
                foreach ($props as $k => $v) {
                    $valStr = (is_array($v) || is_object($v)) ? json_encode($v) : $v;
                    $attrs[] = ['label' => $k, 'value' => $valStr];
                }

                $batchData[] = [
                    'id_dg'            => $idGrup,
                    'nama_dg'          => $namaDg,
                    'data_geospasial'  => json_encode($feature),
                    'atribut_tambahan' => json_encode($attrs)
                ];

                if (count($batchData) >= $chunkSize) {
                    $targetModel->insertBatch($batchData);
                    $totalCount += count($batchData);
                    $batchData = []; 
                }
            }

            if (!empty($batchData)) {
                $targetModel->insertBatch($batchData);
                $totalCount += count($batchData);
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === FALSE) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal insert database']);
            }

            return $this->response->setJSON(['status' => 'success', 'count' => $totalCount]);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Upload Gagal']);
    }

    // =========================================================================
    // EXPORT
    // =========================================================================
    public function exportGeoJSON($idGrup)
    {
        // Tetap set memory limit tinggi untuk export
        ini_set('memory_limit', '-1');
        set_time_limit(0);

        $grup = $this->grupModel->find($idGrup);
        if (!$grup) return redirect()->back();

        if ($grup['jenis_peta'] == 'Line') $model = $this->lineModel;
        elseif ($grup['jenis_peta'] == 'Point') $model = $this->pointModel;
        else $model = $this->poligonModel;

        // Disini kita HARUS mengambil data_geospasial
        // Jika data sangat besar, sebaiknya gunakan cursor/chunk, 
        // tapi untuk download file biasanya findAll masih oke jika di bawah 500MB
        $items = $model->where('id_dg', $idGrup)->findAll();

        $features = [];
        foreach ($items as $item) {
            $rawGeo = json_decode($item['data_geospasial'], true);
            $geometry = $rawGeo['geometry'] ?? ($rawGeo['type'] !== 'Feature' ? $rawGeo : null);
            if (!$geometry) continue;

            $props = ['id' => $item['id'], 'nama' => $item['nama_dg']];
            
            $props['stroke'] = $grup['color'];
            $props['stroke-width'] = $grup['weight'];
            if ($grup['jenis_peta'] == 'Polygon') {
                $props['fill'] = $grup['fillColor'];
                $props['fill-opacity'] = $grup['fillOpacity'];
            }
            
            $attrs = json_decode($item['atribut_tambahan'], true);
            if ($attrs) foreach ($attrs as $a) $props[$a['label']] = $a['value'];

            $features[] = ['type' => 'Feature', 'properties' => $props, 'geometry' => $geometry];
        }

        return $this->response->download(url_title($grup['nama_grup']) . '.geojson', json_encode(['type' => 'FeatureCollection', 'features' => $features]));
    }

    // =========================================================================
    // DELETE
    // =========================================================================
    public function delete($tipe, $id)
    {
        if ($tipe == 'line') { $model = $this->lineModel; $fk = 'line_id'; } 
        elseif ($tipe == 'point') { $model = $this->pointModel; $fk = 'point_id'; } 
        else { $model = $this->poligonModel; $fk = 'poligon_id'; }

        $pdfs = $this->pdfModel->where($fk, $id)->findAll();
        foreach ($pdfs as $pdf) {
            $path = 'uploads/pdf/' . $pdf['file_path'];
            if (file_exists($path)) unlink($path);
        }
        $this->pdfModel->where($fk, $id)->delete();
        $model->delete($id);

        return redirect()->to('geospasial?tab=' . strtolower($tipe));
    }

    public function deleteGrup($id)
    {
        $grup = $this->grupModel->find($id);
        $tab = strtolower($grup['jenis_peta'] ?? 'polygon');
        $this->grupModel->delete($id);
        return redirect()->to('geospasial?tab=' . $tab)->with('success', 'Grup dihapus');
    }

    public function deletePdf($idPdf)
    {
        $pdf = $this->pdfModel->find($idPdf);
        if ($pdf) {
            $path = 'uploads/pdf/' . $pdf['file_path'];
            if (file_exists($path)) unlink($path);
            $this->pdfModel->delete($idPdf);
            return $this->response->setJSON(['status' => 'success']);
        }
        return $this->response->setJSON(['status' => 'error']);
    }
}