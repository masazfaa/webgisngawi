<?php

namespace App\Models;

use CodeIgniter\Model;

class GeospasialGrupModel extends Model
{
    protected $table            = 'geospasial_grup';
    protected $primaryKey       = 'id_dg';
    protected $useAutoIncrement = true;
    protected $allowedFields    = [
        'nama_grup', 'jenis_peta', 'color', 'weight', 'opacity', 
        'dashArray', 'fillColor', 'fillOpacity', 'radius', 'atribut_default'
    ];
    protected $useTimestamps    = true;
}