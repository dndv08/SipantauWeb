<?php

namespace App\Models;

use CodeIgniter\Model;

class MasterKegiatanDetailModel extends Model
{
    protected $table            = 'master_kegiatan_detail';
    protected $primaryKey       = 'id_kegiatan_detail';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_kegiatan',
        'nama_kegiatan_detail',
        'satuan',
        'periode',
        'tahun',
        'tanggal_mulai',
        'tanggal_selesai',
        'keterangan'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation Rules
    protected $validationRules = [
        'id_kegiatan'           => 'required|numeric',
        'nama_kegiatan_detail'  => 'required|max_length[255]',
        'satuan'                => 'required|max_length[100]',
        'periode'               => 'required|max_length[50]',
        'tahun'                 => 'required|numeric|max_length[4]'
    ];

    protected $validationMessages = [
        'id_kegiatan' => [
            'required' => 'Master kegiatan harus dipilih',
            'numeric'  => 'Master kegiatan tidak valid'
        ],
        'nama_kegiatan_detail' => [
            'required'   => 'Nama kegiatan detail harus diisi',
            'max_length' => 'Nama kegiatan detail maksimal 255 karakter'
        ],
        'satuan' => [
            'required'   => 'Satuan harus diisi',
            'max_length' => 'Satuan maksimal 100 karakter'
        ],
        'periode' => [
            'required'   => 'Periode harus diisi',
            'max_length' => 'Periode maksimal 50 karakter'
        ],
        'tahun' => [
            'required'   => 'Tahun harus diisi',
            'numeric'    => 'Tahun harus berupa angka',
            'max_length' => 'Tahun maksimal 4 digit'
        ]
    ];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
    protected $allowCallbacks       = true;
    protected $beforeInsert         = [];
    protected $afterInsert          = [];
    protected $beforeUpdate         = [];
    protected $afterUpdate          = [];
    protected $beforeFind           = [];
    protected $afterFind            = [];
    protected $beforeDelete         = [];
    protected $afterDelete          = [];

    // ====================================================================
    // Custom Methods
    // ====================================================================

    // Get kegiatan detail berdasarkan id_kegiatan
    public function getByKegiatan($idKegiatan)
    {
        return $this->where('id_kegiatan', $idKegiatan)
                    ->orderBy('id_kegiatan_detail', 'DESC')
                    ->findAll();
    }

    // Get kegiatan detail dengan informasi master kegiatan (JOIN)
    public function getWithKegiatan()
    {
        return $this->select('
                master_kegiatan_detail.*,
                master_kegiatan.nama_kegiatan,
                master_kegiatan.periode as periode_kegiatan
            ')
            ->join('master_kegiatan', 'master_kegiatan.id_kegiatan = master_kegiatan_detail.id_kegiatan', 'left')
            ->orderBy('master_kegiatan_detail.id_kegiatan_detail', 'DESC')
            ->findAll();
    }

    // Get kegiatan detail by ID dengan informasi master kegiatan
    public function getWithKegiatanById($id)
    {
        return $this->select('
                master_kegiatan_detail.*,
                master_kegiatan.nama_kegiatan,
                master_kegiatan.periode as periode_kegiatan
            ')
            ->join('master_kegiatan', 'master_kegiatan.id_kegiatan = master_kegiatan_detail.id_kegiatan', 'left')
            ->where('master_kegiatan_detail.id_kegiatan_detail', $id)
            ->first();
    }

    // Search kegiatan detail
    public function search($keyword)
    {
        return $this->select('
                master_kegiatan_detail.*,
                master_kegiatan.nama_kegiatan
            ')
            ->join('master_kegiatan', 'master_kegiatan.id_kegiatan = master_kegiatan_detail.id_kegiatan', 'left')
            ->groupStart()
                ->like('master_kegiatan_detail.nama_kegiatan_detail', $keyword)
                ->orLike('master_kegiatan_detail.satuan', $keyword)
                ->orLike('master_kegiatan_detail.periode', $keyword)
                ->orLike('master_kegiatan.nama_kegiatan', $keyword)
            ->groupEnd()
            ->orderBy('master_kegiatan_detail.id_kegiatan_detail', 'DESC')
            ->findAll();
    }

    // Get total data
    public function getTotalData()
    {
        return $this->countAll();
    }

    // Get by tahun
    public function getByTahun($tahun)
    {
        return $this->where('tahun', $tahun)->findAll();
    }

    // Count by kegiatan
    public function countByKegiatan($idKegiatan)
    {
        return $this->where('id_kegiatan', $idKegiatan)->countAllResults();
    }
}