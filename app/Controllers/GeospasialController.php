<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\GeospasialGrupModel;
use App\Models\PoligonModel;
use App\Models\GeospasialPdfModel; // 1. Load Model PDF

class GeospasialController extends BaseController
{
    protected $grupModel;
    protected $poligonModel;
    protected $pdfModel; // 2. Property PDF

    public function __construct()
    {
        $this->grupModel    = new GeospasialGrupModel();
        $this->poligonModel = new PoligonModel();
        $this->pdfModel     = new GeospasialPdfModel(); // 3. Init PDF
    }

    public function geospasial()
    {
        $grupPolygon = $this->grupModel->where('jenis_peta', 'Polygon')->findAll();
        
        foreach ($grupPolygon as &$grup) {
            $items = $this->poligonModel->where('id_dg', $grup['id_dg'])->findAll();
            
            // 4. Ambil data PDF untuk setiap poligon
            foreach ($items as &$item) {
                $pdf = $this->pdfModel->where('poligon_id', $item['id'])->first();
                $item['pdf'] = $pdf ? $pdf['file_path'] : null;
            }
            
            $grup['items'] = $items;
        }

        $data = [
            'title'       => 'Manajemen Poligon',
            'isi'         => 'v_data', 
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
        // Hanya support polygon dulu sesuai request
        if ($tipe !== 'polygon') return redirect()->back();

        $id = $this->request->getPost('id');
        
        // Atribut JSON
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

        // 5. Simpan Data Poligon
        if ($id) {
            $this->poligonModel->update($id, $dataSimpan);
            $polyId = $id;
        } else {
            $this->poligonModel->save($dataSimpan);
            $polyId = $this->poligonModel->getInsertID();
        }

        // 6. PROSES UPLOAD PDF (LOADING PROCESS DI VIEW)
        $filePdf = $this->request->getFile('file_pdf');
        if ($filePdf && $filePdf->isValid() && !$filePdf->hasMoved()) {
            $newName = $filePdf->getRandomName();
            // Simpan ke folder public/uploads/pdf
            $filePdf->move('uploads/pdf', $newName); 

            // Cek data lama di DB
            $existingPdf = $this->pdfModel->where('poligon_id', $polyId)->first();
            
            // Hapus file fisik lama jika ada (optional, good practice)
            if ($existingPdf && file_exists('uploads/pdf/' . $existingPdf['file_path'])) {
                unlink('uploads/pdf/' . $existingPdf['file_path']);
            }

            $pdfData = [
                'poligon_id' => $polyId,
                'judul_pdf'  => $this->request->getPost('nama_dg'),
                'file_path'  => $newName
            ];

            if ($existingPdf) $this->pdfModel->update($existingPdf['id'], $pdfData);
            else $this->pdfModel->save($pdfData);
        }

        return redirect()->to('geospasial')->with('success', 'Data berhasil disimpan');
    }

    public function deleteGrup($id)
    {
        $this->grupModel->delete($id);
        return redirect()->to('geospasial');
    }

    public function delete($tipe, $id)
    {
        // Hapus PDF jika ada
        if($tipe == 'polygon'){
            $pdf = $this->pdfModel->where('poligon_id', $id)->first();
            if ($pdf && file_exists('uploads/pdf/' . $pdf['file_path'])) {
                unlink('uploads/pdf/' . $pdf['file_path']);
            }
            $this->poligonModel->delete($id);
        }
        return redirect()->to('geospasial');
    }
}