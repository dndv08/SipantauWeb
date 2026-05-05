<?php

namespace App\Controllers\AdminProv;

use App\Controllers\BaseController;
use App\Models\MasterKegiatanDetailProsesModel;
use App\Models\MasterKegiatanDetailModel;
use App\Models\MasterKegiatanDetailAdminModel;
use App\Models\KurvaSProvinsiModel;
use DateInterval;
use DatePeriod;
use DateTime;

class MasterKegiatanDetailProsesController extends BaseController
{
    protected $masterDetailProsesModel;
    protected $masterDetailModel;
    protected $masterDetailAdminModel;
    protected $validation;
    protected $db;

    public function __construct()
    {
        $this->masterDetailProsesModel = new MasterKegiatanDetailProsesModel();
        $this->masterDetailModel = new MasterKegiatanDetailModel();
        $this->masterDetailAdminModel = new MasterKegiatanDetailAdminModel();
        $this->validation = \Config\Services::validation();
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        // Get role dan admin ID dari session
        $role = session()->get('role');
        $roleType = session()->get('role_type');
        $idAdminProvinsi = session()->get('id_admin_provinsi');

        // Tentukan apakah user adalah Super Admin atau Admin Provinsi
        $isSuperAdmin = ($role == 1);
        $isAdminProvinsi = ($role == 2 && $roleType == 'admin_provinsi' && $idAdminProvinsi);

        // Jika bukan Super Admin dan bukan Admin Provinsi, redirect
        if (!$isSuperAdmin && !$isAdminProvinsi) {
            return redirect()->to(base_url('unauthorized'))
                ->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }

        // Ambil perPage dari GET, default 10
        $perPage = $this->request->getGet('perPage') ?? 10;

        // Validasi perPage agar hanya nilai yang diizinkan
        $allowedPerPage = [5, 10, 25, 50, 100];
        if (!in_array((int) $perPage, $allowedPerPage)) {
            $perPage = 10;
        }

        // Ambil filter dari GET atau session
        $kegiatanDetailFilter = $this->request->getGet('kegiatan_detail') ?? session()->get('kegiatan_detail_filter');

        // Simpan filter ke session jika ada
        if ($this->request->getGet('kegiatan_detail') !== null) {
            session()->set('kegiatan_detail_filter', $kegiatanDetailFilter);
        }

        // Get data dengan pagination
        $filterAdminId = $isSuperAdmin ? null : $idAdminProvinsi;
        $kegiatanDetails = $this->masterDetailProsesModel->getDataPaginated($kegiatanDetailFilter, $filterAdminId, $perPage);

        // Get list kegiatan detail
        $kegiatanDetailList = $isSuperAdmin
            ? $this->masterDetailModel->findAll()
            : $this->getAssignedKegiatanDetail($idAdminProvinsi);

        $data = [
            'title' => 'Kelola Master Kegiatan Detail Proses',
            'active_menu' => 'master-kegiatan-detail-proses',
            'kegiatanDetails' => $kegiatanDetails,
            'kegiatanDetailList' => $kegiatanDetailList,
            'kegiatanDetailFilter' => $kegiatanDetailFilter,
            'isSuperAdmin' => $isSuperAdmin,
            'perPage' => $perPage,
            'pager' => $this->masterDetailProsesModel->pager,
        ];

        return view('AdminSurveiProv/MasterKegiatanDetailProses/index', $data);
    }

    public function create()
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

        // Ambil filter dari session
        $kegiatanDetailFilter = session()->get('kegiatan_detail_filter');

        // Get list kegiatan detail
        $kegiatanDetailList = $isSuperAdmin
            ? $this->masterDetailModel->findAll()
            : $this->getAssignedKegiatanDetail($idAdminProvinsi);

        $data = [
            'title' => 'Tambah Master Kegiatan Detail Proses',
            'active_menu' => 'master-kegiatan-detail-proses',
            'kegiatanDetailList' => $kegiatanDetailList,
            'validation' => $this->validation,
            'kegiatanDetailFilter' => $kegiatanDetailFilter,
            'isSuperAdmin' => $isSuperAdmin
        ];

        return view('AdminSurveiProv/MasterKegiatanDetailProses/create', $data);
    }

    public function store()
    {
        $role = session()->get('role');
        $roleType = session()->get('role_type');
        $idAdminProvinsi = session()->get('id_admin_provinsi');

        $isSuperAdmin = ($role == 1);
        $isAdminProvinsi = ($role == 2 && $roleType == 'admin_provinsi' && $idAdminProvinsi);

        if (!$isSuperAdmin && !$isAdminProvinsi) {
            return redirect()->to(base_url('unauthorized'))
                ->with('error', 'Anda tidak memiliki akses.');
        }

        $rules = [
            'kegiatan_detail' => 'required|numeric',
            'nama_proses' => 'required|min_length[3]|max_length[255]',
            'tanggal_mulai' => 'required|valid_date',
            'tanggal_selesai' => 'required|valid_date',
            'satuan' => 'required|max_length[50]',
            'keterangan' => 'required|max_length[255]',
            'periode' => 'required|max_length[50]',
            'target' => 'required|numeric',
            'persentase_target_awal' => 'required|numeric',
            'tanggal_selesai_target' => 'required|valid_date',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $idKegiatanDetail = $this->request->getPost('kegiatan_detail');

        // Validasi untuk Admin Provinsi: Pastikan kegiatan detail ini di-assign ke admin ini
        // Super Admin tidak perlu validasi ini
        if ($isAdminProvinsi && !$this->isKegiatanAssignedToAdmin($idKegiatanDetail, $idAdminProvinsi)) {
            return redirect()->back()->withInput()
                ->with('error', 'Anda tidak memiliki akses ke kegiatan ini.');
        }

        $tanggalMulai = $this->request->getPost('tanggal_mulai');
        $tanggalSelesai = $this->request->getPost('tanggal_selesai');
        $tanggal100Persen = $this->request->getPost('tanggal_selesai_target');

        // Validasi hubungan antar tanggal
        if (strtotime($tanggalSelesai) < strtotime($tanggalMulai)) {
            return redirect()->back()->withInput()->with('error', 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.');
        }

        if (strtotime($tanggal100Persen) < strtotime($tanggalMulai)) {
            return redirect()->back()->withInput()->with('error', 'Target tanggal selesai (100%) tidak boleh lebih awal dari tanggal mulai.');
        }

        if (strtotime($tanggal100Persen) > strtotime($tanggalSelesai)) {
            return redirect()->back()->withInput()->with('error', 'Target tanggal selesai (100%) tidak boleh melebihi tanggal selesai kegiatan.');
        }

        // Simpan ke tabel master_kegiatan_detail_proses
        $this->masterDetailProsesModel->insert([
            'id_kegiatan_detail' => $idKegiatanDetail,
            'nama_kegiatan_detail_proses' => $this->request->getPost('nama_proses'),
            'satuan' => $this->request->getPost('satuan'),
            'tanggal_mulai' => $tanggalMulai,
            'tanggal_selesai' => $tanggalSelesai,
            'keterangan' => $this->request->getPost('keterangan'),
            'periode' => $this->request->getPost('periode'),
            'target' => $this->request->getPost('target'),
            'persentase_target_awal' => $this->request->getPost('persentase_target_awal'),
            'tanggal_selesai_target' => $tanggal100Persen,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $idProses = $this->masterDetailProsesModel->getInsertID();

        // Generate Kurva S
        $this->generateKurvaS(
            $idProses,
            $this->request->getPost('target'),
            $this->request->getPost('persentase_target_awal'),
            $tanggalMulai,
            $tanggal100Persen,
            $tanggalSelesai
        );

        return redirect()
            ->to(base_url('adminsurvei/master-kegiatan-detail-proses'))
            ->with('success', 'Data kegiatan detail proses dan Kurva S Provinsi berhasil dibuat.');
    }

    public function edit($id)
    {
        $role = session()->get('role');
        $roleType = session()->get('role_type');
        $idAdminProvinsi = session()->get('id_admin_provinsi');

        $isSuperAdmin = ($role == 1);
        $isAdminProvinsi = ($role == 2 && $roleType == 'admin_provinsi' && $idAdminProvinsi);

        if (!$isSuperAdmin && !$isAdminProvinsi) {
            return redirect()->to(base_url('unauthorized'))
                ->with('error', 'Anda tidak memiliki akses.');
        }

        $detailProses = $this->masterDetailProsesModel->find($id);

        if (!$detailProses) {
            return redirect()
                ->to(base_url('adminsurvei/master-kegiatan-detail-proses'))
                ->with('error', 'Data tidak ditemukan.');
        }

        // Validasi untuk Admin Provinsi: Pastikan kegiatan detail ini di-assign ke admin ini
        if ($isAdminProvinsi && !$this->isKegiatanAssignedToAdmin($detailProses['id_kegiatan_detail'], $idAdminProvinsi)) {
            return redirect()
                ->to(base_url('adminsurvei/master-kegiatan-detail-proses'))
                ->with('error', 'Anda tidak memiliki akses ke kegiatan ini.');
        }

        // Ambil filter dari session
        $kegiatanDetailFilter = session()->get('kegiatan_detail_filter');

        // Get list kegiatan detail
        $kegiatanDetailList = $isSuperAdmin
            ? $this->masterDetailModel->findAll()
            : $this->getAssignedKegiatanDetail($idAdminProvinsi);

        $data = [
            'title' => 'Edit Master Kegiatan Detail Proses',
            'active_menu' => 'master-kegiatan-detail-proses',
            'kegiatanDetailList' => $kegiatanDetailList,
            'detailProses' => $detailProses,
            'validation' => $this->validation,
            'kegiatanDetailFilter' => $kegiatanDetailFilter,
            'isSuperAdmin' => $isSuperAdmin
        ];

        return view('AdminSurveiProv/MasterKegiatanDetailProses/edit', $data);
    }

    public function update($id)
    {
        $role = session()->get('role');
        $roleType = session()->get('role_type');
        $idAdminProvinsi = session()->get('id_admin_provinsi');

        $isSuperAdmin = ($role == 1);
        $isAdminProvinsi = ($role == 2 && $roleType == 'admin_provinsi' && $idAdminProvinsi);

        if (!$isSuperAdmin && !$isAdminProvinsi) {
            return redirect()->to(base_url('unauthorized'))
                ->with('error', 'Anda tidak memiliki akses.');
        }

        $rules = [
            'kegiatan_detail' => 'required|numeric',
            'nama_proses' => 'required|min_length[3]|max_length[255]',
            'tanggal_mulai' => 'required|valid_date',
            'tanggal_selesai' => 'required|valid_date',
            'satuan' => 'required|max_length[50]',
            'keterangan' => 'required|max_length[255]',
            'periode' => 'required|max_length[50]',
            'target' => 'required|numeric',
            'persentase_target_awal' => 'required|numeric',
            'tanggal_selesai_target' => 'required|valid_date',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $existingData = $this->masterDetailProsesModel->find($id);
        if (!$existingData) {
            return redirect()->back()->with('error', 'Data tidak ditemukan.');
        }

        // Validasi untuk Admin Provinsi: Pastikan kegiatan detail ini di-assign ke admin ini
        if ($isAdminProvinsi && !$this->isKegiatanAssignedToAdmin($existingData['id_kegiatan_detail'], $idAdminProvinsi)) {
            return redirect()->back()
                ->with('error', 'Anda tidak memiliki akses ke kegiatan ini.');
        }

        $tanggalMulai = $this->request->getPost('tanggal_mulai');
        $tanggalSelesai = $this->request->getPost('tanggal_selesai');
        $tanggal100Persen = $this->request->getPost('tanggal_selesai_target');

        // Validasi hubungan antar tanggal
        if (
            strtotime($tanggalSelesai) < strtotime($tanggalMulai) ||
            strtotime($tanggal100Persen) < strtotime($tanggalMulai) ||
            strtotime($tanggal100Persen) > strtotime($tanggalSelesai)
        ) {
            return redirect()->back()->withInput()->with('error', 'Tanggal tidak valid.');
        }

        $input = [
            'id_kegiatan_detail' => $this->request->getPost('kegiatan_detail'),
            'nama_kegiatan_detail_proses' => $this->request->getPost('nama_proses'),
            'tanggal_mulai' => $tanggalMulai,
            'tanggal_selesai' => $tanggalSelesai,
            'satuan' => $this->request->getPost('satuan'),
            'keterangan' => $this->request->getPost('keterangan'),
            'periode' => $this->request->getPost('periode'),
            'target' => $this->request->getPost('target'),
            'persentase_target_awal' => $this->request->getPost('persentase_target_awal'),
            'tanggal_selesai_target' => $tanggal100Persen,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $this->masterDetailProsesModel->update($id, $input);

        // Cek apakah Kurva S perlu diupdate
        $isKurvaNeedsUpdate = (
            $existingData['target'] != $input['target'] ||
            $existingData['persentase_target_awal'] != $input['persentase_target_awal'] ||
            $existingData['tanggal_mulai'] != $input['tanggal_mulai'] ||
            $existingData['tanggal_selesai'] != $input['tanggal_selesai'] ||
            $existingData['tanggal_selesai_target'] != $input['tanggal_selesai_target']
        );

        if ($isKurvaNeedsUpdate) {
            // 1. Regenerate Kurva Provinsi
            $kurvaProvinsiModel = new KurvaSProvinsiModel();
            $kurvaProvinsiModel->where('id_kegiatan_detail_proses', $id)->delete();

            $this->generateKurvaS(
                $id,
                $input['target'],
                $input['persentase_target_awal'],
                $tanggalMulai,
                $tanggal100Persen,
                $tanggalSelesai
            );

            // 2. Regenerate Kurva Kabupaten yang terkait
            $kurvaKabModel = new \App\Models\KurvaSkabModel();
            $kegiatanWilayahModel = new \App\Models\MasterKegiatanWilayahModel();

            $kegiatanWilayahList = $kegiatanWilayahModel
                ->where('id_kegiatan_detail_proses', $id)
                ->findAll();

            foreach ($kegiatanWilayahList as $kw) {
                // Hapus kurva lama
                $kurvaKabModel->where('id_kegiatan_wilayah', $kw['id_kegiatan_wilayah'])->delete();

                // Generate kurva baru
                $this->generateKurvaSKab(
                    $kw['id_kegiatan_wilayah'],
                    $kw['target_wilayah'],
                    $input['persentase_target_awal'],
                    $tanggalMulai,
                    $tanggal100Persen,
                    $tanggalSelesai
                );
            }

            // 3. Regenerate Kurva Petugas (PCL) yang terkait
            $kurvaPetugasModel = new \App\Models\KurvaPetugasModel();
            $pmlModel = new \App\Models\PMLModel();
            $pclModel = new \App\Models\PCLModel();

            foreach ($kegiatanWilayahList as $kw) {
                // Get semua PML di kegiatan wilayah ini
                $pmlList = $pmlModel->where('id_kegiatan_wilayah', $kw['id_kegiatan_wilayah'])->findAll();

                foreach ($pmlList as $pml) {
                    // Get semua PCL di bawah PML ini
                    $pclList = $pclModel->where('id_pml', $pml['id_pml'])->findAll();

                    foreach ($pclList as $pcl) {
                        // Hapus kurva lama
                        $kurvaPetugasModel->where('id_pcl', $pcl['id_pcl'])->delete();

                        // Generate kurva baru
                        $this->generateKurvaPetugas(
                            $pcl['id_pcl'],
                            $pcl['target'],
                            $input['persentase_target_awal'],
                            $tanggalMulai,
                            $tanggal100Persen,
                            $tanggalSelesai
                        );
                    }
                }
            }
        }

        return redirect()
            ->to(base_url('adminsurvei/master-kegiatan-detail-proses'))
            ->with('success', $isKurvaNeedsUpdate
                ? 'Data kegiatan dan Kurva S Provinsi berhasil diperbarui.'
                : 'Data kegiatan berhasil diperbarui tanpa perubahan Kurva S.');
    }

    public function delete($id)
    {
        $role = session()->get('role');
        $roleType = session()->get('role_type');
        $idAdminProvinsi = session()->get('id_admin_provinsi');

        $isSuperAdmin = ($role == 1);
        $isAdminProvinsi = ($role == 2 && $roleType == 'admin_provinsi' && $idAdminProvinsi);

        if (!$isSuperAdmin && !$isAdminProvinsi) {
            return redirect()->to(base_url('unauthorized'))
                ->with('error', 'Anda tidak memiliki akses.');
        }

        $prosesModel = new MasterKegiatanDetailProsesModel();
        $kurvaModel = new KurvaSProvinsiModel();

        $detailProses = $prosesModel->find($id);

        if (!$detailProses) {
            return redirect()->back()->with('error', 'Data kegiatan detail proses tidak ditemukan.');
        }

        // Validasi untuk Admin Provinsi: Pastikan kegiatan detail ini di-assign ke admin ini
        if ($isAdminProvinsi && !$this->isKegiatanAssignedToAdmin($detailProses['id_kegiatan_detail'], $idAdminProvinsi)) {
            return redirect()->back()
                ->with('error', 'Anda tidak memiliki akses ke kegiatan ini.');
        }

        $kurvaModel->where('id_kegiatan_detail_proses', $id)->delete();
        $prosesModel->delete($id);

        return redirect()
            ->to(base_url('adminsurvei/master-kegiatan-detail-proses'))
            ->with('success', 'Data kegiatan detail proses dan kurva S provinsi terkait berhasil dihapus.');
    }

    // Helper methods

    /**
     * Get kegiatan detail yang di-assign ke admin provinsi
     */
    private function getAssignedKegiatanDetail($idAdminProvinsi)
    {
        return $this->db->table('master_kegiatan_detail_admin mkda')
            ->select('mkd.id_kegiatan_detail, mkd.nama_kegiatan_detail, mkd.id_kegiatan, mk.nama_kegiatan')
            ->join('master_kegiatan_detail mkd', 'mkda.id_kegiatan_detail = mkd.id_kegiatan_detail')
            ->join('master_kegiatan mk', 'mkd.id_kegiatan = mk.id_kegiatan')
            ->where('mkda.id_admin_provinsi', $idAdminProvinsi)
            ->orderBy('mk.nama_kegiatan', 'ASC')
            ->orderBy('mkd.nama_kegiatan_detail', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Check apakah kegiatan di-assign ke admin
     */
    private function isKegiatanAssignedToAdmin($idKegiatanDetail, $idAdminProvinsi)
    {
        return $this->masterDetailAdminModel
            ->where('id_kegiatan_detail', $idKegiatanDetail)
            ->where('id_admin_provinsi', $idAdminProvinsi)
            ->first() !== null;
    }

    // Method generateKurvaS() tetap di sini
    private function generateKurvaS($idProses, $target, $persenAwal, $tanggalMulai, $tanggal100, $tanggalSelesai)
{
    $kurvaModel = new KurvaSProvinsiModel();

    $totalTarget = (int) $target;
    $persenAwal = (float) $persenAwal;

    $start = new DateTime($tanggalMulai);
    $tgl100 = new DateTime($tanggal100);
    $end = new DateTime($tanggalSelesai);
    $end->modify('+1 day');

    $interval = new DateInterval('P1D');
    $allDays = iterator_to_array(new DatePeriod($start, $interval, $end));

    $daysUntil100 = array_filter($allDays, fn($d) => $d <= $tgl100);
    $daysUntil100 = array_values($daysUntil100);

    $daysSigmoid = max(count($daysUntil100), 2);
    $k = 8;
    $x0 = 0.5;

    $sigmoidMin = 1 / (1 + exp(-$k * (0 - $x0)));
    $sigmoidMax = 1 / (1 + exp(-$k * (1 - $x0)));

    $dayData = [];
    foreach ($daysUntil100 as $i => $date) {
        $progress = $i / ($daysSigmoid - 1);
        $sigmoid = 1 / (1 + exp(-$k * ($progress - $x0)));
        $normalized = ($sigmoid - $sigmoidMin) / ($sigmoidMax - $sigmoidMin);

        $kumulatifPersen = $persenAwal + (100 - $persenAwal) * $normalized;
        $dayData[$date->format('Y-m-d')] = min($kumulatifPersen, 100);
    }

    $kumulatifAbsolut = 0;

    foreach ($allDays as $date) {
        $currentDate = $date->format('Y-m-d');
        $isHariKerja = $date->format('N') <= 5 ? 1 : 0;

        if (isset($dayData[$currentDate])) {
            $kumulatifPersen = $dayData[$currentDate];
            $harian = round(($totalTarget * ($kumulatifPersen / 100)) - $kumulatifAbsolut);
            $kumulatifAbsolut += $harian;
        } else {
            $kumulatifPersen = 100;
            $harian = 0;
        }

        $kurvaModel->insert([
            'id_kegiatan_detail_proses' => $idProses,
            'tanggal_target' => $currentDate,
            'target_persen_kumulatif' => round($kumulatifPersen, 2),
            'target_harian_absolut' => $harian,
            'target_kumulatif_absolut' => $kumulatifAbsolut,
            'is_hari_kerja' => $isHariKerja,
        ]);
    }

    $selisih = $totalTarget - $kumulatifAbsolut;
    if ($selisih !== 0) {
        $kurvaModel
            ->where('id_kegiatan_detail_proses', $idProses)
            ->orderBy('tanggal_target', 'DESC')
            ->limit(1)
            ->set('target_harian_absolut', "target_harian_absolut + $selisih", false)
            ->set('target_kumulatif_absolut', $totalTarget)
            ->update();
    }
}


    private function generateKurvaSKab($idWilayah, $target, $persenAwal, $tanggalMulai, $tanggal100, $tanggalSelesai)
{
    $kurvaModel = new \App\Models\KurvaSkabModel();

    $totalTarget = (int) $target;
    $persenAwal = (float) $persenAwal;

    $start = new DateTime($tanggalMulai);
    $tgl100 = new DateTime($tanggal100);
    $end = new DateTime($tanggalSelesai);
    $end->modify('+1 day');

    $interval = new DateInterval('P1D');
    $allDays = iterator_to_array(new DatePeriod($start, $interval, $end));

    $daysUntil100 = array_filter($allDays, fn($d) => $d <= $tgl100);
    $daysUntil100 = array_values($daysUntil100);

    $daysSigmoid = max(count($daysUntil100), 2);
    $k = 8;
    $x0 = 0.5;

    $sigmoidMin = 1 / (1 + exp(-$k * (0 - $x0)));
    $sigmoidMax = 1 / (1 + exp(-$k * (1 - $x0)));

    $dayData = [];
    foreach ($daysUntil100 as $i => $date) {
        $progress = $i / ($daysSigmoid - 1);
        $sigmoid = 1 / (1 + exp(-$k * ($progress - $x0)));
        $normalized = ($sigmoid - $sigmoidMin) / ($sigmoidMax - $sigmoidMin);

        $kumulatifPersen = $persenAwal + (100 - $persenAwal) * $normalized;
        $dayData[$date->format('Y-m-d')] = min($kumulatifPersen, 100);
    }

    $kumulatifAbsolut = 0;
    $insertData = [];

    foreach ($allDays as $date) {
        $current = $date->format('Y-m-d');
        $isHariKerja = $date->format('N') <= 5 ? 1 : 0;

        if (isset($dayData[$current])) {
            $kumulatifPersen = $dayData[$current];
            $harian = round(($totalTarget * ($kumulatifPersen / 100)) - $kumulatifAbsolut);
            $kumulatifAbsolut += $harian;
        } else {
            $kumulatifPersen = 100;
            $harian = 0;
        }

        $insertData[] = [
            'id_kegiatan_wilayah' => $idWilayah,
            'tanggal_target' => $current,
            'target_persen_kumulatif' => round($kumulatifPersen, 2),
            'target_harian_absolut' => $harian,
            'target_kumulatif_absolut' => $kumulatifAbsolut,
            'is_hari_kerja' => $isHariKerja,
        ];
    }

    $selisih = $totalTarget - $kumulatifAbsolut;
    if ($selisih !== 0) {
        $insertData[count($insertData) - 1]['target_harian_absolut'] += $selisih;
        $insertData[count($insertData) - 1]['target_kumulatif_absolut'] = $totalTarget;
    }

    $kurvaModel->insertBatch($insertData);
}


    private function generateKurvaPetugas($idPCL, $target, $persenAwal, $tanggalMulai, $tanggal100, $tanggalSelesai)
{
    $kurvaModel = new \App\Models\KurvaPetugasModel();

    $totalTarget = (int) $target;
    $persenAwal = (float) $persenAwal;

    $start = new DateTime($tanggalMulai);
    $tgl100 = new DateTime($tanggal100);
    $end = new DateTime($tanggalSelesai);
    $end->modify('+1 day');

    $interval = new DateInterval('P1D');
    $allDays = iterator_to_array(new DatePeriod($start, $interval, $end));

    $daysUntil100 = array_filter($allDays, fn($d) => $d <= $tgl100);
    $daysUntil100 = array_values($daysUntil100);

    $daysSigmoid = max(count($daysUntil100), 2);
    $k = 8;
    $x0 = 0.5;

    $sigmoidMin = 1 / (1 + exp(-$k * (0 - $x0)));
    $sigmoidMax = 1 / (1 + exp(-$k * (1 - $x0)));

    $dayData = [];
    foreach ($daysUntil100 as $i => $date) {
        $progress = $i / ($daysSigmoid - 1);
        $sigmoid = 1 / (1 + exp(-$k * ($progress - $x0)));
        $normalized = ($sigmoid - $sigmoidMin) / ($sigmoidMax - $sigmoidMin);

        $kumulatifPersen = $persenAwal + (100 - $persenAwal) * $normalized;
        $dayData[$date->format('Y-m-d')] = min($kumulatifPersen, 100);
    }

    $kumulatifAbsolut = 0;

    foreach ($allDays as $date) {
        $current = $date->format('Y-m-d');
        $isHariKerja = $date->format('N') <= 5 ? 1 : 0;

        if (isset($dayData[$current])) {
            $kumulatifPersen = $dayData[$current];
            $harian = round(($totalTarget * ($kumulatifPersen / 100)) - $kumulatifAbsolut);
            $kumulatifAbsolut += $harian;
        } else {
            $kumulatifPersen = 100;
            $harian = 0;
        }

        $kurvaModel->insert([
            'id_pcl' => $idPCL,
            'tanggal_target' => $current,
            'target_persen_kumulatif' => round($kumulatifPersen, 2),
            'target_harian_absolut' => $harian,
            'target_kumulatif_absolut' => $kumulatifAbsolut,
            'is_hari_kerja' => $isHariKerja,
        ]);
    }
}

    // Method untuk clear filter
    public function clearFilter()
    {
        session()->remove('kegiatan_detail_filter');
        return redirect()->to(base_url('adminsurvei/master-kegiatan-detail-proses'));
    }
}