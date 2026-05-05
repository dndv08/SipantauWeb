<?php

namespace App\Controllers\SuperAdmin;

use App\Controllers\BaseController;
use App\Models\AdminSurveiProvinsiModel;
use App\Models\AdminSurveiKabupatenModel;
use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\MasterKegiatanDetailModel;
use App\Models\MasterKegiatanDetailAdminModel;
use App\Models\MasterKegiatanDetailProsesModel;

class KelolaSurveiProvinsiController extends BaseController
{
    protected $adminProvinsiModel;
    protected $adminKabupatenModel;
    protected $userModel;
    protected $roleModel;
    protected $kegiatanDetailModel;
    protected $kegiatanDetailAdminModel;
    protected $kegiatanDetailProsesModel;
    protected $db;

    public function __construct()
    {
        $this->adminProvinsiModel = new AdminSurveiProvinsiModel();
        $this->adminKabupatenModel = new AdminSurveiKabupatenModel();
        $this->userModel = new UserModel();
        $this->roleModel = new RoleModel();
        $this->kegiatanDetailModel = new MasterKegiatanDetailModel();
        $this->kegiatanDetailAdminModel = new MasterKegiatanDetailAdminModel();
        $this->kegiatanDetailProsesModel = new MasterKegiatanDetailProsesModel();
        $this->db = \Config\Database::connect();
    }

    // Halaman index - daftar admin dengan kegiatan mereka
    public function index()
    {
        $search = $this->request->getGet('search') ?? '';
        $roleFilter = $this->request->getGet('role') ?? '';

        // TAMBAHKAN INI - Ambil perPage dari GET, default 10
        $perPage = $this->request->getGet('perPage') ?? 10;

        // TAMBAHKAN INI - Validasi perPage
        $allowedPerPage = [5, 10, 25, 50, 100];
        if (!in_array((int) $perPage, $allowedPerPage)) {
            $perPage = 10;
        }

        // Get all admin survei provinsi dengan informasi user dan kegiatan
        $builder = $this->db->table('admin_survei_provinsi asp')
            ->select('asp.id_admin_provinsi, asp.sobat_id, u.nama_user, u.email, u.hp, u.is_active, u.role, k.nama_kabupaten')
            ->join('sipantau_user u', 'asp.sobat_id = u.sobat_id')
            ->join('master_kabupaten k', 'u.id_kabupaten = k.id_kabupaten', 'left')
            ->orderBy('u.nama_user', 'ASC');

        if (!empty($search)) {
            $builder->groupStart()
                ->like('u.nama_user', $search)
                ->orLike('u.email', $search)
                ->orLike('u.hp', $search)
                ->groupEnd();
        }

        // TAMBAHKAN INI - Get total untuk pagination
        $total = $builder->countAllResults(false);

        // TAMBAHKAN INI - Get current page dan offset
        $page = (int) ($this->request->getGet('page_admin') ?? 1);
        $offset = ($page - 1) * $perPage;

        // UBAH INI - Tambahkan limit dan offset
        $adminList = $builder->limit($perPage, $offset)->get()->getResultArray();

        // Get kegiatan untuk setiap admin
        foreach ($adminList as &$admin) {
            $kegiatan = $this->db->table('master_kegiatan_detail_admin mkda')
                ->select('mkd.id_kegiatan_detail, mkd.nama_kegiatan_detail, mk.nama_kegiatan, mkd.periode, mkd.tahun, mkd.satuan, mkd.tanggal_mulai, mkd.tanggal_selesai')
                ->join('master_kegiatan_detail mkd', 'mkda.id_kegiatan_detail = mkd.id_kegiatan_detail')
                ->join('master_kegiatan mk', 'mkd.id_kegiatan = mk.id_kegiatan')
                ->where('mkda.id_admin_provinsi', $admin['id_admin_provinsi'])
                ->get()
                ->getResultArray();

            $admin['kegiatan'] = $kegiatan;
            $admin['jumlah_kegiatan'] = count($kegiatan);

            // Get role names dengan role tambahan
            $admin['role_names'] = $this->getUserRoleNames($admin['sobat_id'], $admin['role']);
        }

        // TAMBAHKAN INI - Setup pager
        $pager = \Config\Services::pager();
        $pager->store('admin', $page, $perPage, $total);

        $data = [
            'title' => 'Kelola Admin Survei Provinsi',
            'active_menu' => 'kelola-admin-surveyprov',
            'admin_list' => $adminList,
            'search' => $search,
            'perPage' => $perPage,      // TAMBAHKAN INI
            'pager' => $pager           // TAMBAHKAN INI
        ];

        return view('SuperAdmin/KelolaAdminSurvey/index', $data);
    }

    // Get user role names termasuk role tambahan dari tabel admin
    private function getUserRoleNames($sobatId, $roleJson)
    {
        $roleNames = [];

        // Decode role dari tabel user
        $userRoles = [];
        if (is_string($roleJson) && (str_starts_with($roleJson, '[') || str_starts_with($roleJson, '{'))) {
            $decoded = json_decode($roleJson, true);
            if (is_array($decoded)) {
                $userRoles = array_map('intval', $decoded);
            }
        } else {
            $userRoles = [(int) $roleJson];
        }

        // Check admin status
        $isAdminProvinsi = $this->adminProvinsiModel->isAdminProvinsi($sobatId);
        $isAdminKabupaten = $this->adminKabupatenModel->isAdminKabupaten($sobatId);

        // Build available roles dengan nama yang sesuai
        foreach ($userRoles as $roleId) {
            $roleInfo = $this->roleModel->find($roleId);
            if ($roleInfo) {
                if ($roleId == 2) {
                    $roleNames[] = 'Pemantau Provinsi';
                } elseif ($roleId == 3) {
                    $roleNames[] = 'Pemantau Kabupaten';
                } else {
                    $roleNames[] = $roleInfo['roleuser'];
                }
            }
        }

        // Tambahkan role admin jika terdaftar
        if ($isAdminProvinsi) {
            $roleNames[] = 'Admin Survei Provinsi';
        }

        if ($isAdminKabupaten) {
            $roleNames[] = 'Admin Survei Kabupaten';
        }

        return $roleNames;
    }

    // Halaman assign/edit admin survei provinsi ke kegiatan
    public function assign($idAdminProvinsi = null)
    {
        $isEdit = !is_null($idAdminProvinsi);
        $admin = null;
        $assignedIds = [];

        if ($isEdit) {
            // Get admin info
            $admin = $this->db->table('admin_survei_provinsi asp')
                ->select('asp.*, u.nama_user, u.email, u.hp')
                ->join('sipantau_user u', 'asp.sobat_id = u.sobat_id')
                ->where('asp.id_admin_provinsi', $idAdminProvinsi)
                ->get()
                ->getRowArray();

            if (!$admin) {
                return redirect()->to(base_url('superadmin/kelola-admin-surveyprov'))
                    ->with('error', 'Admin tidak ditemukan');
            }

            // Get assigned kegiatan dengan detail lengkap
            $assignedKegiatan = $this->db->table('master_kegiatan_detail_admin mkda')
                ->select('mkd.id_kegiatan_detail, mkd.nama_kegiatan_detail, mk.nama_kegiatan, mkd.satuan, mkd.periode, mkd.tahun, mkd.tanggal_mulai, mkd.tanggal_selesai')
                ->join('master_kegiatan_detail mkd', 'mkda.id_kegiatan_detail = mkd.id_kegiatan_detail')
                ->join('master_kegiatan mk', 'mkd.id_kegiatan = mk.id_kegiatan')
                ->where('mkda.id_admin_provinsi', $idAdminProvinsi)
                ->get()
                ->getResultArray();

            $assignedIds = array_column($assignedKegiatan, 'id_kegiatan_detail');
            $admin['assigned_kegiatan'] = $assignedKegiatan;
        }

        // Get all active users (untuk mode create)
        $users = $this->db->table('sipantau_user')
            ->select('sobat_id, nama_user, email, hp')
            ->where('is_active', 1)
            ->orderBy('nama_user', 'ASC')
            ->get()
            ->getResultArray();

        // Get all kegiatan detail
        $kegiatanDetails = $this->kegiatanDetailModel->getWithKegiatan();

        $data = [
            'title' => $isEdit ? 'Edit Admin Survei Provinsi' : 'Assign Admin Survei Provinsi',
            'active_menu' => 'kelola-admin-surveyprov',
            'users' => $users,
            'kegiatan_details' => $kegiatanDetails,
            'is_edit' => $isEdit,
            'admin' => $admin,
            'assigned_ids' => $assignedIds
        ];

        return view('SuperAdmin/KelolaAdminSurvey/assign', $data);
    }

    // Process assign admin survei provinsi
    public function storeAssign()
    {
        $sobatId = $this->request->getPost('sobat_id');
        $idKegiatanDetail = $this->request->getPost('id_kegiatan_detail');

        // Validation
        if (empty($sobatId)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Silakan pilih user');
        }

        if (empty($idKegiatanDetail)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Silakan pilih kegiatan detail');
        }

        // Check apakah user exists
        $user = $this->userModel->find($sobatId);
        if (!$user) {
            return redirect()->back()->with('error', 'User tidak ditemukan');
        }

        $this->db->transStart();

        // Check apakah user sudah menjadi admin provinsi
        $existingAdmin = $this->adminProvinsiModel->where('sobat_id', $sobatId)->first();

        if ($existingAdmin) {
            $idAdminProvinsi = $existingAdmin['id_admin_provinsi'];
        } else {
            // Jika belum, buat entry baru di admin_survei_provinsi
            $this->db->table('admin_survei_provinsi')->insert(['sobat_id' => $sobatId]);
            $idAdminProvinsi = $this->db->insertID();
        }

        // Check apakah admin sudah di-assign ke kegiatan ini
        $existingAssignment = $this->db->table('master_kegiatan_detail_admin')
            ->where([
                'id_admin_provinsi' => $idAdminProvinsi,
                'id_kegiatan_detail' => $idKegiatanDetail
            ])
            ->get()
            ->getRowArray();

        if ($existingAssignment) {
            return redirect()->back()
                ->with('error', 'Admin sudah di-assign ke kegiatan ini');
        }

        // Insert assignment
        $this->db->table('master_kegiatan_detail_admin')->insert([
            'id_admin_provinsi' => $idAdminProvinsi,
            'id_kegiatan_detail' => $idKegiatanDetail
        ]);

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return redirect()->back()->with('error', 'Gagal melakukan assignment');
        }

        return redirect()->to(base_url('superadmin/kelola-admin-surveyprov'))
            ->with('success', 'Admin berhasil di-assign ke kegiatan');
    }

    // Update assignment admin - Menambah kegiatan baru
    public function update($idAdminProvinsi)
    {
        $idKegiatanDetail = $this->request->getPost('id_kegiatan_detail');

        // Validation
        if (empty($idKegiatanDetail)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Silakan pilih kegiatan detail');
        }

        // Check apakah admin exists
        $admin = $this->adminProvinsiModel->find($idAdminProvinsi);
        if (!$admin) {
            return redirect()->back()->with('error', 'Admin tidak ditemukan');
        }

        // Check apakah sudah di-assign ke kegiatan ini
        $existingAssignment = $this->db->table('master_kegiatan_detail_admin')
            ->where([
                'id_admin_provinsi' => $idAdminProvinsi,
                'id_kegiatan_detail' => $idKegiatanDetail
            ])
            ->get()
            ->getRowArray();

        if ($existingAssignment) {
            return redirect()->back()
                ->with('error', 'Admin sudah di-assign ke kegiatan ini');
        }

        $this->db->transStart();

        // Insert assignment baru
        $this->db->table('master_kegiatan_detail_admin')->insert([
            'id_admin_provinsi' => $idAdminProvinsi,
            'id_kegiatan_detail' => $idKegiatanDetail
        ]);

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return redirect()->back()->with('error', 'Gagal menambahkan kegiatan');
        }

        return redirect()->to(base_url('superadmin/kelola-admin-surveyprov/assign/' . $idAdminProvinsi))
            ->with('success', 'Kegiatan berhasil ditambahkan');
    }

    // Delete assignment admin dari kegiatan tertentu
    public function deleteAssignment()
    {
        $idAdminProvinsi = $this->request->getPost('id_admin_provinsi');
        $idKegiatanDetail = $this->request->getPost('id_kegiatan_detail');

        if (!$idAdminProvinsi || !$idKegiatanDetail) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Data tidak lengkap'
            ]);
        }

        $this->db->transStart();

        // Hapus assignment
        $this->db->table('master_kegiatan_detail_admin')
            ->where([
                'id_admin_provinsi' => $idAdminProvinsi,
                'id_kegiatan_detail' => $idKegiatanDetail
            ])
            ->delete();

        // Check apakah admin masih memiliki kegiatan lain
        $remainingAssignments = $this->db->table('master_kegiatan_detail_admin')
            ->where('id_admin_provinsi', $idAdminProvinsi)
            ->countAllResults();

        // Jika tidak ada kegiatan lagi, hapus dari admin_survei_provinsi
        if ($remainingAssignments == 0) {
            $this->db->table('admin_survei_provinsi')
                ->where('id_admin_provinsi', $idAdminProvinsi)
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
            'remaining' => $remainingAssignments
        ]);
    }

    //  Delete admin completely
    public function delete($idAdminProvinsi)
    {
        $this->db->transStart();

        // Hapus semua assignment
        $this->db->table('master_kegiatan_detail_admin')
            ->where('id_admin_provinsi', $idAdminProvinsi)
            ->delete();

        // Hapus dari admin_survei_provinsi
        $this->db->table('admin_survei_provinsi')
            ->where('id_admin_provinsi', $idAdminProvinsi)
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
            'message' => 'Admin berhasil dihapus'
        ]);
    }

    // View detail admin dengan daftar kegiatan dan progress
    public function detail($idAdminProvinsi)
    {
        // Get admin info
        $admin = $this->db->table('admin_survei_provinsi asp')
            ->select('asp.*, u.nama_user, u.email, u.hp, k.nama_kabupaten')
            ->join('sipantau_user u', 'asp.sobat_id = u.sobat_id')
            ->join('master_kabupaten k', 'u.id_kabupaten = k.id_kabupaten', 'left')
            ->where('asp.id_admin_provinsi', $idAdminProvinsi)
            ->get()
            ->getRowArray();

        if (!$admin) {
            return redirect()->to(base_url('superadmin/kelola-admin-surveyprov'))
                ->with('error', 'Admin tidak ditemukan');
        }

        // Get kegiatan yang di-assign
        $kegiatan = $this->db->table('master_kegiatan_detail_admin mkda')
            ->select('mkd.*, mk.nama_kegiatan')
            ->join('master_kegiatan_detail mkd', 'mkda.id_kegiatan_detail = mkd.id_kegiatan_detail')
            ->join('master_kegiatan mk', 'mkd.id_kegiatan = mk.id_kegiatan')
            ->where('mkda.id_admin_provinsi', $idAdminProvinsi)
            ->orderBy('mkd.tahun', 'DESC')
            ->get()
            ->getResultArray();

        // Get proses untuk setiap kegiatan dengan progress
        foreach ($kegiatan as &$k) {
            // Get all proses
            $prosesList = $this->db->table('master_kegiatan_detail_proses')
                ->where('id_kegiatan_detail', $k['id_kegiatan_detail'])
                ->orderBy('tanggal_mulai', 'ASC')
                ->get()
                ->getResultArray();

            // Calculate progress untuk setiap proses
            $totalProses = count($prosesList);
            $totalProgressSum = 0;

            foreach ($prosesList as &$proses) {
                // Get target dari master_kegiatan_detail_proses
                $targetProses = (int) $proses['target'];

                // Hitung realisasi kumulatif dari semua PCL yang terkait dengan proses ini
                $realisasiKumulatif = $this->db->table('pantau_progress pp')
                    ->select('COALESCE(MAX(pp.jumlah_realisasi_kumulatif), 0) as total_realisasi', false)
                    ->join('pcl', 'pp.id_pcl = pcl.id_pcl')
                    ->join('pml', 'pcl.id_pml = pml.id_pml')
                    ->join('kegiatan_wilayah kw', 'pml.id_kegiatan_wilayah = kw.id_kegiatan_wilayah')
                    ->where('kw.id_kegiatan_detail_proses', $proses['id_kegiatan_detail_proses'])
                    ->groupBy('pp.id_pcl')
                    ->get()
                    ->getResultArray();

                // Jumlahkan realisasi dari semua PCL
                $totalRealisasi = 0;
                foreach ($realisasiKumulatif as $item) {
                    $totalRealisasi += (int) $item['total_realisasi'];
                }

                // Hitung persentase progress
                if ($targetProses > 0) {
                    $progressPercentage = min(100, round(($totalRealisasi / $targetProses) * 100, 1));
                } else {
                    $progressPercentage = 0;
                }

                // Set progress dan status
                $proses['target'] = $targetProses;
                $proses['realisasi'] = $totalRealisasi;
                $proses['progress'] = $progressPercentage;

                // Determine status based on progress and dates
                $today = date('Y-m-d');
                $start = $proses['tanggal_mulai'];
                $end = $proses['tanggal_selesai_target'];

                if ($today < $start) {
                    $proses['status'] = 'Belum Dimulai';
                    $proses['status_class'] = 'gray';
                } elseif ($progressPercentage >= 100) {
                    $proses['status'] = 'Selesai';
                    $proses['status_class'] = 'green';
                } elseif ($today > $end && $progressPercentage < 100) {
                    $proses['status'] = 'Terlambat';
                    $proses['status_class'] = 'red';
                } else {
                    $proses['status'] = 'Sedang Berlangsung';
                    $proses['status_class'] = 'blue';
                }

                // Get jumlah wilayah untuk proses ini
                $jumlahWilayah = $this->db->table('kegiatan_wilayah')
                    ->where('id_kegiatan_detail_proses', $proses['id_kegiatan_detail_proses'])
                    ->countAllResults();

                $proses['jumlah_wilayah'] = $jumlahWilayah;

                // Get jumlah PML dan PCL
                $jumlahPML = $this->db->table('pml')
                    ->join('kegiatan_wilayah kw', 'pml.id_kegiatan_wilayah = kw.id_kegiatan_wilayah')
                    ->where('kw.id_kegiatan_detail_proses', $proses['id_kegiatan_detail_proses'])
                    ->countAllResults();

                $jumlahPCL = $this->db->table('pcl')
                    ->join('pml', 'pcl.id_pml = pml.id_pml')
                    ->join('kegiatan_wilayah kw', 'pml.id_kegiatan_wilayah = kw.id_kegiatan_wilayah')
                    ->where('kw.id_kegiatan_detail_proses', $proses['id_kegiatan_detail_proses'])
                    ->countAllResults();

                $proses['jumlah_pml'] = $jumlahPML;
                $proses['jumlah_pcl'] = $jumlahPCL;

                $totalProgressSum += $progressPercentage;
            }

            $k['proses_list'] = $prosesList;
            $k['total_proses'] = $totalProses;
            $k['overall_progress'] = $totalProses > 0 ? round($totalProgressSum / $totalProses, 1) : 0;
        }

        $data = [
            'title' => 'Detail Admin Survei Provinsi',
            'active_menu' => 'kelola-admin-surveyprov',
            'admin' => $admin,
            'kegiatan' => $kegiatan
        ];

        return view('SuperAdmin/KelolaAdminSurvey/detail', $data);
    }
}