<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\KurvaPetugasModel;
use App\Models\PantauProgressModel;
use CodeIgniter\API\ResponseTrait;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class KurvaPetugasController extends BaseController
{
    use ResponseTrait;

    protected $jwtKey;
    protected $kurvaModel;
    protected $progressModel;

    public function __construct()
    {
        $this->jwtKey = getenv('JWT_SECRET_KEY');
        $this->kurvaModel = new KurvaPetugasModel();
        $this->progressModel = new PantauProgressModel();
    }

    /**
     * GET /api/kurva-petugas/{id_pcl}
     * Ambil data target & realisasi untuk kurva S per petugas (PCL)
     */
    public function show($idPCL = null)
    {
        // 🔒 Validasi Token JWT
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

            // 🔍 Validasi input id_pcl
            if (!$idPCL) {
                return $this->failValidationError('ID PCL tidak boleh kosong');
            }

            // Ambil data target kurva S dari tabel kurva_petugas
            $targetData = $this->kurvaModel->getByPCL($idPCL);

            // Ambil data realisasi harian berdasarkan tanggal (pantau_progress)
            $realisasiData = $this->progressModel->select("
                    DATE(created_at) as tanggal,
                    SUM(jumlah_realisasi_absolut) as total_realisasi
                ")
                ->where('id_pcl', $idPCL)
                ->groupBy('DATE(created_at)')
                ->orderBy('DATE(created_at)', 'ASC')
                ->get()
                ->getResultArray();

            // Format data agar bisa dibandingkan antara target & realisasi
            $kurvaS = [];
            $realisasiKumulatif = 0; // simpan kumulatif agar terus bertambah

            foreach ($targetData as $target) {
                $tanggal = $target['tanggal_target'];
                $realisasiHarian = 0;

                // Cari realisasi harian sesuai tanggal target
                foreach ($realisasiData as $real) {
                    if ($real['tanggal'] === $tanggal) {
                        $realisasiHarian = (int)$real['total_realisasi'];
                        break;
                    }
                }

                // Hitung realisasi kumulatif
                $realisasiKumulatif += $realisasiHarian;

                $kurvaS[] = [
                    'tanggal_target' => $tanggal,
                    'target_kumulatif_absolut' => (int)$target['target_kumulatif_absolut'],
                    'target_harian_absolut' => (int)$target['target_harian_absolut'],
                    'target_persen_kumulatif' => (float)$target['target_persen_kumulatif'],
                    'realisasi_harian' => $realisasiHarian,
                    'realisasi_kumulatif' => $realisasiKumulatif,
                    'is_hari_kerja' => (bool)$target['is_hari_kerja'],
                ];
            }

            return $this->respond([
                'status' => true,
                'message' => 'Data kurva S berhasil diambil',
                'data' => $kurvaS
            ], 200);

        } catch (\Throwable $th) {
            return $this->fail('Token tidak valid: ' . $th->getMessage());
        }
    }
}
