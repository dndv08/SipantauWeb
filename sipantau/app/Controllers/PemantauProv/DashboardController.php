<?php

namespace App\Controllers\PemantauProv;

use App\Models\KurvaSProvinsiModel;
use App\Models\KurvaSkabModel;
use App\Models\MasterKegiatanDetailProsesModel;
use App\Models\MasterKegiatanWilayahModel;
use App\Models\MasterKegiatanDetailAdminModel;
use App\Models\MasterKegiatanDetailModel;
use App\Models\PantauProgressModel;
use CodeIgniter\Controller;

class DashboardController extends Controller
{
    protected $db;
    protected $kepatuhanModel;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->kepatuhanModel = new \App\Models\KepatuhanModel();
    }

    public function index()
    {
        // Get role dari session
        $role = session()->get('role');
        $roleType = session()->get('role_type');

        $isPemantauProvinsi = ($role == 2 && $roleType == 'pemantau_provinsi');

        if (!$isPemantauProvinsi) {
            return redirect()->to(base_url('unauthorized'))
                ->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }

        // Get statistik dashboard - SEMUA kegiatan
        $stats = $this->getDashboardStats();

        // Dropdown kegiatan proses - SEMUA kegiatan (tidak ada filter)
        $kegiatanList = $this->db->table('master_kegiatan_detail_proses kdp')
            ->select('kdp.id_kegiatan_detail_proses, kdp.nama_kegiatan_detail_proses, mkd.nama_kegiatan_detail')
            ->join('master_kegiatan_detail mkd', 'mkd.id_kegiatan_detail = kdp.id_kegiatan_detail')
            ->orderBy('kdp.id_kegiatan_detail_proses', 'DESC')
            ->get()
            ->getResultArray();

        $latest = !empty($kegiatanList) ? $kegiatanList[0] : null;
        $latestKegiatanId = $latest ? $latest['id_kegiatan_detail_proses'] : '';

        // Get progress kegiatan yang sedang berjalan - SEMUA kegiatan
        $progressKegiatan = $this->getProgressKegiatanBerjalan();

        $data = [
            'title' => 'Dashboard',
            'active_menu' => 'dashboard',
            'stats' => $stats,
            'kegiatanList' => $kegiatanList,
            'latestKegiatanId' => $latestKegiatanId,
            'progressKegiatan' => $progressKegiatan,
            'kegiatanDetailProses' => $kegiatanList
        ];

        return view('PemantauProvinsi/dashboard', $data);
    }

    // ======================================================
    // GET DASHBOARD STATS
    // ======================================================
    private function getDashboardStats()
    {
        // Total Kegiatan - SEMUA kegiatan
        $totalKegiatan = $this->db->table('master_kegiatan_detail_proses')
            ->countAllResults();

        // Kegiatan Aktif - SEMUA kegiatan yang sedang berjalan
        $today = date('Y-m-d');
        $kegiatanAktif = $this->db->query("
            SELECT COUNT(*) as total
            FROM master_kegiatan_detail_proses kdp
            WHERE kdp.tanggal_mulai <= ?
            AND kdp.tanggal_selesai >= ?
        ", [$today, $today])->getRowArray();

        // Target Tercapai - rata-rata dari SEMUA kegiatan
        $targetTercapai = $this->calculateOverallProgress();

        return [
            'total_kegiatan' => $totalKegiatan ?? 0,
            'kegiatan_aktif' => (int) ($kegiatanAktif['total'] ?? 0),
            'target_tercapai' => round($targetTercapai, 0)
        ];
    }

    // ======================================================
    // CALCULATE OVERALL PROGRESS
    // ======================================================
    private function calculateOverallProgress()
    {
        // Ambil SEMUA kegiatan proses
        $prosesList = $this->db->query("
            SELECT id_kegiatan_detail_proses, target
            FROM master_kegiatan_detail_proses
            WHERE target > 0
        ")->getResultArray();

        if (empty($prosesList)) {
            return 0;
        }

        $totalProgress = 0;
        $countKegiatan = 0;

        foreach ($prosesList as $proses) {
            $targetTotal = (int) $proses['target'];
            $realisasiTotal = $this->getRealisasiByProses($proses['id_kegiatan_detail_proses']);

            if ($targetTotal > 0) {
                $progress = ($realisasiTotal / $targetTotal) * 100;
                $totalProgress += min(100, $progress);
                $countKegiatan++;
            }
        }

        return $countKegiatan > 0 ? ($totalProgress / $countKegiatan) : 0;
    }

    // ======================================================
    // GET REALISASI BY PROSES
    // ======================================================
    private function getRealisasiByProses($idProses)
    {
        // Menggunakan subquery untuk mengambil nilai kumulatif terakhir per PCL agar lebih akurat untuk statistik card
        $result = $this->db->query("
            SELECT SUM(latest_realisasi) as total_realisasi
            FROM (
                SELECT MAX(pp.jumlah_realisasi_kumulatif) as latest_realisasi
                FROM pantau_progress pp
                JOIN pcl ON pp.id_pcl = pcl.id_pcl
                JOIN pml ON pcl.id_pml = pml.id_pml
                JOIN kegiatan_wilayah kw ON pml.id_kegiatan_wilayah = kw.id_kegiatan_wilayah
                WHERE kw.id_kegiatan_detail_proses = ?
                GROUP BY pp.id_pcl
            ) as subquery
        ", [$idProses])->getRowArray();

        return (int) ($result['total_realisasi'] ?? 0);
    }

    // ======================================================
    // GET PROGRESS KEGIATAN BERJALAN
    // ======================================================
    private function getProgressKegiatanBerjalan()
    {
        // Ambil SEMUA kegiatan detail proses (tidak ada filter)
        $kegiatanDetailProses = $this->db->query("
            SELECT mkdp.*, mkd.nama_kegiatan_detail, mk.nama_kegiatan
            FROM master_kegiatan_detail_proses mkdp
            JOIN master_kegiatan_detail mkd ON mkd.id_kegiatan_detail = mkdp.id_kegiatan_detail
            JOIN master_kegiatan mk ON mk.id_kegiatan = mkd.id_kegiatan
            ORDER BY mkdp.created_at DESC
            LIMIT 4
        ")->getResultArray();

        $progressData = [];
        $colors = ['#1e88e5', '#43a047', '#fdd835', '#8e24aa', '#e53935', '#5e35b1'];
        $colorIndex = 0;

        foreach ($kegiatanDetailProses as $proses) {
            $target = (int) $proses['target'];
            $realisasi = $this->getRealisasiByProses($proses['id_kegiatan_detail_proses']);

            if ($target > 0) {
                $progress = ($realisasi / $target) * 100;

                $progressData[] = [
                    'nama' => $proses['nama_kegiatan_detail'] . ' (' . $proses['nama_kegiatan_detail_proses'] . ')',
                    'progress' => min(100, round($progress, 0)),
                    'color' => $colors[$colorIndex % count($colors)]
                ];

                $colorIndex++;
            }
        }

        return $progressData;
    }

    // ======================================================
    // KEGIATAN WILAYAH DROPDOWN
    // ======================================================
    public function getKegiatanWilayah()
    {
        $idProses = $this->request->getGet('id_kegiatan_detail_proses');

        // Pemantau Provinsi bisa melihat SEMUA kegiatan wilayah
        $wilayahModel = new MasterKegiatanWilayahModel();
        $records = $wilayahModel
            ->select('kegiatan_wilayah.id_kegiatan_wilayah, master_kabupaten.nama_kabupaten')
            ->join('master_kabupaten', 'master_kabupaten.id_kabupaten = kegiatan_wilayah.id_kabupaten', 'left')
            ->where('kegiatan_wilayah.id_kegiatan_detail_proses', $idProses)
            ->findAll();

        return $this->response->setJSON($records);
    }

    // ======================================================
    // GET KURVA S WITH REALISASI (LOGIC DIPERBAIKI)
    // ======================================================
    public function getKurvaSWithRealisasi()
    {
        $idProses = $this->request->getGet('id_kegiatan_detail_proses');
        $idWilayah = $this->request->getGet('id_kegiatan_wilayah');

        if (!$idProses) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID Proses tidak valid'
            ]);
        }

        // Get detail proses untuk config
        $prosesModel = new MasterKegiatanDetailProsesModel();
        $detailProses = $prosesModel->find($idProses);

        if (!$detailProses) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Data proses tidak ditemukan'
            ]);
        }

        // 1. Ambil Data Target (Plan)
        // Jika filter kabupaten dipilih, ambil kurva_kabupaten. Jika tidak, ambil kurva_provinsi (global).
        if ($idWilayah && $idWilayah != 'all') {
            $kurvaTarget = $this->db->table('kurva_kabupaten')
                ->where('id_kegiatan_wilayah', $idWilayah)
                ->orderBy('tanggal_target', 'ASC')
                ->get()
                ->getResultArray();
        } else {
            $kurvaTarget = $this->db->table('kurva_provinsi')
                ->where('id_kegiatan_detail_proses', $idProses)
                ->orderBy('tanggal_target', 'ASC')
                ->get()
                ->getResultArray();
        }

        // 2. Ambil Data Realisasi Mentah (Raw History)
        $realisasiData = $this->getRealisasiDataForChart($idProses, $idWilayah);

        // 3. Format Data untuk Chart (Merging Target & Realisasi)
        $chartData = $this->formatKurvaDataWithRealisasi($kurvaTarget, $realisasiData, $detailProses);

        return $this->response->setJSON([
            'success' => true,
            'data' => $chartData
        ]);
    }

    // ======================================================
    // GET REALISASI RAW DATA
    // ======================================================
    private function getRealisasiDataForChart($idProses, $idWilayah = null)
    {
        // Mengambil histori pelaporan dari pantau_progress
        $builder = $this->db->table('pantau_progress pp')
            ->select('pp.id_pcl, pp.jumlah_realisasi_kumulatif, DATE(pp.created_at) as tanggal')
            ->join('pcl', 'pp.id_pcl = pcl.id_pcl')
            ->join('pml', 'pcl.id_pml = pml.id_pml')
            ->join('kegiatan_wilayah kw', 'pml.id_kegiatan_wilayah = kw.id_kegiatan_wilayah');

        // Filter Scope
        if ($idWilayah && $idWilayah != 'all') {
            // Filter spesifik satu kabupaten
            $builder->where('kw.id_kegiatan_wilayah', $idWilayah);
        } else {
            // Filter global satu provinsi (semua kabupaten dalam proses ini)
            $builder->where('kw.id_kegiatan_detail_proses', $idProses);
        }

        // Order ASC penting untuk replay history
        $builder->orderBy('pp.created_at', 'ASC');

        return $builder->get()->getResultArray();
    }

    // ======================================================
    // FORMAT KURVA DATA WITH REALISASI (STATE TRACKING)
    // ======================================================
    private function formatKurvaDataWithRealisasi($kurvaTarget, $rawRealisasi, $detailProses)
    {
        if (empty($kurvaTarget)) {
            return [
                'labels' => [],
                'target' => [],
                'realisasi' => [],
                'config' => [
                    'nama' => $detailProses['nama_kegiatan_detail_proses'],
                    'tanggal_mulai' => date('d', strtotime($detailProses['tanggal_mulai'])),
                    'tanggal_selesai' => date('d', strtotime($detailProses['tanggal_selesai']))
                ]
            ];
        }

        // A. Kelompokkan Raw Realisasi berdasarkan Tanggal
        // $reportsByDate['2025-01-01']['id_pcl_1'] = 10;
        $reportsByDate = [];
        foreach ($rawRealisasi as $row) {
            $tgl = $row['tanggal'];
            $pclId = $row['id_pcl'];
            $val = (int) $row['jumlah_realisasi_kumulatif'];
            
            // Simpan nilai kumulatif terakhir PCL pada tanggal tersebut
            if (!isset($reportsByDate[$tgl])) {
                $reportsByDate[$tgl] = [];
            }
            $reportsByDate[$tgl][$pclId] = $val;
        }

        // B. Siapkan Loop Timeline berdasarkan Tanggal Target
        $uniqueTarget = [];
        foreach ($kurvaTarget as $row) {
            $uniqueTarget[$row['tanggal_target']] = $row;
        }
        ksort($uniqueTarget);

        $labels = [];
        $targetData = [];
        $realisasiDataFormatted = [];
        
        // C. State Tracking: Menyimpan capaian terakhir masing-masing PCL
        // [id_pcl => total_kumulatif_terakhir]
        $pclCurrentStatus = []; 
        $today = date('Y-m-d');

        // D. Loop Timeline
        foreach ($uniqueTarget as $tanggal => $row) {
            // 1. Label & Target
            $labels[] = date('d M', strtotime($tanggal));
            
            $targetVal = (int) $row['target_kumulatif_absolut'];
            
            // Fix Monotonic Increase untuk Target (Plan tidak boleh turun)
            if (!empty($targetData) && end($targetData) > $targetVal) {
                $targetVal = end($targetData);
            }
            $targetData[] = $targetVal;

            // 2. Hitung Realisasi
            if ($tanggal <= $today) {
                // Cek apakah ada update laporan di tanggal ini
                if (isset($reportsByDate[$tanggal])) {
                    foreach ($reportsByDate[$tanggal] as $pclId => $val) {
                        // Update status kumulatif PCL tersebut dengan angka terbaru
                        $pclCurrentStatus[$pclId] = $val;
                    }
                }

                // Total Realisasi = SUM semua capaian terakhir PCL
                $totalRealisasiHariIni = array_sum($pclCurrentStatus);
                $realisasiDataFormatted[] = $totalRealisasiHariIni;
            }
        }

        return [
            'labels' => array_values($labels),
            'target' => array_values($targetData),
            'realisasi' => array_values($realisasiDataFormatted),
            'config' => [
                'nama' => $detailProses['nama_kegiatan_detail_proses'],
                'tanggal_mulai' => date('d', strtotime($detailProses['tanggal_mulai'])),
                'tanggal_selesai' => date('d', strtotime($detailProses['tanggal_selesai']))
            ]
        ];
    }

    // ======================================================
    // GET PETUGAS
    // ======================================================
    public function getPetugas()
    {
        $idWilayah = $this->request->getGet('id_kegiatan_wilayah');
        $idProses = $this->request->getGet('id_kegiatan_detail_proses');
        $page = $this->request->getGet('page') ?? 1;
        $perPage = $this->request->getGet('perPage') ?? 10;
        $search = $this->request->getGet('search') ?? '';

        if (!$idProses) {
            return $this->response->setJSON(['success' => true, 'data' => [], 'pagination' => ['total' => 0]]);
        }

        // Get detail kegiatan untuk cek tanggal mulai dan selesai
        $prosesModel = new MasterKegiatanDetailProsesModel();
        $detailProses = $prosesModel->find($idProses);

        if (!$detailProses) {
            return $this->response->setJSON(['success' => true, 'data' => [], 'pagination' => ['total' => 0]]);
        }

        $tanggalMulai = $detailProses['tanggal_mulai'];
        $tanggalSelesai = $detailProses['tanggal_selesai'];
        $today = date('Y-m-d');

        // Tentukan status kegiatan global
        $statusKegiatanGlobal = 'Belum Dimulai';
        if ($today >= $tanggalMulai && $today <= $tanggalSelesai) {
            $statusKegiatanGlobal = 'Sedang Berjalan';
        } elseif ($today > $tanggalSelesai) {
            $statusKegiatanGlobal = 'Selesai';
        }

        // Build query dasar
        $baseQuery = "
        SELECT 
            u.nama_user,
            u.sobat_id,
            pcl.id_pcl,
            mk.nama_kabupaten,
            pcl.target,
            COALESCE(MAX(pp.jumlah_realisasi_kumulatif), 0) as realisasi_total,
            'PCL' as role
        FROM pcl
        JOIN sipantau_user u ON pcl.sobat_id = u.sobat_id
        JOIN pml ON pcl.id_pml = pml.id_pml
        JOIN kegiatan_wilayah kw ON pml.id_kegiatan_wilayah = kw.id_kegiatan_wilayah
        JOIN master_kabupaten mk ON kw.id_kabupaten = mk.id_kabupaten
        LEFT JOIN pantau_progress pp ON pp.id_pcl = pcl.id_pcl
        ";

        // Where conditions
        $whereConditions = [];
        $params = [];

        if (!$idWilayah || $idWilayah == 'all') {
            $whereConditions[] = "kw.id_kegiatan_detail_proses = ?";
            $params[] = $idProses;
        } else {
            $whereConditions[] = "kw.id_kegiatan_wilayah = ?";
            $params[] = $idWilayah;
        }

        // Search filter
        if (!empty($search)) {
            $whereConditions[] = "(u.nama_user LIKE ? OR u.sobat_id LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

        // Count total records
        $countQuery = "
        SELECT COUNT(DISTINCT pcl.id_pcl) as total
        FROM pcl
        JOIN sipantau_user u ON pcl.sobat_id = u.sobat_id
        JOIN pml ON pcl.id_pml = pml.id_pml
        JOIN kegiatan_wilayah kw ON pml.id_kegiatan_wilayah = kw.id_kegiatan_wilayah
        JOIN master_kabupaten mk ON kw.id_kabupaten = mk.id_kabupaten
        {$whereClause}
        ";

        $totalRecords = $this->db->query($countQuery, $params)->getRowArray()['total'] ?? 0;
        $totalPages = ceil($totalRecords / $perPage);
        $offset = ($page - 1) * $perPage;

        // Get paginated data
        $query = "{$baseQuery}
        {$whereClause}
        GROUP BY pcl.id_pcl, u.nama_user, u.sobat_id, mk.nama_kabupaten, pcl.target
        ORDER BY " . ($idWilayah == 'all' ? 'mk.nama_kabupaten, u.nama_user' : 'u.nama_user') . "
        LIMIT {$perPage} OFFSET {$offset}
        ";

        $petugas = $this->db->query($query, $params)->getResultArray();

        // Process setiap petugas
        foreach ($petugas as &$p) {
            $target = (int) $p['target'];
            $realisasiTotal = (int) $p['realisasi_total'];

            $progress = $target > 0 ? round(($realisasiTotal / $target) * 100, 0) : 0;
            $p['progress'] = min(100, $progress);
            $p['status_kegiatan'] = $statusKegiatanGlobal;
            $p['status_kegiatan_class'] = $this->getStatusKegiatanClass($statusKegiatanGlobal);

            $statusHarian = $this->getStatusHarian(
                $p['id_pcl'],
                $statusKegiatanGlobal,
                $today,
                $target
            );

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

    // Helper: Get Status Kegiatan Class
    private function getStatusKegiatanClass($status)
    {
        switch ($status) {
            case 'Sedang Berjalan':
                return 'badge-success';
            case 'Belum Dimulai':
                return 'badge-warning';
            case 'Selesai':
                return 'badge-secondary';
            default:
                return 'badge-secondary';
        }
    }

    // Helper: Get Status Harian
    private function getStatusHarian($idPCL, $statusKegiatan, $today, $targetTotal)
    {
        if ($statusKegiatan !== 'Sedang Berjalan') {
            return ['text' => 'Tidak Perlu Lapor', 'class' => 'badge-secondary', 'realisasi_hari_ini' => 0, 'target_harian' => 0];
        }

        $laporanHariIni = $this->db->query("
            SELECT jumlah_realisasi_absolut
            FROM pantau_progress
            WHERE id_pcl = ? AND DATE(created_at) = ?
            ORDER BY created_at DESC LIMIT 1
        ", [$idPCL, $today])->getRowArray();

        $targetHarian = $this->db->query("
            SELECT target_harian_absolut
            FROM kurva_petugas
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

    // ======================================================
    // GET KEPATUHAN DATA
    // ======================================================
    public function getKepatuhanData()
    {
        try {
            $idKegiatanDetailProses = $this->request->getGet('id_kegiatan_detail_proses');
            $idKegiatanWilayah = $this->request->getGet('id_kegiatan_wilayah') ?? 'all';

            if (!$idKegiatanDetailProses) {
                return $this->response->setJSON(['success' => false, 'message' => 'ID Kegiatan Detail Proses diperlukan']);
            }

            // 1. Get Statistik
            $stats = $this->kepatuhanModel->getStatistikKepatuhan(
                $idKegiatanDetailProses,
                $idKegiatanWilayah
            );

            // 2. Get Chart Data
            $chartData = [];
            $chartType = 'line';

            if ($idKegiatanWilayah === 'all') {
                $chartData = $this->kepatuhanModel->getKepatuhanPerKabupaten($idKegiatanDetailProses);
                $chartType = 'bar';
            } else {
                $kegiatanWilayah = $this->db->table('kegiatan_wilayah')
                    ->select('id_kabupaten')
                    ->where('id_kegiatan_wilayah', $idKegiatanWilayah)
                    ->get()
                    ->getRowArray();

                if ($kegiatanWilayah) {
                    $chartData = $this->kepatuhanModel->getTrendKepatuhanHarian(
                        $idKegiatanDetailProses,
                        $kegiatanWilayah['id_kabupaten']
                    );
                }
                $chartType = 'line';
            }

            // 3. Get Leaderboard
            $leaderboard = $this->kepatuhanModel->getLeaderboardKepatuhan(
                $idKegiatanDetailProses,
                $idKegiatanWilayah,
                10
            );

            // 4. Get Petugas Tidak Patuh
            $tidakPatuh = $this->kepatuhanModel->getPetugasTidakPatuh(
                $idKegiatanDetailProses,
                $idKegiatanWilayah
            );

            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'stats' => $stats,
                    'chart' => [
                        'type' => $chartType,
                        'data' => $chartData
                    ],
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