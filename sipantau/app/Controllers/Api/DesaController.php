<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\MasterDesaModel;
use App\Models\UserModel;

class DesaController extends BaseController
{
    use ResponseTrait;

    private $jwtKey;

    public function __construct()
    {
        $this->jwtKey = getenv('JWT_SECRET_KEY');
    }

    public function index()
    {
        // 🔑 Ambil token dari header Authorization
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

            // 🔹 Ambil parameter id_kecamatan dari query
            $idKecamatan = $this->request->getGet('id_kecamatan');

            if (empty($idKecamatan)) {
                return $this->failValidationErrors('Parameter id_kecamatan wajib diisi');
            }

            // 🔹 Ambil daftar desa berdasarkan id_kecamatan
            $desaModel = new MasterDesaModel();
            $data = $desaModel
                ->select('id_desa, id_kecamatan, nama_desa')
                ->where('id_kecamatan', $idKecamatan)
                ->findAll();

            // 🔸 Jika tidak ada data desa
            if (empty($data)) {
                return $this->respond([
                    'status' => 'success',
                    'message' => 'Tidak ada desa pada kecamatan ini',
                    'data' => []
                ], 200);
            }

            // 🔹 Kirim data berhasil
            return $this->respond([
                'status' => 'success',
                'message' => 'Data desa berhasil diambil',
                'id_kecamatan' => $idKecamatan,
                'data' => $data
            ], 200);

        } catch (\Exception $e) {
            return $this->failUnauthorized('Token tidak valid: ' . $e->getMessage());
        }
    }
}
