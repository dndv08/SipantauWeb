<?php

namespace App\Models;

use CodeIgniter\Model;

class UsahaSBRModel extends Model
{
    protected $table = 'usaha_sbr1';
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

    public function getFilteredDataPaginated($filters = [], $perPage = 50)
    {
        $this->select('*');
        
        if (!empty($filters['kabupaten'])) {
            $this->where('kabupaten', $filters['kabupaten']);
        }
        
        if (!empty($filters['kecamatan'])) {
            $this->where('kecamatan', $filters['kecamatan']);
        }
        
        if (!empty($filters['desa'])) {
            $this->where('desa', $filters['desa']);
        }
        
        if (!empty($filters['sls'])) {
            $this->where('sls', $filters['sls']);
        }
        
        if (!empty($filters['search'])) {
            $this->groupStart()
                ->like('nama_usaha', $filters['search'])
                ->orLike('alamat_usaha', $filters['search'])
                ->groupEnd();
        }

        return [
            'data' => $this->paginate($perPage, 'usaha_sbr'),
            'pager' => $this->pager,
            'total' => $this->pager->getTotal('usaha_sbr')
        ];
    }

    public function getTotalUsaha($filters = [])
    {
        if (!empty($filters['kabupaten'])) {
            $this->where('kabupaten', $filters['kabupaten']);
        }
        
        if (!empty($filters['kecamatan'])) {
            $this->where('kecamatan', $filters['kecamatan']);
        }
        
        if (!empty($filters['desa'])) {
            $this->where('desa', $filters['desa']);
        }
        
        if (!empty($filters['sls'])) {
            $this->where('sls', $filters['sls']);
        }
        
        if (!empty($filters['search'])) {
            $this->groupStart()
                ->like('nama_usaha', $filters['search'])
                ->orLike('alamat_usaha', $filters['search'])
                ->groupEnd();
        }
        return $this->countAllResults();
    }

    public function getStats($filters = [])
    {
        $builder = $this->builder();
        
        if (!empty($filters['kabupaten'])) {
            $builder->where('kabupaten', $filters['kabupaten']);
        }
        
        if (!empty($filters['kecamatan'])) {
            $builder->where('kecamatan', $filters['kecamatan']);
        }
        
        if (!empty($filters['desa'])) {
            $builder->where('desa', $filters['desa']);
        }
        
        if (!empty($filters['sls'])) {
            $builder->where('sls', $filters['sls']);
        }
        
        if (!empty($filters['search'])) {
            $builder->groupStart()
                ->like('nama_usaha', $filters['search'])
                ->orLike('alamat_usaha', $filters['search'])
                ->groupEnd();
        }
        
        $select = "COUNT(*) as total_usaha, 
                   COUNT(DISTINCT kecamatan) as total_kecamatan, 
                   COUNT(DISTINCT desa) as total_desa, 
                   COUNT(DISTINCT CONCAT(desa, '-', sls)) as total_sls";
                   
        return $builder->select($select)->get()->getRowArray() ?: [
            'total_usaha' => 0,
            'total_kecamatan' => 0,
            'total_desa' => 0,
            'total_sls' => 0
        ];
    }
}
