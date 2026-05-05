<?php

namespace App\Controllers\SuperAdmin;

use App\Controllers\BaseController;
use App\Models\FeedbackUserModel;
use App\Models\MasterKabModel;

class RatingAplikasiController extends BaseController
{
    protected $feedbackUserModel;
    protected $kabupatenModel;

    public function __construct()
    {
        $this->feedbackUserModel = new FeedbackUserModel();
        $this->kabupatenModel = new MasterKabModel();
    }

    /**
     * Halaman utama rating aplikasi
     */
    public function index()
    {
        $filters = [
            'rating' => $this->request->getGet('rating'),
            'id_kabupaten' => $this->request->getGet('id_kabupaten'),
            'date_from' => $this->request->getGet('date_from'),
            'date_to' => $this->request->getGet('date_to'),
            'search' => $this->request->getGet('search'),
        ];

        // Ambil perPage dari GET, default 10
        $perPage = $this->request->getGet('perPage') ?? 10;

        // Validasi perPage
        $allowedPerPage = [5, 10, 25, 50, 100];
        if (!in_array((int) $perPage, $allowedPerPage)) {
            $perPage = 10;
        }

        // UBAH INI - Tambahkan parameter perPage
        $feedbackData = $this->feedbackUserModel->getFeedbackWithDetailsPaginated($filters, $perPage);

        $stats = $this->feedbackUserModel->getFeedbackStats($filters);
        $kabupatens = $this->kabupatenModel->findAll();

        // Get latest feedbacks untuk sidebar
        $latestFeedbacks = $this->feedbackUserModel->getLatestFeedbacks(5, [
            'id_kabupaten' => $filters['id_kabupaten'] ?? null
        ]);

        $data = [
            'title' => 'Rating Aplikasi',
            'active_menu' => 'rating-aplikasi',
            'feedbacks' => $feedbackData['data'],           
            'pager' => $feedbackData['pager'],              
            'stats' => $stats,
            'kabupatens' => $kabupatens,
            'filters' => $filters,
            'latestFeedbacks' => $latestFeedbacks,
            'perPage' => $perPage                           
        ];

        return view('SuperAdmin/RatingAplikasi/index', $data);
    }
    /**
     * Detail feedback user
     */
    public function show($id)
    {
        $feedback = $this->feedbackUserModel->getFeedbackById($id);

        if (!$feedback) {
            return redirect()->to(base_url('superadmin/rating-aplikasi'))
                ->with('error', 'Feedback tidak ditemukan.');
        }

        $data = [
            'title' => 'Detail Rating',
            'active_menu' => 'rating-aplikasi',
            'feedback' => $feedback
        ];

        return view('SuperAdmin/RatingAplikasi/show', $data);
    }

    /**
     * Delete feedback user (jika diperlukan moderasi)
     */
    public function delete($id)
    {
        $feedback = $this->feedbackUserModel->find($id);

        if (!$feedback) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Feedback tidak ditemukan.'
            ]);
        }

        if ($this->feedbackUserModel->delete($id)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Feedback berhasil dihapus!'
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Gagal menghapus feedback.'
        ]);
    }

    /**
     * Export rating data to CSV
     */
    public function exportCSV()
    {
        $filters = [
            'rating' => $this->request->getGet('rating'),
            'id_kabupaten' => $this->request->getGet('id_kabupaten'),
            'date_from' => $this->request->getGet('date_from'),
            'date_to' => $this->request->getGet('date_to'),
            'search' => $this->request->getGet('search'),
        ];

        $feedbacks = $this->feedbackUserModel->getFeedbackWithDetails($filters);

        // Set headers for CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=rating_aplikasi_' . date('Y-m-d_His') . '.csv');

        // Create output stream
        $output = fopen('php://output', 'w');

        // Add BOM for UTF-8
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Add headers
        fputcsv($output, [
            'No',
            'Tanggal',
            'Nama User',
            'Sobat ID',
            'Kabupaten',
            'Rating',
            'Feedback'
        ]);

        // Add data
        foreach ($feedbacks as $index => $fb) {
            fputcsv($output, [
                $index + 1,
                date('d/m/Y H:i', strtotime($fb['created_at'])),
                $fb['nama_user'],
                $fb['sobat_id'],
                $fb['nama_kabupaten'],
                $fb['rating'],
                $fb['feedback']
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Get rating trend chart data (AJAX)
     */
    public function getRatingTrend()
    {
        $filters = [
            'id_kabupaten' => $this->request->getGet('id_kabupaten'),
        ];

        $stats = $this->feedbackUserModel->getFeedbackStats($filters);

        return $this->response->setJSON([
            'success' => true,
            'data' => $stats['trend']
        ]);
    }
}