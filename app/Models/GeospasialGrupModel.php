<?php

namespace App\Models;

use CodeIgniter\Model;

class GeospasialGrupModel extends Model
{
    protected $table            = 'geospasial_grup';
    protected $primaryKey       = 'id_dg';
    protected $useAutoIncrement = true;
protected $allowedFields    = [
        'nama_grup', 
        'label_column', 
        'jenis_peta', 
        'color', 
        'weight', 
        'opacity', 
        'dashArray', 
        'fillColor', 
        'fillOpacity', 
        'radius', 
        'atribut_default',
        // Tambahan untuk styling Point/Marker:
        'marker_type', // Contoh isi: 'circle', 'pin', 'icon_url', 'icon_file'
        'marker_icon'  // Contoh isi: URL gambar atau nama file hasil upload
    ];

    protected $useTimestamps    = true;
    protected $createdField     = 'created_at'; // Pastikan nama kolom sesuai di database
    protected $updatedField     = 'updated_at';
}