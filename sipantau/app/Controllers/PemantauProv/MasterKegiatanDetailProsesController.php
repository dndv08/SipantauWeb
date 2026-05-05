<?php

namespace App\Controllers\PemantauProv;

use App\Controllers\BaseController;
use App\Models\MasterKegiatanDetailModel;
use App\Models\MasterKegiatanDetailProsesModel;
use CodeIgniter\HTTP\ResponseInterface;

class MasterKegiatanDetailProsesController extends BaseController
{
    protected $masterDetailProsesModel;
    protected $masterDetailModel;
    protected $validation;

    public function __construct()
    {
        $this->masterDetailProsesModel = new MasterKegiatanDetailProsesModel();
        $this->masterDetailModel = new MasterKegiatanDetailModel();
        $this->validation = \Config\Services::validation();
    }

    public function index()
    {
        $kegiatanDetailFilter = $this->request->getGet('kegiatan_detail');

        // Ambil perPage dari GET, default 10
        $perPage = $this->request->getGet('perPage') ?? 10;

        // Validasi perPage agar hanya nilai yang diizinkan
        $allowedPerPage = [5, 10, 25, 50, 100];
        if (!in_array((int) $perPage, $allowedPerPage)) {
            $perPage = 10;
        }

        // Gunakan query builder dari model
        $builder = $this->masterDetailProsesModel
            ->select('master_kegiatan_detail_proses.*, master_kegiatan_detail.nama_kegiatan_detail')
            ->join('master_kegiatan_detail', 'master_kegiatan_detail.id_kegiatan_detail = master_kegiatan_detail_proses.id_kegiatan_detail');

        // Filter jika ada
        if ($kegiatanDetailFilter) {
            $builder->where('master_kegiatan_detail_proses.id_kegiatan_detail', $kegiatanDetailFilter);
        }

        $data = [
            'title' => 'Kelola Master Kegiatan Detail Proses',
            'active_menu' => 'detail-proses',
            'kegiatanDetails' => $builder->paginate($perPage, 'detail_proses'), // Ubah group name
            'pager' => $builder->pager,
            'perPage' => $perPage, // Tambahkan ini
            'kegiatanDetailList' => $this->masterDetailModel->findAll(),
            'selectedKegiatanDetail' => $kegiatanDetailFilter
        ];

        return view('PemantauProvinsi/DetailProses/index', $data);
    }
}