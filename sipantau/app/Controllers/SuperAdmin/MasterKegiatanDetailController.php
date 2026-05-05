<?php

namespace App\Controllers\SuperAdmin;

use App\Controllers\BaseController;
use App\Models\MasterKegiatanDetailModel;
use App\Models\MasterKegiatanModel;
use App\Models\MasterOutputModel;
use App\Models\MasterKegiatanDetailAdminModel;
use App\Models\MasterKegiatanDetailProsesModel;
use App\Models\MasterKegiatanWilayahModel;
use App\Models\KurvaSProvinsiModel;

class MasterKegiatanDetailController extends BaseController
{
    protected $masterKegiatanDetailModel;
    protected $masterKegiatanModel;
    protected $masterOutputModel;
    protected $masterKegiatanDetailAdminModel;
    protected $masterKegiatanDetailProsesModel;
    protected $masterKegiatanWilayahModel;
    protected $validation;
    protected $db;

    public function __construct()
    {
        $this->masterKegiatanDetailModel = new MasterKegiatanDetailModel();
        $this->masterKegiatanModel = new MasterKegiatanModel();
        $this->masterOutputModel = new MasterOutputModel();
        $this->masterKegiatanDetailAdminModel = new MasterKegiatanDetailAdminModel();
        $this->masterKegiatanDetailProsesModel = new MasterKegiatanDetailProsesModel();
        $this->masterKegiatanWilayahModel = new MasterKegiatanWilayahModel();
        $this->validation = \Config\Services::validation();
        $this->db = \Config\Database::connect();
    }

    // ====================================================================
    // Index - Menampilkan halaman daftar master kegiatan detail
    // ====================================================================
    public function index()
    {
        $filterKegiatan = $this->request->getGet('kegiatan');

        // Ambil perPage dari GET, default 10
        $perPage = $this->request->getGet('perPage') ?? 10;

        // Validasi perPage agar hanya nilai yang diizinkan
        $allowedPerPage = [5, 10, 25, 50, 100];
        if (!in_array((int) $perPage, $allowedPerPage)) {
            $perPage = 10;
        }

        // Get all master kegiatan untuk filter
        $masterKegiatans = $this->masterKegiatanModel->getWithOutput();

        // Get kegiatan detail dengan filter dan pagination
        if ($filterKegiatan && $filterKegiatan != 'all') {
            $details = $this->masterKegiatanDetailModel
                ->select('
                master_kegiatan_detail.*,
                master_kegiatan.nama_kegiatan,
                master_kegiatan.periode as periode_kegiatan
            ')
                ->join('master_kegiatan', 'master_kegiatan.id_kegiatan = master_kegiatan_detail.id_kegiatan', 'left')
                ->where('master_kegiatan_detail.id_kegiatan', $filterKegiatan)
                ->orderBy('master_kegiatan_detail.id_kegiatan_detail', 'DESC')
                ->paginate($perPage, 'details');
        } else {
            $details = $this->masterKegiatanDetailModel
                ->select('
                master_kegiatan_detail.*,
                master_kegiatan.nama_kegiatan,
                master_kegiatan.periode as periode_kegiatan
            ')
                ->join('master_kegiatan', 'master_kegiatan.id_kegiatan = master_kegiatan_detail.id_kegiatan', 'left')
                ->orderBy('master_kegiatan_detail.id_kegiatan_detail', 'DESC')
                ->paginate($perPage, 'details');
        }

        // Get admin untuk setiap kegiatan detail
        foreach ($details as &$detail) {
            $detail['admin_list'] = $this->masterKegiatanDetailAdminModel->getAdminByKegiatanDetail($detail['id_kegiatan_detail']);
        }

        $data = [
            'title' => 'Kelola Master Kegiatan Detail',
            'active_menu' => 'master-kegiatan-detail',
            'details' => $details,
            'masterKegiatans' => $masterKegiatans,
            'filterKegiatan' => $filterKegiatan ?? 'all',
            'perPage' => $perPage,
            'pager' => $this->masterKegiatanDetailModel->pager
        ];

        return view('SuperAdmin/MasterKegiatanDetail/index', $data);
    }

    // ====================================================================
    // Create - Menampilkan form tambah master kegiatan detail
    // ====================================================================
    public function create()
    {
        $idKegiatan = $this->request->getGet('id_kegiatan');
        $masterKegiatans = $this->masterKegiatanModel->getWithOutput();

        $data = [
            'title' => 'Tambah Master Kegiatan Detail',
            'active_menu' => 'master-kegiatan-detail',
            'validation' => $this->validation,
            'masterKegiatans' => $masterKegiatans,
            'idKegiatan' => $idKegiatan
        ];

        return view('SuperAdmin/MasterKegiatanDetail/create', $data);
    }

    // ====================================================================
    // Store - Menyimpan data master kegiatan detail baru
    // ====================================================================
    public function store()
    {
        $rules = [
            'id_kegiatan' => [
                'rules' => 'required|numeric',
                'errors' => [
                    'required' => 'Master kegiatan harus dipilih',
                    'numeric' => 'Master kegiatan tidak valid'
                ]
            ],
            'nama_kegiatan_detail' => [
                'rules' => 'required|max_length[255]',
                'errors' => [
                    'required' => 'Nama kegiatan detail harus diisi',
                    'max_length' => 'Nama kegiatan detail maksimal 255 karakter'
                ]
            ],
            'satuan' => [
                'rules' => 'required|max_length[100]',
                'errors' => [
                    'required' => 'Satuan harus diisi',
                    'max_length' => 'Satuan maksimal 100 karakter'
                ]
            ],
            'periode' => [
                'rules' => 'required|max_length[50]',
                'errors' => [
                    'required' => 'Periode harus diisi',
                    'max_length' => 'Periode maksimal 50 karakter'
                ]
            ],
            'tahun' => [
                'rules' => 'required|numeric|exact_length[4]',
                'errors' => [
                    'required' => 'Tahun harus diisi',
                    'numeric' => 'Tahun harus berupa angka',
                    'exact_length' => 'Tahun harus 4 digit'
                ]
            ],
            'tanggal_mulai' => [
                'rules' => 'permit_empty|valid_date',
                'errors' => [
                    'valid_date' => 'Format tanggal mulai tidak valid'
                ]
            ],
            'tanggal_selesai' => [
                'rules' => 'permit_empty|valid_date',
                'errors' => [
                    'valid_date' => 'Format tanggal selesai tidak valid'
                ]
            ],
            'keterangan' => [
                'rules' => 'permit_empty',
                'errors' => []
            ]
        ];

        if (!$this->validate($rules)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $data = [
            'id_kegiatan' => $this->request->getPost('id_kegiatan'),
            'nama_kegiatan_detail' => $this->request->getPost('nama_kegiatan_detail'),
            'satuan' => $this->request->getPost('satuan'),
            'periode' => $this->request->getPost('periode'),
            'tahun' => $this->request->getPost('tahun'),
            'tanggal_mulai' => $this->request->getPost('tanggal_mulai') ?: null,
            'tanggal_selesai' => $this->request->getPost('tanggal_selesai') ?: null,
            'keterangan' => $this->request->getPost('keterangan')
        ];

        if ($this->masterKegiatanDetailModel->insert($data)) {
            return redirect()
                ->to(base_url('superadmin/master-kegiatan-detail'))
                ->with('success', 'Data master kegiatan detail berhasil ditambahkan');
        } else {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal menyimpan data master kegiatan detail');
        }
    }

    // ====================================================================
    // Show - Menampilkan detail master kegiatan detail
    // ====================================================================
    public function show($id)
    {
        $detail = $this->masterKegiatanDetailModel->getWithKegiatanById($id);

        if (!$detail) {
            return redirect()
                ->to(base_url('superadmin/master-kegiatan-detail'))
                ->with('error', 'Data master kegiatan detail tidak ditemukan');
        }

        // Ambil perPage dari GET, default 10
        $perPage = $this->request->getGet('perPage') ?? 10;

        // Validasi perPage
        $allowedPerPage = [5, 10, 25, 50, 100];
        if (!in_array((int) $perPage, $allowedPerPage)) {
            $perPage = 10;
        }

        // Get detail proses yang terkait dengan kegiatan detail ini dengan pagination
        $detailProses = $this->masterKegiatanDetailProsesModel
            ->where('id_kegiatan_detail', $id)
            ->orderBy('created_at', 'DESC')
            ->paginate($perPage, 'detailProses');

        $data = [
            'title' => 'Detail Kegiatan Detail',
            'active_menu' => 'master-kegiatan-detail',
            'detail' => $detail,
            'detailProses' => $detailProses,
            'perPage' => $perPage,
            'pager' => $this->masterKegiatanDetailProsesModel->pager
        ];

        return view('SuperAdmin/MasterKegiatanDetail/show', $data);
    }

    // ====================================================================
    // Edit - Menampilkan form edit master kegiatan detail
    // ====================================================================
    public function edit($id)
    {
        $detail = $this->masterKegiatanDetailModel->find($id);

        if (!$detail) {
            return redirect()
                ->to(base_url('superadmin/master-kegiatan-detail'))
                ->with('error', 'Data master kegiatan detail tidak ditemukan');
        }

        $masterKegiatans = $this->masterKegiatanModel->getWithOutput();

        $data = [
            'title' => 'Edit Master Kegiatan Detail',
            'active_menu' => 'master-kegiatan-detail',
            'detail' => $detail,
            'validation' => $this->validation,
            'masterKegiatans' => $masterKegiatans
        ];

        return view('SuperAdmin/MasterKegiatanDetail/edit', $data);
    }

    // ====================================================================
    // Update - Memperbarui data master kegiatan detail
    // ====================================================================
    public function update($id)
    {
        $detail = $this->masterKegiatanDetailModel->find($id);

        if (!$detail) {
            return redirect()
                ->to(base_url('superadmin/master-kegiatan-detail'))
                ->with('error', 'Data master kegiatan detail tidak ditemukan');
        }

        $rules = [
            'id_kegiatan' => [
                'rules' => 'required|numeric',
                'errors' => [
                    'required' => 'Master kegiatan harus dipilih',
                    'numeric' => 'Master kegiatan tidak valid'
                ]
            ],
            'nama_kegiatan_detail' => [
                'rules' => 'required|max_length[255]',
                'errors' => [
                    'required' => 'Nama kegiatan detail harus diisi',
                    'max_length' => 'Nama kegiatan detail maksimal 255 karakter'
                ]
            ],
            'satuan' => [
                'rules' => 'required|max_length[100]',
                'errors' => [
                    'required' => 'Satuan harus diisi',
                    'max_length' => 'Satuan maksimal 100 karakter'
                ]
            ],
            'periode' => [
                'rules' => 'required|max_length[50]',
                'errors' => [
                    'required' => 'Periode harus diisi',
                    'max_length' => 'Periode maksimal 50 karakter'
                ]
            ],
            'tahun' => [
                'rules' => 'required|numeric|exact_length[4]',
                'errors' => [
                    'required' => 'Tahun harus diisi',
                    'numeric' => 'Tahun harus berupa angka',
                    'exact_length' => 'Tahun harus 4 digit'
                ]
            ],
            'tanggal_mulai' => [
                'rules' => 'permit_empty|valid_date',
                'errors' => [
                    'valid_date' => 'Format tanggal mulai tidak valid'
                ]
            ],
            'tanggal_selesai' => [
                'rules' => 'permit_empty|valid_date',
                'errors' => [
                    'valid_date' => 'Format tanggal selesai tidak valid'
                ]
            ],
            'keterangan' => [
                'rules' => 'permit_empty',
                'errors' => []
            ]
        ];

        if (!$this->validate($rules)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $data = [
            'id_kegiatan' => $this->request->getPost('id_kegiatan'),
            'nama_kegiatan_detail' => $this->request->getPost('nama_kegiatan_detail'),
            'satuan' => $this->request->getPost('satuan'),
            'periode' => $this->request->getPost('periode'),
            'tahun' => $this->request->getPost('tahun'),
            'tanggal_mulai' => $this->request->getPost('tanggal_mulai') ?: null,
            'tanggal_selesai' => $this->request->getPost('tanggal_selesai') ?: null,
            'keterangan' => $this->request->getPost('keterangan')
        ];

        if ($this->masterKegiatanDetailModel->update($id, $data)) {
            return redirect()
                ->to(base_url('superadmin/master-kegiatan-detail'))
                ->with('success', 'Data master kegiatan detail berhasil diperbarui');
        } else {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui data master kegiatan detail');
        }
    }

    // ====================================================================
    // Delete - Menghapus data master kegiatan detail
    // ====================================================================
    public function delete($id)
    {
        // Pastikan request adalah AJAX/DELETE
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request method'
            ]);
        }

        $detail = $this->masterKegiatanDetailModel->find($id);

        if (!$detail) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Data master kegiatan detail tidak ditemukan'
            ]);
        }

        // Cek apakah ada kegiatan detail proses yang terkait
        $hasProses = $this->masterKegiatanDetailProsesModel
            ->where('id_kegiatan_detail', $id)
            ->countAllResults();

        if ($hasProses > 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tidak dapat menghapus kegiatan detail karena masih memiliki ' . $hasProses . ' proses terkait. Hapus proses terlebih dahulu.'
            ]);
        }

        if ($this->masterKegiatanDetailModel->delete($id)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Data master kegiatan detail berhasil dihapus'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal menghapus data master kegiatan detail'
            ]);
        }
    }

    // ====================================================================
    // Get Admins - AJAX endpoint untuk mengambil daftar admin
    // ====================================================================
    public function getAdmins($id)
    {
        $admins = $this->masterKegiatanDetailAdminModel->getAdminByKegiatanDetail($id);

        return $this->response->setJSON([
            'success' => true,
            'admins' => $admins
        ]);
    }

    // ====================================================================
    // Show Kegiatan Wilayah - PERBAIKAN untuk Super Admin
    // ====================================================================
    public function showKegiatanWilayah($idKegiatanDetailProses)
    {
        // Ambil perPage dari GET, default 10
        $perPage = $this->request->getGet('perPage') ?? 10;

        // Validasi perPage
        $allowedPerPage = [5, 10, 25, 50, 100];
        if (!in_array((int) $perPage, $allowedPerPage)) {
            $perPage = 10;
        }

        // Get current page
        $page = $this->request->getGet('page_kegiatanWilayah') ?? 1;

        // Get detail proses dengan informasi lengkap
        $detailProses = $this->db->table('master_kegiatan_detail_proses mkdp')
            ->select('mkdp.*, mkd.nama_kegiatan_detail, mkd.satuan as satuan_detail, 
                  mkg.nama_kegiatan, mkd.periode as periode_detail, mkd.id_kegiatan_detail')
            ->join('master_kegiatan_detail mkd', 'mkd.id_kegiatan_detail = mkdp.id_kegiatan_detail')
            ->join('master_kegiatan mkg', 'mkg.id_kegiatan = mkd.id_kegiatan')
            ->where('mkdp.id_kegiatan_detail_proses', $idKegiatanDetailProses)
            ->get()
            ->getRowArray();

        if (!$detailProses) {
            return redirect()
                ->to(base_url('superadmin/master-kegiatan-detail'))
                ->with('error', 'Data kegiatan detail proses tidak ditemukan');
        }

        // Count total records
        $totalRecords = $this->db->table('kegiatan_wilayah kw')
            ->where('kw.id_kegiatan_detail_proses', $idKegiatanDetailProses)
            ->countAllResults();

        // Calculate offset
        $offset = ($page - 1) * $perPage;

        // Get kegiatan wilayah yang terkait dengan pagination manual
        $kegiatanWilayahRaw = $this->db->table('kegiatan_wilayah kw')
            ->select('kw.*, mk.nama_kabupaten, mk.id_kabupaten')
            ->join('master_kabupaten mk', 'mk.id_kabupaten = kw.id_kabupaten')
            ->where('kw.id_kegiatan_detail_proses', $idKegiatanDetailProses)
            ->orderBy('mk.nama_kabupaten', 'ASC')
            ->limit($perPage, $offset)
            ->get()
            ->getResultArray();

        // Hitung realisasi untuk setiap wilayah
        $kegiatanWilayah = [];
        $totalTarget = 0;
        $totalRealisasi = 0;

        foreach ($kegiatanWilayahRaw as $kg) {
            $target = (int) $kg['target_wilayah'];

            // Hitung realisasi dari pantau_progress
            $realisasiData = $this->db->table('pantau_progress pp')
                ->select('COALESCE(SUM(pp.jumlah_realisasi_kumulatif), 0) as total_realisasi', false)
                ->join('pcl', 'pp.id_pcl = pcl.id_pcl')
                ->join('pml', 'pcl.id_pml = pml.id_pml')
                ->where('pml.id_kegiatan_wilayah', $kg['id_kegiatan_wilayah'])
                ->groupBy('pp.id_pcl')
                ->get()
                ->getResultArray();

            $realisasi = 0;
            foreach ($realisasiData as $item) {
                $realisasi += (int) $item['total_realisasi'];
            }

            // Hitung progress
            $progress = $target > 0 ? min(100, ($realisasi / $target) * 100) : 0;

            // Set warna berdasarkan progress
            if ($progress >= 80) {
                $progressColor = '#10b981'; // green
            } elseif ($progress >= 50) {
                $progressColor = '#3b82f6'; // blue
            } elseif ($progress >= 25) {
                $progressColor = '#f59e0b'; // orange
            } else {
                $progressColor = '#ef4444'; // red
            }

            $kg['realisasi'] = $realisasi;
            $kg['progress'] = round($progress, 1);
            $kg['progress_color'] = $progressColor;

            $kegiatanWilayah[] = $kg;

            $totalTarget += $target;
            $totalRealisasi += $realisasi;
        }

        // Hitung progress rata-rata
        $avgProgress = $totalTarget > 0 ? ($totalRealisasi / $totalTarget) * 100 : 0;

        // Create manual pager
        $pager = \Config\Services::pager();
        $pager->store('kegiatanWilayah', $page, $perPage, $totalRecords);

        $data = [
            'title' => 'Daftar Kegiatan Wilayah',
            'active_menu' => 'master-kegiatan-detail',
            'detailProses' => $detailProses,
            'kegiatanWilayah' => $kegiatanWilayah,
            'totalTarget' => $totalTarget,
            'totalRealisasi' => $totalRealisasi,
            'avgProgress' => round($avgProgress, 1),
            'perPage' => $perPage,
            'pager' => $pager
        ];

        return view('SuperAdmin/MasterKegiatanDetail/kegiatan_wilayah', $data);
    }

    // ====================================================================
    // ENDPOINT BARU: Get Kurva S Provinsi untuk Super Admin
    // ====================================================================
    public function getKurvaProvinsi()
    {
        $idProses = $this->request->getGet('id_kegiatan_detail_proses');

        if (!$idProses) {
            return $this->response->setJSON([
                'labels' => [],
                'targetPersen' => [],
                'targetAbsolut' => [],
                'targetHarian' => [],
                'realisasiAbsolut' => [],
                'realisasiPersen' => []
            ]);
        }

        $kurvaSModel = new KurvaSProvinsiModel();

        // Get data kurva S target
        $records = $kurvaSModel
            ->select('tanggal_target, target_persen_kumulatif, target_kumulatif_absolut, target_harian_absolut')
            ->where('id_kegiatan_detail_proses', $idProses)
            ->orderBy('tanggal_target', 'ASC')
            ->findAll();

        // Get total target dari detail proses
        $detailProses = $this->masterKegiatanDetailProsesModel->find($idProses);
        $totalTarget = $detailProses ? (int) $detailProses['target'] : 0;

        // Get data realisasi kumulatif per tanggal
        $realisasiData = $this->db->query("
            SELECT 
                DATE(pp.created_at) as tanggal_realisasi,
                SUM(pp.jumlah_realisasi_kumulatif) as total_realisasi_harian
            FROM pantau_progress pp
            JOIN pcl ON pp.id_pcl = pcl.id_pcl
            JOIN pml ON pcl.id_pml = pml.id_pml
            JOIN kegiatan_wilayah kw ON pml.id_kegiatan_wilayah = kw.id_kegiatan_wilayah
            WHERE kw.id_kegiatan_detail_proses = ?
            GROUP BY DATE(pp.created_at)
            ORDER BY DATE(pp.created_at) ASC
        ", [$idProses])->getResultArray();

        return $this->response->setJSON($this->formatKurvaDataWithRealisasi($records, $realisasiData, $totalTarget));
    }

    // ====================================================================
    // Helper: format JSON kurva dengan realisasi
    // ====================================================================
    private function formatKurvaDataWithRealisasi($records, $realisasiData, $totalTarget)
    {
        if (empty($records)) {
            return [
                'labels' => [],
                'targetPersen' => [],
                'targetAbsolut' => [],
                'targetHarian' => [],
                'realisasiAbsolut' => [],
                'realisasiPersen' => []
            ];
        }

        // Filter duplikat target berdasarkan tanggal_target
        $unique = [];
        foreach ($records as $row) {
            $tgl = $row['tanggal_target'];
            if (!isset($unique[$tgl])) {
                $unique[$tgl] = $row;
            }
        }

        // Urutkan berdasarkan tanggal
        ksort($unique);

        // Proses data realisasi menjadi array dengan key tanggal
        $realisasiByDate = [];
        foreach ($realisasiData as $real) {
            $realisasiByDate[$real['tanggal_realisasi']] = (int) $real['total_realisasi_harian'];
        }

        $labels = [];
        $targetPersen = [];
        $targetAbsolut = [];
        $targetHarian = [];
        $realisasiAbsolut = [];
        $realisasiPersen = [];

        $realisasiKumulatif = 0;

        foreach ($unique as $row) {
            $tanggal = $row['tanggal_target'];
            $labels[] = date('d M', strtotime($tanggal));
            $targetPersen[] = (float) $row['target_persen_kumulatif'];
            $targetAbsolut[] = (int) $row['target_kumulatif_absolut'];
            $targetHarian[] = (int) $row['target_harian_absolut'];

            // Tambahkan realisasi harian ke kumulatif jika ada
            if (isset($realisasiByDate[$tanggal])) {
                $realisasiKumulatif += $realisasiByDate[$tanggal];
            }

            $realisasiAbsolut[] = $realisasiKumulatif;

            // Hitung persen realisasi
            $realisasiPersenValue = $totalTarget > 0 ? ($realisasiKumulatif / $totalTarget) * 100 : 0;
            $realisasiPersen[] = round($realisasiPersenValue, 2);
        }

        // Pastikan target kumulatif tidak menurun
        for ($i = 1; $i < count($targetAbsolut); $i++) {
            if ($targetAbsolut[$i] < $targetAbsolut[$i - 1]) {
                $targetAbsolut[$i] = $targetAbsolut[$i - 1];
            }
        }

        return [
            'labels' => array_values($labels),
            'targetPersen' => array_values($targetPersen),
            'targetAbsolut' => array_values($targetAbsolut),
            'targetHarian' => array_values($targetHarian),
            'realisasiAbsolut' => array_values($realisasiAbsolut),
            'realisasiPersen' => array_values($realisasiPersen)
        ];
    }
}