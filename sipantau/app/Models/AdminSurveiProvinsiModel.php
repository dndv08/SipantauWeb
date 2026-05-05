<?php

namespace App\Models;

use CodeIgniter\Model;

class AdminSurveiProvinsiModel extends Model
{
    protected $table = 'admin_survei_provinsi';
    protected $primaryKey = 'id_admin_provinsi';
    protected $allowedFields = ['sobat_id'];
    
    // Check apakah user adalah admin provinsi
    public function isAdminProvinsi($sobatId)
    {
        return $this->where('sobat_id', $sobatId)->first() !== null;
    }
    
    // Get ID admin provinsi by sobat_id
    public function getAdminProvinsiId($sobatId)
    {
        $admin = $this->where('sobat_id', $sobatId)->first();
        return $admin ? $admin['id_admin_provinsi'] : null;
    }
    
    // Get kegiatan yang di-assign ke admin provinsi ini
    public function getKegiatanByAdmin($idAdminProvinsi)
    {
        $db = \Config\Database::connect();
        
        return $db->table('master_kegiatan_detail_admin mkda')
            ->select('mkd.*, mk.nama_kegiatan')
            ->join('master_kegiatan_detail mkd', 'mkda.id_kegiatan_detail = mkd.id_kegiatan_detail')
            ->join('master_kegiatan mk', 'mkd.id_kegiatan = mk.id_kegiatan')
            ->where('mkda.id_admin_provinsi', $idAdminProvinsi)
            ->get()
            ->getResultArray();
    }
}
