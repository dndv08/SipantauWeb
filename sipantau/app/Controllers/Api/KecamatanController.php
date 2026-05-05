<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\MasterKecModel;
use App\Models\UserModel;

class KecamatanController extends BaseController
{
    use ResponseTrait;

    private $jwtKey;

    public function __construct()
    {
        $this->jwtKey = getenv('JWT_SECRET_KEY');
    }

    public function index()
    {
        // 🔑 Ambil token JWT dari header Authorization
        $authHeader = $this->request->getHeaderLine('Authorization');
        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $this->failUnauthorized("Token tidak ditemukan");
        }

        $token = $matches[1];

        try {
            // 🔍 Decode token JWT
            $decoded = JWT::decode($token, new Key($this->jwtKey, 'HS256'));
            $sobat_id = $decoded->data->sobat_id ?? $decoded->sobat_id ?? null;

            if (!$sobat_id) {
                return $this->failUnauthorized('Token tidak valid: sobat_id tidak ditemukan');
            }

            // 🔹 Ambil data user untuk mengetahui kabupaten user
            $userModel = new UserModel();
            $user = $userModel->where('sobat_id', $sobat_id)->first();

            if (!$user || empty($user['id_kabupaten'])) {
                return $this->failNotFound('User tidak memiliki data kabupaten');
            }

            $idKabupaten = $user['id_kabupaten'];

            // 🔹 Ambil semua kecamatan yang berelasi dengan kabupaten user
            $kecModel = new MasterKecModel();
            $data = $kecModel
                ->select('id_kecamatan, id_kabupaten, nama_kecamatan')
                ->where('id_kabupaten', $idKabupaten)
                ->findAll();

            // 🔸 Jika tidak ada data kecamatan
            if (empty($data)) {
                return $this->respond([
                    'status' => 'success',
                    'message' => 'Tidak ada kecamatan pada kabupaten ini',
                    'data' => []
                ], 200);
            }

            // 🔹 Berhasil ambil data
            return $this->respond([
                'status' => 'success',
                'message' => 'Data kecamatan berhasil diambil',
                'kabupaten_user' => $idKabupaten,
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            return $this->failUnauthorized('Token tidak valid: ' . $e->getMessage());
        }
    }
}
