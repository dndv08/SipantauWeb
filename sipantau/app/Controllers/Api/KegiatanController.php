<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\PMLModel;
use App\Models\PCLModel;
use App\Models\SipantauUserAchievementModel;
use App\Models\PantauProgressModel; // 🔥 TAMBAHAN

class KegiatanController extends BaseController
{
    use ResponseTrait;

    private $jwtKey;

    public function __construct()
    {
        $this->jwtKey = getenv('JWT_SECRET_KEY');
    }

    public function index()
    {
        // Ambil & cek token
        $authHeader = $this->request->getHeaderLine('Authorization');
        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $this->failUnauthorized('Token tidak ditemukan');
        }

        $token = $matches[1];

        try {
            $decoded = JWT::decode($token, new Key($this->jwtKey, 'HS256'));
            $sobat_id = $decoded->data->sobat_id;

            $pmlModel      = new PMLModel();
            $pclModel      = new PCLModel();
            $progressModel = new PantauProgressModel(); // 🔥 TAMBAHAN

            // Cek role user
            $isPML = $pmlModel->where('sobat_id', $sobat_id)->findAll();
            $isPCL = $pclModel->where('sobat_id', $sobat_id)->findAll();

            if (!$isPML && !$isPCL) {
                return $this->failUnauthorized('User ini bukan PML atau PCL');
            }

            // fungsi cek status kegiatan
            $checkStatus = function ($mulai, $selesai) {
                $today = date('Y-m-d');
                return ($today >= $mulai && $today <= $selesai) ? 'aktif' : 'tidak aktif';
            };

            // ---------------------------
            // SIAPKAN HASIL RESPONSE
            // ---------------------------
            $result = [
                "status"        => "success",
                "roles"         => [],
                "kegiatan_pml"  => [],
                "kegiatan_pcl"  => []
            ];

            // ===========================
            // USER SEBAGAI PML
            // ===========================
            if ($isPML) {

                $dataPML = $pmlModel->select('
                        pml.id_pml, pml.target, pml.status_approval,
                        kw.id_kegiatan_detail_proses,
                        mk.nama_kegiatan, mkdp.nama_kegiatan_detail_proses,
                        mkdp.tanggal_mulai, mkdp.tanggal_selesai,
                        kab.nama_kabupaten
                    ')
                    ->join('kegiatan_wilayah kw', 'pml.id_kegiatan_wilayah = kw.id_kegiatan_wilayah')
                    ->join('master_kegiatan_detail_proses mkdp', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses')
                    ->join('master_kegiatan_detail mkd', 'mkdp.id_kegiatan_detail = mkd.id_kegiatan_detail')
                    ->join('master_kegiatan mk', 'mkd.id_kegiatan = mk.id_kegiatan')
                    ->join('master_kabupaten kab', 'kw.id_kabupaten = kab.id_kabupaten')
                    ->where('pml.sobat_id', $sobat_id)
                    ->findAll();

                foreach ($dataPML as &$k) {
                    $k['status_kegiatan'] = $checkStatus(
                        $k['tanggal_mulai'],
                        $k['tanggal_selesai']
                    );
                }

                $result["roles"][]       = "PML";
                $result["kegiatan_pml"]  = $dataPML;
            }

            // ===========================
            // USER SEBAGAI PCL
            // ===========================
            if ($isPCL) {

                $dataPCL = $pclModel->select('
                        pcl.id_pcl, pcl.target, pcl.status_approval,
                        mk.nama_kegiatan, mkdp.nama_kegiatan_detail_proses,
                        mkdp.tanggal_mulai, mkdp.tanggal_selesai, mkdp.id_kegiatan_detail_proses,
                        kab.nama_kabupaten,
                        u_pml.nama_user AS nama_pml,
                        kw.keterangan AS keterangan_wilayah
                    ')
                    ->join('pml', 'pcl.id_pml = pml.id_pml')
                    ->join('sipantau_user u_pml', 'pml.sobat_id = u_pml.sobat_id')
                    ->join('kegiatan_wilayah kw', 'pml.id_kegiatan_wilayah = kw.id_kegiatan_wilayah')
                    ->join('master_kegiatan_detail_proses mkdp', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses')
                    ->join('master_kegiatan_detail mkd', 'mkdp.id_kegiatan_detail = mkd.id_kegiatan_detail')
                    ->join('master_kegiatan mk', 'mkd.id_kegiatan = mk.id_kegiatan')
                    ->join('master_kabupaten kab', 'kw.id_kabupaten = kab.id_kabupaten')
                    ->where('pcl.sobat_id', $sobat_id)
                    ->findAll();

                foreach ($dataPCL as &$k) {

                    // 🔥 TOTAL REALISASI KUMULATIF (DARI pantau_progress)
                    $k['total_realisasi_kumulatif'] =
                        $progressModel->getRealisasiByPCL($k['id_pcl']);

                    $k['status_kegiatan'] = $checkStatus(
                        $k['tanggal_mulai'],
                        $k['tanggal_selesai']
                    );
                }

                $result["roles"][]        = "PCL";
                $result["kegiatan_pcl"]  = $dataPCL;
            }

            return $this->respond($result);

        } catch (\Exception $e) {
            return $this->failUnauthorized("Token tidak valid: " . $e->getMessage());
        }
    }

    // ===============================
    // TOTAL KEGIATAN PML
    // ===============================
    public function totalKegiatanPML()
    {
        $authHeader = $this->request->getHeaderLine('Authorization');
        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $this->failUnauthorized('Token tidak ditemukan');
        }

        $token = $matches[1];

        try {
            $decoded  = JWT::decode($token, new Key($this->jwtKey, 'HS256'));
            $sobat_id = $decoded->data->sobat_id;

            $pmlModel = new PMLModel();
            $pclModel = new PCLModel();

            $totalKegiatan = $pmlModel
                ->where('sobat_id', $sobat_id)
                ->countAllResults();

            $totalAktif = $pmlModel
                ->join('kegiatan_wilayah kw', 'pml.id_kegiatan_wilayah = kw.id_kegiatan_wilayah')
                ->join('master_kegiatan_detail_proses mkdp', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses')
                ->where('pml.sobat_id', $sobat_id)
                ->where('mkdp.tanggal_mulai <=', date('Y-m-d'))
                ->where('mkdp.tanggal_selesai >=', date('Y-m-d'))
                ->countAllResults();

            $totalPCL = $pclModel
                ->join('pml', 'pcl.id_pml = pml.id_pml')
                ->where('pml.sobat_id', $sobat_id)
                ->countAllResults();

            return $this->respond([
                'status' => 'success',
                'sobat_id' => $sobat_id,
                'total_kegiatan_pml' => $totalKegiatan,
                'total_kegiatan_pml_aktif' => $totalAktif,
                'total_pcl' => $totalPCL,
            ]);

        } catch (\Exception $e) {
            return $this->failUnauthorized("Token tidak valid: " . $e->getMessage());
        }
    }

    // ===============================
    // TOTAL KEGIATAN PCL
    // ===============================
    public function totalKegiatanPCL()
    {
        $authHeader = $this->request->getHeaderLine('Authorization');
        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $this->failUnauthorized('Token tidak ditemukan');
        }

        $token = $matches[1];

        try {
            $decoded  = JWT::decode($token, new Key($this->jwtKey, 'HS256'));
            $sobat_id = $decoded->data->sobat_id;

            $pclModel = new PCLModel();
            $achModel = new SipantauUserAchievementModel();

            $total = $pclModel->where('sobat_id', $sobat_id)->countAllResults();

            $totalAktif = $pclModel
                ->join('pml', 'pcl.id_pml = pml.id_pml')
                ->join('kegiatan_wilayah kw', 'pml.id_kegiatan_wilayah = kw.id_kegiatan_wilayah')
                ->join('master_kegiatan_detail_proses mkdp', 'kw.id_kegiatan_detail_proses = mkdp.id_kegiatan_detail_proses')
                ->where('pcl.sobat_id', $sobat_id)
                ->where('mkdp.tanggal_mulai <=', date('Y-m-d'))
                ->where('mkdp.tanggal_selesai >=', date('Y-m-d'))
                ->countAllResults();

            $totalAchievement = $achModel
                ->where('sobat_id', $sobat_id)
                ->countAllResults();

            return $this->respond([
                'status' => 'success',
                'sobat_id' => $sobat_id,
                'total_kegiatan_pcl' => $total,
                'total_kegiatan_pcl_aktif' => $totalAktif,
                'total_achievement' => $totalAchievement
            ]);

        } catch (\Exception $e) {
            return $this->failUnauthorized("Token tidak valid: " . $e->getMessage());
        }
    }
}
