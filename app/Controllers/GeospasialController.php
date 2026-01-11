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
    // 1. SETTING WAJIB: Atasi masalah Memory Limit & Timeout (Untuk Poligon Besar)
    ini_set('memory_limit', '-1'); 
    set_time_limit(0);

    // 2. Cek Grup
    // Berdasarkan migrasi: Tabel 'geospasial_grup', PK 'id_dg'
    $grup = $this->grupModel->find($idGrup);
    
    if (!$grup) {
        return redirect()->back()->with('error', 'Grup tidak ditemukan.');
    }

    // 3. Pilih Model berdasarkan jenis peta
    $jenis = $grup['jenis_peta']; // Enum: 'Point', 'Line', 'Polygon'
    
    if ($jenis == 'Line') {
        $model = $this->lineModel;
    } elseif ($jenis == 'Point') {
        $model = $this->pointModel;
    } else {
        $model = $this->poligonModel;
    }

    // 4. QUERY DATABASE (DISESUAIKAN DENGAN MIGRATION ANDA)
    // Di migration: $this->forge->addForeignKey('id_dg', ...)
    // Jadi kolom penghubung di tabel anak bernama 'id_dg'
    $items = $model->where('id_dg', $idGrup)->findAll(); 

    // Cek jika data kosong
    if (empty($items)) {
        // Return JSON kosong yang valid agar tidak error di JS
        $emptyGeoJSON = json_encode([
            'type' => 'FeatureCollection', 
            'name' => $grup['nama_grup'],
            'features' => []
        ]);
        return $this->response->download(url_title($grup['nama_grup']) . '_kosong.geojson', $emptyGeoJSON);
    }

    $features = [];
    foreach ($items as $item) {
        // 5. DECODE DATA GEOSPASIAL (Robust Parsing)
        // Data di kolom 'data_geospasial' bertipe LONGTEXT
        $rawGeo = json_decode($item['data_geospasial'], true);
        
        // Validasi: Pastikan hasil decode valid
        if (!$rawGeo) continue;

        // Validasi Geometri: 
        // Leaflet.draw kadang menyimpan sebagai "Feature" lengkap, kadang cuma "Geometry"
        $geometry = null;
        if (isset($rawGeo['type'])) {
            if ($rawGeo['type'] === 'Feature' && isset($rawGeo['geometry'])) {
                $geometry = $rawGeo['geometry'];
            } elseif ($rawGeo['type'] === 'Polygon' || $rawGeo['type'] === 'LineString' || $rawGeo['type'] === 'Point') {
                $geometry = $rawGeo;
            }
        }
        
        // Skip jika geometri tidak dikenali
        if (!$geometry) continue;

        // 6. SUSUN PROPERTIES
        // Berdasarkan migration: Tabel anak punya kolom 'id', 'nama_dg', 'atribut_tambahan'
        $props = [
            'id' => $item['id'], // Primary Key tabel anak
            'nama' => $item['nama_dg']
        ];
        
        // Style (Warna/Tebal) dari Grup
        // GeoJSON SimpleStyle Spec agar langsung berwarna di aplikasi lain
        $props['stroke'] = $grup['color'];
        $props['stroke-width'] = (float)$grup['weight'];
        $props['stroke-opacity'] = (float)$grup['opacity'];
        
        if ($jenis == 'Polygon') {
            $props['fill'] = $grup['fillColor'];
            $props['fill-opacity'] = (float)$grup['fillOpacity'];
        }
        
        // Atribut Tambahan (Dinamis dari kolom JSON)
        if (!empty($item['atribut_tambahan'])) {
            $attrs = json_decode($item['atribut_tambahan'], true);
            if (is_array($attrs)) {
                foreach ($attrs as $a) {
                    // Format JSON atribut biasanya: [{"label": "Luas", "value": "100"}]
                    if (isset($a['label'])) {
                        $props[$a['label']] = $a['value'] ?? '';
                    }
                }
            }
        }

        $features[] = [
            'type' => 'Feature', 
            'properties' => $props, 
            'geometry' => $geometry
        ];
    }

    // 7. HASIL AKHIR
    $finalGeoJSON = [
        'type' => 'FeatureCollection',
        'name' => $grup['nama_grup'],
        'features' => $features
    ];

    $filename = url_title($grup['nama_grup'], '_', true) . '.geojson';

    return $this->response->download($filename, json_encode($finalGeoJSON));
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
        // 1. Ambil Info Grup
        $grup = $this->grupModel->find($id);
        if (!$grup) {
            return redirect()->back();
        }

        $jenis = $grup['jenis_peta']; // Line, Point, atau Polygon
        $tab = strtolower($jenis);

        // 2. Tentukan Model & Foreign Key
        if ($jenis == 'Line') {
            $modelItem = $this->lineModel;
            $fkColumn  = 'line_id';
        } elseif ($jenis == 'Point') {
            $modelItem = $this->pointModel;
            $fkColumn  = 'point_id';
        } else {
            $modelItem = $this->poligonModel;
            $fkColumn  = 'poligon_id';
        }

        // 3. Ambil SEMUA item (anak) yang ada di grup ini
        $items = $modelItem->where('id_dg', $id)->findAll();

        // 4. Loop setiap item untuk mencari PDF-nya
        foreach ($items as $item) {
            // Ambil daftar PDF milik item ini
            $pdfs = $this->pdfModel->where($fkColumn, $item['id'])->findAll();

            foreach ($pdfs as $pdf) {
                $filePath = 'uploads/pdf/' . $pdf['file_path'];
                
                // HAPUS FILE FISIK
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
        }

        // 5. Baru hapus Grup dari Database
        // (Database akan otomatis menghapus record di tabel items & pdf via Cascade)
        $this->grupModel->delete($id);

        return redirect()->to('geospasial?tab=' . $tab)->with('success', 'Grup dan seluruh file PDF terkait berhasil dihapus.');
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