<?php

namespace App\Controllers\AdminKab;

use App\Controllers\BaseController;
use App\Models\AssignmentSubSLSModel;
use App\Models\MasterKegiatanWilayahModel;
use App\Models\AdminSurveiKabupatenModel;

class ViewAssignSubSLSController extends BaseController
{
    protected $assignmentModel;
    protected $kegiatanWilayahModel;
    protected $adminKabModel;

    public function __construct()
    {
        $this->assignmentModel = new AssignmentSubSLSModel();
        $this->kegiatanWilayahModel = new MasterKegiatanWilayahModel();
        $this->adminKabModel = new AdminSurveiKabupatenModel();
    }

    public function index()
    {
        $sobatId = session()->get('sobat_id');
        $admin = $this->adminKabModel->db->table('admin_survei_kabupaten ask')
            ->select('ask.*, u.nama_user, u.id_kabupaten, k.nama_kabupaten')
            ->join('sipantau_user u', 'ask.sobat_id = u.sobat_id')
            ->join('master_kabupaten k', 'u.id_kabupaten = k.id_kabupaten')
            ->where('ask.sobat_id', $sobatId)
            ->get()
            ->getRowArray();

        if (!$admin) {
            return redirect()->to('/')->with('error', 'Anda tidak memiliki akses sebagai admin kabupaten');
        }

        $idKabupaten = $admin['id_kabupaten'];
        $idAdminKabupaten = $admin['id_admin_kabupaten'];
        $idKegiatanWilayah = $this->request->getGet('kegiatan');
        $statusFilter = $this->request->getGet('status');

        $kegiatanList = $this->kegiatanWilayahModel->getByKabupatenAndAdmin($idKabupaten, $idAdminKabupaten);
        
        $db = \Config\Database::connect();
        
        if ($idKegiatanWilayah) {
            // Logic for single activity
            $builder = $db->table('master_sub_sls mss')
                ->select('mss.id_sub_sls, mss.nama_sls, mss.nama_desa as mss_nama_desa, 
                          ass.id_assignment_sub_sls, ass.sobat_id, u.nama_user as nama_petugas,
                          md.nama_desa as real_nama_desa, mk.nama_kecamatan,
                          mkd.nama_kegiatan_detail, mkdp.nama_kegiatan_detail_proses')
                ->join('master_sls ms', 'mss.id_sls = ms.id_sls', 'left')
                ->join('master_desa md', 'ms.id_desa = md.id_desa', 'left')
                ->join('master_kecamatan mk', 'md.id_kecamatan = mk.id_kecamatan', 'left')
                ->join('assignment_sub_sls ass', "mss.id_sub_sls = ass.id_sub_sls AND ass.id_kegiatan_wilayah = " . ($db->escape($idKegiatanWilayah)), 'left')
                ->join('sipantau_user u', 'ass.sobat_id = u.sobat_id', 'left')
                ->join('kegiatan_wilayah kw', $db->escape($idKegiatanWilayah) . " = kw.id_kegiatan_wilayah", 'left')
                ->join('master_kegiatan_detail_proses mkdp', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses', 'left')
                ->join('master_kegiatan_detail mkd', 'mkdp.id_kegiatan_detail = mkd.id_kegiatan_detail', 'left')
                ->where('mss.id_sub_sls LIKE', $idKabupaten . '%');
        } else {
            // Logic for ALL activities
            $builder = $db->table('master_sub_sls mss')
                ->select('mss.id_sub_sls, mss.nama_sls, mss.nama_desa as mss_nama_desa, 
                          ass.id_assignment_sub_sls, ass.sobat_id, u.nama_user as nama_petugas,
                          md.nama_desa as real_nama_desa, mk.nama_kecamatan,
                          mkd.nama_kegiatan_detail, mkdp.nama_kegiatan_detail_proses')
                ->join('master_sls ms', 'mss.id_sls = ms.id_sls', 'left')
                ->join('master_desa md', 'ms.id_desa = md.id_desa', 'left')
                ->join('master_kecamatan mk', 'md.id_kecamatan = mk.id_kecamatan', 'left')
                ->join('kegiatan_wilayah kw', 'kw.id_kabupaten = ' . $db->escape($idKabupaten))
                ->join('kegiatan_wilayah_admin kwa', 'kwa.id_kegiatan_wilayah = kw.id_kegiatan_wilayah AND kwa.id_admin_kabupaten = ' . $idAdminKabupaten)
                ->join('master_kegiatan_detail_proses mkdp', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses')
                ->join('master_kegiatan_detail mkd', 'mkdp.id_kegiatan_detail = mkd.id_kegiatan_detail')
                ->join('assignment_sub_sls ass', 'mss.id_sub_sls = ass.id_sub_sls AND ass.id_kegiatan_wilayah = kw.id_kegiatan_wilayah', 'left')
                ->join('sipantau_user u', 'ass.sobat_id = u.sobat_id', 'left')
                ->where('mss.id_sub_sls LIKE', $idKabupaten . '%');
        }

        if ($statusFilter === 'assigned') {
            $builder->where('ass.id_assignment_sub_sls IS NOT NULL');
        } elseif ($statusFilter === 'unassigned') {
            $builder->where('ass.id_assignment_sub_sls IS NULL');
        }

        $assignments = $builder->orderBy('mk.nama_kecamatan', 'ASC')
            ->orderBy('md.nama_desa', 'ASC')
            ->orderBy('mss.nama_sls', 'ASC')
            ->get()->getResultArray();

        $data = [
            'title' => 'View Hasil Assignment Sub-SLS',
            'active_menu' => 'view-assign-sub-sls',
            'admin' => $admin,
            'kegiatanList' => $kegiatanList,
            'assignments' => $assignments,
            'selectedKegiatan' => $idKegiatanWilayah,
            'selectedStatus' => $statusFilter
        ];

        return view('AdminSurveiKab/ViewAssignSubSLS/index', $data);
    }
}
