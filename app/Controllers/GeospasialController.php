<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\GeospasialGrupModel;
use App\Models\PoligonModel;
use App\Models\GeospasialPdfModel;

class GeospasialController extends BaseController
{
    protected $grupModel;
    protected $poligonModel;
    protected $pdfModel;

    public function __construct()
    {
        $this->grupModel    = new GeospasialGrupModel();
        $this->poligonModel = new PoligonModel();
        $this->pdfModel     = new GeospasialPdfModel();
    }

public function geospasial()
{
    $grupPolygon = $this->grupModel->where('jenis_peta', 'Polygon')->findAll();
    
    foreach ($grupPolygon as &$grup) {
        $items = $this->poligonModel
                      ->select('id, id_dg, nama_dg, atribut_tambahan')
                      ->where('id_dg', $grup['id_dg'])
                      ->findAll();
        
        foreach ($items as &$item) {
            // Default gunakan nama asli yang diinput/diimport awal
            $item['nama_display'] = $item['nama_dg']; 

            // Jika User memilih kolom label tertentu di setting Grup
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

            // Ambil PDF (tetap seperti kode lamamu)
            $pdfs = $this->pdfModel->where('poligon_id', $item['id'])->findAll();
            $item['daftar_pdf'] = $pdfs; 
        }
        $grup['items'] = $items;
    }

    return view('template/v_wrapper', [
        'title'       => 'Manajemen Poligon',
        'isi'         => 'v_data',
        'grupPolygon' => $grupPolygon,
    ]);
}

/**
 * Mengambil detail data poligon secara satuan (AJAX)
 * Digunakan untuk modal edit agar beban loading awal (list tabel) tetap ringan.
 */
public function getPolygonDetail($id)
{
    // 1. Cek apakah ini permintaan AJAX (Opsional tapi disarankan untuk keamanan)
    if (!$this->request->isAJAX()) {
        // Jika diakses langsung via browser, kita kasih error atau redirect
        // return redirect()->to('geospasial'); 
    }

    // 2. Ambil data poligon berdasarkan ID
    // Di sini kita ambil SEMUA kolom (termasuk data_geospasial)
    $poligon = $this->poligonModel->find($id);

    // 3. Validasi jika data tidak ditemukan
    if (!$poligon) {
        return $this->response->setJSON([
            'status'  => 'error',
            'message' => 'Data poligon dengan ID ' . $id . ' tidak ditemukan.'
        ])->setStatusCode(404);
    }

    // 4. Ambil relasi file PDF yang terkait dengan poligon ini
    // Diasumsikan nama kolom relasi di tabel PDF adalah 'poligon_id'
    $pdfs = $this->pdfModel->where('poligon_id', $id)->findAll();

    // 5. Gabungkan data PDF ke dalam objek poligon
    $poligon['daftar_pdf'] = $pdfs;

    // 6. Pastikan atribut_tambahan dipastikan berbentuk string JSON yang valid 
    // (untuk mencegah error parse di sisi JavaScript)
    if (empty($poligon['atribut_tambahan'])) {
        $poligon['atribut_tambahan'] = json_encode([]);
    }

    // 7. Kirim response JSON ke Client
    return $this->response->setJSON($poligon);
}

public function saveGrup()
{
    $id = $this->request->getPost('id_dg');
    
    // Tangkap daftar template atribut
    $template = [];
    $attrs = $this->request->getPost('template_attr');
    if ($attrs) {
        foreach ($attrs as $a) {
            if ($a) $template[] = ['label' => $a];
        }
    }

    $data = [
        'nama_grup'       => $this->request->getPost('nama_grup'),
        'label_column'    => $this->request->getPost('label_column'), // TAMBAHKAN INI
        'atribut_default' => json_encode($template),
        'color'           => $this->request->getPost('color'),
        'weight'          => $this->request->getPost('weight'),
        'opacity'         => $this->request->getPost('opacity'),
        'fillColor'       => $this->request->getPost('fillColor'),
        'fillOpacity'     => $this->request->getPost('fillOpacity'),
        'dashArray'       => $this->request->getPost('dashArray'),
    ];

    if ($id) {
        $this->grupModel->update($id, $data);
        $msg = 'Grup berhasil diperbarui';
    } else {
        $data['jenis_peta'] = 'Polygon'; // Default untuk tab poligon
        $this->grupModel->insert($data);
        $msg = 'Grup baru berhasil dibuat';
    }

    return redirect()->to('geospasial')->with('success', $msg);
}

    public function save($tipe)
    {
        if ($tipe !== 'polygon') return redirect()->back();

        $id = $this->request->getPost('id');
        
        // --- PROSES DATA ATRIBUT JSON ---
        $keys = $this->request->getPost('attr_key');
        $vals = $this->request->getPost('attr_val');
        $atributJson = [];
        if (!empty($keys)) {
            foreach ($keys as $k => $label) {
                if (!empty($label)) $atributJson[] = ['label' => $label, 'value' => $vals[$k] ?? ''];
            }
        }

        $dataSimpan = [
            'id_dg'            => $this->request->getPost('id_dg'),
            'nama_dg'          => $this->request->getPost('nama_dg'),
            'data_geospasial'  => $this->request->getPost('data_geospasial'),
            'atribut_tambahan' => json_encode($atributJson),
        ];

        // --- SIMPAN POLIGON ---
        if ($id) {
            $this->poligonModel->update($id, $dataSimpan);
            $polyId = $id;
        } else {
            $this->poligonModel->save($dataSimpan);
            $polyId = $this->poligonModel->getInsertID();
        }

        // --- [PERUBAHAN 2] PROSES MULTIPLE UPLOAD PDF ---
        // Menggunakan getFiles() untuk menangkap array file_pdf[]
        $files = $this->request->getFiles();

        if ($files && isset($files['file_pdf'])) {
            foreach ($files['file_pdf'] as $file) {
                // Validasi setiap file
                if ($file->isValid() && !$file->hasMoved()) {
                    $newName = $file->getRandomName();
                    $file->move('uploads/pdf', $newName);

                    // Insert BARU (bukan update/timpa) karena relasinya One-to-Many
                    $this->pdfModel->insert([
                        'poligon_id' => $polyId,
                        'judul_pdf'  => $file->getClientName(), // Nama asli file sebagai judul default
                        'file_path'  => $newName
                    ]);
                }
            }
        }

        return redirect()->to('geospasial')->with('success', 'Data berhasil disimpan');
    }

    // --- [PERUBAHAN 3] METHOD BARU: HAPUS SATUAN PDF VIA AJAX ---
    // Dipanggil saat tombol silang (X) di modal diklik
    public function deletePdf($idPdf)
    {
        $pdf = $this->pdfModel->find($idPdf);
        
        if ($pdf) {
            // Hapus file fisik
            $path = 'uploads/pdf/' . $pdf['file_path'];
            if (file_exists($path)) {
                unlink($path);
            }
            
            // Hapus record database
            $this->pdfModel->delete($idPdf);
            
            return $this->response->setJSON(['status' => 'success', 'message' => 'File berhasil dihapus']);
        }
        
        return $this->response->setJSON(['status' => 'error', 'message' => 'File tidak ditemukan']);
    }

    public function deleteGrup($id)
    {
        // 1. Cari semua poligon yang termasuk dalam grup ini
        $poligons = $this->poligonModel->where('id_dg', $id)->findAll();

        foreach ($poligons as $poly) {
            // 2. Cari semua PDF milik poligon tersebut
            $pdfs = $this->pdfModel->where('poligon_id', $poly['id'])->findAll();

            foreach ($pdfs as $pdf) {
                $path = 'uploads/pdf/' . $pdf['file_path'];
                
                // 3. Hapus file fisik dari folder public
                if (file_exists($path)) {
                    unlink($path);
                }
                
                // 4. Hapus data di tabel PDF
                $this->pdfModel->delete($pdf['id']);
            }

            // 5. Hapus data poligon (opsional jika sudah ada Cascade Delete di DB)
            $this->poligonModel->delete($poly['id']);
        }

        // 6. Terakhir, hapus grupnya
        $this->grupModel->delete($id);

        return redirect()->to('geospasial')->with('success', 'Grup dan seluruh file terkait berhasil dihapus');
    }

    public function delete($tipe, $id)
    {
        // --- [PERUBAHAN 4] HAPUS SEMUA PDF TERKAIT ---
        if($tipe == 'polygon'){
            // Cari semua file PDF milik poligon ini
            $pdfs = $this->pdfModel->where('poligon_id', $id)->findAll();
            
            if (!empty($pdfs)) {
                foreach ($pdfs as $pdf) {
                    $path = 'uploads/pdf/' . $pdf['file_path'];
                    if (file_exists($path)) {
                        unlink($path); // Hapus fisik file
                    }
                }
                // Hapus data di tabel PDF (Biasanya otomatis jika ada Cascade Delete di DB, tapi aman dihapus manual)
                $this->pdfModel->where('poligon_id', $id)->delete();
            }

            // Hapus data poligon
            $this->poligonModel->delete($id);
        }
        return redirect()->to('geospasial');
    }

    public function importGeoJSONGrup()
    {
        set_time_limit(0);
        ini_set('memory_limit', '1024M');

        $file = $this->request->getFile('file_geojson');
        $namaGrup = $this->request->getPost('nama_grup');
        $selectedColumn = $this->request->getPost('column_name_map');

        if ($file && $file->isValid()) {
            $jsonContent = file_get_contents($file->getTempName());
            $geoArray = json_decode($jsonContent, true);

            // --- DEFINISIKAN VARIABEL YANG HILANG DI SINI ---
            $dataGrup = [
                'nama_grup'   => $namaGrup,
                'jenis_peta'  => 'Polygon',
                'color'       => $this->request->getPost('color'),
                'weight'      => $this->request->getPost('weight'),
                'opacity'     => $this->request->getPost('opacity'),
                'fillColor'   => $this->request->getPost('fillColor'),
                'fillOpacity' => $this->request->getPost('fillOpacity'),
                'dashArray'   => $this->request->getPost('dashArray') ?: null,
            ];

            // Sekarang baris ini tidak akan error lagi
            $this->grupModel->insert($dataGrup);
            $idGrup = $this->grupModel->getInsertID();

            $batchPoligon = [];
            foreach ($geoArray['features'] as $feature) {
                $props = $feature['properties'] ?? [];
                
                // LOGIK: Gunakan kolom pilihan user, jika tidak ada baru gunakan fallback
                $namaDg = $props[$selectedColumn] ?? ($props['nama'] ?? ($props['NAME'] ?? 'Poligon Tanpa Nama'));

                $atributJson = [];
                foreach ($props as $key => $val) {
                    $atributJson[] = ['label' => $key, 'value' => $val];
                }

                $batchPoligon[] = [
                    'id_dg'            => $idGrup,
                    'nama_dg'          => $namaDg,
                    'data_geospasial'  => json_encode($feature),
                    'atribut_tambahan' => json_encode($atributJson),
                ];
            }
            $this->poligonModel->insertBatch($batchPoligon);
            return $this->response->setJSON(['status' => 'success', 'count' => count($batchPoligon)]);
        }
    }
}