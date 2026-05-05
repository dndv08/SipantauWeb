<?php

namespace App\Controllers\PemantauKab;

use App\Models\KurvaSkabModel;
use App\Models\MasterKegiatanDetailProsesModel;
use App\Models\MasterKegiatanWilayahModel;
use App\Models\UserModel;
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
        $userModel = new UserModel();
        $user = $userModel->find($sobatId);

        if (!$user || !$user['id_kabupaten']) {
            return redirect()->to(base_url('unauthorized'))
                ->with('error', 'Data kabupaten tidak ditemukan.');
        }

        $idKabupaten = $user['id_kabupaten'];

        // Simpan ke session untuk digunakan di method lain
        session()->set('user_kabupaten_id', $idKabupaten);

        // Get statistik dashboard - HANYA untuk kabupaten ini
        $stats = $this->getDashboardStats($idKabupaten);

        // Dropdown kegiatan wilayah - HANYA untuk kabupaten ini
        $kegiatanList = $this->getKegiatanWilayahList($idKabupaten);

        $latest = !empty($kegiatanList) ? $kegiatanList[0] : null;
        $latestKegiatanWilayahId = $latest ? $latest['id_kegiatan_wilayah'] : '';

        // Get progress kegiatan yang sedang berjalan - HANYA kabupaten ini
        $progressKegiatan = $this->getProgressKegiatanBerjalan($idKabupaten);

        // Get nama kabupaten untuk ditampilkan
        $kabupaten = $this->db->table('master_kabupaten')
            ->where('id_kabupaten', $idKabupaten)
            ->get()
            ->getRowArray();

        $data = [
            'title' => 'Dashboard Pemantau Kabupaten',
            'active_menu' => 'dashboard',
            'stats' => $stats,
            'kegiatanList' => $kegiatanList,
            'latestKegiatanWilayahId' => $latestKegiatanWilayahId,
            'progressKegiatan' => $progressKegiatan,
            'kabupaten' => $kabupaten,
            'id_kabupaten' => $idKabupaten
        ];

        return view('PemantauKabupaten/dashboard', $data);
    }

    // ======================================================
    // GET DASHBOARD STATS
    // ======================================================
    private function getDashboardStats($idKabupaten)
    {
        // Total Kegiatan - HANYA di kabupaten ini
        $totalKegiatan = $this->db->table('kegiatan_wilayah')
            ->where('id_kabupaten', $idKabupaten)
            ->countAllResults();

        // Kegiatan Aktif - HANYA di kabupaten ini yang sedang berjalan
        $today = date('Y-m-d');
        $kegiatanAktif = $this->db->query("
            SELECT COUNT(*) as total
            FROM kegiatan_wilayah kw
            JOIN master_kegiatan_detail_proses mkdp ON kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses
            WHERE kw.id_kabupaten = ?
            AND mkdp.tanggal_mulai <= ?
            AND mkdp.tanggal_selesai >= ?
        ", [$idKabupaten, $today, $today])->getRowArray();

        // Target Tercapai - rata-rata dari kegiatan di kabupaten ini
        $targetTercapai = $this->calculateOverallProgress($idKabupaten);

        return [
            'total_kegiatan' => $totalKegiatan ?? 0,
            'kegiatan_aktif' => (int) ($kegiatanAktif['total'] ?? 0),
            'target_tercapai' => round($targetTercapai, 0)
        ];
    }

    // ======================================================
    // CALCULATE OVERALL PROGRESS
    // ======================================================
    private function calculateOverallProgress($idKabupaten)
    {
        // Ambil semua kegiatan wilayah di kabupaten ini
        $wilayahList = $this->db->query("
            SELECT id_kegiatan_wilayah, target_wilayah
            FROM kegiatan_wilayah
            WHERE id_kabupaten = ?
            AND target_wilayah > 0
        ", [$idKabupaten])->getResultArray();

        if (empty($wilayahList)) {
            return 0;
        }

        $totalProgress = 0;
        $countKegiatan = 0;

        foreach ($wilayahList as $wilayah) {
            $targetTotal = (int) $wilayah['target_wilayah'];
            $realisasiTotal = $this->getRealisasiByKegiatanWilayah($wilayah['id_kegiatan_wilayah']);

            if ($targetTotal > 0) {
                $progress = ($realisasiTotal / $targetTotal) * 100;
                $totalProgress += min(100, $progress);
                $countKegiatan++;
            }
        }

        return $countKegiatan > 0 ? ($totalProgress / $countKegiatan) : 0;
    }

    // ======================================================
    // GET REALISASI BY KEGIATAN WILAYAH
    // ======================================================
    private function getRealisasiByKegiatanWilayah($idKegiatanWilayah)
    {
        $result = $this->db->query("
            SELECT COALESCE(SUM(pp.jumlah_realisasi_kumulatif), 0) as total_realisasi
            FROM pantau_progress pp
            JOIN pcl ON pp.id_pcl = pcl.id_pcl
            JOIN pml ON pcl.id_pml = pml.id_pml
            WHERE pml.id_kegiatan_wilayah = ?
        ", [$idKegiatanWilayah])->getRowArray();

        return (int) ($result['total_realisasi'] ?? 0);
    }

    // ======================================================
    // GET KEGIATAN WILAYAH LIST
    // ======================================================
    private function getKegiatanWilayahList($idKabupaten)
    {
        return $this->db->query("
            SELECT 
                kw.id_kegiatan_wilayah,
                mkdp.nama_kegiatan_detail_proses,
                mkd.nama_kegiatan_detail,
                mk.nama_kegiatan,
                mkdp.tanggal_mulai,
                mkdp.tanggal_selesai
            FROM kegiatan_wilayah kw
            JOIN master_kegiatan_detail_proses mkdp ON kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses
            JOIN master_kegiatan_detail mkd ON mkdp.id_kegiatan_detail = mkd.id_kegiatan_detail
            JOIN master_kegiatan mk ON mkd.id_kegiatan = mk.id_kegiatan
            WHERE kw.id_kabupaten = ?
            ORDER BY mkdp.tanggal_mulai DESC
        ", [$idKabupaten])->getResultArray();
    }

    // ======================================================
    // GET PROGRESS KEGIATAN BERJALAN
    // ======================================================
    private function getProgressKegiatanBerjalan($idKabupaten)
{
    // Ambil kegiatan wilayah di kabupaten ini (limit 4)
    $kegiatanWilayah = $this->db->query("
        SELECT 
            kw.id_kegiatan_wilayah,
            kw.target_wilayah,
            mkdp.nama_kegiatan_detail_proses,
            mkd.nama_kegiatan_detail
        FROM kegiatan_wilayah kw
        JOIN master_kegiatan_detail_proses mkdp ON kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses
        JOIN master_kegiatan_detail mkd ON mkdp.id_kegiatan_detail = mkd.id_kegiatan_detail
        WHERE kw.id_kabupaten = ?
        ORDER BY mkdp.tanggal_mulai DESC
        LIMIT 4
    ", [$idKabupaten])->getResultArray();

    $progressData = [];
    $colors = ['#1e88e5', '#43a047', '#fdd835', '#8e24aa', '#e53935', '#5e35b1'];
    $colorIndex = 0;

    foreach ($kegiatanWilayah as $wilayah) {
        $targetTotal = (int) $wilayah['target_wilayah'];
        $realisasiTotal = $this->getRealisasiByKegiatanWilayah($wilayah['id_kegiatan_wilayah']);

        if ($targetTotal > 0) {
            $progress = ($realisasiTotal / $targetTotal) * 100;

            $progressData[] = [
                'nama' => $wilayah['nama_kegiatan_detail_proses'],
                'progress' => min(100, round($progress, 0)),
                'color' => $colors[$colorIndex % count($colors)]
            ];

            $colorIndex++;
        }
    }

    return $progressData;
}

    // ======================================================
    // KURVA S KABUPATEN
    // ======================================================
    public function getKurvaKabupaten()
    {
        $idKegiatanWilayah = $this->request->getGet('id_kegiatan_wilayah');
        $idKabupaten = session()->get('user_kabupaten_id');

        // Validasi: pastikan kegiatan wilayah ini milik kabupaten user
        $kegiatan = $this->db->table('kegiatan_wilayah')
            ->where('id_kegiatan_wilayah', $idKegiatanWilayah)
            ->where('id_kabupaten', $idKabupaten)
            ->get()
            ->getRowArray();

        if (!$kegiatan) {
            return $this->response->setJSON([
                'labels' => [],
                'targetPersen' => [],
                'targetAbsolut' => [],
                'targetHarian' => []
            ]);
        }

        $model = new KurvaSkabModel();
        $records = $model
            ->where('id_kegiatan_wilayah', $idKegiatanWilayah)
            ->orderBy('tanggal_target', 'ASC')
            ->findAll();

        return $this->response->setJSON($this->formatKurvaData($records));
    }

    // ======================================================
    // FORMAT KURVA DATA
    // ======================================================
    private function formatKurvaData($records)
    {
        if (empty($records)) {
            return [
                'labels' => [],
                'targetPersen' => [],
                'targetAbsolut' => [],
                'targetHarian' => []
            ];
        }

        $unique = [];
        foreach ($records as $row) {
            $tgl = $row['tanggal_target'];
            if (!isset($unique[$tgl])) {
                $unique[$tgl] = $row;
            }
        }

        ksort($unique);

        $labels = $targetPersen = $targetAbsolut = $targetHarian = [];
        foreach ($unique as $row) {
            $labels[] = date('d M', strtotime($row['tanggal_target']));
            $targetPersen[] = (float) $row['target_persen_kumulatif'];
            $targetAbsolut[] = (int) $row['target_kumulatif_absolut'];
            $targetHarian[] = (int) $row['target_harian_absolut'];
        }

        for ($i = 1; $i < count($targetAbsolut); $i++) {
            if ($targetAbsolut[$i] < $targetAbsolut[$i - 1]) {
                $targetAbsolut[$i] = $targetAbsolut[$i - 1];
            }
        }

        return [
            'labels' => array_values($labels),
            'targetPersen' => array_values($targetPersen),
            'targetAbsolut' => array_values($targetAbsolut),
            'targetHarian' => array_values($targetHarian)
        ];
    }

    // ======================================================
    // GET PETUGAS
    // ======================================================
    public function getPetugas()
    {
        $idKegiatanWilayah = $this->request->getGet('id_kegiatan_wilayah');
        $idKabupaten = session()->get('user_kabupaten_id');

        // Validasi: pastikan kegiatan wilayah ini milik kabupaten user
        $kegiatan = $this->db->table('kegiatan_wilayah kw')
            ->select('kw.*, mkdp.tanggal_mulai, mkdp.tanggal_selesai')
            ->join('master_kegiatan_detail_proses mkdp', 'mkdp.id_kegiatan_detail_proses = kw.id_kegiatan_detail_proses')
            ->where('kw.id_kegiatan_wilayah', $idKegiatanWilayah)
            ->where('kw.id_kabupaten', $idKabupaten)
            ->get()
            ->getRowArray();

        if (!$kegiatan) {
            return $this->response->setJSON([
                'success' => true,
                'data' => []
            ]);
        }

        $tanggalMulai = $kegiatan['tanggal_mulai'];
        $tanggalSelesai = $kegiatan['tanggal_selesai'];
        $today = date('Y-m-d');

        // Tentukan status kegiatan global
        $statusKegiatanGlobal = 'Belum Dimulai';
        if ($today >= $tanggalMulai && $today <= $tanggalSelesai) {
            $statusKegiatanGlobal = 'Sedang Berjalan';
        } elseif ($today > $tanggalSelesai) {
            $statusKegiatanGlobal = 'Selesai';
        }

        $petugas = $this->db->query("
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
            WHERE kw.id_kegiatan_wilayah = ?
            GROUP BY pcl.id_pcl, u.nama_user, u.sobat_id, mk.nama_kabupaten, pcl.target
            ORDER BY u.nama_user
        ", [$idKegiatanWilayah])->getResultArray();

        // Process setiap petugas
        foreach ($petugas as &$p) {
            $target = (int) $p['target'];
            $realisasiTotal = (int) $p['realisasi_total'];

            // Hitung progress keseluruhan
            $progress = $target > 0 ? round(($realisasiTotal / $target) * 100, 0) : 0;
            $p['progress'] = min(100, $progress);

            // Set status kegiatan (sama dengan global)
            $p['status_kegiatan'] = $statusKegiatanGlobal;
            $p['status_kegiatan_class'] = $this->getStatusKegiatanClass($statusKegiatanGlobal);

            // Cek status harian
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
            'data' => $petugas
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
        // Jika kegiatan belum dimulai atau sudah selesai
        if ($statusKegiatan !== 'Sedang Berjalan') {
            return [
                'text' => 'Tidak Perlu Lapor',
                'class' => 'badge-secondary',
                'realisasi_hari_ini' => 0,
                'target_harian' => 0
            ];
        }

        // Cek apakah sudah lapor hari ini
        $laporanHariIni = $this->db->query("
            SELECT jumlah_realisasi_absolut
            FROM pantau_progress
            WHERE id_pcl = ?
            AND DATE(created_at) = ?
            ORDER BY created_at DESC
            LIMIT 1
        ", [$idPCL, $today])->getRowArray();

        // Get target harian dari kurva_petugas
        $targetHarian = $this->db->query("
            SELECT target_harian_absolut
            FROM kurva_petugas
            WHERE id_pcl = ?
            AND tanggal_target = ?
        ", [$idPCL, $today])->getRowArray();

        $targetHarianValue = $targetHarian ? (int) $targetHarian['target_harian_absolut'] : 0;

        // Jika belum lapor
        if (!$laporanHariIni) {
            return [
                'text' => 'Belum Lapor',
                'class' => 'badge-danger',
                'realisasi_hari_ini' => 0,
                'target_harian' => $targetHarianValue
            ];
        }

        $realisasiHariIni = (int) $laporanHariIni['jumlah_realisasi_absolut'];

        // Jika tidak ada target harian (hari libur atau belum ada kurva)
        if ($targetHarianValue === 0) {
            return [
                'text' => 'Sudah Lapor',
                'class' => 'badge-success',
                'realisasi_hari_ini' => $realisasiHariIni,
                'target_harian' => 0
            ];
        }

        // Bandingkan dengan target harian
        if ($realisasiHariIni < $targetHarianValue) {
            return [
                'text' => 'Di Bawah Target',
                'class' => 'badge-warning',
                'realisasi_hari_ini' => $realisasiHariIni,
                'target_harian' => $targetHarianValue
            ];
        } elseif ($realisasiHariIni > $targetHarianValue) {
            return [
                'text' => 'Melebihi Target',
                'class' => 'badge-info',
                'realisasi_hari_ini' => $realisasiHariIni,
                'target_harian' => $targetHarianValue
            ];
        } else {
            return [
                'text' => 'Sesuai Target',
                'class' => 'badge-success',
                'realisasi_hari_ini' => $realisasiHariIni,
                'target_harian' => $targetHarianValue
            ];
        }
    }

    // ======================================================
    // GET KURVA S WITH REALISASI (NEW METHOD)
    // ======================================================
    public function getKurvaSWithRealisasi()
    {
        $idKegiatanWilayah = $this->request->getGet('id_kegiatan_wilayah');
        $idKabupaten = session()->get('user_kabupaten_id');

        // Validasi: pastikan kegiatan wilayah ini milik kabupaten user
        $kegiatan = $this->db->table('kegiatan_wilayah kw')
            ->select('kw.*, mkdp.nama_kegiatan_detail_proses, mkdp.tanggal_mulai, mkdp.tanggal_selesai')
            ->join('master_kegiatan_detail_proses mkdp', 'mkdp.id_kegiatan_detail_proses = kw.id_kegiatan_detail_proses')
            ->where('kw.id_kegiatan_wilayah', $idKegiatanWilayah)
            ->where('kw.id_kabupaten', $idKabupaten)
            ->get()
            ->getRowArray();

        if (!$kegiatan) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Data tidak ditemukan atau Anda tidak memiliki akses'
            ]);
        }

        // Get data kurva target
        $kurvaTarget = $this->db->table('kurva_kabupaten')
            ->where('id_kegiatan_wilayah', $idKegiatanWilayah)
            ->orderBy('tanggal_target', 'ASC')
            ->get()
            ->getResultArray();

        // Get data realisasi - FIXED
        $realisasiData = $this->getRealisasiDataForChart($idKegiatanWilayah);

        // Format data untuk chart
        $chartData = $this->formatKurvaDataWithRealisasi($kurvaTarget, $realisasiData, $kegiatan);

        return $this->response->setJSON([
            'success' => true,
            'data' => $chartData
        ]);
    }

    // ======================================================
    // GET REALISASI DATA FOR CHART - FIXED
    // ======================================================
    private function getRealisasiDataForChart($idKegiatanWilayah)
    {
        // FIXED: Gunakan SUM realisasi absolut, lalu hitung kumulatif manual
        $sql = "
            SELECT 
                DATE(pp.created_at) as tanggal,
                SUM(pp.jumlah_realisasi_absolut) as realisasi_harian
            FROM pantau_progress pp
            JOIN pcl ON pp.id_pcl = pcl.id_pcl
            JOIN pml ON pcl.id_pml = pml.id_pml
            WHERE pml.id_kegiatan_wilayah = ?
            GROUP BY DATE(pp.created_at)
            ORDER BY DATE(pp.created_at) ASC
        ";
        
        $data = $this->db->query($sql, [$idKegiatanWilayah])->getResultArray();
        
        // Hitung kumulatif manual
        $kumulatif = 0;
        $result = [];
        
        foreach ($data as $item) {
            $kumulatif += (int) $item['realisasi_harian'];
            $result[] = [
                'tanggal' => $item['tanggal'],
                'realisasi_kumulatif' => $kumulatif
            ];
        }
        
        // Logging untuk debugging
        log_message('info', 'Realisasi data count: ' . count($result));
        if (!empty($result)) {
            log_message('info', 'First realisasi: ' . json_encode($result[0]));
            log_message('info', 'Last realisasi: ' . json_encode(end($result)));
        }
        
        return $result;
    }

    // ======================================================
    // FORMAT KURVA DATA WITH REALISASI
    // ======================================================
    private function formatKurvaDataWithRealisasi($kurvaTarget, $realisasiData, $kegiatan)
    {
        if (empty($kurvaTarget)) {
            return [
                'labels' => [],
                'target' => [],
                'realisasi' => [],
                'config' => [
                    'nama' => $kegiatan['nama_kegiatan_detail_proses'],
                    'tanggal_mulai' => date('d', strtotime($kegiatan['tanggal_mulai'])),
                    'tanggal_selesai' => date('d', strtotime($kegiatan['tanggal_selesai']))
                ]
            ];
        }

        // Build realisasi lookup
        $realisasiLookup = [];
        foreach ($realisasiData as $item) {
            $realisasiLookup[$item['tanggal']] = (int) $item['realisasi_kumulatif'];
        }

        // Remove duplicate dates
        $unique = [];
        foreach ($kurvaTarget as $row) {
            $tgl = $row['tanggal_target'];
            $unique[$tgl] = $row;
        }
        ksort($unique);

        $labels = [];
        $targetData = [];
        $realisasiDataFormatted = [];

        $lastRealisasi = 0;

        foreach ($unique as $tanggal => $row) {
            $labels[] = date('d M', strtotime($tanggal));
            $targetData[] = (int) $row['target_kumulatif_absolut'];

            // Update last realisasi jika ada data di tanggal ini
            if (isset($realisasiLookup[$tanggal])) {
                $lastRealisasi = $realisasiLookup[$tanggal];
            }
            
            // Gunakan last known realisasi untuk tanggal ini
            $realisasiDataFormatted[] = $lastRealisasi;
        }

        // Ensure monotonic increase for target
        for ($i = 1; $i < count($targetData); $i++) {
            if ($targetData[$i] < $targetData[$i - 1]) {
                $targetData[$i] = $targetData[$i - 1];
            }
        }

        // Logging untuk debugging
        log_message('info', 'Kurva target points: ' . count($targetData));
        log_message('info', 'Kurva realisasi points: ' . count($realisasiDataFormatted));

        return [
            'labels' => array_values($labels),
            'target' => array_values($targetData),
            'realisasi' => array_values($realisasiDataFormatted),
            'config' => [
                'nama' => $kegiatan['nama_kegiatan_detail_proses'],
                'tanggal_mulai' => date('d', strtotime($kegiatan['tanggal_mulai'])),
                'tanggal_selesai' => date('d', strtotime($kegiatan['tanggal_selesai']))
            ]
        ];
    }

    // ======================================================
    // GET KEPATUHAN DATA
    // ======================================================
    public function getKepatuhanData()
    {
        try {
            $idKegiatanWilayah = $this->request->getGet('id_kegiatan_wilayah');
            $idKabupaten = session()->get('user_kabupaten_id');

            log_message('info', 'getKepatuhanData - ID Kegiatan Wilayah: ' . $idKegiatanWilayah . ', ID Kabupaten: ' . $idKabupaten);

            // Validasi: pastikan kegiatan wilayah ini milik kabupaten user
            $kegiatan = $this->db->table('kegiatan_wilayah kw')
                ->select('kw.*, mkdp.tanggal_mulai, mkdp.tanggal_selesai, mkdp.id_kegiatan_detail_proses')
                ->join('master_kegiatan_detail_proses mkdp', 'mkdp.id_kegiatan_detail_proses = kw.id_kegiatan_detail_proses')
                ->where('kw.id_kegiatan_wilayah', $idKegiatanWilayah)
                ->where('kw.id_kabupaten', $idKabupaten)
                ->get()
                ->getRowArray();

            if (!$kegiatan) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke kegiatan ini'
                ]);
            }

            $idKegiatanDetailProses = $kegiatan['id_kegiatan_detail_proses'];

            // 1. Get Statistik - gunakan method dari KepatuhanModel
            $stats = $this->kepatuhanModel->getStatistikKepatuhan(
                $idKegiatanDetailProses,
                $idKegiatanWilayah,
                $idKabupaten
            );

            // 2. Get Chart Data - Line chart untuk trend harian
            $chartData = $this->kepatuhanModel->getTrendKepatuhanHarian(
                $idKegiatanDetailProses,
                $idKabupaten
            );

            // 3. Get Leaderboard
            $leaderboard = $this->kepatuhanModel->getLeaderboardKepatuhan(
                $idKegiatanDetailProses,
                $idKegiatanWilayah,
                10,
                $idKabupaten
            );

            // 4. Get Petugas Tidak Patuh
            $tidakPatuh = $this->kepatuhanModel->getPetugasTidakPatuh(
                $idKegiatanDetailProses,
                $idKegiatanWilayah,
                $idKabupaten
            );

            log_message('info', 'Stats calculated: ' . json_encode($stats));
            log_message('info', 'Chart data points: ' . count($chartData));

            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'stats' => $stats,
                    'chart' => [
                        'type' => 'line',
                        'data' => $chartData
                    ],
                    'leaderboard' => $leaderboard,
                    'tidak_patuh' => $tidakPatuh
                ]
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error in getKepatuhanData: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }
}