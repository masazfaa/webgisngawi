<?php

namespace App\Models;

use CodeIgniter\Model;

class PoligonModel extends Model
{
    protected $table            = 'poligon';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $allowedFields    = ['id_dg', 'nama_dg', 'data_geospasial', 'atribut_tambahan'];
    protected $useTimestamps    = true;

    // Fungsi helper untuk join ke tabel grup (mengambil style)
    public function getWithStyle()
    {
        return $this->select('poligon.*, geospasial_grup.nama_grup, geospasial_grup.color, geospasial_grup.fillColor')
                    ->join('geospasial_grup', 'geospasial_grup.id_dg = poligon.id_dg')
                    ->findAll();
    }
}