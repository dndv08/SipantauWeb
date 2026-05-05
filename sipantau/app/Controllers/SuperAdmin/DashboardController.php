<?php

namespace App\Controllers\SuperAdmin;

use App\Controllers\BaseController;
use App\Models\MasterKegiatanDetailModel;
use App\Models\MasterKegiatanDetailProsesModel;
use App\Models\MasterKegiatanWilayahModel;
use App\Models\KepatuhanModel;
use App\Models\KurvaSProvinsiModel;
use App\Models\PantauProgressModel;
use App\Models\PCLModel;
use App\Models\PMLModel;

class DashboardController extends BaseController
{
    protected $masterKegiatanDetailModel;
    protected $masterKegiatanDetailProsesModel;
    protected $kepatuhanModel;
    protected $kegiatanDetailProsesModel;
    protected $kurvaSProvinsiModel;
    protected $pantauProgressModel;
    protected $pclModel;
    protected $pmlModel;
    protected $db;


    public function __construct()
    {
        $this->masterKegiatanDetailModel = new MasterKegiatanDetailModel();
        $this->masterKegiatanDetailProsesModel = new MasterKegiatanDetailProsesModel();
        $this->masterKegiatanWilayahModel = new MasterKegiatanWilayahModel();
        $this->kurvaSProvinsiModel = new KurvaSProvinsiModel();
        $this->pantauProgressModel = new PantauProgressModel();
        $this->kepatuhanModel = new KepatuhanModel();
        $this->pclModel = new PCLModel();
        $this->pmlModel = new PMLModel();
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        // Get statistik dashboard
        $stats = $this->getDashboardStats();

        // Get kegiatan detail proses untuk filter
        $kegiatanDetailProses = $this->masterKegiatanDetailProsesModel
            ->select('master_kegiatan_detail_proses.*, master_kegiatan_detail.nama_kegiatan_detail')
            ->join('master_kegiatan_detail', 'master_kegiatan_detail.id_kegiatan_detail = master_kegiatan_detail_proses.id_kegiatan_detail')
            ->orderBy('master_kegiatan_detail_proses.tanggal_mulai', 'DESC')
            ->findAll();

        // Get progress kegiatan yang sedang berjalan
        $progressKegiatan = $this->getProgressKegiatanBerjalan();

        $data = [
            'title' => 'Dashboard',
            'active_menu' => 'dashboard',
            'stats' => $stats,
            'kegiatanDetailProses' => $kegiatanDetailProses,
            'progressKegiatan' => $progressKegiatan
        ];

        return view('SuperAdmin/dashboard', $data);
    }

    private function getDashboardStats()
    {
        // Total Pengguna
        $totalPengguna = $this->db->table('sipantau_user')
            ->where('is_active', 1)
            ->countAllResults();

        // Total Kegiatan (dari master_kegiatan_detail)
        $totalKegiatan = $this->masterKegiatanDetailModel->countAll();

        // Kegiatan Aktif (kegiatan yang sedang berjalan berdasarkan tanggal)
        $today = date('Y-m-d');
        $kegiatanAktif = $this->db->table('master_kegiatan_detail_proses')
            ->where('tanggal_mulai <=', $today)
            ->where('tanggal_selesai >=', $today)
            ->countAllResults();

        // Target Tercapai (rata-rata persentase dari semua kegiatan detail)
        $targetTercapai = $this->calculateOverallProgress();

        return [
            'total_pengguna' => $totalPengguna,
            'total_kegiatan' => $totalKegiatan,
            'kegiatan_aktif' => $kegiatanAktif,
            'target_tercapai' => round($targetTercapai, 0)
        ];
    }

    private function calculateOverallProgress()
    {
        $kegiatanDetails = $this->masterKegiatanDetailModel->findAll();

        if (empty($kegiatanDetails)) {
            return 0;
        }

        $totalProgress = 0;
        $countKegiatan = 0;

        foreach ($kegiatanDetails as $detail) {
            // Get semua proses dari kegiatan detail ini
            $prosesList = $this->masterKegiatanDetailProsesModel
                ->where('id_kegiatan_detail', $detail['id_kegiatan_detail'])
                ->findAll();

            foreach ($prosesList as $proses) {
                $targetTotal = (int) $proses['target'];

                if ($targetTotal > 0) {
                    $realisasiTotal = $this->getRealisasiByProses($proses['id_kegiatan_detail_proses']);
                    $progress = ($realisasiTotal / $targetTotal) * 100;
                    $totalProgress += min(100, $progress);
                    $countKegiatan++;
                }
            }
        }

        return $countKegiatan > 0 ? ($totalProgress / $countKegiatan) : 0;
    }

    // ======================================================
    // FIXED: Get Realisasi By Proses (Subquery Logic)
    // ======================================================
    private function getRealisasiByProses($idProses)
    {
        // Menggunakan subquery untuk mengambil nilai MAX kumulatif per PCL
        // agar tidak menjumlahkan history record berulang kali
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

    private function getProgressKegiatanBerjalan()
    {
        $kegiatanDetailProses = $this->masterKegiatanDetailProsesModel
            ->select('master_kegiatan_detail_proses.*, master_kegiatan_detail.nama_kegiatan_detail, master_kegiatan.nama_kegiatan')
            ->join('master_kegiatan_detail', 'master_kegiatan_detail.id_kegiatan_detail = master_kegiatan_detail_proses.id_kegiatan_detail')
            ->join('master_kegiatan', 'master_kegiatan.id_kegiatan = master_kegiatan_detail.id_kegiatan')
            ->orderBy('master_kegiatan_detail_proses.created_at', 'DESC')
            ->limit(4)
            ->findAll();

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

    // AJAX endpoint untuk get kurva S
    public function getKurvaS()
    {
        $idProses = $this->request->getGet('id_kegiatan_detail_proses');
        $idWilayah = $this->request->getGet('id_kegiatan_wilayah');

        if (!$idProses) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID Proses tidak valid'
            ]);
        }

        // Get detail proses
        $detailProses = $this->masterKegiatanDetailProsesModel->find($idProses);

        if (!$detailProses) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Data proses tidak ditemukan'
            ]);
        }

        // Get data kurva target
        // Jika ada filter wilayah, ambil dari kurva_kabupaten, jika tidak dari kurva_provinsi
        if ($idWilayah && $idWilayah != 'all') {
            $kurvaTarget = $this->db->table('kurva_kabupaten')
                ->where('id_kegiatan_wilayah', $idWilayah)
                ->orderBy('tanggal_target', 'ASC')
                ->get()->getResultArray();
        } else {
            $kurvaTarget = $this->kurvaSProvinsiModel
                ->where('id_kegiatan_detail_proses', $idProses)
                ->orderBy('tanggal_target', 'ASC')
                ->findAll();
        }

        // Get data realisasi (Raw Data)
        $realisasiData = $this->getRealisasiData($idProses, $idWilayah);

        // Format data untuk chart (State Tracking)
        $chartData = $this->formatKurvaData($kurvaTarget, $realisasiData, $detailProses);

        return $this->response->setJSON([
            'success' => true,
            'data' => $chartData
        ]);
    }

    // ======================================================
    // FIXED: Get Realisasi Data (Raw Data per PCL)
    // ======================================================
    private function getRealisasiData($idProses, $idWilayah = null)
    {
        $builder = $this->db->table('pantau_progress pp')
            ->select('pp.id_pcl, pp.jumlah_realisasi_kumulatif, DATE(pp.created_at) as tanggal')
            ->join('pcl', 'pp.id_pcl = pcl.id_pcl')
            ->join('pml', 'pcl.id_pml = pml.id_pml')
            ->join('kegiatan_wilayah kw', 'pml.id_kegiatan_wilayah = kw.id_kegiatan_wilayah');

        if ($idWilayah && $idWilayah != 'all') {
            $builder->where('kw.id_kegiatan_wilayah', $idWilayah);
        } else {
            $builder->where('kw.id_kegiatan_detail_proses', $idProses);
        }

        // Order by created_at ASC is crucial for processing history
        $builder->orderBy('pp.created_at', 'ASC');

        return $builder->get()->getResultArray();
    }

    // ======================================================
    // FIXED: Format Kurva Data (State Tracking Logic)
    // ======================================================
    private function formatKurvaData($kurvaTarget, $rawRealisasi, $detailProses)
    {
        if (empty($kurvaTarget)) {
            return [
                'labels' => [],
                'target' => [],
                'realisasi' => [],
                'stats' => ['target' => 0, 'realisasi' => 0, 'persentase' => 0, 'selisih' => 0],
                'config' => [
                    'nama' => $detailProses['nama_kegiatan_detail_proses'],
                    'tanggal_mulai' => date('d', strtotime($detailProses['tanggal_mulai'])),
                    'tanggal_selesai' => date('d', strtotime($detailProses['tanggal_selesai']))
                ]
            ];
        }

        // 1. Group raw realization by date and PCL
        // $reportsByDate['2023-01-01']['pcl_id'] = 50
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

        // 2. Prepare timelines
        $uniqueTarget = [];
        foreach ($kurvaTarget as $row) {
            $uniqueTarget[$row['tanggal_target']] = $row;
        }
        ksort($uniqueTarget);

        $labels = [];
        $targetData = [];
        $realisasiDataFormatted = [];
        
        // 3. State Tracking Variables
        $pclCurrentStatus = []; // Holds the last known value for each PCL
        $today = date('Y-m-d');

        // 4. Loop through timeline
        foreach ($uniqueTarget as $tanggal => $row) {
            $labels[] = date('d M', strtotime($tanggal));
            
            // Target
            $targetVal = (int) $row['target_kumulatif_absolut'];
            // Ensure monotonic increase for target
            if (!empty($targetData) && end($targetData) > $targetVal) {
                $targetVal = end($targetData);
            }
            $targetData[] = $targetVal;

            // Realization Logic
            if ($tanggal <= $today) {
                // Update PCL states if they reported on this date
                if (isset($reportsByDate[$tanggal])) {
                    foreach ($reportsByDate[$tanggal] as $pclId => $val) {
                        $pclCurrentStatus[$pclId] = $val;
                    }
                }
                
                // Sum current states of all PCLs
                $totalRealisasiHariIni = array_sum($pclCurrentStatus);
                $realisasiDataFormatted[] = $totalRealisasiHariIni;
            }
        }

        // Calculate stats
        $totalTarget = !empty($targetData) ? end($targetData) : 0;
        $totalRealisasi = !empty($realisasiDataFormatted) ? end($realisasiDataFormatted) : 0;
        $persentase = $totalTarget > 0 ? round(($totalRealisasi / $totalTarget) * 100, 1) : 0;
        $selisih = $totalRealisasi - $totalTarget;

        return [
            'labels' => $labels,
            'target' => $targetData,
            'realisasi' => $realisasiDataFormatted,
            'stats' => [
                'target' => $totalTarget,
                'realisasi' => $totalRealisasi,
                'persentase' => $persentase,
                'selisih' => $selisih
            ],
            'config' => [
                'nama' => $detailProses['nama_kegiatan_detail_proses'],
                'tanggal_mulai' => date('d', strtotime($detailProses['tanggal_mulai'])),
                'tanggal_selesai' => date('d', strtotime($detailProses['tanggal_selesai']))
            ]
        ];
    }

    // AJAX endpoint untuk get wilayah by proses
    public function getKegiatanWilayah()
    {
        $idProses = $this->request->getGet('id_kegiatan_detail_proses');

        if (!$idProses) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID Proses tidak valid'
            ]);
        }

        $wilayah = $this->db->table('kegiatan_wilayah kw')
            ->select('kw.id_kegiatan_wilayah, mk.nama_kabupaten')
            ->join('master_kabupaten mk', 'mk.id_kabupaten = kw.id_kabupaten')
            ->where('kw.id_kegiatan_detail_proses', $idProses)
            ->orderBy('mk.nama_kabupaten', 'ASC')
            ->get()
            ->getResultArray();

        return $this->response->setJSON([
            'success' => true,
            'data' => $wilayah
        ]);
    }

    // AJAX endpoint untuk get petugas by kegiatan wilayah WITH PAGINATION
    public function getPetugas()
    {
        $idWilayah = $this->request->getGet('id_kegiatan_wilayah');
        $idProses = $this->request->getGet('id_kegiatan_detail_proses');
        $page = $this->request->getGet('page') ?? 1;
        $perPage = $this->request->getGet('perPage') ?? 10;
        $search = $this->request->getGet('search') ?? '';

        if (!$idProses) {
            return $this->response->setJSON([
                'success' => true,
                'data' => [],
                'pagination' => ['total' => 0]
            ]);
        }

        // Get detail kegiatan untuk cek tanggal mulai dan selesai
        $detailProses = $this->masterKegiatanDetailProsesModel->find($idProses);

        if (!$detailProses) {
            return $this->response->setJSON([
                'success' => true,
                'data' => [],
                'pagination' => ['total' => 0]
            ]);
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
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'ID Kegiatan Detail Proses diperlukan'
                ]);
            }

            $stats = $this->kepatuhanModel->getStatistikKepatuhan(
                $idKegiatanDetailProses,
                $idKegiatanWilayah
            );

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

            $leaderboard = $this->kepatuhanModel->getLeaderboardKepatuhan(
                $idKegiatanDetailProses,
                $idKegiatanWilayah,
                10
            );

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

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }
}