<?php

namespace App\Controllers\SuperAdmin;

use App\Controllers\BaseController;
use App\Models\MasterKegiatanModel;
use App\Models\MasterOutputModel;
use App\Models\MasterKegiatanDetailModel;

class MasterKegiatanController extends BaseController
{
    protected $masterKegiatanModel;
    protected $masterOutputModel;
    protected $masterKegiatanDetailModel;
    protected $validation;

    public function __construct()
    {
        $this->masterKegiatanModel = new MasterKegiatanModel();
        $this->masterOutputModel = new MasterOutputModel();
        $this->masterKegiatanDetailModel = new MasterKegiatanDetailModel();
        $this->validation = \Config\Services::validation();
    }

    // Index - Menampilkan halaman daftar master kegiatan
    public function index()
    {
        $filterOutput = $this->request->getGet('output');

        // Ambil perPage dari GET, default 10
        $perPage = $this->request->getGet('perPage') ?? 10;

        // Validasi perPage agar hanya nilai yang diizinkan
        $allowedPerPage = [5, 10, 25, 50, 100];
        if (!in_array((int) $perPage, $allowedPerPage)) {
            $perPage = 10;
        }

        // Get all master outputs untuk filter
        $masterOutputs = $this->masterOutputModel->orderBy('nama_output', 'ASC')->findAll();

        // Get kegiatan dengan pagination
        if ($filterOutput && $filterOutput != 'all') {
            $kegiatans = $this->masterKegiatanModel
                ->select('
                master_kegiatan.*,
                master_output.nama_output,
                master_output.fungsi
            ')
                ->join('master_output', 'master_output.id_output = master_kegiatan.id_output', 'left')
                ->where('master_kegiatan.id_output', $filterOutput)
                ->orderBy('master_kegiatan.id_kegiatan', 'DESC')
                ->paginate($perPage, 'kegiatans');
        } else {
            $kegiatans = $this->masterKegiatanModel
                ->select('
                master_kegiatan.*,
                master_output.nama_output,
                master_output.fungsi
            ')
                ->join('master_output', 'master_output.id_output = master_kegiatan.id_output', 'left')
                ->orderBy('master_kegiatan.id_kegiatan', 'DESC')
                ->paginate($perPage, 'kegiatans');
        }

        $data = [
            'title' => 'Kelola Master Kegiatan',
            'active_menu' => 'master-kegiatan',
            'kegiatans' => $kegiatans,
            'masterOutputs' => $masterOutputs,
            'filterOutput' => $filterOutput ?? 'all',
            'perPage' => $perPage,
            'pager' => $this->masterKegiatanModel->pager,
        ];

        return view('SuperAdmin/MasterKegiatan/index', $data);
    }

    // Create - Menampilkan form tambah master kegiatan
    public function create()
    {
        $masterOutputs = $this->masterOutputModel->orderBy('nama_output', 'ASC')->findAll();

        $data = [
            'title' => 'Tambah Master Kegiatan',
            'active_menu' => 'master-kegiatan',
            'validation' => $this->validation,
            'masterOutputs' => $masterOutputs
        ];

        return view('SuperAdmin/MasterKegiatan/create', $data);
    }

    // Store - Menyimpan data master kegiatan baru
    public function store()
    {
        $rules = [
            'id_output' => [
                'rules' => 'required|numeric',
                'errors' => [
                    'required' => 'Master output harus dipilih',
                    'numeric' => 'Master output tidak valid'
                ]
            ],
            'nama_kegiatan' => [
                'rules' => 'required|max_length[255]|is_unique[master_kegiatan.nama_kegiatan]',
                'errors' => [
                    'required' => 'Nama kegiatan harus diisi',
                    'max_length' => 'Nama kegiatan maksimal 255 karakter',
                    'is_unique' => 'Nama kegiatan sudah terdaftar'
                ]
            ],
            'keterangan' => [
                'rules' => 'permit_empty',
                'errors' => []
            ],
            'pelaksana' => [
                'rules' => 'permit_empty|max_length[255]',
                'errors' => [
                    'max_length' => 'Pelaksana maksimal 255 karakter'
                ]
            ],
            'periode' => [
                'rules' => 'required|max_length[50]',
                'errors' => [
                    'required' => 'Periode harus diisi',
                    'max_length' => 'Periode maksimal 50 karakter'
                ]
            ]
        ];

        if (!$this->validate($rules)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $data = [
            'id_output' => $this->request->getPost('id_output'),
            'nama_kegiatan' => $this->request->getPost('nama_kegiatan'),
            'keterangan' => $this->request->getPost('keterangan'),
            'pelaksana' => $this->request->getPost('pelaksana'),
            'periode' => $this->request->getPost('periode')
        ];

        if ($this->masterKegiatanModel->insert($data)) {
            return redirect()
                ->to(base_url('superadmin/master-kegiatan'))
                ->with('success', 'Data master kegiatan berhasil ditambahkan');
        } else {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal menyimpan data master kegiatan');
        }
    }

    // Show - Menampilkan detail master kegiatan
    public function show($id)
    {
        $kegiatan = $this->masterKegiatanModel->getWithOutputById($id);

        if (!$kegiatan) {
            return redirect()
                ->to(base_url('superadmin/master-kegiatan'))
                ->with('error', 'Data master kegiatan tidak ditemukan');
        }

        // Ambil perPage dari GET, default 10
        $perPage = $this->request->getGet('perPage') ?? 10;

        // Validasi perPage
        $allowedPerPage = [5, 10, 25, 50, 100];
        if (!in_array((int) $perPage, $allowedPerPage)) {
            $perPage = 10;
        }

        // Get kegiatan detail dengan pagination
        $kegiatanDetails = $this->masterKegiatanDetailModel
            ->where('id_kegiatan', $id)
            ->orderBy('id_kegiatan_detail', 'DESC')
            ->paginate($perPage, 'kegiatanDetails');

        $data = [
            'title' => 'Detail Master Kegiatan',
            'active_menu' => 'master-kegiatan',
            'kegiatan' => $kegiatan,
            'kegiatanDetails' => $kegiatanDetails,
            'perPage' => $perPage,
            'pager' => $this->masterKegiatanDetailModel->pager,
        ];

        return view('SuperAdmin/MasterKegiatan/show', $data);
    }

    // Edit - Menampilkan form edit master kegiatan
    public function edit($id)
    {
        $kegiatan = $this->masterKegiatanModel->find($id);

        if (!$kegiatan) {
            return redirect()
                ->to(base_url('superadmin/master-kegiatan'))
                ->with('error', 'Data master kegiatan tidak ditemukan');
        }

        $masterOutputs = $this->masterOutputModel->orderBy('nama_output', 'ASC')->findAll();

        $data = [
            'title' => 'Edit Master Kegiatan',
            'active_menu' => 'master-kegiatan',
            'kegiatan' => $kegiatan,
            'validation' => $this->validation,
            'masterOutputs' => $masterOutputs
        ];

        return view('SuperAdmin/MasterKegiatan/edit', $data);
    }

    // Update - Memperbarui data master kegiatan
    public function update($id)
    {
        $kegiatan = $this->masterKegiatanModel->find($id);

        if (!$kegiatan) {
            return redirect()
                ->to(base_url('superadmin/master-kegiatan'))
                ->with('error', 'Data master kegiatan tidak ditemukan');
        }

        $rules = [
            'id_output' => [
                'rules' => 'required|numeric',
                'errors' => [
                    'required' => 'Master output harus dipilih',
                    'numeric' => 'Master output tidak valid'
                ]
            ],
            'nama_kegiatan' => [
                'rules' => "required|max_length[255]|is_unique[master_kegiatan.nama_kegiatan,id_kegiatan,{$id}]",
                'errors' => [
                    'required' => 'Nama kegiatan harus diisi',
                    'max_length' => 'Nama kegiatan maksimal 255 karakter',
                    'is_unique' => 'Nama kegiatan sudah terdaftar'
                ]
            ],
            'keterangan' => [
                'rules' => 'permit_empty',
                'errors' => []
            ],
            'pelaksana' => [
                'rules' => 'permit_empty|max_length[255]',
                'errors' => [
                    'max_length' => 'Pelaksana maksimal 255 karakter'
                ]
            ],
            'periode' => [
                'rules' => 'required|max_length[50]',
                'errors' => [
                    'required' => 'Periode harus diisi',
                    'max_length' => 'Periode maksimal 50 karakter'
                ]
            ]
        ];

        if (!$this->validate($rules)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $data = [
            'id_output' => $this->request->getPost('id_output'),
            'nama_kegiatan' => $this->request->getPost('nama_kegiatan'),
            'keterangan' => $this->request->getPost('keterangan'),
            'pelaksana' => $this->request->getPost('pelaksana'),
            'periode' => $this->request->getPost('periode')
        ];

        if ($this->masterKegiatanModel->update($id, $data)) {
            return redirect()
                ->to(base_url('superadmin/master-kegiatan'))
                ->with('success', 'Data master kegiatan berhasil diperbarui');
        } else {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui data master kegiatan');
        }
    }

    // Delete - Menghapus data master kegiatan
    public function delete($id)
    {
        $kegiatan = $this->masterKegiatanModel->find($id);

        if (!$kegiatan) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Data master kegiatan tidak ditemukan'
            ]);
        }

        if ($this->masterKegiatanModel->delete($id)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Data master kegiatan berhasil dihapus'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal menghapus data master kegiatan'
            ]);
        }
    }
}