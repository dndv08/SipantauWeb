<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\PCLModel;
use App\Models\SipantauTransaksiModel;

class PelaporanController extends BaseController
{
    use ResponseTrait;

    private $jwtKey;

    public function __construct()
    {
        $this->jwtKey = getenv('JWT_SECRET_KEY');
    }

    /**
     * 🟢 GET /api/pelaporan
     * Menampilkan semua laporan berdasarkan user (PCL) yang login
     */
    public function index()
    {
        $authHeader = $this->request->getHeaderLine('Authorization');
        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $this->failUnauthorized('Token tidak ditemukan');
        }

        try {
            // 🔐 Decode token JWT, tetap wajib login
            $decoded = JWT::decode($matches[1], new Key($this->jwtKey, 'HS256'));
            $sobat_id = $decoded->data->sobat_id;

            // 🔍 Model transaksi
            $transaksiModel = new \App\Models\SipantauTransaksiModel();

            // 🔧 Query semua laporan tanpa filter role
            $builder = $transaksiModel
                ->select("
                    sipantau_transaksi.id_sipantau_transaksi,
                    sipantau_transaksi.resume,
                    sipantau_transaksi.latitude,
                    sipantau_transaksi.longitude,
                    sipantau_transaksi.imagepath,
                    sipantau_transaksi.created_at,
                    mk.nama_kegiatan,
                    mkdp.nama_kegiatan_detail_proses,
                    kab.nama_kabupaten,
                    kec.nama_kecamatan,
                    des.nama_desa
                ")
                ->join('pcl', 'pcl.id_pcl = sipantau_transaksi.id_pcl', 'left')
                ->join('pml', 'pml.id_pml = COALESCE(pcl.id_pml, sipantau_transaksi.id_pml)', 'left')
                ->join('kegiatan_wilayah kw', 'kw.id_kegiatan_wilayah = pml.id_kegiatan_wilayah', 'left')
                ->join('master_kegiatan_detail_proses mkdp', 'mkdp.id_kegiatan_detail_proses = sipantau_transaksi.id_kegiatan_detail_proses')
                ->join('master_kegiatan_detail mkd', 'mkd.id_kegiatan_detail = mkdp.id_kegiatan_detail')
                ->join('master_kegiatan mk', 'mk.id_kegiatan = mkd.id_kegiatan')
                ->join('master_kabupaten kab', 'kab.id_kabupaten = kw.id_kabupaten', 'left')
                ->join('master_kecamatan kec', 'kec.id_kecamatan = sipantau_transaksi.id_kecamatan', 'left')
                ->join('master_desa des', 'des.id_desa = sipantau_transaksi.id_desa', 'left')
                ->orderBy('sipantau_transaksi.created_at', 'DESC');

            // 🔹 Ambil parameter filter opsional
            $filterIdPcl = $this->request->getGet('id_pcl');
            if (!empty($filterIdPcl)) {
                $builder->where('sipantau_transaksi.id_pcl', $filterIdPcl);
            }

            $filterIdPml = $this->request->getGet('id_pml');
            if (!empty($filterIdPml)) {
                $builder->where('sipantau_transaksi.id_pml', $filterIdPml);
            }

            $laporan = $builder->findAll();

            // Ubah imagepath jadi URL lengkap
            foreach ($laporan as &$item) {
                $item['image_url'] = !empty($item['imagepath'])
                    ? base_url($item['imagepath'])
                    : null;
            }

            return $this->respond([
                'status' => 'success',
                'total_laporan' => count($laporan),
                'data' => $laporan
            ]);

        } catch (\Firebase\JWT\ExpiredException $e) {
            return $this->failUnauthorized('Token kadaluarsa');
        } catch (\Exception $e) {
            return $this->failUnauthorized('Token tidak valid: ' . $e->getMessage());
        }
    }

    /**
     * POST /api/pelaporan
     * Membuat laporan baru oleh PCL
     * OPTIMIZED: Mengurangi waktu proses kompresi gambar
     */
    public function create()
    {
        $authHeader = $this->request->getHeaderLine('Authorization');
        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $this->failUnauthorized('Token tidak ditemukan');
        }

        try {
            // 🔐 Decode token JWT
            $decoded = \Firebase\JWT\JWT::decode($matches[1], new \Firebase\JWT\Key($this->jwtKey, 'HS256'));
            $sobat_id = $decoded->data->sobat_id;

            // Ambil data dari multipart form
            $data = $this->request->getPost();
            $image = $this->request->getFile('image');

            $idPcl = $data['id_pcl'] ?? null;
            $idPml = $data['id_pml'] ?? null;

            // 🧩 Validasi input wajib
            if ((empty($idPcl) && empty($idPml)) || empty($data['id_kegiatan_detail_proses']) || empty($data['resume'])) {
                return $this->failValidationErrors('id_pcl atau id_pml, kegiatan, dan resume wajib diisi.');
            }

            // 🔎 Verifikasi kepemilikan
            if (!empty($idPcl)) {
                $pclModel = new \App\Models\PCLModel();
                $pcl = $pclModel
                    ->where('id_pcl', $idPcl)
                    ->where('sobat_id', $sobat_id)
                    ->first();

                if (!$pcl) {
                    return $this->failUnauthorized('id_pcl tidak valid atau bukan milik user ini.');
                }
            }

            if (!empty($idPml)) {
                $pmlModel = new \App\Models\PMLModel();
                $pml = $pmlModel
                    ->where('id_pml', $idPml)
                    ->where('sobat_id', $sobat_id)
                    ->first();

                if (!$pml) {
                    return $this->failUnauthorized('id_pml tidak valid atau bukan milik user ini.');
                }
            }

            // 📸 Proses upload gambar (OPTIMIZED)
            $imagePath = '';
            if ($image && $image->isValid() && !$image->hasMoved()) {
                $uploadDir = FCPATH . 'uploads/laporan/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $newName = $image->getRandomName();
                $finalPath = $uploadDir . $newName;

                // Langsung move file (Android sudah kompres)
                $image->move($uploadDir, $newName);

                // Cek ukuran file setelah di-move
                $fileSize = filesize($finalPath);
                
                // Hanya kompres jika file masih > 200KB
                if ($fileSize > 200 * 1024) {
                    try {
                        $imageService = \Config\Services::image()->withFile($finalPath);
                        
                        // Single compression dengan quality 70
                        $imageService->save($finalPath, 70);
                        
                        $newSize = filesize($finalPath);
                        log_message('info', 'File dikompres dari ' . round($fileSize / 1024, 2) . 'KB ke ' . round($newSize / 1024, 2) . 'KB');
                    } catch (\Exception $e) {
                        // Jika kompresi gagal, tetap lanjutkan dengan file original
                        log_message('error', 'Kompresi gagal: ' . $e->getMessage());
                    }
                } else {
                    log_message('info', 'File sudah optimal: ' . round($fileSize / 1024, 2) . 'KB, skip kompresi');
                }

                // Simpan path relatif untuk database
                $imagePath = 'uploads/laporan/' . $newName;

            } else {
                log_message('warning', 'Tidak ada file image yang diupload atau tidak valid.');
            }

            // 💾 Simpan data ke database
            $transaksiModel = new \App\Models\SipantauTransaksiModel();
            $insertData = [
                'id_pcl' => !empty($idPcl) ? $idPcl : null,
                'id_pml' => !empty($idPml) ? $idPml : null,
                'id_kegiatan_detail_proses' => $data['id_kegiatan_detail_proses'],
                'resume' => $data['resume'],
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'id_kecamatan' => $data['id_kecamatan'] ?? null,
                'id_desa' => $data['id_desa'] ?? null,
                'id_sls' => $data['id_sls'] ?? null,
                'id_subsls' => $data['id_subsls'] ?? null,
                'imagepath' => $imagePath,
                'created_at' => !empty($data['created_at']) ? $data['created_at'] : date('Y-m-d H:i:s')
            ];

            $transaksiModel->insert($insertData);

            return $this->respondCreated([
                'status' => 'success',
                'message' => 'Pelaporan berhasil disimpan (≤100KB).',
                'data' => $insertData
            ]);

        } catch (\Firebase\JWT\ExpiredException $e) {
            return $this->failUnauthorized('Token kadaluarsa');
        } catch (\Exception $e) {
            return $this->failUnauthorized('Token tidak valid: ' . $e->getMessage());
        }
    }

    /**
     * DELETE /api/pelaporan/{id}
     * Menghapus laporan milik sendiri dan file gambarnya
     */
    public function delete($id = null)
    {
        $authHeader = $this->request->getHeaderLine('Authorization');
        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $this->failUnauthorized('Token tidak ditemukan');
        }

        try {
            // ✅ Tetap decode token agar endpoint tidak bisa diakses tanpa login
            $decoded = JWT::decode($matches[1], new Key($this->jwtKey, 'HS256'));

            $transaksiModel = new SipantauTransaksiModel();
            $laporan = $transaksiModel->find($id);

            if (!$laporan) {
                return $this->failNotFound('Laporan tidak ditemukan');
            }

            // 🧹 Hapus file gambar jika ada
            if (!empty($laporan['imagepath'])) {
                $filePath = FCPATH . $laporan['imagepath'];

                if (file_exists($filePath)) {
                    unlink($filePath);
                    log_message('info', 'File dihapus: ' . $filePath);
                } else {
                    log_message('warning', 'File tidak ditemukan: ' . $filePath);
                }
            }

            // 🗑️ Hapus data dari database
            $transaksiModel->delete($id);

            return $this->respondDeleted([
                'status' => 'success',
                'message' => 'Laporan dan file gambar berhasil dihapus'
            ]);

        } catch (\Firebase\JWT\ExpiredException $e) {
            return $this->failUnauthorized('Token kadaluarsa');
        } catch (\Exception $e) {
            return $this->failUnauthorized('Token tidak valid: ' . $e->getMessage());
        }
    }
}