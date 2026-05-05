<?php

namespace App\Models;

use CodeIgniter\Model;

class MasterSLSModel extends Model
{
    protected $table = 'master_sls';
    protected $primaryKey = 'id_sls';
    protected $allowedFields = ['id_desa', 'id_sls', 'nama_sls', 'nama_desa'];

    public function getSLSByDesa($idDesa)
    {
        return $this->where('id_desa', $idDesa)->orderBy('nama_sls', 'ASC')->findAll();
    }
}
