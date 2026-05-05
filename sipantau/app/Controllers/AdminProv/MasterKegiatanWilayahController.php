<?php

namespace App\Controllers\AdminProv;

use App\Controllers\BaseController;
use App\Models\MasterKabModel;
use App\Models\MasterKegiatanDetailProsesModel;
use App\Models\MasterKegiatanWilayahModel;
use App\Models\MasterKegiatanDetailAdminModel;
use App\Models\MasterKegiatanDetailModel;
use App\Models\KurvaSkabModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use DateInterval;
use DatePeriod;
use DateTime;

class MasterKegiatanWilayahController extends BaseController
{
    protected $masterDetailProsesModel;
    protected $masterKegiatanWilayahModel;
    protected $masterDetailAdminModel;
    protected $masterDetailModel;
    protected $masterKab;
    protected $validation;
    protected $db;

    public function __construct()
    {
        $this->masterDetailProsesModel = new MasterKegiatanDetailProsesModel();
        $this->masterKegiatanWilayahModel = new MasterKegiatanWilayahModel();
        $this->masterDetailAdminModel = new MasterKegiatanDetailAdminModel();
        $this->masterDetailModel = new MasterKegiatanDetailModel();
        $this->validation = \Config\Services::validation();
        $this->masterKab = new MasterKabModel();
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        // Get role dan admin ID dari session
        $role = session()->get('role');
        $roleType = session()->get('role_type');
        $idAdminProvinsi = session()->get('id_admin_provinsi');

        $isSuperAdmin = ($role == 1);
        $isAdminProvinsi = ($role == 2 && $roleType == 'admin_provinsi' && $idAdminProvinsi);

        if (!$isSuperAdmin && !$isAdminProvinsi) {
            return redirect()->to(base_url('unauthorized'))
                ->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }

        // Get filter parameters dari GET atau session
        $filterKegiatan = $this->request->getGet('kegiatan_detail') ?? session()->get('kegiatan_wilayah_filter_detail');
        $filterProses = $this->request->getGet('kegiatan_proses') ?? session()->get('kegiatan_wilayah_filter_proses');
        $filterKabupaten = $this->request->getGet('kabupaten') ?? session()->get('kegiatan_wilayah_filter_kabupaten');

        // Simpan filter ke session
        if ($this->request->getGet('kegiatan_detail') !== null) {
            session()->set('kegiatan_wilayah_filter_detail', $filterKegiatan);
        }
        if ($this->request->getGet('kegiatan_proses') !== null) {
            session()->set('kegiatan_wilayah_filter_proses', $filterProses);
        }
        if ($this->request->getGet('kabupaten') !== null) {
            session()->set('kegiatan_wilayah_filter_kabupaten', $filterKabupaten);
        }

        // Ambil perPage dari GET, default 10
        $perPage = $this->request->getGet('perPage') ?? 10;

        // Validasi perPage agar hanya nilai yang diizinkan
        $allowedPerPage = [5, 10, 25, 50, 100];
        if (!in_array((int) $perPage, $allowedPerPage)) {
            $perPage = 10;
        }

        // Build query untuk pagination
        $builder = $this->masterKegiatanWilayahModel
            ->select('kegiatan_wilayah.*, 
              master_kegiatan_detail_proses.nama_kegiatan_detail_proses, 
              master_kegiatan_detail_proses.target as target_proses,
              master_kegiatan_detail_proses.tanggal_mulai, 
              master_kegiatan_detail_proses.tanggal_selesai,
              master_kabupaten.nama_kabupaten,
              master_kegiatan_detail.nama_kegiatan_detail,
              master_kegiatan.nama_kegiatan,
              master_kegiatan_detail_proses.id_kegiatan_detail')
            ->join('master_kegiatan_detail_proses', 'kegiatan_wilayah.id_kegiatan_detail_proses = master_kegiatan_detail_proses.id_kegiatan_detail_proses')
            ->join('master_kegiatan_detail', 'master_kegiatan_detail_proses.id_kegiatan_detail = master_kegiatan_detail.id_kegiatan_detail')
            ->join('master_kegiatan', 'master_kegiatan_detail.id_kegiatan = master_kegiatan.id_kegiatan')
            ->join('master_kabupaten', 'kegiatan_wilayah.id_kabupaten = master_kabupaten.id_kabupaten');

        // Filter berdasarkan assignment untuk Admin Provinsi
        if ($isAdminProvinsi) {
            $builder->join('master_kegiatan_detail_admin', 'master_kegiatan_detail_admin.id_kegiatan_detail = master_kegiatan_detail_proses.id_kegiatan_detail')
                ->where('master_kegiatan_detail_admin.id_admin_provinsi', $idAdminProvinsi);
        }

        // Apply filters
        if (!empty($filterKegiatan)) {
            $builder->where('master_kegiatan_detail_proses.id_kegiatan_detail', $filterKegiatan);
        }

        if (!empty($filterProses)) {
            $builder->where('kegiatan_wilayah.id_kegiatan_detail_proses', $filterProses);
        }

        if (!empty($filterKabupaten)) {
            $builder->where('kegiatan_wilayah.id_kabupaten', $filterKabupaten);
        }

        $builder->orderBy('master_kegiatan_detail.nama_kegiatan_detail', 'ASC')
            ->orderBy('master_kabupaten.nama_kabupaten', 'ASC');

        // Paginate
        $kegiatanWilayah = $builder->paginate($perPage, 'kegiatan_wilayah');

        // Calculate progress for each kegiatan wilayah
        foreach ($kegiatanWilayah as &$kw) {
            $targetWilayah = (int) $kw['target_wilayah'];

            $realisasi = $this->db->table('pantau_progress pp')
                ->select('COALESCE(SUM(pp.jumlah_realisasi_kumulatif), 0) as total_realisasi', false)
                ->join('pcl', 'pp.id_pcl = pcl.id_pcl')
                ->join('pml', 'pcl.id_pml = pml.id_pml')
                ->where('pml.id_kegiatan_wilayah', $kw['id_kegiatan_wilayah'])
                ->groupBy('pp.id_pcl')
                ->get()
                ->getResultArray();

            $totalRealisasi = 0;
            foreach ($realisasi as $item) {
                $totalRealisasi += (int) $item['total_realisasi'];
            }

            if ($targetWilayah > 0) {
                $progressPercentage = min(100, round(($totalRealisasi / $targetWilayah) * 100, 1));
            } else {
                $progressPercentage = 0;
            }

            $kw['realisasi'] = $totalRealisasi;
            $kw['progress'] = $progressPercentage;

            if ($progressPercentage >= 80) {
                $kw['progress_color'] = '#10b981';
            } elseif ($progressPercentage >= 50) {
                $kw['progress_color'] = '#3b82f6';
            } elseif ($progressPercentage >= 25) {
                $kw['progress_color'] = '#f59e0b';
            } else {
                $kw['progress_color'] = '#ef4444';
            }
        }

        // Get all kegiatan detail for filter (filtered by assignment for Admin Provinsi)
        if ($isSuperAdmin) {
            $allKegiatanDetail = $this->db->table('master_kegiatan_detail mkd')
                ->select('mkd.id_kegiatan_detail, mkd.nama_kegiatan_detail, mkg.nama_kegiatan')
                ->join('master_kegiatan mkg', 'mkd.id_kegiatan = mkg.id_kegiatan')
                ->orderBy('mkg.nama_kegiatan', 'ASC')
                ->orderBy('mkd.nama_kegiatan_detail', 'ASC')
                ->get()
                ->getResultArray();
        } else {
            $allKegiatanDetail = $this->db->table('master_kegiatan_detail mkd')
                ->select('mkd.id_kegiatan_detail, mkd.nama_kegiatan_detail, mkg.nama_kegiatan')
                ->join('master_kegiatan mkg', 'mkd.id_kegiatan = mkg.id_kegiatan')
                ->join('master_kegiatan_detail_admin mkda', 'mkda.id_kegiatan_detail = mkd.id_kegiatan_detail')
                ->where('mkda.id_admin_provinsi', $idAdminProvinsi)
                ->orderBy('mkg.nama_kegiatan', 'ASC')
                ->orderBy('mkd.nama_kegiatan_detail', 'ASC')
                ->get()
                ->getResultArray();
        }

        // Get kegiatan detail proses berdasarkan filter kegiatan detail
        $allKegiatanDetailProses = [];
        if (!empty($filterKegiatan)) {
            $allKegiatanDetailProses = $this->masterDetailProsesModel
                ->where('id_kegiatan_detail', $filterKegiatan)
                ->orderBy('nama_kegiatan_detail_proses', 'ASC')
                ->findAll();
        }

        // Get all kabupaten for filter
        $allKabupaten = $this->masterKab->orderBy('id_kabupaten', 'ASC')->findAll();

        // Get all kegiatan detail proses for modal (filtered by assignment for Admin Provinsi)
        $filterAdminId = $isSuperAdmin ? null : $idAdminProvinsi;
        $allKegiatanDetailProsesForModal = $this->masterDetailProsesModel->getData(null, $filterAdminId);

        $data = [
            'title' => 'Kelola Master Kegiatan Wilayah',
            'active_menu' => 'master-kegiatan-wilayah',
            'kegiatanWilayah' => $kegiatanWilayah,
            'allKegiatanDetail' => $allKegiatanDetail,
            'allKegiatanDetailProses' => $allKegiatanDetailProses,
            'allKegiatanDetailProsesForModal' => $allKegiatanDetailProsesForModal,
            'allKabupaten' => $allKabupaten,
            'filterKegiatan' => $filterKegiatan,
            'filterProses' => $filterProses,
            'filterKabupaten' => $filterKabupaten,
            'isSuperAdmin' => $isSuperAdmin,
            'perPage' => $perPage,
            'pager' => $this->masterKegiatanWilayahModel->pager,
        ];
        return view('AdminSurveiProv/MasterKegiatanWilayah/index', $data);
    }

    public function store()
    {
        // Get role dan admin ID dari session
        $role = session()->get('role');
        $roleType = session()->get('role_type');
        $idAdminProvinsi = session()->get('id_admin_provinsi');

        $isSuperAdmin = ($role == 1);
        $isAdminProvinsi = ($role == 2 && $roleType == 'admin_provinsi' && $idAdminProvinsi);

        if (!$isSuperAdmin && !$isAdminProvinsi) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Anda tidak memiliki akses.'
            ]);
        }

        $rules = [
            'kegiatan_detail' => 'required|numeric',
            'kabupaten' => 'required|numeric',
            'target' => 'required|numeric',
            'keterangan' => 'required|string|max_length[255]'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'errors' => $this->validator->getErrors()
            ]);
        }

        $idDetailProses = $this->request->getPost('kegiatan_detail');
        $idKabupaten = $this->request->getPost('kabupaten');
        $targetWilayah = (int) $this->request->getPost('target');
        $keterangan = $this->request->getPost('keterangan');

        // Validasi untuk Admin Provinsi: Pastikan kegiatan detail proses ini accessible
        if ($isAdminProvinsi) {
            $detailProses = $this->masterDetailProsesModel->find($idDetailProses);
            if ($detailProses) {
                $hasAccess = $this->masterDetailAdminModel
                    ->where('id_kegiatan_detail', $detailProses['id_kegiatan_detail'])
                    ->where('id_admin_provinsi', $idAdminProvinsi)
                    ->first();

                if (!$hasAccess) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Anda tidak memiliki akses ke kegiatan ini.'
                    ]);
                }
            }
        }

        // Check duplicate
        $existingWilayah = $this->masterKegiatanWilayahModel
            ->where('id_kegiatan_detail_proses', $idDetailProses)
            ->where('id_kabupaten', $idKabupaten)
            ->first();

        if ($existingWilayah) {
            $kabupatenInfo = $this->masterKab->find($idKabupaten);
            $kabupatenName = $kabupatenInfo['nama_kabupaten'] ?? 'Kabupaten ini';

            return $this->response->setJSON([
                'success' => false,
                'message' => $kabupatenName . ' sudah ditambahkan pada kegiatan detail proses ini.'
            ]);
        }

        $detailProses = $this->masterDetailProsesModel->find($idDetailProses);
        if (!$detailProses) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Data kegiatan detail proses tidak ditemukan.'
            ]);
        }

        $targetProv = (int) $detailProses['target'];
        $tanggalMulai = $detailProses['tanggal_mulai'];
        $tanggalSelesai = $detailProses['tanggal_selesai'];
        $tanggal100 = $detailProses['tanggal_selesai_target'] ?? $tanggalSelesai;
        $persenAwal = $detailProses['persentase_target_awal'] ?? 0;

        // Validasi total target
        $totalExisting = $this->masterKegiatanWilayahModel
            ->where('id_kegiatan_detail_proses', $idDetailProses)
            ->selectSum('target_wilayah')
            ->get()
            ->getRow()
            ->target_wilayah ?? 0;

        if (($totalExisting + $targetWilayah) > $targetProv) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Total target wilayah melebihi target provinsi (' . $targetProv . '). Sisa target: ' . ($targetProv - $totalExisting) . '.'
            ]);
        }

        $this->masterKegiatanWilayahModel->insert([
            'id_kegiatan_detail_proses' => $idDetailProses,
            'id_kabupaten' => $idKabupaten,
            'target_wilayah' => $targetWilayah,
            'keterangan' => $keterangan,
            'status' => 'Aktif'
        ]);

        $idWilayah = $this->masterKegiatanWilayahModel->getInsertID();

        // Generate kurva S
        $this->generateKurvaSKab($idWilayah, $targetWilayah, $persenAwal, $tanggalMulai, $tanggal100, $tanggalSelesai);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Data kegiatan wilayah dan Kurva S berhasil dibuat.'
        ]);
    }

    public function edit($id)
    {
        // Tambahkan validasi akses
        $role = session()->get('role');
        $roleType = session()->get('role_type');
        $idAdminProvinsi = session()->get('id_admin_provinsi');

        $isSuperAdmin = ($role == 1);
        $isAdminProvinsi = ($role == 2 && $roleType == 'admin_provinsi' && $idAdminProvinsi);

        $wilayah = $this->masterKegiatanWilayahModel->find($id);
        if (!$wilayah) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ]);
        }

        // Validasi akses untuk Admin Provinsi
        if ($isAdminProvinsi) {
            $detailProses = $this->masterDetailProsesModel->find($wilayah['id_kegiatan_detail_proses']);
            if ($detailProses) {
                $hasAccess = $this->masterDetailAdminModel
                    ->where('id_kegiatan_detail', $detailProses['id_kegiatan_detail'])
                    ->where('id_admin_provinsi', $idAdminProvinsi)
                    ->first();

                if (!$hasAccess) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Anda tidak memiliki akses ke kegiatan ini.'
                    ]);
                }
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $wilayah
        ]);
    }

    public function update($id)
    {
        // Get role dan admin ID dari session
        $role = session()->get('role');
        $roleType = session()->get('role_type');
        $idAdminProvinsi = session()->get('id_admin_provinsi');

        $isSuperAdmin = ($role == 1);
        $isAdminProvinsi = ($role == 2 && $roleType == 'admin_provinsi' && $idAdminProvinsi);

        if (!$isSuperAdmin && !$isAdminProvinsi) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Anda tidak memiliki akses.'
            ]);
        }

        $rules = [
            'kegiatan_detail' => 'required|numeric',
            'kabupaten' => 'required|numeric',
            'target' => 'required|numeric',
            'keterangan' => 'required|string|max_length[255]'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'errors' => $this->validator->getErrors()
            ]);
        }

        $wilayah = $this->masterKegiatanWilayahModel->find($id);
        if (!$wilayah) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Data kegiatan wilayah tidak ditemukan.'
            ]);
        }

        $idDetailProses = $this->request->getPost('kegiatan_detail');
        $idKabupaten = $this->request->getPost('kabupaten');
        $targetWilayah = (int) $this->request->getPost('target');
        $keterangan = $this->request->getPost('keterangan');

        // Validasi untuk Admin Provinsi: Pastikan kegiatan detail proses ini accessible
        if ($isAdminProvinsi) {
            $detailProses = $this->masterDetailProsesModel->find($idDetailProses);
            if ($detailProses) {
                $hasAccess = $this->masterDetailAdminModel
                    ->where('id_kegiatan_detail', $detailProses['id_kegiatan_detail'])
                    ->where('id_admin_provinsi', $idAdminProvinsi)
                    ->first();

                if (!$hasAccess) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Anda tidak memiliki akses ke kegiatan ini.'
                    ]);
                }
            }
        }

        // Check duplicate
        $existingWilayah = $this->masterKegiatanWilayahModel
            ->where('id_kegiatan_detail_proses', $idDetailProses)
            ->where('id_kabupaten', $idKabupaten)
            ->where('id_kegiatan_wilayah !=', $id)
            ->first();

        if ($existingWilayah) {
            $kabupatenInfo = $this->masterKab->find($idKabupaten);
            $kabupatenName = $kabupatenInfo['nama_kabupaten'] ?? 'Kabupaten ini';

            return $this->response->setJSON([
                'success' => false,
                'message' => $kabupatenName . ' sudah ditambahkan pada kegiatan detail proses ini.'
            ]);
        }

        $detailProses = $this->masterDetailProsesModel->find($idDetailProses);
        if (!$detailProses) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Data kegiatan detail proses tidak ditemukan.'
            ]);
        }

        $targetProv = (int) $detailProses['target'];
        $tanggalMulai = $detailProses['tanggal_mulai'];
        $tanggalSelesai = $detailProses['tanggal_selesai'];
        $tanggal100 = $detailProses['tanggal_selesai_target'] ?? $tanggalSelesai;
        $persenAwal = $detailProses['persentase_target_awal'] ?? 0;

        // Validasi total target (exclude current)
        $totalExisting = $this->masterKegiatanWilayahModel
            ->where('id_kegiatan_detail_proses', $idDetailProses)
            ->where('id_kegiatan_wilayah !=', $id)
            ->selectSum('target_wilayah')
            ->get()
            ->getRow()
            ->target_wilayah ?? 0;

        if (($totalExisting + $targetWilayah) > $targetProv) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Total target wilayah melebihi target provinsi (' . number_format($targetProv) . '). Sisa target: ' . number_format($targetProv - $totalExisting) . '.'
            ]);
        }

        // Update data
        $this->masterKegiatanWilayahModel->update($id, [
            'id_kegiatan_detail_proses' => $idDetailProses,
            'id_kabupaten' => $idKabupaten,
            'target_wilayah' => $targetWilayah,
            'keterangan' => $keterangan,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // Regenerate kurva S
        $this->generateKurvaSKab($id, $targetWilayah, $persenAwal, $tanggalMulai, $tanggal100, $tanggalSelesai);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Data kegiatan wilayah dan Kurva S berhasil diperbarui.'
        ]);
    }

    public function delete($id)
    {
        // Get role dan admin ID dari session
        $role = session()->get('role');
        $roleType = session()->get('role_type');
        $idAdminProvinsi = session()->get('id_admin_provinsi');

        $isSuperAdmin = ($role == 1);
        $isAdminProvinsi = ($role == 2 && $roleType == 'admin_provinsi' && $idAdminProvinsi);

        if (!$isSuperAdmin && !$isAdminProvinsi) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Anda tidak memiliki akses.'
            ]);
        }

        $wilayah = $this->masterKegiatanWilayahModel->find($id);
        if (!$wilayah) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Data tidak ditemukan.'
            ]);
        }

        // Validasi akses untuk Admin Provinsi
        if ($isAdminProvinsi) {
            $detailProses = $this->masterDetailProsesModel->find($wilayah['id_kegiatan_detail_proses']);
            if ($detailProses) {
                $hasAccess = $this->masterDetailAdminModel
                    ->where('id_kegiatan_detail', $detailProses['id_kegiatan_detail'])
                    ->where('id_admin_provinsi', $idAdminProvinsi)
                    ->first();

                if (!$hasAccess) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Anda tidak memiliki akses ke kegiatan ini.'
                    ]);
                }
            }
        }

        // Delete kurva S terlebih dahulu
        $kurvaModel = new KurvaSkabModel();
        $kurvaModel->where('id_kegiatan_wilayah', $id)->delete();

        // Delete kegiatan wilayah
        $this->masterKegiatanWilayahModel->delete($id);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Data kegiatan wilayah dan kurva S terkait berhasil dihapus.'
        ]);
    }

    private function generateKurvaSKab(
    $idWilayah,
    $target,
    $persenAwal,
    $tanggalMulai,
    $tanggal100,
    $tanggalSelesai
) {
    $kurvaModel = new KurvaSkabModel();
    $kurvaModel->where('id_kegiatan_wilayah', $idWilayah)->delete();

    $totalTarget = (int) $target;
    $persenAwal  = (float) $persenAwal;

    $start = new DateTime($tanggalMulai);
    $tgl100 = new DateTime($tanggal100);
    $end = new DateTime($tanggalSelesai);
    $end->modify('+1 day');

    // =========================
    // SEMUA HARI (Senin–Minggu)
    // =========================
    $interval = new DateInterval('P1D');
    $allDays = iterator_to_array(new DatePeriod($start, $interval, $end));

    // =========================
    // Hari sampai 100%
    // =========================
    $daysUntil100 = array_filter($allDays, fn($d) => $d <= $tgl100);
    $daysUntil100 = array_values($daysUntil100);

    // =========================
    // Parameter sigmoid
    // =========================
    $daysSigmoid = max(count($daysUntil100), 2);
    $k = 8;
    $x0 = 0.5;

    $sigmoidMin = 1 / (1 + exp(-$k * (0 - $x0)));
    $sigmoidMax = 1 / (1 + exp(-$k * (1 - $x0)));

    // =========================
    // Hitung persen kumulatif per hari
    // =========================
    $dayData = [];
    foreach ($daysUntil100 as $i => $date) {
        $progress = $i / ($daysSigmoid - 1);
        $sigmoid = 1 / (1 + exp(-$k * ($progress - $x0)));
        $normalized = ($sigmoid - $sigmoidMin) / ($sigmoidMax - $sigmoidMin);

        $kumulatifPersen = $persenAwal + (100 - $persenAwal) * $normalized;
        $dayData[$date->format('Y-m-d')] = min($kumulatifPersen, 100);
    }

    // =========================
    // Distribusi target absolut
    // =========================
    $kumulatifAbsolut = 0;
    $insertData = [];

    foreach ($allDays as $date) {
        $currentDate = $date->format('Y-m-d');
        $isHariKerja = $date->format('N') <= 5 ? 1 : 0;

        if (isset($dayData[$currentDate])) {
            $kumulatifPersen = $dayData[$currentDate];
            $harianAbsolut = round(
                ($totalTarget * ($kumulatifPersen / 100)) - $kumulatifAbsolut
            );
            $kumulatifAbsolut += $harianAbsolut;
        } else {
            // Setelah tanggal 100%
            $kumulatifPersen = 100;
            $harianAbsolut = 0;
        }

        $insertData[] = [
            'id_kegiatan_wilayah'       => $idWilayah,
            'tanggal_target'           => $currentDate,
            'target_persen_kumulatif'  => round($kumulatifPersen, 2),
            'target_harian_absolut'    => $harianAbsolut,
            'target_kumulatif_absolut' => $kumulatifAbsolut,
            'is_hari_kerja'            => $isHariKerja,
        ];
    }

    // =========================
    // Koreksi selisih pembulatan
    // =========================
    $selisih = $totalTarget - $kumulatifAbsolut;
    if ($selisih !== 0 && !empty($insertData)) {
        $last = count($insertData) - 1;
        $insertData[$last]['target_harian_absolut'] += $selisih;
        $insertData[$last]['target_kumulatif_absolut'] = $totalTarget;
    }

    // =========================
    // Insert ke database
    // =========================
    $kurvaModel->insertBatch($insertData);
}

    public function getSisaTarget($idKegiatanDetailProses)
    {
        $detail = $this->masterDetailProsesModel->find($idKegiatanDetailProses);
        if (!$detail) {
            return $this->response->setJSON(['error' => 'Data kegiatan detail proses tidak ditemukan.']);
        }

        $targetProv = (int) $detail['target'];
        $terpakai = (int) $this->masterKegiatanWilayahModel
            ->where('id_kegiatan_detail_proses', $idKegiatanDetailProses)
            ->selectSum('target_wilayah')
            ->get()
            ->getRow()
            ->target_wilayah ?? 0;

        $sisa = max($targetProv - $terpakai, 0);

        return $this->response->setJSON([
            'target_prov' => $targetProv,
            'terpakai' => $terpakai,
            'sisa' => $sisa
        ]);
    }

    // Method untuk mendapatkan kegiatan detail proses berdasarkan kegiatan detail
    public function getKegiatanDetailProses($idKegiatanDetail)
    {
        $prosesList = $this->masterDetailProsesModel
            ->where('id_kegiatan_detail', $idKegiatanDetail)
            ->orderBy('nama_kegiatan_detail_proses', 'ASC')
            ->findAll();

        return $this->response->setJSON([
            'success' => true,
            'data' => $prosesList
        ]);
    }

    // Clear filter
    public function clearFilter()
    {
        session()->remove('kegiatan_wilayah_filter_detail');
        session()->remove('kegiatan_wilayah_filter_proses');
        session()->remove('kegiatan_wilayah_filter_kabupaten');
        return redirect()->to(base_url('adminsurvei/master-kegiatan-wilayah'));
    }

    public function downloadTemplate($idKegiatanDetailProses)
    {
        // Get role dan admin ID dari session
        $role = session()->get('role');
        $roleType = session()->get('role_type');
        $idAdminProvinsi = session()->get('id_admin_provinsi');

        $isSuperAdmin = ($role == 1);
        $isAdminProvinsi = ($role == 2 && $roleType == 'admin_provinsi' && $idAdminProvinsi);

        if (!$isSuperAdmin && !$isAdminProvinsi) {
            return redirect()->to(base_url('unauthorized'));
        }

        // Get data kegiatan detail proses
        $detailProses = $this->masterDetailProsesModel->find($idKegiatanDetailProses);
        if (!$detailProses) {
            return redirect()->back()->with('error', 'Data kegiatan detail proses tidak ditemukan');
        }

        // Validasi akses untuk Admin Provinsi
        if ($isAdminProvinsi) {
            $hasAccess = $this->masterDetailAdminModel
                ->where('id_kegiatan_detail', $detailProses['id_kegiatan_detail'])
                ->where('id_admin_provinsi', $idAdminProvinsi)
                ->first();

            if (!$hasAccess) {
                return redirect()->back()->with('error', 'Anda tidak memiliki akses ke kegiatan ini');
            }
        }

        // Get nama kegiatan detail
        $kegiatanDetail = $this->masterDetailModel->find($detailProses['id_kegiatan_detail']);

        // Get kabupaten yang belum di-assign
        $assignedKabupaten = $this->masterKegiatanWilayahModel
            ->where('id_kegiatan_detail_proses', $idKegiatanDetailProses)
            ->findColumn('id_kabupaten');

        $builder = $this->masterKab->orderBy('id_kabupaten', 'ASC');
        if (!empty($assignedKabupaten)) {
            $builder->whereNotIn('id_kabupaten', $assignedKabupaten);
        }
        $availableKabupaten = $builder->findAll();

        // Hitung sisa target
        $targetProv = (int) $detailProses['target'];
        $terpakai = (int) $this->masterKegiatanWilayahModel
            ->where('id_kegiatan_detail_proses', $idKegiatanDetailProses)
            ->selectSum('target_wilayah')
            ->get()
            ->getRow()
            ->target_wilayah ?? 0;
        $sisaTarget = $targetProv - $terpakai;

        $spreadsheet = new Spreadsheet();

        // === SHEET 1: Template Import ===
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Template Import');

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]]
        ];

        // Info Kegiatan
        $sheet1->setCellValue('A1', 'Kegiatan Detail:');
        $sheet1->setCellValue('B1', $kegiatanDetail['nama_kegiatan_detail'] ?? '-');
        $sheet1->mergeCells('B1:D1');
        $sheet1->getStyle('A1')->getFont()->setBold(true);

        $sheet1->setCellValue('A2', 'Kegiatan Detail Proses:');
        $sheet1->setCellValue('B2', $detailProses['nama_kegiatan_detail_proses']);
        $sheet1->mergeCells('B2:D2');
        $sheet1->getStyle('A2')->getFont()->setBold(true);

        $sheet1->setCellValue('A3', 'Target Provinsi:');
        $sheet1->setCellValue('B3', number_format($targetProv));
        $sheet1->getStyle('A3')->getFont()->setBold(true);

        $sheet1->setCellValue('A4', 'Target Terpakai:');
        $sheet1->setCellValue('B4', number_format($terpakai));
        $sheet1->getStyle('A4')->getFont()->setBold(true);

        $sheet1->setCellValue('A5', 'Sisa Target:');
        $sheet1->setCellValue('B5', number_format($sisaTarget));
        $sheet1->getStyle('A5')->getFont()->setBold(true);
        $sheet1->getStyle('B5')->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('008000'));

        // Header tabel
        $headers = [
            'A7' => 'ID Kabupaten',
            'B7' => 'Nama Kabupaten',
            'C7' => 'Target Wilayah',
            'D7' => 'Keterangan'
        ];
        foreach ($headers as $cell => $value) {
            $sheet1->setCellValue($cell, $value);
        }
        $sheet1->getStyle('A7:D7')->applyFromArray($headerStyle);

        // Set column widths
        $sheet1->getColumnDimension('A')->setWidth(15);
        $sheet1->getColumnDimension('B')->setWidth(35);
        $sheet1->getColumnDimension('C')->setWidth(15);
        $sheet1->getColumnDimension('D')->setWidth(40);

        // Format kolom A, B, D sebagai teks
        $sheet1->getStyle('A:A')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
        $sheet1->getStyle('B:B')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
        $sheet1->getStyle('D:D')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);

        // Format kolom C (Target Wilayah) sebagai number tanpa desimal
        $sheet1->getStyle('C:C')->getNumberFormat()->setFormatCode('#,##0');
        // Data kabupaten yang tersedia
        $row = 8;
        foreach ($availableKabupaten as $kab) {
            $sheet1->setCellValue("A{$row}", $kab['id_kabupaten']);
            $sheet1->setCellValue("B{$row}", $kab['nama_kabupaten']);
            $sheet1->setCellValue("C{$row}", ''); // Kosong untuk diisi user
            $sheet1->setCellValue("D{$row}", ''); // Kosong untuk diisi user
            $row++;
        }

        // Add formula for total di baris terakhir + 2
        $lastDataRow = $row - 1;
        $totalRow = $lastDataRow + 2;

        $sheet1->setCellValue("A{$totalRow}", 'TOTAL TARGET:');
        $sheet1->mergeCells("A{$totalRow}:B{$totalRow}");
        $sheet1->getStyle("A{$totalRow}")->getFont()->setBold(true);
        $sheet1->getStyle("A{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Formula SUM untuk total
        $sheet1->setCellValue("C{$totalRow}", "=SUM(C8:C{$lastDataRow})");
        $sheet1->getStyle("C{$totalRow}")->getFont()->setBold(true);
        $sheet1->getStyle("C{$totalRow}")->getNumberFormat()->setFormatCode('#,##0');

        // Sisa target
        $sisaRow = $totalRow + 1;
        $sheet1->setCellValue("A{$sisaRow}", 'SISA TARGET:');
        $sheet1->mergeCells("A{$sisaRow}:B{$sisaRow}");
        $sheet1->getStyle("A{$sisaRow}")->getFont()->setBold(true);
        $sheet1->getStyle("A{$sisaRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Formula untuk sisa
        $sheet1->setCellValue("C{$sisaRow}", "={$sisaTarget}-C{$totalRow}");
        $sheet1->getStyle("C{$sisaRow}")->getFont()->setBold(true);
        $sheet1->getStyle("C{$sisaRow}")->getNumberFormat()->setFormatCode('#,##0');

        // Conditional formatting: hijau jika positif, merah jika negatif
        $conditionalGreen = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $conditionalGreen->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_CELLIS);
        $conditionalGreen->setOperatorType(\PhpOffice\PhpSpreadsheet\Style\Conditional::OPERATOR_GREATERTHANOREQUAL);
        $conditionalGreen->addCondition('0');
        $conditionalGreen->getStyle()->getFont()->getColor()->setARGB('008000'); // Hijau

        $conditionalRed = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $conditionalRed->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_CELLIS);
        $conditionalRed->setOperatorType(\PhpOffice\PhpSpreadsheet\Style\Conditional::OPERATOR_LESSTHAN);
        $conditionalRed->addCondition('0');
        $conditionalRed->getStyle()->getFont()->getColor()->setARGB('FF0000'); // Merah

        $conditionalStyles = [$conditionalGreen, $conditionalRed];
        $sheet1->getStyle("C{$sisaRow}")->setConditionalStyles($conditionalStyles);
        // === SHEET 2: Panduan ===
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Panduan');

        $sheet2->setCellValue('A1', 'PANDUAN PENGISIAN TEMPLATE');
        $sheet2->mergeCells('A1:D1');
        $sheet2->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet2->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet2->setCellValue('A3', '1. Isi kolom "Target Wilayah" dengan jumlah target untuk masing-masing kabupaten');
        $sheet2->setCellValue('A4', '2. Isi kolom "Keterangan" dengan informasi tambahan jika diperlukan');
        $sheet2->setCellValue('A5', '3. Pastikan total target tidak melebihi sisa target yang tersedia');
        $sheet2->setCellValue('A6', '4. Kolom "ID Kabupaten" dan "Nama Kabupaten" JANGAN DIUBAH');
        $sheet2->setCellValue('A7', '5. Baris yang kosong (target = 0 atau kosong) tidak akan diimport');
        $sheet2->setCellValue('A8', '6. Simpan file dan upload kembali ke sistem');

        $sheet2->setCellValue('A10', 'INFORMASI KEGIATAN:');
        $sheet2->getStyle('A10')->getFont()->setBold(true);

        $sheet2->setCellValue('A11', 'Kegiatan Detail:');
        $sheet2->setCellValue('B11', $kegiatanDetail['nama_kegiatan_detail'] ?? '-');
        $sheet2->mergeCells('B11:D11');

        $sheet2->setCellValue('A12', 'Kegiatan Detail Proses:');
        $sheet2->setCellValue('B12', $detailProses['nama_kegiatan_detail_proses']);
        $sheet2->mergeCells('B12:D12');

        $sheet2->setCellValue('A13', 'Target Provinsi:');
        $sheet2->setCellValue('B13', number_format($targetProv));

        $sheet2->setCellValue('A14', 'Sisa Target Tersedia:');
        $sheet2->setCellValue('B14', number_format($sisaTarget));
        $sheet2->getStyle('B14')->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('008000'));

        foreach (['A' => 80, 'B' => 40, 'C' => 20, 'D' => 20] as $col => $width) {
            $sheet2->getColumnDimension($col)->setWidth($width);
        }

        // Generate filename
        $namaKegiatan = preg_replace('/[^A-Za-z0-9_\-]/', '_', $detailProses['nama_kegiatan_detail_proses']);
        $filename = 'Template_Kegiatan_Wilayah_' . $namaKegiatan . '_' . date('YmdHis') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function import()
    {
        // Get role dan admin ID dari session
        $role = session()->get('role');
        $roleType = session()->get('role_type');
        $idAdminProvinsi = session()->get('id_admin_provinsi');

        $isSuperAdmin = ($role == 1);
        $isAdminProvinsi = ($role == 2 && $roleType == 'admin_provinsi' && $idAdminProvinsi);

        if (!$isSuperAdmin && !$isAdminProvinsi) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Anda tidak memiliki akses.'
            ]);
        }

        $file = $this->request->getFile('file');
        $idKegiatanDetailProses = $this->request->getPost('id_kegiatan_detail_proses');

        if (!$file || !$file->isValid()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'File tidak valid'
            ]);
        }

        if (!$idKegiatanDetailProses) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID Kegiatan Detail Proses tidak ditemukan'
            ]);
        }

        $extension = $file->getClientExtension();
        if (!in_array($extension, ['xlsx', 'xls'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Format file harus Excel (.xlsx atau .xls)'
            ]);
        }

        // Validasi kegiatan detail proses
        $detailProses = $this->masterDetailProsesModel->find($idKegiatanDetailProses);
        if (!$detailProses) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Data kegiatan detail proses tidak ditemukan'
            ]);
        }

        // Validasi akses untuk Admin Provinsi
        if ($isAdminProvinsi) {
            $hasAccess = $this->masterDetailAdminModel
                ->where('id_kegiatan_detail', $detailProses['id_kegiatan_detail'])
                ->where('id_admin_provinsi', $idAdminProvinsi)
                ->first();

            if (!$hasAccess) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke kegiatan ini'
                ]);
            }
        }

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getTempName());
            $sheet = $spreadsheet->getActiveSheet();
            $data = $sheet->toArray();

            // Get target info
            $targetProv = (int) $detailProses['target'];
            $tanggalMulai = $detailProses['tanggal_mulai'];
            $tanggalSelesai = $detailProses['tanggal_selesai'];
            $tanggal100 = $detailProses['tanggal_selesai_target'] ?? $tanggalSelesai;
            $persenAwal = $detailProses['persentase_target_awal'] ?? 0;

            // Hitung terpakai saat ini
            $terpakaiSebelum = (int) $this->masterKegiatanWilayahModel
                ->where('id_kegiatan_detail_proses', $idKegiatanDetailProses)
                ->selectSum('target_wilayah')
                ->get()
                ->getRow()
                ->target_wilayah ?? 0;

            $imported = 0;
            $skipped = 0;
            $errors = [];

            // Get valid kabupaten
            $validKabupaten = [];
            $allKabupaten = $this->masterKab->findAll();
            foreach ($allKabupaten as $kab) {
                $validKabupaten[$kab['id_kabupaten']] = $kab['nama_kabupaten'];
            }

            $this->db->transStart();

            // Start from row 8 (index 7), karena row 1-7 adalah header dan info
            for ($i = 7; $i < count($data); $i++) {
                $row = $data[$i];
                $rowNumber = $i + 1;

                // Skip empty rows atau rows yang bukan data
                if (empty($row[0]) || empty($row[1]) || trim($row[0]) === '' || trim($row[1]) === '') {
                    continue;
                }

                // Skip jika target kosong atau 0
                if (empty($row[2]) || trim($row[2]) === '' || (int) $row[2] === 0) {
                    continue;
                }

                $idKabupaten = trim($row[0]);
                $targetWilayah = (int) preg_replace('/[^0-9]/', '', trim($row[2]));
                $keterangan = !empty($row[3]) ? trim($row[3]) : 'Import dari Excel';

                // Validasi kabupaten
                if (!isset($validKabupaten[$idKabupaten])) {
                    $errors[] = "Baris {$rowNumber}: ID Kabupaten '{$idKabupaten}' tidak valid";
                    $skipped++;
                    continue;
                }

                // Validasi target
                if ($targetWilayah <= 0) {
                    $errors[] = "Baris {$rowNumber}: Target harus lebih dari 0";
                    $skipped++;
                    continue;
                }

                // Check duplicate
                $existing = $this->masterKegiatanWilayahModel
                    ->where('id_kegiatan_detail_proses', $idKegiatanDetailProses)
                    ->where('id_kabupaten', $idKabupaten)
                    ->first();

                if ($existing) {
                    $errors[] = "Baris {$rowNumber}: {$validKabupaten[$idKabupaten]} sudah ditambahkan sebelumnya";
                    $skipped++;
                    continue;
                }

                // Validasi total tidak melebihi target provinsi
                $totalBaru = $terpakaiSebelum + $targetWilayah;
                if ($totalBaru > $targetProv) {
                    $sisa = $targetProv - $terpakaiSebelum;
                    $errors[] = "Baris {$rowNumber}: Target melebihi sisa tersedia (Sisa: " . number_format($sisa) . ")";
                    $skipped++;
                    continue;
                }

                // Insert data
                $this->masterKegiatanWilayahModel->insert([
                    'id_kegiatan_detail_proses' => $idKegiatanDetailProses,
                    'id_kabupaten' => $idKabupaten,
                    'target_wilayah' => $targetWilayah,
                    'keterangan' => $keterangan,
                    'status' => 'Aktif'
                ]);

                $idWilayah = $this->masterKegiatanWilayahModel->getInsertID();

                // Generate kurva S
                $this->generateKurvaSKab($idWilayah, $targetWilayah, $persenAwal, $tanggalMulai, $tanggal100, $tanggalSelesai);

                $terpakaiSebelum += $targetWilayah;
                $imported++;
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat menyimpan data'
                ]);
            }

            $message = "Import selesai!<br>Berhasil: <strong>{$imported}</strong> data<br>Dilewati: <strong>{$skipped}</strong> data";

            if ($imported === 0) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Tidak ada data yang berhasil diimport.<br>' . implode('<br>', array_slice($errors, 0, 5))
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => $message,
                'errors' => !empty($errors) ? implode("\n", array_slice($errors, 0, 10)) : null
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }
}
