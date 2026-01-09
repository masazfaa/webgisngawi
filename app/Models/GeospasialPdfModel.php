<?php

namespace App\Models;

use CodeIgniter\Model;

class GeospasialPdfModel extends Model
{
    protected $table            = 'geospasial_pdf';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $allowedFields    = ['poligon_id', 'line_id', 'point_id', 'judul_pdf', 'file_path'];
    protected $useTimestamps    = true;
}