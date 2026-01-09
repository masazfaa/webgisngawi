<?php

namespace App\Controllers;

class Home extends BaseController
{
    // protected $db;
    // protected $m_aset;

    // public function __construct()
    // {
    //     $this->m_aset = new m_aset();
    //     $this->db = \Config\Database::connect();
    // }

    public function index()
    {
        $data = [
            'title' => 'Web Map',
            // 'dataLaporan' => $this->m_aset->getAllLaporan(),
            'isi' => 'v_home'
        ];

        return view('template/v_wrapperr', $data);
    }

    public function data()
    {
        $data = [
            'title' => 'Manajemen Laporan',
            // 'dataLaporan' => $this->m_aset->getAllLaporan(),
            'isi' => 'v_data'
        ];

        return view('template/v_wrapper', $data);
    }
}
