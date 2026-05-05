<?php

namespace App\Models;

use CodeIgniter\Model;

class KurvaPetugasModel extends Model
{
    protected $table            = 'kurva_petugas';
    protected $primaryKey       = 'id_kurva_petugas';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_pcl',
        'tanggal_target',
        'target_persen_kumulatif',
        'target_harian_absolut',
        'target_kumulatif_absolut',
        'is_hari_kerja'
    ];

    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    /**
     * Get Kurva S data by PCL ID
     */
    public function getByPCL($idPCL)
    {
        return $this->where('id_pcl', $idPCL)
                    ->orderBy('tanggal_target', 'ASC')
                    ->findAll();
    }

    /**
     * Delete Kurva S by PCL ID
     */
    public function deleteByPCL($idPCL)
    {
        return $this->where('id_pcl', $idPCL)->delete();
    }
}