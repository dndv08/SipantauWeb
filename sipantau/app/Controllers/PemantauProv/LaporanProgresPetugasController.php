<?php

namespace App\Controllers\PemantauProv;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\MasterKabModel;
use App\Models\MasterKegiatanDetailProsesModel;
use App\Models\PCLModel;

class LaporanProgresPetugasController extends BaseController
{
    protected $userModel;
    protected $masterKab;
    protected $kegiatanProsesModel;
    protected $pclModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->masterKab = new MasterKabModel();
        $this->kegiatanProsesModel = new MasterKegiatanDetailProsesModel();
        $this->pclModel = new PCLModel();
    }

    public function index()
    {
        // Ambil parameter filter dari GET
        $idKegiatanProses = $this->request->getGet('kegiatan_proses');
        $kabupatenId = $this->request->getGet('kabupaten');
        $search = $this->request->getGet('search');
        $perPage = $this->request->getGet('perPage') ?? 10;

        // Validasi perPage
        $allowedPerPage = [5, 10, 25, 50, 100];
        if (!in_array((int) $perPage, $allowedPerPage)) {
            $perPage = 10;
        }

        // Ambil list kabupaten
        $kabupatenList = $this->masterKab->orderBy('id_kabupaten', 'ASC')->findAll();

        // Ambil list kegiatan proses dengan nama kegiatan detail
        $kegiatanProsesList = $this->kegiatanProsesModel
            ->select('master_kegiatan_detail_proses.*, master_kegiatan_detail.nama_kegiatan_detail')
            ->join('master_kegiatan_detail', 'master_kegiatan_detail_proses.id_kegiatan_detail = master_kegiatan_detail.id_kegiatan_detail')
            ->orderBy('master_kegiatan_detail_proses.tanggal_mulai', 'DESC')
            ->findAll();

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

                // Ambil data petugas dan progress
                $dataPetugas = $this->getLaporanProgressPetugas(
                    $idKegiatanProses,
                    $kabupatenId,
                    $search,
                    $dateHeaders,
                    $perPage
                );

                $totalData = $dataPetugas['total'];
                $dataPetugas = $dataPetugas['data'];
            }
        }

        $data = [
            'title' => 'Pemantau - Laporan Progress Petugas',
            'active_menu' => 'laporan-progress-petugas',
            'kegiatanProsesList' => $kegiatanProsesList,
            'kabupatenList' => $kabupatenList,
            'dataPetugas' => $dataPetugas,
            'dateHeaders' => $dateHeaders,
            'selectedKegiatanProses' => $idKegiatanProses,
            'selectedKabupaten' => $kabupatenId,
            'search' => $search,
            'perPage' => $perPage,
            'totalData' => $totalData,
            'currentPage' => $this->request->getGet('page') ?? 1
        ];

        return view('PemantauProvinsi/LaporanProgresPetugas/index', $data);
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
     * Get laporan progress petugas dengan pagination
     */
    private function getLaporanProgressPetugas($idKegiatanProses, $kabupatenId, $search, $dateHeaders, $perPage)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('pcl')
            ->select('pcl.id_pcl, pcl.sobat_id, pcl.target, sipantau_user.nama_user, master_kabupaten.nama_kabupaten, sipantau_user.id_kabupaten')
            ->join('sipantau_user', 'pcl.sobat_id = sipantau_user.sobat_id')
            ->join('master_kabupaten', 'sipantau_user.id_kabupaten = master_kabupaten.id_kabupaten')
            ->join('pml', 'pcl.id_pml = pml.id_pml')
            ->join('kegiatan_wilayah', 'pml.id_kegiatan_wilayah = kegiatan_wilayah.id_kegiatan_wilayah')
            ->where('kegiatan_wilayah.id_kegiatan_detail_proses', $idKegiatanProses);

        // Filter kabupaten
        if ($kabupatenId) {
            $builder->where('sipantau_user.id_kabupaten', $kabupatenId);
        }

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

        // Ambil data progress untuk setiap petugas berdasarkan pantau_progress
        foreach ($petugas as &$p) {
            $progressData = [];
            $totalRealisasi = 0;

            foreach ($dateHeaders as $dateInfo) {
                // Ambil progress kumulatif terakhir pada tanggal tersebut
                $progress = $db->table('pantau_progress')
                    ->select('jumlah_realisasi_kumulatif')
                    ->where('id_pcl', $p['id_pcl'])
                    ->where('DATE(created_at)', $dateInfo['date'])
                    ->orderBy('created_at', 'DESC')
                    ->limit(1)
                    ->get()
                    ->getRowArray();

                $kumulatif = $progress ? (int)$progress['jumlah_realisasi_kumulatif'] : 0;
                $progressData[] = $kumulatif;
                
                // Total realisasi adalah progress kumulatif terakhir
                if ($kumulatif > $totalRealisasi) {
                    $totalRealisasi = $kumulatif;
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
        $idKegiatanProses = $this->request->getGet('kegiatan_proses');
        $kabupatenId = $this->request->getGet('kabupaten');
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
        $dataPetugas = $this->getLaporanProgressPetugasAll($idKegiatanProses, $kabupatenId, $search, $dateHeaders);

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
    private function getLaporanProgressPetugasAll($idKegiatanProses, $kabupatenId, $search, $dateHeaders)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('pcl')
            ->select('pcl.id_pcl, pcl.sobat_id, pcl.target, sipantau_user.nama_user, master_kabupaten.nama_kabupaten')
            ->join('sipantau_user', 'pcl.sobat_id = sipantau_user.sobat_id')
            ->join('master_kabupaten', 'sipantau_user.id_kabupaten = master_kabupaten.id_kabupaten')
            ->join('pml', 'pcl.id_pml = pml.id_pml')
            ->join('kegiatan_wilayah', 'pml.id_kegiatan_wilayah = kegiatan_wilayah.id_kegiatan_wilayah')
            ->where('kegiatan_wilayah.id_kegiatan_detail_proses', $idKegiatanProses);

        if ($kabupatenId) {
            $builder->where('sipantau_user.id_kabupaten', $kabupatenId);
        }

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
                $progress = $db->table('pantau_progress')
                    ->select('jumlah_realisasi_kumulatif')
                    ->where('id_pcl', $p['id_pcl'])
                    ->where('DATE(created_at)', $dateInfo['date'])
                    ->orderBy('created_at', 'DESC')
                    ->limit(1)
                    ->get()
                    ->getRowArray();

                $kumulatif = $progress ? (int)$progress['jumlah_realisasi_kumulatif'] : 0;
                $progressData[] = $kumulatif;
                
                if ($kumulatif > $totalRealisasi) {
                    $totalRealisasi = $kumulatif;
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
        $db = \Config\Database::connect();

        // Get PCL detail
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
            ->get()
            ->getRowArray();

        if (!$pclDetail) {
            return redirect()->to('pemantau-provinsi/laporan-progress-petugas')->with('error', 'Data PCL tidak ditemukan');
        }

        $kurvaPetugasModel = new \App\Models\KurvaPetugasModel();

        // Get realisasi data dari pantau_progress
        $realisasi = $db->table('pantau_progress')
            ->select('COALESCE(MAX(jumlah_realisasi_kumulatif), 0) as total_realisasi')
            ->where('id_pcl', $idPCL)
            ->get()
            ->getRowArray();

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

        return view('PemantauProvinsi/LaporanProgresPetugas/detail', $data);
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

        // Get realisasi harian dari pantau_progress (ambil progress kumulatif terakhir per hari)
        $realisasiHarian = $db->query("
            SELECT 
                DATE(created_at) as tanggal,
                MAX(jumlah_realisasi_kumulatif) as jumlah_realisasi_kumulatif
            FROM pantau_progress
            WHERE id_pcl = ?
            GROUP BY DATE(created_at)
            ORDER BY DATE(created_at) ASC
        ", [$idPCL])->getResultArray();

        // Build realisasi lookup
        $realisasiLookup = [];
        foreach ($realisasiHarian as $item) {
            $realisasiLookup[$item['tanggal']] = (int) $item['jumlah_realisasi_kumulatif'];
        }

        // Format data untuk chart
        $labels = [];
        $targetData = [];
        $realisasiData = [];
        
        // Variable untuk menyimpan nilai kumulatif terakhir
        $lastRealisasiKumulatif = 0;

        foreach ($kurvaTarget as $item) {
            $tanggal = $item['tanggal_target'];
            $labels[] = date('d M', strtotime($tanggal));
            $targetData[] = (int) $item['target_kumulatif_absolut'];

            // Jika ada realisasi pada tanggal tersebut, gunakan nilai tersebut
            // Jika tidak ada, gunakan nilai kumulatif terakhir (carry forward)
            if (isset($realisasiLookup[$tanggal])) {
                $lastRealisasiKumulatif = $realisasiLookup[$tanggal];
            }
            
            $realisasiData[] = $lastRealisasiKumulatif;
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
    public function getPantauProgress()
    {
        $idPCL = $this->request->getGet('id_pcl');
        $page = $this->request->getGet('page') ?? 1;
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        $db = \Config\Database::connect();
        
        $total = $db->table('pantau_progress')
            ->where('id_pcl', $idPCL)
            ->countAllResults();
        
        $data = $db->table('pantau_progress')
            ->where('id_pcl', $idPCL)
            ->orderBy('created_at', 'DESC')
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

    /**
     * Get Laporan Transaksi via AJAX (untuk detail laporan)
     */
    public function getLaporanTransaksi()
    {
        $db = \Config\Database::connect();
        $idPCL = $this->request->getGet('id_pcl');
        $page = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        $builder = $db->table('sipantau_transaksi st');

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
}