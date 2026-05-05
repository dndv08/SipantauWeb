<?php

namespace App\Controllers\PemantauKab;

use App\Controllers\BaseController;
use App\Models\MasterKegiatanWilayahModel;
use App\Models\UserModel;

class MasterKegiatanWilayahController extends BaseController
{
    protected $masterKegiatanWilayah;
    protected $userModel;

    public function __construct()
    {
        $this->masterKegiatanWilayah = new MasterKegiatanWilayahModel();
        $this->userModel = new UserModel();
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        // Get role dan user info dari session
        $role = session()->get('role');
        $roleType = session()->get('role_type');
        $sobatId = session()->get('sobat_id');
        
        $isPemantauKabupaten = ($role == 3 && $roleType == 'pemantau_kabupaten');
        
        if (!$isPemantauKabupaten) {
            return redirect()->to(base_url('unauthorized'))
                ->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }

        // Get kabupaten user dari database
        $user = $this->userModel->find($sobatId);
        
        if (!$user || !$user['id_kabupaten']) {
            return redirect()->to(base_url('unauthorized'))
                ->with('error', 'Data kabupaten tidak ditemukan.');
        }

        $idKabupaten = $user['id_kabupaten'];
        
        // Ambil perPage dari GET, default 10
        $perPage = $this->request->getGet('perPage') ?? 10;
        
        // Validasi perPage agar hanya nilai yang diizinkan
        $allowedPerPage = [5, 10, 25, 50, 100];
        if (!in_array((int)$perPage, $allowedPerPage)) {
            $perPage = 10;
        }

        // Pagination setup
        $page = $this->request->getVar('page') ?? 1;

        // Query dengan pagination - HANYA untuk kabupaten user ini
        $query = $this->masterKegiatanWilayah
            ->getAllWithDetailsQuery($idKabupaten)
            ->paginate($perPage, 'kegiatanWilayah');

        // Get nama kabupaten untuk ditampilkan
        $kabupaten = $this->db->table('master_kabupaten')
            ->where('id_kabupaten', $idKabupaten)
            ->get()
            ->getRowArray();

        $data = [
            'title' => 'Pemantau Kabupaten - Kegiatan Wilayah',
            'active_menu' => 'kegiatan-wilayah',
            'kegiatanWilayah' => $query,
            'perPage' => $perPage,
            'pager' => $this->masterKegiatanWilayah->pager,
            'kabupaten' => $kabupaten
        ];

        return view('PemantauKabupaten/KegiatanWilayah/index', $data);
    }
}