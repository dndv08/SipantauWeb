<?php

namespace App\Controllers\AdminKab;

use App\Controllers\BaseController;
use App\Models\AdminSurveiKabupatenModel;
use App\Models\MasterKegiatanDetailProsesModel;
use App\Models\MasterKegiatanWilayahModel;
use App\Models\KegiatanWilayahAdminModel;
use App\Models\KurvaSkabModel;
use App\Models\PantauProgressModel;
use App\Models\PCLModel;
use App\Models\PMLModel;

class DashboardController extends BaseController
{
    protected $adminKabModel;
    protected $prosesModel;
    protected $kegiatanWilayahModel;
    protected $kegiatanWilayahAdminModel;
    protected $kurvaKabModel;
    protected $pantauProgressModel;
    protected $pclModel;
    protected $pmlModel;
    protected $db;

    public function __construct()
    {
        $this->adminKabModel = new AdminSurveiKabupatenModel();
        $this->prosesModel = new MasterKegiatanDetailProsesModel();
        $this->kegiatanWilayahModel = new MasterKegiatanWilayahModel();
        $this->kegiatanWilayahAdminModel = new KegiatanWilayahAdminModel();
        $this->kurvaKabModel = new KurvaSkabModel();
        $this->pantauProgressModel = new PantauProgressModel();
        $this->pclModel = new PCLModel();
        $this->pmlModel = new PMLModel();
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

        // Get statistik dashboard
        $stats = $this->getDashboardStats($idKabupaten, $idAdminKabupaten);

        // Get kegiatan detail proses yang di-assign ke admin ini
        // Data ini akan memuat nama_kegiatan_detail untuk dropdown filter
        $kegiatanList = $this->getAssignedKegiatan($idKabupaten, $idAdminKabupaten);

        $latest = !empty($kegiatanList) ? $kegiatanList[0] : null;
        $latestKegiatanId = $latest ? $latest['id_kegiatan_detail_proses'] : '';

        // Get progress kegiatan yang sedang berjalan
        // Data ini sudah diformat namanya di dalam method
        $progressKegiatan = $this->getProgressKegiatanBerjalan($idKabupaten, $idAdminKabupaten);

        $data = [
            'title' => 'Dashboard',
            'active_menu' => 'dashboard',
            'admin' => $admin,
            'stats' => $stats,
            'kegiatanList' => $kegiatanList,
            'latestKegiatanId' => $latestKegiatanId,
            'progressKegiatan' => $progressKegiatan,
            'kegiatanDetailProses' => $kegiatanList
        ];

        return view('AdminSurveiKab/dashboard', $data);
    }

    // Get Dashboard Stats
    private function getDashboardStats($idKabupaten, $idAdminKabupaten)
    {
        $totalKegiatan = $this->db->query("
            SELECT COUNT(DISTINCT kw.id_kegiatan_detail_proses) as total
            FROM kegiatan_wilayah kw
            JOIN kegiatan_wilayah_admin kwa ON kwa.id_kegiatan_wilayah = kw.id_kegiatan_wilayah
            WHERE kw.id_kabupaten = ?
            AND kwa.id_admin_kabupaten = ?
        ", [$idKabupaten, $idAdminKabupaten])->getRowArray();

        $today = date('Y-m-d');
        $kegiatanAktif = $this->db->query("
            SELECT COUNT(DISTINCT kdp.id_kegiatan_detail_proses) as total
            FROM master_kegiatan_detail_proses kdp
            JOIN kegiatan_wilayah kw ON kw.id_kegiatan_detail_proses = kdp.id_kegiatan_detail_proses
            JOIN kegiatan_wilayah_admin kwa ON kwa.id_kegiatan_wilayah = kw.id_kegiatan_wilayah
            WHERE kw.id_kabupaten = ?
            AND kwa.id_admin_kabupaten = ?
            AND kdp.tanggal_mulai <= ?
            AND kdp.tanggal_selesai >= ?
        ", [$idKabupaten, $idAdminKabupaten, $today, $today])->getRowArray();

        $targetTercapai = $this->calculateOverallProgress($idKabupaten, $idAdminKabupaten);

        // Sub-SLS Monitoring Stats
        $totalSubSlsInKab = $this->db->table('master_sub_sls mss')
            ->where('mss.id_sub_sls LIKE', $idKabupaten . '%')
            ->countAllResults();

        $assignedActivitiesCount = (int) ($totalKegiatan['total'] ?? 0);
        $totalRequiredAssignments = $totalSubSlsInKab * $assignedActivitiesCount;

        $totalAssigned = $this->db->table('assignment_sub_sls ass')
            ->join('kegiatan_wilayah kw', 'ass.id_kegiatan_wilayah = kw.id_kegiatan_wilayah')
            ->join('kegiatan_wilayah_admin kwa', 'kwa.id_kegiatan_wilayah = kw.id_kegiatan_wilayah')
            ->where('ass.id_sub_sls LIKE', $idKabupaten . '%')
            ->where('kwa.id_admin_kabupaten', $idAdminKabupaten)
            ->countAllResults();

        return [
            'total_kegiatan' => (int) ($totalKegiatan['total'] ?? 0),
            'kegiatan_aktif' => (int) ($kegiatanAktif['total'] ?? 0),
            'target_tercapai' => round($targetTercapai, 0),
            'sub_sls_total' => $totalSubSlsInKab,
            'assignment_total' => $totalRequiredAssignments,
            'assignment_done' => $totalAssigned,
            'assignment_pending' => max(0, $totalRequiredAssignments - $totalAssigned)
        ];
    }

    // Calculate Overall Progress
    private function calculateOverallProgress($idKabupaten, $idAdminKabupaten)
    {
        $kegiatanWilayah = $this->db->query("
            SELECT kw.id_kegiatan_wilayah, kw.target_wilayah
            FROM kegiatan_wilayah kw
            JOIN kegiatan_wilayah_admin kwa ON kwa.id_kegiatan_wilayah = kw.id_kegiatan_wilayah
            WHERE kw.id_kabupaten = ?
            AND kwa.id_admin_kabupaten = ?
            AND kw.target_wilayah > 0
        ", [$idKabupaten, $idAdminKabupaten])->getResultArray();

        if (empty($kegiatanWilayah)) {
            return 0;
        }

        $totalProgress = 0;
        $countKegiatan = 0;

        foreach ($kegiatanWilayah as $kegiatan) {
            $targetTotal = (int) $kegiatan['target_wilayah'];
            $realisasiTotal = $this->getRealisasiByKegiatanWilayah($kegiatan['id_kegiatan_wilayah']);

            if ($targetTotal > 0) {
                $progress = ($realisasiTotal / $targetTotal) * 100;
                $totalProgress += min(100, $progress);
                $countKegiatan++;
            }
        }

        return $countKegiatan > 0 ? ($totalProgress / $countKegiatan) : 0;
    }

    // Get Realisasi by Kegiatan Wilayah (Subquery logic)
    private function getRealisasiByKegiatanWilayah($idKegiatanWilayah)
    {
        $result = $this->db->query("
            SELECT SUM(latest_realisasi) as total_realisasi
            FROM (
                SELECT MAX(pp.jumlah_realisasi_kumulatif) as latest_realisasi
                FROM pantau_progress pp
                JOIN pcl ON pp.id_pcl = pcl.id_pcl
                JOIN pml ON pcl.id_pml = pml.id_pml
                WHERE pml.id_kegiatan_wilayah = ?
                GROUP BY pp.id_pcl
            ) as subquery
        ", [$idKegiatanWilayah])->getRowArray();

        return (int) ($result['total_realisasi'] ?? 0);
    }

    // ======================================================
    // MODIFIED: Get Assigned Kegiatan (Join master_kegiatan_detail)
    // ======================================================
    private function getAssignedKegiatan($idKabupaten, $idAdminKabupaten)
    {
        return $this->db->query("
            SELECT DISTINCT 
                kdp.id_kegiatan_detail_proses, 
                kdp.nama_kegiatan_detail_proses,
                mkd.nama_kegiatan_detail -- Ambil nama kegiatan induk
            FROM master_kegiatan_detail_proses kdp
            JOIN master_kegiatan_detail mkd ON kdp.id_kegiatan_detail = mkd.id_kegiatan_detail -- Join table
            JOIN kegiatan_wilayah kw ON kw.id_kegiatan_detail_proses = kdp.id_kegiatan_detail_proses
            JOIN kegiatan_wilayah_admin kwa ON kwa.id_kegiatan_wilayah = kw.id_kegiatan_wilayah
            WHERE kw.id_kabupaten = ?
            AND kwa.id_admin_kabupaten = ?
            ORDER BY kdp.id_kegiatan_detail_proses DESC
        ", [$idKabupaten, $idAdminKabupaten])->getResultArray();
    }

    // ======================================================
    // MODIFIED: Get Progress Kegiatan Berjalan (Join & Format Name)
    // ======================================================
    private function getProgressKegiatanBerjalan($idKabupaten, $idAdminKabupaten)
    {
        $kegiatan = $this->db->query("
            SELECT 
                kdp.id_kegiatan_detail_proses, 
                kdp.nama_kegiatan_detail_proses, 
                mkd.nama_kegiatan_detail, -- Ambil nama kegiatan induk
                kdp.created_at,
                kw.id_kegiatan_wilayah, 
                kw.target_wilayah
            FROM master_kegiatan_detail_proses kdp
            JOIN master_kegiatan_detail mkd ON kdp.id_kegiatan_detail = mkd.id_kegiatan_detail -- Join table
            JOIN kegiatan_wilayah kw ON kw.id_kegiatan_detail_proses = kdp.id_kegiatan_detail_proses
            JOIN kegiatan_wilayah_admin kwa ON kwa.id_kegiatan_wilayah = kw.id_kegiatan_wilayah
            WHERE kw.id_kabupaten = ?
            AND kwa.id_admin_kabupaten = ?
            GROUP BY kdp.id_kegiatan_detail_proses, 
                     kdp.nama_kegiatan_detail_proses, 
                     mkd.nama_kegiatan_detail,
                     kdp.created_at,
                     kw.id_kegiatan_wilayah, 
                     kw.target_wilayah
            ORDER BY kdp.created_at DESC
            LIMIT 4
        ", [$idKabupaten, $idAdminKabupaten])->getResultArray();

        $progressData = [];
        $colors = ['#1e88e5', '#43a047', '#fdd835', '#8e24aa', '#e53935', '#5e35b1'];
        $colorIndex = 0;

        foreach ($kegiatan as $item) {
            $targetTotal = (int) $item['target_wilayah'];
            $realisasiTotal = $this->getRealisasiByKegiatanWilayah($item['id_kegiatan_wilayah']);

            if ($targetTotal > 0) {
                $progress = ($realisasiTotal / $targetTotal) * 100;

                $progressData[] = [
                    // Format Nama: Nama Detail (Nama Proses)
                    'nama' => $item['nama_kegiatan_detail'] . ' (' . $item['nama_kegiatan_detail_proses'] . ')',
                    'progress' => min(100, round($progress, 0)),
                    'color' => $colors[$colorIndex % count($colors)]
                ];

                $colorIndex++;
            }
        }

        return $progressData;
    }

    // Get Kurva S
    public function getKurvaS()
    {
        $idProses = $this->request->getGet('id_kegiatan_detail_proses');
        $sobatId = session()->get('sobat_id');

        if (!$sobatId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized'
            ]);
        }

        // Get admin data
        $admin = $this->adminKabModel->db->table('admin_survei_kabupaten ask')
            ->select('ask.*, u.id_kabupaten')
            ->join('sipantau_user u', 'ask.sobat_id = u.sobat_id')
            ->where('ask.sobat_id', $sobatId)
            ->get()
            ->getRowArray();

        if (!$admin) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Admin not found'
            ]);
        }

        // Get kegiatan wilayah untuk kabupaten ini yang di-assign
        $kegiatanWilayah = $this->db->query("
            SELECT kw.*
            FROM kegiatan_wilayah kw
            JOIN kegiatan_wilayah_admin kwa ON kwa.id_kegiatan_wilayah = kw.id_kegiatan_wilayah
            WHERE kw.id_kegiatan_detail_proses = ?
            AND kw.id_kabupaten = ?
            AND kwa.id_admin_kabupaten = ?
        ", [$idProses, $admin['id_kabupaten'], $admin['id_admin_kabupaten']])->getRowArray();

        if (!$kegiatanWilayah) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Kegiatan tidak ditemukan atau tidak di-assign'
            ]);
        }

        // Get kurva target
        $kurvaTarget = $this->kurvaKabModel
            ->where('id_kegiatan_wilayah', $kegiatanWilayah['id_kegiatan_wilayah'])
            ->orderBy('tanggal_target', 'ASC')
            ->findAll();

        // Get realisasi data (Raw Data)
        $realisasiData = $this->getRealisasiData($kegiatanWilayah['id_kegiatan_wilayah']);

        // MODIFIED: Get detail proses with join to get full name
        $detailProses = $this->prosesModel
            ->select('master_kegiatan_detail_proses.*, mkd.nama_kegiatan_detail')
            ->join('master_kegiatan_detail mkd', 'master_kegiatan_detail_proses.id_kegiatan_detail = mkd.id_kegiatan_detail')
            ->find($idProses);

        // Format data untuk chart (State Tracking)
        $chartData = $this->formatKurvaData($kurvaTarget, $realisasiData, $detailProses);

        return $this->response->setJSON([
            'success' => true,
            'data' => $chartData
        ]);
    }

    // Get Realisasi Data (Ambil Raw Data History)
    private function getRealisasiData($idKegiatanWilayah)
    {
        return $this->db->table('pantau_progress pp')
            ->select('pp.id_pcl, pp.jumlah_realisasi_kumulatif, DATE(pp.created_at) as tanggal')
            ->join('pcl', 'pp.id_pcl = pcl.id_pcl')
            ->join('pml', 'pcl.id_pml = pml.id_pml')
            ->where('pml.id_kegiatan_wilayah', $idKegiatanWilayah)
            ->orderBy('pp.created_at', 'ASC')
            ->get()->getResultArray();
    }

    // Format Kurva Data (State Tracking Logic)
    private function formatKurvaData($kurvaTarget, $rawRealisasi, $detailProses)
    {
        if (empty($kurvaTarget)) {
            return [
                'labels' => [],
                'target' => [],
                'realisasi' => [],
                'config' => [
                    'nama' => $detailProses['nama_kegiatan_detail_proses'] ?? '',
                    'tanggal_mulai' => date('d', strtotime($detailProses['tanggal_mulai'])),
                    'tanggal_selesai' => date('d', strtotime($detailProses['tanggal_selesai']))
                ]
            ];
        }

        $reportsByDate = [];
        foreach ($rawRealisasi as $row) {
            $tgl = $row['tanggal'];
            $pclId = $row['id_pcl'];
            $val = (int) $row['jumlah_realisasi_kumulatif'];
            
            if (!isset($reportsByDate[$tgl])) {
                $reportsByDate[$tgl] = [];
            }
            $reportsByDate[$tgl][$pclId] = $val;
        }

        $uniqueTarget = [];
        foreach ($kurvaTarget as $row) {
            $uniqueTarget[$row['tanggal_target']] = $row;
        }
        ksort($uniqueTarget);

        $labels = [];
        $targetData = [];
        $realisasiDataFormatted = [];
        
        $pclCurrentStatus = [];
        $today = date('Y-m-d');

        foreach ($uniqueTarget as $tanggal => $row) {
            $labels[] = date('d M', strtotime($tanggal));
            
            $targetVal = (int) $row['target_kumulatif_absolut'];
            if (!empty($targetData) && end($targetData) > $targetVal) {
                $targetVal = end($targetData);
            }
            $targetData[] = $targetVal;

            if ($tanggal <= $today) {
                if (isset($reportsByDate[$tanggal])) {
                    foreach ($reportsByDate[$tanggal] as $pclId => $val) {
                        $pclCurrentStatus[$pclId] = $val;
                    }
                }
                $totalRealisasiHariIni = array_sum($pclCurrentStatus);
                $realisasiDataFormatted[] = $totalRealisasiHariIni;
            }
        }

        return [
            'labels' => array_values($labels),
            'target' => array_values($targetData),
            'realisasi' => array_values($realisasiDataFormatted),
            'config' => [
                // MODIFIED: Format Nama Chart
                'nama' => ($detailProses['nama_kegiatan_detail'] ?? '') . ' (' . ($detailProses['nama_kegiatan_detail_proses'] ?? '') . ')',
                'tanggal_mulai' => date('d', strtotime($detailProses['tanggal_mulai'])),
                'tanggal_selesai' => date('d', strtotime($detailProses['tanggal_selesai']))
            ]
        ];
    }

    // Get Petugas
    public function getPetugas()
    {
        $idProses = $this->request->getGet('id_kegiatan_detail_proses');
        $page = $this->request->getGet('page') ?? 1;
        $perPage = $this->request->getGet('perPage') ?? 10;
        $search = $this->request->getGet('search') ?? '';
        $sobatId = session()->get('sobat_id');

        if (!$sobatId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $admin = $this->adminKabModel->db->table('admin_survei_kabupaten ask')
            ->select('ask.*, u.id_kabupaten')
            ->join('sipantau_user u', 'ask.sobat_id = u.sobat_id')
            ->where('ask.sobat_id', $sobatId)
            ->get()
            ->getRowArray();

        if (!$admin) {
            return $this->response->setJSON(['success' => false, 'message' => 'Admin not found']);
        }

        $detailProses = $this->prosesModel->find($idProses);

        if (!$detailProses) {
            return $this->response->setJSON(['success' => true, 'data' => [], 'pagination' => ['total' => 0]]);
        }

        $tanggalMulai = $detailProses['tanggal_mulai'];
        $tanggalSelesai = $detailProses['tanggal_selesai'];
        $today = date('Y-m-d');

        $statusKegiatanGlobal = 'Belum Dimulai';
        if ($today >= $tanggalMulai && $today <= $tanggalSelesai) {
            $statusKegiatanGlobal = 'Sedang Berjalan';
        } elseif ($today > $tanggalSelesai) {
            $statusKegiatanGlobal = 'Selesai';
        }

        $kegiatanWilayah = $this->db->query("
            SELECT kw.*
            FROM kegiatan_wilayah kw
            JOIN kegiatan_wilayah_admin kwa ON kwa.id_kegiatan_wilayah = kw.id_kegiatan_wilayah
            WHERE kw.id_kegiatan_detail_proses = ?
            AND kw.id_kabupaten = ?
            AND kwa.id_admin_kabupaten = ?
        ", [$idProses, $admin['id_kabupaten'], $admin['id_admin_kabupaten']])->getRowArray();

        if (!$kegiatanWilayah) {
            return $this->response->setJSON(['success' => true, 'data' => [], 'pagination' => ['total' => 0]]);
        }

        $baseQuery = "
            SELECT 
                u.nama_user, u.sobat_id, pcl.id_pcl, mk.nama_kabupaten, pcl.target,
                COALESCE(MAX(pp.jumlah_realisasi_kumulatif), 0) as realisasi_total,
                'PCL' as role
            FROM pcl
            JOIN sipantau_user u ON pcl.sobat_id = u.sobat_id
            JOIN pml ON pcl.id_pml = pml.id_pml
            JOIN kegiatan_wilayah kw ON pml.id_kegiatan_wilayah = kw.id_kegiatan_wilayah
            JOIN master_kabupaten mk ON kw.id_kabupaten = mk.id_kabupaten
            LEFT JOIN pantau_progress pp ON pp.id_pcl = pcl.id_pcl
            WHERE kw.id_kegiatan_wilayah = ?
        ";

        $params = [$kegiatanWilayah['id_kegiatan_wilayah']];

        if (!empty($search)) {
            $baseQuery .= " AND (u.nama_user LIKE ? OR u.sobat_id LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $countQuery = "
            SELECT COUNT(DISTINCT pcl.id_pcl) as total
            FROM pcl
            JOIN sipantau_user u ON pcl.sobat_id = u.sobat_id
            JOIN pml ON pcl.id_pml = pml.id_pml
            JOIN kegiatan_wilayah kw ON pml.id_kegiatan_wilayah = kw.id_kegiatan_wilayah
            WHERE kw.id_kegiatan_wilayah = ?
        ";

        $countParams = [$kegiatanWilayah['id_kegiatan_wilayah']];

        if (!empty($search)) {
            $countQuery .= " AND (u.nama_user LIKE ? OR u.sobat_id LIKE ?)";
            $countParams[] = "%{$search}%";
            $countParams[] = "%{$search}%";
        }

        $totalRecords = $this->db->query($countQuery, $countParams)->getRowArray()['total'] ?? 0;
        $totalPages = ceil($totalRecords / $perPage);
        $offset = ($page - 1) * $perPage;

        $query = $baseQuery . "
            GROUP BY pcl.id_pcl, u.nama_user, u.sobat_id, mk.nama_kabupaten, pcl.target
            ORDER BY u.nama_user
            LIMIT {$perPage} OFFSET {$offset}
        ";

        $petugas = $this->db->query($query, $params)->getResultArray();

        foreach ($petugas as &$p) {
            $target = (int) $p['target'];
            $realisasiTotal = (int) $p['realisasi_total'];
            $progress = $target > 0 ? round(($realisasiTotal / $target) * 100, 0) : 0;
            $p['progress'] = min(100, $progress);
            $p['status_kegiatan'] = $statusKegiatanGlobal;
            $p['status_kegiatan_class'] = $this->getStatusKegiatanClass($statusKegiatanGlobal);

            $statusHarian = $this->getStatusHarian($p['id_pcl'], $statusKegiatanGlobal, $today, $target);
            $p['status_harian'] = $statusHarian['text'];
            $p['status_harian_class'] = $statusHarian['class'];
            $p['realisasi_hari_ini'] = $statusHarian['realisasi_hari_ini'];
            $p['target_harian'] = $statusHarian['target_harian'];
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $petugas,
            'pagination' => [
                'total' => $totalRecords,
                'per_page' => (int) $perPage,
                'current_page' => (int) $page,
                'total_pages' => $totalPages
            ]
        ]);
    }

    private function getStatusKegiatanClass($status)
    {
        switch ($status) {
            case 'Sedang Berjalan': return 'badge-success';
            case 'Belum Dimulai': return 'badge-warning';
            case 'Selesai': return 'badge-secondary';
            default: return 'badge-secondary';
        }
    }

    private function getStatusHarian($idPCL, $statusKegiatan, $today, $targetTotal)
    {
        if ($statusKegiatan !== 'Sedang Berjalan') {
            return ['text' => 'Tidak Perlu Lapor', 'class' => 'badge-secondary', 'realisasi_hari_ini' => 0, 'target_harian' => 0];
        }

        $laporanHariIni = $this->db->query("
            SELECT jumlah_realisasi_absolut FROM pantau_progress
            WHERE id_pcl = ? AND DATE(created_at) = ?
            ORDER BY created_at DESC LIMIT 1
        ", [$idPCL, $today])->getRowArray();

        $targetHarian = $this->db->query("
            SELECT target_harian_absolut FROM kurva_petugas
            WHERE id_pcl = ? AND tanggal_target = ? AND is_hari_kerja = 1
        ", [$idPCL, $today])->getRowArray();

        $targetHarianValue = $targetHarian ? (int) $targetHarian['target_harian_absolut'] : 0;

        if (!$laporanHariIni) {
            return ['text' => 'Belum Lapor', 'class' => 'badge-danger', 'realisasi_hari_ini' => 0, 'target_harian' => $targetHarianValue];
        }

        $realisasiHariIni = (int) $laporanHariIni['jumlah_realisasi_absolut'];

        if ($targetHarianValue === 0) {
            return ['text' => 'Sudah Lapor', 'class' => 'badge-success', 'realisasi_hari_ini' => $realisasiHariIni, 'target_harian' => 0];
        }

        if ($realisasiHariIni < $targetHarianValue) {
            return ['text' => 'Di Bawah Target', 'class' => 'badge-warning', 'realisasi_hari_ini' => $realisasiHariIni, 'target_harian' => $targetHarianValue];
        } elseif ($realisasiHariIni > $targetHarianValue) {
            return ['text' => 'Melebihi Target', 'class' => 'badge-info', 'realisasi_hari_ini' => $realisasiHariIni, 'target_harian' => $targetHarianValue];
        } else {
            return ['text' => 'Sesuai Target', 'class' => 'badge-success', 'realisasi_hari_ini' => $realisasiHariIni, 'target_harian' => $targetHarianValue];
        }
    }

    public function getKepatuhanData()
    {
        try {
            $idKegiatanDetailProses = $this->request->getGet('id_kegiatan_detail_proses');
            $sobatId = session()->get('sobat_id');

            if (!$idKegiatanDetailProses || !$sobatId) {
                return $this->response->setJSON(['success' => false, 'message' => 'Parameter tidak lengkap']);
            }

            $admin = $this->adminKabModel->db->table('admin_survei_kabupaten ask')
                ->select('ask.*, u.id_kabupaten')
                ->join('sipantau_user u', 'ask.sobat_id = u.sobat_id')
                ->where('ask.sobat_id', $sobatId)
                ->get()->getRowArray();

            if (!$admin) {
                return $this->response->setJSON(['success' => false, 'message' => 'Admin tidak ditemukan']);
            }

            $kegiatanWilayah = $this->db->query("
                SELECT kw.* FROM kegiatan_wilayah kw
                JOIN kegiatan_wilayah_admin kwa ON kwa.id_kegiatan_wilayah = kw.id_kegiatan_wilayah
                WHERE kw.id_kegiatan_detail_proses = ? AND kw.id_kabupaten = ? AND kwa.id_admin_kabupaten = ?
            ", [$idKegiatanDetailProses, $admin['id_kabupaten'], $admin['id_admin_kabupaten']])->getRowArray();

            if (!$kegiatanWilayah) {
                return $this->response->setJSON(['success' => false, 'message' => 'Anda tidak memiliki akses ke kegiatan ini']);
            }

            $kepatuhanModel = new \App\Models\KepatuhanModel();
            $stats = $kepatuhanModel->getStatistikKepatuhan($idKegiatanDetailProses, null, $admin['id_kabupaten']);
            $chartData = $kepatuhanModel->getTrendKepatuhanHarian($idKegiatanDetailProses, $admin['id_kabupaten']);
            $leaderboard = $kepatuhanModel->getLeaderboardKepatuhan($idKegiatanDetailProses, null, 10, $admin['id_kabupaten']);
            $tidakPatuh = $kepatuhanModel->getPetugasTidakPatuh($idKegiatanDetailProses, null, $admin['id_kabupaten']);

            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'stats' => $stats,
                    'chart' => ['type' => 'line', 'data' => $chartData],
                    'leaderboard' => $leaderboard,
                    'tidak_patuh' => $tidakPatuh
                ]
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error in getKepatuhanData: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }
}