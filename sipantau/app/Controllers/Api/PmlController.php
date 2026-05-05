<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\PMLModel as PmlModel;
use App\Models\PCLModel as PclModel;

use App\Models\UserModel;
use App\Models\PantauProgressModel;

class PmlController extends BaseController
{
    use ResponseTrait;

    private $jwtKey;

    public function __construct()
    {
        $this->jwtKey = getenv('JWT_SECRET_KEY');
    }

    /**
     * GET /api/pcl/by-kegiatan/{id_pml}
     * Menampilkan semua PCL bawahan + total realisasi kumulatif
     */
    public function index($id_pml)
    {
        /** -------------------------------
         * 1. Validasi JWT
         * -------------------------------*/
        $authHeader = $this->request->getHeaderLine('Authorization');
        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $this->failUnauthorized('Token tidak ditemukan');
        }

        try {
            $decoded = JWT::decode($matches[1], new Key($this->jwtKey, 'HS256'));
        } catch (\Exception $e) {
            return $this->failUnauthorized('Token tidak valid: ' . $e->getMessage());
        }

        /** -------------------------------
         * 2. Ambil PML berdasarkan id_pml
         * -------------------------------*/
        $pmlModel = new PmlModel();
        $pmlData = $pmlModel->find($id_pml);

        if (!$pmlData) {
            return $this->respond([
                'status' => false,
                'message' => 'PML tidak ditemukan',
                'data' => []
            ]);
        }

        /** -------------------------------
         * 3. Ambil semua PCL bawahan PML
         * -------------------------------*/
        $pclModel = new PclModel();
        $pclList = $pclModel->where('id_pml', $id_pml)->findAll();

        if (empty($pclList)) {
            return $this->respond([
                'status' => true,
                'message' => 'Tidak ada PCL untuk PML ini',
                'data' => []
            ]);
        }

        /** -------------------------------
         * 4. Ambil realisasi kumulatif tiap PCL
         * -------------------------------*/
        $userModel = new UserModel();
        $progressModel = new PantauProgressModel();

        $result = [];

        foreach ($pclList as $pcl) {

            // Ambil user PCL
            $user = $userModel->find($pcl['sobat_id']);

            // Ambil total realisasi kumulatif by PCL
            $totalRealisasi = $progressModel->getRealisasiByPCL($pcl['id_pcl']);

            // Hitung persentase
            $persentase = 0;
            if (!empty($pcl['target']) && $pcl['target'] > 0) {
                $persentase = round(($totalRealisasi / $pcl['target']) * 100, 2);
            }

            // Push data
            $result[] = [
                'id_pcl' => (int) $pcl['id_pcl'],
                'sobat_id' => $pcl['sobat_id'],
                'nama_pcl' => $user['nama_user'] ?? '-',
                'hp' => $user['hp'] ?? '-',
                'id_pml' => $id_pml,
                'status_approval' => $pcl['status_approval'],
                'target' => (int) $pcl['target'],
                'total_realisasi_kumulatif' => (int) $totalRealisasi,
                'persentase' => $persentase . '%',
            ];
        }

        /** -------------------------------
         * 5. Response
         * -------------------------------*/
        return $this->respond([
            'status' => true,
            'message' => 'Data PCL bawahan PML ditemukan',
            'data' => $result
        ]);
    }

    public function approvePCL($id_pcl = null)
    {
        // 1. Validasi JWT
        $authHeader = $this->request->getHeaderLine('Authorization');
        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $this->failUnauthorized('Token tidak ditemukan');
        }

        try {
            $decoded = JWT::decode($matches[1], new Key($this->jwtKey, 'HS256'));
        } catch (\Exception $e) {
            return $this->failUnauthorized('Token tidak valid: ' . $e->getMessage());
        }

        // 2. Ambil status_approval dari JSON body
        $input = $this->request->getJSON(true);
        $status_approval = $input['status_approval'] ?? null;

        if (!$id_pcl || !$status_approval) {
            return $this->failValidationErrors('id_pcl dan status_approval wajib diisi');
        }

        // 3. Update PCL
        $pclModel = new PclModel();
        $pcl = $pclModel->find($id_pcl);

        if (!$pcl) {
            return $this->failNotFound('PCL tidak ditemukan');
        }

        $pclModel->update($id_pcl, [
            'status_approval' => $status_approval
        ]);

        // 4. Response
        return $this->respond([
            'status' => true,
            'message' => 'Status approval berhasil diupdate',
            'data' => [
                'id_pcl' => (int) $id_pcl,
                'status_approval' => $status_approval
            ]
        ]);
    }
}
