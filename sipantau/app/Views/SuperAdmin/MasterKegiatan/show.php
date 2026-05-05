<?= $this->extend('layouts/sadmin_layout') ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="mb-6">
    <div class="flex items-center text-sm text-gray-600 mb-4">
        <a href="<?= base_url('superadmin/master-kegiatan') ?>" class="hover:text-blue-600 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Kembali ke Master Kegiatan
        </a>
    </div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Detail Master Kegiatan</h1>
            <p class="text-gray-600 mt-1">Lihat informasi lengkap master kegiatan</p>
        </div>
        <div class="flex gap-2">
            <a href="<?= base_url('superadmin/master-kegiatan/edit/' . $kegiatan['id_kegiatan']) ?>"
                class="btn-primary">
                <i class="fas fa-edit mr-2"></i>Edit Kegiatan
            </a>
        </div>
    </div>
</div>

<!-- Info Master Kegiatan -->
<div class="card mb-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold text-gray-900">Informasi Master Kegiatan</h2>
        <span class="badge badge-info"><?= esc($kegiatan['periode']) ?></span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Master Output -->
        <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">Master Output</label>
            <div class="flex items-center gap-2">
                <span
                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                    <?= esc($kegiatan['nama_output'] ?? 'Tidak ada') ?>
                </span>
                <?php if (!empty($kegiatan['alias'])): ?>
                    <span class="text-sm text-gray-500">(<?= esc($kegiatan['alias']) ?>)</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Nama Kegiatan -->
        <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">Nama Kegiatan</label>
            <p class="text-base text-gray-900 font-medium"><?= esc($kegiatan['nama_kegiatan']) ?></p>
        </div>

        <!-- Pelaksana -->
        <?php if (!empty($kegiatan['pelaksana'])): ?>
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Pelaksana</label>
                <p class="text-base text-gray-900"><?= esc($kegiatan['pelaksana']) ?></p>
            </div>
        <?php endif; ?>

        <!-- Periode -->
        <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">Periode</label>
            <p class="text-base text-gray-900"><?= esc($kegiatan['periode']) ?></p>
        </div>

        <!-- Fungsi -->
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-600 mb-1">Fungsi</label>
            <p class="text-base text-gray-900"><?= esc($kegiatan['fungsi']) ?></p>
        </div>

        <!-- Keterangan -->
        <?php if (!empty($kegiatan['keterangan'])): ?>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-600 mb-1">Keterangan</label>
                <p class="text-base text-gray-900"><?= esc($kegiatan['keterangan']) ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Action Buttons -->
<div class="flex flex-col sm:flex-row gap-3 mb-6">
    <a href="<?= base_url('superadmin/master-kegiatan') ?>" class="btn-secondary">
        <i class="fas fa-arrow-left mr-2"></i>Kembali
    </a>
    <a href="<?= base_url('superadmin/master-kegiatan/edit/' . $kegiatan['id_kegiatan']) ?>" class="btn-primary">
        <i class="fas fa-edit mr-2"></i>Edit Data
    </a>
    <button onclick="confirmDelete(<?= $kegiatan['id_kegiatan'] ?>, '<?= esc($kegiatan['nama_kegiatan'], 'js') ?>')"
        class="btn-danger sm:ml-auto">
        <i class="fas fa-trash mr-2"></i>Hapus Data
    </button>
</div>

<!-- List Master Kegiatan Detail -->
<div class="card">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Daftar Kegiatan Detail</h2>
            <p class="text-sm text-gray-600 mt-1">Kegiatan detail yang terkait dengan master kegiatan ini</p>
        </div>

        <!-- DESKTOP LAYOUT -->
        <div class="hidden lg:flex items-center gap-4 mb-6">
            <!-- Search Box -->
            <div class="flex-1">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" id="searchDetailInput" class="input-field w-full pl-10"
                        placeholder="Cari nama kegiatan detail, satuan..." onkeyup="searchDetailTable()">
                </div>
            </div>

            <!-- Per Page Selector -->
            <div class="w-48">
                <div class="relative">
                    <select name="perPage" id="perPageDetailSelect"
                        class="input-field w-full pr-10 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 appearance-none cursor-pointer"
                        onchange="updatePerPageDetail()">
                        <option value="5" <?= ($perPage == 5) ? 'selected' : ''; ?>>5</option>
                        <option value="10" <?= ($perPage == 10) ? 'selected' : ''; ?>>10</option>
                        <option value="25" <?= ($perPage == 25) ? 'selected' : ''; ?>>25</option>
                        <option value="50" <?= ($perPage == 50) ? 'selected' : ''; ?>>50</option>
                        <option value="100" <?= ($perPage == 100) ? 'selected' : ''; ?>>100</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <i id="perPageDetailChevron"
                            class="fas fa-chevron-down text-gray-400 text-sm transition-transform duration-300"></i>
                    </div>
                </div>
            </div>

            <!-- Add Button -->
            <a href="<?= base_url('superadmin/master-kegiatan-detail/create?id_kegiatan=' . $kegiatan['id_kegiatan']) ?>"
                class="btn-primary whitespace-nowrap">
                <i class="fas fa-plus mr-2"></i>Tambah Kegiatan Detail
            </a>
        </div>

        <!-- MOBILE LAYOUT -->
        <div class="lg:hidden space-y-3 mb-6">
            <!-- Search Box Mobile -->
            <div class="w-full">
                <label class="block text-sm font-medium text-gray-700 mb-1">Pencarian</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" id="searchDetailInputMobile" class="input-field w-full pl-10 text-sm"
                        placeholder="Cari nama kegiatan detail, satuan..." onkeyup="searchDetailTable()">
                </div>
            </div>

            <!-- Per Page Mobile -->
            <div class="w-full">
                <label class="block text-sm font-medium text-gray-700 mb-1">Data per Halaman</label>
                <select id="perPageDetailSelectMobile" class="input-field w-full text-sm"
                    onchange="updatePerPageDetail()">
                    <option value="5" <?= ($perPage == 5) ? 'selected' : ''; ?>>5</option>
                    <option value="10" <?= ($perPage == 10) ? 'selected' : ''; ?>>10</option>
                    <option value="25" <?= ($perPage == 25) ? 'selected' : ''; ?>>25</option>
                    <option value="50" <?= ($perPage == 50) ? 'selected' : ''; ?>>50</option>
                    <option value="100" <?= ($perPage == 100) ? 'selected' : ''; ?>>100</option>
                </select>
            </div>

            <!-- Add Button Mobile -->
            <div class="w-full">
                <a href="<?= base_url('superadmin/master-kegiatan-detail/create?id_kegiatan=' . $kegiatan['id_kegiatan']) ?>"
                    class="btn-primary w-full text-center inline-block">
                    <i class="fas fa-plus mr-2"></i>Tambah Kegiatan Detail
                </a>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-200">
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-16">No
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nama
                        Kegiatan Detail</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-28">
                        Tanggal Mulai</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-28">
                        Tanggal Selesai</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-28">
                        Satuan</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-40">
                        Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (empty($kegiatanDetails)): ?>
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center">
                            <i class="fas fa-inbox text-gray-300 text-5xl mb-4"></i>
                            <p class="text-gray-500 mb-4">Belum ada kegiatan detail</p>
                            <a href="<?= base_url('superadmin/master-kegiatan-detail/create?id_kegiatan=' . $kegiatan['id_kegiatan']) ?>"
                                class="btn-primary inline-block">
                                <i class="fas fa-plus mr-2"></i>Tambah Kegiatan Detail Pertama
                            </a>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php $no = 1; ?>
                    <?php foreach ($kegiatanDetails as $detail): ?>
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-4 py-4 text-sm text-gray-900">
                                <?= ($pager->getCurrentPage('kegiatanDetails') - 1) * $pager->getPerPage('kegiatanDetails') + $no ?>
                            </td>
                            <td class="px-4 py-4">
                                <p class="text-sm font-medium text-gray-900"><?= esc($detail['nama_kegiatan_detail']) ?></p>
                                <?php if (!empty($detail['keterangan'])): ?>
                                    <p class="text-xs text-gray-500 mt-1"><?= esc($detail['keterangan']) ?></p>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-4">
                                <span class="text-sm text-gray-600"><?= esc($detail['satuan']) ?></span>
                            </td>
                            <td class="px-4 py-4">
                                <span class="badge badge-info"><?= esc($detail['periode']) ?></span>
                            </td>
                            <td class="px-4 py-4">
                                <span class="text-sm font-medium text-gray-900"><?= esc($detail['tahun']) ?></span>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center justify-center space-x-2">
                                    <a href="<?= base_url('superadmin/master-kegiatan-detail/show/' . $detail['id_kegiatan_detail']) ?>"
                                        class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition-colors duration-200"
                                        title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?= base_url('superadmin/master-kegiatan-detail/edit/' . $detail['id_kegiatan_detail']) ?>"
                                        class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors duration-200"
                                        title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button
                                        onclick="confirmDeleteDetail(<?= $detail['id_kegiatan_detail'] ?>, '<?= esc($detail['nama_kegiatan_detail'], 'js') ?>')"
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
                class="font-medium"><?= (($pager->getCurrentPage('kegiatanDetails') - 1) * $pager->getPerPage('kegiatanDetails')) + 1 ?></span>-<span
                class="font-medium"><?= min($pager->getCurrentPage('kegiatanDetails') * $pager->getPerPage('kegiatanDetails'), $pager->getTotal('kegiatanDetails')) ?></span>
            dari <span class="font-medium"><?= $pager->getTotal('kegiatanDetails') ?></span> data
        </p>

        <!-- Custom Pagination -->
        <?php if ($pager->getPageCount('kegiatanDetails') > 1): ?>
            <?= $pager->links('kegiatanDetails', 'tailwind_pager') ?>
        <?php endif; ?>
    </div>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Delete confirmation untuk Master Kegiatan
    function confirmDelete(id, name) {
        Swal.fire({
            title: 'Hapus Data Kegiatan?',
            html: `Apakah Anda yakin ingin menghapus kegiatan <strong>"${name}"</strong>?<br><span class="text-sm text-gray-600">Tindakan ini tidak dapat dibatalkan dan akan menghapus semua kegiatan detail terkait.</span>`,
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

    // Fungsi untuk proses delete Master Kegiatan
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
        fetch(`<?= base_url('superadmin/master-kegiatan/delete') ?>/${id}`, {
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
                        window.location.href = '<?= base_url('superadmin/master-kegiatan') ?>';
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

    // Delete confirmation untuk Kegiatan Detail
    function confirmDeleteDetail(id, name) {
        Swal.fire({
            title: 'Hapus Kegiatan Detail?',
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
                deleteDetailData(id, name);
            }
        });
    }

    // Fungsi untuk proses delete Kegiatan Detail
    function deleteDetailData(id, name) {
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
        fetch(`<?= base_url('superadmin/master-kegiatan-detail/delete') ?>/${id}`, {
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

    // Handle animasi chevron untuk perPage detail selector
    const perPageDetailSelect = document.getElementById('perPageDetailSelect');
    const perPageDetailChevron = document.getElementById('perPageDetailChevron');

    perPageDetailSelect.addEventListener('focus', function () {
        perPageDetailChevron.classList.add('rotate-180');
    });

    perPageDetailSelect.addEventListener('blur', function () {
        perPageDetailChevron.classList.remove('rotate-180');
    });

    // Function untuk update perPage detail
    function updatePerPageDetail() {
        // Deteksi apakah desktop atau mobile
        const isDesktop = window.innerWidth >= 1024;

        let perPage;
        if (isDesktop) {
            perPage = document.getElementById('perPageDetailSelect')?.value || '10';
        } else {
            perPage = document.getElementById('perPageDetailSelectMobile')?.value || '10';
        }

        const params = new URLSearchParams();
        if (perPage) params.append('perPage', perPage);
        window.location.href = '<?= base_url('superadmin/master-kegiatan/show/' . $kegiatan['id_kegiatan']) ?>?' + params.toString();
    }

    // Search function untuk kegiatan detail
    function searchDetailTable() {
        // Ambil input dari desktop atau mobile
        const inputDesktop = document.getElementById('searchDetailInput');
        const inputMobile = document.getElementById('searchDetailInputMobile');

        let input = '';
        if (window.innerWidth >= 1024 && inputDesktop) {
            input = inputDesktop.value.toLowerCase();
        } else if (inputMobile) {
            input = inputMobile.value.toLowerCase();
        }

        const rows = document.querySelectorAll('table tbody tr');
        rows.forEach(row => {
            // Skip row pertama jika ada colspan (empty state)
            if (row.querySelector('td[colspan]')) return;

            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(input) ? '' : 'none';
        });
    }
</script>

<?= $this->endSection() ?>