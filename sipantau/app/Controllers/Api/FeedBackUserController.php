<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\FeedbackUserModel;
use CodeIgniter\API\ResponseTrait;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class FeedBackUserController extends BaseController
{
    use ResponseTrait;

    protected $jwtKey;
    protected $feedbackModel;

    public function __construct()
    {
        $this->jwtKey = getenv('JWT_SECRET_KEY');
        $this->feedbackModel = new FeedbackUserModel();
    }

    /**
     * GET /api/feedback
     * Ambil semua feedback berdasarkan sobat_id user login
     */
    public function index()
    {
        $authHeader = $this->request->getHeaderLine('Authorization');
        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $this->failUnauthorized('Token tidak ditemukan');
        }

        try {
            $token = trim($matches[1]);
            $decoded = JWT::decode($token, new Key($this->jwtKey, 'HS256'));
            $sobat_id = $decoded->data->sobat_id ?? null;

            if (!$sobat_id) {
                return $this->failUnauthorized('ID pengguna tidak ditemukan dalam token');
            }

            // Ambil semua feedback berdasarkan sobat_id
            $feedback = $this->feedbackModel
                ->where('sobat_id', $sobat_id)
                ->orderBy('created_at', 'DESC')
                ->findAll();

            return $this->respond([
                'status' => true,
                'message' => 'Data feedback berhasil diambil',
                'data' => $feedback
            ]);
        } catch (\Throwable $th) {
            return $this->fail('Token tidak valid: ' . $th->getMessage());
        }
    }

    /**
     * POST /api/feedback
     * Tambah feedback baru berdasarkan sobat_id user login
     */
    public function create()
    {
        $authHeader = $this->request->getHeaderLine('Authorization');
        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $this->failUnauthorized('Token tidak ditemukan');
        }

        try {
            $token = trim($matches[1]);
            $decoded = JWT::decode($token, new Key($this->jwtKey, 'HS256'));
            $sobat_id = $decoded->data->sobat_id ?? null;

            if (!$sobat_id) {
                return $this->failUnauthorized('ID pengguna tidak ditemukan dalam token');
            }

            $feedbackText = $this->request->getVar('feedback');
            $rating = $this->request->getVar('rating');

            if (!$feedbackText) {
                return $this->failValidationErrors('Feedback tidak boleh kosong');
            }

            $data = [
                'sobat_id' => $sobat_id,
                'feedback' => $feedbackText,
                'rating'   => $rating,
                'created_at' => date('Y-m-d H:i:s'),
            ];

            $this->feedbackModel->insert($data);

            return $this->respondCreated([
                'status' => true,
                'message' => 'Feedback berhasil dikirim',
                'data' => $data
            ]);
        } catch (\Throwable $th) {
            return $this->fail('Token tidak valid: ' . $th->getMessage());
        }
    }
}
