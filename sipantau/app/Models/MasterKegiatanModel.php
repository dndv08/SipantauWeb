<?php

namespace App\Models;

use CodeIgniter\Model;

class MasterKegiatanModel extends Model
{
    protected $table            = 'master_kegiatan';
    protected $primaryKey       = 'id_kegiatan';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_output', 
        'nama_kegiatan', 
        'keterangan', 
        'pelaksana', 
        'periode'
    ];

    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'id_output'     => 'required|numeric',
        'nama_kegiatan' => 'required|max_length[255]',
        'periode'       => 'required|max_length[50]'
    ];

    protected $validationMessages = [
        'id_output' => [
            'required' => 'Master output harus dipilih',
            'numeric'  => 'Master output tidak valid'
        ],
        'nama_kegiatan' => [
            'required'   => 'Nama kegiatan harus diisi',
            'max_length' => 'Nama kegiatan maksimal 255 karakter'
        ],
        'periode' => [
            'required'   => 'Periode harus diisi',
            'max_length' => 'Periode maksimal 50 karakter'
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
    
    //  Get kegiatan dengan informasi master output (JOIN)
    public function getWithOutput()
    {
        return $this->select('
                master_kegiatan.*,
                master_output.nama_output,
                master_output.fungsi
            ')
            ->join('master_output', 'master_output.id_output = master_kegiatan.id_output', 'left')
            ->orderBy('master_kegiatan.id_kegiatan', 'DESC')
            ->findAll();
    }

    //Get kegiatan berdasarkan ID dengan informasi master output
    public function getWithOutputById($id)
    {
        return $this->select('
                master_kegiatan.*,
                master_output.nama_output,
                master_output.fungsi
            ')
            ->join('master_output', 'master_output.id_output = master_kegiatan.id_output', 'left')
            ->where('master_kegiatan.id_kegiatan', $id)
            ->first();
    }

    // Get kegiatan berdasarkan master output
     
    public function getByOutput($idOutput)
{
    return $this->select('
            master_kegiatan.*,
            master_output.nama_output,
            master_output.fungsi
        ')
        ->join('master_output', 'master_output.id_output = master_kegiatan.id_output', 'left')
        ->where('master_kegiatan.id_output', $idOutput)
        ->orderBy('master_kegiatan.id_kegiatan', 'DESC')
        ->findAll();
}


    // Search kegiatan
    public function search($keyword)
    {
        return $this->select('
                master_kegiatan.*,
                master_output.nama_output
            ')
            ->join('master_output', 'master_output.id_output = master_kegiatan.id_output', 'left')
            ->groupStart()
                ->like('master_kegiatan.nama_kegiatan', $keyword)
                ->orLike('master_output.fungsi', $keyword)
                ->orLike('master_kegiatan.periode', $keyword)
                ->orLike('master_output.nama_output', $keyword)
            ->groupEnd()
            ->orderBy('master_kegiatan.id_kegiatan', 'DESC')
            ->findAll();
    }

    // Get total data
    public function getTotalData()
    {
        return $this->countAll();
    }

    // Get kegiatan by periode
    public function getByPeriode($periode)
    {
        return $this->where('periode', $periode)->findAll();
    }
}