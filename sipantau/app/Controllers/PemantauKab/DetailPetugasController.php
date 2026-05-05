<?php

namespace App\Controllers\PemantauKab;

use App\Controllers\BaseController;
use App\Models\PCLModel;
use App\Models\PMLModel;
use App\Models\KurvaPetugasModel;
use App\Models\PantauProgressModel;
use App\Models\SipantauTransaksiModel;
use App\Models\MasterKegiatanDetailProsesModel;
use App\Models\UserModel;

class DetailPetugasController extends BaseController
{
    protected $pclModel;
    protected $pmlModel;
    protected $kurvaPetugasModel;
    protected $pantauProgressModel;
    protected $sipantauTransaksiModel;
    protected $kegiatanProsesModel;
    protected $userModel;
    protected $db;

    public function __construct()
    {
        $this->pclModel = new PCLModel();
        $this->pmlModel = new PMLModel();
        $this->kurvaPetugasModel = new KurvaPetugasModel();
        $this->pantauProgressModel = new PantauProgressModel();
        $this->sipantauTransaksiModel = new SipantauTransaksiModel();
        $this->kegiatanProsesModel = new MasterKegiatanDetailProsesModel();
        $this->userModel = new UserModel();
        $this->db = \Config\Database::connect();
    }

    public function index($idPCL)
    {
        // Validasi role
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

        // Get PCL detail dengan join lengkap
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
            return redirect()->to('pemantau-kabupaten/laporan-petugas')->with('error', 'Data PCL tidak ditemukan');
        }

        // Validasi bahwa PCL ini berada di kabupaten user
        if ($pclDetail['id_kabupaten'] != $idKabupaten) {
            return redirect()->to(base_url('unauthorized'))
                ->with('error', 'Anda tidak memiliki akses ke data petugas ini.');
        }

        // Get realisasi data
        $realisasi = $this->pantauProgressModel
            ->select('COALESCE(MAX(jumlah_realisasi_kumulatif), 0) as total_realisasi')
            ->where('id_pcl', $idPCL)
            ->first();

        $realisasiKumulatif = (int)($realisasi['total_realisasi'] ?? 0);
        $target = (int)$pclDetail['target'];
        $persentase = $target > 0 ? round(($realisasiKumulatif / $target) * 100, 2) : 0;
        $selisih = $target - $realisasiKumulatif;

        // Get Kurva S data
        $kurvaData = $this->getKurvaData($idPCL, $pclDetail);

        $data = [
            'title' => 'Detail Progress PCL',
            'active_menu' => 'laporan-petugas',
            'pcl' => $pclDetail,
            'target' => $target,
            'realisasi' => $realisasiKumulatif,
            'persentase' => $persentase,
            'selisih' => $selisih,
            'kurvaData' => $kurvaData,
            'idPCL' => $idPCL
        ];

        return view('PemantauKabupaten/LaporanPetugas/detail', $data);
    }

    /**
     * Get Kurva S data untuk chart
     */
    private function getKurvaData($idPCL, $pclDetail)
    {
        // Get kurva target dari kurva_petugas
        $kurvaTarget = $this->kurvaPetugasModel
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
            $realisasiLookup[$item['tanggal']] = (int)$item['realisasi_harian'];
        }

        // Format data untuk chart
        $labels = [];
        $targetData = [];
        $realisasiData = [];
        $realisasiKumulatif = 0;

        foreach ($kurvaTarget as $item) {
            $tanggal = $item['tanggal_target'];
            $labels[] = date('d M', strtotime($tanggal));
            $targetData[] = (int)$item['target_kumulatif_absolut'];

            // Add realisasi kumulatif
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
     * Get Pantau Progress data via AJAX
     */
    public function getPantauProgress()
    {
        $idPCL = $this->request->getGet('id_pcl');
        $page = $this->request->getGet('page') ?? 1;
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        // Get total count
        $total = $this->pantauProgressModel
            ->where('id_pcl', $idPCL)
            ->countAllResults(false);

        // Get data with pagination
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

    /**
     * Get Laporan Transaksi data via AJAX - OPTIMIZED
     */
    public function getLaporanTransaksi()
    {
        $idPCL = $this->request->getGet('id_pcl');
        $page = (int)($this->request->getGet('page') ?? 1);
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        // Optimized: Single query dengan subquery untuk get id_kegiatan_detail_proses
        $builder = $this->db->table('sipantau_transaksi st');
        
        // Get total count first (faster without joins)
        $totalBuilder = clone $builder;
        $total = $totalBuilder
            ->where('st.id_pcl', $idPCL)
            ->countAllResults();

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

        // Get data with optimized query
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