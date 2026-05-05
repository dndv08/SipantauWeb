<?= $this->extend('layouts/sadmin_layout') ?>

<?= $this->section('content') ?>

<style>
    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 24px;
    }

    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #cbd5e0;
        transition: .3s;
        border-radius: 24px;
    }

    .toggle-slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .3s;
        border-radius: 50%;
    }

    input:checked+.toggle-slider {
        background-color: #3b82f6;
    }

    input:checked+.toggle-slider:before {
        transform: translateX(26px);
    }

    .status-label {
        font-size: 0.75rem;
        font-weight: 500;
        margin-left: 8px;
    }
</style>

<!-- Page Header -->
<div class="mb-6">
    <div class="flex items-center text-sm text-gray-600 mb-4">
        <a href="<?= base_url('superadmin') ?>" class="hover:text-blue-600 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Back
        </a>
    </div>
    <h1 class="text-2xl font-bold text-gray-900">Kelola Pengguna</h1>
    <p class="text-gray-600 mt-1">Kelola data pengguna sistem SiPantau</p>
</div>

<!-- Flash Messages -->
<?php if (session()->getFlashdata('success')): ?>
    <div class="mb-4 bg-green-50 border border-green-200 rounded-lg p-4">
        <div class="flex items-center">
            <i class="fas fa-check-circle text-green-600 mr-3"></i>
            <p class="text-sm text-green-700"><?= session()->getFlashdata('success') ?></p>
        </div>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="mb-4 bg-red-50 border border-red-200 rounded-lg p-4">
        <div class="flex items-center">
            <i class="fas fa-exclamation-circle text-red-600 mr-3"></i>
            <p class="text-sm text-red-700"><?= session()->getFlashdata('error') ?></p>
        </div>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('import_errors')): ?>
    <div class="mb-4 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
        <div class="flex items-start">
            <i class="fas fa-exclamation-triangle text-yellow-600 mr-3 mt-0.5"></i>
            <div>
                <p class="text-sm font-semibold text-yellow-800 mb-1">Detail Error Import:</p>
                <pre class="text-xs text-yellow-700 whitespace-pre-wrap"><?= session()->getFlashdata('import_errors') ?></pre>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- DESKTOP FILTER CARD -->
<div class="card mb-6 hidden lg:block">
    <!-- Search and Actions -->
    <div class="space-y-4">
        <!-- Row 1: Search, Filter, dan PerPage -->
        <div style="display: grid; grid-template-columns: 1fr 250px 200px; gap: 1rem;">
            <!-- Search Box -->
            <div>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" id="searchInput" class="input-field w-full pl-10"
                        placeholder="Cari nama, email, atau HP..." value="<?= esc($search) ?>" onkeyup="handleSearch()">
                </div>
            </div>

            <!-- Role Filter -->
            <div>
                <div class="relative">
                    <select id="roleFilter" class="input-field w-full" onchange="filterByRole()">
                        <option value="">Semua Role</option>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?= $role['id_roleuser'] ?>" <?= $roleFilter == $role['id_roleuser'] ? 'selected' : '' ?>>
                                <?= esc($role['roleuser']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Per Page Selector -->
            <div>
                <div class="relative">
                    <select name="perPage" id="perPageSelect"
                        class="input-field w-full pr-10 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 appearance-none cursor-pointer"
                        onchange="updatePerPage()">
                        <option value="5" <?= ($perPage == 5) ? 'selected' : ''; ?>>5</option>
                        <option value="10" <?= ($perPage == 10) ? 'selected' : ''; ?>>10</option>
                        <option value="25" <?= ($perPage == 25) ? 'selected' : ''; ?>>25</option>
                        <option value="50" <?= ($perPage == 50) ? 'selected' : ''; ?>>50</option>
                        <option value="100" <?= ($perPage == 100) ? 'selected' : ''; ?>>100</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <i id="perPageChevron"
                            class="fas fa-chevron-down text-gray-400 text-sm transition-transform duration-300"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 2: Action Buttons -->
        <div class="flex flex-wrap gap-2 justify-end">
            <button onclick="showImportModal()" class="btn-secondary whitespace-nowrap">
                <i class="fas fa-file-import mr-2"></i>Import
            </button>

            <a href="<?= base_url('superadmin/kelola-pengguna/export') ?>"
                class="btn-secondary whitespace-nowrap inline-flex items-center">
                <i class="fas fa-file-export mr-2"></i>Export
            </a>

            <a href="<?= base_url('superadmin/kelola-pengguna/create') ?>"
                class="btn-primary whitespace-nowrap inline-flex items-center">
                <i class="fas fa-plus mr-2"></i>Tambah Pengguna
            </a>
        </div>
    </div>
</div>

<!-- MOBILE FILTER CARD -->
<div class="card mb-6 lg:hidden">
    <div class="space-y-3">
        <!-- Search Box Mobile -->
        <div class="w-full">
            <label class="block text-sm font-medium text-gray-700 mb-1">Pencarian</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                <input type="text" id="searchInputMobile" class="input-field w-full pl-10 text-sm"
                    placeholder="Cari nama, email, atau HP..." value="<?= esc($search) ?>" onkeyup="handleSearch()">
            </div>
        </div>

        <!-- Role Filter Mobile -->
        <div class="w-full">
            <label class="block text-sm font-medium text-gray-700 mb-1">Filter Role</label>
            <select id="roleFilterMobile" class="input-field w-full text-sm" onchange="filterByRole()">
                <option value="">Semua Role</option>
                <?php foreach ($roles as $role): ?>
                    <option value="<?= $role['id_roleuser'] ?>" <?= $roleFilter == $role['id_roleuser'] ? 'selected' : '' ?>>
                        <?= esc($role['roleuser']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Per Page Mobile -->
        <div class="w-full">
            <label class="block text-sm font-medium text-gray-700 mb-1">Data per Halaman</label>
            <select id="perPageSelectMobile" class="input-field w-full text-sm" onchange="updatePerPage()">
                <option value="5" <?= ($perPage == 5) ? 'selected' : ''; ?>>5</option>
                <option value="10" <?= ($perPage == 10) ? 'selected' : ''; ?>>10</option>
                <option value="25" <?= ($perPage == 25) ? 'selected' : ''; ?>>25</option>
                <option value="50" <?= ($perPage == 50) ? 'selected' : ''; ?>>50</option>
                <option value="100" <?= ($perPage == 100) ? 'selected' : ''; ?>>100</option>
            </select>
        </div>

        <!-- Action Buttons Mobile -->
        <div class="flex flex-col gap-2">
            <button onclick="showImportModal()" class="btn-secondary w-full text-center">
                <i class="fas fa-file-import mr-2"></i>Import
            </button>

            <a href="<?= base_url('superadmin/kelola-pengguna/export') ?>"
                class="btn-secondary w-full text-center inline-flex items-center justify-center">
                <i class="fas fa-file-export mr-2"></i>Export
            </a>

            <a href="<?= base_url('superadmin/kelola-pengguna/create') ?>"
                class="btn-primary w-full text-center inline-flex items-center justify-center">
                <i class="fas fa-plus mr-2"></i>Tambah Pengguna
            </a>
        </div>
    </div>
</div>

<!-- DATA CARD (Table + Mobile Cards + Pagination) -->
<div class="card">
    <!-- DESKTOP TABLE VIEW -->
    <div class="hidden lg:block overflow-x-auto">
        <table class="w-full" id="penggunaTable">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-16 border-r border-gray-200">
                        No</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider border-r border-gray-200">
                        Nama & Email</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-40 border-r border-gray-200">
                        Kab/Kota</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider border-r border-gray-200">
                        No HP</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider border-r border-gray-200">
                        Role</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider border-r border-gray-200">
                        Pegawai/Mitra</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-32 border-r border-gray-200">
                        Status</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-32 border-r border-gray-200">
                        Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-2"></i>
                            <p>Tidak ada data pengguna</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $index => $user): ?>
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-4 py-4 text-sm text-gray-900 border-r border-gray-200">
                                <?= $pagination['startItem'] + $index ?>
                            </td>
                            <td class="px-4 py-4 border-r border-gray-200">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center mr-3">
                                        <span class="text-white text-sm font-medium">
                                            <?= strtoupper(substr($user['nama_user'], 0, 2)) ?>
                                        </span>
                                    </div>
                                    <div>
                                        <a href="<?= base_url('superadmin/kelola-pengguna/detail/' . $user['sobat_id']) ?>"
                                            class="text-sm font-medium text-blue-600 hover:text-blue-800 hover:underline transition-colors">
                                            <?= esc($user['nama_user']) ?>
                                        </a>
                                        <p class="text-xs text-gray-500"><?= esc($user['email']) ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4 border-r border-gray-200">
                                <p class="text-sm text-gray-600"><?= esc($user['nama_kabupaten'] ?? '-') ?></p>
                            </td>
                            <td class="px-4 py-4 border-r border-gray-200">
                                <p class="text-sm text-gray-600"><?= esc($user['hp']) ?></p>
                            </td>
                            <td class="px-4 py-4 border-r border-gray-200">
                                <div class="flex flex-wrap gap-1">
                                    <?php if (!empty($user['role_names'])): ?>
                                        <?php
                                        $badgeColors = ['bg-green-100 text-green-700', 'bg-blue-100 text-blue-700', 'bg-yellow-100 text-yellow-700', 'bg-purple-100 text-purple-700', 'bg-pink-100 text-pink-700', 'bg-indigo-100 text-indigo-700'];
                                        foreach ($user['role_names'] as $idx => $roleName):
                                            $colorClass = $badgeColors[$idx % count($badgeColors)];
                                            ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $colorClass ?>">
                                                <?= esc($roleName) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="text-xs text-gray-400">Belum ada role</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-4 py-4 border-r border-gray-200 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                            <?= $user['is_pegawai'] == '1' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' ?>">
                                    <?= $user['is_pegawai'] == '1' ? 'Pegawai' : 'Mitra' ?>
                                </span>
                            </td>
                            <td class="px-4 py-4 border-r border-gray-200 text-center">
                                <div class="flex items-center justify-center">
                                    <label class="toggle-switch">
                                        <input type="checkbox" 
                                               data-sobat-id="<?= $user['sobat_id'] ?>"
                                               <?= $user['is_active'] ? 'checked' : '' ?>
                                               onchange="toggleStatus(this)">
                                        <span class="toggle-slider"></span>
                                    </label>
                                    <span class="status-label" id="status-label-<?= $user['sobat_id'] ?>">
                                        <?= $user['is_active'] ? '<span class="text-blue-600">Aktif</span>' : '<span class="text-gray-500">Nonaktif</span>' ?>
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-4 border-r border-gray-200">
                                <div class="flex items-center justify-center space-x-2">
                                    <a href="<?= base_url('superadmin/kelola-pengguna/edit/' . $user['sobat_id']) ?>"
                                        class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors duration-200"
                                        title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn-delete p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors duration-200"
                                            data-sobat-id="<?= $user['sobat_id'] ?>"
                                            data-nama="<?= esc($user['nama_user']) ?>"
                                            title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- MOBILE CARD VIEW -->
    <div class="lg:hidden space-y-3">
        <?php if (empty($users)): ?>
            <div class="text-center py-12">
                <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
                <p class="text-gray-500 text-lg">Tidak ada data pengguna</p>
            </div>
        <?php else: ?>
            <?php foreach ($users as $index => $user): ?>
                <div class="bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                    <!-- Header Card -->
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-4 py-3 border-b border-gray-200">
                        <a href="<?= base_url('superadmin/kelola-pengguna/detail/' . $user['sobat_id']) ?>"
                            class="block">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center mr-3">
                                    <span class="text-white text-base font-medium">
                                        <?= strtoupper(substr($user['nama_user'], 0, 2)) ?>
                                    </span>
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-base font-semibold text-gray-900 hover:text-blue-600 transition-colors">
                                        <?= esc($user['nama_user']) ?>
                                    </h3>
                                    <p class="text-xs text-gray-600"><?= esc($user['email']) ?></p>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Content Card -->
                    <div class="px-4 py-3 space-y-3">
                        <!-- Kab/Kota & HP -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase mb-1">Kab/Kota</p>
                                <p class="text-sm text-gray-900"><?= esc($user['nama_kabupaten'] ?? '-') ?></p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase mb-1">No HP</p>
                                <p class="text-sm text-gray-900"><?= esc($user['hp']) ?></p>
                            </div>
                        </div>

                        <!-- Roles -->
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase mb-2">Role</p>
                            <div class="flex flex-wrap gap-1">
                                <?php if (!empty($user['role_names'])): ?>
                                    <?php
                                    $badgeColors = ['bg-green-100 text-green-700', 'bg-blue-100 text-blue-700', 'bg-yellow-100 text-yellow-700', 'bg-purple-100 text-purple-700', 'bg-pink-100 text-pink-700', 'bg-indigo-100 text-indigo-700'];
                                    foreach ($user['role_names'] as $idx => $roleName):
                                        $colorClass = $badgeColors[$idx % count($badgeColors)];
                                    ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $colorClass ?>">
                                            <?= esc($roleName) ?>
                                        </span>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span class="text-xs text-gray-400">Belum ada role</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Pegawai/Mitra -->
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase mb-1">Status</p>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                <?= $user['is_pegawai'] == '1' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' ?>">
                                <?= $user['is_pegawai'] == '1' ? 'Pegawai' : 'Mitra' ?>
                            </span>
                        </div>
                    </div>

                    <!-- Footer Card -->
                    <div class="bg-gray-50 px-4 py-3 border-t border-gray-200">
                        <div class="flex items-center justify-between">
                            <!-- Status Toggle -->
                            <div>
                                <p class="text-xs text-gray-600 font-medium mb-2">Status Aktif</p>
                                <div class="flex items-center">
                                    <label class="toggle-switch">
                                        <input type="checkbox" 
                                               data-sobat-id="<?= $user['sobat_id'] ?>"
                                               <?= $user['is_active'] ? 'checked' : '' ?>
                                               onchange="toggleStatus(this)">
                                        <span class="toggle-slider"></span>
                                    </label>
                                    <span class="status-label ml-2" id="status-label-<?= $user['sobat_id'] ?>">
                                        <?= $user['is_active'] ? '<span class="text-blue-600 text-xs">Aktif</span>' : '<span class="text-gray-500 text-xs">Nonaktif</span>' ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex items-center gap-2">
                                <a href="<?= base_url('superadmin/kelola-pengguna/edit/' . $user['sobat_id']) ?>"
                                    class="w-9 h-9 flex items-center justify-center text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                    title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn-delete w-9 h-9 flex items-center justify-center text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                        data-sobat-id="<?= $user['sobat_id'] ?>"
                                        data-nama="<?= esc($user['nama_user']) ?>"
                                        title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Footer dengan Pagination -->
    <div class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-4">
        <p class="text-sm text-gray-600">
            Menampilkan data
            <span class="font-medium"><?= $pagination['startItem'] ?></span>-<span
                class="font-medium"><?= $pagination['endItem'] ?></span>
            dari <span class="font-medium"><?= $pagination['total'] ?></span> data
        </p>

        <!-- Custom Pagination -->
        <?php if ($pagination['totalPages'] > 1): ?>
            <nav class="flex items-center gap-2">
                <!-- Previous Button -->
                <?php if ($pagination['currentPage'] > 1): ?>
                    <a href="?page_users=<?= $pagination['currentPage'] - 1 ?>&perPage=<?= $perPage ?>&search=<?= urlencode($search) ?>&role=<?= $roleFilter ?>"
                        class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                <?php else: ?>
                    <span class="px-3 py-2 text-sm bg-gray-100 border border-gray-300 rounded-lg text-gray-400 cursor-not-allowed">
                        <i class="fas fa-chevron-left"></i>
                    </span>
                <?php endif; ?>

                <!-- Page Numbers -->
                <?php
                $startPage = max(1, $pagination['currentPage'] - 2);
                $endPage = min($pagination['totalPages'], $pagination['currentPage'] + 2);
                
                for ($i = $startPage; $i <= $endPage; $i++):
                ?>
                    <a href="?page_users=<?= $i ?>&perPage=<?= $perPage ?>&search=<?= urlencode($search) ?>&role=<?= $roleFilter ?>"
                        class="px-3 py-2 text-sm rounded-lg transition-colors <?= $i == $pagination['currentPage'] ? 'bg-blue-600 text-white font-medium' : 'bg-white border border-gray-300 hover:bg-gray-50' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>

                <!-- Next Button -->
                <?php if ($pagination['currentPage'] < $pagination['totalPages']): ?>
                    <a href="?page_users=<?= $pagination['currentPage'] + 1 ?>&perPage=<?= $perPage ?>&search=<?= urlencode($search) ?>&role=<?= $roleFilter ?>"
                        class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                <?php else: ?>
                    <span class="px-3 py-2 text-sm bg-gray-100 border border-gray-300 rounded-lg text-gray-400 cursor-not-allowed">
                        <i class="fas fa-chevron-right"></i>
                    </span>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    </div>
</div>

<!-- Import Modal -->
<div id="importModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Import Data Pengguna</h3>
                <button onclick="closeImportModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="mb-4">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
                    <p class="text-sm text-blue-700">
                        <i class="fas fa-info-circle mr-2"></i>
                        Download template Excel terlebih dahulu
                    </p>
                </div>

                <a href="<?= base_url('superadmin/kelola-pengguna/download-template') ?>"
                    class="btn-secondary w-full text-center mb-4">
                    <i class="fas fa-download mr-2"></i>Download Template
                </a>
            </div>

            <form id="formImport" action="<?= base_url('superadmin/kelola-pengguna/import') ?>" method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Upload File Excel <span class="text-red-500">*</span>
                </label>
                <input type="file" name="file" id="fileImport" accept=".xlsx,.xls" class="input-field" required>
                <p class="mt-1 text-xs text-gray-500">Format: .xlsx atau .xls</p>
            </div>

            <div class="flex gap-3">
                <button type="button" onclick="closeImportModal()" class="btn-secondary flex-1">
                    Batal
                </button>
                <button type="submit" class="btn-primary flex-1">
                    <i class="fas fa-upload mr-2"></i>Import
                </button>
            </div>
        </form>
    </div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Handle animasi chevron untuk perPage selector
    const perPageSelect = document.getElementById('perPageSelect');
    const perPageChevron = document.getElementById('perPageChevron');

    perPageSelect.addEventListener('focus', function () {
        perPageChevron.classList.add('rotate-180');
    });

    perPageSelect.addEventListener('blur', function () {
        perPageChevron.classList.remove('rotate-180');
    });

    // Function untuk update perPage
    function updatePerPage() {
        const perPage = document.getElementById('perPageSelect')?.value || 
                    document.getElementById('perPageSelectMobile')?.value || '10';
        const search = document.getElementById('searchInput')?.value || 
                    document.getElementById('searchInputMobile')?.value || '';
        const roleFilter = document.getElementById('roleFilter')?.value || 
                        document.getElementById('roleFilterMobile')?.value || '';

        const params = new URLSearchParams();
        if (perPage) params.append('perPage', perPage);
        if (search) params.append('search', search);
        if (roleFilter) params.append('role', roleFilter);

        window.location.href = '<?= base_url('superadmin/kelola-pengguna') ?>?' + params.toString();
    }

    function handleSearch() {
    // Ambil input dari desktop atau mobile
    const inputDesktop = document.getElementById('searchInput');
    const inputMobile = document.getElementById('searchInputMobile');
    
    let searchValue = '';
    if (window.innerWidth >= 1024 && inputDesktop) {
        searchValue = inputDesktop.value;
        // Sync ke mobile
        if (inputMobile) inputMobile.value = searchValue;
    } else if (inputMobile) {
        searchValue = inputMobile.value;
        // Sync ke desktop
        if (inputDesktop) inputDesktop.value = searchValue;
    }
    
    const perPage = document.getElementById('perPageSelect')?.value || 
                   document.getElementById('perPageSelectMobile')?.value || '10';
    const roleFilter = document.getElementById('roleFilter')?.value || 
                      document.getElementById('roleFilterMobile')?.value || '';

    const params = new URLSearchParams();
    if (searchValue) params.append('search', searchValue);
    if (perPage) params.append('perPage', perPage);
    if (roleFilter) params.append('role', roleFilter);

    clearTimeout(window.searchTimeout);
    window.searchTimeout = setTimeout(() => {
        window.location.href = '<?= base_url('superadmin/kelola-pengguna') ?>?' + params.toString();
    }, 500);
}

    function filterByRole() {
        const roleFilter = document.getElementById('roleFilter')?.value || 
                        document.getElementById('roleFilterMobile')?.value || '';
        const search = document.getElementById('searchInput')?.value || 
                    document.getElementById('searchInputMobile')?.value || '';
        const perPage = document.getElementById('perPageSelect')?.value || 
                    document.getElementById('perPageSelectMobile')?.value || '10';

        const params = new URLSearchParams();
        if (roleFilter) params.append('role', roleFilter);
        if (search) params.append('search', search);
        if (perPage) params.append('perPage', perPage);

        window.location.href = '<?= base_url('superadmin/kelola-pengguna') ?>?' + params.toString();
    }

    //  FIX: Toggle Status - terima checkbox element, ambil ID dari data attribute
    function toggleStatus(checkbox) {
        const sobatId = checkbox.dataset.sobatId; // Ambil sebagai STRING dari data attribute
        const isActive = checkbox.checked;
        const labelEl = document.getElementById('status-label-' + sobatId);

        fetch(`<?= base_url('superadmin/kelola-pengguna/toggle-status') ?>/${sobatId}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (labelEl) {
                        labelEl.innerHTML = isActive ? '<span class="text-blue-600">Aktif</span>' : '<span class="text-gray-500">Nonaktif</span>';
                    }
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                } else {
                    checkbox.checked = !isActive;
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: data.message
                    });
                }
            })
            .catch(error => {
                checkbox.checked = !isActive;
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Terjadi kesalahan saat mengubah status'
                });
            });
    }

    // ✅ FIX: Event Delegation untuk Delete Button (Support Desktop & Mobile)
document.addEventListener('DOMContentLoaded', function() {
    // Event delegation untuk tabel desktop
    const tableBody = document.querySelector('#penggunaTable tbody');
    if (tableBody) {
        tableBody.addEventListener('click', function(e) {
            const deleteBtn = e.target.closest('.btn-delete');
            if (deleteBtn) {
                e.preventDefault();
                const sobatId = deleteBtn.dataset.sobatId;
                const nama = deleteBtn.dataset.nama;
                confirmDelete(sobatId, nama);
            }
        });
    }

    // Event delegation untuk card mobile
    const mobileCardContainer = document.querySelector('.lg\\:hidden.space-y-3');
    if (mobileCardContainer) {
        mobileCardContainer.addEventListener('click', function(e) {
            const deleteBtn = e.target.closest('.btn-delete');
            if (deleteBtn) {
                e.preventDefault();
                const sobatId = deleteBtn.dataset.sobatId;
                const nama = deleteBtn.dataset.nama;
                confirmDelete(sobatId, nama);
            }
        });
    }
});

    // ✅ FIX: Confirm Delete - terima sobatId sebagai STRING
    function confirmDelete(sobatId, name) {
        // sobatId sudah STRING dari data attribute, JANGAN konversi ke Number!
        console.log('confirmDelete called with:', {
            sobatId: sobatId,
            sobatIdType: typeof sobatId,
            sobatIdLength: sobatId.length
        });

        Swal.fire({
            title: 'Hapus Pengguna?',
            html: `Apakah Anda yakin ingin menghapus <strong>"${name}"</strong>?<br><small class="text-gray-500">Sobat ID: ${sobatId}</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="fas fa-trash mr-2"></i>Hapus',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                console.log('Sending delete request for:', sobatId);
                
                // Kirim sebagai STRING ke backend
                fetch(`<?= base_url('superadmin/kelola-pengguna/delete') ?>/${sobatId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json'
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Delete response:', data);
                        
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: data.message,
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: data.message
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Delete error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Terjadi kesalahan saat menghapus data'
                        });
                    });
            }
        });
    }

    function showImportModal() {
        document.getElementById('importModal').classList.remove('hidden');
    }

    function closeImportModal() {
        document.getElementById('importModal').classList.add('hidden');
        document.getElementById('formImport').reset();
    }

    document.getElementById('importModal').addEventListener('click', function (e) {
        if (e.target === this) {
            closeImportModal();
        }
    });
</script>

<?= $this->endSection() ?>