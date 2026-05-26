<?php

namespace App\Models;

use CodeIgniter\Model;

class AssignmentSubSLSModel extends Model
{
    protected $table = 'assignment_sub_sls';
    protected $primaryKey = 'id_assignment_sub_sls';
    protected $allowedFields = ['id_kegiatan_wilayah', 'id_sub_sls', 'sobat_id', 'created_at', 'updated_at'];
    protected $useTimestamps = true;

    public function getAssignmentsWithDetails($idKabupaten, $idKegiatanWilayah = null)
    {
        $builder = $this->db->table('assignment_sub_sls ass')
            ->select('ass.*, u.nama_user as nama_petugas, kw.target_wilayah, 
                      mss.nama_sls, mss.nama_desa, 
                      md.nama_desa as real_nama_desa, mk.nama_kecamatan, 
                      mkd.nama_kegiatan_detail, mkdp.nama_kegiatan_detail_proses')
            ->join('sipantau_user u', 'ass.sobat_id = u.sobat_id', 'left')
            ->join('kegiatan_wilayah kw', 'ass.id_kegiatan_wilayah = kw.id_kegiatan_wilayah', 'left')
            ->join('master_kegiatan_detail_proses mkdp', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses', 'left')
            ->join('master_kegiatan_detail mkd', 'mkdp.id_kegiatan_detail = mkd.id_kegiatan_detail', 'left')
            ->join('master_sub_sls mss', 'ass.id_sub_sls = mss.id_sub_sls', 'left')
            // Join langsung ke Kecamatan menggunakan 7 digit pertama dari id_sub_sls (Contoh: 1401010)
            ->join('master_kecamatan mk', 'SUBSTR(ass.id_sub_sls, 1, 7) = mk.id_kecamatan', 'left')
            // Join langsung ke Desa menggunakan 10 digit pertama dari id_sub_sls (Contoh: 1401010001)
            ->join('master_desa md', 'SUBSTR(ass.id_sub_sls, 1, 10) = md.id_desa', 'left')
            ->where('kw.id_kabupaten', $idKabupaten);

        if ($idKegiatanWilayah) {
            $builder->where('ass.id_kegiatan_wilayah', $idKegiatanWilayah);
        }

        return $builder->orderBy('ass.created_at', 'DESC')
            ->get()
            ->getResultArray();
    }
}
