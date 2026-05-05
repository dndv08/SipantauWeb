<?= $this->extend('layouts/sadmin_layout') ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="mb-6">
    <div class="flex items-center text-sm text-gray-600 mb-4">
        <a href="<?= base_url('admin') ?>" class="hover:text-blue-600 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Back
        </a>
    </div>
    <h1 class="text-2xl font-bold text-gray-900">Kelola Master Kegiatan</h1>
    <p class="text-gray-600 mt-1">Kelola data master kegiatan survei/sensus beserta detail periode</p>
</div>

<!-- DESKTOP LAYOUT -->
<div class="card mb-6 hidden lg:block">
    <!-- Search, Filter, PerPage and Add Button -->
    <div style="display: grid; grid-template-columns: 1fr 300px 200px 200px; gap: 1rem; margin-bottom: 1.5rem;">
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
                    placeholder="Cari nama kegiatan, periode..." onkeyup="searchTable()">
            </div>
        </div>

        <!-- Filter Master Output -->
        <div>
            <label for="filterOutput" class="block text-sm font-medium text-gray-700 mb-1">
                Filter Master Output
            </label>
            <div class="relative">
                <select id="filterOutput"
                    class="input-field w-full pr-10 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 appearance-none cursor-pointer"
                    onchange="updateFilters()">
                    <option value="all" <?= $filterOutput == 'all' ? 'selected' : '' ?>>Semua Master Output</option>
                    <?php foreach ($masterOutputs as $output): ?>
                        <option value="<?= $output['id_output'] ?>" <?= $filterOutput == $output['id_output'] ? 'selected' : '' ?>>
                            <?= esc($output['nama_output']) ?>
                        </option>
                    <?php endforeach; ?>
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
            <a href="<?= base_url('superadmin/master-kegiatan/create') ?>"
                class="btn-primary whitespace-nowrap w-full text-center inline-block">
                <i class="fas fa-plus mr-2"></i>
                Tambah Kegiatan
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
                    placeholder="Cari nama kegiatan, periode..." onkeyup="searchTable()">
            </div>
        </div>

        <!-- Filter Master Output Mobile -->
        <div class="w-full">
            <label class="block text-sm font-medium text-gray-700 mb-1">Filter Master Output</label>
            <select id="filterOutputMobile" class="input-field w-full text-sm" onchange="updateFilters()">
                <option value="all" <?= $filterOutput == 'all' ? 'selected' : '' ?>>Semua Master Output</option>
                <?php foreach ($masterOutputs as $output): ?>
                    <option value="<?= $output['id_output'] ?>" <?= $filterOutput == $output['id_output'] ? 'selected' : '' ?>>
                        <?= esc($output['nama_output']) ?>
                    </option>
                <?php endforeach; ?>
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
            <a href="<?= base_url('superadmin/master-kegiatan/create') ?>"
                class="btn-primary w-full text-center inline-block">
                <i class="fas fa-plus mr-2"></i>
                Tambah Kegiatan
            </a>
        </div>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div class="overflow-x-auto">
        <table class="w-full" id="masterKegiatanTable">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th
                        class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-r border-gray-200 w-16">
                        No
                    </th>
                    <th
                        class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-r border-gray-200">
                        Nama Kegiatan
                    </th>
                    <th
                        class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-r border-gray-200">
                        Master Output
                    </th>
                    <th
                        class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-r border-gray-200">
                        Fungsi
                    </th>
                    <th
                        class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-r border-gray-200 w-32">
                        Periode
                    </th>
                    <th
                        class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider border-r border-gray-200 w-32">
                        Aksi
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (empty($kegiatans)): ?>
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center">
                            <i class="fas fa-inbox text-gray-300 text-5xl mb-4"></i>
                            <p class="text-gray-500 mb-4">Belum ada data master kegiatan</p>
                            <a href="<?= base_url('superadmin/master-kegiatan/create') ?>" class="btn-primary inline-block">
                                <i class="fas fa-plus mr-2"></i>Tambah Master Kegiatan Pertama
                            </a>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php $no = 1; ?>
                    <?php foreach ($kegiatans as $kegiatan): ?>
                        <tr class="hover:bg-gray-50 transition-colors duration-150 border-r border-gray-200">
                            <td class="px-4 py-4 text-sm text-gray-900 border-r border-gray-200">
                                <?= ($pager->getCurrentPage('kegiatans') - 1) * $pager->getPerPage('kegiatans') + $no ?>
                            </td>
                            <td class="px-4 py-4 border-r border-gray-200">
                                <p class="text-sm font-medium text-gray-900"><?= esc($kegiatan['nama_kegiatan']) ?></p>
                                <?php if (!empty($kegiatan['pelaksana'])): ?>
                                    <p class="text-xs text-gray-500 mt-1">Pelaksana: <?= esc($kegiatan['pelaksana']) ?></p>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-4 border-r border-gray-200">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                    <?= esc($kegiatan['nama_output'] ?? 'Tidak ada') ?>
                                </span>
                            </td>
                            <td class="px-4 py-4 border-r border-gray-200">
                                <p class="text-sm text-gray-600 line-clamp-2"><?= esc($kegiatan['fungsi']) ?></p>
                            </td>
                            <td class="px-4 py-4 border-r border-gray-200">
                                <span class="badge badge-info"><?= esc($kegiatan['periode']) ?></span>
                            </td>
                            <td class="px-4 py-4 border-r border-gray-200">
                                <div class="flex items-center justify-center space-x-2">
                                    <a href="<?= base_url('superadmin/master-kegiatan/show/' . $kegiatan['id_kegiatan']) ?>"
                                        class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition-colors duration-200"
                                        title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?= base_url('superadmin/master-kegiatan/edit/' . $kegiatan['id_kegiatan']) ?>"
                                        class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors duration-200"
                                        title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button
                                        onclick="confirmDelete(<?= $kegiatan['id_kegiatan'] ?>, '<?= esc($kegiatan['nama_kegiatan'], 'js') ?>')"
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
                class="font-medium"><?= (($pager->getCurrentPage('kegiatans') - 1) * $pager->getPerPage('kegiatans')) + 1 ?></span>-<span
                class="font-medium"><?= min($pager->getCurrentPage('kegiatans') * $pager->getPerPage('kegiatans'), $pager->getTotal('kegiatans')) ?></span>
            dari <span class="font-medium"><?= $pager->getTotal('kegiatans') ?></span> data
        </p>

        <!-- Custom Pagination -->
        <?php if ($pager->getPageCount('kegiatans') > 1): ?>
            <?= $pager->links('kegiatans', 'tailwind_pager') ?>
        <?php endif; ?>
    </div>
</div>

<!-- SweetAlert2 Script -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Search functionality
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
        
        const table = document.getElementById('masterKegiatanTable');
        const rows = table.getElementsByTagName('tr');

        for (let i = 1; i < rows.length; i++) {
            const row = rows[i];
            const cells = row.getElementsByTagName('td');
            let found = false;

            if (cells.length > 1) {
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
            }

            row.style.display = found ? '' : 'none';
        }
    }

    // Handle animasi chevron untuk filter output
    const filterOutput = document.getElementById('filterOutput');
    const filterChevron = document.getElementById('filterChevron');

    filterOutput.addEventListener('focus', function () {
        filterChevron.classList.add('rotate-180');
    });

    filterOutput.addEventListener('blur', function () {
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
        
        let output, perPage;
        if (isDesktop) {
            output = document.getElementById('filterOutput')?.value || 'all';
            perPage = document.getElementById('perPageSelect')?.value || '10';
        } else {
            output = document.getElementById('filterOutputMobile')?.value || 'all';
            perPage = document.getElementById('perPageSelectMobile')?.value || '10';
        }

        const params = new URLSearchParams();
        if (output && output !== 'all') params.append('output', output);
        if (perPage) params.append('perPage', perPage);

        window.location.href = '<?= base_url('superadmin/master-kegiatan') ?>?' + params.toString();
    }

    // Delete confirmation dengan SweetAlert2
    function confirmDelete(id, name) {
        Swal.fire({
            title: 'Hapus Data Kegiatan?',
            html: `Apakah Anda yakin ingin menghapus kegiatan <strong>"${name}"</strong>?<br><span class="text-sm text-gray-600">Tindakan ini tidak dapat dibatalkan.</span>`,
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
            },
            buttonsStyling: true
        }).then((result) => {
            if (result.isConfirmed) {
                deleteData(id, name);
            }
        });
    }

    // Fungsi untuk proses delete
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

        // Kirim request delete ke server
        fetch(`<?= base_url('superadmin/master-kegiatan/') ?>${id}`, {
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
                        confirmButtonColor: '#3b82f6',
                        customClass: {
                            popup: 'rounded-xl',
                            confirmButton: 'px-6 py-2.5 rounded-lg font-medium'
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

    // Show success/error message from session
    <?php if (session()->getFlashdata('success')): ?>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '<?= session()->getFlashdata('success') ?>',
            timer: 3000,
            showConfirmButton: false,
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
</script>

<?= $this->endSection() ?>