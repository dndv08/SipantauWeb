<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\UserModel;
use App\Models\MasterKecModel;
use App\Models\MasterDesaModel;

class WilayahController extends BaseController
{
    use ResponseTrait;

    private $jwtKey;

    public function __construct()
    {
        $this->jwtKey = getenv('JWT_SECRET_KEY');
    }

    /**
     * 🔥 LOAD SEMUA KECAMATAN & DESA (UNTUK SYNC ROOM)
     */
    public function loadAll()
    {
        // 🔑 Ambil token
        $authHeader = $this->request->getHeaderLine('Authorization');
        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $this->failUnauthorized('Token tidak ditemukan');
        }

        $token = $matches[1];

        try {
            // 🔍 Decode JWT
            $decoded = JWT::decode($token, new Key($this->jwtKey, 'HS256'));
            $sobat_id = $decoded->data->sobat_id ?? $decoded->sobat_id ?? null;

            if (!$sobat_id) {
                return $this->failUnauthorized('Token tidak valid');
            }

            // 🔹 Ambil user
            $userModel = new UserModel();
            $user = $userModel->where('sobat_id', $sobat_id)->first();

            if (!$user || empty($user['id_kabupaten'])) {
                return $this->failNotFound('User tidak memiliki kabupaten');
            }

            $idKabupaten = $user['id_kabupaten'];

            // 🔹 Ambil kecamatan
            $kecModel = new MasterKecModel();
            $kecamatan = $kecModel
                ->select('id_kecamatan, id_kabupaten, nama_kecamatan')
                ->where('id_kabupaten', $idKabupaten)
                ->findAll();

            // 🔹 Ambil desa
            $desaModel = new MasterDesaModel();
            $desa = [];

            if (!empty($kecamatan)) {
                $ids = array_column($kecamatan, 'id_kecamatan');

                $desa = $desaModel
                    ->select('id_desa, id_kecamatan, nama_desa')
                    ->whereIn('id_kecamatan', $ids)
                    ->findAll();
            }

            return $this->respond([
                'kecamatan' => [
                    'status' => 'success',
                    'message' => 'Data kecamatan berhasil diambil',
                    'data' => $kecamatan
                ],
                'desa' => [
                    'status' => 'success',
                    'message' => 'Data desa berhasil diambil',
                    'data' => $desa
                ]
            ], 200);

        } catch (\Exception $e) {
            return $this->failUnauthorized('Token tidak valid: ' . $e->getMessage());
        }
    }

    public function getSls()
    {
        $authHeader = $this->request->getHeaderLine('Authorization');
        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $this->failUnauthorized('Token tidak ditemukan');
        }

        try {
            $decoded = JWT::decode($matches[1], new Key($this->jwtKey, 'HS256'));
        } catch (\Exception $e) {
            return $this->failUnauthorized('Token tidak valid: ' . $e->getMessage());
        }

        $idDesa = $this->request->getGet('id_desa');
        if (empty($idDesa)) {
            return $this->failValidationErrors('id_desa wajib diisi');
        }

        $slsModel = new \App\Models\MasterSLSModel();
        $data = $slsModel->where('id_desa', $idDesa)->orderBy('nama_sls', 'ASC')->findAll();

        return $this->respond([
            'status' => 'success',
            'data' => $data
        ]);
    }

    public function getSubSls()
    {
        $authHeader = $this->request->getHeaderLine('Authorization');
        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $this->failUnauthorized('Token tidak ditemukan');
        }

        try {
            $decoded = JWT::decode($matches[1], new Key($this->jwtKey, 'HS256'));
        } catch (\Exception $e) {
            return $this->failUnauthorized('Token tidak valid: ' . $e->getMessage());
        }

        $idSls = $this->request->getGet('id_sls');
        if (empty($idSls)) {
            return $this->failValidationErrors('id_sls wajib diisi');
        }

        $subSlsModel = new \App\Models\MasterSubSLSModel();
        $data = $subSlsModel->where('id_sls', $idSls)->orderBy('id_sub_sls', 'ASC')->findAll();

        return $this->respond([
            'status' => 'success',
            'data' => $data
        ]);
    }
}
