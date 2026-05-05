<?php

namespace App\Models;

use CodeIgniter\Model;

class MasterKegiatanDetailProsesModel extends Model
{
    protected $table = 'master_kegiatan_detail_proses';
    protected $primaryKey = 'id_kegiatan_detail_proses';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'id_kegiatan_detail',
        'nama_kegiatan_detail_proses',
        'satuan',
        'tanggal_mulai',
        'tanggal_selesai',
        'keterangan',
        'periode',
        'target',
        'persentase_target_awal',
        'tanggal_selesai_target',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation Rules
    protected $validationRules = [
        'id_kegiatan_detail' => 'required|numeric',
        'nama_kegiatan_detail_proses' => 'required|max_length[255]',
        'satuan' => 'permit_empty|max_length[50]',
        'periode' => 'permit_empty|max_length[50]',
        'target' => 'permit_empty|numeric',
        'persentase' => 'permit_empty|decimal',
        'persentase_target_awal' => 'permit_empty|decimal',
        'tanggal_selesai_target' => 'permit_empty|valid_date'
    ];

    protected $validationMessages = [
        'id_kegiatan_detail' => [
            'required' => 'Kegiatan detail harus dipilih',
            'numeric' => 'Kegiatan detail tidak valid'
        ],
        'nama_kegiatan_detail_proses' => [
            'required' => 'Nama kegiatan detail proses harus diisi',
            'max_length' => 'Nama kegiatan detail proses maksimal 255 karakter'
        ],
        'satuan' => [
            'max_length' => 'Satuan maksimal 50 karakter'
        ],
        'periode' => [
            'max_length' => 'Periode maksimal 50 karakter'
        ],
        'target' => [
            'numeric' => 'Target harus berupa angka'
        ],
        'persentase' => [
            'decimal' => 'Persentase harus berupa angka desimal'
        ],
        'persentase_target_awal' => [
            'decimal' => 'Persentase hari pertama harus berupa angka desimal'
        ],
        'tanggal_selesai_target' => [
            'valid_date' => 'Target 100 persen harus berupa tanggal yang valid'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;
    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    // ====================================================================
    // Custom Methods
    // ====================================================================

    public function getData($kegiatanDetailFilter = null, $idAdminProvinsi = null)
    {
        $builder = $this->db->table($this->table . ' kdp')
            ->select('kdp.*, mkd.nama_kegiatan_detail')
            ->join('master_kegiatan_detail mkd', 'mkd.id_kegiatan_detail = kdp.id_kegiatan_detail');

        // Filter berdasarkan assignment admin provinsi
        // Jika idAdminProvinsi null, artinya Super Admin, tampilkan semua
        if ($idAdminProvinsi !== null) {
            $builder->join('master_kegiatan_detail_admin mkda', 'mkda.id_kegiatan_detail = kdp.id_kegiatan_detail')
                ->where('mkda.id_admin_provinsi', $idAdminProvinsi);
        }

        // Filter by kegiatan detail jika ada
        if ($kegiatanDetailFilter) {
            $builder->where('kdp.id_kegiatan_detail', $kegiatanDetailFilter);
        }

        return $builder->orderBy('kdp.created_at', 'DESC')->get()->getResultArray();
    }

    public function getDataPaginated($kegiatanDetailFilter = null, $idAdminProvinsi = null, $perPage = 10)
    {
        $builder = $this->select('master_kegiatan_detail_proses.*, mkd.nama_kegiatan_detail')
            ->join('master_kegiatan_detail mkd', 'mkd.id_kegiatan_detail = master_kegiatan_detail_proses.id_kegiatan_detail');

        // Filter berdasarkan assignment admin provinsi
        if ($idAdminProvinsi !== null) {
            $builder->join('master_kegiatan_detail_admin mkda', 'mkda.id_kegiatan_detail = master_kegiatan_detail_proses.id_kegiatan_detail')
                ->where('mkda.id_admin_provinsi', $idAdminProvinsi);
        }

        // Filter by kegiatan detail jika ada
        if ($kegiatanDetailFilter) {
            $builder->where('master_kegiatan_detail_proses.id_kegiatan_detail', $kegiatanDetailFilter);
        }

        return $builder->orderBy('master_kegiatan_detail_proses.created_at', 'DESC')
            ->paginate($perPage, 'kegiatan_details');
    }
}