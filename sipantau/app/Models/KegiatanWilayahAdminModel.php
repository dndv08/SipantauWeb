<?php

namespace App\Models;

use CodeIgniter\Model;

class KegiatanWilayahAdminModel extends Model
{
    protected $table = 'kegiatan_wilayah_admin';
    protected $primaryKey = 'id';
    protected $allowedFields = ['id_admin_kabupaten', 'id_kegiatan_wilayah'];
    protected $useTimestamps = false;

    /**
     * Get assigned kegiatan wilayah untuk admin tertentu
     */
    public function getAssignedKegiatanByAdmin($idAdminKabupaten)
    {
        return $this->where('id_admin_kabupaten', $idAdminKabupaten)
            ->findAll();
    }

    /**
     * Check apakah kegiatan sudah di-assign ke admin
     */
    public function isAssigned($idAdminKabupaten, $idKegiatanWilayah)
    {
        return $this->where([
            'id_admin_kabupaten' => $idAdminKabupaten,
            'id_kegiatan_wilayah' => $idKegiatanWilayah
        ])->first() !== null;
    }

    /**
     * Assign admin ke kegiatan wilayah
     */
    public function assignAdmin($idAdminKabupaten, $idKegiatanWilayah)
    {
        // Check jika sudah ada
        if ($this->isAssigned($idAdminKabupaten, $idKegiatanWilayah)) {
            return false;
        }

        return $this->insert([
            'id_admin_kabupaten' => $idAdminKabupaten,
            'id_kegiatan_wilayah' => $idKegiatanWilayah
        ]);
    }

    /**
     * Unassign admin dari kegiatan wilayah
     */
    public function unassignAdmin($idAdminKabupaten, $idKegiatanWilayah)
    {
        return $this->where([
            'id_admin_kabupaten' => $idAdminKabupaten,
            'id_kegiatan_wilayah' => $idKegiatanWilayah
        ])->delete();
    }

    /**
     * Get jumlah kegiatan yang di-assign ke admin
     */
    public function countKegiatanByAdmin($idAdminKabupaten)
    {
        return $this->where('id_admin_kabupaten', $idAdminKabupaten)
            ->countAllResults();
    }

    /**
     * Delete all assignments untuk admin tertentu
     */
    public function deleteByAdmin($idAdminKabupaten)
    {
        return $this->where('id_admin_kabupaten', $idAdminKabupaten)->delete();
    }

    /**
     * Bulk assign - hapus semua lalu insert baru
     */
    public function bulkAssign($idAdminKabupaten, array $kegiatanWilayahIds)
    {
        $this->db->transStart();

        // Hapus semua assignment lama
        $this->deleteByAdmin($idAdminKabupaten);

        // Insert assignment baru
        foreach ($kegiatanWilayahIds as $idKegiatanWilayah) {
            $this->insert([
                'id_admin_kabupaten' => $idAdminKabupaten,
                'id_kegiatan_wilayah' => $idKegiatanWilayah
            ]);
        }

        $this->db->transComplete();

        return $this->db->transStatus();
    }
}