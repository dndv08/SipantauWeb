<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\AdminSurveiProvinsiModel;
use App\Models\AdminSurveiKabupatenModel;

class LoginController extends BaseController
{
    // Role yang diperbolehkan login ke web
    private const ALLOWED_WEB_ROLES = [1, 2, 3, 4]; // Super Admin, Pemantau Provinsi, Pemantau Kabupaten, Petugas
    private const MOBILE_ONLY_ROLES = [5]; // Role 5 = lainnya (mobile only)

    public function Home()
    {
        return view('auth/home');
    }
    public function index()
    {
        $session = session();

        // Jika user sudah login, arahkan langsung ke dashboard sesuai role
        if ($session->get('isLoggedIn')) {
            return $this->redirectToDashboard($session->get('role'), $session->get('role_type'));
        }

        return view('auth/login');
    }

    public function process()
    {
        $session = session();
        $userModel = new UserModel();

        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        // Cari user berdasarkan email
        $user = $userModel->where('email', $email)->first();

        // Validasi user
        if (!$user) {
            $session->setFlashdata('error', 'Email tidak ditemukan.');
            return redirect()->back()->withInput();
        }

        if (!password_verify($password, $user['password'])) {
            $session->setFlashdata('error', 'Password salah.');
            return redirect()->back()->withInput();
        }

        if ($user['is_active'] == 0) {
            $session->setFlashdata('error', 'Akun belum aktif.');
            return redirect()->back()->withInput();
        }

        // --- Decode roles ---
        $roleValue = $user['role'];
        $userRoles = [];

        if (is_string($roleValue) && (str_starts_with($roleValue, '[') || str_starts_with($roleValue, '{'))) {
            $decoded = json_decode($roleValue, true);
            if (is_array($decoded)) {
                $userRoles = array_map('intval', $decoded);
            }
        } else {
            $userRoles = [(int) $roleValue];
        }

        // VALIDASI: Cek apakah user punya role yang diperbolehkan login ke web
        $hasWebRole = false;
        foreach ($userRoles as $roleId) {
            if (in_array($roleId, self::ALLOWED_WEB_ROLES)) {
                $hasWebRole = true;
                break;
            }
        }

        // VALIDASI: Cek apakah semua role adalah mobile-only
        $allRolesAreMobileOnly = true;
        foreach ($userRoles as $roleId) {
            if (!in_array($roleId, self::MOBILE_ONLY_ROLES)) {
                $allRolesAreMobileOnly = false;
                break;
            }
        }

        // Jika semua role adalah mobile-only (Petugas/Role 4 atau Role 5)
        if ($allRolesAreMobileOnly) {
            $session->setFlashdata('error', 'Anda tidak memiliki akses ke Web. Akun Anda hanya dapat digunakan pada aplikasi mobile. Silakan login melalui aplikasi mobile.');
            return redirect()->back()->withInput();
        }

        // --- Check admin status ---
        $adminProvinsiModel = new AdminSurveiProvinsiModel();
        $adminKabupatenModel = new AdminSurveiKabupatenModel();

        $isAdminProvinsi = $adminProvinsiModel->isAdminProvinsi($user['sobat_id']);
        $isAdminKabupaten = $adminKabupatenModel->isAdminKabupaten($user['sobat_id']);

        // Get admin IDs
        $adminProvinsiId = $isAdminProvinsi ? $adminProvinsiModel->getAdminProvinsiId($user['sobat_id']) : null;
        $adminKabupatenId = $isAdminKabupaten ? $adminKabupatenModel->getAdminKabupatenId($user['sobat_id']) : null;

        // Build available roles (hanya yang diperbolehkan di web)
        $availableRoles = [];

        // 1. Tambahkan role dari tabel user (hanya yang diperbolehkan login ke web)
        foreach ($userRoles as $roleId) {
            // Skip role yang tidak diperbolehkan login ke web
            if (in_array($roleId, self::MOBILE_ONLY_ROLES)) {
                continue;
            }

            if ($roleId == 1) {
                // Super Admin
                $availableRoles[] = [
                    'id' => 1,
                    'type' => 'superadmin',
                ];
            } elseif ($roleId == 2) {
                // Pemantau Provinsi (role asli)
                $availableRoles[] = [
                    'id' => 2,
                    'type' => 'pemantau_provinsi',
                ];
            } elseif ($roleId == 3) {
                // Pemantau Kabupaten (role asli)
                $availableRoles[] = [
                    'id' => 3,
                    'type' => 'pemantau_kabupaten',
                ];
            } elseif ($roleId == 4) {
                // Petugas Survei (PCL/PML)
                $availableRoles[] = [
                    'id' => 4,
                    'type' => 'petugas',
                ];
            }
        }

        // 2. Tambahkan role admin JIKA terdaftar di tabel admin (role tambahan)
        if ($isAdminProvinsi && $adminProvinsiId) {
            // Tambahkan opsi Admin Survei Provinsi
            $availableRoles[] = [
                'id' => 2,
                'type' => 'admin_provinsi',
                'admin_id' => $adminProvinsiId,
            ];
        }

        if ($isAdminKabupaten && $adminKabupatenId) {
            // Tambahkan opsi Admin Survei Kabupaten
            $availableRoles[] = [
                'id' => 3,
                'type' => 'admin_kabupaten',
                'admin_id' => $adminKabupatenId,
            ];
        }

        // Jika tidak ada role yang valid untuk web
        if (empty($availableRoles)) {
            $session->setFlashdata('error', 'Anda tidak memiliki akses ke sistem web. Hubungi administrator atau gunakan aplikasi mobile.');
            return redirect()->back();
        }

        // Jika user hanya punya 1 role valid, langsung login
        if (count($availableRoles) === 1) {
            return $this->loginWithRole($user, $availableRoles[0]);
        }

        // Jika multi-role, simpan data sementara dan tampilkan halaman pemilihan role
        $session->setTempdata(
            'temp_user_data',
            [
                'sobat_id' => $user['sobat_id'],
                'nama_user' => $user['nama_user'],
                'email' => $user['email'],
                'roles' => $availableRoles,
            ],
            300 // Expire dalam 5 menit
        );

        return redirect()->to('/login/select-role');
    }

    // Halaman pemilihan role untuk user dengan multi-role
    public function selectRole()
    {
        $session = session();
        $tempUserData = $session->getTempdata('temp_user_data');

        if (!$tempUserData) {
            return redirect()->to('/login')->with('error', 'Session expired. Silakan login kembali.');
        }

        // Get role details dengan label yang sesuai
        $roleModel = new RoleModel();
        $processedRoles = [];

        foreach ($tempUserData['roles'] as $roleData) {
            $roleId = $roleData['id'];
            $roleType = $roleData['type'];

            // Get basic role info
            $roleInfo = $roleModel->find($roleId);

            if ($roleInfo) {
                // Customize nama role berdasarkan tipe
                if ($roleType === 'superadmin') {
                    $roleInfo['roleuser'] = 'Super Admin';
                    $roleInfo['keterangan'] = 'Mengelola seluruh sistem';
                } elseif ($roleType === 'admin_provinsi') {
                    $roleInfo['roleuser'] = 'Admin Survei Provinsi';
                    $roleInfo['keterangan'] = 'Mengelola survei tingkat provinsi';
                } elseif ($roleType === 'pemantau_provinsi') {
                    $roleInfo['roleuser'] = 'Pemantau Provinsi';
                    $roleInfo['keterangan'] = 'Melihat data provinsi';
                } elseif ($roleType === 'admin_kabupaten') {
                    $roleInfo['roleuser'] = 'Admin Survei Kabupaten';
                    $roleInfo['keterangan'] = 'Mengelola survei kabupaten/kota';
                } elseif ($roleType === 'pemantau_kabupaten') {
                    $roleInfo['roleuser'] = 'Pemantau Kabupaten';
                    $roleInfo['keterangan'] = 'Melihat data kabupaten';
                } elseif ($roleType === 'petugas') {
                    $roleInfo['roleuser'] = 'Petugas Survei';
                    $roleInfo['keterangan'] = 'PCL / PML - Petugas lapangan survei';
                }

                $roleInfo['role_type'] = $roleType;
                $roleInfo['admin_id'] = $roleData['admin_id'] ?? null;
                $processedRoles[] = $roleInfo;
            }
        }

        $data = [
            'user' => $tempUserData,
            'roles' => $processedRoles,
        ];

        return view('auth/select_role', $data);
    }

    // Process role selection
    public function processRoleSelection()
    {
        $session = session();
        $selectedRoleId = (int) $this->request->getPost('selected_role');
        $selectedRoleType = $this->request->getPost('selected_role_type');

        $tempUserData = $session->getTempdata('temp_user_data');

        if (!$tempUserData) {
            return redirect()->to('/login')->with('error', 'Session expired. Silakan login kembali.');
        }

        // Validasi apakah role yang dipilih valid untuk user ini
        $selectedRoleData = null;
        foreach ($tempUserData['roles'] as $role) {
            if ($role['id'] == $selectedRoleId && $role['type'] == $selectedRoleType) {
                $selectedRoleData = $role;
                break;
            }
        }

        if (!$selectedRoleData) {
            return redirect()->back()->with('error', 'Role tidak valid.');
        }

        // Get full user data
        $userModel = new UserModel();
        $user = $userModel->find($tempUserData['sobat_id']);

        // Login dengan role yang dipilih
        return $this->loginWithRole($user, $selectedRoleData);
    }

    // Login user dengan role tertentu
    public function loginWithRole($user, $roleData)
    {
        $session = session();
        $roleId = $roleData['id'];
        $roleType = $roleData['type'];

        // Hitung total available roles (untuk switch role feature)
        $userRoles = is_string($user['role']) ? json_decode($user['role'], true) : [$user['role']];
        $webRoles = array_filter($userRoles, function ($r) {
            return !in_array($r, self::MOBILE_ONLY_ROLES);
        });

        $totalRoles = count($webRoles);

        // Cek admin
        $adminProvinsiModel = new AdminSurveiProvinsiModel();
        $adminKabupatenModel = new AdminSurveiKabupatenModel();

        if ($adminProvinsiModel->isAdminProvinsi($user['sobat_id'])) {
            $totalRoles++;
        }
        if ($adminKabupatenModel->isAdminKabupaten($user['sobat_id'])) {
            $totalRoles++;
        }

        // Set session dasar
        $sessionData = [
            'user_id' => $user['sobat_id'],
            'sobat_id' => $user['sobat_id'],
            'nama_user' => $user['nama_user'],
            'email' => $user['email'],
            'role' => (int) $roleId,
            'role_type' => $roleType,
            'all_roles' => $userRoles,
            'total_available_roles' => $totalRoles, // TAMBAHKAN INI
            'user_kabupaten_id' => $user['id_kabupaten'] ?? null,
            'isLoggedIn' => true,
        ];

        // Tambahkan admin_id ke session jika tipe admin
        if ($roleType === 'admin_provinsi' && isset($roleData['admin_id'])) {
            $sessionData['id_admin_provinsi'] = $roleData['admin_id'];
            log_message('info', 'Login Admin Provinsi - ID: ' . $roleData['admin_id']);
        } elseif ($roleType === 'admin_kabupaten' && isset($roleData['admin_id'])) {
            $sessionData['id_admin_kabupaten'] = $roleData['admin_id'];
            log_message('info', 'Login Admin Kabupaten - ID: ' . $roleData['admin_id']);
        }

        $session->set($sessionData);
        log_message('info', 'Session Data After Login: ' . json_encode($sessionData));

        // Hapus temporary data
        $session->removeTempdata('temp_user_data');

        return $this->redirectToDashboard($roleId, $roleType);
    }

    // Helper untuk redirect ke dashboard
    private function redirectToDashboard($role, $roleType = 'default')
    {
        switch ($role) {
            case 1:
                return redirect()->to('/superadmin');

            case 2:
                if ($roleType === 'admin_provinsi') {
                    return redirect()->to('/adminsurvei');
                }
                // Pemantau Provinsi
                return redirect()->to('/pemantau-provinsi');

            case 3:
                if ($roleType === 'admin_kabupaten') {
                    return redirect()->to('/adminsurvei-kab');
                }
                // Pemantau Kabupaten
                return redirect()->to('/pemantau-kabupaten');

            case 4:
                // Petugas Survei (PCL / PML)
                return redirect()->to('/petugas');

            default:
                session()->setFlashdata('error', 'Role tidak dikenali atau tidak memiliki akses ke sistem web.');
                return redirect()->to('/login');
        }
    }

    // Switch role untuk user yang sedang login
    public function switchRole()
    {
        $session = session();

        if (!$session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        // Get user data
        $userModel = new UserModel();
        $user = $userModel->find($session->get('user_id'));

        // Get all roles dari database user
        $allRoles = is_string($user['role']) ? json_decode($user['role'], true) : [$user['role']];

        // Check admin status
        $adminProvinsiModel = new AdminSurveiProvinsiModel();
        $adminKabupatenModel = new AdminSurveiKabupatenModel();

        $isAdminProvinsi = $adminProvinsiModel->isAdminProvinsi($user['sobat_id']);
        $isAdminKabupaten = $adminKabupatenModel->isAdminKabupaten($user['sobat_id']);

        // Get admin IDs
        $adminProvinsiId = $isAdminProvinsi ? $adminProvinsiModel->getAdminProvinsiId($user['sobat_id']) : null;
        $adminKabupatenId = $isAdminKabupaten ? $adminKabupatenModel->getAdminKabupatenId($user['sobat_id']) : null;

        // Build available roles (hanya yang diperbolehkan di web)
        $availableRoles = [];

        // 1. Tambahkan role dari tabel user (hanya yang diperbolehkan login ke web)
        foreach ($allRoles as $roleId) {
            // Skip role mobile-only
            if (in_array($roleId, self::MOBILE_ONLY_ROLES)) {
                continue;
            }

            if ($roleId == 1) {
                $availableRoles[] = [
                    'id' => 1,
                    'type' => 'superadmin',
                ];
            } elseif ($roleId == 2) {
                $availableRoles[] = [
                    'id' => 2,
                    'type' => 'pemantau_provinsi',
                ];
            } elseif ($roleId == 3) {
                $availableRoles[] = [
                    'id' => 3,
                    'type' => 'pemantau_kabupaten',
                ];
            } elseif ($roleId == 4) {
                $availableRoles[] = [
                    'id' => 4,
                    'type' => 'petugas',
                ];
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

        // Jika hanya 1 role, redirect back dengan pesan
        if (count($availableRoles) <= 1) {
            return redirect()->back()->with('info', 'Anda hanya memiliki satu role.');
        }

        // Get role details dengan label yang sesuai
        $roleModel = new RoleModel();
        $processedRoles = [];

        foreach ($availableRoles as $roleData) {
            $roleId = $roleData['id'];
            $roleType = $roleData['type'];

            $roleInfo = $roleModel->find($roleId);

            if ($roleInfo) {
                if ($roleType === 'superadmin') {
                    $roleInfo['roleuser'] = 'Super Admin';
                    $roleInfo['keterangan'] = 'Mengelola seluruh sistem';
                } elseif ($roleType === 'admin_provinsi') {
                    $roleInfo['roleuser'] = 'Admin Survei Provinsi';
                    $roleInfo['keterangan'] = 'Mengelola survei tingkat provinsi';
                } elseif ($roleType === 'pemantau_provinsi') {
                    $roleInfo['roleuser'] = 'Pemantau Provinsi';
                    $roleInfo['keterangan'] = 'Melihat data provinsi';
                } elseif ($roleType === 'admin_kabupaten') {
                    $roleInfo['roleuser'] = 'Admin Survei Kabupaten';
                    $roleInfo['keterangan'] = 'Mengelola survei kabupaten/kota';
                } elseif ($roleType === 'pemantau_kabupaten') {
                    $roleInfo['roleuser'] = 'Pemantau Kabupaten';
                    $roleInfo['keterangan'] = 'Melihat data kabupaten';
                } elseif ($roleType === 'petugas') {
                    $roleInfo['roleuser'] = 'Petugas Survei';
                    $roleInfo['keterangan'] = 'PCL / PML - Petugas lapangan survei';
                }

                $roleInfo['role_type'] = $roleType;
                $roleInfo['admin_id'] = $roleData['admin_id'] ?? null;
                $processedRoles[] = $roleInfo;
            }
        }

        $data = [
            'roles' => $processedRoles,
            'current_role' => $session->get('role'),
            'current_role_type' => $session->get('role_type'),
        ];

        return view('auth/switch_role', $data);
    }

    // Process switch role
    public function processSwitchRole()
    {
        $session = session();
        $selectedRoleId = (int) $this->request->getPost('selected_role');
        $selectedRoleType = $this->request->getPost('selected_role_type');

        if (!$session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        // Get user data dari database untuk validasi
        $userModel = new UserModel();
        $user = $userModel->find($session->get('user_id'));

        // Get all roles dari database
        $allRoles = is_string($user['role']) ? json_decode($user['role'], true) : [$user['role']];

        // Check admin status
        $adminProvinsiModel = new AdminSurveiProvinsiModel();
        $adminKabupatenModel = new AdminSurveiKabupatenModel();

        $isAdminProvinsi = $adminProvinsiModel->isAdminProvinsi($user['sobat_id']);
        $isAdminKabupaten = $adminKabupatenModel->isAdminKabupaten($user['sobat_id']);

        // Build available roles untuk validasi (hanya web)
        $availableRoles = [];

        foreach ($allRoles as $roleId) {
            // Skip mobile-only roles
            if (in_array($roleId, self::MOBILE_ONLY_ROLES)) {
                continue;
            }

            if ($roleId == 1) {
                $availableRoles[] = ['id' => 1, 'type' => 'superadmin'];
            } elseif ($roleId == 2) {
                $availableRoles[] = ['id' => 2, 'type' => 'pemantau_provinsi'];
            } elseif ($roleId == 3) {
                $availableRoles[] = ['id' => 3, 'type' => 'pemantau_kabupaten'];
            } elseif ($roleId == 4) {
                $availableRoles[] = ['id' => 4, 'type' => 'petugas'];
            }
        }

        if ($isAdminProvinsi) {
            $availableRoles[] = [
                'id' => 2,
                'type' => 'admin_provinsi',
                'admin_id' => $adminProvinsiModel->getAdminProvinsiId($user['sobat_id'])
            ];
        }

        if ($isAdminKabupaten) {
            $availableRoles[] = [
                'id' => 3,
                'type' => 'admin_kabupaten',
                'admin_id' => $adminKabupatenModel->getAdminKabupatenId($user['sobat_id'])
            ];
        }

        // Validasi apakah kombinasi role ID dan type valid
        $isValidRole = false;
        $adminId = null;

        foreach ($availableRoles as $role) {
            if ($role['id'] == $selectedRoleId && $role['type'] == $selectedRoleType) {
                $isValidRole = true;
                $adminId = $role['admin_id'] ?? null;
                break;
            }
        }

        if (!$isValidRole) {
            log_message('error', 'Invalid role switch attempt - Role ID: ' . $selectedRoleId . ', Type: ' . $selectedRoleType);
            return redirect()->back()->with('error', 'Role tidak valid untuk user ini.');
        }

        // Update session berdasarkan role type
        if ($selectedRoleType === 'admin_provinsi') {
            $session->set('id_admin_provinsi', $adminId);
            $session->remove('id_admin_kabupaten');
            log_message('info', 'Switch to Admin Provinsi - ID: ' . $adminId);
        } elseif ($selectedRoleType === 'admin_kabupaten') {
            $session->set('id_admin_kabupaten', $adminId);
            $session->remove('id_admin_provinsi');
            log_message('info', 'Switch to Admin Kabupaten - ID: ' . $adminId);
        } else {
            // Remove admin IDs untuk role pemantau biasa
            $session->remove('id_admin_provinsi');
            $session->remove('id_admin_kabupaten');
            log_message('info', 'Switch to non-admin role');
        }

        // Update session role
        $session->set('role', $selectedRoleId);
        $session->set('role_type', $selectedRoleType);

        log_message('info', 'Role switched successfully to: ' . $selectedRoleId . ' (' . $selectedRoleType . ')');

        // Redirect ke dashboard sesuai role baru
        return $this->redirectToDashboard($selectedRoleId, $selectedRoleType);
    }

    public function logout()
    {
        $session = session();

        // Ambil token jika ingin (opsional, tapi berguna)
        $accessToken = $session->get('access_token');

        // Destroy session lokal
        $session->destroy();

        // Redirect ke logout SSO
        $sso = new \App\Libraries\SSOLibrary();

        return redirect()->to(
            $sso->getLogoutUrl(base_url('home'))
        );
    }

}