<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class PemantauController extends BaseController
{
    public function index()
    {
         $data = [
            'title' => 'Dashboard',
            'active_menu' => 'dashboard'
        ];
        
        return view('PemantauProvinsi/dashboard', $data);
    }

     public function DetailProses()
    {
         $data = [
            'title' => 'Kegiatan Detail Proses',
            'active_menu' => 'detail-proses'
        ];
        
        return view('Pemantau/DetailProses/index', $data);
    }

    public function KegiatanWilayah()
    {
         $data = [
            'title' => 'Kegiatan Wilayah',
            'active_menu' => 'kegiatan-wilayah-pemantau'
        ];
        
        return view('Pemantau/KegiatanWilayah/index', $data);
    }

    public function DataPetugas()
    {
         $data = [
            'title' => 'Data Petugas',
            'active_menu' => 'data-petugas'
        ];
        
        return view('PemantauProvinsi/DataPetugas/index', $data);
    }

    public function LaporanPetugas()
    {
         $data = [
            'title' => 'Data Petugas',
            'active_menu' => 'laporan-petugas'
        ];
        
        return view('PemantauProvinsi/LaporanPetugas/index', $data);
    }
    public function detailLaporanPetugas($id)
    {
        $data = [
            'title' => 'Detail Laporan Petugas',
            'active_menu' => 'laporan-petugas',
            'petugas_id' => $id
        ];
        
        return view('PemantauProvinsi/LaporanPetugas/detail', $data);
    }




}
