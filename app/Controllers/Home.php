<?php

namespace App\Controllers;

use App\Models\GeospasialGrupModel;
use App\Models\PoligonModel;

class Home extends BaseController
{
    protected $grupModel;
    protected $poligonModel;

    public function __construct()
    {
        $this->grupModel    = new GeospasialGrupModel();
        $this->poligonModel = new PoligonModel();
    }

    public function index()
    {
        // 1. Ambil Semua Grup yang jenisnya Polygon
        $grupPolygon = $this->grupModel->where('jenis_peta', 'Polygon')->findAll();

        // 2. Ambil Data Poligon untuk setiap Grup
        foreach ($grupPolygon as &$grup) {
            $polygons = $this->poligonModel->where('id_dg', $grup['id_dg'])->findAll();
            
            // Kita perlu menyusun FeatureCollection GeoJSON agar mudah dibaca Leaflet
            $features = [];
            foreach ($polygons as $p) {
                // Decode string GeoJSON dari database
                $geometry = json_decode($p['data_geospasial']);
                $properties = [
                    'id' => $p['id'],
                    'nama' => $p['nama_dg'],
                    'atribut' => json_decode($p['atribut_tambahan'], true) // Decode atribut JSON
                ];

                $features[] = [
                    'type' => 'Feature',
                    'properties' => $properties,
                    'geometry' => $geometry
                ];
            }

            $grup['geojson'] = [
                'type' => 'FeatureCollection',
                'features' => $features
            ];
        }

        $data = [
            'title' => 'Peta Persebaran',
            'layers' => $grupPolygon // Kirim data yang sudah distrukturkan ke View
        ];

        return view('v_home', $data); // Sesuaikan nama file view kamu
    }
}