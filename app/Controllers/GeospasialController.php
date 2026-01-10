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
        // 1. Ambil Grup Poligon
        $grupPolygon = $this->grupModel->where('jenis_peta', 'Polygon')->findAll();
        
        foreach ($grupPolygon as &$grup) {
            // 2. Ambil Item Poligon berdasarkan Grup
            $items = $this->poligonModel->where('id_dg', $grup['id_dg'])->findAll();
            
            foreach ($items as &$item) {
                // [PERUBAHAN 1] Mengambil SEMUA PDF (findAll), bukan cuma satu (first)
                // Kita simpan ke key 'daftar_pdf' agar sesuai dengan JS di View
                $pdfs = $this->pdfModel->where('poligon_id', $item['id'])->findAll();
                
                // Jika ingin menampilkan satu PDF utama di tabel (icon kecil), ambil yang pertama
                $item['pdf'] = !empty($pdfs) ? $pdfs[0]['file_path'] : null;
                
                // List lengkap untuk Modal Edit & Popup
                $item['daftar_pdf'] = $pdfs; 
            }
            
            $grup['items'] = $items;
        }

        $data = [
            'title'       => 'Manajemen Poligon',
            'isi'         => 'v_data', // Sesuaikan dengan nama view Anda
            'grupPolygon' => $grupPolygon,
        ];

        return view('template/v_wrapper', $data);
    }

    public function saveGrup()
    {
        $id = $this->request->getPost('id_dg');
        
        $attrLabels = $this->request->getPost('template_attr');
        $templateJson = [];
        if (!empty($attrLabels)) {
            foreach ($attrLabels as $label) {
                if (!empty($label)) $templateJson[] = ['label' => $label];
            }
        }

        $data = [
            'nama_grup'       => $this->request->getPost('nama_grup'),
            'jenis_peta'      => 'Polygon',
            'color'           => $this->request->getPost('color'),
            'weight'          => $this->request->getPost('weight'),
            'opacity'         => $this->request->getPost('opacity'),
            'dashArray'       => $this->request->getPost('dashArray') ?: null,
            'fillColor'       => $this->request->getPost('fillColor'),
            'fillOpacity'     => $this->request->getPost('fillOpacity'),
            'atribut_default' => json_encode($templateJson)
        ];

        if ($id) $this->grupModel->update($id, $data);
        else $this->grupModel->save($data);

        return redirect()->to('geospasial')->with('success', 'Grup berhasil disimpan');
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
}