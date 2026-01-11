<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\GeospasialGrupModel;
use App\Models\PoligonModel;
use App\Models\LineModel;  // Pastikan Model ini dibuat
use App\Models\PointModel; // Pastikan Model ini dibuat
use App\Models\GeospasialPdfModel;

class Home extends BaseController
{
    public function index()
    {
        $grupModel    = new GeospasialGrupModel();
        $pdfModel     = new GeospasialPdfModel();
        
        // Instansiasi model per tipe
        $models = [
            'Polygon' => new PoligonModel(),
            'Line'    => new LineModel(),
            'Point'   => new PointModel(),
        ];

        // 1. Ambil SEMUA Grup (hapus filter where jenis_peta)
        // Urutkan agar layer titik berada di paling atas (z-index logic), lalu garis, lalu poligon
        $allGroups = $grupModel->orderBy("FIELD(jenis_peta, 'Point', 'Line', 'Polygon')")->findAll();

        foreach ($allGroups as &$grup) {
            $jenis = $grup['jenis_peta'];
            
            // Skip jika jenis peta tidak dikenali di array models
            if (!isset($models[$jenis])) continue;

            $activeModel = $models[$jenis];

            // Ambil data dari model yang sesuai
            $dataItems = $activeModel->where('id_dg', $grup['id_dg'])->findAll();
            $features = [];

            foreach ($dataItems as $item) {
                // --- A. DECODE JSON ---
                $rawGeo = json_decode($item['data_geospasial']);
                if (!$rawGeo) continue;

                // --- B. AMBIL GEOMETRY ---
                $geometry = null;
                if (isset($rawGeo->geometry)) {
                    $geometry = $rawGeo->geometry; 
                } else if (isset($rawGeo->type)) {
                    $geometry = $rawGeo; 
                }

                if (!$geometry) continue; 

                // --- C. AMBIL PDF ---
                // Pastikan kolom relasi di tabel PDF fleksibel (misal: object_id) atau gunakan switch logic
                // Disini saya asumsikan tabel PDF punya kolom 'poligon_id' yang dipakai generik untuk ID objek
                $listPdf = $pdfModel->select('judul_pdf, file_path')
                                    ->where('poligon_id', $item['id']) 
                                    ->findAll();

                // --- D. PROPERTI ---
                $atributDB = json_decode($item['atribut_tambahan'], true) ?? [];
                
                $finalProps = [
                    'id'         => $item['id'],
                    'nama'       => $item['nama_dg'], // Pastikan nama kolom sama di semua tabel (nama_dg)
                    'daftar_pdf' => $listPdf, 
                    'info'       => $atributDB,
                    'jenis_peta' => $jenis // Inject jenis untuk JS
                ];

                // --- E. FEATURE GEOJSON ---
                $features[] = [
                    'type'       => 'Feature',
                    'properties' => $finalProps,
                    'geometry'   => $geometry
                ];
            }

            // --- F. FEATURE COLLECTION ---
            $grup['final_geojson'] = [
                'type'     => 'FeatureCollection',
                'features' => $features
            ];
        }

        return view('v_home', [
            'title'  => 'Peta Persebaran',
            'layers' => $allGroups
        ]);
    }
}