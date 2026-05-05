<?php

namespace App\Models;

use CodeIgniter\Model;

class PMLModel extends Model
{
    protected $table = 'pml';
    protected $primaryKey = 'id_pml';
    protected $allowedFields = [
        'sobat_id',
        'id_kegiatan_wilayah',
        'target',
        'status_approval',
        'tanggal_approval',
        'feedback_admin',
        'created_at',
        'updated_at'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Get all PML dengan detail lengkap untuk admin kabupaten
     * ORIGINAL METHOD - TIDAK DIUBAH
     */
    public function getPMLByKabupaten($idKabupaten, $idKegiatanWilayah = null)
    {
        $builder = $this->db->table('pml p')
            ->select('p.id_pml, p.target, p.status_approval, p.created_at,
                     u.nama_user as nama_pml, u.email, u.hp,
                     kw.id_kegiatan_wilayah, kw.target_wilayah,
                     mkdp.nama_kegiatan_detail_proses, mkdp.tanggal_mulai, mkdp.tanggal_selesai,
                     mkd.nama_kegiatan_detail,
                     mk.nama_kegiatan,
                     (SELECT COUNT(*) FROM pcl WHERE id_pml = p.id_pml) as jumlah_pcl,
                     (SELECT COALESCE(SUM(target), 0) FROM pcl WHERE id_pml = p.id_pml) as total_target_pcl')
            ->join('sipantau_user u', 'p.sobat_id = u.sobat_id')
            ->join('kegiatan_wilayah kw', 'p.id_kegiatan_wilayah = kw.id_kegiatan_wilayah')
            ->join('master_kegiatan_detail_proses mkdp', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses')
            ->join('master_kegiatan_detail mkd', 'mkdp.id_kegiatan_detail = mkd.id_kegiatan_detail')
            ->join('master_kegiatan mk', 'mkd.id_kegiatan = mk.id_kegiatan')
            ->where('kw.id_kabupaten', $idKabupaten)
            ->orderBy('mkdp.tanggal_mulai', 'DESC')
            ->orderBy('p.created_at', 'DESC');

        if ($idKegiatanWilayah) {
            $builder->where('p.id_kegiatan_wilayah', $idKegiatanWilayah);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Get PML by Kabupaten dan Admin (hanya kegiatan yang di-assign)
     * NEW METHOD - untuk filter berdasarkan assignment admin
     */
    public function getPMLByKabupatenAndAdmin($idKabupaten, $idAdminKabupaten, $idKegiatanWilayah = null)
    {
        $builder = $this->db->table('pml p')
            ->select('p.id_pml, p.target, p.status_approval, p.created_at,
                     u.nama_user as nama_pml, u.email, u.hp,
                     kw.id_kegiatan_wilayah, kw.target_wilayah,
                     mkdp.nama_kegiatan_detail_proses, mkdp.tanggal_mulai, mkdp.tanggal_selesai,
                     mkd.nama_kegiatan_detail,
                     mk.nama_kegiatan,
                     (SELECT COUNT(*) FROM pcl WHERE id_pml = p.id_pml) as jumlah_pcl,
                     (SELECT COALESCE(SUM(target), 0) FROM pcl WHERE id_pml = p.id_pml) as total_target_pcl')
            ->join('sipantau_user u', 'p.sobat_id = u.sobat_id')
            ->join('kegiatan_wilayah kw', 'p.id_kegiatan_wilayah = kw.id_kegiatan_wilayah')
            ->join('kegiatan_wilayah_admin kwa', 'kw.id_kegiatan_wilayah = kwa.id_kegiatan_wilayah')
            ->join('master_kegiatan_detail_proses mkdp', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses')
            ->join('master_kegiatan_detail mkd', 'mkdp.id_kegiatan_detail = mkd.id_kegiatan_detail')
            ->join('master_kegiatan mk', 'mkd.id_kegiatan = mk.id_kegiatan')
            ->where('kw.id_kabupaten', $idKabupaten)
            ->where('kwa.id_admin_kabupaten', $idAdminKabupaten)
            ->orderBy('mkdp.tanggal_mulai', 'DESC')
            ->orderBy('p.created_at', 'DESC');

        if ($idKegiatanWilayah) {
            $builder->where('p.id_kegiatan_wilayah', $idKegiatanWilayah);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Get PML by Kabupaten dan Admin dengan Pagination
     * NEW METHOD - untuk pagination
     */
    public function getPMLByKabupatenAndAdminPaginated($idKabupaten, $idAdminKabupaten, $idKegiatanWilayah = null, $perPage = 10)
    {
        $builder = $this->select('pml.id_pml, pml.target, pml.sobat_id,
                u.nama_user as nama_pml, u.email,
                kw.id_kegiatan_wilayah,
                mkd.nama_kegiatan_detail,
                mkdp.nama_kegiatan_detail_proses,
                (SELECT COUNT(*) FROM pcl WHERE pcl.id_pml = pml.id_pml) as jumlah_pcl,
                (SELECT COALESCE(SUM(target), 0) FROM pcl WHERE pcl.id_pml = pml.id_pml) as total_target_pcl')
            ->join('sipantau_user u', 'pml.sobat_id = u.sobat_id')
            ->join('kegiatan_wilayah kw', 'pml.id_kegiatan_wilayah = kw.id_kegiatan_wilayah')
            ->join('kegiatan_wilayah_admin kwa', 'kw.id_kegiatan_wilayah = kwa.id_kegiatan_wilayah')
            ->join('master_kegiatan_detail_proses mkdp', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses')
            ->join('master_kegiatan_detail mkd', 'mkdp.id_kegiatan_detail = mkd.id_kegiatan_detail')
            ->where('kw.id_kabupaten', $idKabupaten)
            ->where('kwa.id_admin_kabupaten', $idAdminKabupaten)
            ->orderBy('mkdp.tanggal_mulai', 'DESC')
            ->orderBy('pml.created_at', 'DESC');

        if ($idKegiatanWilayah) {
            $builder->where('kw.id_kegiatan_wilayah', $idKegiatanWilayah);
        }

        return $builder->paginate($perPage, 'pml_list');
    }

    /**
     * Get PML by ID dengan detail
     * ORIGINAL METHOD - TIDAK DIUBAH
     */
    public function getPMLWithDetails($idPML)
    {
        return $this->db->table('pml p')
            ->select('p.*, u.nama_user as nama_pml, u.email, u.hp,
                     kw.id_kegiatan_wilayah, kw.target_wilayah, kw.id_kabupaten,
                     mkdp.nama_kegiatan_detail_proses, mkdp.tanggal_mulai, mkdp.tanggal_selesai,
                     mk.nama_kegiatan,
                     kab.nama_kabupaten')
            ->join('sipantau_user u', 'p.sobat_id = u.sobat_id')
            ->join('kegiatan_wilayah kw', 'p.id_kegiatan_wilayah = kw.id_kegiatan_wilayah')
            ->join('master_kegiatan_detail_proses mkdp', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses')
            ->join('master_kegiatan_detail mkd', 'mkdp.id_kegiatan_detail = mkd.id_kegiatan_detail')
            ->join('master_kegiatan mk', 'mkd.id_kegiatan = mk.id_kegiatan')
            ->join('master_kabupaten kab', 'kw.id_kabupaten = kab.id_kabupaten')
            ->where('p.id_pml', $idPML)
            ->get()
            ->getRowArray();
    }

    /**
     * Get daftar user yang bisa dijadikan PML (belum di-assign)
     * ORIGINAL METHOD - TIDAK DIUBAH
     */
    public function getAvailablePML($idKabupaten, $idKegiatanWilayah)
    {
        return $this->db->table('sipantau_user u')
            ->select('u.sobat_id, u.nama_user, u.email, u.hp')
            ->where('u.id_kabupaten', $idKabupaten)
            ->where('u.is_active', 1)
            ->where('u.sobat_id NOT IN (
                SELECT sobat_id FROM pml 
                WHERE id_kegiatan_wilayah = ' . $idKegiatanWilayah . '
            )')
            ->where('u.sobat_id NOT IN (
                SELECT sobat_id FROM admin_survei_kabupaten
            )')
            ->orderBy('u.nama_user', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Get Available PML untuk kegiatan tertentu
     * Exclude: admin yang login dan user yang sudah terlibat (PML/PCL/Admin) di kegiatan ini
     * NEW METHOD - dengan filter yang lebih ketat
     */
    public function getAvailablePMLForKegiatan($idKabupaten, $idKegiatanWilayah, $excludeSobatId = null)
    {
        $builder = $this->db->table('sipantau_user u')
            ->select('u.sobat_id, u.nama_user, u.email, u.hp')
            ->where('u.id_kabupaten', $idKabupaten)
            ->where('u.is_active', 1)
            ->orderBy('u.nama_user', 'ASC');

        // Exclude admin yang sedang login
        if ($excludeSobatId) {
            $builder->where('u.sobat_id !=', $excludeSobatId);
        }

        // Exclude user yang sudah menjadi PML di kegiatan ini
        $builder->whereNotIn('u.sobat_id', function ($subquery) use ($idKegiatanWilayah) {
            return $subquery->select('sobat_id')
                ->from('pml')
                ->where('id_kegiatan_wilayah', $idKegiatanWilayah);
        });

        // Exclude user yang sudah menjadi PCL di kegiatan ini
        $builder->whereNotIn('u.sobat_id', function ($subquery) use ($idKegiatanWilayah) {
            return $subquery->select('pcl.sobat_id')
                ->from('pcl')
                ->join('pml', 'pml.id_pml = pcl.id_pml')
                ->where('pml.id_kegiatan_wilayah', $idKegiatanWilayah);
        });

        // Exclude admin kabupaten yang meng-handle kegiatan ini
        $builder->whereNotIn('u.sobat_id', function ($subquery) use ($idKegiatanWilayah) {
            return $subquery->select('ask.sobat_id')
                ->from('admin_survei_kabupaten ask')
                ->join('kegiatan_wilayah_admin kwa', 'kwa.id_admin_kabupaten = ask.id_admin_kabupaten')
                ->where('kwa.id_kegiatan_wilayah', $idKegiatanWilayah);
        });

        return $builder->get()->getResultArray();
    }

    /**
     * Delete PML beserta PCL-nya
     * ORIGINAL METHOD - TIDAK DIUBAH
     */
    public function deletePMLWithPCL($idPML)
    {
        $this->db->transStart();

        // 1. Ambil semua PCL di bawah PML ini
        $pcls = $this->db->table('pcl')
            ->where('id_pml', $idPML)
            ->get()
            ->getResultArray();

        $pclModel = new \App\Models\PCLModel();
        $kurvaModel = new \App\Models\KurvaPetugasModel();

        // 2. Hapus semua data terkait untuk setiap PCL
        foreach ($pcls as $pcl) {
            $idPCL = $pcl['id_pcl'];
            
            // Hapus Kurva
            $kurvaModel->deleteByPCL($idPCL);
            
            // Hapus Progress
            $this->db->table('pantau_progress')
                ->where('id_pcl', $idPCL)
                ->delete();

            // Hapus Transaksi
            $this->db->table('sipantau_transaksi')
                ->where('id_pcl', $idPCL)
                ->delete();

            // Hapus Kepatuhan Summary
            $this->db->table('kepatuhan_summary')
                ->where('id_pcl', $idPCL)
                ->delete();

            // Hapus PCL record
            $this->db->table('pcl')
                ->where('id_pcl', $idPCL)
                ->delete();
        }

        // 3. Hapus PML
        $this->delete($idPML);

        $this->db->transComplete();

        return $this->db->transStatus();
    }

    /**
     * Cek apakah PML memiliki data transaksi (progress/aktivitas)
     * Digunakan untuk mencegah reassignment jika sudah ada data
     *
     * @param int $idPML
     * @return bool
     */
    public function hasTransactionData($idPML)
    {
        // Cek pantau_progress melalui PCL
        $progressCount = $this->db->table('pantau_progress pp')
            ->join('pcl', 'pp.id_pcl = pcl.id_pcl')
            ->where('pcl.id_pml', $idPML)
            ->countAllResults();

        if ($progressCount > 0) {
            return true;
        }

        // Cek sipantau_transaksi melalui PCL
        $transaksiCount = $this->db->table('sipantau_transaksi st')
            ->join('pcl', 'st.id_pcl = pcl.id_pcl')
            ->where('pcl.id_pml', $idPML)
            ->countAllResults();

        return $transaksiCount > 0;
    }

    /**
     * Get ringkasan data transaksi PML untuk ditampilkan di pesan error
     *
     * @param int $idPML
     * @return array Detail ringkasan transaksi
     */
    public function getTransactionSummary($idPML)
    {
        // Hitung total PCL
        $totalPCL = $this->db->table('pcl')
            ->where('id_pml', $idPML)
            ->countAllResults();

        // Hitung total progress records
        $totalProgress = $this->db->table('pantau_progress pp')
            ->join('pcl', 'pp.id_pcl = pcl.id_pcl')
            ->where('pcl.id_pml', $idPML)
            ->countAllResults();

        // Hitung total transaksi records
        $totalTransaksi = $this->db->table('sipantau_transaksi st')
            ->join('pcl', 'st.id_pcl = pcl.id_pcl')
            ->where('pcl.id_pml', $idPML)
            ->countAllResults();

        // Hitung total realisasi kumulatif
        $realisasi = $this->db->table('pantau_progress pp')
            ->selectMax('pp.jumlah_realisasi_kumulatif', 'max_realisasi')
            ->join('pcl', 'pp.id_pcl = pcl.id_pcl')
            ->where('pcl.id_pml', $idPML)
            ->groupBy('pp.id_pcl')
            ->get()
            ->getResultArray();

        $totalRealisasi = 0;
        foreach ($realisasi as $row) {
            $totalRealisasi += (int) $row['max_realisasi'];
        }

        // Get daftar PCL yang memiliki data
        $pclWithData = $this->db->table('pcl p')
            ->select('p.id_pcl, u.nama_user,
                (SELECT COUNT(*) FROM pantau_progress WHERE id_pcl = p.id_pcl) as jumlah_progress,
                (SELECT COUNT(*) FROM sipantau_transaksi WHERE id_pcl = p.id_pcl) as jumlah_transaksi,
                (SELECT COALESCE(MAX(jumlah_realisasi_kumulatif), 0) FROM pantau_progress WHERE id_pcl = p.id_pcl) as realisasi_kumulatif')
            ->join('sipantau_user u', 'p.sobat_id = u.sobat_id')
            ->where('p.id_pml', $idPML)
            ->having('jumlah_progress > 0 OR jumlah_transaksi > 0')
            ->get()
            ->getResultArray();

        return [
            'has_data'         => ($totalProgress > 0 || $totalTransaksi > 0),
            'total_pcl'        => $totalPCL,
            'total_progress'   => $totalProgress,
            'total_transaksi'  => $totalTransaksi,
            'total_realisasi'  => $totalRealisasi,
            'pcl_with_data'    => $pclWithData,
        ];
    }
}