<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SiPantau - <?= $title ?? 'Dashboard' ?></title>
    <link rel="shortcut icon" type="image/png" href="<?= base_url('assets/gambar/LOGO_BPS.png') ?>">

    <!-- Google Fonts - Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Tailwind CSS -->
    <link href="<?= base_url('assets/css/output.css') ?>" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
</head>

<body class="bg-gray-50">

    <!-- Sidebar -->
    <aside id="sidebar"
        class="fixed top-0 left-0 z-40 w-64 h-screen transition-transform -translate-x-full lg:translate-x-0">
        <div class="h-full flex flex-col bg-white border-r border-gray-200">
            <!-- Logo -->
            <div class="h-20 border-b border-gray-200 px-6 py-4">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                        <img src="<?= base_url('assets/gambar/LOGO_BPS.png') ?>" alt="Logo BPS"
                            class="w-12 h-12 object-contain" />
                    </div>
                    <div class="flex flex-col">
                        <span class="text-xl font-bold text-gray-900 leading-tight">SiPantau</span>
                        <span class="text-xs text-gray-600 leading-tight">Badan Pusat Statistik</span>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto scrollbar-thin py-4 px-3 pb-20">
                <div class="space-y-1">
                    <!-- Dashboard -->
                    <a href="<?= base_url('adminsurvei') ?>"
                        class="sidebar-link <?= ($active_menu ?? '') == 'dashboard' ? 'active' : '' ?>">
                        <i class="fas fa-th-large w-5"></i>
                        <span class="ml-3">Dashboard</span>
                    </a>

                    <!-- Divider -->
                    <div class="py-2">
                        <div class="border-t border-gray-200"></div>
                    </div>

                    <!-- Master Data Menu -->
                    <div class="space-y-1">
                        <button onclick="toggleSubmenu('task')" class="sidebar-link w-full justify-between">
                            <div class="flex items-center">
                                <i class="fas fa-database w-5"></i>
                                <span class="ml-3">Master Data</span>
                            </div>
                            <i class="fas fa-chevron-down text-xs transition-transform duration-200" id="task-icon"></i>
                        </button>
                        <div id="task-submenu" class="hidden ml-8 space-y-1">
                            <a href="<?= base_url('adminsurvei/master-kegiatan-detail-proses') ?>"
                                class="sidebar-link text-sm <?= ($active_menu ?? '') == 'master-kegiatan-detail-proses' ? 'active' : '' ?>">
                                <i class="fas fa-bullseye w-4 mr-2"></i>
                                <span>Master Kegiatan Detail Proses</span>
                            </a>
                            <a href="<?= base_url('adminsurvei/master-kegiatan-wilayah/') ?>"
                                class="sidebar-link text-sm <?= ($active_menu ?? '') == 'master-kegiatan-wilayah' ? 'active' : '' ?>">
                                <i class="fas fa-clipboard-list w-4 mr-2"></i>
                                <span>Master Kegiatan Wilayah</span>
                            </a>
                        </div>
                    </div>

                    <!-- Divider -->
                    <div class="py-2">
                        <div class="border-t border-gray-200"></div>
                    </div>

                    <!-- Kelola Pengguna -->
                    <a href="<?= base_url('adminsurvei/admin-survei-kab') ?>"
                        class="sidebar-link <?= ($active_menu ?? '') == 'assign-admin-kab' ? 'active' : '' ?>">
                        <i class="fas fa-users w-5"></i>
                        <span class="ml-3">Assign Admin Survei Kab</span>
                    </a>

                </div>
            </nav>

            <!-- Logout Button - Fixed at Bottom -->
            <div class="absolute bottom-0 left-0 right-0 p-4 bg-white border-t border-gray-200">
                <a href="<?= base_url('logout') ?>"
                    class="sidebar-link text-red-600 hover:bg-red-50 border border-red-200">
                    <i class="fas fa-sign-out-alt w-5"></i>
                    <span class="ml-3">Log Out</span>
                </a>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="lg:ml-64">
        <!-- Header -->
        <header class="bg-white border-b border-gray-200 sticky top-0 z-30">
            <div class="px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">

                    <!-- Mobile Menu -->
                    <button onclick="toggleSidebar()" class="lg:hidden p-2 text-gray-600 hover:bg-gray-100 rounded-lg">
                        <i class="fas fa-bars text-xl"></i>
                    </button>

                    <!-- Right Section -->
                    <div class="flex items-center space-x-4 ml-auto">
                        <div class="relative">
                            <button onclick="toggleUserMenu()"
                                class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-100 transition-colors">
                                <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center">
                                    <?php
                                    $namaUser = session()->get('nama_user') ?? 'User';
                                    $initials = '';
                                    $nameParts = explode(' ', $namaUser);
                                    if (count($nameParts) >= 2) {
                                        $initials = strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1));
                                    } else {
                                        $initials = strtoupper(substr($namaUser, 0, 2));
                                    }
                                    ?>
                                    <span class="text-white text-sm font-medium"><?= $initials ?></span>
                                </div>
                                <div class="hidden sm:block text-left">
                                    <p class="text-sm font-medium text-gray-900"><?= esc($namaUser) ?></p>
                                    <p class="text-xs text-gray-500">
                                        <?php
                                        $roleType = session()->get('role_type');
                                        $roleName = '';

                                        switch ($roleType) {
                                            case 'superadmin':
                                                $roleName = 'Super Admin';
                                                break;
                                            case 'admin_provinsi':
                                                $roleName = 'Admin Survei Provinsi';
                                                break;
                                            case 'pemantau_provinsi':
                                                $roleName = 'Pemantau Provinsi';
                                                break;
                                            case 'admin_kabupaten':
                                                $roleName = 'Admin Survei Kabupaten';
                                                break;
                                            case 'pemantau_kabupaten':
                                                $roleName = 'Pemantau Kabupaten';
                                                break;
                                            default:
                                                $roleName = 'User';
                                        }
                                        echo esc($roleName);
                                        ?>
                                    </p>
                                </div>
                                <i class="fas fa-chevron-down text-xs text-gray-500 hidden sm:block"></i>
                            </button>

                            <!-- Dropdown -->
                            <div id="userMenu"
                                class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1">
                                <div class="px-4 py-3 border-b border-gray-200">
                                    <p class="text-sm font-medium text-gray-900"><?= esc($namaUser) ?></p>
                                    <p class="text-xs text-gray-500"><?= esc(session()->get('email') ?? '-') ?></p>
                                </div>

                                <?php
                                // Hitung total available roles (termasuk admin)
                                $userId = session()->get('user_id');

                                if ($userId) {
                                    $userModel = new \App\Models\UserModel();
                                    $adminProvinsiModel = new \App\Models\AdminSurveiProvinsiModel();
                                    $adminKabupatenModel = new \App\Models\AdminSurveiKabupatenModel();

                                    $user = $userModel->find($userId);
                                    $allRoles = is_string($user['role']) ?? false ? json_decode($user['role'], true) : [$user['role']];

                                    // Filter hanya role web (exclude mobile-only: role 4 dan 5)
                                    $MOBILE_ONLY_ROLES = [4, 5];
                                    $webRoles = array_filter($allRoles, function ($roleId) use ($MOBILE_ONLY_ROLES) {
                                        return !in_array($roleId, $MOBILE_ONLY_ROLES);
                                    });

                                    $totalAvailableRoles = count($webRoles);

                                    // Tambah 1 jika terdaftar sebagai Admin Provinsi
                                    if ($adminProvinsiModel->isAdminProvinsi($userId)) {
                                        $totalAvailableRoles++;
                                    }

                                    // Tambah 1 jika terdaftar sebagai Admin Kabupaten
                                    if ($adminKabupatenModel->isAdminKabupaten($userId)) {
                                        $totalAvailableRoles++;
                                    }

                                    // Tampilkan switch role jika punya lebih dari 1 role
                                    if ($totalAvailableRoles > 1):
                                        ?>
                                        <a href="<?= base_url('login/switch-role') ?>"
                                            class="flex items-center px-4 py-2 text-sm text-blue-600 hover:bg-blue-50 transition-colors">
                                            <i class="fas fa-exchange-alt w-5"></i>
                                            <span class="ml-2">Switch Role</span>
                                        </a>
                                        <div class="border-t border-gray-200 my-1"></div>
                                    <?php
                                    endif;
                                }
                                ?>

                                <a href="<?= base_url('logout') ?>"
                                    class="flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                    <i class="fas fa-sign-out-alt w-5"></i>
                                    <span class="ml-2">Logout</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="p-4 sm:p-6 lg:p-8">
            <?= $this->renderSection('content') ?>
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200 mt-8">
            <div class="px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex flex-col sm:flex-row justify-between items-center text-sm text-gray-600">
                    <p>&copy; <?= date('Y') ?> SiPantau - BPS Provinsi Riau. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>

    <!-- Sidebar Overlay (Mobile) -->
    <div id="sidebarOverlay" class="hidden fixed inset-0 bg-black bg-opacity-50 z-30 lg:hidden"
        onclick="toggleSidebar()"></div>

    <!-- Scripts -->
    <script src="<?= base_url('assets/js/admin.js') ?>"></script>
</body>

</html>