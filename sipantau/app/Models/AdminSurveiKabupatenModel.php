<?php

namespace App\Models;

use CodeIgniter\Model;

class AdminSurveiKabupatenModel extends Model
{
    protected $table = 'admin_survei_kabupaten';
    protected $primaryKey = 'id_admin_kabupaten';
    protected $allowedFields = ['sobat_id'];
    protected $useTimestamps = false;

    /**
     * Get all admin kabupaten dengan detail user dan kegiatan
     */
    public function getAdminWithDetails($search = '', $kabupaten = '')
    {
        $builder = $this->db->table('admin_survei_kabupaten ask')
            ->select('ask.id_admin_kabupaten, ask.sobat_id, u.nama_user, u.email, u.hp, u.is_active, k.nama_kabupaten, k.id_kabupaten')
            ->join('sipantau_user u', 'ask.sobat_id = u.sobat_id')
            ->join('master_kabupaten k', 'u.id_kabupaten = k.id_kabupaten', 'left')
            ->orderBy('k.nama_kabupaten', 'ASC')
            ->orderBy('u.nama_user', 'ASC');

        if (!empty($search)) {
            $builder->groupStart()
                ->like('u.nama_user', $search)
                ->orLike('u.email', $search)
                ->orLike('k.nama_kabupaten', $search)
                ->groupEnd();
        }

        if (!empty($kabupaten)) {
            $builder->where('k.id_kabupaten', $kabupaten);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Get admin by ID dengan detail user
     */
    public function getAdminById($idAdmin)
    {
        return $this->db->table('admin_survei_kabupaten ask')
            ->select('ask.*, u.nama_user, u.email, u.hp, k.nama_kabupaten, k.id_kabupaten')
            ->join('sipantau_user u', 'ask.sobat_id = u.sobat_id')
            ->join('master_kabupaten k', 'u.id_kabupaten = k.id_kabupaten', 'left')
            ->where('ask.id_admin_kabupaten', $idAdmin)
            ->get()
            ->getRowArray();
    }

    /**
     * Check apakah user sudah menjadi admin kabupaten
     */
    public function isAdminKabupaten($sobatId)
    {
        return $this->where('sobat_id', $sobatId)->first() !== null;
    }

    // Get ID admin kabupaten by sobat_id
    public function getAdminKabupatenId($sobatId)
    {
        $admin = $this->where('sobat_id', $sobatId)->first();
        return $admin ? $admin['id_admin_kabupaten'] : null;
    }

    /**
     * Get kegiatan wilayah yang di-assign ke admin
     */
    public function getKegiatanWilayahByAdmin($idAdmin)
    {
        return $this->db->table('kegiatan_wilayah_admin kwa')
            ->select('kw.id_kegiatan_wilayah, kw.target_wilayah, kw.keterangan, 
                     mkdp.nama_kegiatan_detail_proses, mkdp.tanggal_mulai, mkdp.tanggal_selesai,
                     mkd.nama_kegiatan_detail, mk.nama_kegiatan, kab.nama_kabupaten')
            ->join('kegiatan_wilayah kw', 'kwa.id_kegiatan_wilayah = kw.id_kegiatan_wilayah')
            ->join('master_kegiatan_detail_proses mkdp', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses')
            ->join('master_kegiatan_detail mkd', 'mkdp.id_kegiatan_detail = mkd.id_kegiatan_detail')
            ->join('master_kegiatan mk', 'mkd.id_kegiatan = mk.id_kegiatan')
            ->join('master_kabupaten kab', 'kw.id_kabupaten = kab.id_kabupaten')
            ->where('kwa.id_admin_kabupaten', $idAdmin)
            ->orderBy('mkdp.tanggal_mulai', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Delete admin dan semua assignmentnya
     */
    public function deleteAdminWithAssignments($idAdmin)
    {
        $this->db->transStart();

        // Hapus assignments
        $this->db->table('kegiatan_wilayah_admin')
            ->where('id_admin_kabupaten', $idAdmin)
            ->delete();

        // Hapus admin
        $this->delete($idAdmin);

        $this->db->transComplete();

        return $this->db->transStatus();
    }
}