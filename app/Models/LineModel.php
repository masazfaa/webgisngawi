<?php

namespace App\Models;

use CodeIgniter\Model;

class LineModel extends Model
{
    protected $table            = 'line';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['id_dg', 'nama_dg', 'data_geospasial', 'atribut_tambahan'];
    protected $useTimestamps    = true;

    public function getWithStyle()
    {
        return $this->select('line.*, geospasial_grup.nama_grup, geospasial_grup.color')
                    ->join('geospasial_grup', 'geospasial_grup.id_dg = line.id_dg')
                    ->findAll();
    }
}