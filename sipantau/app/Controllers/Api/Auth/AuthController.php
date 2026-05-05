<?php

namespace App\Controllers\Api\Auth;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\PMLModel;
use App\Models\PCLModel;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use CodeIgniter\API\ResponseTrait;

class AuthController extends BaseController
{
    use ResponseTrait;

    private $jwtKey;
    private $jwtExpiry;

    public function __construct()
    {
        $this->jwtKey = getenv('JWT_SECRET_KEY');
        $this->jwtExpiry = getenv('JWT_EXPIRY') ?: 604800; // default 7 hari
    }

    public function login()
    {
        // Ambil payload
        if ($this->request->getHeaderLine('Content-Type') === 'application/json') {
            $data = $this->request->getJSON(true);
        } else {
            $data = $this->request->getPost();
        }

        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        if (!$email || !$password) {
            return $this->failValidationErrors('Email dan password wajib diisi');
        }

        // Cari user
        $userModel = new UserModel();
        $user = $userModel->where('email', $email)
            ->where('is_active', 1)
            ->first();

        if (!$user) {
            return $this->failNotFound('User tidak ditemukan atau tidak aktif');
        }

        // Decode role JSON
        $roleIds = json_decode($user['role'], true) ?? [];

        // Verifikasi password
        if (!password_verify($password, $user['password'])) {
            return $this->failUnauthorized('Password salah');
        }

        /**
         * ==============================
         * 🔍 AMBIL LIST ID PML & PCL
         * ==============================
         */
        $pmlModel = new PMLModel();
        $pclModel = new PCLModel();

        // Ambil semua id_pml berdasarkan sobat_id
        $listPML = $pmlModel->select('id_pml')
            ->where('sobat_id', $user['sobat_id'])
            ->findAll();

        // Ambil semua id_pcl berdasarkan sobat_id
        $listPCL = $pclModel->select('id_pcl')
            ->where('sobat_id', $user['sobat_id'])
            ->findAll();

        // Convert ke array flat
        $idPML = array_map('intval', array_column($listPML, 'id_pml'));
        $idPCL = array_map('intval', array_column($listPCL, 'id_pcl'));

        /**
         * ==============================
         * Generate JWT
         * ==============================
         */
        $issuedAt = time();
        $expireAt = $issuedAt + $this->jwtExpiry;

        $payload = [
            'iss' => base_url(),
            'iat' => $issuedAt,
            'exp' => $expireAt,
            'data' => [
                'sobat_id'     => $user['sobat_id'],
                'nama_user'    => $user['nama_user'],
                'email'        => $user['email'],
                'hp'           => $user['hp'],
                'id_kabupaten' => $user['id_kabupaten'],
                'roles'        => $roleIds,
                'id_pml'  => $idPML,  // <= Tambahan
                'id_pcl'  => $idPCL   // <= Tambahan
            ]
        ];

        $token = JWT::encode($payload, $this->jwtKey, 'HS256');

        return $this->respond([
            'status'  => 'success',
            'message' => 'Login berhasil',
            'token'   => $token,
            'user'    => $payload['data']
        ]);
    }

    public function me()
    {
        $authHeader = $this->request->getHeaderLine('Authorization');
        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $this->failUnauthorized('Token tidak ditemukan');
        }

        $token = $matches[1];

        try {
            $decoded = JWT::decode($token, new Key($this->jwtKey, 'HS256'));

            return $this->respond([
                'status' => 'success',
                'user'   => $decoded->data
            ]);
        } catch (\Firebase\JWT\ExpiredException $e) {
            return $this->failUnauthorized('Token kadaluarsa');
        } catch (\Exception $e) {
            return $this->failUnauthorized('Token tidak valid: ' . $e->getMessage());
        }
    }

    public function updateProfile()
{
    $authHeader = $this->request->getHeaderLine('Authorization');
    if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        return $this->failUnauthorized('Token tidak ditemukan');
    }

    $token = $matches[1];

    try {
        // Decode token JWT
        $decoded = JWT::decode($token, new Key($this->jwtKey, 'HS256'));
        $userData = (array) $decoded->data;

        $sobatId = $userData['sobat_id'];

        // Ambil input JSON atau form-data
        if ($this->request->getHeaderLine('Content-Type') === 'application/json') {
            $input = $this->request->getJSON(true);
        } else {
            $input = $this->request->getPost();
        }

        $userModel = new \App\Models\UserModel();
        $user = $userModel->where('sobat_id', $sobatId)->first();

        if (!$user) {
            return $this->failNotFound("User tidak ditemukan");
        }

        // Data yang boleh diupdate
        $updateData = [];

        // Update nama
        if (!empty($input['nama_user'])) {
            $updateData['nama_user'] = $input['nama_user'];
        }

        // Update nomor hp
        if (!empty($input['hp'])) {
            $updateData['hp'] = $input['hp'];
        }

        // Update password → TIDAK di-hash di sini
        if (!empty($input['password'])) {
            $updateData['password'] = $input['password']; // Model akan hash otomatis
        }

        // Jika tidak ada yang diupdate
        if (empty($updateData)) {
            return $this->failValidationErrors("Tidak ada data yang diupdate");
        }

        // Update ke database
        $userModel->update($sobatId, $updateData);

        return $this->respond([
            'status' => 'success',
            'message' => 'Profil berhasil diperbarui',
            'updated_data' => $updateData
        ]);

    } catch (\Firebase\JWT\ExpiredException $e) {
        return $this->failUnauthorized('Token kadaluarsa');
    } catch (\Exception $e) {
        return $this->failUnauthorized('Token tidak valid: ' . $e->getMessage());
    }
}


}
