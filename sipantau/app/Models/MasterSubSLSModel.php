<?php

namespace App\Models;

use CodeIgniter\Model;

class MasterSubSLSModel extends Model
{
    protected $table = 'master_sub_sls';
    protected $primaryKey = 'id_sub_sls';
    protected $allowedFields = ['id_sls', 'id_sub_sls', 'nama_sls', 'nama_desa'];

    public function getSubSLSBySLS($idSLS)
    {
        return $this->where('id_sls', $idSLS)->orderBy('id_sub_sls', 'ASC')->findAll();
    }
}
