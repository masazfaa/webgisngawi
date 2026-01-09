<?php

namespace App\Controllers;

use App\Models\GeospasialGrupModel;
use App\Models\PoligonModel;

class Home extends BaseController
{
    public function index()
    {
        $grupModel    = new GeospasialGrupModel();
        $poligonModel = new PoligonModel();

        // 1. Ambil Grup
        $grupPolygon = $grupModel->where('jenis_peta', 'Polygon')->findAll();

        foreach ($grupPolygon as &$grup) {
            $polygons = $poligonModel->where('id_dg', $grup['id_dg'])->findAll();
            $features = [];

            foreach ($polygons as $p) {
                // A. DECODE JSON DARI DB
                $rawGeo = json_decode($p['data_geospasial']);
                
                // Validasi: Pastikan data tidak kosong
                if (!$rawGeo) continue;

                // B. AMBIL GEOMETRY NYA SAJA
                // Kasus 1: Jika di DB tersimpan {"type":"Feature", "geometry":{...}} -> Ambil ->geometry
                // Kasus 2: Jika di DB tersimpan {"type":"Polygon", ...} -> Pakai langsung
                $geometry = null;
                if (isset($rawGeo->geometry)) {
                    $geometry = $rawGeo->geometry;
                } else if (isset($rawGeo->type) && ($rawGeo->type == 'Polygon' || $rawGeo->type == 'MultiPolygon')) {
                    $geometry = $rawGeo;
                }

                if (!$geometry) continue; // Skip jika geometri tidak valid

                // C. SUSUN ATRIBUT (PROPERTIES)
                $atributDB = json_decode($p['atribut_tambahan'], true) ?? [];
                
                // Kita rapikan atribut agar enak dibaca di JS
                $finalProps = [
                    'id'   => $p['id'],
                    'nama' => $p['nama_dg'],
                    'pdf'  => $p['file_path'] ?? null,
                    'info' => $atributDB // Array [{label:..., value:...}]
                ];

                // D. SUSUN ULANG MENJADI FEATURE GEOJSON STANDAR
                $features[] = [
                    'type'       => 'Feature',
                    'properties' => $finalProps,
                    'geometry'   => $geometry // Ini isinya {type:Polygon, coordinates:[...]}
                ];
            }

            // E. BUNGKUS SATU GRUP MENJADI FEATURE COLLECTION
            $grup['final_geojson'] = [
                'type'     => 'FeatureCollection',
                'features' => $features
            ];
        }

        return view('v_home', [
            'title'  => 'Peta Persebaran',
            'layers' => $grupPolygon
        ]);
    }
}