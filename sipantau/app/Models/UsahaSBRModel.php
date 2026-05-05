<?php

namespace App\Models;

use CodeIgniter\Model;

class UsahaSBRModel extends Model
{
    protected $table = 'usaha_sbr';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'id_kabupaten',
        'id_kecamatan',
        'id_desa',
        'id_sls',
        'nama_usaha',
        'alamat_usaha',
        'jenis_usaha',
        'nama_pemilik'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getFilteredData($idKabupaten, $filters = [])
    {
        $builder = $this->db->table($this->table . ' u')
            ->select('u.*, kec.nama_kecamatan, des.nama_desa, sls.nama_sls')
            ->join('master_kecamatan kec', 'u.id_kecamatan = kec.id_kecamatan', 'left')
            ->join('master_desa des', 'u.id_desa = des.id_desa', 'left')
            ->join('master_sls sls', 'u.id_sls = sls.id_sls', 'left')
            ->where('u.id_kabupaten', $idKabupaten);

        if (!empty($filters['id_kecamatan'])) {
            $builder->where('u.id_kecamatan', $filters['id_kecamatan']);
        }
        if (!empty($filters['id_desa'])) {
            $builder->where('u.id_desa', $filters['id_desa']);
        }
        if (!empty($filters['id_sls'])) {
            $builder->where('u.id_sls', $filters['id_sls']);
        }
        if (!empty($filters['search'])) {
            $builder->like('u.nama_usaha', $filters['search']);
        }

        return $builder->get()->getResultArray();
    }
}
