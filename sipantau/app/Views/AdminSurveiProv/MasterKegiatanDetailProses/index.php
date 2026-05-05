<?= $this->extend('layouts/adminprov_layout') ?>
<?= $this->section('content') ?>
<?php
/**
 * @var string|null $kegiatanDetailFilter
 * @var array $kegiatanDetailList
 * @var array $kegiatanDetails
 * @var int $perPage
 * @var object $pager
 * @var bool $isSuperAdmin
 */
?>
<!-- Page Header -->
<div class="mb-6">
    <div class="flex items-center text-sm text-gray-600 mb-4">
        <a href="<?= base_url('adminsurvei') ?>" class="hover:text-blue-600 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Back
        </a>
    </div>
    <h1 class="text-2xl font-bold text-gray-900">Kelola Master Kegiatan Detail Proses</h1>
    <p class="text-gray-600 mt-1">
        Kelola data detail kegiatan survei/sensus beserta satuan, periode, dan target pelaksanaan
    </p>
</div>

<!-- DESKTOP LAYOUT -->
<div class="card mb-6 hidden lg:block">
    <!-- Filter dan Search -->
    <div style="display: grid; grid-template-columns: 1fr 300px 200px 200px; gap: 1rem; margin-bottom: 1.5rem;">
        <!-- Filter Kegiatan Detail -->
        <form method="GET" action="<?= base_url('adminsurvei/master-kegiatan-detail-proses') ?>"
            style="display: contents;">
            <div class="relative">
                <label for="kegiatanDetailSelect" class="block text-sm font-medium text-gray-700 mb-1">
                    Filter Kegiatan Detail
                </label>
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                    style="margin-top: 28px;">
                    <i class="fas fa-filter text-gray-400"></i>
                </div>
                <select name="kegiatan_detail" id="kegiatanDetailSelect" class="input-field w-full pl-10"
                    onchange="this.form.submit()">
                    <option value="">Semua Kegiatan Detail</option>
                    <?php foreach ($kegiatanDetailList as $item): ?>
                        <option value="<?= esc($item['id_kegiatan_detail']) ?>"
                            <?= ($kegiatanDetailFilter == $item['id_kegiatan_detail']) ? 'selected' : '' ?>>
                            <?= esc($item['nama_kegiatan_detail']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <input type="hidden" name="perPage" value="<?= $perPage ?>">
        </form>

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
                    placeholder="Cari kegiatan atau satuan..." onkeyup="searchTable()">
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

        <!-- Add Button -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">&nbsp;</label>
            <a href="<?= base_url('adminsurvei/master-kegiatan-detail-proses/create') ?>"
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
        <!-- Filter Kegiatan Detail Mobile -->
        <form method="GET" action="<?= base_url('adminsurvei/master-kegiatan-detail-proses') ?>">
            <div class="w-full">
                <label for="kegiatanDetailSelectMobile" class="block text-sm font-medium text-gray-700 mb-1">
                    Filter Kegiatan Detail
                </label>
                <select name="kegiatan_detail" id="kegiatanDetailSelectMobile" class="input-field w-full text-sm"
                    onchange="this.form.submit()">
                    <option value="">Semua Kegiatan Detail</option>
                    <?php foreach ($kegiatanDetailList as $item): ?>
                        <option value="<?= esc($item['id_kegiatan_detail']) ?>"
                            <?= ($kegiatanDetailFilter == $item['id_kegiatan_detail']) ? 'selected' : '' ?>>
                            <?= esc($item['nama_kegiatan_detail']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <input type="hidden" name="perPage" value="<?= $perPage ?>">
        </form>

        <!-- Search Box Mobile -->
        <div class="w-full">
            <label for="searchInputMobile" class="block text-sm font-medium text-gray-700 mb-1">
                Pencarian
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                <input type="text" id="searchInputMobile" class="input-field w-full pl-10 text-sm"
                    placeholder="Cari kegiatan atau satuan..." onkeyup="searchTable()">
            </div>
        </div>

        <!-- Per Page Mobile -->
        <div class="w-full">
            <label for="perPageSelectMobile" class="block text-sm font-medium text-gray-700 mb-1">
                Data per Halaman
            </label>
            <select id="perPageSelectMobile" class="input-field w-full text-sm" onchange="updatePerPage()">
                <option value="5" <?= ($perPage == 5) ? 'selected' : ''; ?>>5</option>
                <option value="10" <?= ($perPage == 10) ? 'selected' : ''; ?>>10</option>
                <option value="25" <?= ($perPage == 25) ? 'selected' : ''; ?>>25</option>
                <option value="50" <?= ($perPage == 50) ? 'selected' : ''; ?>>50</option>
                <option value="100" <?= ($perPage == 100) ? 'selected' : ''; ?>>100</option>
            </select>
        </div>

        <!-- Add Button Mobile -->
        <div class="w-full">
            <a href="<?= base_url('adminsurvei/master-kegiatan-detail-proses/create') ?>"
                class="btn-primary w-full text-center inline-block">
                <i class="fas fa-plus mr-2"></i>
                Tambah Kegiatan
            </a>
        </div>
    </div>
</div>

<!-- Filter Info -->
<?php if ($kegiatanDetailFilter): ?>
    <div class="card mb-4">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 flex items-center justify-between">
            <div class="flex items-center gap-2 text-sm text-blue-700">
                <i class="fas fa-info-circle"></i>
                <span>Filter aktif:
                    <strong>
                        <?php
                        $selectedKegiatan = array_filter($kegiatanDetailList, fn($item) => $item['id_kegiatan_detail'] == $kegiatanDetailFilter);
                        echo !empty($selectedKegiatan) ? esc(reset($selectedKegiatan)['nama_kegiatan_detail']) : '-';
                        ?>
                    </strong>
                </span>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Table -->
<div class="card">
    <div class="overflow-x-auto">
        <table class="w-full" id="kegiatanDetailTable">
            <thead>
                <tr class="border-b border-gray-200 bg-gray-50">
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-16">No
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Master
                        Kegiatan Detail</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nama
                        Kegiatan Detail Proses</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-32">
                        Satuan</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-32">
                        Tanggal Mulai</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-32">
                        Tanggal Selesai</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-40">
                        Keterangan</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-24">
                        Periode</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-24">
                        Target</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-32">
                        Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (!empty($kegiatanDetails)): ?>
                    <?php foreach ($kegiatanDetails as $index => $detail): ?>
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-4 py-4 text-sm text-gray-900">
                                <?= ($pager->getCurrentPage('kegiatan_details') - 1) * $pager->getPerPage('kegiatan_details') + $index + 1 ?>
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-900"><?= esc($detail['nama_kegiatan_detail']) ?></td>
                            <td class="px-4 py-4 text-sm text-gray-600"><?= esc($detail['nama_kegiatan_detail_proses']) ?></td>
                            <td class="px-4 py-4 text-sm text-gray-600"><?= esc($detail['satuan']) ?></td>
                            <td class="px-4 py-4 text-sm text-gray-600"><?= esc($detail['tanggal_mulai']) ?></td>
                            <td class="px-4 py-4 text-sm text-gray-600"><?= esc($detail['tanggal_selesai']) ?></td>
                            <td class="px-4 py-4 text-sm text-center text-gray-600"><?= esc($detail['keterangan']) ?></td>
                            <td class="px-4 py-4 text-center"><span
                                    class="badge badge-info"><?= esc($detail['periode']) ?></span>
                            </td>
                            <td class="px-4 py-4 text-center text-gray-900 font-medium"><?= esc($detail['target']) ?></td>
                            <td class="px-4 py-4 text-center">
                                <a href="<?= base_url('adminsurvei/master-kegiatan-detail-proses/edit/' . $detail['id_kegiatan_detail_proses']) ?>"
                                    class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button
                                    onclick="confirmDelete(<?= $detail['id_kegiatan_detail_proses'] ?>, '<?= esc($detail['nama_kegiatan_detail_proses']) ?>')"
                                    class="p-2 text-red-600 hover:bg-red-50 rounded-lg">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10" class="px-4 py-6 text-center text-gray-500">
                            <?= $kegiatanDetailFilter ? 'Tidak ada data untuk filter yang dipilih.' : 'Belum ada data kegiatan detail proses.' ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <form id="deleteForm" method="post" style="display:none;">
            <?= csrf_field() ?>
            <input type="hidden" name="_method" value="DELETE">
        </form>
    </div>

    <!-- Footer dengan Pagination -->
    <div class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-4">
        <p class="text-sm text-gray-600">
            Menampilkan data
            <span
                class="font-medium"><?= (($pager->getCurrentPage('kegiatan_details') - 1) * $pager->getPerPage('kegiatan_details')) + 1 ?></span>-<span
                class="font-medium"><?= min($pager->getCurrentPage('kegiatan_details') * $pager->getPerPage('kegiatan_details'), $pager->getTotal('kegiatan_details')) ?></span>
            dari <span class="font-medium"><?= $pager->getTotal('kegiatan_details') ?></span> data
            <?php if ($kegiatanDetailFilter): ?>
                <span class="text-blue-600">(terfilter)</span>
            <?php endif; ?>
        </p>

        <!-- Custom Pagination -->
        <?php if ($pager->getPageCount('kegiatan_details') > 1): ?>
            <?= $pager->links('kegiatan_details', 'tailwind_pager') ?>
        <?php endif; ?>
    </div>
</div>

<!-- SweetAlert2 -->
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

    // Function untuk update perPage dengan mempertahankan filter
    function updatePerPage() {
        // Deteksi apakah desktop atau mobile
        const isDesktop = window.innerWidth >= 1024;

        let perPage;
        if (isDesktop) {
            perPage = document.getElementById('perPageSelect')?.value || '10';
        } else {
            perPage = document.getElementById('perPageSelectMobile')?.value || '10';
        }

        const params = new URLSearchParams(window.location.search);
        params.set('perPage', perPage);

        window.location.href = '<?= base_url('adminsurvei/master-kegiatan-detail-proses') ?>?' + params.toString();
    }

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

        const rows = document.querySelectorAll('#kegiatanDetailTable tbody tr');

        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    }

    function confirmDelete(id, name) {
        Swal.fire({
            title: 'Hapus Data?',
            html: `Yakin ingin menghapus <strong>${name}</strong>?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('deleteForm');
                form.action = `<?= base_url('adminsurvei/master-kegiatan-detail-proses/delete/') ?>${id}`;
                form.submit();
            }
        });
    }

    // Alert sukses setelah tambah/edit/hapus
    <?php if (session()->getFlashdata('success')): ?>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '<?= session()->getFlashdata('success') ?>',
            showConfirmButton: false,
            timer: 2000
        });
    <?php endif; ?>
</script>

<?= $this->endSection() ?>