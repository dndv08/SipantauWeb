<?php

namespace App\Controllers\AdminKab;

use App\Controllers\BaseController;
use App\Models\AdminSurveiKabupatenModel;
use App\Models\UserModel;
use App\Models\MasterKabModel;
use App\Models\PMLModel;
use App\Models\PCLModel;
use App\Models\MasterKegiatanDetailProsesModel;
use App\Models\KegiatanWilayahAdminModel;

class DataPetugasController extends BaseController
{
    protected $adminKabModel;
    protected $userModel;
    protected $kabupatenModel;
    protected $pmlModel;
    protected $pclModel;
    protected $kegiatanDetailProsesModel;
    protected $kegiatanWilayahAdminModel;

    public function __construct()
    {
        $this->adminKabModel = new AdminSurveiKabupatenModel();
        $this->userModel = new UserModel();
        $this->kabupatenModel = new MasterKabModel();
        $this->pmlModel = new PMLModel();
        $this->pclModel = new PCLModel();
        $this->kegiatanDetailProsesModel = new MasterKegiatanDetailProsesModel();
        $this->kegiatanWilayahAdminModel = new KegiatanWilayahAdminModel();
    }

    /**
     * Halaman index - daftar semua petugas di kabupaten
     */
    public function index()
    {
        $sobatId = session()->get('sobat_id');

        // Get admin info
        $admin = $this->adminKabModel->db->table('admin_survei_kabupaten ask')
            ->select('ask.*, u.id_kabupaten')
            ->join('sipantau_user u', 'ask.sobat_id = u.sobat_id')
            ->where('ask.sobat_id', $sobatId)
            ->get()
            ->getRowArray();

        if (!$admin) {
            return redirect()->to('unauthorized');
        }

        $idKabupaten = $admin['id_kabupaten'];
        $idAdminKabupaten = $admin['id_admin_kabupaten'];

        // Get kabupaten info
        $kabupaten = $this->kabupatenModel->find($idKabupaten);

        // Get filter parameters
        $selectedKegiatanProses = $this->request->getGet('kegiatan_proses');
        $search = $this->request->getGet('search');
        $perPage = $this->request->getGet('perPage') ?? 10;
        $sortBy = $this->request->getGet('sort_by') ?? '';
        $sortOrder = $this->request->getGet('sort_order') ?? 'asc';

        // Validasi sort order
        $sortOrder = in_array($sortOrder, ['asc', 'desc']) ? $sortOrder : 'asc';

        // Get kegiatan proses yang di-assign ke admin ini
        $kegiatanProsesList = $this->getAssignedKegiatanProses($idAdminKabupaten);

        // Get all data petugas first
        $allPetugas = $this->getAllPetugasData($idKabupaten, $selectedKegiatanProses, $search);

        // PENTING: Enrich data SEBELUM sorting
        foreach ($allPetugas as &$petugas) {
            $petugas['roles'] = $this->getPetugasRoles($petugas['sobat_id'], $idKabupaten, $selectedKegiatanProses);
            $petugas['kegiatan'] = $this->getPetugasKegiatan($petugas['sobat_id'], $idKabupaten, $selectedKegiatanProses);
            $petugas['rating_data'] = $this->getAverageRating($petugas['sobat_id'], $idKabupaten, $selectedKegiatanProses);

            // Tambahkan field untuk sorting
            $petugas['jumlah_kegiatan'] = count($petugas['kegiatan']);
        }

        // Apply sorting
        if (!empty($sortBy)) {
            usort($allPetugas, function ($a, $b) use ($sortBy, $sortOrder) {
                $result = 0;

                switch ($sortBy) {
                    case 'nama':
                        $result = strcasecmp($a['nama_user'], $b['nama_user']);
                        break;

                    case 'rating':
                        $ratingA = $a['rating_data']['avg_rating'] ?? 0;
                        $ratingB = $b['rating_data']['avg_rating'] ?? 0;
                        $result = $ratingA <=> $ratingB;
                        break;

                    case 'kegiatan':
                        $result = $a['jumlah_kegiatan'] <=> $b['jumlah_kegiatan'];
                        break;
                }

                return $sortOrder === 'desc' ? -$result : $result;
            });
        }

        // Manual pagination
        $totalData = count($allPetugas);
        $currentPage = $this->request->getVar('page_dataPetugas') ?? 1;
        $offset = ($currentPage - 1) * $perPage;

        $dataPetugas = array_slice($allPetugas, $offset, $perPage);

        // Create pager manually
        $pager = \Config\Services::pager();
        $pager->store('dataPetugas', $currentPage, $perPage, $totalData);

        $data = [
            'title' => 'Data Petugas',
            'active_menu' => 'data-petugas',
            'kabupaten' => $kabupaten,
            'dataPetugas' => $dataPetugas,
            'kegiatanProsesList' => $kegiatanProsesList,
            'selectedKegiatanProses' => $selectedKegiatanProses,
            'search' => $search,
            'perPage' => $perPage,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
            'pager' => $pager,
            'totalData' => $totalData
        ];

        return view('AdminSurveiKab/DataPetugas/index', $data);
    }

    /**
     * Get all petugas data dengan filter
     */
    /**
     * Get all petugas data dengan filter
     */
    private function getAllPetugasData($idKabupaten, $kegiatanProsesFilter = null, $search = null)
    {
        $builder = $this->userModel->db->table('sipantau_user u')
            ->select('u.sobat_id, u.nama_user, u.is_active')
            ->where('u.id_kabupaten', $idKabupaten);

        // Apply search filter
        if (!empty($search)) {
            $builder->groupStart()
                ->like('u.nama_user', $search)
                ->orLike('u.sobat_id', $search)
                ->groupEnd();
        }

        // Filter berdasarkan kegiatan jika ada
        if (!empty($kegiatanProsesFilter)) {
            $builder->where('(
            u.sobat_id IN (
                SELECT pml.sobat_id 
                FROM pml 
                JOIN kegiatan_wilayah kw ON pml.id_kegiatan_wilayah = kw.id_kegiatan_wilayah
                WHERE kw.id_kegiatan_detail_proses = ' . $this->userModel->db->escape($kegiatanProsesFilter) . '
                AND kw.id_kabupaten = ' . $this->userModel->db->escape($idKabupaten) . '
            )
            OR u.sobat_id IN (
                SELECT pcl.sobat_id 
                FROM pcl
                JOIN pml ON pcl.id_pml = pml.id_pml
                JOIN kegiatan_wilayah kw ON pml.id_kegiatan_wilayah = kw.id_kegiatan_wilayah
                WHERE kw.id_kegiatan_detail_proses = ' . $this->userModel->db->escape($kegiatanProsesFilter) . '
                AND kw.id_kabupaten = ' . $this->userModel->db->escape($idKabupaten) . '
            )
        )');
        }

        $builder->groupBy('u.sobat_id, u.nama_user, u.is_active')
            ->orderBy('u.nama_user', 'ASC');

        return $builder->get()->getResultArray();
    }

    /**
     * Get rata-rata rating petugas dari semua kegiatan PCL
     */
    private function getAverageRating($sobatId, $idKabupaten, $kegiatanProsesFilter = null)
    {
        $builder = $this->pclModel->db->table('pcl')
            ->selectAvg('pcl.rating', 'avg_rating')
            ->select('COUNT(pcl.id_pcl) as total_kegiatan')
            ->join('pml', 'pcl.id_pml = pml.id_pml')
            ->join('kegiatan_wilayah kw', 'pml.id_kegiatan_wilayah = kw.id_kegiatan_wilayah')
            ->where('pcl.sobat_id', $sobatId)
            ->where('kw.id_kabupaten', $idKabupaten);

        if ($kegiatanProsesFilter) {
            $builder->where('kw.id_kegiatan_detail_proses', $kegiatanProsesFilter);
        }

        $result = $builder->get()->getRowArray();

        return [
            'avg_rating' => $result['avg_rating'] ? round($result['avg_rating'], 1) : 0,
            'total_kegiatan' => (int) $result['total_kegiatan']
        ];
    }

    /**
     * Get kegiatan proses yang di-assign ke admin
     */
    private function getAssignedKegiatanProses($idAdminKabupaten)
    {
        return $this->kegiatanWilayahAdminModel->db->table('kegiatan_wilayah_admin kwa')
            ->select('mkdp.id_kegiatan_detail_proses, 
                     mkdp.nama_kegiatan_detail_proses,
                     mkd.nama_kegiatan_detail')
            ->join('kegiatan_wilayah kw', 'kwa.id_kegiatan_wilayah = kw.id_kegiatan_wilayah')
            ->join('master_kegiatan_detail_proses mkdp', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses')
            ->join('master_kegiatan_detail mkd', 'mkdp.id_kegiatan_detail = mkd.id_kegiatan_detail')
            ->where('kwa.id_admin_kabupaten', $idAdminKabupaten)
            ->groupBy('mkdp.id_kegiatan_detail_proses, mkdp.nama_kegiatan_detail_proses, mkd.nama_kegiatan_detail')
            ->orderBy('mkdp.tanggal_mulai', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Get roles petugas (PML/PCL)
     */
    private function getPetugasRoles($sobatId, $idKabupaten, $kegiatanProsesFilter = null)
    {
        $roles = [];

        // Check PML
        $builderPML = $this->pmlModel->db->table('pml')
            ->join('kegiatan_wilayah kw', 'pml.id_kegiatan_wilayah = kw.id_kegiatan_wilayah')
            ->where('pml.sobat_id', $sobatId)
            ->where('kw.id_kabupaten', $idKabupaten);

        if ($kegiatanProsesFilter) {
            $builderPML->where('kw.id_kegiatan_detail_proses', $kegiatanProsesFilter);
        }

        if ($builderPML->countAllResults() > 0) {
            $roles[] = 'PML';
        }

        // Check PCL
        $builderPCL = $this->pclModel->db->table('pcl')
            ->join('pml', 'pcl.id_pml = pml.id_pml')
            ->join('kegiatan_wilayah kw', 'pml.id_kegiatan_wilayah = kw.id_kegiatan_wilayah')
            ->where('pcl.sobat_id', $sobatId)
            ->where('kw.id_kabupaten', $idKabupaten);

        if ($kegiatanProsesFilter) {
            $builderPCL->where('kw.id_kegiatan_detail_proses', $kegiatanProsesFilter);
        }

        if ($builderPCL->countAllResults() > 0) {
            $roles[] = 'PCL';
        }

        return array_unique($roles);
    }

    /**
     * Get daftar kegiatan petugas (format: Nama Kegiatan Detail Proses (Tahun))
     */
    private function getPetugasKegiatan($sobatId, $idKabupaten, $kegiatanProsesFilter = null)
    {
        $kegiatan = [];

        // Get kegiatan sebagai PML
        $builderPML = $this->pmlModel->db->table('pml')
            ->select('mkdp.nama_kegiatan_detail_proses, mkd.tahun')
            ->join('kegiatan_wilayah kw', 'pml.id_kegiatan_wilayah = kw.id_kegiatan_wilayah')
            ->join('master_kegiatan_detail_proses mkdp', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses')
            ->join('master_kegiatan_detail mkd', 'mkdp.id_kegiatan_detail = mkd.id_kegiatan_detail')
            ->where('pml.sobat_id', $sobatId)
            ->where('kw.id_kabupaten', $idKabupaten);

        if ($kegiatanProsesFilter) {
            $builderPML->where('kw.id_kegiatan_detail_proses', $kegiatanProsesFilter);
        }

        $pmlKegiatan = $builderPML->get()->getResultArray();
        foreach ($pmlKegiatan as $kg) {
            $kegiatan[] = [
                'display' => $kg['nama_kegiatan_detail_proses'] . ' (' . $kg['tahun'] . ')'
            ];
        }

        // Get kegiatan sebagai PCL
        $builderPCL = $this->pclModel->db->table('pcl')
            ->select('mkdp.nama_kegiatan_detail_proses, mkd.tahun')
            ->join('pml', 'pcl.id_pml = pml.id_pml')
            ->join('kegiatan_wilayah kw', 'pml.id_kegiatan_wilayah = kw.id_kegiatan_wilayah')
            ->join('master_kegiatan_detail_proses mkdp', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses')
            ->join('master_kegiatan_detail mkd', 'mkdp.id_kegiatan_detail = mkd.id_kegiatan_detail')
            ->where('pcl.sobat_id', $sobatId)
            ->where('kw.id_kabupaten', $idKabupaten);

        if ($kegiatanProsesFilter) {
            $builderPCL->where('kw.id_kegiatan_detail_proses', $kegiatanProsesFilter);
        }

        $pclKegiatan = $builderPCL->get()->getResultArray();
        foreach ($pclKegiatan as $kg) {
            $kegiatan[] = [
                'display' => $kg['nama_kegiatan_detail_proses'] . ' (' . $kg['tahun'] . ')'
            ];
        }

        // Remove duplicates
        $kegiatan = array_map("unserialize", array_unique(array_map("serialize", $kegiatan)));

        return $kegiatan;
    }

    /**
     * Detail petugas - list kegiatan yang pernah diikuti
     */
    public function detailPetugas($sobatId)
    {
        $adminSobatId = session()->get('sobat_id');

        // Get admin info
        $admin = $this->adminKabModel->db->table('admin_survei_kabupaten ask')
            ->select('ask.*, u.id_kabupaten')
            ->join('sipantau_user u', 'ask.sobat_id = u.sobat_id')
            ->where('ask.sobat_id', $adminSobatId)
            ->get()
            ->getRowArray();

        if (!$admin) {
            return redirect()->to('unauthorized');
        }

        // Get petugas info
        $petugas = $this->userModel->where('sobat_id', $sobatId)->first();

        if (!$petugas) {
            return redirect()->to('adminsurvei-kab/data-petugas')->with('error', 'Petugas tidak ditemukan');
        }

        // Get kegiatan sebagai PML
        $kegiatanPML = $this->pmlModel->db->table('pml')
            ->select('pml.id_pml as id, pml.target, pml.created_at,
                     mk.nama_kegiatan, mkd.nama_kegiatan_detail, 
                     mkdp.nama_kegiatan_detail_proses, mkdp.tanggal_mulai, mkdp.tanggal_selesai,
                     "PML" as role')
            ->join('kegiatan_wilayah kw', 'pml.id_kegiatan_wilayah = kw.id_kegiatan_wilayah')
            ->join('master_kegiatan_detail_proses mkdp', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses')
            ->join('master_kegiatan_detail mkd', 'mkdp.id_kegiatan_detail = mkd.id_kegiatan_detail')
            ->join('master_kegiatan mk', 'mkd.id_kegiatan = mk.id_kegiatan')
            ->where('pml.sobat_id', $sobatId)
            ->where('kw.id_kabupaten', $admin['id_kabupaten'])
            ->orderBy('mkdp.tanggal_mulai', 'DESC')
            ->get()
            ->getResultArray();

        // Get kegiatan sebagai PCL
        $kegiatanPCL = $this->pclModel->db->table('pcl')
            ->select('pcl.id_pcl as id, pcl.target, pcl.created_at,
                     mk.nama_kegiatan, mkd.nama_kegiatan_detail, 
                     mkdp.nama_kegiatan_detail_proses, mkdp.tanggal_mulai, mkdp.tanggal_selesai,
                     u_pml.nama_user as nama_pml, "PCL" as role')
            ->join('pml', 'pcl.id_pml = pml.id_pml')
            ->join('sipantau_user u_pml', 'pml.sobat_id = u_pml.sobat_id')
            ->join('kegiatan_wilayah kw', 'pml.id_kegiatan_wilayah = kw.id_kegiatan_wilayah')
            ->join('master_kegiatan_detail_proses mkdp', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses')
            ->join('master_kegiatan_detail mkd', 'mkdp.id_kegiatan_detail = mkd.id_kegiatan_detail')
            ->join('master_kegiatan mk', 'mkd.id_kegiatan = mk.id_kegiatan')
            ->where('pcl.sobat_id', $sobatId)
            ->where('kw.id_kabupaten', $admin['id_kabupaten'])
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
            'active_menu' => 'data-petugas',
            'petugas' => $petugas,
            'kegiatanList' => $kegiatanList
        ];

        return view('AdminSurveiKab/DataPetugas/detail_petugas', $data);
    }

    /**
     * Detail PCL - laporan progress dan transaksi
     */
    public function detailPCL($idPCL)
    {
        $sobatId = session()->get('sobat_id');

        // Get admin info
        $admin = $this->adminKabModel->db->table('admin_survei_kabupaten ask')
            ->select('ask.*, u.id_kabupaten')
            ->join('sipantau_user u', 'ask.sobat_id = u.sobat_id')
            ->where('ask.sobat_id', $sobatId)
            ->get()
            ->getRowArray();

        if (!$admin) {
            return redirect()->to('unauthorized');
        }

        // Get PCL detail
        $pclDetail = $this->pclModel->db->table('pcl')
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
            return redirect()->to('adminsurvei-kab/data-petugas')->with('error', 'Data PCL tidak ditemukan');
        }

        // Validasi kabupaten
        if ($pclDetail['id_kabupaten'] != $admin['id_kabupaten']) {
            return redirect()->to('unauthorized')->with('error', 'Anda tidak memiliki akses ke data ini');
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
            'active_menu' => 'data-petugas',
            'pcl' => $pclDetail,
            'target' => $target,
            'realisasi' => $realisasiKumulatif,
            'persentase' => $persentase,
            'selisih' => $selisih,
            'kurvaData' => $kurvaData,
            'idPCL' => $idPCL
        ];

        return view('AdminSurveiKab/DataPetugas/detail_pcl', $data);
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
        $realisasiHarian = $this->userModel->db->query("
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
     * Get Pantau Progress via AJAX
     */
    public function getPantauProgress()
    {
        $idPCL = $this->request->getGet('id_pcl');
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
        $idPCL = $this->request->getGet('id_pcl');
        $page = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        $builder = $this->userModel->db->table('sipantau_transaksi st');

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

    /**
     * Save Feedback PCL via AJAX
     */
    public function saveFeedbackPCL()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request method'
            ]);
        }

        $idPCL = $this->request->getPost('id_pcl');
        $feedback = trim($this->request->getPost('feedback'));
        $rating = (int) $this->request->getPost('rating'); // TAMBAHKAN INI

        if (empty($idPCL)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID PCL tidak ditemukan'
            ]);
        }

        if (empty($feedback)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Feedback tidak boleh kosong'
            ]);
        }

        // VALIDASI RATING
        if ($rating < 1 || $rating > 5) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Rating harus antara 1-5'
            ]);
        }

        $pcl = $this->pclModel->find($idPCL);
        if (!$pcl) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Data PCL tidak ditemukan'
            ]);
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
                'message' => 'Unauthorized access'
            ]);
        }

        $pclUser = $this->userModel->where('sobat_id', $pcl['sobat_id'])->first();
        if ($pclUser['id_kabupaten'] != $admin['id_kabupaten']) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke data PCL ini'
            ]);
        }

        // UPDATE DENGAN RATING
        $updated = $this->pclModel->update($idPCL, [
            'feedback_admin' => $feedback,
            'rating' => $rating,  // TAMBAHKAN INI
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        if ($updated) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Feedback dan rating berhasil disimpan',
                'feedback' => $feedback,
                'rating' => $rating  // TAMBAHKAN INI
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal menyimpan feedback dan rating'
            ]);
        }
    }

    /**
     * Detail PML - progress dari PCL yang dipegang
     */
    public function detailPML($idPML)
    {
        $sobatId = session()->get('sobat_id');

        // Get admin info
        $admin = $this->adminKabModel->db->table('admin_survei_kabupaten ask')
            ->select('ask.*, u.id_kabupaten')
            ->join('sipantau_user u', 'ask.sobat_id = u.sobat_id')
            ->where('ask.sobat_id', $sobatId)
            ->get()
            ->getRowArray();

        if (!$admin) {
            return redirect()->to('unauthorized');
        }

        // Get PML detail
        $pmlDetail = $this->pmlModel->getPMLWithDetails($idPML);

        if (!$pmlDetail) {
            return redirect()->to('adminsurvei-kab/data-petugas')->with('error', 'Data PML tidak ditemukan');
        }
        // Validasi kabupaten
        if ($pmlDetail['id_kabupaten'] != $admin['id_kabupaten']) {
            return redirect()->to('unauthorized')->with('error', 'Anda tidak memiliki akses ke data ini');
        }

        // Get daftar PCL beserta progress
        $pantauProgressModel = new \App\Models\PantauProgressModel();

        $pclList = $this->pclModel->db->table('pcl')
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
            'active_menu' => 'data-petugas',
            'pml' => $pmlDetail,
            'pclList' => $pclList
        ];

        return view('AdminSurveiKab/DataPetugas/detail_pml', $data);
    }


}