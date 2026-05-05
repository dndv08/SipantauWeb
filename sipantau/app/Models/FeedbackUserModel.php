<?php

namespace App\Models;

use CodeIgniter\Model;

class FeedbackUserModel extends Model
{
    protected $table = 'sipantau_feedback_user';
    protected $primaryKey = 'id_feedback';
    protected $allowedFields = [
        'sobat_id',
        'feedback',
        'rating',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Get feedback user dengan detail user
     */
    public function getFeedbackWithDetails($filters = [])
    {
        $builder = $this->db->table('sipantau_feedback_user f')
            ->select('f.*, 
                     u.nama_user,
                     u.email,
                     u.hp,
                     k.nama_kabupaten,
                     u.role')
            ->join('sipantau_user u', 'f.sobat_id = u.sobat_id', 'left')
            ->join('master_kabupaten k', 'u.id_kabupaten = k.id_kabupaten', 'left')
            ->orderBy('f.created_at', 'DESC');

        // Apply filters
        if (!empty($filters['rating'])) {
            $builder->where('f.rating', $filters['rating']);
        }

        if (!empty($filters['id_kabupaten'])) {
            $builder->where('u.id_kabupaten', $filters['id_kabupaten']);
        }

        if (!empty($filters['date_from'])) {
            $builder->where('DATE(f.created_at) >=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $builder->where('DATE(f.created_at) <=', $filters['date_to']);
        }

        if (!empty($filters['search'])) {
            $builder->groupStart()
                ->like('f.feedback', $filters['search'])
                ->orLike('u.nama_user', $filters['search'])
                ->orLike('u.sobat_id', $filters['search'])
                ->groupEnd();
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Get feedback with details WITH PAGINATION
     */
    /**
     * Get feedback with details WITH PAGINATION
     */
    public function getFeedbackWithDetailsPaginated($filters = [], $perPage = 10)
    {
        // PERBAIKAN: Ganti 'feedback_user' menjadi 'sipantau_feedback_user' dan alias 'f' bukan 'fu'
        $builder = $this->db->table('sipantau_feedback_user f')
            ->select('f.*, 
                 u.nama_user, 
                 u.email, 
                 u.sobat_id, 
                 u.hp,
                 k.nama_kabupaten,
                 u.role')
            ->join('sipantau_user u', 'f.sobat_id = u.sobat_id', 'left')
            ->join('master_kabupaten k', 'u.id_kabupaten = k.id_kabupaten', 'left')
            ->orderBy('f.created_at', 'DESC');

        // Apply filters - UBAH alias dari 'fu' ke 'f'
        if (!empty($filters['rating'])) {
            $builder->where('f.rating', $filters['rating']);
        }

        if (!empty($filters['id_kabupaten'])) {
            $builder->where('u.id_kabupaten', $filters['id_kabupaten']);
        }

        if (!empty($filters['date_from'])) {
            $builder->where('DATE(f.created_at) >=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $builder->where('DATE(f.created_at) <=', $filters['date_to']);
        }

        if (!empty($filters['search'])) {
            $builder->groupStart()
                ->like('u.nama_user', $filters['search'])
                ->orLike('u.sobat_id', $filters['search'])
                ->orLike('f.feedback', $filters['search'])
                ->groupEnd();
        }

        // Get total untuk pagination
        $total = $builder->countAllResults(false);

        // Get current page dan offset
        $request = \Config\Services::request();
        $page = (int) ($request->getGet('page_feedbacks') ?? 1);
        $offset = ($page - 1) * $perPage;

        // Get paginated data
        $feedbacks = $builder->limit($perPage, $offset)->get()->getResultArray();

        // Setup pager
        $pager = \Config\Services::pager();
        $pager->store('feedbacks', $page, $perPage, $total);

        return [
            'data' => $feedbacks,
            'pager' => $pager
        ];
    }

    /**
     * Get feedback by ID dengan detail lengkap
     */
    public function getFeedbackById($id)
    {
        return $this->db->table('sipantau_feedback_user f')
            ->select('f.*, 
                     u.nama_user,
                     u.email,
                     u.hp,
                     u.sobat_id as sobat_id_user,
                     k.nama_kabupaten,
                     u.role')
            ->join('sipantau_user u', 'f.sobat_id = u.sobat_id', 'left')
            ->join('master_kabupaten k', 'u.id_kabupaten = k.id_kabupaten', 'left')
            ->where('f.id_feedback', $id)
            ->get()
            ->getRowArray();
    }

    /**
     * Get feedback statistics
     */
    public function getFeedbackStats($filters = [])
    {
        $builder = $this->db->table('sipantau_feedback_user f')
            ->join('sipantau_user u', 'f.sobat_id = u.sobat_id', 'left');

        if (!empty($filters['id_kabupaten'])) {
            $builder->where('u.id_kabupaten', $filters['id_kabupaten']);
        }

        if (!empty($filters['date_from'])) {
            $builder->where('DATE(f.created_at) >=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $builder->where('DATE(f.created_at) <=', $filters['date_to']);
        }

        $total = $builder->countAllResults(false);

        // Rating distribution
        $ratingBuilder = clone $builder;
        $ratingDist = $ratingBuilder
            ->select('f.rating, COUNT(*) as jumlah')
            ->groupBy('f.rating')
            ->orderBy('f.rating', 'DESC')
            ->get()
            ->getResultArray();

        // Average rating
        $avgBuilder = clone $builder;
        $avgRating = $avgBuilder->selectAvg('f.rating', 'avg_rating')->get()->getRow();

        // Feedback hari ini
        $hariIniBuilder = clone $builder;
        $hariIni = $hariIniBuilder->where('DATE(f.created_at)', date('Y-m-d'))->countAllResults();

        // Feedback minggu ini
        $mingguIniBuilder = clone $builder;
        $mingguIni = $mingguIniBuilder->where('YEARWEEK(f.created_at)', date('YW'))->countAllResults();

        // Feedback bulan ini
        $bulanIniBuilder = clone $builder;
        $bulanIni = $bulanIniBuilder->where('YEAR(f.created_at)', date('Y'))
            ->where('MONTH(f.created_at)', date('m'))
            ->countAllResults();

        // Trend per bulan (6 bulan terakhir)
        $trendBuilder = $this->db->table('sipantau_feedback_user f')
            ->select('DATE_FORMAT(f.created_at, "%Y-%m") as bulan, 
                     COUNT(*) as total,
                     AVG(f.rating) as avg_rating')
            ->join('sipantau_user u', 'f.sobat_id = u.sobat_id', 'left');

        if (!empty($filters['id_kabupaten'])) {
            $trendBuilder->where('u.id_kabupaten', $filters['id_kabupaten']);
        }

        $trend = $trendBuilder
            ->where('f.created_at >=', date('Y-m-d', strtotime('-6 months')))
            ->groupBy('bulan')
            ->orderBy('bulan', 'DESC')
            ->get()
            ->getResultArray();

        return [
            'total' => $total,
            'hari_ini' => $hariIni,
            'minggu_ini' => $mingguIni,
            'bulan_ini' => $bulanIni,
            'avg_rating' => round($avgRating->avg_rating ?? 0, 2),
            'rating_distribution' => $ratingDist,
            'trend' => $trend
        ];
    }

    /**
     * Get feedback count by rating
     */
    public function getCountByRating($rating, $filters = [])
    {
        $builder = $this->db->table('sipantau_feedback_user f')
            ->join('sipantau_user u', 'f.sobat_id = u.sobat_id', 'left')
            ->where('f.rating', $rating);

        if (!empty($filters['id_kabupaten'])) {
            $builder->where('u.id_kabupaten', $filters['id_kabupaten']);
        }

        return $builder->countAllResults();
    }

    /**
     * Get latest feedbacks
     */
    public function getLatestFeedbacks($limit = 10, $filters = [])
    {
        $builder = $this->db->table('sipantau_feedback_user f')
            ->select('f.*, 
                     u.nama_user,
                     k.nama_kabupaten')
            ->join('sipantau_user u', 'f.sobat_id = u.sobat_id', 'left')
            ->join('master_kabupaten k', 'u.id_kabupaten = k.id_kabupaten', 'left')
            ->orderBy('f.created_at', 'DESC')
            ->limit($limit);

        if (!empty($filters['id_kabupaten'])) {
            $builder->where('u.id_kabupaten', $filters['id_kabupaten']);
        }

        return $builder->get()->getResultArray();
    }
}