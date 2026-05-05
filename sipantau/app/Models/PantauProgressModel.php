<?php

namespace App\Models;

use CodeIgniter\Model;

class PantauProgressModel extends Model
{
    protected $table            = 'pantau_progress';
    protected $primaryKey       = 'id_pantau_progess';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_pantau_progess',
        'id_pcl',
        'jumlah_realisasi_absolut',
        'jumlah_realisasi_kumulatif',
        'catatan_aktivitas',
        'created_at',
        'updated_at'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Get total realisasi kumulatif by PCL
     */
    public function getRealisasiByPCL($idPCL)
    {
        $result = $this->select('COALESCE(MAX(jumlah_realisasi_kumulatif), 0) as total_realisasi')
            ->where('id_pcl', $idPCL)
            ->first();

        return (int)($result['total_realisasi'] ?? 0);
    }

    /**
     * Get total realisasi kumulatif by PML
     */
    public function getRealisasiByPML($idPML)
    {
        $result = $this->db->query("
            SELECT COALESCE(SUM(pp.jumlah_realisasi_kumulatif), 0) as total_realisasi
            FROM pantau_progress pp
            JOIN pcl ON pp.id_pcl = pcl.id_pcl
            WHERE pcl.id_pml = ?
            GROUP BY pp.id_pcl
        ", [$idPML])->getResultArray();

        $total = 0;
        foreach ($result as $row) {
            $total += (int)$row['total_realisasi'];
        }

        return $total;
    }

    /**
     * Get total realisasi by kegiatan wilayah
     */
    public function getRealisasiByKegiatanWilayah($idKegiatanWilayah)
    {
        $result = $this->db->query("
            SELECT COALESCE(SUM(pp.jumlah_realisasi_kumulatif), 0) as total_realisasi
            FROM pantau_progress pp
            JOIN pcl ON pp.id_pcl = pcl.id_pcl
            JOIN pml ON pcl.id_pml = pml.id_pml
            WHERE pml.id_kegiatan_wilayah = ?
            GROUP BY pp.id_pcl
        ", [$idKegiatanWilayah])->getResultArray();

        $total = 0;
        foreach ($result as $row) {
            $total += (int)$row['total_realisasi'];
        }

        return $total;
    }

    /**
     * Get total realisasi by kegiatan detail proses
     */
    public function getRealisasiByProses($idProses)
    {
        $result = $this->db->query("
            SELECT COALESCE(SUM(pp.jumlah_realisasi_kumulatif), 0) as total_realisasi
            FROM pantau_progress pp
            JOIN pcl ON pp.id_pcl = pcl.id_pcl
            JOIN pml ON pcl.id_pml = pml.id_pml
            JOIN kegiatan_wilayah kw ON pml.id_kegiatan_wilayah = kw.id_kegiatan_wilayah
            WHERE kw.id_kegiatan_detail_proses = ?
            GROUP BY pp.id_pcl
        ", [$idProses])->getResultArray();

        $total = 0;
        foreach ($result as $row) {
            $total += (int)$row['total_realisasi'];
        }

        return $total;
    }

    /**
     * Get realisasi harian per tanggal by kegiatan detail proses
     */
    public function getRealisasiHarianByProses($idProses)
    {
        return $this->db->query("
            SELECT 
                DATE(pp.created_at) as tanggal,
                SUM(pp.jumlah_realisasi_kumulatif) as total_realisasi
            FROM pantau_progress pp
            JOIN pcl ON pp.id_pcl = pcl.id_pcl
            JOIN pml ON pcl.id_pml = pml.id_pml
            JOIN kegiatan_wilayah kw ON pml.id_kegiatan_wilayah = kw.id_kegiatan_wilayah
            WHERE kw.id_kegiatan_detail_proses = ?
            GROUP BY DATE(pp.created_at)
            ORDER BY DATE(pp.created_at) ASC
        ", [$idProses])->getResultArray();
    }

    /**
     * Get realisasi harian per tanggal by kegiatan wilayah
     */
    public function getRealisasiHarianByWilayah($idKegiatanWilayah)
    {
        return $this->db->query("
            SELECT 
                DATE(pp.created_at) as tanggal,
                SUM(pp.jumlah_realisasi_kumulatif) as total_realisasi
            FROM pantau_progress pp
            JOIN pcl ON pp.id_pcl = pcl.id_pcl
            JOIN pml ON pcl.id_pml = pml.id_pml
            WHERE pml.id_kegiatan_wilayah = ?
            GROUP BY DATE(pp.created_at)
            ORDER BY DATE(pp.created_at) ASC
        ", [$idKegiatanWilayah])->getResultArray();
    }

    /**
     * Get progress statistics by PCL
     */
    public function getProgressStatsByPCL($idPCL)
    {
        return $this->db->query("
            SELECT 
                pp.*,
                u.nama_user,
                pcl.target,
                ROUND((pp.jumlah_realisasi_kumulatif / pcl.target) * 100, 2) as persentase
            FROM pantau_progress pp
            JOIN pcl ON pp.id_pcl = pcl.id_pcl
            JOIN sipantau_user u ON pcl.sobat_id = u.sobat_id
            WHERE pp.id_pcl = ?
            ORDER BY pp.created_at DESC
        ", [$idPCL])->getResultArray();
    }

    /**
     * Get latest progress by PCL
     */
    public function getLatestProgressByPCL($idPCL)
    {
        return $this->where('id_pcl', $idPCL)
            ->orderBy('created_at', 'DESC')
            ->first();
    }

    /**
     * Check if PCL has reported today
     */
    public function hasReportedToday($idPCL)
    {
        $today = date('Y-m-d');
        
        $result = $this->where('id_pcl', $idPCL)
            ->where('DATE(created_at)', $today)
            ->countAllResults();

        return $result > 0;
    }

    /**
     * Get daily report count by date range
     */
    public function getDailyReportCount($startDate, $endDate, $idProses = null)
    {
        $builder = $this->db->table('pantau_progress pp')
            ->select('DATE(pp.created_at) as tanggal, COUNT(*) as jumlah_laporan')
            ->where('DATE(pp.created_at) >=', $startDate)
            ->where('DATE(pp.created_at) <=', $endDate);

        if ($idProses) {
            $builder->join('pcl', 'pp.id_pcl = pcl.id_pcl')
                ->join('pml', 'pcl.id_pml = pml.id_pml')
                ->join('kegiatan_wilayah kw', 'pml.id_kegiatan_wilayah = kw.id_kegiatan_wilayah')
                ->where('kw.id_kegiatan_detail_proses', $idProses);
        }

        $builder->groupBy('DATE(pp.created_at)')
            ->orderBy('DATE(pp.created_at)', 'ASC');

        return $builder->get()->getResultArray();
    }
}