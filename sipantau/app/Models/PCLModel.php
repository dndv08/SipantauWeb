<?php

namespace App\Models;

use CodeIgniter\Model;

class PCLModel extends Model
{
    protected $table = 'pcl';
    protected $primaryKey = 'id_pcl';
    protected $allowedFields = [
        'sobat_id',
        'id_pml',
        'target',
        'status_approval',
        'tanggal_approval',
        'feedback_admin',
        'rating',  
        'created_at',
        'updated_at'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Get PCL by PML
     */
    public function getPCLByPML($idPML)
    {
        return $this->db->table('pcl p')
            ->select('p.*, u.nama_user as nama_pcl, u.email, u.hp')
            ->join('sipantau_user u', 'p.sobat_id = u.sobat_id')
            ->where('p.id_pml', $idPML)
            ->orderBy('u.nama_user', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Get PCL dengan detail lengkap
     */
    public function getPCLWithDetails($idPCL)
    {
        return $this->db->table('pcl p')
            ->select('p.*, 
                     u.nama_user AS nama_pcl, u.email, u.hp, 
                     kab.nama_kabupaten, 
                     pml.target AS target_pml, 
                     u_pml.nama_user AS nama_pml, 
                     kw.id_kegiatan_wilayah, kw.id_kabupaten, 
                     mkdp.nama_kegiatan_detail_proses, 
                     mkdp.tanggal_mulai, mkdp.tanggal_selesai, 
                     mkdp.tanggal_selesai_target, mkdp.persentase_target_awal, 
                     mk.nama_kegiatan')
            ->join('sipantau_user u', 'p.sobat_id = u.sobat_id')
            ->join('master_kabupaten kab', 'u.id_kabupaten = kab.id_kabupaten', 'left')
            ->join('pml', 'p.id_pml = pml.id_pml')
            ->join('sipantau_user u_pml', 'pml.sobat_id = u_pml.sobat_id')
            ->join('kegiatan_wilayah kw', 'pml.id_kegiatan_wilayah = kw.id_kegiatan_wilayah')
            ->join('master_kegiatan_detail_proses mkdp', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses')
            ->join('master_kegiatan_detail mkd', 'mkdp.id_kegiatan_detail = mkd.id_kegiatan_detail')
            ->join('master_kegiatan mk', 'mkd.id_kegiatan = mk.id_kegiatan')
            ->where('p.id_pcl', $idPCL)
            ->get()
            ->getRowArray();
    }

    /**
     * Get user yang bisa dijadikan PCL (belum di-assign untuk PML tertentu)
     */
    public function getAvailablePCL()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'error' => 'Invalid request']);
        }

        $sobatId = session()->get('sobat_id');
        $admin = $this->adminKabModel->db->table('admin_survei_kabupaten ask')
            ->select('ask.*, u.id_kabupaten')
            ->join('sipantau_user u', 'ask.sobat_id = u.sobat_id')
            ->where('ask.sobat_id', $sobatId)
            ->get()
            ->getRowArray();

        if (!$admin) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Unauthorized',
                'csrf_hash' => csrf_hash()
            ]);
        }

        $idPML = $this->request->getPost('id_pml');
        $pmlSobatId = $this->request->getPost('pml_sobat_id'); // PML yang dipilih di form
        $idKegiatanWilayah = $this->request->getPost('id_kegiatan_wilayah'); // WAJIB ADA

        // VALIDASI: id_kegiatan_wilayah harus ada
        if (!$idKegiatanWilayah) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'ID Kegiatan Wilayah diperlukan',
                'csrf_hash' => csrf_hash()
            ]);
        }

        // Cek akses admin ke kegiatan ini
        $isAssigned = $this->kegiatanWilayahAdminModel->isAssigned($admin['id_admin_kabupaten'], $idKegiatanWilayah);
        if (!$isAssigned) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Anda tidak memiliki akses ke kegiatan ini',
                'csrf_hash' => csrf_hash()
            ]);
        }

        // Get available PCL dengan filter lengkap
        $users = $this->pclModel->getAvailablePCLForKegiatan(
            $admin['id_kabupaten'],
            $idKegiatanWilayah,
            $idPML,
            $pmlSobatId // Exclude PML yang baru dipilih di form
        );

        return $this->response->setJSON([
            'success' => true,
            'data' => $users,
            'csrf_hash' => csrf_hash()
        ]);
    }

    /**
     * Get Available PCL untuk kegiatan tertentu
     * Exclude: PML yang dipilih, admin yang terlibat di kegiatan ini, dan user yang sudah terlibat (PML/PCL) di kegiatan ini
     * FIXED METHOD - sekarang sudah filter admin yang di-assign ke kegiatan
     */
    public function getAvailablePCLForKegiatan($idKabupaten, $idKegiatanWilayah, $idPML = null, $pmlSobatId = null)
    {
        $builder = $this->db->table('sipantau_user u')
            ->select('u.sobat_id, u.nama_user, u.email, u.hp')
            ->where('u.id_kabupaten', $idKabupaten)
            ->where('u.is_active', 1)
            ->orderBy('u.nama_user', 'ASC');

        // Exclude PML yang dipilih
        if ($pmlSobatId) {
            $builder->where('u.sobat_id !=', $pmlSobatId);
        }

        // Exclude user yang sudah menjadi PML di kegiatan ini
        $builder->whereNotIn('u.sobat_id', function ($subquery) use ($idKegiatanWilayah) {
            return $subquery->select('sobat_id')
                ->from('pml')
                ->where('id_kegiatan_wilayah', $idKegiatanWilayah);
        });

        // Exclude user yang sudah menjadi PCL di kegiatan ini (kecuali untuk PML yang sedang diedit)
        if ($idPML) {
            $builder->whereNotIn('u.sobat_id', function ($subquery) use ($idKegiatanWilayah, $idPML) {
                return $subquery->select('pcl.sobat_id')
                    ->from('pcl')
                    ->join('pml', 'pml.id_pml = pcl.id_pml')
                    ->where('pml.id_kegiatan_wilayah', $idKegiatanWilayah)
                    ->where('pcl.id_pml !=', $idPML);
            });
        } else {
            $builder->whereNotIn('u.sobat_id', function ($subquery) use ($idKegiatanWilayah) {
                return $subquery->select('pcl.sobat_id')
                    ->from('pcl')
                    ->join('pml', 'pml.id_pml = pcl.id_pml')
                    ->where('pml.id_kegiatan_wilayah', $idKegiatanWilayah);
            });
        }

        // **PERBAIKAN UTAMA**: Exclude admin kabupaten yang meng-handle kegiatan ini
        $builder->whereNotIn('u.sobat_id', function ($subquery) use ($idKegiatanWilayah) {
            return $subquery->select('ask.sobat_id')
                ->from('admin_survei_kabupaten ask')
                ->join('kegiatan_wilayah_admin kwa', 'kwa.id_admin_kabupaten = ask.id_admin_kabupaten')
                ->where('kwa.id_kegiatan_wilayah', $idKegiatanWilayah);
        });

        return $builder->get()->getResultArray();
    }

    /**
     * Get progress PCL dengan data pantau progress
     */
    public function getPCLProgress($idPCL)
    {
        return $this->db->table('pcl p')
            ->select('p.*, u.nama_user as nama_pcl,
                     (SELECT MAX(jumlah_realisasi_kumulatif) 
                      FROM pantau_progress 
                      WHERE id_pcl = p.id_pcl) as realisasi_terakhir')
            ->join('sipantau_user u', 'p.sobat_id = u.sobat_id')
            ->where('p.id_pcl', $idPCL)
            ->get()
            ->getRowArray();
    }

    /**
     * Get all PCL by kabupaten untuk monitoring
     */
    public function getAllPCLByKabupaten($idKabupaten)
    {
        return $this->db->table('pcl p')
            ->select('p.*, u.nama_user as nama_pcl,
                     u_pml.nama_user as nama_pml,
                     mkdp.nama_kegiatan_detail_proses,
                     (SELECT MAX(jumlah_realisasi_kumulatif) 
                      FROM pantau_progress 
                      WHERE id_pcl = p.id_pcl) as realisasi_terakhir')
            ->join('sipantau_user u', 'p.sobat_id = u.sobat_id')
            ->join('pml', 'p.id_pml = pml.id_pml')
            ->join('sipantau_user u_pml', 'pml.sobat_id = u_pml.sobat_id')
            ->join('kegiatan_wilayah kw', 'pml.id_kegiatan_wilayah = kw.id_kegiatan_wilayah')
            ->join('master_kegiatan_detail_proses mkdp', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses')
            ->where('kw.id_kabupaten', $idKabupaten)
            ->orderBy('p.created_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Get total target PCL yang sudah di-assign ke PML tertentu
     */
    public function getTotalTargetByPML($idPML, $excludePCLId = null)
    {
        $builder = $this->db->table('pcl')
            ->selectSum('target', 'total_target')
            ->where('id_pml', $idPML);

        if ($excludePCLId) {
            $builder->where('id_pcl !=', $excludePCLId);
        }

        $result = $builder->get()->getRowArray();
        return (int) ($result['total_target'] ?? 0);
    }

    /**
     * Get sisa target PML yang belum di-assign ke PCL
     */
    public function getSisaTargetPML($idPML, $excludePCLId = null)
    {
        $pml = $this->db->table('pml')
            ->select('target')
            ->where('id_pml', $idPML)
            ->get()
            ->getRowArray();

        if (!$pml) {
            return 0;
        }

        $targetPML = (int) $pml['target'];
        $totalTargetPCL = $this->getTotalTargetByPML($idPML, $excludePCLId);

        return max(0, $targetPML - $totalTargetPCL);
    }

    /**
     * Validasi apakah target PCL valid
     */
    public function validateTargetPCL($idPML, $targetPCL, $excludePCLId = null)
    {
        $sisaTarget = $this->getSisaTargetPML($idPML, $excludePCLId);

        if ($targetPCL > $sisaTarget) {
            return [
                'valid' => false,
                'message' => "Target PCL ($targetPCL) melebihi sisa target PML yang tersedia ($sisaTarget)",
                'sisa_target' => $sisaTarget
            ];
        }

        return [
            'valid' => true,
            'message' => 'Target valid',
            'sisa_target' => $sisaTarget
        ];
    }

    /**
     * Get detail kegiatan untuk generate Kurva S
     */
    public function getKegiatanDetailForKurva($idPML)
    {
        return $this->db->table('pml')
            ->select('mkdp.tanggal_mulai, mkdp.tanggal_selesai, 
                     mkdp.tanggal_selesai_target, mkdp.persentase_target_awal,
                     kw.id_kegiatan_wilayah')
            ->join('kegiatan_wilayah kw', 'pml.id_kegiatan_wilayah = kw.id_kegiatan_wilayah')
            ->join('master_kegiatan_detail_proses mkdp', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses')
            ->where('pml.id_pml', $idPML)
            ->get()
            ->getRowArray();
    }

    /**
     * Delete PCL beserta Kurva Petugas-nya
     */
    public function deletePCLWithKurva($idPCL)
    {
        $kurvaModel = new \App\Models\KurvaPetugasModel();

        $this->db->transStart();

        try {
            $kurvaModel->deleteByPCL($idPCL);
            
            // 1. Hapus Pantau Progress
            $this->db->table('pantau_progress')
                ->where('id_pcl', $idPCL)
                ->delete();

            // 2. Hapus Sipantau Transaksi
            $this->db->table('sipantau_transaksi')
                ->where('id_pcl', $idPCL)
                ->delete();

            // 3. Hapus Kepatuhan Summary
            $this->db->table('kepatuhan_summary')
                ->where('id_pcl', $idPCL)
                ->delete();

            // 4. Hapus PCL
            $this->delete($idPCL);

            $this->db->transComplete();

            return $this->db->transStatus();
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Error deleting PCL with Kurva: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update PCL dan regenerate Kurva S jika target berubah
     */
    public function updatePCLWithKurva($idPCL, $data)
    {
        $existingPCL = $this->find($idPCL);

        if (!$existingPCL) {
            return false;
        }

        $this->db->transStart();

        try {
            $this->update($idPCL, $data);

            if (isset($data['target']) && $data['target'] != $existingPCL['target']) {
                $kurvaModel = new \App\Models\KurvaPetugasModel();
                $kurvaModel->deleteByPCL($idPCL);

                log_message('info', "Target PCL berubah dari {$existingPCL['target']} ke {$data['target']}, regenerate Kurva S diperlukan");
            }

            $this->db->transComplete();

            return $this->db->transStatus();
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Error updating PCL with Kurva: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Batch insert PCL dengan validasi total target
     */
    public function batchInsertPCL($idPML, $pclDataArray)
    {
        $totalTarget = 0;
        foreach ($pclDataArray as $pcl) {
            $totalTarget += (int) $pcl['target'];
        }

        $validation = $this->validateTargetPCL($idPML, $totalTarget);

        if (!$validation['valid']) {
            return [
                'success' => false,
                'message' => $validation['message'],
                'inserted_ids' => []
            ];
        }

        $this->db->transStart();

        try {
            $insertedIds = [];

            foreach ($pclDataArray as $pclData) {
                $pclData['id_pml'] = $idPML;
                $pclData['status_approval'] = 0;
                $pclData['tanggal_approval'] = null;

                $insertedId = $this->insert($pclData);
                $insertedIds[] = $insertedId;
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                return [
                    'success' => false,
                    'message' => 'Gagal menyimpan data PCL',
                    'inserted_ids' => []
                ];
            }

            return [
                'success' => true,
                'message' => 'Berhasil menyimpan ' . count($insertedIds) . ' PCL',
                'inserted_ids' => $insertedIds
            ];
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Error batch insert PCL: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'inserted_ids' => []
            ];
        }
    }


    /**
     * Get statistik target vs realisasi PCL
     */
    public function getTargetStatistics($idPCL)
    {
        $pcl = $this->find($idPCL);

        if (!$pcl) {
            return null;
        }

        $realisasi = $this->db->table('pantau_progress')
            ->select('MAX(jumlah_realisasi_kumulatif) as realisasi_kumulatif')
            ->where('id_pcl', $idPCL)
            ->get()
            ->getRowArray();

        $realisasiKumulatif = (int) ($realisasi['realisasi_kumulatif'] ?? 0);
        $target = (int) $pcl['target'];
        $persentase = $target > 0 ? round(($realisasiKumulatif / $target) * 100, 2) : 0;

        return [
            'id_pcl' => $idPCL,
            'target' => $target,
            'realisasi' => $realisasiKumulatif,
            'sisa' => max(0, $target - $realisasiKumulatif),
            'persentase' => $persentase,
            'status' => $persentase >= 100 ? 'Tercapai' : ($persentase >= 80 ? 'Hampir Tercapai' : 'Dalam Progress')
        ];
    }
}
