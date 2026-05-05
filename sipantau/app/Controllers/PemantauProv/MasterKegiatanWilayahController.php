<?php

namespace App\Controllers\PemantauProv;

use App\Controllers\BaseController;
use App\Models\MasterKabModel;
use App\Models\MasterKegiatanWilayahModel;

class MasterKegiatanWilayahController extends BaseController
{
    protected $masterKegiatanWilayah;
    protected $masterKab;

    public function __construct()
    {
        $this->masterKegiatanWilayah = new MasterKegiatanWilayahModel();
        $this->masterKab = new MasterKabModel();
    }

    public function index()
    {
        // Ambil parameter filter dari GET
        $kabupatenId = $this->request->getGet('kabupaten');

        // Ambil perPage dari GET, default 10
        $perPage = $this->request->getGet('perPage') ?? 10;

        // Validasi perPage agar hanya nilai yang diizinkan
        $allowedPerPage = [5, 10, 25, 50, 100];
        if (!in_array((int) $perPage, $allowedPerPage)) {
            $perPage = 10;
        }

        // Ambil semua kabupaten untuk dropdown
        $kabupatenList = $this->masterKab->orderBy('id_kabupaten', 'ASC')->findAll();

        // Query dengan pagination - pastikan menggunakan builder yang benar
        $kegiatanWilayah = $this->masterKegiatanWilayah
            ->getAllWithDetailsQuery($kabupatenId)
            ->paginate($perPage, 'kegiatanWilayah');

        $data = [
            'title' => 'Pemantau - Kegiatan Wilayah',
            'active_menu' => 'kegiatan-wilayah-pemantau',
            'kegiatanWilayah' => $kegiatanWilayah,
            'kabupatenList' => $kabupatenList,
            'selectedKabupaten' => $kabupatenId,
            'perPage' => $perPage,
            'pager' => $this->masterKegiatanWilayah->pager,
        ];

        return view('PemantauProvinsi/KegiatanWilayah/index', $data);
    }
}