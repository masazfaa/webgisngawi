<?php

namespace App\Models;

use CodeIgniter\Model;

class PointModel extends Model
{
    protected $table            = 'point';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['id_dg', 'nama_dg', 'data_geospasial', 'atribut_tambahan'];
    protected $useTimestamps    = true;

    public function getWithStyle()
    {
        return $this->select('point.*, geospasial_grup.nama_grup, geospasial_grup.radius')
                    ->join('geospasial_grup', 'geospasial_grup.id_dg = point.id_dg')
                    ->findAll();
    }
}