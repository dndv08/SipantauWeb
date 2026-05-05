<?php

namespace App\Controllers\AdminProv;

use App\Controllers\BaseController;
use App\Models\AdminSurveiKabupatenModel;
use App\Models\UserModel;
use App\Models\MasterKabModel;
use App\Models\MasterKegiatanWilayahModel;
use App\Models\KegiatanWilayahAdminModel;

class AssignAdminSurveiKabController extends BaseController
{
    protected $adminKabupatenModel;
    protected $userModel;
    protected $masterKabModel;
    protected $kegiatanWilayahModel;
    protected $kegiatanWilayahAdminModel;
    protected $db;
    protected $validation;

    public function __construct()
    {
        $this->adminKabupatenModel = new AdminSurveiKabupatenModel();
        $this->userModel = new UserModel();
        $this->masterKabModel = new MasterKabModel();
        $this->kegiatanWilayahModel = new MasterKegiatanWilayahModel();
        $this->kegiatanWilayahAdminModel = new KegiatanWilayahAdminModel();
        $this->validation = \Config\Services::validation();
        $this->db = \Config\Database::connect();
    }

    // Halaman index - daftar admin kabupaten dengan kegiatan wilayah mereka
    public function index()
    {
        $search = $this->request->getGet('search') ?? '';
        $filterKabupaten = $this->request->getGet('kabupaten') ?? '';

        // Ambil perPage dari GET, default 10
        $perPage = $this->request->getGet('perPage') ?? 10;

        // Validasi perPage agar hanya nilai yang diizinkan
        $allowedPerPage = [5, 10, 25, 50, 100];
        if (!in_array((int) $perPage, $allowedPerPage)) {
            $perPage = 10;
        }

        // Use AdminSurveiKabupatenModel untuk pagination
        $builder = $this->adminKabupatenModel
            ->select('admin_survei_kabupaten.id_admin_kabupaten, admin_survei_kabupaten.sobat_id, 
              sipantau_user.nama_user, sipantau_user.email, sipantau_user.hp, sipantau_user.is_active, 
              master_kabupaten.nama_kabupaten, master_kabupaten.id_kabupaten')
            ->join('sipantau_user', 'admin_survei_kabupaten.sobat_id = sipantau_user.sobat_id')
            ->join('master_kabupaten', 'sipantau_user.id_kabupaten = master_kabupaten.id_kabupaten', 'left')
            ->orderBy('master_kabupaten.nama_kabupaten', 'ASC')
            ->orderBy('sipantau_user.nama_user', 'ASC');

        if (!empty($search)) {
            $builder->groupStart()
                ->like('sipantau_user.nama_user', $search)
                ->orLike('sipantau_user.email', $search)
                ->orLike('master_kabupaten.nama_kabupaten', $search)
                ->groupEnd();
        }

        if (!empty($filterKabupaten)) {
            $builder->where('master_kabupaten.id_kabupaten', $filterKabupaten);
        }

        $adminList = $builder->paginate($perPage, 'admin_survei');

        // Get kegiatan wilayah untuk setiap admin dengan progress
        foreach ($adminList as &$admin) {
            $kegiatanWilayah = $this->db->table('kegiatan_wilayah_admin kwa')
                ->select('kw.id_kegiatan_wilayah, kw.target_wilayah, kw.keterangan, 
                     mkdp.nama_kegiatan_detail_proses, mkdp.tanggal_mulai, mkdp.tanggal_selesai,
                     mkd.nama_kegiatan_detail, mk.nama_kegiatan, kab.nama_kabupaten')
                ->join('kegiatan_wilayah kw', 'kwa.id_kegiatan_wilayah = kw.id_kegiatan_wilayah')
                ->join('master_kegiatan_detail_proses mkdp', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses')
                ->join('master_kegiatan_detail mkd', 'mkdp.id_kegiatan_detail = mkd.id_kegiatan_detail')
                ->join('master_kegiatan mk', 'mkd.id_kegiatan = mk.id_kegiatan')
                ->join('master_kabupaten kab', 'kw.id_kabupaten = kab.id_kabupaten')
                ->where('kwa.id_admin_kabupaten', $admin['id_admin_kabupaten'])
                ->orderBy('mkdp.tanggal_mulai', 'DESC')
                ->get()
                ->getResultArray();

            // Calculate progress for each kegiatan
            foreach ($kegiatanWilayah as &$kegiatan) {
                $targetWilayah = (int) $kegiatan['target_wilayah'];

                // Get realisasi dari PCL
                $realisasi = $this->db->table('pantau_progress pp')
                    ->select('COALESCE(SUM(pp.jumlah_realisasi_kumulatif), 0) as total_realisasi', false)
                    ->join('pcl', 'pp.id_pcl = pcl.id_pcl')
                    ->join('pml', 'pcl.id_pml = pml.id_pml')
                    ->where('pml.id_kegiatan_wilayah', $kegiatan['id_kegiatan_wilayah'])
                    ->groupBy('pp.id_pcl')
                    ->get()
                    ->getResultArray();

                $totalRealisasi = 0;
                foreach ($realisasi as $item) {
                    $totalRealisasi += (int) $item['total_realisasi'];
                }

                $kegiatan['realisasi'] = $totalRealisasi;

                // Calculate progress percentage
                if ($targetWilayah > 0) {
                    $kegiatan['progress'] = min(100, round(($totalRealisasi / $targetWilayah) * 100, 1));
                } else {
                    $kegiatan['progress'] = 0;
                }
            }

            $admin['kegiatan_wilayah'] = $kegiatanWilayah;
            $admin['jumlah_kegiatan'] = count($kegiatanWilayah);
        }

        $allKabupaten = $this->masterKabModel->orderBy('id_kabupaten', 'ASC')->findAll();

        $data = [
            'title' => 'Kelola Admin Survei Kabupaten',
            'active_menu' => 'assign-admin-kab',
            'admin_list' => $adminList,
            'allKabupaten' => $allKabupaten,
            'search' => $search,
            'filterKabupaten' => $filterKabupaten,
            'perPage' => $perPage,
            'pager' => $this->adminKabupatenModel->pager,
        ];

        return view('AdminSurveiProv/AssignAdminSurveiKab/index', $data);
    }

    // Halaman assign admin kabupaten ke kegiatan wilayah
    public function assign($idAdminKabupaten = null)
    {
        $isEdit = !is_null($idAdminKabupaten);
        $admin = null;
        $assignedIds = [];

        $allKabupaten = $this->masterKabModel->orderBy('id_kabupaten', 'ASC')->findAll();

        if ($isEdit) {
            // MODE EDIT - Get admin info
            $admin = $this->db->table('admin_survei_kabupaten ask')
                ->select('ask.*, u.nama_user, u.email, u.hp, k.nama_kabupaten, k.id_kabupaten')
                ->join('sipantau_user u', 'ask.sobat_id = u.sobat_id')
                ->join('master_kabupaten k', 'u.id_kabupaten = k.id_kabupaten', 'left')
                ->where('ask.id_admin_kabupaten', $idAdminKabupaten)
                ->get()
                ->getRowArray();

            if (!$admin) {
                return redirect()->to(base_url('adminsurvei/admin-survei-kab'))
                    ->with('error', 'Admin tidak ditemukan');
            }

            // Get assigned kegiatan wilayah
            $assigned = $this->db->table('kegiatan_wilayah_admin')
                ->select('id_kegiatan_wilayah')
                ->where('id_admin_kabupaten', $idAdminKabupaten)
                ->get()
                ->getResultArray();

            $assignedIds = array_column($assigned, 'id_kegiatan_wilayah');

            // Get all kegiatan wilayah untuk kabupaten ini
            $kegiatanWilayah = $this->db->table('kegiatan_wilayah kw')
                ->select('kw.id_kegiatan_wilayah, kw.target_wilayah, kw.keterangan,
                     mkdp.nama_kegiatan_detail_proses, mkdp.tanggal_mulai, mkdp.tanggal_selesai,
                     mkd.nama_kegiatan_detail, mk.nama_kegiatan, 
                     kab.nama_kabupaten, kab.id_kabupaten')
                ->join('master_kegiatan_detail_proses mkdp', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses')
                ->join('master_kegiatan_detail mkd', 'mkdp.id_kegiatan_detail = mkd.id_kegiatan_detail')
                ->join('master_kegiatan mk', 'mkd.id_kegiatan = mk.id_kegiatan')
                ->join('master_kabupaten kab', 'kw.id_kabupaten = kab.id_kabupaten')
                ->where('kw.id_kabupaten', $admin['id_kabupaten'])
                ->orderBy('mkdp.tanggal_mulai', 'DESC')
                ->get()
                ->getResultArray();

            $data = [
                'title' => 'Edit Assignment Admin Survei Kabupaten',
                'active_menu' => 'admin-survei-kab',
                'kegiatan_wilayah' => $kegiatanWilayah,
                'admin' => $admin,
                'assigned_ids' => $assignedIds,
                'allKabupaten' => $allKabupaten,
                'is_edit' => true,
                'users' => [] // Empty array untuk edit mode
            ];

            return view('AdminSurveiProv/AssignAdminSurveiKab/assign', $data);

        } else {
            // MODE CREATE - Get all active users grouped by kabupaten
            $users = $this->db->table('sipantau_user u')
                ->select('u.sobat_id, u.nama_user, u.email, u.hp, k.nama_kabupaten, k.id_kabupaten')
                ->join('master_kabupaten k', 'u.id_kabupaten = k.id_kabupaten', 'left')
                ->where('u.is_active', 1)
                ->orderBy('k.nama_kabupaten', 'ASC')
                ->orderBy('u.nama_user', 'ASC')
                ->get()
                ->getResultArray();

            // Get assigned kegiatan for each user
            $usersWithAssignments = [];
            foreach ($users as $user) {
                $adminRecord = $this->db->table('admin_survei_kabupaten')
                    ->where('sobat_id', $user['sobat_id'])
                    ->get()
                    ->getRowArray();

                $assignedKegiatanIds = [];
                if ($adminRecord) {
                    $assignedKegiatan = $this->db->table('kegiatan_wilayah_admin')
                        ->select('id_kegiatan_wilayah')
                        ->where('id_admin_kabupaten', $adminRecord['id_admin_kabupaten'])
                        ->get()
                        ->getResultArray();
                    $assignedKegiatanIds = array_column($assignedKegiatan, 'id_kegiatan_wilayah');
                }

                $user['assigned_kegiatan_ids'] = $assignedKegiatanIds;
                $usersWithAssignments[] = $user;
            }

            // Get all kegiatan wilayah dengan detail
            $kegiatanWilayah = $this->db->table('kegiatan_wilayah kw')
                ->select('kw.id_kegiatan_wilayah, kw.target_wilayah, kw.keterangan,
                     mkdp.nama_kegiatan_detail_proses, mkdp.tanggal_mulai, mkdp.tanggal_selesai,
                     mkd.nama_kegiatan_detail, mk.nama_kegiatan, 
                     kab.nama_kabupaten, kab.id_kabupaten')
                ->join('master_kegiatan_detail_proses mkdp', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses')
                ->join('master_kegiatan_detail mkd', 'mkdp.id_kegiatan_detail = mkd.id_kegiatan_detail')
                ->join('master_kegiatan mk', 'mkd.id_kegiatan = mk.id_kegiatan')
                ->join('master_kabupaten kab', 'kw.id_kabupaten = kab.id_kabupaten')
                ->orderBy('kab.nama_kabupaten', 'ASC')
                ->orderBy('mkdp.tanggal_mulai', 'DESC')
                ->get()
                ->getResultArray();

            $data = [
                'title' => 'Assign Admin Survei Kabupaten',
                'active_menu' => 'admin-survei-kab',
                'users' => $usersWithAssignments,
                'kegiatan_wilayah' => $kegiatanWilayah,
                'allKabupaten' => $allKabupaten,
                'is_edit' => false,
                'admin' => null,
                'assigned_ids' => []
            ];

            return view('AdminSurveiProv/AssignAdminSurveiKab/assign', $data);
        }
    }

    // Process assign admin kabupaten
    public function storeAssign()
    {
        $sobatId = $this->request->getPost('sobat_id');
        $kegiatanWilayahIds = $this->request->getPost('kegiatan_wilayah');

        // Validation
        if (empty($sobatId)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Silakan pilih user');
        }

        if (empty($kegiatanWilayahIds) || !is_array($kegiatanWilayahIds)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Silakan pilih minimal satu kegiatan wilayah');
        }

        // Check apakah user exists
        $user = $this->userModel->find($sobatId);
        if (!$user) {
            return redirect()->back()->with('error', 'User tidak ditemukan');
        }

        $this->db->transStart();

        // Check apakah user sudah menjadi admin kabupaten
        $existingAdmin = $this->adminKabupatenModel->where('sobat_id', $sobatId)->first();

        if ($existingAdmin) {
            $idAdminKabupaten = $existingAdmin['id_admin_kabupaten'];
        } else {
            // Jika belum, buat entry baru di admin_survei_kabupaten
            $this->db->table('admin_survei_kabupaten')->insert(['sobat_id' => $sobatId]);
            $idAdminKabupaten = $this->db->insertID();
        }

        // Assign multiple kegiatan wilayah
        $insertedCount = 0;
        foreach ($kegiatanWilayahIds as $idKegiatanWilayah) {
            // Check apakah admin sudah di-assign ke kegiatan wilayah ini
            $existingAssignment = $this->db->table('kegiatan_wilayah_admin')
                ->where([
                    'id_admin_kabupaten' => $idAdminKabupaten,
                    'id_kegiatan_wilayah' => $idKegiatanWilayah
                ])
                ->get()
                ->getRowArray();

            if (!$existingAssignment) {
                $this->db->table('kegiatan_wilayah_admin')->insert([
                    'id_admin_kabupaten' => $idAdminKabupaten,
                    'id_kegiatan_wilayah' => $idKegiatanWilayah
                ]);
                $insertedCount++;
            }
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return redirect()->back()->with('error', 'Gagal melakukan assignment');
        }

        $message = $insertedCount > 0
            ? "Admin berhasil di-assign ke {$insertedCount} kegiatan wilayah"
            : "Admin sudah di-assign ke semua kegiatan wilayah yang dipilih";

        return redirect()->to(base_url('adminsurvei/admin-survei-kab'))
            ->with('success', $message);
    }

    // Update assignment admin
    public function update($idAdminKabupaten)
    {
        $kegiatanWilayahIds = $this->request->getPost('kegiatan_wilayah');

        // Validation
        if (empty($kegiatanWilayahIds) || !is_array($kegiatanWilayahIds)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Silakan pilih minimal satu kegiatan wilayah');
        }

        $this->db->transStart();

        // Hapus semua assignment lama
        $this->db->table('kegiatan_wilayah_admin')
            ->where('id_admin_kabupaten', $idAdminKabupaten)
            ->delete();

        // Insert assignment baru
        foreach ($kegiatanWilayahIds as $idKegiatanWilayah) {
            $this->db->table('kegiatan_wilayah_admin')->insert([
                'id_admin_kabupaten' => $idAdminKabupaten,
                'id_kegiatan_wilayah' => $idKegiatanWilayah
            ]);
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return redirect()->back()->with('error', 'Gagal melakukan update');
        }

        return redirect()->to(base_url('adminsurvei/admin-survei-kab'))
            ->with('success', 'Assignment berhasil diupdate');
    }

    // Delete assignment admin dari kegiatan wilayah tertentu
    public function deleteAssignment()
    {
        // Get JSON input
        $json = $this->request->getJSON();

        // Try getPost as fallback
        $idAdminKabupaten = $json->id_admin_kabupaten ?? $this->request->getPost('id_admin_kabupaten');
        $idKegiatanWilayah = $json->id_kegiatan_wilayah ?? $this->request->getPost('id_kegiatan_wilayah');

        if (!$idAdminKabupaten || !$idKegiatanWilayah) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Data tidak lengkap'
            ]);
        }

        $this->db->transStart();

        // Hapus assignment
        $this->db->table('kegiatan_wilayah_admin')
            ->where([
                'id_admin_kabupaten' => $idAdminKabupaten,
                'id_kegiatan_wilayah' => $idKegiatanWilayah
            ])
            ->delete();

        // Check apakah admin masih memiliki kegiatan lain
        $remainingAssignments = $this->db->table('kegiatan_wilayah_admin')
            ->where('id_admin_kabupaten', $idAdminKabupaten)
            ->countAllResults();

        // Jika tidak ada kegiatan lagi, hapus dari admin_survei_kabupaten
        if ($remainingAssignments == 0) {
            $this->db->table('admin_survei_kabupaten')
                ->where('id_admin_kabupaten', $idAdminKabupaten)
                ->delete();
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal menghapus assignment'
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Assignment berhasil dihapus',
            'redirect' => $remainingAssignments == 0
        ]);
    }

    // Delete admin completely (beserta semua assignment)
    public function delete($idAdminKabupaten)
    {
        if (!$idAdminKabupaten) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID Admin tidak valid'
            ]);
        }

        $this->db->transStart();

        // Hapus semua assignment
        $this->db->table('kegiatan_wilayah_admin')
            ->where('id_admin_kabupaten', $idAdminKabupaten)
            ->delete();

        // Hapus dari admin_survei_kabupaten
        $this->db->table('admin_survei_kabupaten')
            ->where('id_admin_kabupaten', $idAdminKabupaten)
            ->delete();

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal menghapus admin'
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Admin beserta semua assignment berhasil dihapus'
        ]);
    }

    // Get kegiatan wilayah by kabupaten (AJAX)
    public function getKegiatanByKabupaten($idKabupaten)
    {
        $kegiatanWilayah = $this->db->table('kegiatan_wilayah kw')
            ->select('kw.id_kegiatan_wilayah, kw.target_wilayah, kw.keterangan,
                     mkdp.nama_kegiatan_detail_proses, mkdp.tanggal_mulai, mkdp.tanggal_selesai,
                     mkd.nama_kegiatan_detail, mk.nama_kegiatan')
            ->join('master_kegiatan_detail_proses mkdp', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses')
            ->join('master_kegiatan_detail mkd', 'mkdp.id_kegiatan_detail = mkd.id_kegiatan_detail')
            ->join('master_kegiatan mk', 'mkd.id_kegiatan = mk.id_kegiatan')
            ->where('kw.id_kabupaten', $idKabupaten)
            ->orderBy('mkdp.tanggal_mulai', 'DESC')
            ->get()
            ->getResultArray();

        return $this->response->setJSON($kegiatanWilayah);
    }

    // Get assigned kegiatan for a user (AJAX)
    public function getAssignedKegiatan($sobatId)
    {
        // Get admin_kabupaten record for this user
        $admin = $this->db->table('admin_survei_kabupaten')
            ->where('sobat_id', $sobatId)
            ->get()
            ->getRowArray();

        if (!$admin) {
            return $this->response->setJSON([]);
        }

        // Get assigned kegiatan IDs
        $assignedKegiatan = $this->db->table('kegiatan_wilayah_admin')
            ->select('id_kegiatan_wilayah')
            ->where('id_admin_kabupaten', $admin['id_admin_kabupaten'])
            ->get()
            ->getResultArray();

        $kegiatanIds = array_column($assignedKegiatan, 'id_kegiatan_wilayah');

        return $this->response->setJSON($kegiatanIds);
    }
}