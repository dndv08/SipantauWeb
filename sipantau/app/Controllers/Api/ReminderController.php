<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\KurvaPetugasModel;
use App\Models\PantauProgressModel;
use App\Models\SipantauTransaksiModel;
use App\Models\PCLModel;
use CodeIgniter\API\ResponseTrait;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class ReminderController extends BaseController
{
    use ResponseTrait;

    protected $jwtKey;
    protected $kurvaModel;
    protected $progressModel;
    protected $transaksiModel;
    protected $pclModel;

    public function __construct()
    {
        $this->jwtKey = getenv('JWT_SECRET_KEY');
        $this->kurvaModel = new KurvaPetugasModel();
        $this->progressModel = new PantauProgressModel();
        $this->transaksiModel = new SipantauTransaksiModel();
        $this->pclModel = new PCLModel();
    }

    public function show()
    {
        // Validasi token
        $authHeader = $this->request->getHeaderLine('Authorization');
        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $this->failUnauthorized('Token tidak ditemukan');
        }

        try {
            $token = trim($matches[1]);
            $decoded = JWT::decode($token, new Key($this->jwtKey, 'HS256'));

            $sobatId = $decoded->data->sobat_id ?? null;
            if (!$sobatId) {
                return $this->failUnauthorized('ID user tidak ditemukan dalam token');
            }

            // Ambil semua id_pcl milik user
            $pclList = $this->pclModel->where('sobat_id', $sobatId)->findAll();

            $today = date('Y-m-d');
            $result = [];

            foreach ($pclList as $pcl) {
                $idPCL = $pcl['id_pcl'];

                // Cek target hari ini
                $target = $this->kurvaModel
                    ->where('id_pcl', $idPCL)
                    ->where('tanggal_target', $today)
                    ->first();

                if (!$target) {
                    continue; // skip pcl yg tidak punya target hari ini
                }

                // Cek transaksi & progress
                $cekTransaksi = $this->transaksiModel
                    ->where('id_pcl', $idPCL)
                    ->where('DATE(created_at)', $today)
                    ->countAllResults();

                $cekProgress = $this->progressModel
                    ->where('id_pcl', $idPCL)
                    ->where('DATE(created_at)', $today)
                    ->countAllResults();

                $result[] = [
                    'id_pcl' => $idPCL,
                    'tanggal' => $today,
                    'target_harian' => (int)$target['target_harian_absolut'],
                    'sudah_transaksi' => $cekTransaksi > 0,
                    'sudah_progress' => $cekProgress > 0
                ];
            }

            return $this->respond([
                'status' => true,
                'message' => 'Reminder status seluruh PCL berhasil diambil',
                'data' => $result
            ], 200);

        } catch (\Throwable $th) {
            return $this->fail('Token tidak valid: ' . $th->getMessage());
        }
    }
}
