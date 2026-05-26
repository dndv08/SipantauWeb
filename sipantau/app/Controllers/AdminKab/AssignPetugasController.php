<?php

namespace App\Controllers\AdminKab;

use App\Controllers\BaseController;
use App\Models\AdminSurveiKabupatenModel;
use App\Models\MasterKegiatanWilayahModel;
use App\Models\KegiatanWilayahAdminModel;
use App\Models\PMLModel;
use App\Models\PCLModel;
use App\Models\UserModel;
use App\Models\KurvaPetugasModel;
use App\Models\MasterKegiatanDetailProsesModel;
use DateInterval;
use DatePeriod;
use DateTime;

class AssignPetugasController extends BaseController
{
    protected $adminKabModel;
    protected $kegiatanWilayahModel;
    protected $kegiatanWilayahAdminModel;
    protected $pmlModel;
    protected $pclModel;
    protected $userModel;
    protected $kurvaModel;
    protected $prosesModel;

    public function __construct()
    {
        $this->adminKabModel = new AdminSurveiKabupatenModel();
        $this->kegiatanWilayahModel = new MasterKegiatanWilayahModel();
        $this->kegiatanWilayahAdminModel = new KegiatanWilayahAdminModel();
        $this->pmlModel = new PMLModel();
        $this->pclModel = new PCLModel();
        $this->userModel = new UserModel();
        $this->kurvaModel = new KurvaPetugasModel();
        $this->prosesModel = new MasterKegiatanDetailProsesModel();
    }

    //  Halaman Index - Daftar Assignment PML
    //  Hanya tampilkan kegiatan yang di-assign ke admin yang login
    public function index()
    {
        $sobatId = session()->get('sobat_id');

        if (!$sobatId) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu');
        }

        $admin = $this->adminKabModel->db->table('admin_survei_kabupaten ask')
            ->select('ask.*, u.nama_user, u.id_kabupaten, k.nama_kabupaten')
            ->join('sipantau_user u', 'ask.sobat_id = u.sobat_id')
            ->join('master_kabupaten k', 'u.id_kabupaten = k.id_kabupaten')
            ->where('ask.sobat_id', $sobatId)
            ->get()
            ->getRowArray();

        if (!$admin) {
            return redirect()->to('/')->with('error', 'Anda tidak memiliki akses sebagai admin kabupaten');
        }

        $idKabupaten = $admin['id_kabupaten'];
        $idAdminKabupaten = $admin['id_admin_kabupaten'];
        $idKegiatanWilayah = $this->request->getGet('kegiatan');

        // Ambil perPage dari GET, default 10
        $perPage = $this->request->getGet('perPage') ?? 10;

        // Validasi perPage agar hanya nilai yang diizinkan
        $allowedPerPage = [5, 10, 25, 50, 100];
        if (!in_array((int) $perPage, $allowedPerPage)) {
            $perPage = 10;
        }

        // Ambil PML dengan pagination hanya untuk kegiatan yang di-assign ke admin ini
        $dataPML = $this->pmlModel->getPMLByKabupatenAndAdminPaginated($idKabupaten, $idAdminKabupaten, $idKegiatanWilayah, $perPage);

        // Cek data transaksi untuk setiap PML (untuk enable/disable tombol di view)
        foreach ($dataPML as &$pml) {
            $txSummary = $this->pmlModel->getTransactionSummary($pml['id_pml']);
            $pml['has_transaction_data'] = $txSummary['has_data'];
            $pml['tx_total_progress']    = $txSummary['total_progress'];
            $pml['tx_total_transaksi']   = $txSummary['total_transaksi'];
            $pml['tx_total_realisasi']   = $txSummary['total_realisasi'];
        }
        unset($pml);

        // Ambil kegiatan list hanya yang di-assign ke admin ini
        $kegiatanList = $this->kegiatanWilayahModel->getByKabupatenAndAdmin($idKabupaten, $idAdminKabupaten);

        $data = [
            'title' => 'Assign Petugas Survei',
            'active_menu' => 'assign-admin-kab',
            'admin' => $admin,
            'dataPML' => $dataPML,
            'kegiatanList' => $kegiatanList,
            'selectedKegiatan' => $idKegiatanWilayah,
            'perPage' => $perPage,
            'pager' => $this->pmlModel->pager,
        ];

        return view('AdminSurveiKab/AssignPetugasSurvei/index', $data);
    }

    //  Halaman Create - Form Assign PML dan PCL
    public function create()
    {
        $sobatId = session()->get('sobat_id');

        if (!$sobatId) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu');
        }

        $admin = $this->adminKabModel->db->table('admin_survei_kabupaten ask')
            ->select('ask.*, u.nama_user, u.id_kabupaten, k.nama_kabupaten')
            ->join('sipantau_user u', 'ask.sobat_id = u.sobat_id')
            ->join('master_kabupaten k', 'u.id_kabupaten = k.id_kabupaten')
            ->where('ask.sobat_id', $sobatId)
            ->get()
            ->getRowArray();

        if (!$admin) {
            return redirect()->to('/')->with('error', 'Anda tidak memiliki akses sebagai admin kabupaten');
        }

        $idKabupaten = $admin['id_kabupaten'];
        $idAdminKabupaten = $admin['id_admin_kabupaten'];

        // Hanya tampilkan kegiatan yang di-assign ke admin ini
        $kegiatanList = $this->kegiatanWilayahModel->getByKabupatenAndAdmin($idKabupaten, $idAdminKabupaten);

        $data = [
            'title' => 'Assign Petugas Survei',
            'active_menu' => 'assign-admin-kab',
            'admin' => $admin,
            'kegiatanList' => $kegiatanList
        ];

        return view('AdminSurveiKab/AssignPetugasSurvei/create', $data);
    }

    public function edit($id_pml)
    {
        $sobatId = session()->get('sobat_id');

        if (!$sobatId) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu');
        }

        // Ambil data admin kabupaten
        $admin = $this->adminKabModel->join('sipantau_user u', 'admin_survei_kabupaten.sobat_id = u.sobat_id')
            ->join('master_kabupaten k', 'u.id_kabupaten = k.id_kabupaten')
            ->select('admin_survei_kabupaten.*, u.id_kabupaten, u.nama_user, k.nama_kabupaten')
            ->where('admin_survei_kabupaten.sobat_id', $sobatId)
            ->first();

        if (!$admin) {
            return redirect()->to('/')->with('error', 'Anda tidak memiliki akses sebagai admin kabupaten');
        }

        $idKabupaten = $admin['id_kabupaten'];
        $idAdminKabupaten = $admin['id_admin_kabupaten'];

        // Ambil data PML beserta PCL-nya
        $pml = $this->pmlModel->getPMLWithDetails($id_pml);
        if (!$pml || $pml['id_kabupaten'] != $idKabupaten) {
            return redirect()->back()->with('error', 'Data PML tidak ditemukan atau akses ditolak');
        }

        // Cek apakah kegiatan ini di-assign ke admin yang login
        $isAssigned = $this->kegiatanWilayahAdminModel->isAssigned($idAdminKabupaten, $pml['id_kegiatan_wilayah']);
        if (!$isAssigned) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke kegiatan ini');
        }

        // ===== GUARD: Cek data transaksi sebelum izinkan edit =====
        $txSummary = $this->pmlModel->getTransactionSummary($id_pml);
        if ($txSummary['has_data']) {
            $detailPCL = '';
            foreach ($txSummary['pcl_with_data'] as $pcl) {
                $detailPCL .= "• {$pcl['nama_user']} — {$pcl['jumlah_progress']} progress, {$pcl['jumlah_transaksi']} transaksi, realisasi: {$pcl['realisasi_kumulatif']}\n";
            }

            $message = "Tidak dapat mengedit assignment PML ini karena sudah memiliki data transaksi.\n"
                . "Detail: {$txSummary['total_progress']} data progress, "
                . "{$txSummary['total_transaksi']} data transaksi, "
                . "total realisasi: {$txSummary['total_realisasi']}.\n"
                . "PCL yang memiliki data:\n{$detailPCL}"
                . "Hapus data transaksi terlebih dahulu sebelum mengedit assignment.";

            return redirect()->back()->with('error', $message);
        }
        // ===== END GUARD =====

        // Ambil PCL terkait
        $pclsRaw = $this->pclModel->getPCLByPML($id_pml);
        $pcls = [];
        foreach ($pclsRaw as $p) {
            $user = $this->userModel->find($p['sobat_id']);
            $p['nama_user'] = $user['nama_user'] ?? 'Unknown';
            $pcls[] = $p;
        }

        // Kegiatan yang tersedia untuk admin ini
        $kegiatanList = $this->kegiatanWilayahModel->getByKabupatenAndAdmin($idKabupaten, $idAdminKabupaten);

        // PCL yang bisa dipilih (exclude PML dan admin yang terlibat di kegiatan yang sama)
        $availablePCL = $this->pclModel->getAvailablePCLForKegiatan($idKabupaten, $pml['id_kegiatan_wilayah'], $id_pml);

        return view('AdminSurveiKab/AssignPetugasSurvei/edit', [
            'title' => 'Edit Assign Petugas Survei',
            'active_menu' => 'assign-admin-kab',
            'pml' => $pml,
            'pcls' => $pcls,
            'kegiatanList' => $kegiatanList,
            'availablePCL' => $availablePCL,
            'isEdit' => true
        ]);
    }

    public function update($id_pml)
    {
        $sobatId = session()->get('sobat_id');
        if (!$sobatId) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu');
        }

        $admin = $this->adminKabModel->join('sipantau_user u', 'admin_survei_kabupaten.sobat_id = u.sobat_id')
            ->join('master_kabupaten k', 'u.id_kabupaten = k.id_kabupaten')
            ->select('admin_survei_kabupaten.*, u.id_kabupaten, u.nama_user, k.nama_kabupaten')
            ->where('admin_survei_kabupaten.sobat_id', $sobatId)
            ->first();

        if (!$admin) {
            return redirect()->to('/')->with('error', 'Anda tidak memiliki akses sebagai admin kabupaten');
        }

        $idKabupaten = $admin['id_kabupaten'];
        $idAdminKabupaten = $admin['id_admin_kabupaten'];

        // Ambil PML beserta id_kabupaten
        $pml = $this->pmlModel->db->table('pml p')
            ->select('p.*, kw.id_kabupaten, kw.id_kegiatan_wilayah')
            ->join('kegiatan_wilayah kw', 'kw.id_kegiatan_wilayah = p.id_kegiatan_wilayah')
            ->where('p.id_pml', $id_pml)
            ->get()
            ->getRowArray();

        if (!$pml || $pml['id_kabupaten'] != $idKabupaten) {
            return redirect()->back()->with('error', 'Data PML tidak ditemukan atau akses ditolak');
        }

        // Cek apakah kegiatan ini di-assign ke admin yang login
        $isAssigned = $this->kegiatanWilayahAdminModel->isAssigned($idAdminKabupaten, $pml['id_kegiatan_wilayah']);
        if (!$isAssigned) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke kegiatan ini');
        }

        // ===== GUARD: Cek data transaksi sebelum izinkan update =====
        $txSummary = $this->pmlModel->getTransactionSummary($id_pml);
        if ($txSummary['has_data']) {
            $detailPCL = '';
            foreach ($txSummary['pcl_with_data'] as $pcl) {
                $detailPCL .= "• {$pcl['nama_user']} — {$pcl['jumlah_progress']} progress, {$pcl['jumlah_transaksi']} transaksi, realisasi: {$pcl['realisasi_kumulatif']}\n";
            }

            $message = "Tidak dapat mengupdate assignment PML ini karena sudah memiliki data transaksi.\n"
                . "Detail: {$txSummary['total_progress']} data progress, "
                . "{$txSummary['total_transaksi']} data transaksi, "
                . "total realisasi: {$txSummary['total_realisasi']}.\n"
                . "PCL yang memiliki data:\n{$detailPCL}"
                . "Hapus data transaksi terlebih dahulu sebelum mengupdate assignment.";

            return redirect()->back()->with('error', $message);
        }
        // ===== END GUARD =====

        // Validasi input
        $pmlTarget = (int) $this->request->getPost('pml_target');
        $kegiatanSurvei = $this->request->getPost('kegiatan_survei');
        $pclData = $this->request->getPost('pcl') ?? [];

        // Validasi kegiatan survei juga harus di-assign ke admin ini
        $isKegiatanAssigned = $this->kegiatanWilayahAdminModel->isAssigned($idAdminKabupaten, $kegiatanSurvei);
        if (!$isKegiatanAssigned) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke kegiatan yang dipilih');
        }

        // Update PML
        $this->pmlModel->update($id_pml, [
            'target' => $pmlTarget,
            'id_kegiatan_wilayah' => $kegiatanSurvei
        ]);

        // Hapus PCL lama beserta Kurva
        $oldPCLs = $this->pclModel->where('id_pml', $id_pml)->findAll();
        foreach ($oldPCLs as $pcl) {
            $this->kurvaModel->where('id_pcl', $pcl['id_pcl'])->delete();
        }
        $this->pclModel->where('id_pml', $id_pml)->delete();

        // Insert PCL baru & generate Kurva
        foreach ($pclData as $p) {
            if (!empty($p['sobat_id']) && !empty($p['target'])) {
                $idPCL = $this->pclModel->insert([
                    'id_pml' => $id_pml,
                    'sobat_id' => $p['sobat_id'],
                    'target' => $p['target']
                ], true);

                // Ambil data proses
                $kegiatan = $this->kegiatanWilayahModel->find($kegiatanSurvei);
                $detailProses = $this->prosesModel->find($kegiatan['id_kegiatan_detail_proses']);

                $this->generateKurvaPetugas(
                    $idPCL,
                    $p['target'],
                    $detailProses['persentase_target_awal'],
                    $detailProses['tanggal_mulai'],
                    $detailProses['tanggal_selesai_target'],
                    $detailProses['tanggal_selesai']
                );
            }
        }

        return redirect()->to('adminsurvei-kab/assign-petugas/detail/' . $id_pml)
            ->with('success', 'Data assignment berhasil diperbarui dan kurva petugas diperbarui.');
    }

    //  AJAX: Get Sisa Target Kegiatan Wilayah
    //  Menghitung sisa target dari kegiatan wilayah dikurangi total target PML yang sudah di-assign
    public function getSisaTargetKegiatanWilayah()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'error' => 'Invalid request']);
        }

        $sobatId = session()->get('sobat_id');
        $admin = $this->adminKabModel->where('sobat_id', $sobatId)->first();

        if (!$admin) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Unauthorized',
                'csrf_hash' => csrf_hash()
            ]);
        }

        $idKegiatanWilayah = $this->request->getPost('id_kegiatan_wilayah');

        if (!$idKegiatanWilayah) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'ID Kegiatan Wilayah tidak ditemukan',
                'csrf_hash' => csrf_hash()
            ]);
        }

        // Cek apakah admin punya akses ke kegiatan ini
        $isAssigned = $this->kegiatanWilayahAdminModel->isAssigned($admin['id_admin_kabupaten'], $idKegiatanWilayah);
        if (!$isAssigned) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Anda tidak memiliki akses ke kegiatan ini',
                'csrf_hash' => csrf_hash()
            ]);
        }

        try {
            // Get kegiatan wilayah
            $kegiatanWilayah = $this->kegiatanWilayahModel->find($idKegiatanWilayah);

            if (!$kegiatanWilayah) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'Kegiatan wilayah tidak ditemukan',
                    'csrf_hash' => csrf_hash()
                ]);
            }

            if (!isset($kegiatanWilayah['target_wilayah'])) {
                log_message('error', 'Field target_wilayah tidak ditemukan. Data: ' . json_encode($kegiatanWilayah));

                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'Field target tidak ditemukan di database. Silakan periksa struktur tabel.',
                    'csrf_hash' => csrf_hash(),
                    'debug_data' => array_keys($kegiatanWilayah)
                ]);
            }

            $targetWilayah = (int) $kegiatanWilayah['target_wilayah'];

            // Hitung total target PML yang sudah di-assign untuk kegiatan ini
            $totalTargetPML = $this->pmlModel->db->table('pml')
                ->selectSum('target', 'total_target')
                ->where('id_kegiatan_wilayah', $idKegiatanWilayah)
                ->get()
                ->getRow()
                ->total_target ?? 0;

            $sisaTarget = $targetWilayah - (int) $totalTargetPML;

            return $this->response->setJSON([
                'success' => true,
                'target_wilayah' => $targetWilayah,
                'target_terpakai' => (int) $totalTargetPML,
                'sisa_target' => max(0, $sisaTarget),
                'csrf_hash' => csrf_hash()
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error getSisaTargetKegiatanWilayah: ' . $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'error' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'csrf_hash' => csrf_hash()
            ]);
        }
    }

    //  AJAX: Get Available PML
    //  Exclude admin yang sedang login dan user yang sudah terlibat di kegiatan ini
    public function getAvailablePML()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'error' => 'Invalid request']);
        }

        $sobatId = session()->get('sobat_id');
        $admin = $this->adminKabModel->db->table('admin_survei_kabupaten ask')
            ->select('ask.*, u.id_kabupaten')
            ->join('sipantau_user u', 'ask.sobat_id = u.sobat_id')
            ->where('ask.sobat_id', $sobatId)
            ->get()
            ->getRowArray();

        if (!$admin) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Unauthorized',
                'csrf_hash' => csrf_hash()
            ]);
        }

        $idKegiatanWilayah = $this->request->getPost('id_kegiatan_wilayah');

        // Cek akses admin ke kegiatan ini
        $isAssigned = $this->kegiatanWilayahAdminModel->isAssigned($admin['id_admin_kabupaten'], $idKegiatanWilayah);
        if (!$isAssigned) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Anda tidak memiliki akses ke kegiatan ini',
                'csrf_hash' => csrf_hash()
            ]);
        }

        // Get available PML (exclude admin yang sedang login dan yang terlibat di kegiatan ini)
        $users = $this->pmlModel->getAvailablePMLForKegiatan($admin['id_kabupaten'], $idKegiatanWilayah, $sobatId);

        return $this->response->setJSON([
            'success' => true,
            'data' => $users,
            'csrf_hash' => csrf_hash()
        ]);
    }

    //  AJAX: Get Available PCL
    //  Exclude PML yang dipilih dan admin yang terlibat di kegiatan ini
    public function getAvailablePCL()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'error' => 'Invalid request']);
        }

        $sobatId = session()->get('sobat_id');
        $admin = $this->adminKabModel->db->table('admin_survei_kabupaten ask')
            ->select('ask.*, u.id_kabupaten')
            ->join('sipantau_user u', 'ask.sobat_id = u.sobat_id')
            ->where('ask.sobat_id', $sobatId)
            ->get()
            ->getRowArray();

        if (!$admin) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Unauthorized',
                'csrf_hash' => csrf_hash()
            ]);
        }

        $idPML = $this->request->getPost('id_pml');
        $pmlSobatId = $this->request->getPost('pml_sobat_id'); // PML yang dipilih
        $idKegiatanWilayah = $this->request->getPost('id_kegiatan_wilayah');

        // Cek akses admin ke kegiatan ini
        if ($idKegiatanWilayah) {
            $isAssigned = $this->kegiatanWilayahAdminModel->isAssigned($admin['id_admin_kabupaten'], $idKegiatanWilayah);
            if (!$isAssigned) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'Anda tidak memiliki akses ke kegiatan ini',
                    'csrf_hash' => csrf_hash()
                ]);
            }
        }

        // Get available PCL (exclude PML yang dipilih dan yang terlibat di kegiatan ini)
        $users = $this->pclModel->getAvailablePCLForKegiatan(
            $admin['id_kabupaten'],
            $idKegiatanWilayah,
            $idPML,
            $pmlSobatId
        );

        return $this->response->setJSON([
            'success' => true,
            'data' => $users,
            'csrf_hash' => csrf_hash()
        ]);
    }

    //  AJAX: Get Sisa Target PML
    public function getSisaTargetPML()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'error' => 'Invalid request']);
        }

        $idPML = $this->request->getPost('id_pml');
        $excludePCLId = $this->request->getPost('exclude_pcl_id');

        $sisaTarget = $this->pclModel->getSisaTargetPML($idPML, $excludePCLId);

        return $this->response->setJSON([
            'success' => true,
            'sisa_target' => $sisaTarget,
            'csrf_hash' => csrf_hash()
        ]);
    }

    //  Store Assignment - Updated with validation
    public function store()
    {
        $sobatId = session()->get('sobat_id');

        if (!$sobatId) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu');
        }

        $admin = $this->adminKabModel->db->table('admin_survei_kabupaten ask')
            ->select('ask.*, u.id_kabupaten')
            ->join('sipantau_user u', 'ask.sobat_id = u.sobat_id')
            ->where('ask.sobat_id', $sobatId)
            ->get()
            ->getRowArray();

        if (!$admin) {
            return redirect()->to('/')->with('error', 'Anda tidak memiliki akses sebagai admin kabupaten');
        }

        $rules = [
            'kegiatan_survei' => 'required|numeric',
            'pml_sobat_id' => 'required|numeric',
            'pml_target' => 'required|numeric|greater_than[0]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $idKegiatanWilayah = $this->request->getPost('kegiatan_survei');
        $pmlSobatId = $this->request->getPost('pml_sobat_id');
        $pmlTarget = (int) $this->request->getPost('pml_target');
        $pclData = $this->request->getPost('pcl');

        // Validasi: Cek apakah admin punya akses ke kegiatan ini
        $isAssigned = $this->kegiatanWilayahAdminModel->isAssigned($admin['id_admin_kabupaten'], $idKegiatanWilayah);
        if (!$isAssigned) {
            return redirect()->back()->withInput()->with('error', 'Anda tidak memiliki akses ke kegiatan yang dipilih');
        }

        // Validasi: PML tidak boleh admin yang sedang login
        // if ($pmlSobatId == $sobatId) {
        //     return redirect()->back()->withInput()->with('error', 'Anda tidak dapat mengassign diri sendiri sebagai PML');
        // }

        // Validasi 1: Cek sisa target kegiatan wilayah
        $kegiatanWilayah = $this->kegiatanWilayahModel->find($idKegiatanWilayah);
        if (!$kegiatanWilayah) {
            return redirect()->back()->withInput()->with('error', 'Kegiatan wilayah tidak ditemukan');
        }

        // Cek duplikasi PML
        $existingPML = $this->pmlModel->where('sobat_id', $pmlSobatId)
                                      ->where('id_kegiatan_wilayah', $idKegiatanWilayah)
                                      ->first();
        if ($existingPML) {
            return redirect()->back()->withInput()->with('error', 'Petugas tersebut sudah di-assign sebagai PML di kegiatan ini.');
        }

        // Cek duplikasi PCL
        $pclIdsInForm = [];
        if ($pclData && is_array($pclData)) {
            foreach ($pclData as $pcl) {
                if (!empty($pcl['sobat_id'])) {
                    if (in_array($pcl['sobat_id'], $pclIdsInForm)) {
                        return redirect()->back()->withInput()->with('error', "Petugas dengan ID {$pcl['sobat_id']} dipilih lebih dari satu kali dalam form.");
                    }
                    $pclIdsInForm[] = $pcl['sobat_id'];

                    $existingPCL = $this->pclModel->db->table('pcl')
                        ->join('pml', 'pml.id_pml = pcl.id_pml')
                        ->where('pcl.sobat_id', $pcl['sobat_id'])
                        ->where('pml.id_kegiatan_wilayah', $idKegiatanWilayah)
                        ->get()->getRowArray();

                    if ($existingPCL) {
                        return redirect()->back()->withInput()->with('error', "Petugas dengan ID {$pcl['sobat_id']} sudah di-assign sebagai PCL di kegiatan ini.");
                    }
                }
            }
        }

        $targetWilayah = (int) $kegiatanWilayah['target_wilayah'];

        // Hitung total target PML yang sudah ada
        $totalTargetPMLExisting = $this->pmlModel->db->table('pml')
            ->selectSum('target', 'total_target')
            ->where('id_kegiatan_wilayah', $idKegiatanWilayah)
            ->get()
            ->getRow()
            ->total_target ?? 0;

        $sisaTargetWilayah = $targetWilayah - (int) $totalTargetPMLExisting;

        if ($pmlTarget > $sisaTargetWilayah) {
            return redirect()->back()->withInput()->with(
                'error',
                "Target PML ($pmlTarget) melebihi sisa target kegiatan wilayah yang tersedia ($sisaTargetWilayah)"
            );
        }

        // Validasi 2: total target PCL tidak melebihi target PML
        $totalTargetPCL = 0;
        if ($pclData && is_array($pclData)) {
            foreach ($pclData as $pcl) {
                if (!empty($pcl['target'])) {
                    $totalTargetPCL += (int) $pcl['target'];
                }
            }
        }

        if ($totalTargetPCL > $pmlTarget) {
            return redirect()->back()->withInput()->with(
                'error',
                "Total target PCL ($totalTargetPCL) melebihi target PML ($pmlTarget)"
            );
        }

        $this->pmlModel->db->transStart();

        try {
            // Insert PML
            $pmlId = $this->pmlModel->insert([
                'sobat_id' => $pmlSobatId,
                'id_kegiatan_wilayah' => $idKegiatanWilayah,
                'target' => $pmlTarget,
                'status_approval' => 0,
                'tanggal_approval' => null
            ]);

            // Get kegiatan detail proses untuk mendapatkan tanggal
            $detailProses = $this->prosesModel->find($kegiatanWilayah['id_kegiatan_detail_proses']);

            // Insert PCL dan generate Kurva S
            if ($pclData && is_array($pclData)) {
                foreach ($pclData as $pcl) {
                    if (!empty($pcl['sobat_id']) && !empty($pcl['target'])) {
                        $pclId = $this->pclModel->insert([
                            'sobat_id' => $pcl['sobat_id'],
                            'id_pml' => $pmlId,
                            'target' => $pcl['target'],
                            'status_approval' => 0,
                            'tanggal_approval' => null
                        ]);

                        // Generate Kurva S untuk PCL
                        $this->generateKurvaPetugas(
                            $pclId,
                            $pcl['target'],
                            $detailProses['persentase_target_awal'],
                            $detailProses['tanggal_mulai'],
                            $detailProses['tanggal_selesai_target'],
                            $detailProses['tanggal_selesai']
                        );
                    }
                }
            }

            $this->pmlModel->db->transComplete();

            if ($this->pmlModel->db->transStatus() === false) {
                return redirect()->back()->withInput()->with('error', 'Gagal menyimpan data');
            }

            return redirect()->to('/adminsurvei-kab/assign-petugas')
                ->with('success', 'Berhasil assign petugas survei dan generate Kurva S');

        } catch (\Exception $e) {
            $this->pmlModel->db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    //  Generate Kurva S untuk PCL
    private function generateKurvaPetugas(
    $idPCL,
    $target,
    $persenAwal,
    $tanggalMulai,
    $tanggal100,
    $tanggalSelesai
) {
    $totalTarget = (int) $target;
    $persenAwal  = (float) $persenAwal;

    $start = new DateTime($tanggalMulai);
    $tgl100 = new DateTime($tanggal100);
    $end = new DateTime($tanggalSelesai);
    $end->modify('+1 day');

    $interval = new DateInterval('P1D');
    $allDays = iterator_to_array(new DatePeriod($start, $interval, $end));

    // Semua hari sampai tanggal 100%
    $daysUntil100 = array_filter($allDays, fn($d) => $d <= $tgl100);
    $daysUntil100 = array_values($daysUntil100);

    $daysSigmoid = max(count($daysUntil100), 2);

    $k = 8;
    $x0 = 0.5;

    $sigmoidMin = 1 / (1 + exp(-$k * (0 - $x0)));
    $sigmoidMax = 1 / (1 + exp(-$k * (1 - $x0)));

    $dayData = [];

    foreach ($daysUntil100 as $i => $date) {
        $progress = $i / ($daysSigmoid - 1);

        $sigmoid = 1 / (1 + exp(-$k * ($progress - $x0)));
        $normalized = ($sigmoid - $sigmoidMin) / ($sigmoidMax - $sigmoidMin);

        $kumulatifPersen = $persenAwal + (100 - $persenAwal) * $normalized;
        $dayData[$date->format('Y-m-d')] = min($kumulatifPersen, 100);
    }

    $kumulatifAbsolut = 0;
    $insertData = [];

    // Tahap 1: sebelum / sampai 100%
    foreach ($daysUntil100 as $date) {
        $current = $date->format('Y-m-d');
        $kumulatifPersen = $dayData[$current];

        $harian = round(($totalTarget * ($kumulatifPersen / 100)) - $kumulatifAbsolut);
        $kumulatifAbsolut += $harian;

        $isHariKerja = ($date->format('N') <= 5) ? 1 : 0;

        $insertData[] = [
            'id_pcl' => $idPCL,
            'tanggal_target' => $current,
            'target_persen_kumulatif' => round($kumulatifPersen, 2),
            'target_harian_absolut' => $harian,
            'target_kumulatif_absolut' => $kumulatifAbsolut,
            'is_hari_kerja' => $isHariKerja
        ];
    }

    // Koreksi hari terakhir
    $selisih = $totalTarget - $kumulatifAbsolut;
    if (!empty($insertData) && $selisih !== 0) {
        $last = count($insertData) - 1;
        $insertData[$last]['target_harian_absolut'] += $selisih;
        $insertData[$last]['target_kumulatif_absolut'] += $selisih;
    }

    // Tahap 2: setelah tanggal100 → mendatar
    $daysAfter100 = array_filter($allDays, fn($d) => $d > $tgl100);

    foreach ($daysAfter100 as $date) {
        $insertData[] = [
            'id_pcl' => $idPCL,
            'tanggal_target' => $date->format('Y-m-d'),
            'target_persen_kumulatif' => 100,
            'target_harian_absolut' => 0,
            'target_kumulatif_absolut' => $totalTarget,
            'is_hari_kerja' => ($date->format('N') <= 5) ? 1 : 0
        ];
    }

    $this->kurvaModel->insertBatch($insertData);

    log_message('info', "Kurva Petugas dibuat (harian termasuk hari libur) id_pcl=$idPCL");
}


    //  Detail PML dan PCL-nya
    public function detail($idPML)
    {
        $sobatId = session()->get('sobat_id');

        if (!$sobatId) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu');
        }

        $admin = $this->adminKabModel->db->table('admin_survei_kabupaten ask')
            ->select('ask.*, u.id_kabupaten')
            ->join('sipantau_user u', 'ask.sobat_id = u.sobat_id')
            ->where('ask.sobat_id', $sobatId)
            ->get()
            ->getRowArray();

        if (!$admin) {
            return redirect()->to('/')->with('error', 'Anda tidak memiliki akses sebagai admin kabupaten');
        }

        $pml = $this->pmlModel->getPMLWithDetails($idPML);

        if (!$pml) {
            return redirect()->to('/adminsurvei-kab/assign-petugas')->with('error', 'Data PML tidak ditemukan');
        }

        if ($admin['id_kabupaten'] != $pml['id_kabupaten']) {
            return redirect()->to('/adminsurvei-kab/assign-petugas')->with('error', 'Akses ditolak');
        }

        // Cek apakah kegiatan ini di-assign ke admin yang login
        $isAssigned = $this->kegiatanWilayahAdminModel->isAssigned($admin['id_admin_kabupaten'], $pml['id_kegiatan_wilayah']);
        if (!$isAssigned) {
            return redirect()->to('/adminsurvei-kab/assign-petugas')->with('error', 'Anda tidak memiliki akses ke kegiatan ini');
        }

        $dataPCL = $this->pclModel->getPCLByPML($idPML);

        $data = [
            'title' => 'Detail Assignment PML',
            'active_menu' => 'assign-admin-kab',
            'pml' => $pml,
            'dataPCL' => $dataPCL
        ];

        return view('AdminSurveiKab/AssignPetugasSurvei/detail', $data);
    }

    //  Delete PML beserta PCL dan Kurva-nya
    public function delete($idPML)
    {
        $sobatId = session()->get('sobat_id');

        if (!$sobatId) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu');
        }

        $admin = $this->adminKabModel->db->table('admin_survei_kabupaten ask')
            ->select('ask.*, u.id_kabupaten')
            ->join('sipantau_user u', 'ask.sobat_id = u.sobat_id')
            ->where('ask.sobat_id', $sobatId)
            ->get()
            ->getRowArray();

        if (!$admin) {
            return redirect()->to('/')->with('error', 'Anda tidak memiliki akses');
        }

        $pml = $this->pmlModel->getPMLWithDetails($idPML);

        if (!$pml) {
            return redirect()->to('/adminsurvei-kab/assign-petugas')->with('error', 'Data tidak ditemukan');
        }

        if ($admin['id_kabupaten'] != $pml['id_kabupaten']) {
            return redirect()->to('/adminsurvei-kab/assign-petugas')->with('error', 'Akses ditolak');
        }

        // Cek apakah kegiatan ini di-assign ke admin yang login
        $isAssigned = $this->kegiatanWilayahAdminModel->isAssigned($admin['id_admin_kabupaten'], $pml['id_kegiatan_wilayah']);
        if (!$isAssigned) {
            return redirect()->to('/adminsurvei-kab/assign-petugas')->with('error', 'Anda tidak memiliki akses ke kegiatan ini');
        }

        // ===== GUARD: Cek data transaksi sebelum izinkan delete =====
        $txSummary = $this->pmlModel->getTransactionSummary($idPML);
        $force = $this->request->getGet('force') === 'true';

        if ($txSummary['has_data'] && !$force) {
            $detailPCL = '';
            foreach ($txSummary['pcl_with_data'] as $pcl) {
                $detailPCL .= "• {$pcl['nama_user']} — {$pcl['jumlah_progress']} progress, {$pcl['jumlah_transaksi']} transaksi. ";
            }

            $message = "Tidak dapat menghapus assignment PML '{$pml['nama_pml']}' karena sudah memiliki data transaksi. "
                . "Detail: {$txSummary['total_progress']} progress, {$txSummary['total_transaksi']} transaksi. "
                . "PCL bermasalah: {$detailPCL}"
                . "Jika Anda yakin ingin menghapus PML ini beserta SELURUH DATA aktivitas PCL di bawahnya, silakan gunakan 'Force Delete'.";

            return redirect()->to('/adminsurvei-kab/assign-petugas')->with('error', $message)->with('show_force_delete_pml', $idPML);
        }
        // ===== END GUARD =====

        // Delete dengan cascade (PCL dan Kurva akan terhapus otomatis jika FK ON DELETE CASCADE)
        if ($this->pmlModel->deletePMLWithPCL($idPML)) {
            return redirect()->to('/adminsurvei-kab/assign-petugas')
                ->with('success', 'Berhasil menghapus assignment PML, PCL, dan Kurva S');
        }

        return redirect()->to('/adminsurvei-kab/assign-petugas')->with('error', 'Gagal menghapus data');
    }

    public function detailPML($id_pml)
    {
        $sobatId = session()->get('sobat_id');

        if (!$sobatId) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu');
        }

        $admin = $this->adminKabModel->db->table('admin_survei_kabupaten ask')
            ->select('ask.*, u.id_kabupaten')
            ->join('sipantau_user u', 'ask.sobat_id = u.sobat_id')
            ->where('ask.sobat_id', $sobatId)
            ->get()
            ->getRowArray();

        if (!$admin) {
            return redirect()->to('/')->with('error', 'Anda tidak memiliki akses');
        }

        // Ambil data PML
        $pml = $this->pmlModel
            ->select('pml.*, 
                      u.nama_user AS nama_pml, u.email AS email_pml,
                      kw.id_kegiatan_wilayah, 
                      kd.nama_kegiatan_detail, 
                      kdp.nama_kegiatan_detail_proses')
            ->join('sipantau_user u', 'u.sobat_id = pml.sobat_id')
            ->join('kegiatan_wilayah kw', 'kw.id_kegiatan_wilayah = pml.id_kegiatan_wilayah')
            ->join('master_kegiatan_detail_proses kdp', 'kdp.id_kegiatan_detail_proses = kw.id_kegiatan_detail_proses')
            ->join('master_kegiatan_detail kd', 'kd.id_kegiatan_detail = kdp.id_kegiatan_detail')
            ->where('pml.id_pml', $id_pml)
            ->first();

        if (!$pml) {
            return redirect()->back()->with('error', 'Data PML tidak ditemukan.');
        }

        // Cek apakah kegiatan ini di-assign ke admin yang login
        $isAssigned = $this->kegiatanWilayahAdminModel->isAssigned($admin['id_admin_kabupaten'], $pml['id_kegiatan_wilayah']);
        if (!$isAssigned) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke kegiatan ini');
        }

        // Ambil semua PCL di bawah PML ini
        $pclList = $this->pclModel
            ->select('pcl.*, u.nama_user AS nama_pcl, u.email AS email_pcl')
            ->join('sipantau_user u', 'u.sobat_id = pcl.sobat_id')
            ->where('pcl.id_pml', $id_pml)
            ->findAll();

        // Ringkasan sederhana
        $summary = [
            'total_pcl' => count($pclList),
            'total_target' => array_sum(array_column($pclList, 'target'))
        ];

        return view('AdminSurveiKab/AssignPetugasSurvei/detail', [
            'pml' => $pml,
            'active_menu' => 'assign-admin-kab',
            'pclList' => $pclList,
            'summary' => $summary
        ]);
    }

    public function pclDetail($id_pcl)
    {
        $sobatId = session()->get('sobat_id');

        if (!$sobatId) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu');
        }

        $admin = $this->adminKabModel->db->table('admin_survei_kabupaten ask')
            ->select('ask.*, u.id_kabupaten')
            ->join('sipantau_user u', 'ask.sobat_id = u.sobat_id')
            ->where('ask.sobat_id', $sobatId)
            ->get()
            ->getRowArray();

        if (!$admin) {
            return redirect()->to('/')->with('error', 'Anda tidak memiliki akses');
        }

        // Get PCL detail
        $pclDetail = $this->pclModel->db->table('pcl')
            ->select('pcl.*, 
                 u.nama_user as nama_pcl, 
                 u.email, 
                 u.hp,
                 u.id_kabupaten,
                 u_pml.nama_user as nama_pml,
                 pml.id_pml,
                 kw.id_kegiatan_wilayah,
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
            ->where('pcl.id_pcl', $id_pcl)
            ->get()
            ->getRowArray();

        if (!$pclDetail) {
            return redirect()->back()->with('error', 'Data PCL tidak ditemukan.');
        }

        // Validasi kabupaten
        if ($pclDetail['id_kabupaten'] != $admin['id_kabupaten']) {
            return redirect()->to('unauthorized')->with('error', 'Anda tidak memiliki akses ke data ini');
        }

        // Cek apakah kegiatan ini di-assign ke admin yang login
        $isAssigned = $this->kegiatanWilayahAdminModel->isAssigned($admin['id_admin_kabupaten'], $pclDetail['id_kegiatan_wilayah']);
        if (!$isAssigned) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke kegiatan ini');
        }

        // Load additional models
        $pantauProgressModel = new \App\Models\PantauProgressModel();
        $kurvaPetugasModel = new \App\Models\KurvaPetugasModel();

        // Get realisasi data
        $realisasi = $pantauProgressModel
            ->select('COALESCE(MAX(jumlah_realisasi_kumulatif), 0) as total_realisasi')
            ->where('id_pcl', $id_pcl)
            ->first();

        $realisasiKumulatif = (int) ($realisasi['total_realisasi'] ?? 0);
        $target = (int) $pclDetail['target'];
        $persentase = $target > 0 ? round(($realisasiKumulatif / $target) * 100, 2) : 0;
        $selisih = $target - $realisasiKumulatif;

        // Get Kurva S data
        $kurvaData = $this->getKurvaDataPCL($id_pcl, $pclDetail, $pantauProgressModel, $kurvaPetugasModel);

        $from = $this->request->getGet('from');
        $data = [
            'title' => 'Detail Laporan PCL',
            'active_menu' => 'assign-admin-kab',
            'pcl' => $pclDetail,
            'target' => $target,
            'realisasi' => $realisasiKumulatif,
            'persentase' => $persentase,
            'selisih' => $selisih,
            'kurvaData' => $kurvaData,
            'idPCL' => $id_pcl,
            'from' => $from
        ];

        return view('AdminSurveiKab/AssignPetugasSurvei/kurva_s', $data);
    }

    //  Delete PCL beserta Kurva-nya
    public function deletePcl($idPCL)
    {
        $sobatId = session()->get('sobat_id');

        if (!$sobatId) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu');
        }

        $admin = $this->adminKabModel->db->table('admin_survei_kabupaten ask')
            ->select('ask.*, u.id_kabupaten')
            ->join('sipantau_user u', 'ask.sobat_id = u.sobat_id')
            ->where('ask.sobat_id', $sobatId)
            ->get()
            ->getRowArray();

        if (!$admin) {
            return redirect()->to('/')->with('error', 'Anda tidak memiliki akses');
        }

        $pclDetail = $this->pclModel->getPCLWithDetails($idPCL);

        if (!$pclDetail) {
            return redirect()->back()->with('error', 'Data PCL tidak ditemukan');
        }

        if ($admin['id_kabupaten'] != $pclDetail['id_kabupaten']) {
            return redirect()->back()->with('error', 'Akses ditolak');
        }

        // Cek apakah kegiatan ini di-assign ke admin yang login
        $isAssigned = $this->kegiatanWilayahAdminModel->isAssigned($admin['id_admin_kabupaten'], $pclDetail['id_kegiatan_wilayah']);
        if (!$isAssigned) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke kegiatan ini');
        }

        // ===== GUARD: Cek data transaksi sebelum izinkan delete =====
        $db = \Config\Database::connect();
        
        $progressCount = $db->table('pantau_progress')->where('id_pcl', $idPCL)->countAllResults();
        $transaksiCount = $db->table('sipantau_transaksi')->where('id_pcl', $idPCL)->countAllResults();
        
        $force = $this->request->getGet('force') === 'true';

        if (($progressCount > 0 || $transaksiCount > 0) && !$force) {
            $msg = "Tidak dapat menghapus assign PCL '{$pclDetail['nama_pcl']}' karena petugas sudah memiliki $progressCount progress aktivitas dan $transaksiCount data transaksi. ";
            $msg .= "Jika Anda benar-benar ingin menghapus petugas ini beserta SEMUA DATA aktivitasnya, silakan gunakan tombol 'Force Delete'.";
            
            return redirect()->back()->with('error', $msg)->with('show_force_delete', $idPCL);
        }
        // ===== END GUARD =====

        // Delete PCL menggunakan helper cascade
        if ($this->pclModel->deletePCLWithKurva($idPCL)) {
            return redirect()->back()
                ->with('success', "Berhasil menghapus assignment PCL '{$pclDetail['nama_pcl']}'");
        }

        return redirect()->back()->with('error', 'Gagal menghapus data PCL');
    }


    private function getKurvaDataPCL($idPCL, $pclDetail, $pantauProgressModel, $kurvaPetugasModel)
    {
        // Get kurva target
        $kurvaTarget = $kurvaPetugasModel
            ->where('id_pcl', $idPCL)
            ->orderBy('tanggal_target', 'ASC')
            ->findAll();

        // Get realisasi harian
        $realisasiHarian = $this->pmlModel->db->query("
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

    // Method Download Template
    public function downloadTemplate($idKegiatanWilayah)
    {
        $sobatId = session()->get('sobat_id');
        if (!$sobatId) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu');
        }

        $admin = $this->adminKabModel->db->table('admin_survei_kabupaten ask')
            ->select('ask.*, u.id_kabupaten, u.nama_user, k.nama_kabupaten')
            ->join('sipantau_user u', 'ask.sobat_id = u.sobat_id')
            ->join('master_kabupaten k', 'u.id_kabupaten = k.id_kabupaten')
            ->where('ask.sobat_id', $sobatId)
            ->get()
            ->getRowArray();

        if (!$admin) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses');
        }

        $idKabupaten = $admin['id_kabupaten'];
        $idAdminKabupaten = $admin['id_admin_kabupaten'];

        // Cek akses ke kegiatan
        $isAssigned = $this->kegiatanWilayahAdminModel->isAssigned($idAdminKabupaten, $idKegiatanWilayah);
        if (!$isAssigned) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke kegiatan ini');
        }

        // Get data kegiatan wilayah
        $kegiatanWilayah = $this->kegiatanWilayahModel->find($idKegiatanWilayah);
        if (!$kegiatanWilayah || $kegiatanWilayah['id_kabupaten'] != $idKabupaten) {
            return redirect()->back()->with('error', 'Kegiatan tidak ditemukan');
        }

        // Get detail kegiatan
        $detailProses = $this->prosesModel->find($kegiatanWilayah['id_kegiatan_detail_proses']);
        $detailModel = new \App\Models\MasterKegiatanDetailModel();
        $kegiatanDetail = $detailModel->find($detailProses['id_kegiatan_detail']);

        // Get sisa target
        $targetWilayah = (int) $kegiatanWilayah['target_wilayah'];
        $totalTargetPML = $this->pmlModel->db->table('pml')
            ->selectSum('target', 'total_target')
            ->where('id_kegiatan_wilayah', $idKegiatanWilayah)
            ->get()
            ->getRow()
            ->total_target ?? 0;
        $sisaTarget = $targetWilayah - (int) $totalTargetPML;

        // Get available users (PML & PCL)
        $availableUsers = $this->userModel->db->table('sipantau_user u')
            ->select('u.sobat_id, u.nama_user, u.email, u.hp')
            ->where('u.id_kabupaten', $idKabupaten)
            ->where('u.sobat_id !=', $sobatId)
            ->whereNotIn('u.sobat_id', function ($builder) use ($idKegiatanWilayah) {
                $builder->select('pml.sobat_id')
                    ->from('pml')
                    ->where('pml.id_kegiatan_wilayah', $idKegiatanWilayah);
            })
            ->whereNotIn('u.sobat_id', function ($builder) use ($idKegiatanWilayah) {
                $builder->select('pcl.sobat_id')
                    ->from('pcl')
                    ->join('pml', 'pcl.id_pml = pml.id_pml')
                    ->where('pml.id_kegiatan_wilayah', $idKegiatanWilayah);
            })
            ->orderBy('u.nama_user', 'ASC')
            ->get()
            ->getResultArray();

        // Create Spreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        // === SHEET 1: Template Import ===
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Template Import');

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => '000000']]]
        ];

        // Info Kegiatan
        $sheet1->setCellValue('A1', 'Kegiatan:');
        $sheet1->setCellValue('B1', $kegiatanDetail['nama_kegiatan_detail'] ?? '-');
        $sheet1->mergeCells('B1:E1');
        $sheet1->getStyle('A1')->getFont()->setBold(true);

        $sheet1->setCellValue('A2', 'Proses:');
        $sheet1->setCellValue('B2', $detailProses['nama_kegiatan_detail_proses']);
        $sheet1->mergeCells('B2:E2');
        $sheet1->getStyle('A2')->getFont()->setBold(true);

        $sheet1->setCellValue('A3', 'Kabupaten:');
        $sheet1->setCellValue('B3', $admin['nama_kabupaten']);
        $sheet1->mergeCells('B3:E3');
        $sheet1->getStyle('A3')->getFont()->setBold(true);

        $sheet1->setCellValue('A4', 'Target Wilayah:');
        $sheet1->setCellValue('B4', number_format($targetWilayah));
        $sheet1->getStyle('A4')->getFont()->setBold(true);

        $sheet1->setCellValue('A5', 'Target Terpakai:');
        $sheet1->setCellValue('B5', number_format($totalTargetPML));
        $sheet1->getStyle('A5')->getFont()->setBold(true);

        $sheet1->setCellValue('A6', 'Sisa Target:');
        $sheet1->setCellValue('B6', number_format($sisaTarget));
        $sheet1->getStyle('A6')->getFont()->setBold(true);
        $sheet1->getStyle('B6')->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('008000'));

        // Header tabel (baris 8) - HANYA 4 KOLOM
        $sheet1->setCellValue('A8', 'SOBAT ID PML');
        $sheet1->setCellValue('B8', 'Target PML');
        $sheet1->setCellValue('C8', 'SOBAT ID PCL');
        $sheet1->setCellValue('D8', 'Target PCL');
        $sheet1->getStyle('A8:D8')->applyFromArray($headerStyle);

        // Set column widths
        $sheet1->getColumnDimension('A')->setWidth(20);
        $sheet1->getColumnDimension('B')->setWidth(15);
        $sheet1->getColumnDimension('C')->setWidth(20);
        $sheet1->getColumnDimension('D')->setWidth(15);

        // Format sebagai teks untuk SOBAT ID, number untuk target
        $sheet1->getStyle('A:A')->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
        $sheet1->getStyle('B:B')->getNumberFormat()->setFormatCode('#,##0');
        $sheet1->getStyle('C:C')->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
        $sheet1->getStyle('D:D')->getNumberFormat()->setFormatCode('#,##0');

        // ======= CONTOH DATA BARU (PML hanya ditulis sekali) =======
        // PML 1 dengan 2 PCL
        $sheet1->setCellValue('A9', 'Contoh: 3301010001');
        $sheet1->setCellValue('B9', '100');
        $sheet1->setCellValue('C9', 'Contoh: 3301010002');
        $sheet1->setCellValue('D9', '50');

        $sheet1->setCellValue('A10', ''); // Kosongkan PML
        $sheet1->setCellValue('B10', ''); // Kosongkan Target PML
        $sheet1->setCellValue('C10', 'Contoh: 3301010003');
        $sheet1->setCellValue('D10', '50');

        // PML 2 dengan 3 PCL
        $sheet1->setCellValue('A11', 'Contoh: 3301010004');
        $sheet1->setCellValue('B11', '150');
        $sheet1->setCellValue('C11', 'Contoh: 3301010005');
        $sheet1->setCellValue('D11', '50');

        $sheet1->setCellValue('A12', ''); // Kosongkan PML
        $sheet1->setCellValue('B12', ''); // Kosongkan Target PML
        $sheet1->setCellValue('C12', 'Contoh: 3301010006');
        $sheet1->setCellValue('D12', '50');

        $sheet1->setCellValue('A13', ''); // Kosongkan PML
        $sheet1->setCellValue('B13', ''); // Kosongkan Target PML
        $sheet1->setCellValue('C13', 'Contoh: 3301010007');
        $sheet1->setCellValue('D13', '50');

        // === SHEET 2: Panduan & List Petugas ===
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Panduan & Petugas');

        $sheet2->setCellValue('A1', 'PANDUAN PENGISIAN TEMPLATE');
        $sheet2->mergeCells('A1:D1');
        $sheet2->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet2->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet2->setCellValue('A3', 'CARA PENGISIAN:');
        $sheet2->getStyle('A3')->getFont()->setBold(true);

        $sheet2->setCellValue('A4', '1. Isi data dimulai dari baris 9 (setelah contoh)');
        $sheet2->setCellValue('A5', '2. Kolom A & B: SOBAT ID PML dan Target PML (HANYA ISI SEKALI untuk setiap PML)');
        $sheet2->setCellValue('A6', '3. Kolom C & D: SOBAT ID PCL dan Target PCL');
        $sheet2->setCellValue('A7', '4. Untuk PCL berikutnya dari PML yang sama, KOSONGKAN kolom A & B, isi hanya kolom C & D');
        $sheet2->setCellValue('A8', '5. Total target PCL harus sama dengan Target PML-nya');
        $sheet2->setCellValue('A9', '6. Total semua Target PML tidak boleh melebihi Sisa Target');
        $sheet2->setCellValue('A10', '7. Gunakan SOBAT ID dari daftar petugas di bawah');
        $sheet2->setCellValue('A11', '8. HANYA ISI KOLOM A, B, C, D - Nama dan Email otomatis terisi sistem');

        $sheet2->setCellValue('A13', 'CONTOH FORMAT:');
        $sheet2->getStyle('A13')->getFont()->setBold(true);

        $sheet2->setCellValue('A14', '==================== PML PERTAMA ====================');
        $sheet2->setCellValue('A15', 'Baris 9:  PML 3301010001 (Target 100) | PCL 3301010002 (Target 50) ← ISI SEMUA');
        $sheet2->setCellValue('A16', 'Baris 10: [KOSONG]                    | PCL 3301010003 (Target 50) ← PML KOSONG');
        $sheet2->setCellValue('A17', '');
        $sheet2->setCellValue('A18', '==================== PML KEDUA ====================');
        $sheet2->setCellValue('A19', 'Baris 11: PML 3301010004 (Target 150) | PCL 3301010005 (Target 50) ← ISI SEMUA');
        $sheet2->setCellValue('A20', 'Baris 12: [KOSONG]                    | PCL 3301010006 (Target 50) ← PML KOSONG');
        $sheet2->setCellValue('A21', 'Baris 13: [KOSONG]                    | PCL 3301010007 (Target 50) ← PML KOSONG');

        // List Petugas Available
        $sheet2->setCellValue('A24', 'DAFTAR PETUGAS YANG TERSEDIA:');
        $sheet2->getStyle('A24')->getFont()->setBold(true)->setSize(12);

        $sheet2->setCellValue('A25', 'SOBAT ID');
        $sheet2->setCellValue('B25', 'Nama Lengkap');
        $sheet2->setCellValue('C25', 'Email');
        $sheet2->setCellValue('D25', 'No. HP');
        $sheet2->getStyle('A25:D25')->applyFromArray($headerStyle);

        $sheet2->getColumnDimension('A')->setWidth(50);
        $sheet2->getColumnDimension('B')->setWidth(35);
        $sheet2->getColumnDimension('C')->setWidth(35);
        $sheet2->getColumnDimension('D')->setWidth(18);

        // Fill available users
        $row = 26;
        foreach ($availableUsers as $user) {
            $sheet2->setCellValue("A{$row}", $user['sobat_id']);
            $sheet2->setCellValue("B{$row}", $user['nama_user']);
            $sheet2->setCellValue("C{$row}", $user['email']);
            $sheet2->setCellValue("D{$row}", $user['hp']);
            $row++;
        }

        // Generate filename
        $namaKegiatan = preg_replace('/[^A-Za-z0-9_\-]/', '_', $detailProses['nama_kegiatan_detail_proses']);
        $filename = 'Template_Assign_Petugas_' . $namaKegiatan . '_' . date('YmdHis') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    // Method Import
    public function import()
    {
        $sobatId = session()->get('sobat_id');
        if (!$sobatId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Silakan login terlebih dahulu'
            ]);
        }

        $admin = $this->adminKabModel->db->table('admin_survei_kabupaten ask')
            ->select('ask.*, u.id_kabupaten')
            ->join('sipantau_user u', 'ask.sobat_id = u.sobat_id')
            ->where('ask.sobat_id', $sobatId)
            ->get()
            ->getRowArray();

        if (!$admin) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Anda tidak memiliki akses'
            ]);
        }

        $file = $this->request->getFile('file');
        $idKegiatanWilayah = $this->request->getPost('id_kegiatan_wilayah');

        if (!$file || !$file->isValid()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'File tidak valid'
            ]);
        }

        if (!$idKegiatanWilayah) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID Kegiatan tidak ditemukan'
            ]);
        }

        $extension = $file->getClientExtension();
        if (!in_array($extension, ['xlsx', 'xls'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Format file harus Excel (.xlsx atau .xls)'
            ]);
        }

        // Validasi kegiatan
        $idKabupaten = $admin['id_kabupaten'];
        $idAdminKabupaten = $admin['id_admin_kabupaten'];

        $isAssigned = $this->kegiatanWilayahAdminModel->isAssigned($idAdminKabupaten, $idKegiatanWilayah);
        if (!$isAssigned) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke kegiatan ini'
            ]);
        }

        $kegiatanWilayah = $this->kegiatanWilayahModel->find($idKegiatanWilayah);
        if (!$kegiatanWilayah || $kegiatanWilayah['id_kabupaten'] != $idKabupaten) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Kegiatan tidak valid'
            ]);
        }

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getTempName());
            $sheet = $spreadsheet->getActiveSheet();
            $data = $sheet->toArray();

            // Get target info
            $detailProses = $this->prosesModel->find($kegiatanWilayah['id_kegiatan_detail_proses']);
            $targetWilayah = (int) $kegiatanWilayah['target_wilayah'];
            $totalTargetPMLExisting = (int) $this->pmlModel->db->table('pml')
                ->selectSum('target', 'total_target')
                ->where('id_kegiatan_wilayah', $idKegiatanWilayah)
                ->get()
                ->getRow()
                ->total_target ?? 0;
            $sisaTarget = $targetWilayah - $totalTargetPMLExisting;

            $imported = 0;
            $skipped = 0;
            $errors = [];

            $this->pmlModel->db->transStart();

            // Process data mulai dari baris 9 (index 8)
            $pmlData = [];
            $currentPMLSobatId = null;
            $currentPMLTarget = 0;

            for ($i = 8; $i < count($data); $i++) {
                $row = $data[$i];
                $rowNumber = $i + 1;

                // Skip baris contoh atau kosong
                if (empty($row[0]) && empty($row[2])) {
                    continue;
                }

                if (!empty($row[0]) && strpos($row[0], 'Contoh') !== false) {
                    continue;
                }

                // Cek apakah ada PML baru (kolom A tidak kosong)
                if (!empty($row[0])) {
                    $currentPMLSobatId = trim($row[0]);
                    $currentPMLTarget = (int) preg_replace('/[^0-9]/', '', trim($row[1] ?? '0'));

                    // Validasi PML
                    if ($currentPMLTarget <= 0) {
                        $errors[] = "Baris {$rowNumber}: Target PML harus lebih dari 0";
                        $skipped++;
                        $currentPMLSobatId = null;
                        continue;
                    }

                    // Validasi PML tidak boleh admin yang login
                    // if ($currentPMLSobatId == $sobatId) {
                    //     $errors[] = "Baris {$rowNumber}: PML tidak boleh admin yang sedang login";
                    //     $skipped++;
                    //     $currentPMLSobatId = null;
                    //     continue;
                    // }

                    // Validasi user exists
                    $pmlUser = $this->userModel->find($currentPMLSobatId);
                    if (!$pmlUser || $pmlUser['id_kabupaten'] != $idKabupaten) {
                        $errors[] = "Baris {$rowNumber}: SOBAT ID PML '{$currentPMLSobatId}' tidak valid atau bukan dari kabupaten ini";
                        $skipped++;
                        $currentPMLSobatId = null;
                        continue;
                    }

                    // Inisialisasi array PML jika belum ada
                    if (!isset($pmlData[$currentPMLSobatId])) {
                        $pmlData[$currentPMLSobatId] = [
                            'sobat_id' => $currentPMLSobatId,
                            'target' => $currentPMLTarget,
                            'pcl' => []
                        ];
                    }
                }

                // Proses PCL (kolom C dan D)
                $pclSobatId = !empty($row[2]) ? trim($row[2]) : null;
                $pclTarget = !empty($row[3]) ? (int) preg_replace('/[^0-9]/', '', trim($row[3])) : 0;

                // Jika ada PCL dan PML sudah di-set
                if ($pclSobatId && $pclTarget > 0 && $currentPMLSobatId) {
                    // Validasi PCL
                    // if ($pclSobatId == $sobatId) {
                    //     $errors[] = "Baris {$rowNumber}: PCL tidak boleh admin yang sedang login";
                    //     $skipped++;
                    //     continue;
                    // }

                    if ($pclSobatId == $currentPMLSobatId) {
                        $errors[] = "Baris {$rowNumber}: PCL tidak boleh sama dengan PML-nya";
                        $skipped++;
                        continue;
                    }

                    $pclUser = $this->userModel->find($pclSobatId);
                    if (!$pclUser || $pclUser['id_kabupaten'] != $idKabupaten) {
                        $errors[] = "Baris {$rowNumber}: SOBAT ID PCL '{$pclSobatId}' tidak valid";
                        $skipped++;
                        continue;
                    }

                    // Validasi PCL tidak boleh sudah ada di DB untuk kegiatan ini
                    $existingPCL = $this->pclModel->db->table('pcl')
                        ->join('pml', 'pml.id_pml = pcl.id_pml')
                        ->where('pcl.sobat_id', $pclSobatId)
                        ->where('pml.id_kegiatan_wilayah', $idKegiatanWilayah)
                        ->get()->getRowArray();
                    
                    if ($existingPCL) {
                        $errors[] = "Baris {$rowNumber}: PCL '{$pclSobatId}' sudah di-assign di kegiatan ini";
                        $skipped++;
                        continue;
                    }

                    // Validasi PCL duplikat di dalam file excel ini
                    $isDuplicateInFile = false;
                    foreach ($pmlData as $pData) {
                        foreach ($pData['pcl'] as $pPcl) {
                            if ($pPcl['sobat_id'] == $pclSobatId) {
                                $isDuplicateInFile = true;
                                break 2;
                            }
                        }
                    }
                    if ($isDuplicateInFile) {
                        $errors[] = "Baris {$rowNumber}: PCL '{$pclSobatId}' dimasukkan lebih dari satu kali dalam file";
                        $skipped++;
                        continue;
                    }

                    // Tambahkan PCL ke PML yang sedang aktif
                    $pmlData[$currentPMLSobatId]['pcl'][] = [
                        'sobat_id' => $pclSobatId,
                        'target' => $pclTarget
                    ];
                }
            }

            // Validate dan insert PML beserta PCL
            foreach ($pmlData as $sobatId => $pml) {
                // Validasi sisa target
                if ($pml['target'] > $sisaTarget) {
                    $errors[] = "PML {$sobatId}: Target ({$pml['target']}) melebihi sisa target ({$sisaTarget})";
                    $skipped++;
                    continue;
                }

                // Validasi total target PCL
                $totalPCL = array_sum(array_column($pml['pcl'], 'target'));
                if ($totalPCL > $pml['target']) {
                    $errors[] = "PML {$sobatId}: Total target PCL ({$totalPCL}) melebihi target PML ({$pml['target']})";
                    $skipped++;
                    continue;
                }

                // Check duplicate
                $existingPML = $this->pmlModel->db->table('pml')
                    ->where('sobat_id', $pml['sobat_id'])
                    ->where('id_kegiatan_wilayah', $idKegiatanWilayah)
                    ->get()
                    ->getRowArray();

                if ($existingPML) {
                    $errors[] = "PML {$sobatId}: Sudah di-assign di kegiatan ini";
                    $skipped++;
                    continue;
                }

                // Insert PML
                $pmlId = $this->pmlModel->insert([
                    'sobat_id' => $pml['sobat_id'],
                    'id_kegiatan_wilayah' => $idKegiatanWilayah,
                    'target' => $pml['target'],
                    'status_approval' => 0,
                    'tanggal_approval' => null
                ]);

                // Insert PCL dan generate Kurva
                foreach ($pml['pcl'] as $pcl) {
                    $pclId = $this->pclModel->insert([
                        'sobat_id' => $pcl['sobat_id'],
                        'id_pml' => $pmlId,
                        'target' => $pcl['target'],
                        'status_approval' => 0,
                        'tanggal_approval' => null
                    ]);

                    // Generate Kurva S untuk PCL
                    $this->generateKurvaPetugas(
                        $pclId,
                        $pcl['target'],
                        $detailProses['persentase_target_awal'],
                        $detailProses['tanggal_mulai'],
                        $detailProses['tanggal_selesai_target'],
                        $detailProses['tanggal_selesai']
                    );
                }

                $sisaTarget -= $pml['target'];
                $imported++;
            }

            $this->pmlModel->db->transComplete();

            if ($this->pmlModel->db->transStatus() === false) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat menyimpan data'
                ]);
            }

            $message = "Import selesai!<br>Berhasil: <strong>{$imported}</strong> PML<br>Dilewati: <strong>{$skipped}</strong> data";

            if ($imported === 0) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Tidak ada data yang berhasil diimport.<br>' . implode('<br>', array_slice($errors, 0, 5))
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => $message,
                'errors' => !empty($errors) ? implode("\n", array_slice($errors, 0, 10)) : null
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    // Method untuk menampilkan modal dan get data kegiatan untuk copy
    public function getKegiatanForCopy()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'error' => 'Invalid request']);
        }

        $sobatId = session()->get('sobat_id');
        $admin = $this->adminKabModel->db->table('admin_survei_kabupaten ask')
            ->select('ask.*, u.id_kabupaten')
            ->join('sipantau_user u', 'ask.sobat_id = u.sobat_id')
            ->where('ask.sobat_id', $sobatId)
            ->get()
            ->getRowArray();

        if (!$admin) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Unauthorized',
                'csrf_hash' => csrf_hash()
            ]);
        }

        $idKabupaten = $admin['id_kabupaten'];
        $idAdminKabupaten = $admin['id_admin_kabupaten'];

        // Get kegiatan yang punya assignment (sebagai sumber)
        $kegiatanWithAssignment = $this->kegiatanWilayahModel->db->table('kegiatan_wilayah kw')
            ->select('kw.id_kegiatan_wilayah, mkd.nama_kegiatan_detail, mkdp.nama_kegiatan_detail_proses, mkdp.tanggal_mulai, COUNT(pml.id_pml) as jumlah_pml')
            ->join('master_kegiatan_detail_proses mkdp', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses')
            ->join('master_kegiatan_detail mkd', 'mkdp.id_kegiatan_detail = mkd.id_kegiatan_detail')
            ->join('kegiatan_wilayah_admin kwa', 'kw.id_kegiatan_wilayah = kwa.id_kegiatan_wilayah')
            ->join('pml', 'kw.id_kegiatan_wilayah = pml.id_kegiatan_wilayah', 'left')
            ->where('kw.id_kabupaten', $idKabupaten)
            ->where('kwa.id_admin_kabupaten', $idAdminKabupaten)
            ->groupBy('kw.id_kegiatan_wilayah, mkd.nama_kegiatan_detail, mkdp.nama_kegiatan_detail_proses, mkdp.tanggal_mulai')
            ->having('COUNT(pml.id_pml) >', 0)
            ->get()
            ->getResultArray();

        // Get semua kegiatan (sebagai target)
        $allKegiatan = $this->kegiatanWilayahModel->getByKabupatenAndAdmin($idKabupaten, $idAdminKabupaten);

        return $this->response->setJSON([
            'success' => true,
            'kegiatan_source' => $kegiatanWithAssignment,
            'kegiatan_target' => $allKegiatan,
            'csrf_hash' => csrf_hash()
        ]);
    }

    // Method untuk preview konfigurasi yang akan dicopy
    public function previewCopyConfiguration()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'error' => 'Invalid request']);
        }

        $sobatId = session()->get('sobat_id');
        $admin = $this->adminKabModel->where('sobat_id', $sobatId)->first();

        if (!$admin) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Unauthorized',
                'csrf_hash' => csrf_hash()
            ]);
        }

        // Ambil dari request body JSON
        $json = $this->request->getJSON();
        $idKegiatanSource = $json->id_kegiatan_source ?? null;

        if (!$idKegiatanSource) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'ID Kegiatan Source tidak ditemukan',
                'csrf_hash' => csrf_hash()
            ]);
        }

        // Get PML dan PCL dari kegiatan source
        $pmlList = $this->pmlModel->db->table('pml')
            ->select('pml.*, u.nama_user as nama_pml')
            ->join('sipantau_user u', 'pml.sobat_id = u.sobat_id')
            ->where('pml.id_kegiatan_wilayah', $idKegiatanSource)
            ->get()
            ->getResultArray();

        $preview = [];
        foreach ($pmlList as $pml) {
            $pclList = $this->pclModel->db->table('pcl')
                ->select('pcl.*, u.nama_user as nama_pcl')
                ->join('sipantau_user u', 'pcl.sobat_id = u.sobat_id')
                ->where('pcl.id_pml', $pml['id_pml'])
                ->get()
                ->getResultArray();

            $preview[] = [
                'pml' => $pml,
                'pcl' => $pclList
            ];
        }

        return $this->response->setJSON([
            'success' => true,
            'preview' => $preview,
            'csrf_hash' => csrf_hash()
        ]);
    }

    // Method untuk execute copy configuration
    public function executeCopyConfiguration()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'error' => 'Invalid request']);
        }

        $sobatId = session()->get('sobat_id');
        $admin = $this->adminKabModel->db->table('admin_survei_kabupaten ask')
            ->select('ask.*, u.id_kabupaten')
            ->join('sipantau_user u', 'ask.sobat_id = u.sobat_id')
            ->where('ask.sobat_id', $sobatId)
            ->get()
            ->getRowArray();

        if (!$admin) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized',
                'csrf_hash' => csrf_hash()
            ]);
        }

        // Ambil dari request body JSON
        $json = $this->request->getJSON();
        $idKegiatanSource = $json->id_kegiatan_source ?? null;
        $idKegiatanTarget = $json->id_kegiatan_target ?? null;

        $idKabupaten = $admin['id_kabupaten'];
        $idAdminKabupaten = $admin['id_admin_kabupaten'];

        // Validasi kegiatan
        if (!$idKegiatanSource || !$idKegiatanTarget) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID Kegiatan tidak lengkap',
                'csrf_hash' => csrf_hash()
            ]);
        }

        if ($idKegiatanSource == $idKegiatanTarget) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Kegiatan sumber dan target tidak boleh sama',
                'csrf_hash' => csrf_hash()
            ]);
        }

        // Cek akses ke kedua kegiatan
        $isSourceAssigned = $this->kegiatanWilayahAdminModel->isAssigned($idAdminKabupaten, $idKegiatanSource);
        $isTargetAssigned = $this->kegiatanWilayahAdminModel->isAssigned($idAdminKabupaten, $idKegiatanTarget);

        if (!$isSourceAssigned || !$isTargetAssigned) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke salah satu kegiatan',
                'csrf_hash' => csrf_hash()
            ]);
        }

        // Get kegiatan target untuk validasi
        $kegiatanTarget = $this->kegiatanWilayahModel->find($idKegiatanTarget);
        if (!$kegiatanTarget || $kegiatanTarget['id_kabupaten'] != $idKabupaten) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Kegiatan target tidak valid',
                'csrf_hash' => csrf_hash()
            ]);
        }

        // Hitung sisa target kegiatan target
        $targetWilayahTarget = (int) $kegiatanTarget['target_wilayah'];
        $totalTargetPMLExisting = (int) $this->pmlModel->db->table('pml')
            ->selectSum('target', 'total_target')
            ->where('id_kegiatan_wilayah', $idKegiatanTarget)
            ->get()
            ->getRow()
            ->total_target ?? 0;
        $sisaTarget = $targetWilayahTarget - $totalTargetPMLExisting;

        // Get PML dan PCL dari kegiatan source
        $pmlSource = $this->pmlModel->where('id_kegiatan_wilayah', $idKegiatanSource)->findAll();

        // Hitung total target yang akan dicopy
        $totalTargetCopy = array_sum(array_column($pmlSource, 'target'));

        if ($totalTargetCopy > $sisaTarget) {
            return $this->response->setJSON([
                'success' => false,
                'message' => "Total target yang akan dicopy ({$totalTargetCopy}) melebihi sisa target kegiatan tujuan ({$sisaTarget})",
                'csrf_hash' => csrf_hash()
            ]);
        }

        // Get detail proses untuk generate kurva
        $detailProses = $this->prosesModel->find($kegiatanTarget['id_kegiatan_detail_proses']);

        $this->pmlModel->db->transStart();

        try {
            $copiedPML = 0;
            $copiedPCL = 0;

            foreach ($pmlSource as $pml) {
                // Cek apakah PML sudah ada di kegiatan target
                $existingPML = $this->pmlModel->db->table('pml')
                    ->where('sobat_id', $pml['sobat_id'])
                    ->where('id_kegiatan_wilayah', $idKegiatanTarget)
                    ->get()
                    ->getRowArray();

                if ($existingPML) {
                    continue; // Skip jika sudah ada
                }

                // Insert PML baru
                $newPMLId = $this->pmlModel->insert([
                    'sobat_id' => $pml['sobat_id'],
                    'id_kegiatan_wilayah' => $idKegiatanTarget,
                    'target' => $pml['target'],
                    'status_approval' => 0,
                    'tanggal_approval' => null
                ]);

                $copiedPML++;

                // Get PCL dari PML source
                $pclSource = $this->pclModel->where('id_pml', $pml['id_pml'])->findAll();

                foreach ($pclSource as $pcl) {
                    // Insert PCL baru
                    $newPCLId = $this->pclModel->insert([
                        'sobat_id' => $pcl['sobat_id'],
                        'id_pml' => $newPMLId,
                        'target' => $pcl['target'],
                        'status_approval' => 0,
                        'tanggal_approval' => null
                    ]);

                    // Generate Kurva S untuk PCL
                    $this->generateKurvaPetugas(
                        $newPCLId,
                        $pcl['target'],
                        $detailProses['persentase_target_awal'],
                        $detailProses['tanggal_mulai'],
                        $detailProses['tanggal_selesai_target'],
                        $detailProses['tanggal_selesai']
                    );

                    $copiedPCL++;
                }
            }

            $this->pmlModel->db->transComplete();

            if ($this->pmlModel->db->transStatus() === false) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat menyalin konfigurasi',
                    'csrf_hash' => csrf_hash()
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => "Berhasil menyalin konfigurasi!<br>PML: <strong>{$copiedPML}</strong><br>PCL: <strong>{$copiedPCL}</strong>",
                'csrf_hash' => csrf_hash()
            ]);

        } catch (\Exception $e) {
            $this->pmlModel->db->transRollback();
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'csrf_hash' => csrf_hash()
            ]);
        }
    }
}