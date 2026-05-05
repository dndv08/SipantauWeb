<?php

namespace App\Models;

use CodeIgniter\Model;

class MasterKegiatanDetailAdminModel extends Model
{
    protected $table = 'master_kegiatan_detail_admin';
    protected $primaryKey = 'id';
    protected $allowedFields = ['id_admin_provinsi', 'id_kegiatan_detail'];
    
    // Get admin provinsi untuk kegiatan detail tertentu
    public function getAdminByKegiatanDetail($idKegiatanDetail)
    {
        return $this->select('admin_survei_provinsi.sobat_id, sipantau_user.nama_user, sipantau_user.email')
            ->join('admin_survei_provinsi', 'admin_survei_provinsi.id_admin_provinsi = master_kegiatan_detail_admin.id_admin_provinsi')
            ->join('sipantau_user', 'sipantau_user.sobat_id = admin_survei_provinsi.sobat_id')
            ->where('id_kegiatan_detail', $idKegiatanDetail)
            ->findAll();
    }
    
    // Assign admin ke kegiatan detail
    public function assignAdmin($idAdminProvinsi, $idKegiatanDetail)
    {
        // Check if already assigned
        $existing = $this->where([
            'id_admin_provinsi' => $idAdminProvinsi,
            'id_kegiatan_detail' => $idKegiatanDetail
        ])->first();
        
        if (!$existing) {
            return $this->insert([
                'id_admin_provinsi' => $idAdminProvinsi,
                'id_kegiatan_detail' => $idKegiatanDetail
            ]);
        }
        
        return false;
    }
}

