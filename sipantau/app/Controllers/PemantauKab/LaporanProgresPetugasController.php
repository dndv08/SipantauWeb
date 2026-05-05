<?php

namespace App\Controllers\PemantauKab;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\MasterKegiatanDetailProsesModel;
use App\Models\PantauProgressModel;
use App\Models\PCLModel;

class LaporanProgresPetugasController extends BaseController
{
    protected $userModel;
    protected $kegiatanProsesModel;
    protected $pantauProgressModel;
    protected $pclModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->kegiatanProsesModel = new MasterKegiatanDetailProsesModel();
        $this->pantauProgressModel = new PantauProgressModel();
        $this->pclModel = new PCLModel();
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

        // Get kabupaten user
        $user = $this->userModel->find($sobatId);

        if (!$user || !$user['id_kabupaten']) {
            return redirect()->to(base_url('unauthorized'))
                ->with('error', 'Data kabupaten tidak ditemukan.');
        }

        $idKabupaten = $user['id_kabupaten'];

        // Ambil parameter filter dari GET
        $idKegiatanProses = $this->request->getGet('kegiatan_proses');
        $search = $this->request->getGet('search');
        $perPage = $this->request->getGet('perPage') ?? 10;

        // Validasi perPage
        $allowedPerPage = [5, 10, 25, 50, 100];
        if (!in_array((int) $perPage, $allowedPerPage)) {
            $perPage = 10;
        }

        // Ambil list kegiatan proses untuk kabupaten ini saja
        $db = \Config\Database::connect();
        $kegiatanProsesList = $db->table('master_kegiatan_detail_proses mkdp')
            ->select('mkdp.*, mkd.nama_kegiatan_detail')
            ->join('master_kegiatan_detail mkd', 'mkdp.id_kegiatan_detail = mkd.id_kegiatan_detail')
            ->join('kegiatan_wilayah kw', 'mkdp.id_kegiatan_detail_proses = kw.id_kegiatan_detail_proses')
            ->where('kw.id_kabupaten', $idKabupaten)
            ->groupBy('mkdp.id_kegiatan_detail_proses')
            ->orderBy('mkdp.tanggal_mulai', 'DESC')
            ->get()
            ->getResultArray();

        $dataPetugas = [];
        $dateHeaders = [];
        $totalData = 0;

        // Jika kegiatan proses dipilih, ambil data laporan
        if ($idKegiatanProses) {
            // Ambil detail kegiatan untuk tanggal
            $kegiatanDetail = $this->kegiatanProsesModel->find($idKegiatanProses);

            if ($kegiatanDetail) {
                // Generate tanggal (semua hari, termasuk Sabtu & Minggu)
                $dateHeaders = $this->generateAllDates(
                    $kegiatanDetail['tanggal_mulai'],
                    $kegiatanDetail['tanggal_selesai']
                );

                // Ambil data petugas dan progress untuk kabupaten ini
                $dataPetugas = $this->getLaporanProgressPetugas(
                    $idKegiatanProses,
                    $idKabupaten,
                    $search,
                    $dateHeaders,
                    $perPage
                );

                $totalData = $dataPetugas['total'];
                $dataPetugas = $dataPetugas['data'];
            }
        }

        // Get nama kabupaten
        $kabupaten = $db->table('master_kabupaten')
            ->where('id_kabupaten', $idKabupaten)
            ->get()
            ->getRowArray();

        $data = [
            'title' => 'Pemantau Kabupaten - Laporan Progress Petugas',
            'active_menu' => 'laporan-progress-petugas',
            'kegiatanProsesList' => $kegiatanProsesList,
            'dataPetugas' => $dataPetugas,
            'dateHeaders' => $dateHeaders,
            'selectedKegiatanProses' => $idKegiatanProses,
            'search' => $search,
            'perPage' => $perPage,
            'totalData' => $totalData,
            'currentPage' => $this->request->getGet('page') ?? 1,
            'kabupaten' => $kabupaten
        ];

        return view('PemantauKabupaten/LaporanProgresPetugas/index', $data);
    }

    /**
     * Generate all dates (termasuk Sabtu & Minggu)
     */
    private function generateAllDates($startDate, $endDate)
    {
        $dates = [];
        $current = strtotime($startDate);
        $end = strtotime($endDate);

        while ($current <= $end) {
            $dates[] = [
                'date' => date('Y-m-d', $current),
                'display' => date('d/m', $current)
            ];

            $current = strtotime('+1 day', $current);
        }

        return $dates;
    }

    /**
     * Get laporan progress petugas dengan pagination - FILTERED BY KABUPATEN
     * Menggunakan jumlah_realisasi_kumulatif dari pantau_progress
     */
    private function getLaporanProgressPetugas($idKegiatanProses, $idKabupaten, $search, $dateHeaders, $perPage)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('pcl')
            ->select('pcl.id_pcl, pcl.sobat_id, pcl.target, sipantau_user.nama_user, master_kabupaten.nama_kabupaten, sipantau_user.id_kabupaten')
            ->join('sipantau_user', 'pcl.sobat_id = sipantau_user.sobat_id')
            ->join('master_kabupaten', 'sipantau_user.id_kabupaten = master_kabupaten.id_kabupaten')
            ->join('pml', 'pcl.id_pml = pml.id_pml')
            ->join('kegiatan_wilayah', 'pml.id_kegiatan_wilayah = kegiatan_wilayah.id_kegiatan_wilayah')
            ->where('kegiatan_wilayah.id_kegiatan_detail_proses', $idKegiatanProses)
            ->where('sipantau_user.id_kabupaten', $idKabupaten); // FILTER BY KABUPATEN

        // Search
        if ($search) {
            $builder->groupStart()
                ->like('sipantau_user.nama_user', $search)
                ->orLike('pcl.sobat_id', $search)
                ->groupEnd();
        }

        // Clone builder untuk count total sebelum pagination
        $builderCount = clone $builder;
        $total = $builderCount->countAllResults();

        // Pagination
        $page = $this->request->getGet('page') ?? 1;
        $offset = ($page - 1) * $perPage;

        $petugas = $builder
            ->orderBy('sipantau_user.nama_user', 'ASC')
            ->limit($perPage, $offset)
            ->get()
            ->getResultArray();

        // Ambil data progress untuk setiap petugas
        foreach ($petugas as &$p) {
            $progressData = [];
            $totalRealisasi = 0;

            foreach ($dateHeaders as $dateInfo) {
                // Ambil realisasi kumulatif terakhir pada tanggal tersebut
                $realisasiKumulatif = $db->table('pantau_progress')
                    ->select('jumlah_realisasi_kumulatif')
                    ->where('id_pcl', $p['id_pcl'])
                    ->where('DATE(created_at)', $dateInfo['date'])
                    ->orderBy('created_at', 'DESC')
                    ->limit(1)
                    ->get()
                    ->getRowArray();

                $nilai = $realisasiKumulatif ? (int)$realisasiKumulatif['jumlah_realisasi_kumulatif'] : 0;
                $progressData[] = $nilai;

                // Update total realisasi dengan nilai tertinggi
                if ($nilai > $totalRealisasi) {
                    $totalRealisasi = $nilai;
                }
            }

            $p['progress_data'] = $progressData;
            $p['total_realisasi'] = $totalRealisasi;
            $p['status_complete'] = $totalRealisasi >= $p['target'];
        }

        return [
            'data' => $petugas,
            'total' => $total
        ];
    }

    /**
     * Export to CSV
     */
    public function exportCSV()
    {
        // Get role dan user info dari session
        $role = session()->get('role');
        $roleType = session()->get('role_type');
        $sobatId = session()->get('sobat_id');

        $isPemantauKabupaten = ($role == 3 && $roleType == 'pemantau_kabupaten');

        if (!$isPemantauKabupaten) {
            return redirect()->to(base_url('unauthorized'));
        }

        // Get kabupaten user
        $user = $this->userModel->find($sobatId);
        $idKabupaten = $user['id_kabupaten'];

        $idKegiatanProses = $this->request->getGet('kegiatan_proses');
        $search = $this->request->getGet('search');

        if (!$idKegiatanProses) {
            return redirect()->back()->with('error', 'Pilih kegiatan proses terlebih dahulu');
        }

        // Ambil detail kegiatan
        $kegiatanDetail = $this->kegiatanProsesModel
            ->select('master_kegiatan_detail_proses.*, master_kegiatan_detail.nama_kegiatan_detail')
            ->join('master_kegiatan_detail', 'master_kegiatan_detail_proses.id_kegiatan_detail = master_kegiatan_detail.id_kegiatan_detail')
            ->find($idKegiatanProses);

        // Generate tanggal (semua hari, termasuk Sabtu & Minggu)
        $dateHeaders = $this->generateAllDates(
            $kegiatanDetail['tanggal_mulai'],
            $kegiatanDetail['tanggal_selesai']
        );

        // Ambil semua data (no pagination)
        $dataPetugas = $this->getLaporanProgressPetugasAll($idKegiatanProses, $idKabupaten, $search, $dateHeaders);

        // Set headers untuk download CSV
        $filename = 'laporan_progress_petugas_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        // Output CSV
        $output = fopen('php://output', 'w');

        // UTF-8 BOM untuk Excel
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Header CSV
        $headers = array_merge(['No', 'Nama Petugas', 'Kabupaten'], array_column($dateHeaders, 'display'), ['TOTAL', 'Target', 'Status']);
        fputcsv($output, $headers);

        // Data rows
        $no = 1;
        foreach ($dataPetugas as $petugas) {
            $status = $petugas['total_realisasi'] >= $petugas['target'] ? 'Complete' : 'Incomplete';
            $row = array_merge(
                [$no++, $petugas['nama_user'], $petugas['nama_kabupaten']],
                $petugas['progress_data'],
                [$petugas['total_realisasi'], $petugas['target'], $status]
            );
            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    }

    /**
     * Get all laporan progress petugas (untuk export)
     */
    private function getLaporanProgressPetugasAll($idKegiatanProses, $idKabupaten, $search, $dateHeaders)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('pcl')
            ->select('pcl.id_pcl, pcl.sobat_id, pcl.target, sipantau_user.nama_user, master_kabupaten.nama_kabupaten')
            ->join('sipantau_user', 'pcl.sobat_id = sipantau_user.sobat_id')
            ->join('master_kabupaten', 'sipantau_user.id_kabupaten = master_kabupaten.id_kabupaten')
            ->join('pml', 'pcl.id_pml = pml.id_pml')
            ->join('kegiatan_wilayah', 'pml.id_kegiatan_wilayah = kegiatan_wilayah.id_kegiatan_wilayah')
            ->where('kegiatan_wilayah.id_kegiatan_detail_proses', $idKegiatanProses)
            ->where('sipantau_user.id_kabupaten', $idKabupaten); // FILTER BY KABUPATEN

        if ($search) {
            $builder->groupStart()
                ->like('sipantau_user.nama_user', $search)
                ->orLike('pcl.sobat_id', $search)
                ->groupEnd();
        }

        $petugas = $builder
            ->orderBy('sipantau_user.nama_user', 'ASC')
            ->get()
            ->getResultArray();

        foreach ($petugas as &$p) {
            $progressData = [];
            $totalRealisasi = 0;

            foreach ($dateHeaders as $dateInfo) {
                // Ambil realisasi kumulatif terakhir pada tanggal tersebut
                $realisasiKumulatif = $db->table('pantau_progress')
                    ->select('jumlah_realisasi_kumulatif')
                    ->where('id_pcl', $p['id_pcl'])
                    ->where('DATE(created_at)', $dateInfo['date'])
                    ->orderBy('created_at', 'DESC')
                    ->limit(1)
                    ->get()
                    ->getRowArray();

                $nilai = $realisasiKumulatif ? (int)$realisasiKumulatif['jumlah_realisasi_kumulatif'] : 0;
                $progressData[] = $nilai;

                // Update total realisasi dengan nilai tertinggi
                if ($nilai > $totalRealisasi) {
                    $totalRealisasi = $nilai;
                }
            }

            $p['progress_data'] = $progressData;
            $p['total_realisasi'] = $totalRealisasi;
        }

        return $petugas;
    }

    /**
     * Detail Laporan Progress Petugas (PCL)
     */
    public function detailLaporanProgressPetugas($idPCL)
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

        // Get kabupaten user
        $user = $this->userModel->find($sobatId);
        $idKabupaten = $user['id_kabupaten'];

        $db = \Config\Database::connect();

        // Get PCL detail with kabupaten filter
        $pclDetail = $db->table('pcl')
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
            ->where('u.id_kabupaten', $idKabupaten) // Filter by kabupaten
            ->get()
            ->getRowArray();

        if (!$pclDetail) {
            return redirect()->to('pemantau-kabupaten/laporan-progress-petugas')->with('error', 'Data PCL tidak ditemukan atau bukan dari kabupaten Anda');
        }

        $kurvaPetugasModel = new \App\Models\KurvaPetugasModel();

        // Get realisasi data - ambil nilai kumulatif tertinggi
        $realisasi = $this->pantauProgressModel
            ->select('COALESCE(MAX(jumlah_realisasi_kumulatif), 0) as total_realisasi')
            ->where('id_pcl', $idPCL)
            ->first();

        $realisasiKumulatif = (int) ($realisasi['total_realisasi'] ?? 0);
        $target = (int) $pclDetail['target'];
        $persentase = $target > 0 ? round(($realisasiKumulatif / $target) * 100, 2) : 0;
        $selisih = $target - $realisasiKumulatif;

        // Get Kurva S data
        $kurvaData = $this->getKurvaDataPCL($idPCL, $pclDetail, $kurvaPetugasModel);

        $data = [
            'title' => 'Detail Laporan Progress PCL',
            'active_menu' => 'laporan-progress-petugas',
            'pcl' => $pclDetail,
            'target' => $target,
            'realisasi' => $realisasiKumulatif,
            'persentase' => $persentase,
            'selisih' => $selisih,
            'kurvaData' => $kurvaData,
            'idPCL' => $idPCL
        ];

        return view('PemantauKabupaten/LaporanProgresPetugas/detail', $data);
    }

    /**
     * Get Kurva S data untuk chart
     */
    private function getKurvaDataPCL($idPCL, $pclDetail, $kurvaPetugasModel)
    {
        $db = \Config\Database::connect();

        // Get kurva target
        $kurvaTarget = $kurvaPetugasModel
            ->where('id_pcl', $idPCL)
            ->orderBy('tanggal_target', 'ASC')
            ->findAll();

        // Get realisasi kumulatif per hari
        $realisasiHarian = $db->query("
        SELECT 
            DATE(created_at) as tanggal,
            MAX(jumlah_realisasi_kumulatif) as realisasi_kumulatif
        FROM pantau_progress
        WHERE id_pcl = ?
        GROUP BY DATE(created_at)
        ORDER BY DATE(created_at) ASC
    ", [$idPCL])->getResultArray();

        // Build realisasi lookup
        $realisasiLookup = [];
        foreach ($realisasiHarian as $item) {
            $realisasiLookup[$item['tanggal']] = (int) $item['realisasi_kumulatif'];
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

            // Gunakan nilai kumulatif dari hari tersebut jika ada
            if (isset($realisasiLookup[$tanggal])) {
                $realisasiKumulatif = $realisasiLookup[$tanggal];
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
     * Get Pantau Progress via AJAX (untuk detail laporan)
     */
    public function getPantauProgressLaporan()
    {
        $idPCL = $this->request->getGet('id_pcl');
        $page = $this->request->getGet('page') ?? 1;
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        $total = $this->pantauProgressModel->where('id_pcl', $idPCL)->countAllResults(false);
        $data = $this->pantauProgressModel
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
}