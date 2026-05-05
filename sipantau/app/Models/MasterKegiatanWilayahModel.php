<?php

namespace App\Models;

use CodeIgniter\Model;

class MasterKegiatanWilayahModel extends Model
{
    protected $table = 'kegiatan_wilayah';
    protected $primaryKey = 'id_kegiatan_wilayah';
    protected $allowedFields = [
        'id_kegiatan_detail_proses',
        'id_kabupaten',
        'target_wilayah',
        'keterangan',
        'status'
    ];
    protected $useTimestamps = false;

    /**
     * Get all kegiatan wilayah dengan detail lengkap
     * ORIGINAL METHOD - TIDAK DIUBAH
     */
    public function getAllWithDetails($kabupatenId = null)
    {
        $builder = $this->db->table('kegiatan_wilayah kw')
            ->select('kw.id_kegiatan_wilayah, kw.target_wilayah, kw.keterangan,
                     mkdp.nama_kegiatan_detail_proses, mkdp.tanggal_mulai, mkdp.tanggal_selesai,
                     mkd.nama_kegiatan_detail, mk.nama_kegiatan, 
                     kab.nama_kabupaten, kab.id_kabupaten')
            ->join('master_kegiatan_detail_proses mkdp', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses')
            ->join('master_kegiatan_detail mkd', 'mkdp.id_kegiatan_detail = mkd.id_kegiatan_detail')
            ->join('master_kegiatan mk', 'mkd.id_kegiatan = mk.id_kegiatan')
            ->join('master_kabupaten kab', 'kw.id_kabupaten = kab.id_kabupaten')
            ->orderBy('kab.nama_kabupaten', 'ASC')
            ->orderBy('mkdp.tanggal_mulai', 'DESC');

        if ($kabupatenId) {
            $builder->where('kw.id_kabupaten', $kabupatenId);
        }

        return $builder->get()->getResultArray();
    }

    //untuk pagination
    public function getAllWithDetailsQuery($kabupatenId = null)
{
    $builder = $this->select('
            kegiatan_wilayah.id_kegiatan_wilayah,
            kegiatan_wilayah.target_wilayah,
            kegiatan_wilayah.keterangan,
            master_kegiatan_detail_proses.nama_kegiatan_detail_proses,
            master_kegiatan_detail_proses.tanggal_mulai,
            master_kegiatan_detail_proses.tanggal_selesai,
            master_kegiatan_detail.nama_kegiatan_detail,
            master_kegiatan.nama_kegiatan,
            master_kabupaten.nama_kabupaten,
            master_kabupaten.id_kabupaten
        ')
        ->join('master_kegiatan_detail_proses', 'kegiatan_wilayah.id_kegiatan_detail_proses = master_kegiatan_detail_proses.id_kegiatan_detail_proses')
        ->join('master_kegiatan_detail', 'master_kegiatan_detail_proses.id_kegiatan_detail = master_kegiatan_detail.id_kegiatan_detail')
        ->join('master_kegiatan', 'master_kegiatan_detail.id_kegiatan = master_kegiatan.id_kegiatan')
        ->join('master_kabupaten', 'kegiatan_wilayah.id_kabupaten = master_kabupaten.id_kabupaten')
        ->orderBy('master_kabupaten.nama_kabupaten', 'ASC')
        ->orderBy('master_kegiatan_detail_proses.tanggal_mulai', 'DESC');

    if ($kabupatenId) {
        $builder->where('kegiatan_wilayah.id_kabupaten', $kabupatenId);
    }

    return $builder;
}


    /**
     * Get kegiatan wilayah by kabupaten (untuk filter dropdown)
     * ORIGINAL METHOD - TIDAK DIUBAH
     */
    public function getByKabupaten($idKabupaten)
    {
        return $this->db->table('kegiatan_wilayah kw')
            ->select('kw.id_kegiatan_wilayah, kw.target_wilayah, kw.keterangan,
                     mkdp.nama_kegiatan_detail_proses, mkdp.tanggal_mulai, mkdp.tanggal_selesai,
                     mkd.nama_kegiatan_detail, mk.nama_kegiatan')
            ->join('master_kegiatan_detail_proses mkdp', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses')
            ->join('master_kegiatan_detail mkd', 'mkdp.id_kegiatan_detail = mkd.id_kegiatan_detail')
            ->join('master_kegiatan mk', 'mkd.id_kegiatan = mk.id_kegiatan')
            ->where('kw.id_kabupaten', $idKabupaten)
            ->orderBy('mkdp.tanggal_mulai', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Get kegiatan wilayah with admin info
     * ORIGINAL METHOD - TIDAK DIUBAH
     */
    public function getWithAdminInfo($idKegiatanWilayah)
    {
        return $this->db->table('kegiatan_wilayah kw')
            ->select('kw.*, mkdp.nama_kegiatan_detail_proses, mkdp.tanggal_mulai, mkdp.tanggal_selesai,
                     mkd.nama_kegiatan_detail, mk.nama_kegiatan,
                     kab.nama_kabupaten')
            ->join('master_kegiatan_detail_proses mkdp', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses')
            ->join('master_kegiatan_detail mkd', 'mkdp.id_kegiatan_detail = mkd.id_kegiatan_detail')
            ->join('master_kegiatan mk', 'mkd.id_kegiatan = mk.id_kegiatan')
            ->join('master_kabupaten kab', 'kw.id_kabupaten = kab.id_kabupaten')
            ->where('kw.id_kegiatan_wilayah', $idKegiatanWilayah)
            ->get()
            ->getRowArray();
    }

    // ============================================================
    // NEW METHODS - untuk filter berdasarkan assignment admin
    // ============================================================

    /**
     * Get kegiatan wilayah by kabupaten dan admin (hanya yang di-assign)
     * NEW METHOD - untuk Admin Kabupaten yang hanya bisa akses kegiatan yang di-assign ke mereka
     * 
     * @param int $idKabupaten ID Kabupaten
     * @param int $idAdminKabupaten ID Admin Kabupaten
     * @return array List kegiatan wilayah yang di-assign ke admin
     */
    public function getByKabupatenAndAdmin($idKabupaten, $idAdminKabupaten)
    {
        return $this->db->table('kegiatan_wilayah kw')
            ->select('kw.id_kegiatan_wilayah, kw.target_wilayah, kw.keterangan,
                     mkdp.nama_kegiatan_detail_proses, mkdp.tanggal_mulai, mkdp.tanggal_selesai,
                     mkd.nama_kegiatan_detail, mk.nama_kegiatan')
            ->join('kegiatan_wilayah_admin kwa', 'kw.id_kegiatan_wilayah = kwa.id_kegiatan_wilayah')
            ->join('master_kegiatan_detail_proses mkdp', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses')
            ->join('master_kegiatan_detail mkd', 'mkdp.id_kegiatan_detail = mkd.id_kegiatan_detail')
            ->join('master_kegiatan mk', 'mkd.id_kegiatan = mk.id_kegiatan')
            ->where('kw.id_kabupaten', $idKabupaten)
            ->where('kwa.id_admin_kabupaten', $idAdminKabupaten)
            ->orderBy('mkdp.tanggal_mulai', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Get all kegiatan wilayah dengan detail lengkap (filtered by admin)
     * NEW METHOD - variant dari getAllWithDetails dengan filter admin
     * 
     * @param int $idAdminKabupaten ID Admin Kabupaten
     * @param int|null $kabupatenId Optional filter by kabupaten
     * @return array List kegiatan wilayah yang di-assign ke admin
     */
    public function getAllWithDetailsByAdmin($idAdminKabupaten, $kabupatenId = null)
    {
        $builder = $this->db->table('kegiatan_wilayah kw')
            ->select('kw.id_kegiatan_wilayah, kw.target_wilayah, kw.keterangan,
                     mkdp.nama_kegiatan_detail_proses, mkdp.tanggal_mulai, mkdp.tanggal_selesai,
                     mkd.nama_kegiatan_detail, mk.nama_kegiatan, 
                     kab.nama_kabupaten, kab.id_kabupaten')
            ->join('kegiatan_wilayah_admin kwa', 'kw.id_kegiatan_wilayah = kwa.id_kegiatan_wilayah')
            ->join('master_kegiatan_detail_proses mkdp', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses')
            ->join('master_kegiatan_detail mkd', 'mkdp.id_kegiatan_detail = mkd.id_kegiatan_detail')
            ->join('master_kegiatan mk', 'mkd.id_kegiatan = mk.id_kegiatan')
            ->join('master_kabupaten kab', 'kw.id_kabupaten = kab.id_kabupaten')
            ->where('kwa.id_admin_kabupaten', $idAdminKabupaten)
            ->orderBy('kab.nama_kabupaten', 'ASC')
            ->orderBy('mkdp.tanggal_mulai', 'DESC');

        if ($kabupatenId) {
            $builder->where('kw.id_kabupaten', $kabupatenId);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Check apakah admin punya akses ke kegiatan wilayah tertentu
     * NEW METHOD - untuk validasi akses
     * 
     * @param int $idKegiatanWilayah ID Kegiatan Wilayah
     * @param int $idAdminKabupaten ID Admin Kabupaten
     * @return bool True jika admin punya akses
     */
    public function isAdminHasAccess($idKegiatanWilayah, $idAdminKabupaten)
    {
        $result = $this->db->table('kegiatan_wilayah_admin')
            ->where('id_kegiatan_wilayah', $idKegiatanWilayah)
            ->where('id_admin_kabupaten', $idAdminKabupaten)
            ->countAllResults();

        return $result > 0;
    }

    /**
     * Get kegiatan wilayah dengan informasi assignment admin
     * NEW METHOD - untuk melihat admin mana saja yang di-assign ke kegiatan ini
     * 
     * @param int $idKegiatanWilayah ID Kegiatan Wilayah
     * @return array Detail kegiatan dengan list admin yang di-assign
     */
    public function getWithAssignedAdmins($idKegiatanWilayah)
    {
        $kegiatan = $this->getWithAdminInfo($idKegiatanWilayah);
        
        if (!$kegiatan) {
            return null;
        }

        // Get list admin yang di-assign
        $admins = $this->db->table('kegiatan_wilayah_admin kwa')
            ->select('ask.id_admin_kabupaten, u.nama_user, u.email, u.hp')
            ->join('admin_survei_kabupaten ask', 'kwa.id_admin_kabupaten = ask.id_admin_kabupaten')
            ->join('sipantau_user u', 'ask.sobat_id = u.sobat_id')
            ->where('kwa.id_kegiatan_wilayah', $idKegiatanWilayah)
            ->get()
            ->getResultArray();

        $kegiatan['assigned_admins'] = $admins;
        $kegiatan['jumlah_admin'] = count($admins);

        return $kegiatan;
    }

    /**
     * Get statistik kegiatan per admin
     * NEW METHOD - untuk dashboard admin kabupaten
     * 
     * @param int $idAdminKabupaten ID Admin Kabupaten
     * @return array Statistik kegiatan yang di-handle admin
     */
    public function getStatisticsByAdmin($idAdminKabupaten)
    {
        $totalKegiatan = $this->db->table('kegiatan_wilayah_admin')
            ->where('id_admin_kabupaten', $idAdminKabupaten)
            ->countAllResults();

        $kegiatanAktif = $this->db->table('kegiatan_wilayah_admin kwa')
            ->join('kegiatan_wilayah kw', 'kwa.id_kegiatan_wilayah = kw.id_kegiatan_wilayah')
            ->where('kwa.id_admin_kabupaten', $idAdminKabupaten)
            ->where('kw.status', 'Aktif')
            ->countAllResults();

        $totalTarget = $this->db->table('kegiatan_wilayah_admin kwa')
            ->selectSum('kw.target_wilayah', 'total_target')
            ->join('kegiatan_wilayah kw', 'kwa.id_kegiatan_wilayah = kw.id_kegiatan_wilayah')
            ->where('kwa.id_admin_kabupaten', $idAdminKabupaten)
            ->get()
            ->getRow()
            ->total_target ?? 0;

        return [
            'total_kegiatan' => $totalKegiatan,
            'kegiatan_aktif' => $kegiatanAktif,
            'total_target' => (int)$totalTarget
        ];
    }
}