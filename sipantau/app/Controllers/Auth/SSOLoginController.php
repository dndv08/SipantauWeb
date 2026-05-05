<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\AdminSurveiProvinsiModel;
use App\Models\AdminSurveiKabupatenModel;
use App\Libraries\SSOLibrary;

class SSOLoginController extends BaseController
{
    private const MOBILE_ONLY_ROLES = [4, 5];

    public function login()
    {
        log_message('debug', '[SSO] Redirect ke halaman login SSO');

        $sso = new SSOLibrary();
        return redirect()->to($sso->getLoginUrl());
    }

    public function callback()
    {
        $session = session();
        $userModel = new UserModel();
        $sso = new SSOLibrary();

        $code = $this->request->getGet('code');

        log_message('debug', '[SSO] Callback diterima');
        log_message('debug', '[SSO] Authorization Code: ' . ($code ?? 'NULL'));

        if (!$code) {
            log_message('error', '[SSO] Code kosong');
            return redirect()->to('/login')->with('error', 'Login SSO gagal.');
        }

        try {
            /**
             * ============================
             * GET ACCESS TOKEN
             * ============================
             */
            $accessToken = $sso->getAccessToken($code);

            if (!$accessToken) {
                throw new \Exception('Access token kosong');
            }

            log_message('debug', '[SSO] Access token berhasil');

            /**
             * ============================
             * GET USER INFO
             * ============================
             */
            $userInfo = $sso->getUserInfo($accessToken);

            if (!$userInfo || !isset($userInfo['nip'])) {
                log_message('error', '[SSO] UserInfo tidak valid: ' . json_encode($userInfo));
                throw new \Exception('User info tidak valid');
            }

            log_message('debug', '[SSO] UserInfo berhasil diambil');

            /**
             * ============================
             * MAPPING DATA SSO
             * ============================
             */
            $sobatId = trim((string) $userInfo['nip']);
            $nama = $userInfo['name'] ?? '-';
            $email = $userInfo['email'] ?? '-';

            // id_kabupaten dari organisasi (4 digit pertama)
            $idKabupaten = null;
            if (!empty($userInfo['organisasi'])) {
                $idKabupaten = substr($userInfo['organisasi'], 0, 4);
            }

            log_message('debug', "[SSO] sobat_id: {$sobatId}");
            log_message('debug', "[SSO] id_kabupaten: {$idKabupaten}");

            /**
             * ============================
             * TENTUKAN ROLE BERDASARKAN ID_KABUPATEN
             * ============================
             */
            $defaultRole = [];

            if ($idKabupaten == '1400') {
                // Provinsi Aceh (Kode 1400) → Pemantau Provinsi
                $defaultRole = [2];
                log_message('debug', "[SSO] Role ditetapkan: Pemantau Provinsi (2)");
            } elseif ($idKabupaten) {
                // Kabupaten/Kota selain 1400 → Pemantau Kabupaten
                $defaultRole = [3];
                log_message('debug', "[SSO] Role ditetapkan: Pemantau Kabupaten (3)");
            } else {
                // Jika id_kabupaten tidak tersedia, gunakan role default (1)
                $defaultRole = [1];
                log_message('warning', "[SSO] id_kabupaten tidak tersedia, menggunakan role default: Superadmin (1)");
            }

            /**
             * ============================
             * INSERT / UPDATE USER
             * ============================
             */
            $user = $userModel->find($sobatId);

            if (!$user) {
                log_message('debug', "[SSO] Insert user baru: {$sobatId}");

                $userModel->insert([
                    'sobat_id' => $sobatId,
                    'nama_user' => $nama,
                    'email' => $email,
                    'password' => (string)$sobatId,
                    'id_kabupaten' => $idKabupaten,
                    'role' => json_encode($defaultRole),
                    'is_active' => 1,
                    'is_pegawai' => 1,
                ]);
            } else {
                log_message('debug', "[SSO] Update user: {$sobatId}");

                // Update data user, tapi pertahankan role yang sudah ada
                $userModel->update($sobatId, [
                    'nama_user' => $nama,
                    'email' => $email,
                    'id_kabupaten' => $idKabupaten,
                    'is_pegawai' => 1,
                ]);
            }

            // Ambil ulang user (ANTI NULL)
            $user = $userModel->find($sobatId);

            if (!$user) {
                throw new \Exception('User gagal diambil setelah insert/update');
            }

            /**
             * ============================
             * DECODE ROLE USER
             * ============================
             */
            $userRoles = is_string($user['role'])
                ? json_decode($user['role'], true)
                : (is_array($user['role']) ? $user['role'] : [$user['role']]);

            log_message('debug', '[SSO] Role user dari database: ' . json_encode($userRoles));

            /**
             * ============================
             * VALIDASI ROLE WEB
             * ============================
             */
            $webRoles = array_diff($userRoles, self::MOBILE_ONLY_ROLES);

            if (empty($webRoles)) {
                log_message('warning', "[SSO] User {$sobatId} hanya punya role mobile");
                return redirect()->to('/login')->with(
                    'error',
                    'Akun SSO Anda hanya memiliki akses Mobile.'
                );
            }

            /**
             * ============================
             * CEK STATUS ADMIN SURVEI
             * ============================
             */
            $adminProvinsiModel = new AdminSurveiProvinsiModel();
            $adminKabupatenModel = new AdminSurveiKabupatenModel();

            $isAdminProvinsi = $adminProvinsiModel->isAdminProvinsi($user['sobat_id']);
            $isAdminKabupaten = $adminKabupatenModel->isAdminKabupaten($user['sobat_id']);

            $adminProvinsiId = $isAdminProvinsi ? $adminProvinsiModel->getAdminProvinsiId($user['sobat_id']) : null;
            $adminKabupatenId = $isAdminKabupaten ? $adminKabupatenModel->getAdminKabupatenId($user['sobat_id']) : null;

            log_message('debug', "[SSO] Admin Provinsi: " . ($isAdminProvinsi ? 'Ya (ID: ' . $adminProvinsiId . ')' : 'Tidak'));
            log_message('debug', "[SSO] Admin Kabupaten: " . ($isAdminKabupaten ? 'Ya (ID: ' . $adminKabupatenId . ')' : 'Tidak'));

            /**
             * ============================
             * BUILD AVAILABLE ROLES
             * ============================
             */
            $availableRoles = [];

            // 1. Tambahkan role dari tabel user (hanya yang diperbolehkan di web)
            foreach ($webRoles as $roleId) {
                if ($roleId == 1) {
                    $availableRoles[] = ['id' => 1, 'type' => 'superadmin'];
                } elseif ($roleId == 2) {
                    $availableRoles[] = ['id' => 2, 'type' => 'pemantau_provinsi'];
                } elseif ($roleId == 3) {
                    $availableRoles[] = ['id' => 3, 'type' => 'pemantau_kabupaten'];
                }
            }

            // 2. Tambahkan role admin JIKA terdaftar di tabel admin
            if ($isAdminProvinsi && $adminProvinsiId) {
                $availableRoles[] = [
                    'id' => 2,
                    'type' => 'admin_provinsi',
                    'admin_id' => $adminProvinsiId,
                ];
            }

            if ($isAdminKabupaten && $adminKabupatenId) {
                $availableRoles[] = [
                    'id' => 3,
                    'type' => 'admin_kabupaten',
                    'admin_id' => $adminKabupatenId,
                ];
            }

            log_message('debug', '[SSO] Available roles: ' . json_encode($availableRoles));

            /**
             * ============================
             * LOGIN FINAL
             * ============================
             */
            $loginController = new LoginController();

            // 1 role → langsung login
            if (count($availableRoles) === 1) {
                log_message('debug', '[SSO] Auto login dengan 1 role');
                return $loginController->loginWithRole($user, $availableRoles[0]);
            }

            // Multi role → pilih
            log_message('debug', '[SSO] Multi role terdeteksi, redirect ke select-role');

            $session->setTempdata('temp_user_data', [
                'sobat_id' => $user['sobat_id'],
                'nama_user' => $user['nama_user'],
                'email' => $user['email'],
                'roles' => $availableRoles,
            ], 300);

            return redirect()->to('/login/select-role');

        } catch (\Throwable $e) {
            log_message('critical', '[SSO] Exception: ' . $e->getMessage());
            log_message('critical', '[SSO] Trace: ' . $e->getTraceAsString());

            return redirect()->to('/login')->with('error', 'Login SSO gagal: ' . $e->getMessage());
        }
    }
}