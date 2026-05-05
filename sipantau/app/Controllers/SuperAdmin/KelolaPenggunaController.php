<?php

namespace App\Controllers\SuperAdmin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\AdminSurveiProvinsiModel;
use App\Models\AdminSurveiKabupatenModel;
use CodeIgniter\HTTP\ResponseInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class KelolaPenggunaController extends BaseController
{
    protected $userModel;
    protected $roleModel;
    protected $adminProvinsiModel;
    protected $adminKabupatenModel;
    protected $db;

    public function __construct()
    {

        $this->userModel = new UserModel();
        $this->roleModel = new RoleModel();
        $this->adminProvinsiModel = new AdminSurveiProvinsiModel();
        $this->adminKabupatenModel = new AdminSurveiKabupatenModel();
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        $search = $this->request->getGet('search') ?? '';
        $roleFilter = $this->request->getGet('role') ?? '';
        $perPage = $this->request->getGet('perPage') ?? 10;

        // Validasi perPage
        $allowedPerPage = [5, 10, 25, 50, 100];
        if (!in_array((int) $perPage, $allowedPerPage)) {
            $perPage = 10;
        }

        // Get current page
        $page = (int) ($this->request->getGet('page_users') ?? 1);

        // Get users dengan query builder
        $builder = $this->db->table('sipantau_user u')
            ->select('u.*, mk.nama_kabupaten')
            ->join('master_kabupaten mk', 'u.id_kabupaten = mk.id_kabupaten', 'left')
            ->where('u.is_active >=', 0)
            ->orderBy('u.nama_user', 'ASC');

        if (!empty($search)) {
            $builder->groupStart()
                ->like('u.nama_user', $search)
                ->orLike('u.email', $search)
                ->orLike('u.hp', $search)
                ->groupEnd();
        }

        // Clone builder untuk count
        $totalBuilder = clone $builder;
        $total = $totalBuilder->countAllResults();

        // Get data dengan pagination
        $offset = ($page - 1) * $perPage;
        $usersRaw = $builder->limit($perPage, $offset)->get()->getResultArray();

        // Enrich dengan role names
        $users = [];
        foreach ($usersRaw as $user) {
            $user['role_names'] = $this->getUserRoleNames($user['sobat_id'], $user['role']);

            // Filter by role jika ada
            if (!empty($roleFilter)) {
                $roleInfo = $this->roleModel->find($roleFilter);
                if ($roleInfo && in_array($roleInfo['roleuser'], $user['role_names'])) {
                    $users[] = $user;
                }
            } else {
                $users[] = $user;
            }
        }

        // Hitung pagination info
        $totalPages = ceil($total / $perPage);
        $startItem = (($page - 1) * $perPage) + 1;
        $endItem = min($page * $perPage, $total);

        $data = [
            'title' => 'Kelola Pengguna',
            'active_menu' => 'kelola-pengguna',
            'users' => $users,
            'roles' => $this->roleModel->findAll(),
            'search' => $search,
            'roleFilter' => $roleFilter,
            'perPage' => $perPage,
            'pagination' => [
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'perPage' => $perPage,
                'total' => $total,
                'startItem' => $startItem,
                'endItem' => $endItem
            ]
        ];

        return view('SuperAdmin/KelolaPengguna/index', $data);
    }

    /**
     * Detail petugas - list kegiatan yang pernah diikuti
     */
    public function detailPetugas($sobatId)
    {
        $sobatId = trim((string) $sobatId);

        // Get petugas info
        $petugas = $this->userModel->where('sobat_id', $sobatId)->first();

        if (!$petugas) {
            return redirect()->to('superadmin/kelola-pengguna')->with('error', 'Petugas tidak ditemukan');
        }

        // Get kegiatan sebagai PML
        $kegiatanPML = $this->db->table('pml')
            ->select('pml.id_pml as id, pml.target, pml.created_at,
                 mk.nama_kegiatan, mkd.nama_kegiatan_detail, 
                 mkdp.nama_kegiatan_detail_proses, mkdp.tanggal_mulai, mkdp.tanggal_selesai,
                 mkab.nama_kabupaten,
                 "PML" as role')
            ->join('kegiatan_wilayah kw', 'pml.id_kegiatan_wilayah = kw.id_kegiatan_wilayah')
            ->join('master_kabupaten mkab', 'kw.id_kabupaten = mkab.id_kabupaten')
            ->join('master_kegiatan_detail_proses mkdp', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses')
            ->join('master_kegiatan_detail mkd', 'mkdp.id_kegiatan_detail = mkd.id_kegiatan_detail')
            ->join('master_kegiatan mk', 'mkd.id_kegiatan = mk.id_kegiatan')
            ->where('pml.sobat_id', $sobatId)
            ->orderBy('mkdp.tanggal_mulai', 'DESC')
            ->get()
            ->getResultArray();

        // Get kegiatan sebagai PCL
        $kegiatanPCL = $this->db->table('pcl')
            ->select('pcl.id_pcl as id, pcl.target, pcl.created_at,
                 mk.nama_kegiatan, mkd.nama_kegiatan_detail, 
                 mkdp.nama_kegiatan_detail_proses, mkdp.tanggal_mulai, mkdp.tanggal_selesai,
                 mkab.nama_kabupaten,
                 u_pml.nama_user as nama_pml, "PCL" as role')
            ->join('pml', 'pcl.id_pml = pml.id_pml')
            ->join('sipantau_user u_pml', 'pml.sobat_id = u_pml.sobat_id')
            ->join('kegiatan_wilayah kw', 'pml.id_kegiatan_wilayah = kw.id_kegiatan_wilayah')
            ->join('master_kabupaten mkab', 'kw.id_kabupaten = mkab.id_kabupaten')
            ->join('master_kegiatan_detail_proses mkdp', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses')
            ->join('master_kegiatan_detail mkd', 'mkdp.id_kegiatan_detail = mkd.id_kegiatan_detail')
            ->join('master_kegiatan mk', 'mkd.id_kegiatan = mk.id_kegiatan')
            ->where('pcl.sobat_id', $sobatId)
            ->orderBy('mkdp.tanggal_mulai', 'DESC')
            ->get()
            ->getResultArray();

        // Merge dan sort semua kegiatan
        $kegiatanList = array_merge($kegiatanPML, $kegiatanPCL);
        usort($kegiatanList, function ($a, $b) {
            return strtotime($b['tanggal_mulai']) - strtotime($a['tanggal_mulai']);
        });

        $data = [
            'title' => 'Detail Petugas',
            'active_menu' => 'kelola-pengguna',
            'petugas' => $petugas,
            'kegiatanList' => $kegiatanList
        ];

        return view('SuperAdmin/KelolaPengguna/detail_petugas', $data);
    }

    /**
     * Detail PCL - laporan progress dan transaksi
     */
    public function detailPCL($idPCL)
    {
        $idPCL = (int) $idPCL;

        // Get PCL detail
        $pclDetail = $this->db->table('pcl')
            ->select('pcl.*, 
                 u.nama_user as nama_pcl, 
                 u.email, 
                 u.hp,
                 u.id_kabupaten,
                 u_pml.nama_user as nama_pml,
                 mk.nama_kabupaten,
                 mkdp.nama_kegiatan_detail_proses,
                 mkdp.tanggal_mulai,
                 mkdp.tanggal_selesai,
                 mkd.nama_kegiatan_detail')
            ->join('sipantau_user u', 'pcl.sobat_id = u.sobat_id')
            ->join('pml', 'pcl.id_pml = pml.id_pml')
            ->join('sipantau_user u_pml', 'pml.sobat_id = u_pml.sobat_id')
            ->join('kegiatan_wilayah kw', 'pml.id_kegiatan_wilayah = kw.id_kegiatan_wilayah')
            ->join('master_kabupaten mk', 'kw.id_kabupaten = mk.id_kabupaten')
            ->join('master_kegiatan_detail_proses mkdp', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses')
            ->join('master_kegiatan_detail mkd', 'mkdp.id_kegiatan_detail = mkd.id_kegiatan_detail')
            ->where('pcl.id_pcl', $idPCL)
            ->get()
            ->getRowArray();

        if (!$pclDetail) {
            return redirect()->to('superadmin/kelola-pengguna')->with('error', 'Data PCL tidak ditemukan');
        }

        // Load additional models
        $pantauProgressModel = new \App\Models\PantauProgressModel();
        $kurvaPetugasModel = new \App\Models\KurvaPetugasModel();

        // Get realisasi data
        $realisasi = $pantauProgressModel
            ->select('COALESCE(MAX(jumlah_realisasi_kumulatif), 0) as total_realisasi')
            ->where('id_pcl', $idPCL)
            ->first();

        $realisasiKumulatif = (int) ($realisasi['total_realisasi'] ?? 0);
        $target = (int) $pclDetail['target'];
        $persentase = $target > 0 ? round(($realisasiKumulatif / $target) * 100, 2) : 0;
        $selisih = $target - $realisasiKumulatif;

        // Get Kurva S data
        $kurvaData = $this->getKurvaDataPCL($idPCL, $pclDetail, $pantauProgressModel, $kurvaPetugasModel);

        $data = [
            'title' => 'Detail Laporan PCL',
            'active_menu' => 'kelola-pengguna',
            'pcl' => $pclDetail,
            'target' => $target,
            'realisasi' => $realisasiKumulatif,
            'persentase' => $persentase,
            'selisih' => $selisih,
            'kurvaData' => $kurvaData,
            'idPCL' => $idPCL
        ];

        return view('SuperAdmin/KelolaPengguna/detail_pcl', $data);
    }

    /**
     * Get Kurva S data untuk chart
     */
    private function getKurvaDataPCL($idPCL, $pclDetail, $pantauProgressModel, $kurvaPetugasModel)
    {
        // Get kurva target
        $kurvaTarget = $kurvaPetugasModel
            ->where('id_pcl', $idPCL)
            ->orderBy('tanggal_target', 'ASC')
            ->findAll();

        // Get realisasi harian
        $realisasiHarian = $this->db->query("
        SELECT 
            DATE(created_at) as tanggal,
            SUM(jumlah_realisasi_absolut) as realisasi_harian
        FROM pantau_progress
        WHERE id_pcl = ?
        GROUP BY DATE(created_at)
        ORDER BY DATE(created_at) ASC
    ", [$idPCL])->getResultArray();

        // Build realisasi lookup
        $realisasiLookup = [];
        foreach ($realisasiHarian as $item) {
            $realisasiLookup[$item['tanggal']] = (int) $item['realisasi_harian'];
        }

        // Format data untuk chart
        $labels = [];
        $targetData = [];
        $realisasiData = [];
        $realisasiKumulatif = 0;

        foreach ($kurvaTarget as $item) {
            $tanggal = $item['tanggal_target'];
            $labels[] = date('d M', strtotime($tanggal));
            $targetData[] = (int) $item['target_kumulatif_absolut'];

            if (isset($realisasiLookup[$tanggal])) {
                $realisasiKumulatif += $realisasiLookup[$tanggal];
            }
            $realisasiData[] = $realisasiKumulatif;
        }

        return [
            'labels' => $labels,
            'target' => $targetData,
            'realisasi' => $realisasiData,
            'config' => [
                'tanggal_mulai' => date('d M', strtotime($pclDetail['tanggal_mulai'])),
                'tanggal_selesai' => date('d M', strtotime($pclDetail['tanggal_selesai']))
            ]
        ];
    }

    /**
     * Detail PML - progress dari PCL yang dipegang
     */
    public function detailPML($idPML)
    {
        $idPML = (int) $idPML;

        // Get PML detail
        $pmlModel = new \App\Models\PMLModel();
        $pmlDetail = $pmlModel->getPMLWithDetails($idPML);

        if (!$pmlDetail) {
            return redirect()->to('superadmin/kelola-pengguna')->with('error', 'Data PML tidak ditemukan');
        }

        // Get daftar PCL beserta progress
        $pantauProgressModel = new \App\Models\PantauProgressModel();
        $pclModel = new \App\Models\PCLModel();

        $pclList = $this->db->table('pcl')
            ->select('pcl.id_pcl, pcl.target, pcl.sobat_id,
                 u.nama_user as nama_pcl, u.email, u.hp')
            ->join('sipantau_user u', 'pcl.sobat_id = u.sobat_id')
            ->where('pcl.id_pml', $idPML)
            ->orderBy('u.nama_user', 'ASC')
            ->get()
            ->getResultArray();

        // Enrich dengan data realisasi
        foreach ($pclList as &$pcl) {
            $realisasi = $pantauProgressModel
                ->select('COALESCE(MAX(jumlah_realisasi_kumulatif), 0) as realisasi_kumulatif')
                ->where('id_pcl', $pcl['id_pcl'])
                ->first();

            $pcl['realisasi_kumulatif'] = $realisasi['realisasi_kumulatif'] ?? 0;
        }

        $data = [
            'title' => 'Detail PML',
            'active_menu' => 'kelola-pengguna',
            'pml' => $pmlDetail,
            'pclList' => $pclList
        ];

        return view('SuperAdmin/KelolaPengguna/detail_pml', $data);
    }

    /**
     * Get Pantau Progress via AJAX
     */
    public function getPantauProgress()
    {


        $idPCL = (int) $this->request->getGet('id_pcl');
        $page = $this->request->getGet('page') ?? 1;
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        $pantauProgressModel = new \App\Models\PantauProgressModel();

        $total = $pantauProgressModel->where('id_pcl', $idPCL)->countAllResults(false);
        $data = $pantauProgressModel
            ->where('id_pcl', $idPCL)
            ->orderBy('created_at', 'DESC')
            ->limit($perPage, $offset)
            ->findAll();

        return $this->response->setJSON([
            'success' => true,
            'data' => $data,
            'pagination' => [
                'total' => $total,
                'perPage' => $perPage,
                'currentPage' => $page,
                'totalPages' => ceil($total / $perPage)
            ]
        ]);
    }

    /**
     * Get Laporan Transaksi via AJAX
     */
    public function getLaporanTransaksi()
    {
        $idPCL = (int) $this->request->getGet('id_pcl');
        $page = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        $builder = $this->db->table('sipantau_transaksi st');

        $totalBuilder = clone $builder;
        $total = $totalBuilder->where('st.id_pcl', $idPCL)->countAllResults();

        if ($total == 0) {
            return $this->response->setJSON([
                'success' => true,
                'data' => [],
                'pagination' => [
                    'total' => 0,
                    'perPage' => $perPage,
                    'currentPage' => $page,
                    'totalPages' => 0
                ]
            ]);
        }

        $data = $builder
            ->select('st.id_sipantau_transaksi,
                 st.resume,
                 st.latitude,
                 st.longitude,
                 st.imagepath,
                 st.created_at,
                 COALESCE(mk.nama_kecamatan, "-") as nama_kecamatan,
                 COALESCE(md.nama_desa, "-") as nama_desa')
            ->join('master_kecamatan mk', 'st.id_kecamatan = mk.id_kecamatan', 'left')
            ->join('master_desa md', 'st.id_desa = md.id_desa', 'left')
            ->where('st.id_pcl', $idPCL)
            ->orderBy('st.created_at', 'DESC')
            ->limit($perPage, $offset)
            ->get()
            ->getResultArray();

        return $this->response->setJSON([
            'success' => true,
            'data' => $data,
            'pagination' => [
                'total' => $total,
                'perPage' => $perPage,
                'currentPage' => $page,
                'totalPages' => ceil($total / $perPage)
            ]
        ]);
    }

    // Get user role names termasuk role tambahan dari tabel admin
    private function getUserRoleNames($sobatId, $roleJson)
    {
        $sobatId = trim((string) $sobatId);
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
                    // Role Pemantau Provinsi (role asli)
                    $roleNames[] = 'Pemantau Provinsi';
                } elseif ($roleId == 3) {
                    // Role Pemantau Kabupaten (role asli)
                    $roleNames[] = 'Pemantau Kabupaten';
                } else {
                    // Role lain
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

    public function create()
    {
        $data = [
            'title' => 'Tambah Pengguna',
            'active_menu' => 'kelola-pengguna',
            'roles' => $this->roleModel->findAll(),
            'kabupaten' => $this->getKabupatenList()
        ];

        return view('SuperAdmin/KelolaPengguna/create', $data);
    }

    public function store()
    {
        $validation = \Config\Services::validation();

        $rules = [
            'sobat_id' => 'required|numeric|is_unique[sipantau_user.sobat_id]',
            'nama_user' => 'required|min_length[3]|max_length[100]',
            'email' => 'required|valid_email|is_unique[sipantau_user.email]',
            'hp' => 'required|numeric|min_length[10]|max_length[20]',
            'id_kabupaten' => 'required|numeric',
            'roles' => 'required',
            'is_pegawai' => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $sobatId = trim((string) $this->request->getPost('sobat_id'));
        $roleIds = $this->request->getPost('roles');

        if (is_string($roleIds)) {
            $roleIds = explode(',', $roleIds);
        }

        $roleIds = array_map('intval', $roleIds);

        $userData = [
            'sobat_id' => $sobatId,
            'nama_user' => $this->request->getPost('nama_user'),
            'email' => $this->request->getPost('email'),
            'hp' => $this->request->getPost('hp'),
            'id_kabupaten' => $this->request->getPost('id_kabupaten'),
            'role' => json_encode($roleIds), // Sesuai nama kolom
            'password' => (string) $sobatId, // Password = Sobat ID,
            'is_pegawai' => $this->request->getPost('is_pegawai'),
            'is_active' => 1
        ];

        $this->userModel->insert($userData);

        return redirect()->to(base_url('superadmin/kelola-pengguna'))
            ->with('success', 'Data pengguna berhasil ditambahkan');
    }

    public function edit($id)
    {
        $id = trim((string) $id);
        $user = $this->userModel->getUserWithRoles($id);

        if (!$user) {
            return redirect()->to(base_url('superadmin/kelola-pengguna'))
                ->with('error', 'Data pengguna tidak ditemukan');
        }

        $data = [
            'title' => 'Edit Pengguna',
            'active_menu' => 'kelola-pengguna',
            'user' => $user,
            'roles' => $this->roleModel->findAll(),
            'kabupaten' => $this->getKabupatenList()
        ];

        return view('SuperAdmin/KelolaPengguna/edit', $data);
    }

    public function update($id)
    {
        $id = trim((string) $id);
        $rules = [
            'nama_user' => 'required|min_length[3]|max_length[100]',
            'email' => "required|valid_email|is_unique[sipantau_user.email,sobat_id,{$id}]",
            'hp' => 'required|numeric|min_length[10]|max_length[20]',
            'id_kabupaten' => 'required|numeric',
            'roles' => 'required',
            'is_pegawai' => 'required',
        ];

        $password = $this->request->getPost('password');
        if (!empty($password)) {
            $rules['password'] = 'min_length[8]';
            $rules['password_confirm'] = 'matches[password]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $roleIds = $this->request->getPost('roles');

        if (is_string($roleIds)) {
            $roleIds = explode(',', $roleIds);
        }

        $roleIds = array_map('intval', $roleIds);

        $userData = [
            'nama_user' => $this->request->getPost('nama_user'),
            'email' => $this->request->getPost('email'),
            'hp' => $this->request->getPost('hp'),
            'id_kabupaten' => $this->request->getPost('id_kabupaten'),
            'role' => json_encode($roleIds),
            'is_pegawai' => $this->request->getPost('is_pegawai'),
        ];

        if (!empty($password)) {
            $userData['password'] = $password;
        }

        $this->userModel->where('sobat_id', $id)->set($userData)->update();

        return redirect()->to(base_url('superadmin/kelola-pengguna'))
            ->with('success', 'Data pengguna berhasil diperbarui');
    }

    public function delete($id)
    {
        log_message('debug', '=== START DELETE USER ===');
        log_message('debug', "Raw ID received: '{$id}' (type: " . gettype($id) . ", length: " . strlen($id) . ")");

        // JANGAN konversi ke integer karena bisa overflow!
        // Gunakan string untuk BIGINT
        $sobatId = trim((string) $id);

        log_message('debug', "String ID: '{$sobatId}' (type: " . gettype($sobatId) . ", length: " . strlen($sobatId) . ")");
        log_message('debug', "Attempting to find user with sobat_id: {$sobatId}");

        // Cari user dengan where clause menggunakan string
        $user = $this->userModel->where('sobat_id', $sobatId)->first();

        log_message('debug', "Query executed: SELECT * FROM sipantau_user WHERE sobat_id = '{$sobatId}'");
        log_message('debug', "User found: " . ($user ? 'YES' : 'NO'));

        if ($user) {
            log_message('debug', "User details: " . json_encode([
                'sobat_id' => $user['sobat_id'],
                'nama_user' => $user['nama_user'],
                'email' => $user['email'],
                'sobat_id_type' => gettype($user['sobat_id'])
            ]));
        } else {
            log_message('error', "Delete user failed: User with sobat_id '{$sobatId}' not found");

            // Cek apakah user ada dengan query langsung
            $checkQuery = $this->db->query("SELECT sobat_id, nama_user FROM sipantau_user WHERE sobat_id = ?", [$sobatId]);
            $checkResult = $checkQuery->getRowArray();
            log_message('debug', "Direct query check result: " . json_encode($checkResult));

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Data pengguna tidak ditemukan'
            ]);
        }

        log_message('info', "Deleting user: sobat_id = {$sobatId}, nama = {$user['nama_user']}");

        // Hapus dengan where clause menggunakan string
        try {
            $deleted = $this->userModel->where('sobat_id', $sobatId)->delete();
            log_message('debug', "Delete result: " . ($deleted ? 'SUCCESS' : 'FAILED'));
            log_message('debug', "Affected rows: " . $this->db->affectedRows());
        } catch (\Exception $e) {
            log_message('critical', "Delete exception: " . $e->getMessage());
            log_message('critical', "Stack trace: " . $e->getTraceAsString());

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error saat menghapus: ' . $e->getMessage()
            ]);
        }

        if (!$deleted) {
            log_message('error', "Delete user failed: Unable to delete sobat_id '{$sobatId}'");
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal menghapus data pengguna'
            ]);
        }

        log_message('info', "User deleted successfully: sobat_id = {$sobatId}");
        log_message('debug', '=== END DELETE USER ===');

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Data pengguna berhasil dihapus'
        ]);
    }

    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();

        // === SHEET 1: Template Import ===
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Template Import');

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]]
        ];

        $headers = [
            'A1' => 'Sobat ID',
            'B1' => 'Nama Lengkap',
            'C1' => 'Email',
            'D1' => 'No HP',
            'E1' => 'ID Kabupaten/Kota',
            'F1' => 'Pegawai/Mitra (1=Pegawai, 0=Mitra)',
            'G1' => 'Role (pisahkan dengan koma)'
        ];
        foreach ($headers as $cell => $value) {
            $sheet1->setCellValue($cell, $value);
        }

        $sheet1->getStyle('A1:G1')->applyFromArray($headerStyle);

        foreach (['A' => 15, 'B' => 25, 'C' => 30, 'D' => 15, 'E' => 20, 'F' => 35, 'G' => 35] as $col => $width) {
            $sheet1->getColumnDimension($col)->setWidth($width);
        }

        // Ubah kolom sebagai teks
        $sheet1->getStyle('A:G')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);

        // Contoh data
        $sheet1->setCellValue('A2', '3201010101');
        $sheet1->setCellValue('B2', 'Ahmad Hidayat');
        $sheet1->setCellValue('C2', 'ahmad@bps.go.id');
        $sheet1->setCellValue('D2', '081234567890');
        $sheet1->setCellValue('E2', '1401');     // contoh ID kabupaten
        $sheet1->setCellValue('F2', '1');        // 1=Pegawai, 0=Mitra
        $sheet1->setCellValue('G2', '2,3');      // contoh ID role
        // Catatan
        $sheet1->setCellValue('H2', '// Contoh');
        $sheet1->setCellValue('H3', '// Catatan: Pisahkan banyak role dengan koma, tanpa spasi');

        $sheet1->getStyle('A1:G2')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        // === SHEET 2: Legenda ===
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Legenda');

        $sheet2->setCellValue('A1', 'Panduan Pengisian Template');
        $sheet2->mergeCells('A1:D1');
        $sheet2->getStyle('A1')->getFont()->setBold(true)->setSize(13);

        $sheet2->setCellValue('A3', '1. Gunakan ID Kabupaten/Kota sesuai tabel di bawah.');
        $sheet2->setCellValue('A4', '2. Gunakan ID Role sesuai tabel role di bawah.');
        $sheet2->setCellValue('A5', '3. Pisahkan banyak role dengan koma (contoh: 2,3).');
        $sheet2->setCellValue('A6', '4. Kolom Pegawai/Mitra: isi 1 untuk Pegawai, 0 untuk Mitra.');

        // Daftar Kabupaten
        $sheet2->setCellValue('A8', 'Daftar Kabupaten/Kota');
        $sheet2->getStyle('A8')->getFont()->setBold(true);
        $sheet2->setCellValue('A9', 'ID Kabupaten');
        $sheet2->setCellValue('B9', 'Nama Kabupaten');
        $sheet2->getStyle('A9:B9')->applyFromArray($headerStyle);

        $kabupatenList = $this->getKabupatenList();
        $rowKab = 10;
        foreach ($kabupatenList as $kab) {
            $sheet2->setCellValue("A{$rowKab}", $kab['id_kabupaten']);
            $sheet2->setCellValue("B{$rowKab}", $kab['nama_kabupaten']);
            $rowKab++;
        }

        // Daftar Role
        $rowRoleHeader = $rowKab + 2;
        $sheet2->setCellValue("A{$rowRoleHeader}", 'Daftar Role');
        $sheet2->getStyle("A{$rowRoleHeader}")->getFont()->setBold(true);

        $sheet2->setCellValue("A" . ($rowRoleHeader + 1), 'ID Role');
        $sheet2->setCellValue("B" . ($rowRoleHeader + 1), 'Nama Role');
        $sheet2->getStyle("A" . ($rowRoleHeader + 1) . ":B" . ($rowRoleHeader + 1))->applyFromArray($headerStyle);

        $roles = $this->roleModel->findAll();
        $rowRole = $rowRoleHeader + 2;
        foreach ($roles as $role) {
            $sheet2->setCellValue("A{$rowRole}", $role['id_roleuser']);
            $sheet2->setCellValue("B{$rowRole}", $role['roleuser']);
            $rowRole++;
        }

        foreach (['A' => 15, 'B' => 30, 'C' => 30, 'D' => 20] as $col => $width) {
            $sheet2->getColumnDimension($col)->setWidth($width);
        }

        $filename = 'Template_Import_Pengguna_' . date('YmdHis') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }



    public function import()
    {
        $file = $this->request->getFile('file');
        if (!$file || !$file->isValid()) {
            return redirect()->back()->with('error', 'File tidak valid');
        }

        $extension = $file->getClientExtension();
        if (!in_array($extension, ['xlsx', 'xls'])) {
            return redirect()->back()->with('error', 'Format file harus Excel (.xlsx atau .xls)');
        }

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getTempName());
        $sheet = $spreadsheet->getActiveSheet();
        $data = $sheet->toArray();

        // Buang baris header
        array_shift($data);

        $imported = 0;
        $skipped = 0;
        $errors = [];

        $validKabupaten = $this->getKabupatenMap();
        $validRoleIds = $this->getValidRoleIds();

        $this->db->transStart();

        foreach ($data as $index => $row) {
            if (empty(array_filter($row)))
                continue;

            $rowNumber = $index + 2;

            // Kolom wajib: A..G (index 0..6)
            if (empty($row[0]) || empty($row[1]) || empty($row[2]) || empty($row[3]) || empty($row[4]) || $row[5] === null || empty($row[6])) {
                $errors[] = "Baris {$rowNumber}: Data tidak lengkap";
                $skipped++;
                continue;
            }

            $idKabupaten = trim($row[4]);

            // Validasi kabupaten
            if (!isset($validKabupaten[$idKabupaten])) {
                $errors[] = "Baris {$rowNumber}: ID Kabupaten '{$idKabupaten}' tidak ditemukan";
                $skipped++;
                continue;
            }

            // Ambil & validasi is_pegawai (F)
            $isPegawai = trim((string) $row[5]);
            if (!in_array($isPegawai, ['0', '1'], true)) {
                $errors[] = "Baris {$rowNumber}: Nilai Pegawai/Mitra harus 1 (Pegawai) atau 0 (Mitra)";
                $skipped++;
                continue;
            }

            //  Ambil roles dari kolom G (bukan F)
            $roleInput = str_replace(' ', '', trim($row[6]));
            $roleIds = array_filter(array_map('intval', explode(',', $roleInput)), static fn($v) => $v !== 0);

            // Filter role valid
            $validRoleIdsForUser = array_values(array_filter($roleIds, function ($id) use ($validRoleIds) {
                return in_array($id, $validRoleIds, true);
            }));

            if (empty($validRoleIdsForUser)) {
                $errors[] = "Baris {$rowNumber}: Role ID tidak valid: {$row[6]}";
                $skipped++;
                continue;
            }

            // Cek duplikat
            if ($this->userModel->where('email', $row[2])->first()) {
                $errors[] = "Baris {$rowNumber}: Email {$row[2]} sudah terdaftar";
                $skipped++;
                continue;
            }

            if ($this->userModel->where('sobat_id', $row[0])->first()) {
                $errors[] = "Baris {$rowNumber}: Sobat ID {$row[0]} sudah terdaftar";
                $skipped++;
                continue;
            }

            // Insert user
            $userData = [
                'sobat_id' => trim($row[0]),
                'nama_user' => trim($row[1]),
                'email' => trim($row[2]),
                'hp' => trim($row[3]),
                'id_kabupaten' => $idKabupaten,
                'role' => json_encode($validRoleIdsForUser),
                'password' => trim($row[0]),
                'is_active' => 1,
                'is_pegawai' => (int) $isPegawai,
            ];

            $this->userModel->insert($userData);
            $imported++;
        }

        $this->db->transComplete();

        $message = "Import selesai! Berhasil: {$imported} data, Dilewati: {$skipped} data";

        if (!empty($errors)) {
            $errorMessage = implode("\n", array_slice($errors, 0, 10));
            if (count($errors) > 10) {
                $errorMessage .= "\n... dan " . (count($errors) - 10) . " error lainnya";
            }
            session()->setFlashdata('import_errors', $errorMessage);
        }

        return redirect()->to(base_url('superadmin/kelola-pengguna'))
            ->with($imported > 0 ? 'success' : 'warning', $message);
    }



    public function export()
    {
        // Get all users dengan kabupaten
        $users = $this->db->table('sipantau_user u')
            ->select('u.*, mk.nama_kabupaten')
            ->join('master_kabupaten mk', 'u.id_kabupaten = mk.id_kabupaten', 'left')
            ->orderBy('u.nama_user', 'ASC')
            ->get()
            ->getResultArray();

        // Enrich dengan role names
        foreach ($users as &$user) {
            $roleNames = $this->getUserRoleNames($user['sobat_id'], $user['role']);
            $user['roles_display'] = implode(', ', $roleNames);
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]]
        ];

        $headers = [
            'No',
            'Sobat ID',
            'Nama Lengkap',
            'Email',
            'No HP',
            'Kabupaten/Kota',
            'Roles',
            'Pegawai/Mitra',
            'Status',
            'Tanggal Dibuat'
        ];
        $column = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($column . '1', $header);
            $column++;
        }

        $sheet->getStyle('A1:J1')->applyFromArray($headerStyle);

        foreach ([
            'A' => 5,
            'B' => 15,
            'C' => 25,
            'D' => 30,
            'E' => 15,
            'F' => 20,
            'G' => 35,
            'H' => 18,
            'I' => 10,
            'J' => 20
        ] as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        $row = 2;
        $no = 1;
        foreach ($users as $user) {
            $sheet->setCellValue('A' . $row, $no);
            $sheet->setCellValue('B' . $row, $user['sobat_id']);
            $sheet->setCellValue('C' . $row, $user['nama_user']);
            $sheet->setCellValue('D' . $row, $user['email']);
            $sheet->setCellValue('E' . $row, $user['hp']);
            $sheet->setCellValue('F' . $row, $user['nama_kabupaten'] ?? '-');
            $sheet->setCellValue('G' . $row, $user['roles_display'] ?? '-');
            $sheet->setCellValue('H' . $row, ((string) $user['is_pegawai'] === '1') ? 'Pegawai' : 'Mitra');
            $sheet->setCellValue('I' . $row, $user['is_active'] ? 'Aktif' : 'Nonaktif');
            $sheet->setCellValue('J' . $row, $user['created_at']);

            $row++;
            $no++;
        }

        $filename = 'Data_Pengguna_' . date('YmdHis') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }


    private function getKabupatenList()
    {
        return $this->db->table('master_kabupaten')
            ->select('id_kabupaten, nama_kabupaten')
            ->orderBy('id_kabupaten', 'ASC')
            ->get()
            ->getResultArray();
    }

    private function getKabupatenMap()
    {
        $kabupaten = $this->getKabupatenList();
        $map = [];
        foreach ($kabupaten as $kab) {
            $map[$kab['id_kabupaten']] = $kab['nama_kabupaten'];
        }
        return $map;
    }

    private function getValidRoleIds()
    {
        $roles = $this->roleModel->findAll();

        return array_map('intval', array_column($roles, 'id_roleuser'));
    }

    public function toggleStatus($id)
    {
        $id = trim((string) $id);

        $user = $this->userModel->find($id);

        if (!$user) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Data pengguna tidak ditemukan'
            ]);
        }

        $newStatus = $user['is_active'] ? 0 : 1;
        $this->userModel->update($id, ['is_active' => $newStatus]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Status pengguna berhasil diubah',
            'status' => $newStatus
        ]);
    }
}
