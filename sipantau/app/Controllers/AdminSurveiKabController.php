<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class AdminSurveiKabController extends BaseController
{
    public function index()
    {
        $data = [
            'title' => 'Dashboard',
            'active_menu' => 'dashboard'
        ];
        
        return view('AdminSurveiKab/dashboard', $data);
    }

    public function AssignPetugas()
    {
        $data = [
            'title' => 'Assign Petugas Survey',
            'active_menu' => 'assign-admin-kab'
        ];
        
        return view('AdminSurveiKab/AssignPetugasSurvei/index', $data);
    }

    public function createAssignPetugas()
    {
        $data = [
            'title' => 'Tambah Assignment',
            'active_menu' => 'assign-admin-kab'
        ];
        
        return view('AdminSurveiKab/AssignPetugasSurvei/create', $data);
    }

    public function detail($id)
    {
        $data = [
            'title' => 'Detail PML',
            'active_menu' => 'assign-admin-kab',
            'pml_id' => $id
        ];
        
        return view('AdminSurveiKab/AssignPetugasSurvei/detail', $data);
    }

    public function kurva_s($id)
    {
        $data = [
            'title' => 'Kurva S PCL',
            'active_menu' => 'assign-admin-kab',
            'pcl_id' => $id
        ];
        
        return view('AdminSurveiKab/AssignPetugasSurvei/kurva_s', $data);
    }

    public function approve_laporan() {
        $data = [
            'title' => 'Approval Laporan',
            'active_menu' => 'comingsoon'
        ];

        return view('AdminSurveiKab/ApprovalLaporan/index', $data);
    }


}
