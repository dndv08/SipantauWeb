<?= $this->extend('layouts/sadmin_layout') ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="mb-6">
    <div class="flex items-center text-sm text-gray-600 mb-4">
        <a href="<?= base_url('superadmin') ?>" class="hover:text-blue-600 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Back
        </a>
    </div>
    <h1 class="text-2xl font-bold text-gray-900">Kelola Master Kegiatan Detail</h1>
    <p class="text-gray-600 mt-1">Kelola data detail kegiatan survei/sensus beserta satuan dan periode pelaksanaan</p>
</div>

<!-- DESKTOP LAYOUT -->
<div class="card mb-6 hidden lg:block">
    <!-- Search, Filter, PerPage and Add Button -->
    <div style="display: grid; grid-template-columns: 1fr 300px 200px 250px; gap: 1rem; margin-bottom: 1.5rem;">
        <!-- Search Box -->
        <div>
            <label for="searchInput" class="block text-sm font-medium text-gray-700 mb-1">
                Pencarian
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                <input type="text" id="searchInput" class="input-field w-full pl-10"
                    placeholder="Cari master kegiatan, nama detail, atau satuan..." onkeyup="searchTable()">
            </div>
        </div>

        <!-- Filter Master Kegiatan -->
        <div>
            <label for="filterKegiatan" class="block text-sm font-medium text-gray-700 mb-1">
                Filter Master Kegiatan
            </label>
            <div class="relative">
                <select id="filterKegiatan"
                    class="input-field w-full pr-10 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 appearance-none cursor-pointer"
                    onchange="updateFilters()">
                    <option value="all" <?= ($filterKegiatan ?? 'all') == 'all' ? 'selected' : '' ?>>Semua Master Kegiatan
                    </option>
                    <?php if (!empty($masterKegiatans)): ?>
                        <?php foreach ($masterKegiatans as $kegiatan): ?>
                            <option value="<?= $kegiatan['id_kegiatan'] ?>" <?= ($filterKegiatan ?? '') == $kegiatan['id_kegiatan'] ? 'selected' : '' ?>>
                                <?= esc($kegiatan['nama_kegiatan']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <i id="filterChevron"
                        class="fas fa-chevron-down text-gray-400 text-sm transition-transform duration-300"></i>
                </div>
            </div>
        </div>

        <!-- Per Page Selector -->
        <div>
            <label for="perPageSelect" class="block text-sm font-medium text-gray-700 mb-1">
                Data per Halaman
            </label>
            <div class="relative">
                <select name="perPage" id="perPageSelect"
                    class="input-field w-full pr-10 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 appearance-none cursor-pointer"
                    onchange="updateFilters()">
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

        <!-- Add Button -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">&nbsp;</label>
            <a href="<?= base_url('superadmin/master-kegiatan-detail/create') ?>"
                class="btn-primary whitespace-nowrap w-full text-center inline-block">
                <i class="fas fa-plus mr-2"></i>Tambah Kegiatan Detail
            </a>
        </div>
    </div>
</div>

<!-- MOBILE LAYOUT -->
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
                    placeholder="Cari master kegiatan, nama detail..." onkeyup="searchTable()">
            </div>
        </div>

        <!-- Filter Master Kegiatan Mobile -->
        <div class="w-full">
            <label class="block text-sm font-medium text-gray-700 mb-1">Filter Master Kegiatan</label>
            <select id="filterKegiatanMobile" class="input-field w-full text-sm" onchange="updateFilters()">
                <option value="all" <?= ($filterKegiatan ?? 'all') == 'all' ? 'selected' : '' ?>>Semua Master Kegiatan
                </option>
                <?php if (!empty($masterKegiatans)): ?>
                    <?php foreach ($masterKegiatans as $kegiatan): ?>
                        <option value="<?= $kegiatan['id_kegiatan'] ?>" <?= ($filterKegiatan ?? '') == $kegiatan['id_kegiatan'] ? 'selected' : '' ?>>
                            <?= esc($kegiatan['nama_kegiatan']) ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <!-- Per Page Mobile -->
        <div class="w-full">
            <label class="block text-sm font-medium text-gray-700 mb-1">Data per Halaman</label>
            <select id="perPageSelectMobile" class="input-field w-full text-sm" onchange="updateFilters()">
                <option value="5" <?= ($perPage == 5) ? 'selected' : ''; ?>>5</option>
                <option value="10" <?= ($perPage == 10) ? 'selected' : ''; ?>>10</option>
                <option value="25" <?= ($perPage == 25) ? 'selected' : ''; ?>>25</option>
                <option value="50" <?= ($perPage == 50) ? 'selected' : ''; ?>>50</option>
                <option value="100" <?= ($perPage == 100) ? 'selected' : ''; ?>>100</option>
            </select>
        </div>

        <!-- Add Button Mobile -->
        <div class="w-full">
            <a href="<?= base_url('superadmin/master-kegiatan-detail/create') ?>"
                class="btn-primary w-full text-center inline-block">
                <i class="fas fa-plus mr-2"></i>Tambah Kegiatan Detail
            </a>
        </div>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div class="overflow-x-auto">
        <table class="w-full" id="kegiatanDetailTable">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th
                        class="px-4 py-3 text-xs text-center font-semibold text-gray-600 uppercase tracking-wider w-16 border-r border-gray-200">
                        No
                    </th>
                    <th
                        class="px-4 py-3 text-xs text-center font-semibold text-gray-600 uppercase tracking-wider border-r border-gray-200">
                        Master Kegiatan
                    </th>
                    <th
                        class="px-4 py-3 text-xs text-center font-semibold text-gray-600 uppercase tracking-wider border-r border-gray-200">
                        Nama Kegiatan Detail
                    </th>
                    <th
                        class="px-4 py-3 text-xs text-center font-semibold text-gray-600 uppercase tracking-wider w-32 border-r border-gray-200">
                        Satuan
                    </th>
                    <th
                        class="px-4 py-3 text-xs text-center font-semibold text-gray-600 uppercase tracking-wider w-32 border-r border-gray-200">
                        Periode
                    </th>
                    <th
                        class="px-4 py-3 text-xs text-center font-semibold text-gray-600 uppercase tracking-wider w-24 border-r border-gray-200">
                        Tahun
                    </th>
                    <th
                        class="px-4 py-3 text-xs text-center font-semibold text-gray-600 uppercase tracking-wider w-64 border-r border-gray-200">
                        Admin
                    </th>
                    <th
                        class="px-4 py-3 text-xs text-center font-semibold text-gray-600 uppercase tracking-wider w-32 border-r border-gray-200">
                        Aksi
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (empty($details)): ?>
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center">
                            <i class="fas fa-inbox text-gray-300 text-5xl mb-4"></i>
                            <p class="text-gray-500 mb-4">Belum ada data master kegiatan detail</p>
                            <a href="<?= base_url('superadmin/master-kegiatan-detail/create') ?>"
                                class="btn-primary inline-block">
                                <i class="fas fa-plus mr-2"></i>Tambah Master Kegiatan Detail Pertama
                            </a>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php $no = 1; ?>
                    <?php foreach ($details as $detail): ?>
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-4 py-4 text-sm text-gray-900 text-center border-r border-gray-200">
                                <?= ($pager->getCurrentPage('details') - 1) * $pager->getPerPage('details') + $no++ ?>
                            </td>
                            <td class="px-4 py-4 border-r border-gray-200">
                                <p class="text-sm font-medium text-gray-900"><?= esc($detail['nama_kegiatan'] ?? '-') ?></p>
                                <?php if (!empty($detail['periode_kegiatan'])): ?>
                                    <p class="text-xs text-gray-500 mt-1">
                                        <i class="fas fa-calendar-alt mr-1"></i><?= esc($detail['periode_kegiatan']) ?>
                                    </p>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-4 border-r border-gray-200">
                                <p class="text-sm text-gray-900"><?= esc($detail['nama_kegiatan_detail']) ?></p>
                                <?php if (!empty($detail['keterangan'])): ?>
                                    <p class="text-xs text-gray-500 mt-1 line-clamp-1"><?= esc($detail['keterangan']) ?></p>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-4 border-r border-gray-200 text-center">
                                <span class="text-sm text-gray-600"><?= esc($detail['satuan']) ?></span>
                            </td>
                            <td class="px-4 py-4 border-r border-gray-200 text-center">
                                <span class="badge badge-info"><?= esc($detail['periode']) ?></span>
                            </td>
                            <td class="px-4 py-4 border-r border-gray-200 text-center">
                                <span class="text-sm font-medium text-gray-900"><?= esc($detail['tahun']) ?></span>
                            </td>
                            <td class="px-4 py-4 border-r border-gray-200">
                                <?php if (empty($detail['admin_list'])): ?>
                                    <div class="flex justify-center">
                                        <span
                                            class="inline-flex items-center px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-xs">
                                            <i class="fas fa-user-slash mr-1.5 text-xs"></i>
                                            Belum ada admin
                                        </span>
                                    </div>
                                <?php else: ?>
                                    <div class="flex flex-wrap gap-1.5 justify-center">
                                        <?php
                                        $maxShow = 2;
                                        $badgeColors = ['bg-blue-100 text-blue-700', 'bg-green-100 text-green-700', 'bg-purple-100 text-purple-700', 'bg-pink-100 text-pink-700'];
                                        $shown = array_slice($detail['admin_list'], 0, $maxShow);
                                        $remaining = count($detail['admin_list']) - $maxShow;

                                        foreach ($shown as $idx => $admin):
                                            $colorClass = $badgeColors[$idx % count($badgeColors)];
                                            ?>
                                            <span
                                                class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium <?= $colorClass ?>"
                                                title="<?= esc($admin['email']) ?>">
                                                <i class="fas fa-user mr-1.5 text-xs"></i>
                                                <?= esc($admin['nama_user']) ?>
                                            </span>
                                        <?php endforeach; ?>

                                        <?php if ($remaining > 0): ?>
                                            <button
                                                onclick="showAllAdmins(<?= $detail['id_kegiatan_detail'] ?>, '<?= esc($detail['nama_kegiatan_detail'], 'js') ?>')"
                                                class="inline-flex items-center px-2.5 py-1 bg-gray-100 text-gray-700 hover:bg-gray-200 rounded-full text-xs font-medium transition-colors cursor-pointer">
                                                <i class="fas fa-plus-circle mr-1 text-xs"></i>
                                                <?= $remaining ?> lainnya
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-4 border-r border-gray-200 text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <a href="<?= base_url('superadmin/master-kegiatan-detail/show/' . $detail['id_kegiatan_detail']) ?>"
                                        class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition-colors duration-200"
                                        title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?= base_url('superadmin/master-kegiatan-detail/' . $detail['id_kegiatan_detail'] . '/edit') ?>"
                                        class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors duration-200"
                                        title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button
                                        onclick="confirmDelete(<?= $detail['id_kegiatan_detail'] ?>, '<?= esc($detail['nama_kegiatan_detail'], 'js') ?>')"
                                        class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors duration-200"
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

    <!-- Footer dengan Pagination -->
    <div class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-4">
        <p class="text-sm text-gray-600">
            Menampilkan data
            <span
                class="font-medium"><?= (($pager->getCurrentPage('details') - 1) * $pager->getPerPage('details')) + 1 ?></span>-<span
                class="font-medium"><?= min($pager->getCurrentPage('details') * $pager->getPerPage('details'), $pager->getTotal('details')) ?></span>
            dari <span class="font-medium"><?= $pager->getTotal('details') ?></span> data
        </p>

        <!-- Custom Pagination -->
        <?php if ($pager->getPageCount('details') > 1): ?>
            <?= $pager->links('details', 'tailwind_pager') ?>
        <?php endif; ?>
    </div>
</div>

<!-- SweetAlert2 Script -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>

    // Handle animasi chevron untuk filter kegiatan
    const filterKegiatan = document.getElementById('filterKegiatan');
    const filterChevron = document.getElementById('filterChevron');

    filterKegiatan.addEventListener('focus', function () {
        filterChevron.classList.add('rotate-180');
    });

    filterKegiatan.addEventListener('blur', function () {
        filterChevron.classList.remove('rotate-180');
    });

    // Handle animasi chevron untuk perPage selector
    const perPageSelect = document.getElementById('perPageSelect');
    const perPageChevron = document.getElementById('perPageChevron');

    perPageSelect.addEventListener('focus', function () {
        perPageChevron.classList.add('rotate-180');
    });

    perPageSelect.addEventListener('blur', function () {
        perPageChevron.classList.remove('rotate-180');
    });

    // Function untuk update filters dengan mempertahankan parameter yang ada
    function updateFilters() {
        // Deteksi apakah desktop atau mobile
        const isDesktop = window.innerWidth >= 1024;

        let kegiatan, perPage;
        if (isDesktop) {
            kegiatan = document.getElementById('filterKegiatan')?.value || 'all';
            perPage = document.getElementById('perPageSelect')?.value || '10';
        } else {
            kegiatan = document.getElementById('filterKegiatanMobile')?.value || 'all';
            perPage = document.getElementById('perPageSelectMobile')?.value || '10';
        }

        const params = new URLSearchParams();
        if (kegiatan && kegiatan !== 'all') params.append('kegiatan', kegiatan);
        if (perPage) params.append('perPage', perPage);

        window.location.href = '<?= base_url('superadmin/master-kegiatan-detail') ?>?' + params.toString();
    }

    // ====================================================================
    // Show SweetAlert on Page Load
    // ====================================================================
    <?php if (session()->getFlashdata('success')): ?>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '<?= session()->getFlashdata('success') ?>',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true,
            customClass: {
                popup: 'rounded-xl'
            }
        });
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: '<?= session()->getFlashdata('error') ?>',
            confirmButtonColor: '#3b82f6',
            customClass: {
                popup: 'rounded-xl',
                confirmButton: 'px-6 py-2.5 rounded-lg font-medium'
            }
        });
    <?php endif; ?>

    // ====================================================================
    // Search Table
    // ====================================================================
    function searchTable() {
        // Ambil input dari desktop atau mobile
        const inputDesktop = document.getElementById('searchInput');
        const inputMobile = document.getElementById('searchInputMobile');

        let filter = '';
        if (window.innerWidth >= 1024 && inputDesktop) {
            filter = inputDesktop.value.toLowerCase();
        } else if (inputMobile) {
            filter = inputMobile.value.toLowerCase();
        }

        const table = document.getElementById('kegiatanDetailTable');
        const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];
            const cells = row.getElementsByTagName('td');
            let found = false;

            if (cells.length === 1 && cells[0].getAttribute('colspan')) {
                continue;
            }

            for (let j = 1; j < cells.length - 1; j++) {
                const cell = cells[j];
                if (cell) {
                    const textValue = cell.textContent || cell.innerText;
                    if (textValue.toLowerCase().indexOf(filter) > -1) {
                        found = true;
                        break;
                    }
                }
            }

            row.style.display = found ? '' : 'none';
        }
    }

    // ====================================================================
    // Filter by Master Kegiatan
    // ====================================================================
    function filterByKegiatan(kegiatanId) {
        const url = new URL(window.location.href);
        if (kegiatanId === 'all') {
            url.searchParams.delete('kegiatan');
        } else {
            url.searchParams.set('kegiatan', kegiatanId);
        }
        window.location.href = url.toString();
    }

    // ====================================================================
    // Show All Admins Modal
    // ====================================================================
    function showAllAdmins(idKegiatanDetail, namaKegiatan) {
        // Fetch admin list via AJAX
        fetch(`<?= base_url('superadmin/master-kegiatan-detail/get-admins/') ?>${idKegiatanDetail}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const adminList = data.admins;
                    const badgeColors = ['bg-green-100 text-green-700'];

                    let adminHTML = '<div class="space-y-2 max-h-96 overflow-y-auto">';
                    adminList.forEach((admin, idx) => {
                        const colorClass = badgeColors[idx % badgeColors.length];
                        adminHTML += `
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center">
                                    <span class="text-white text-sm font-medium">${admin.nama_user.substring(0, 2).toUpperCase()}</span>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">${admin.nama_user}</p>
                                    <p class="text-xs text-gray-500">${admin.email}</p>
                                </div>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-medium ${colorClass}">
                                <i class="fas fa-user-check mr-1"></i>Admin
                            </span>
                        </div>
                    `;
                    });
                    adminHTML += '</div>';

                    Swal.fire({
                        title: `<div class="text-lg font-semibold text-gray-900">Daftar Admin</div>`,
                        html: `
                        <div class="text-left">
                            <p class="text-sm text-gray-600 mb-4">Kegiatan: <strong>${namaKegiatan}</strong></p>
                            ${adminHTML}
                        </div>
                    `,
                        width: '600px',
                        confirmButtonText: 'Tutup',
                        confirmButtonColor: '#3b82f6',
                        customClass: {
                            popup: 'rounded-xl',
                            confirmButton: 'px-6 py-2.5 rounded-lg font-medium'
                        }
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Gagal mengambil data admin',
                    confirmButtonColor: '#3b82f6'
                });
            });
    }

    // ====================================================================
    // Delete Confirmation
    // ====================================================================
    function confirmDelete(id, name) {
        Swal.fire({
            title: 'Hapus Data Kegiatan Detail?',
            html: `Apakah Anda yakin ingin menghapus <strong>"${name}"</strong>?<br><span class="text-sm text-gray-600">Tindakan ini tidak dapat dibatalkan.</span>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="fas fa-trash mr-2"></i>Hapus',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            customClass: {
                popup: 'rounded-xl',
                confirmButton: 'px-6 py-2.5 rounded-lg font-medium',
                cancelButton: 'px-6 py-2.5 rounded-lg font-medium'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                deleteData(id, name);
            }
        });
    }

    // ====================================================================
    // Delete Data - FIX: URL yang benar
    // ====================================================================
    function deleteData(id, name) {
        Swal.fire({
            title: 'Menghapus...',
            html: 'Mohon tunggu sebentar',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });

        // FIX: Gunakan URL yang benar sesuai route
        fetch(`<?= base_url('superadmin/master-kegiatan-detail/') ?>${id}`, {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil Dihapus!',
                        text: data.message,
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true,
                        customClass: {
                            popup: 'rounded-xl'
                        }
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Menghapus',
                        text: data.message,
                        confirmButtonColor: '#3b82f6',
                        customClass: {
                            popup: 'rounded-xl',
                            confirmButton: 'px-6 py-2.5 rounded-lg font-medium'
                        }
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Terjadi Kesalahan',
                    text: 'Gagal menghapus data. Silakan coba lagi.',
                    confirmButtonColor: '#3b82f6',
                    customClass: {
                        popup: 'rounded-xl',
                        confirmButton: 'px-6 py-2.5 rounded-lg font-medium'
                    }
                });
            });
    }
</script>

<?= $this->endSection() ?>