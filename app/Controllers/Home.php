<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\GeospasialGrupModel;
use App\Models\PoligonModel;
use App\Models\GeospasialPdfModel; // Pastikan Model ini sudah dibuat

class Home extends BaseController
{
    public function index()
    {
        $grupModel    = new GeospasialGrupModel();
        $poligonModel = new PoligonModel();
        $pdfModel     = new GeospasialPdfModel(); // 1. Instansiasi Model PDF

        // 1. Ambil Grup yang berjenis Polygon
        $grupPolygon = $grupModel->where('jenis_peta', 'Polygon')->findAll();

        foreach ($grupPolygon as &$grup) {
            // Ambil semua data poligon berdasarkan grup ini
            $polygons = $poligonModel->where('id_dg', $grup['id_dg'])->findAll();
            $features = [];

            foreach ($polygons as $p) {
                // --- A. DECODE JSON DARI DB ---
                $rawGeo = json_decode($p['data_geospasial']);
                
                // Validasi: Pastikan data tidak kosong
                if (!$rawGeo) continue;

                // --- B. AMBIL GEOMETRY SAJA ---
                $geometry = null;
                if (isset($rawGeo->geometry)) {
                    $geometry = $rawGeo->geometry; // Format GeoJSON standar
                } else if (isset($rawGeo->type) && ($rawGeo->type == 'Polygon' || $rawGeo->type == 'MultiPolygon')) {
                    $geometry = $rawGeo; // Format Geometry mentah
                }

                if (!$geometry) continue; 

                // --- C. AMBIL DATA PDF TERKAIT (RELASI) ---
                // Cari di tabel geospasial_pdf yang poligon_id nya sama dengan id poligon ini
                $listPdf = $pdfModel->select('judul_pdf, file_path')
                                    ->where('poligon_id', $p['id'])
                                    ->findAll();

                // --- D. SUSUN ATRIBUT (PROPERTIES) ---
                $atributDB = json_decode($p['atribut_tambahan'], true) ?? [];
                
                $finalProps = [
                    'id'   => $p['id'],
                    'nama' => $p['nama_dg'],
                    
                    // Masukkan array PDF yang ditemukan ke key 'daftar_pdf'
                    // Ini yang akan dibaca oleh JavaScript (props.daftar_pdf)
                    'daftar_pdf' => $listPdf, 
                    
                    'info' => $atributDB 
                ];

                // --- E. SUSUN MENJADI FEATURE GEOJSON ---
                $features[] = [
                    'type'       => 'Feature',
                    'properties' => $finalProps,
                    'geometry'   => $geometry
                ];
            }

            // --- F. BUNGKUS KE FEATURE COLLECTION ---
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