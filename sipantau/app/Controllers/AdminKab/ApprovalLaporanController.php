<?php

namespace App\Controllers\AdminKab;

use App\Controllers\BaseController;
use App\Models\AdminSurveiKabupatenModel;
use App\Models\PMLModel;
use App\Models\PCLModel;
use App\Models\MasterKegiatanWilayahModel;
use App\Models\KegiatanWilayahAdminModel;

class ApprovalLaporanController extends BaseController
{
    protected $adminKabModel;
    protected $pmlModel;
    protected $pclModel;
    protected $kegiatanWilayahModel;
    protected $kegiatanWilayahAdminModel;
    protected $db;

    public function __construct()
    {
        $this->adminKabModel = new AdminSurveiKabupatenModel();
        $this->pmlModel = new PMLModel();
        $this->pclModel = new PCLModel();
        $this->kegiatanWilayahModel = new MasterKegiatanWilayahModel();
        $this->kegiatanWilayahAdminModel = new KegiatanWilayahAdminModel();
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        $sobatId = session()->get('sobat_id');

        if (!$sobatId) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu');
        }

        // Get admin data
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

        // Get filter parameters
        $filterKegiatan = $this->request->getGet('kegiatan_wilayah');
        $filterStatus = $this->request->getGet('status');

        // Ambil perPage dari GET, default 10
        $perPage = $this->request->getGet('perPage') ?? 10;

        // Validasi perPage
        $allowedPerPage = [5, 10, 25, 50, 100];
        if (!in_array((int) $perPage, $allowedPerPage)) {
            $perPage = 10;
        }

        // Get kegiatan wilayah yang di-assign ke admin ini
        $kegiatanWilayahList = $this->kegiatanWilayahModel->getByKabupatenAndAdmin(
            $idKabupaten,
            $idAdminKabupaten
        );

        // Get PML data dengan status approval dan pagination
        $pmlData = $this->getPMLForApprovalPaginated(
            $idKabupaten,
            $idAdminKabupaten,
            $filterKegiatan,
            $filterStatus,
            $perPage
        );

        $data = [
            'title' => 'Approval Laporan',
            'active_menu' => 'approval-laporan',
            'admin' => $admin,
            'kegiatanWilayahList' => $kegiatanWilayahList,
            'pmlData' => $pmlData['data'],
            'filterKegiatan' => $filterKegiatan,
            'filterStatus' => $filterStatus,
            'perPage' => $perPage,
            'pager' => $pmlData['pager'],
        ];

        return view('AdminSurveiKab/ApprovalLaporan/index', $data);
    }

    /**
     * Get PML yang bisa diapprove beserta statusnya
     */
    private function getPMLForApproval($idKabupaten, $idAdminKabupaten, $filterKegiatan = null, $filterStatus = null)
    {
        $builder = $this->db->table('pml p')
            ->select('p.id_pml, p.target as target_pml, p.status_approval as pml_status_approval, 
                     p.tanggal_approval, p.feedback_admin,
                     u.nama_user as nama_pml, u.email, u.hp,
                     kw.id_kegiatan_wilayah, kw.target_wilayah,
                     mkdp.nama_kegiatan_detail_proses, mkdp.tanggal_mulai, mkdp.tanggal_selesai,
                     mkd.nama_kegiatan_detail, mk.nama_kegiatan')
            ->join('sipantau_user u', 'p.sobat_id = u.sobat_id')
            ->join('kegiatan_wilayah kw', 'p.id_kegiatan_wilayah = kw.id_kegiatan_wilayah')
            ->join('kegiatan_wilayah_admin kwa', 'kw.id_kegiatan_wilayah = kwa.id_kegiatan_wilayah')
            ->join('master_kegiatan_detail_proses mkdp', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses')
            ->join('master_kegiatan_detail mkd', 'mkdp.id_kegiatan_detail = mkd.id_kegiatan_detail')
            ->join('master_kegiatan mk', 'mkd.id_kegiatan = mk.id_kegiatan')
            ->where('kw.id_kabupaten', $idKabupaten)
            ->where('kwa.id_admin_kabupaten', $idAdminKabupaten)
            ->orderBy('mkdp.tanggal_mulai', 'DESC')
            ->orderBy('p.created_at', 'DESC');

        if ($filterKegiatan) {
            $builder->where('kw.id_kegiatan_wilayah', $filterKegiatan);
        }

        $pmlList = $builder->get()->getResultArray();

        // Process each PML
        foreach ($pmlList as &$pml) {
            // Get all PCL under this PML
            $pclList = $this->db->table('pcl')
                ->select('pcl.*, u.nama_user')
                ->join('sipantau_user u', 'pcl.sobat_id = u.sobat_id')
                ->where('pcl.id_pml', $pml['id_pml'])
                ->get()
                ->getResultArray();

            $totalPCL = count($pclList);
            $pclCompleted = 0;
            $pclApproved = 0;
            $totalProgress = 0;

            foreach ($pclList as $pcl) {
                // Check if PCL target is 100%
                $realisasi = $this->db->table('pantau_progress')
                    ->select('MAX(jumlah_realisasi_kumulatif) as total_realisasi')
                    ->where('id_pcl', $pcl['id_pcl'])
                    ->get()
                    ->getRowArray();

                $totalRealisasi = (int) ($realisasi['total_realisasi'] ?? 0);
                $targetPCL = (int) $pcl['target'];
                $progressPCL = $targetPCL > 0 ? round(($totalRealisasi / $targetPCL) * 100, 2) : 0;

                $totalProgress += $progressPCL;

                if ($progressPCL >= 100) {
                    $pclCompleted++;
                }

                if ($pcl['status_approval'] == 1) {
                    $pclApproved++;
                }
            }

            $pml['total_pcl'] = $totalPCL;
            $pml['pcl_completed'] = $pclCompleted;
            $pml['pcl_approved'] = $pclApproved;
            $pml['average_progress'] = $totalPCL > 0 ? round($totalProgress / $totalPCL, 2) : 0;

            // Determine eligibility for approval
            $pml['is_eligible'] = ($totalPCL > 0 && $pclCompleted === $totalPCL && $pclApproved === $totalPCL);

            // Set status label
            if ($pml['pml_status_approval'] == 1) {
                $pml['status_label'] = 'Sudah Disetujui';
                $pml['status_class'] = 'badge-success';
            } elseif ($pml['is_eligible']) {
                $pml['status_label'] = 'Siap Disetujui';
                $pml['status_class'] = 'badge-info';
            } else {
                $pml['status_label'] = 'Belum Memenuhi Syarat';
                $pml['status_class'] = 'badge-warning';
            }

            $pml['pcl_list'] = $pclList;
        }

        // Apply status filter
        if ($filterStatus) {
            $pmlList = array_filter($pmlList, function ($pml) use ($filterStatus) {
                if ($filterStatus == 'approved') {
                    return $pml['pml_status_approval'] == 1;
                } elseif ($filterStatus == 'eligible') {
                    return $pml['is_eligible'] && $pml['pml_status_approval'] != 1;
                } elseif ($filterStatus == 'not_eligible') {
                    return !$pml['is_eligible'] && $pml['pml_status_approval'] != 1;
                }
                return true;
            });
        }

        return array_values($pmlList);
    }

    /**
     * Detail PML untuk approval
     */
    public function detail($idPML)
    {
        // Set JSON response header
        $this->response->setContentType('application/json');

        try {
            if (!$this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Invalid request method'
                ]);
            }

            $sobatId = session()->get('sobat_id');

            if (!$sobatId) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Session expired. Please login again.'
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
                    'message' => 'Unauthorized'
                ]);
            }

            // Get PML details
            $pml = $this->db->table('pml p')
                ->select('p.*, u.nama_user as nama_pml, u.email, u.hp,
                         kw.id_kegiatan_wilayah, kw.target_wilayah, kw.id_kabupaten,
                         mkdp.nama_kegiatan_detail_proses, mkdp.tanggal_mulai, mkdp.tanggal_selesai,
                         mk.nama_kegiatan,
                         kab.nama_kabupaten')
                ->join('sipantau_user u', 'p.sobat_id = u.sobat_id')
                ->join('kegiatan_wilayah kw', 'p.id_kegiatan_wilayah = kw.id_kegiatan_wilayah')
                ->join('master_kegiatan_detail_proses mkdp', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses')
                ->join('master_kegiatan_detail mkd', 'mkdp.id_kegiatan_detail = mkd.id_kegiatan_detail')
                ->join('master_kegiatan mk', 'mkd.id_kegiatan = mk.id_kegiatan')
                ->join('master_kabupaten kab', 'kw.id_kabupaten = kab.id_kabupaten')
                ->where('p.id_pml', $idPML)
                ->get()
                ->getRowArray();

            if (!$pml) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'PML tidak ditemukan'
                ]);
            }

            // Check access
            $hasAccess = $this->kegiatanWilayahAdminModel->isAssigned(
                $admin['id_admin_kabupaten'],
                $pml['id_kegiatan_wilayah']
            );

            if (!$hasAccess) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke kegiatan ini'
                ]);
            }

            // Get PCL list with detailed progress
            $pclList = $this->db->table('pcl')
                ->select('pcl.*, u.nama_user')
                ->join('sipantau_user u', 'pcl.sobat_id = u.sobat_id')
                ->where('pcl.id_pml', $idPML)
                ->get()
                ->getResultArray();

            foreach ($pclList as &$pcl) {
                $realisasi = $this->db->table('pantau_progress')
                    ->select('MAX(jumlah_realisasi_kumulatif) as total_realisasi')
                    ->where('id_pcl', $pcl['id_pcl'])
                    ->get()
                    ->getRowArray();

                $totalRealisasi = (int) ($realisasi['total_realisasi'] ?? 0);
                $targetPCL = (int) $pcl['target'];
                $progressPCL = $targetPCL > 0 ? round(($totalRealisasi / $targetPCL) * 100, 2) : 0;

                $pcl['realisasi'] = $totalRealisasi;
                $pcl['progress'] = min(100, $progressPCL);
                $pcl['is_completed'] = $progressPCL >= 100;
            }

            $pml['pcl_list'] = $pclList;

            return $this->response->setJSON([
                'success' => true,
                'data' => $pml
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error in ApprovalLaporanController::detail - ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Approve PML
     */
    public function approve()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        try {
            $sobatId = session()->get('sobat_id');

            if (!$sobatId) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Session expired. Please login again.'
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
                    'message' => 'Unauthorized'
                ]);
            }

            $idPML = $this->request->getPost('id_pml');
            $feedback = $this->request->getPost('feedback');

            if (!$idPML) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'ID PML tidak valid'
                ]);
            }

            $pml = $this->db->table('pml p')
                ->select('p.*, kw.id_kegiatan_wilayah')
                ->join('kegiatan_wilayah kw', 'p.id_kegiatan_wilayah = kw.id_kegiatan_wilayah')
                ->where('p.id_pml', $idPML)
                ->get()
                ->getRowArray();

            if (!$pml) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'PML tidak ditemukan'
                ]);
            }

            if ($pml['status_approval'] == 1) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'PML sudah disetujui sebelumnya'
                ]);
            }

            $hasAccess = $this->kegiatanWilayahAdminModel->isAssigned(
                $admin['id_admin_kabupaten'],
                $pml['id_kegiatan_wilayah']
            );

            if (!$hasAccess) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke kegiatan ini'
                ]);
            }

            $pclList = $this->pclModel->where('id_pml', $idPML)->findAll();

            if (empty($pclList)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'PML ini belum memiliki PCL'
                ]);
            }

            foreach ($pclList as $pcl) {
                if ($pcl['status_approval'] != 1) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Semua PCL harus sudah disetujui oleh PML terlebih dahulu'
                    ]);
                }

                $realisasi = $this->db->table('pantau_progress')
                    ->select('MAX(jumlah_realisasi_kumulatif) as total_realisasi')
                    ->where('id_pcl', $pcl['id_pcl'])
                    ->get()
                    ->getRowArray();

                $totalRealisasi = (int) ($realisasi['total_realisasi'] ?? 0);
                $targetPCL = (int) $pcl['target'];
                $progressPCL = $targetPCL > 0 ? ($totalRealisasi / $targetPCL) * 100 : 0;

                if ($progressPCL < 100) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Semua PCL harus sudah mencapai target 100%'
                    ]);
                }
            }

            $updateResult = $this->pmlModel->update($idPML, [
                'status_approval' => 1,
                'tanggal_approval' => date('Y-m-d H:i:s'),
                'feedback_admin' => $feedback
            ]);

            if (!$updateResult) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Gagal menyimpan approval'
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Laporan PML berhasil disetujui'
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Reject PML approval
     */
    public function reject()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
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
                'message' => 'Unauthorized'
            ]);
        }

        $idPML = $this->request->getPost('id_pml');
        $feedback = $this->request->getPost('feedback');

        if (!$idPML || empty($feedback)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Alasan penolakan harus diisi'
            ]);
        }

        // Get PML
        $pml = $this->pmlModel->find($idPML);
        if (!$pml) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'PML tidak ditemukan'
            ]);
        }

        // Check access
        $pmlDetails = $this->pmlModel->getPMLWithDetails($idPML);
        $hasAccess = $this->kegiatanWilayahAdminModel->isAssigned(
            $admin['id_admin_kabupaten'],
            $pmlDetails['id_kegiatan_wilayah']
        );

        if (!$hasAccess) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke kegiatan ini'
            ]);
        }

        // Update PML
        $this->pmlModel->update($idPML, [
            'status_approval' => 0,
            'tanggal_approval' => null,
            'feedback_admin' => $feedback
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Laporan PML ditolak'
        ]);
    }

    /**
     * Get PML yang bisa diapprove beserta statusnya dengan pagination
     */
    private function getPMLForApprovalPaginated($idKabupaten, $idAdminKabupaten, $filterKegiatan = null, $filterStatus = null, $perPage = 10)
    {
        // Get all PML data first (without pagination) untuk filtering
        $allPMLData = $this->getPMLForApproval($idKabupaten, $idAdminKabupaten, $filterKegiatan, $filterStatus);

        // Manual pagination
        $page = $this->request->getGet('page_pml') ?? 1;
        $offset = ($page - 1) * $perPage;
        $total = count($allPMLData);
        $totalPages = ceil($total / $perPage);

        // Slice data untuk pagination
        $paginatedData = array_slice($allPMLData, $offset, $perPage);

        // Create pager manually
        $pager = new \stdClass();
        $pager->currentPage = (int) $page;
        $pager->perPage = (int) $perPage;
        $pager->total = $total;
        $pager->totalPages = $totalPages;

        return [
            'data' => $paginatedData,
            'pager' => $pager
        ];
    }
}