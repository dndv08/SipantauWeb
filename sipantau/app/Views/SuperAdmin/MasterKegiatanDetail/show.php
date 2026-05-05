<?= $this->extend('layouts/sadmin_layout') ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="mb-6">
    <div class="flex items-center text-sm text-gray-600 mb-4">
        <a href="<?= base_url('superadmin/master-kegiatan-detail') ?>" class="hover:text-blue-600 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Kembali ke Master Kegiatan Detail
        </a>
    </div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Detail Kegiatan Detail</h1>
            <p class="text-gray-600 mt-1">Lihat informasi lengkap kegiatan detail</p>
        </div>
        <div class="flex gap-2">
            <a href="<?= base_url('superadmin/master-kegiatan-detail/' . $detail['id_kegiatan_detail'] . '/edit') ?>"
                class="btn-primary">
                <i class="fas fa-edit mr-2"></i>Edit Kegiatan Detail
            </a>
        </div>
    </div>
</div>

<!-- Breadcrumb Info -->
<div class="card mb-6 bg-gradient-to-r from-blue-50 to-indigo-50 border-blue-200">
    <div class="flex flex-wrap items-center text-sm gap-2">
        <span class="text-gray-600">Master Kegiatan:</span>
        <a href="<?= base_url('superadmin/master-kegiatan/' . $detail['id_kegiatan']) ?>"
            class="font-medium text-blue-600 hover:text-blue-700">
            <?= esc($detail['nama_kegiatan']) ?>
        </a>
        <?php if (!empty($detail['periode_kegiatan'])): ?>
            <span class="mx-2 text-gray-400">/</span>
            <span class="text-gray-600">Periode:</span>
            <span class="font-medium text-gray-900"><?= esc($detail['periode_kegiatan']) ?></span>
        <?php endif; ?>
    </div>
</div>

<!-- Info Kegiatan Detail -->
<div class="card mb-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold text-gray-900">Informasi Kegiatan Detail</h2>
        <span class="badge badge-info"><?= esc($detail['periode']) ?> - <?= esc($detail['tahun']) ?></span>
    </div>

    <div class="space-y-6">
        <!-- Nama Kegiatan Detail -->
        <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">Nama Kegiatan Detail</label>
            <p class="text-base text-gray-900 font-medium"><?= esc($detail['nama_kegiatan_detail']) ?></p>
        </div>

        <!-- Row 1: Satuan dan Periode -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Satuan</label>
                <p class="text-base text-gray-900"><?= esc($detail['satuan']) ?></p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Periode</label>
                <p class="text-base text-gray-900">
                    <span class="badge badge-info"><?= esc($detail['periode']) ?></span>
                </p>
            </div>
        </div>

        <!-- Row 2: Tahun dan Status -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Tahun</label>
                <p class="text-base text-gray-900 font-medium"><?= esc($detail['tahun']) ?></p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Status</label>
                <p class="text-base text-gray-900">
                    <?php if (!empty($detail['tanggal_mulai']) && !empty($detail['tanggal_selesai'])): ?>
                        <?php
                        $now = time();
                        $start = strtotime($detail['tanggal_mulai']);
                        $end = strtotime($detail['tanggal_selesai']);

                        if ($now < $start): ?>
                            <span class="badge badge-warning">Belum Dimulai</span>
                        <?php elseif ($now >= $start && $now <= $end): ?>
                            <span class="badge badge-success">Sedang Berjalan</span>
                        <?php else: ?>
                            <span class="badge badge-secondary">Selesai</span>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="badge badge-info">Terjadwal</span>
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <!-- Row 3: Tanggal Mulai dan Tanggal Selesai -->
        <?php if (!empty($detail['tanggal_mulai']) || !empty($detail['tanggal_selesai'])): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Tanggal Mulai</label>
                    <p class="text-base text-gray-900">
                        <?= !empty($detail['tanggal_mulai']) ? date('d F Y', strtotime($detail['tanggal_mulai'])) : '-' ?>
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Tanggal Selesai</label>
                    <p class="text-base text-gray-900">
                        <?= !empty($detail['tanggal_selesai']) ? date('d F Y', strtotime($detail['tanggal_selesai'])) : '-' ?>
                    </p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Keterangan -->
        <?php if (!empty($detail['keterangan'])): ?>
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Keterangan</label>
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <p class="text-base text-gray-900"><?= nl2br(esc($detail['keterangan'])) ?></p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Action Buttons -->
<div class="flex flex-col sm:flex-row gap-3 mb-6">
    <a href="<?= base_url('superadmin/master-kegiatan-detail') ?>" class="btn-secondary">
        <i class="fas fa-arrow-left mr-2"></i>Kembali
    </a>
    <a href="<?= base_url('superadmin/master-kegiatan-detail/' . $detail['id_kegiatan_detail'] . '/edit') ?>"
        class="btn-primary">
        <i class="fas fa-edit mr-2"></i>Edit Data
    </a>
    <button
        onclick="confirmDelete(<?= $detail['id_kegiatan_detail'] ?>, '<?= esc($detail['nama_kegiatan_detail'], 'js') ?>')"
        class="btn-danger sm:ml-auto">
        <i class="fas fa-trash mr-2"></i>Hapus Data
    </button>
</div>

<!-- List Master Kegiatan Detail Proses -->
<div class="card">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Daftar Proses Kegiatan</h2>
            <p class="text-sm text-gray-600 mt-1">Proses-proses yang terkait dengan kegiatan detail ini</p>
        </div>
    </div>

    <!-- Search dan PerPage Section -->
    <div style="display: grid; grid-template-columns: 1fr 200px; gap: 1rem; margin-bottom: 1.5rem;">
        <!-- Search Box -->
        <div>
            <label for="searchProsesInput" class="block text-sm font-medium text-gray-700 mb-1">
                Pencarian Proses Kegiatan
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                <input type="text" id="searchProsesInput" class="input-field w-full pl-10"
                    placeholder="Cari nama proses, satuan..." onkeyup="searchProsesTable()">
            </div>
        </div>

        <!-- Per Page Selector -->
        <div>
            <label for="perPageProsesSelect" class="block text-sm font-medium text-gray-700 mb-1">
                Data per Halaman
            </label>
            <div class="relative">
                <select name="perPage" id="perPageProsesSelect"
                    class="input-field w-full pr-10 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 appearance-none cursor-pointer"
                    onchange="updatePerPageProses()">
                    <option value="5" <?= ($perPage == 5) ? 'selected' : ''; ?>>5</option>
                    <option value="10" <?= ($perPage == 10) ? 'selected' : ''; ?>>10</option>
                    <option value="25" <?= ($perPage == 25) ? 'selected' : ''; ?>>25</option>
                    <option value="50" <?= ($perPage == 50) ? 'selected' : ''; ?>>50</option>
                    <option value="100" <?= ($perPage == 100) ? 'selected' : ''; ?>>100</option>
                </select>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <i id="perPageProsesChevron"
                        class="fas fa-chevron-down text-gray-400 text-sm transition-transform duration-300"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full" id="prosesTable">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th
                        class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-16 border-r border-gray-200">
                        No</th>
                    <th
                        class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-r border-gray-200">
                        Nama Proses</th>
                    <th
                        class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-r border-gray-200">
                        Satuan</th>
                    <th
                        class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-32 border-r border-gray-200">
                        Tanggal Mulai</th>
                    <th
                        class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-32 border-r border-gray-200">
                        Tanggal Selesai</th>
                    <th
                        class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-32 border-r border-gray-200">
                        Target</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (empty($detailProses)): ?>
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center">
                            <i class="fas fa-inbox text-gray-300 text-5xl mb-4"></i>
                            <p class="text-gray-500">Belum ada proses kegiatan</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php $no = 1; ?>
                    <?php foreach ($detailProses as $proses): ?>
                        <tr class="hover:bg-gray-50 transition-colors duration-150 border-r border-gray-200">
                            <td class="px-4 py-4 text-sm text-gray-900 border-r border-gray-200">
                                <?= ($pager->getCurrentPage('detailProses') - 1) * $pager->getPerPage('detailProses') + $no++ ?>
                            </td>
                            <td class="px-4 py-4 border-r border-gray-200">
                                <a href="<?= base_url('superadmin/master-kegiatan-detail/kegiatan-wilayah/' . $proses['id_kegiatan_detail_proses']) ?>"
                                    class="text-sm font-medium text-blue-600 hover:text-blue-800 hover:underline">
                                    <?= esc($proses['nama_kegiatan_detail_proses']) ?>
                                </a>
                                <?php if (!empty($proses['keterangan'])): ?>
                                    <p class="text-xs text-gray-500 mt-1 line-clamp-1"><?= esc($proses['keterangan']) ?></p>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-4 border-r border-gray-200">
                                <span class="text-sm text-gray-600"><?= esc($proses['satuan'] ?? '-') ?></span>
                            </td>
                            <td class="px-4 py-4 border-r border-gray-200">
                                <span class="text-sm text-gray-600">
                                    <?= !empty($proses['tanggal_mulai']) ? date('d/m/Y', strtotime($proses['tanggal_mulai'])) : '-' ?>
                                </span>
                            </td>
                            <td class="px-4 py-4 border-r border-gray-200">
                                <span class="text-sm text-gray-600">
                                    <?= !empty($proses['tanggal_selesai']) ? date('d/m/Y', strtotime($proses['tanggal_selesai'])) : '-' ?>
                                </span>
                            </td>
                            <td class="px-4 py-4 border-r border-gray-200">
                                <span class="text-sm font-medium text-gray-900">
                                    <?= !empty($proses['target']) ? number_format($proses['target'], 0, ',', '.') : '-' ?>
                                </span>
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
                class="font-medium"><?= (($pager->getCurrentPage('detailProses') - 1) * $pager->getPerPage('detailProses')) + 1 ?></span>-<span
                class="font-medium"><?= min($pager->getCurrentPage('detailProses') * $pager->getPerPage('detailProses'), $pager->getTotal('detailProses')) ?></span>
            dari <span class="font-medium"><?= $pager->getTotal('detailProses') ?></span> data
        </p>

        <!-- Custom Pagination -->
        <?php if ($pager->getPageCount('detailProses') > 1): ?>
            <?= $pager->links('detailProses', 'tailwind_pager') ?>
        <?php endif; ?>
    </div>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Search function untuk proses kegiatan
    function searchProsesTable() {
        const input = document.getElementById('searchProsesInput').value.toLowerCase();
        const table = document.getElementById('prosesTable');
        const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];
            const cells = row.getElementsByTagName('td');

            // Skip row pertama jika ada colspan (empty state)
            if (cells.length === 1 && cells[0].getAttribute('colspan')) {
                continue;
            }

            let found = false;
            for (let j = 1; j < cells.length; j++) {
                const cell = cells[j];
                if (cell) {
                    const textValue = cell.textContent || cell.innerText;
                    if (textValue.toLowerCase().indexOf(input) > -1) {
                        found = true;
                        break;
                    }
                }
            }

            row.style.display = found ? '' : 'none';
        }
    }

    // Handle animasi chevron untuk perPage proses selector
    const perPageProsesSelect = document.getElementById('perPageProsesSelect');
    const perPageProsesChevron = document.getElementById('perPageProsesChevron');

    perPageProsesSelect.addEventListener('focus', function () {
        perPageProsesChevron.classList.add('rotate-180');
    });

    perPageProsesSelect.addEventListener('blur', function () {
        perPageProsesChevron.classList.remove('rotate-180');
    });

    // Function untuk update perPage proses
    function updatePerPageProses() {
        const perPage = document.getElementById('perPageProsesSelect').value;
        const params = new URLSearchParams();
        if (perPage) params.append('perPage', perPage);
        window.location.href = '<?= base_url('superadmin/master-kegiatan-detail/show/' . $detail['id_kegiatan_detail']) ?>?' + params.toString();
    }

    // Delete confirmation untuk Kegiatan Detail
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
                        confirmButtonColor: '#3b82f6',
                        customClass: {
                            popup: 'rounded-xl',
                            confirmButton: 'px-6 py-2.5 rounded-lg font-medium'
                        }
                    }).then(() => {
                        window.location.href = '<?= base_url('superadmin/master-kegiatan-detail') ?>';
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