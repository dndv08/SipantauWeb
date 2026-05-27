<?php
$db = \Config\Database::connect();
$sobatId = session()->get('sobat_id');

// Cek apakah user PCL / PML
$isPCL = $db->table('pcl')->where('sobat_id', $sobatId)->countAllResults() > 0;
$isPML = $db->table('pml')->where('sobat_id', $sobatId)->countAllResults() > 0;

$roleLabel = '';
if ($isPCL && $isPML) $roleLabel = 'PCL & PML';
elseif ($isPCL) $roleLabel = 'PCL';
elseif ($isPML) $roleLabel = 'PML';
else $roleLabel = 'Petugas';

$kabupatenName = '';
if (session()->has('user_kabupaten_id')) {
    $kab = $db->table('master_kabupaten')
        ->where('id_kabupaten', session()->get('user_kabupaten_id'))
        ->get()->getRowArray();
    $kabupatenName = $kab['nama_kabupaten'] ?? '';
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="X-CSRF-TOKEN" content="<?= csrf_hash() ?>">
    <title>SiPantau - <?= $title ?? 'Dashboard Petugas' ?></title>
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

    <style>
        .sidebar-petugas {
            background: linear-gradient(180deg, #1e3a5f 0%, #0f2439 100%);
        }
        .sidebar-petugas .sidebar-link {
            display: flex;
            align-items: center;
            padding: 0.625rem 0.875rem;
            border-radius: 0.5rem;
            color: #94a3b8;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s;
        }
        .sidebar-petugas .sidebar-link:hover {
            background: rgba(255,255,255,0.08);
            color: #e2e8f0;
        }
        .sidebar-petugas .sidebar-link.active {
            background: rgba(59,130,246,0.25);
            color: #ffffff;
            border-left: 3px solid #3b82f6;
        }
        .role-badge-pcl { background: #dbeafe; color: #1e40af; }
        .role-badge-pml { background: #fef3c7; color: #92400e; }
        .role-badge-both { background: #d1fae5; color: #065f46; }
    </style>
</head>

<body class="bg-gray-50">

    <!-- Sidebar -->
    <aside id="sidebar"
        class="fixed top-0 left-0 z-40 w-64 h-screen transition-transform -translate-x-full lg:translate-x-0">
        <div class="h-full flex flex-col sidebar-petugas">

            <!-- Logo -->
            <div class="h-20 border-b border-white/10 px-6 py-4">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                        <img src="<?= base_url('assets/gambar/LOGO_BPS.png') ?>" alt="Logo BPS"
                            class="w-12 h-12 object-contain" />
                    </div>
                    <div class="flex flex-col">
                        <span class="text-xl font-bold text-white leading-tight">SiPantau</span>
                        <span class="text-xs text-blue-200 leading-tight">Petugas Survei</span>
                    </div>
                </div>
            </div>

            <!-- Role Badge -->
            <div class="px-4 py-3">
                <div class="px-3 py-2 rounded-lg bg-white/10 text-center">
                    <span class="text-xs text-blue-200">Status Anda</span>
                    <div class="mt-1">
                        <?php if ($isPCL && $isPML): ?>
                            <span class="inline-block px-2 py-0.5 rounded text-xs font-semibold role-badge-both">PCL & PML</span>
                        <?php elseif ($isPCL): ?>
                            <span class="inline-block px-2 py-0.5 rounded text-xs font-semibold role-badge-pcl">PCL</span>
                        <?php elseif ($isPML): ?>
                            <span class="inline-block px-2 py-0.5 rounded text-xs font-semibold role-badge-pml">PML</span>
                        <?php else: ?>
                            <span class="inline-block px-2 py-0.5 rounded text-xs font-semibold bg-gray-200 text-gray-600">Petugas</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto scrollbar-thin py-2 px-3 pb-20">
                <div class="space-y-1">
                    <p class="px-3 pt-2 pb-1 text-xs font-semibold text-blue-300 uppercase tracking-wider">Menu Utama</p>

                    <!-- Dashboard -->
                    <a href="<?= base_url('petugas') ?>"
                        class="sidebar-link <?= ($active_menu ?? '') == 'dashboard' ? 'active' : '' ?>">
                        <i class="fas fa-th-large w-5"></i>
                        <span class="ml-3">Dashboard</span>
                    </a>

                    <!-- Lapor Aktivitas -->
                    <a href="<?= base_url('petugas/lapor-aktivitas') ?>"
                        class="sidebar-link <?= ($active_menu ?? '') == 'lapor-aktivitas' ? 'active' : '' ?>">
                        <i class="fas fa-clipboard-check w-5"></i>
                        <span class="ml-3">Lapor Aktivitas</span>
                    </a>

                    <!-- Lapor Industri SE2026 -->
                    <a href="<?= base_url('petugas/lapor-industri') ?>"
                        class="sidebar-link <?= ($active_menu ?? '') == 'lapor-industri' ? 'active' : '' ?>">
                        <i class="fas fa-industry w-5"></i>
                        <span class="ml-3">Lapor Industri SE2026</span>
                    </a>

                    <p class="px-3 pt-4 pb-1 text-xs font-semibold text-blue-300 uppercase tracking-wider">Monitoring</p>

                    <!-- Daftar Kegiatan -->
                    <a href="<?= base_url('petugas/daftar-kegiatan') ?>"
                        class="sidebar-link <?= ($active_menu ?? '') == 'daftar-kegiatan' ? 'active' : '' ?>">
                        <i class="fas fa-list-ul w-5"></i>
                        <span class="ml-3">Daftar Kegiatan</span>
                    </a>

                    <!-- Pantau Progress -->
                    <a href="<?= base_url('petugas/pantau-progress') ?>"
                        class="sidebar-link <?= ($active_menu ?? '') == 'pantau-progress' ? 'active' : '' ?>">
                        <i class="fas fa-chart-line w-5"></i>
                        <span class="ml-3">Pantau Progress</span>
                    </a>

                    <!-- Kinerja Harian -->
                    <a href="<?= base_url('petugas/kinerja-harian') ?>"
                        class="sidebar-link <?= ($active_menu ?? '') == 'kinerja-harian' ? 'active' : '' ?>">
                        <i class="fas fa-calendar-check w-5"></i>
                        <span class="ml-3">Kinerja Harian</span>
                    </a>

                    <p class="px-3 pt-4 pb-1 text-xs font-semibold text-blue-300 uppercase tracking-wider">Lainnya</p>

                    <!-- Achievement -->
                    <a href="<?= base_url('petugas/achievement') ?>"
                        class="sidebar-link <?= ($active_menu ?? '') == 'achievement' ? 'active' : '' ?>">
                        <i class="fas fa-trophy w-5"></i>
                        <span class="ml-3">Achievement</span>
                    </a>

                    <!-- Feedback -->
                    <a href="<?= base_url('petugas/feedback') ?>"
                        class="sidebar-link <?= ($active_menu ?? '') == 'feedback' ? 'active' : '' ?>">
                        <i class="fas fa-comment-dots w-5"></i>
                        <span class="ml-3">Feedback</span>
                    </a>
                </div>
            </nav>

            <!-- Logout Button -->
            <div class="absolute bottom-0 left-0 right-0 p-4 bg-black/20 border-t border-white/10">
                <a href="<?= base_url('logout') ?>"
                    class="sidebar-link text-red-300 hover:bg-red-500/20 hover:text-red-200 border border-red-400/30">
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

                    <!-- Role Badge -->
                    <div class="hidden sm:flex items-center px-3 py-1.5 bg-blue-50 border border-blue-200 rounded-lg">
                        <i class="fas fa-id-badge text-blue-600 text-sm mr-2"></i>
                        <span class="text-sm font-medium text-blue-900"><?= esc($roleLabel) ?></span>
                    </div>

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
                                    <p class="text-xs text-gray-500">Petugas Survei</p>
                                </div>
                                <i class="fas fa-chevron-down text-xs text-gray-500 hidden sm:block"></i>
                            </button>

                            <!-- Dropdown -->
                            <div id="userMenu"
                                class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50">
                                <div class="px-4 py-3 border-b border-gray-200">
                                    <p class="text-sm font-medium text-gray-900"><?= esc($namaUser) ?></p>
                                    <p class="text-xs text-gray-500"><?= esc(session()->get('email') ?? '-') ?></p>
                                </div>

                                <?php
                                $userId = session()->get('user_id');
                                if ($userId) {
                                    $userModel = new \App\Models\UserModel();
                                    $adminProvinsiModel = new \App\Models\AdminSurveiProvinsiModel();
                                    $adminKabupatenModel = new \App\Models\AdminSurveiKabupatenModel();

                                    $user = $userModel->find($userId);
                                    $allRoles = is_string($user['role']) ?? false ? json_decode($user['role'], true) : [$user['role']];

                                    $MOBILE_ONLY_ROLES = [5];
                                    $webRoles = array_filter($allRoles, function ($roleId) use ($MOBILE_ONLY_ROLES) {
                                        return !in_array($roleId, $MOBILE_ONLY_ROLES);
                                    });

                                    $totalAvailableRoles = count($webRoles);

                                    if ($adminProvinsiModel->isAdminProvinsi($userId)) {
                                        $totalAvailableRoles++;
                                    }
                                    if ($adminKabupatenModel->isAdminKabupaten($userId)) {
                                        $totalAvailableRoles++;
                                    }

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

        <!-- Flash Messages -->
        <?php if (session()->getFlashdata('success')): ?>
        <div class="mx-4 sm:mx-6 lg:mx-8 mt-4">
            <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg p-4 flex items-center">
                <i class="fas fa-check-circle mr-3 text-green-500"></i>
                <span class="text-sm"><?= session()->getFlashdata('success') ?></span>
            </div>
        </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
        <div class="mx-4 sm:mx-6 lg:mx-8 mt-4">
            <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg p-4 flex items-center">
                <i class="fas fa-exclamation-circle mr-3 text-red-500"></i>
                <span class="text-sm"><?= session()->getFlashdata('error') ?></span>
            </div>
        </div>
        <?php endif; ?>

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
    <script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        sidebar.classList.toggle('-translate-x-full');
        overlay.classList.toggle('hidden');
    }

    function toggleUserMenu() {
        const menu = document.getElementById('userMenu');
        menu.classList.toggle('hidden');
    }

    // Close user menu on click outside
    document.addEventListener('click', function(e) {
        const menu = document.getElementById('userMenu');
        const button = e.target.closest('button[onclick="toggleUserMenu()"]');
        if (!button && menu && !menu.contains(e.target)) {
            menu.classList.add('hidden');
        }
    });
    </script>
</body>

</html>
