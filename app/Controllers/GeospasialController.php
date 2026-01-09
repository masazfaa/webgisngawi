<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\GeospasialGrupModel;
use App\Models\PoligonModel;
use App\Models\LineModel;
use App\Models\PointModel;

class GeospasialController extends BaseController
{
    protected $grupModel;
    protected $poligonModel;
    protected $lineModel;
    protected $pointModel;

    public function __construct()
    {
        $this->grupModel    = new GeospasialGrupModel();
        $this->poligonModel = new PoligonModel();
        $this->lineModel    = new LineModel();
        $this->pointModel   = new PointModel();
    }

    public function geospasial()
    {
        // Ambil semua grup khusus Polygon
        $grupPolygon = $this->grupModel->where('jenis_peta', 'Polygon')->findAll();
        
        // Ambil data poligon dan pasangkan ke grupnya masing-masing
        foreach ($grupPolygon as &$grup) {
            $grup['items'] = $this->poligonModel->where('id_dg', $grup['id_dg'])->findAll();
        }

        $data = [
            'title'       => 'Manajemen Data Geospasial',
            'isi'         => 'v_data', 
            
            // Data Terstruktur untuk Accordion Poligon
            'grupPolygon' => $grupPolygon,

            // Data Line & Point (Masih Flat/Lama)
            'dataLine'    => $this->lineModel->getWithStyle(),
            'dataPoint'   => $this->pointModel->getWithStyle(),
            'listGrup'    => $this->grupModel->findAll() 
        ];

        return view('template/v_wrapper', $data);
    }

    // --- CRUD GRUP (STYLE) ---
public function saveGrup()
    {
        $id = $this->request->getPost('id_dg');
        
        // Handle dashArray
        $dashArray = $this->request->getPost('dashArray');
        if($dashArray == '') $dashArray = null;

        // --- BARU: Handle Template Atribut ---
        $attrLabels = $this->request->getPost('template_attr'); // Array nama kolom
        $templateJson = [];
        if (!empty($attrLabels)) {
            foreach ($attrLabels as $label) {
                if (!empty($label)) {
                    $templateJson[] = ['label' => $label]; // Simpan label saja
                }
            }
        }
        // -------------------------------------

        $data = [
            'nama_grup'       => $this->request->getPost('nama_grup'),
            'jenis_peta'      => $this->request->getPost('jenis_peta'),
            'color'           => $this->request->getPost('color'),
            'weight'          => $this->request->getPost('weight'),
            'opacity'         => $this->request->getPost('opacity'),
            'dashArray'       => $dashArray,
            'fillColor'       => $this->request->getPost('fillColor'),
            'fillOpacity'     => $this->request->getPost('fillOpacity'),
            'atribut_default' => json_encode($templateJson) // Simpan JSON
        ];

        if ($id) {
            $this->grupModel->update($id, $data);
        } else {
            $this->grupModel->save($data);
        }

        return redirect()->to('geospasial')->with('success', 'Grup & Template Atribut berhasil disimpan');
    }

    public function deleteGrup($id)
    {
        // Hapus grup (Data di dalamnya ikut terhapus karena CASCADE di database)
        $this->grupModel->delete($id);
        return redirect()->to('geospasial')->with('success', 'Grup berhasil dihapus');
    }

    // --- CRUD DATA (POLIGON/LINE/POINT) ---
    public function save($tipe)
    {
        switch ($tipe) {
            case 'polygon': $model = $this->poligonModel; break;
            case 'line':    $model = $this->lineModel; break;
            case 'point':   $model = $this->pointModel; break;
            default: return redirect()->back();
        }

        $id = $this->request->getPost('id');
        
        // Proses Atribut JSON (Dynamic Key-Value)
        $labels = $this->request->getPost('attr_key'); // name="attr_key[]"
        $values = $this->request->getPost('attr_val'); // name="attr_val[]"
        $atributJson = [];

        if (!empty($labels)) {
            foreach ($labels as $k => $l) {
                if (!empty($l)) { // Value boleh kosong, Label tidak boleh
                    $atributJson[] = ['label' => $l, 'value' => $values[$k] ?? ''];
                }
            }
        }

        $dataSimpan = [
            'id_dg'            => $this->request->getPost('id_dg'),
            'nama_dg'          => $this->request->getPost('nama_dg'),
            'data_geospasial'  => $this->request->getPost('data_geospasial'),
            'atribut_tambahan' => json_encode($atributJson),
        ];

        if ($id) {
            $model->update($id, $dataSimpan);
        } else {
            $model->save($dataSimpan);
        }

        return redirect()->to('geospasial')->with('success', 'Data berhasil disimpan');
    }

    public function delete($tipe, $id)
    {
        switch ($tipe) {
            case 'polygon': $model = $this->poligonModel; break;
            case 'line':    $model = $this->lineModel; break;
            case 'point':   $model = $this->pointModel; break;
        }
        $model->delete($id);
        return redirect()->to('geospasial')->with('success', 'Data berhasil dihapus');
    }
}