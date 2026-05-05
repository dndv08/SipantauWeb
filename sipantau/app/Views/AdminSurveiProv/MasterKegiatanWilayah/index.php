<?= $this->extend('layouts/adminprov_layout') ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="mb-6">
    <div class="flex items-center text-sm text-gray-600 mb-4">
        <a href="<?= base_url('adminsurvei') ?>" class="hover:text-blue-600 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Back
        </a>
    </div>
    <h1 class="text-2xl font-bold text-gray-900">Kelola Master Kegiatan Wilayah</h1>
    <p class="text-gray-600 mt-1">Kelola data target survei untuk setiap kab/kota</p>
</div>


<!-- DESKTOP LAYOUT -->
<div class="card mb-6 hidden lg:block">
    <!-- Filter Section -->
    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6">
        <div class="flex items-center gap-2 mb-3">
            <i class="fas fa-filter text-gray-600"></i>
            <h3 class="text-sm font-semibold text-gray-700">Filter Data</h3>
        </div>

        <form method="GET" action="<?= base_url('adminsurvei/master-kegiatan-wilayah') ?>" id="filterForm">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Filter Kegiatan Detail -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Kegiatan Detail</label>
                    <select name="kegiatan_detail" id="filterKegiatanDetail" class="input-field"
                        onchange="loadKegiatanProses(this.value)">
                        <option value="">Semua Kegiatan</option>
                        <?php foreach ($allKegiatanDetail as $kd): ?>
                            <option value="<?= $kd['id_kegiatan_detail'] ?>" <?= ($filterKegiatan == $kd['id_kegiatan_detail']) ? 'selected' : '' ?>>
                                <?= esc($kd['nama_kegiatan']) ?> -
                                <?= esc($kd['nama_kegiatan_detail']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Filter Kegiatan Detail Proses (Dependent) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Kegiatan Detail Proses</label>
                    <select name="kegiatan_proses" id="filterKegiatanProses" class="input-field"
                        onchange="applyFilter()" <?= empty($filterKegiatan) ? 'disabled' : '' ?>>
                        <option value="">Semua Proses</option>
                        <?php foreach ($allKegiatanDetailProses as $kdp): ?>
                            <option value="<?= $kdp['id_kegiatan_detail_proses'] ?>"
                                <?= ($filterProses == $kdp['id_kegiatan_detail_proses']) ? 'selected' : '' ?>>
                                <?= esc($kdp['nama_kegiatan_detail_proses']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Filter Kabupaten -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Kabupaten</label>
                    <select name="kabupaten" id="filterKabupaten" class="input-field" onchange="applyFilter()">
                        <option value="">Semua Kabupaten</option>
                        <?php foreach ($allKabupaten as $kab): ?>
                            <option value="<?= $kab['id_kabupaten'] ?>" <?= ($filterKabupaten == $kab['id_kabupaten']) ? 'selected' : '' ?>>
                                <?= esc($kab['nama_kabupaten']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Reset Filter Button -->
            <?php if (!empty($filterKegiatan) || !empty($filterProses) || !empty($filterKabupaten)): ?>
                <div class="mt-3 pt-3 border-t border-gray-200">
                    <a href="<?= base_url('adminsurvei/master-kegiatan-wilayah/clear-filter') ?>"
                        class="inline-flex items-center text-sm text-blue-600 hover:text-blue-700 hover:underline">
                        <i class="fas fa-times-circle mr-1.5"></i>
                        Reset Semua Filter
                    </a>
                </div>
            <?php endif; ?>
        </form>
    </div>

    <!-- Search, PerPage, and Action Buttons -->
    <div style="display: grid; grid-template-columns: 1fr 200px 200px 200px; gap: 1rem;">
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
                    placeholder="Cari kegiatan atau kabupaten..." onkeyup="searchTable()">
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

        <!-- Import Button -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">&nbsp;</label>
            <button onclick="openImportModal()" class="btn-secondary whitespace-nowrap w-full text-center">
                <i class="fas fa-file-excel mr-2"></i>
                Import Excel
            </button>
        </div>

        <!-- Add Button -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">&nbsp;</label>
            <button onclick="openModal()" class="btn-primary whitespace-nowrap w-full text-center">
                <i class="fas fa-plus mr-2"></i>
                Tambah Kegiatan
            </button>
        </div>
    </div>
</div>

<!-- MOBILE LAYOUT -->
<div class="card mb-6 lg:hidden">
    <!-- Filter Section Mobile -->
    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-4">
        <div class="flex items-center gap-2 mb-3">
            <i class="fas fa-filter text-gray-600"></i>
            <h3 class="text-sm font-semibold text-gray-700">Filter Data</h3>
        </div>

        <form method="GET" action="<?= base_url('adminsurvei/master-kegiatan-wilayah') ?>" id="filterFormMobile">
            <div class="space-y-3">
                <!-- Filter Kegiatan Detail Mobile -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Kegiatan Detail</label>
                    <select name="kegiatan_detail" id="filterKegiatanDetailMobile" class="input-field text-sm"
                        onchange="loadKegiatanProsesMobile(this.value)">
                        <option value="">Semua Kegiatan</option>
                        <?php foreach ($allKegiatanDetail as $kd): ?>
                            <option value="<?= $kd['id_kegiatan_detail'] ?>" <?= ($filterKegiatan == $kd['id_kegiatan_detail']) ? 'selected' : '' ?>>
                                <?= esc($kd['nama_kegiatan']) ?> -
                                <?= esc($kd['nama_kegiatan_detail']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Filter Kegiatan Detail Proses Mobile -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Kegiatan Detail Proses</label>
                    <select name="kegiatan_proses" id="filterKegiatanProsesMobile" class="input-field text-sm"
                        onchange="applyFilterMobile()" <?= empty($filterKegiatan) ? 'disabled' : '' ?>>
                        <option value="">Semua Proses</option>
                        <?php foreach ($allKegiatanDetailProses as $kdp): ?>
                            <option value="<?= $kdp['id_kegiatan_detail_proses'] ?>"
                                <?= ($filterProses == $kdp['id_kegiatan_detail_proses']) ? 'selected' : '' ?>>
                                <?= esc($kdp['nama_kegiatan_detail_proses']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Filter Kabupaten Mobile -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Kabupaten</label>
                    <select name="kabupaten" id="filterKabupatenMobile" class="input-field text-sm"
                        onchange="applyFilterMobile()">
                        <option value="">Semua Kabupaten</option>
                        <?php foreach ($allKabupaten as $kab): ?>
                            <option value="<?= $kab['id_kabupaten'] ?>" <?= ($filterKabupaten == $kab['id_kabupaten']) ? 'selected' : '' ?>>
                                <?= esc($kab['nama_kabupaten']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Reset Filter Button Mobile -->
            <?php if (!empty($filterKegiatan) || !empty($filterProses) || !empty($filterKabupaten)): ?>
                <div class="mt-3 pt-3 border-t border-gray-200">
                    <a href="<?= base_url('adminsurvei/master-kegiatan-wilayah/clear-filter') ?>"
                        class="inline-flex items-center text-sm text-blue-600 hover:text-blue-700 hover:underline">
                        <i class="fas fa-times-circle mr-1.5"></i>
                        Reset Semua Filter
                    </a>
                </div>
            <?php endif; ?>
        </form>
    </div>

    <!-- Search, PerPage, and Action Buttons Mobile -->
    <div class="space-y-3">
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
                    placeholder="Cari kegiatan atau kabupaten..." onkeyup="searchTable()">
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

        <!-- Import Button Mobile -->
        <div class="w-full">
            <button onclick="openImportModal()" class="btn-secondary w-full text-center">
                <i class="fas fa-file-excel mr-2"></i>
                Import Excel
            </button>
        </div>

        <!-- Add Button Mobile -->
        <div class="w-full">
            <button onclick="openModal()" class="btn-primary w-full text-center">
                <i class="fas fa-plus mr-2"></i>
                Tambah Kegiatan
            </button>
        </div>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div class="overflow-x-auto">
        <table class="w-full" id="kegiatanDetailTable">
            <thead>
                <tr class="border-b border-gray-200 bg-gray-50">
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-12">No
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nama
                        Kegiatan Detail Proses</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Kab/Kota</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-28">
                        Tanggal Mulai</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-28">
                        Tanggal Selesai</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-32">
                        Keterangan</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-28">
                        Target</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-24">
                        Progress</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-24">
                        Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (!empty($kegiatanWilayah)): ?>
                    <?php foreach ($kegiatanWilayah as $index => $kg): ?>
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-4 py-3 text-sm text-gray-900">
                                <?= ($pager->getCurrentPage('kegiatan_wilayah') - 1) * $pager->getPerPage('kegiatan_wilayah') + $index + 1 ?>
                            </td>
                            <td class="px-4 py-3 text-sm">
    <div class="font-medium text-gray-900">
        <?= esc($kg['nama_kegiatan_detail']) ?>
    </div>
    <div class="text-xs text-gray-500 mt-0.5">
        (<?= esc($kg['nama_kegiatan_detail_proses']) ?>)
    </div>
</td>
                            <td class="px-4 py-3 text-sm text-gray-600"><?= esc($kg['nama_kabupaten']) ?></td>
                            <td class="px-4 py-3 text-xs text-gray-600"><?= esc($kg['tanggal_mulai']) ?></td>
                            <td class="px-4 py-3 text-xs text-gray-600"><?= esc($kg['tanggal_selesai']) ?></td>
                            <td class="px-4 py-3 text-xs text-gray-600"><?= esc($kg['keterangan']) ?></td>
                            <td class="px-4 py-3 text-center">
                                <div class="text-sm font-semibold text-gray-900"><?= number_format($kg['target_wilayah']) ?>
                                </div>
                                <div class="text-xs text-gray-500 mt-0.5">
                                    <span class="text-gray-400">Realisasi:</span> <?= number_format($kg['realisasi']) ?>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-col items-center">
                                    <div class="relative inline-flex items-center justify-center">
                                        <svg class="transform -rotate-90" width="50" height="50">
                                            <circle cx="25" cy="25" r="20" stroke="#e5e7eb" stroke-width="4" fill="none" />
                                            <circle cx="25" cy="25" r="20" stroke="<?= $kg['progress_color'] ?>"
                                                stroke-width="4" fill="none" stroke-dasharray="<?= (2 * 3.14159 * 20) ?>"
                                                stroke-dashoffset="<?= (2 * 3.14159 * 20) * (1 - ($kg['progress'] / 100)) ?>"
                                                stroke-linecap="round" />
                                        </svg>
                                        <span class="absolute text-xs font-semibold"
                                            style="color: <?= $kg['progress_color'] ?>">
                                            <?= number_format($kg['progress'], 1) ?>%
                                        </span>
                                    </div>
                                    <div class="mt-1 text-xs text-center">
                                        <?php if ($kg['progress'] >= 80): ?>
                                            <span class="text-green-600 font-medium">Sangat Baik</span>
                                        <?php elseif ($kg['progress'] >= 50): ?>
                                            <span class="text-blue-600 font-medium">Baik</span>
                                        <?php elseif ($kg['progress'] >= 25): ?>
                                            <span class="text-orange-600 font-medium">Sedang</span>
                                        <?php else: ?>
                                            <span class="text-red-600 font-medium">Rendah</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button onclick="openEditModal(<?= $kg['id_kegiatan_wilayah'] ?>)"
                                        class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg">
                                        <i class="fas fa-edit text-sm"></i>
                                    </button>
                                    <button
                                        onclick="confirmDelete(<?= $kg['id_kegiatan_wilayah'] ?>, '<?= esc($kg['keterangan']) ?>')"
                                        class="p-2 text-red-600 hover:bg-red-50 rounded-lg">
                                        <i class="fas fa-trash text-sm"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl text-gray-300 mb-3"></i>
                            <p>Belum ada data kegiatan wilayah.</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <!-- Footer dengan Pagination -->
    <div class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-4">
        <p class="text-sm text-gray-600">
            Menampilkan data
            <span
                class="font-medium"><?= (($pager->getCurrentPage('kegiatan_wilayah') - 1) * $pager->getPerPage('kegiatan_wilayah')) + 1 ?></span>-<span
                class="font-medium"><?= min($pager->getCurrentPage('kegiatan_wilayah') * $pager->getPerPage('kegiatan_wilayah'), $pager->getTotal('kegiatan_wilayah')) ?></span>
            dari <span class="font-medium"><?= $pager->getTotal('kegiatan_wilayah') ?></span> data
            <?php if ($filterKegiatan || $filterProses || $filterKabupaten): ?>
                <span class="text-blue-600">(terfilter)</span>
            <?php endif; ?>
        </p>

        <!-- Custom Pagination -->
        <?php if ($pager->getPageCount('kegiatan_wilayah') > 1): ?>
            <?= $pager->links('kegiatan_wilayah', 'tailwind_pager') ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Form Tambah -->
<div id="modalTambah" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900">Tambah Kegiatan Wilayah</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <form id="formTambah" class="p-6">
            <?= csrf_field() ?>

            <div class="mb-4 bg-blue-50 border border-blue-200 rounded-lg p-3">
                <p class="text-sm text-blue-700">
                    <i class="fas fa-info-circle mr-2"></i>
                    Lengkapi semua field yang bertanda <span class="text-red-500 font-semibold">*</span>
                </p>
            </div>

            <!-- Kegiatan Detail Proses -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Kegiatan Detail Proses <span class="text-red-500">*</span>
                </label>
                <select name="kegiatan_detail" id="modalKegiatanDetail" class="input-field" required>
                    <option value="">-- Pilih Kegiatan Detail Proses --</option>
                    <?php foreach ($allKegiatanDetailProsesForModal as $kdp): ?>
                        <option value="<?= $kdp['id_kegiatan_detail_proses'] ?>"
                            <?= ($filterProses == $kdp['id_kegiatan_detail_proses']) ? 'selected' : '' ?>>
                            <?= esc($kdp['nama_kegiatan_detail']) ?> - <?= esc($kdp['nama_kegiatan_detail_proses']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Kabupaten -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Kabupaten/Kota <span class="text-red-500">*</span>
                </label>
                <select name="kabupaten" id="modalKabupaten" class="input-field" required>
                    <option value="">-- Pilih Kabupaten/Kota --</option>
                    <?php foreach ($allKabupaten as $kab): ?>
                        <option value="<?= $kab['id_kabupaten'] ?>">
                            <?= esc($kab['nama_kabupaten']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Target Wilayah -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Target Wilayah <span class="text-red-500">*</span>
                </label>
                <input type="number" name="target" id="modalTarget" class="input-field"
                    placeholder="Masukkan target wilayah" min="1" required>
                <p id="modalSisaInfo" class="text-sm text-gray-500 mt-1"></p>
            </div>

            <!-- Keterangan -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Keterangan <span class="text-red-500">*</span>
                </label>
                <textarea name="keterangan" id="modalKeterangan" rows="3" class="input-field resize-none"
                    placeholder="Masukkan keterangan" required></textarea>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-3 justify-end pt-4 border-t">
                <button type="button" onclick="closeModal()" class="btn-secondary px-6">
                    Batal
                </button>
                <button type="submit" class="btn-primary px-6">
                    <i class="fas fa-save mr-2"></i>
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Form Edit -->
<div id="modalEdit" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900">Edit Kegiatan Wilayah</h3>
            <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <form id="formEdit" class="p-6">
            <?= csrf_field() ?>
            <input type="hidden" id="editId" name="id">

            <div class="mb-4 bg-blue-50 border border-blue-200 rounded-lg p-3">
                <p class="text-sm text-blue-700">
                    <i class="fas fa-info-circle mr-2"></i>
                    Lengkapi semua field yang bertanda <span class="text-red-500 font-semibold">*</span>
                </p>
            </div>

            <!-- Kegiatan Detail Proses -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Kegiatan Detail Proses <span class="text-red-500">*</span>
                </label>
                <select name="kegiatan_detail" id="editKegiatanDetail" class="input-field" required>
                    <option value="">-- Pilih Kegiatan Detail Proses --</option>
                    <?php foreach ($allKegiatanDetailProsesForModal as $kdp): ?>
                        <option value="<?= $kdp['id_kegiatan_detail_proses'] ?>">
                            <?= esc($kdp['nama_kegiatan_detail']) ?> - <?= esc($kdp['nama_kegiatan_detail_proses']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Kabupaten -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Kabupaten/Kota <span class="text-red-500">*</span>
                </label>
                <select name="kabupaten" id="editKabupaten" class="input-field" required>
                    <option value="">-- Pilih Kabupaten/Kota --</option>
                    <?php foreach ($allKabupaten as $kab): ?>
                        <option value="<?= $kab['id_kabupaten'] ?>">
                            <?= esc($kab['nama_kabupaten']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Target Wilayah -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Target Wilayah <span class="text-red-500">*</span>
                </label>
                <input type="number" name="target" id="editTarget" class="input-field"
                    placeholder="Masukkan target wilayah" min="1" required>
                <p id="editSisaInfo" class="text-sm text-gray-500 mt-1"></p>
            </div>

            <!-- Keterangan -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Keterangan <span class="text-red-500">*</span>
                </label>
                <textarea name="keterangan" id="editKeterangan" rows="3" class="input-field resize-none"
                    placeholder="Masukkan keterangan" required></textarea>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-3 justify-end pt-4 border-t">
                <button type="button" onclick="closeEditModal()" class="btn-secondary px-6">
                    Batal
                </button>
                <button type="submit" class="btn-primary px-6">
                    <i class="fas fa-save mr-2"></i>
                    Update
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Import Excel -->
<div id="modalImport" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full">
        <div
            class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center rounded-t-lg">
            <h3 class="text-lg font-semibold text-gray-900">Import Data dari Excel</h3>
            <button onclick="closeImportModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <div class="p-6">
            <!-- Step 1: Pilih Kegiatan -->
            <div id="step1" class="mb-6">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                    <p class="text-sm text-blue-700">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Langkah 1:</strong> Pilih Kegiatan Detail Proses untuk mengunduh template
                    </p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Pilih Kegiatan Detail Proses <span class="text-red-500">*</span>
                    </label>
                    <select id="importKegiatanProses" class="input-field">
                        <option value="">-- Pilih Kegiatan Detail Proses --</option>
                        <?php foreach ($allKegiatanDetailProsesForModal as $kdp): ?>
                            <option value="<?= $kdp['id_kegiatan_detail_proses'] ?>">
                                <?= esc($kdp['nama_kegiatan_detail']) ?> - <?= esc($kdp['nama_kegiatan_detail_proses']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button onclick="downloadTemplate()" id="btnDownloadTemplate" disabled
                    class="btn-primary w-full disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-download mr-2"></i>
                    Download Template Excel
                </button>
            </div>

            <!-- Divider -->
            <div class="border-t border-gray-200 my-6"></div>

            <!-- Step 2: Upload File -->
            <div id="step2">
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                    <p class="text-sm text-green-700">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Langkah 2:</strong> Upload file Excel yang sudah diisi
                    </p>
                </div>

                <form id="formImport" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <input type="hidden" id="importKegiatanProsesId" name="id_kegiatan_detail_proses">

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            File Excel <span class="text-red-500">*</span>
                        </label>
                        <input type="file" id="fileImport" name="file" accept=".xlsx,.xls" class="input-field" required>
                        <p class="mt-1 text-xs text-gray-500">
                            Format file: .xlsx atau .xls (Maksimal 5MB)
                        </p>
                    </div>

                    <div class="flex gap-3">
                        <button type="button" onclick="closeImportModal()" class="btn-secondary flex-1">
                            Batal
                        </button>
                        <button type="submit" class="btn-primary flex-1">
                            <i class="fas fa-upload mr-2"></i>
                            Upload & Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // ==================== PERPAGE FUNCTIONS ====================
    // Handle animasi chevron untuk perPage selector
    const perPageSelect = document.getElementById('perPageSelect');
    const perPageChevron = document.getElementById('perPageChevron');

    if (perPageSelect && perPageChevron) {
        perPageSelect.addEventListener('focus', function () {
            perPageChevron.classList.add('rotate-180');
        });

        perPageSelect.addEventListener('blur', function () {
            perPageChevron.classList.remove('rotate-180');
        });
    }

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

        window.location.href = '<?= base_url('adminsurvei/master-kegiatan-wilayah') ?>?' + params.toString();
    }

    // ==================== FILTER FUNCTIONS ====================
    function loadKegiatanProses(idKegiatanDetail) {
        const filterProses = document.getElementById('filterKegiatanProses');

        if (!idKegiatanDetail) {
            filterProses.disabled = true;
            filterProses.innerHTML = '<option value="">Semua Proses</option>';
            applyFilter();
            return;
        }

        fetch(`<?= base_url('adminsurvei/master-kegiatan-wilayah/get-kegiatan-detail-proses/') ?>${idKegiatanDetail}`)
            .then(response => response.json())
            .then(result => {
                filterProses.disabled = false;
                filterProses.innerHTML = '<option value="">Semua Proses</option>';

                if (result.success && result.data) {
                    result.data.forEach(proses => {
                        const option = document.createElement('option');
                        option.value = proses.id_kegiatan_detail_proses;
                        option.textContent = proses.nama_kegiatan_detail_proses;
                        filterProses.appendChild(option);
                    });
                }

                applyFilter();
            });
    }

    function loadKegiatanProsesMobile(idKegiatanDetail) {
        const filterProses = document.getElementById('filterKegiatanProsesMobile');

        if (!idKegiatanDetail) {
            filterProses.disabled = true;
            filterProses.innerHTML = '<option value="">Semua Proses</option>';
            applyFilterMobile();
            return;
        }

        fetch(`<?= base_url('adminsurvei/master-kegiatan-wilayah/get-kegiatan-detail-proses/') ?>${idKegiatanDetail}`)
            .then(response => response.json())
            .then(result => {
                filterProses.disabled = false;
                filterProses.innerHTML = '<option value="">Semua Proses</option>';

                if (result.success && result.data) {
                    result.data.forEach(proses => {
                        const option = document.createElement('option');
                        option.value = proses.id_kegiatan_detail_proses;
                        option.textContent = proses.nama_kegiatan_detail_proses;
                        filterProses.appendChild(option);
                    });
                }

                applyFilterMobile();
            });
    }

    function applyFilter() {
        document.getElementById('filterForm').submit();
    }

    function applyFilterMobile() {
        document.getElementById('filterFormMobile').submit();
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

        const table = document.getElementById('kegiatanDetailTable');
        const rows = table.getElementsByTagName('tr');

        for (let i = 1; i < rows.length; i++) {
            const row = rows[i];
            const cells = row.getElementsByTagName('td');
            let found = false;

            // Skip jika row adalah "tidak ada data"
            if (cells.length === 1 && cells[0].getAttribute('colspan')) {
                continue;
            }

            for (let j = 1; j < cells.length - 1; j++) {
                if (cells[j] && cells[j].innerText.toLowerCase().includes(filter)) {
                    found = true;
                    break;
                }
            }
            row.style.display = found ? '' : 'none';
        }
    }

    // ==================== MODAL TAMBAH FUNCTIONS ====================
    function openModal() {
        document.getElementById('modalTambah').classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        // Auto-select dari filter jika ada
        <?php if ($filterProses): ?>
            document.getElementById('modalKegiatanDetail').value = '<?= $filterProses ?>';
            loadModalSisaTarget('<?= $filterProses ?>');
        <?php endif; ?>
    }

    function closeModal() {
        document.getElementById('modalTambah').classList.add('hidden');
        document.body.style.overflow = 'auto';
        document.getElementById('formTambah').reset();
        document.getElementById('modalSisaInfo').innerHTML = '';
    }

    // ==================== MODAL EDIT FUNCTIONS ====================
    function openEditModal(id) {
        fetch(`<?= base_url('adminsurvei/master-kegiatan-wilayah/edit/') ?>${id}`)
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    const data = result.data;
                    document.getElementById('editId').value = data.id_kegiatan_wilayah;
                    document.getElementById('editKegiatanDetail').value = data.id_kegiatan_detail_proses;
                    document.getElementById('editKabupaten').value = data.id_kabupaten;
                    document.getElementById('editTarget').value = data.target_wilayah;
                    document.getElementById('editKeterangan').value = data.keterangan;

                    // Load sisa target
                    loadEditSisaTarget(data.id_kegiatan_detail_proses, data.id_kegiatan_wilayah);

                    document.getElementById('modalEdit').classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: result.message || 'Data tidak ditemukan',
                        confirmButtonColor: '#ef4444'
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Terjadi kesalahan saat memuat data',
                    confirmButtonColor: '#ef4444'
                });
            });
    }

    function closeEditModal() {
        document.getElementById('modalEdit').classList.add('hidden');
        document.body.style.overflow = 'auto';
        document.getElementById('formEdit').reset();
        document.getElementById('editSisaInfo').innerHTML = '';
    }

    // ==================== LOAD SISA TARGET FUNCTIONS ====================
    function loadModalSisaTarget(idKegiatan) {
        const targetInput = document.getElementById('modalTarget');
        const sisaInfo = document.getElementById('modalSisaInfo');

        if (!idKegiatan) {
            targetInput.value = '';
            targetInput.removeAttribute('max');
            sisaInfo.textContent = '';
            return;
        }

        fetch(`<?= base_url('adminsurvei/master-kegiatan-wilayah/sisa-target/') ?>${idKegiatan}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    sisaInfo.innerHTML = `<span class="text-red-600"><i class="fas fa-exclamation-triangle mr-1"></i>${data.error}</span>`;
                } else {
                    sisaInfo.innerHTML = `
                    <span class="inline-flex items-center gap-3 text-xs">
                        <span><strong>Target Provinsi:</strong> ${data.target_prov.toLocaleString()}</span>
                        <span class="text-gray-300">|</span>
                        <span><strong>Terpakai:</strong> ${data.terpakai.toLocaleString()}</span>
                        <span class="text-gray-300">|</span>
                        <span class="text-green-600 font-semibold"><strong>Sisa:</strong> ${data.sisa.toLocaleString()}</span>
                    </span>
                `;
                    targetInput.max = data.sisa;
                    if (!targetInput.value) {
                        targetInput.value = data.sisa;
                    }
                }
            });
    }

    function loadEditSisaTarget(idKegiatan, currentId) {
        const targetInput = document.getElementById('editTarget');
        const sisaInfo = document.getElementById('editSisaInfo');

        if (!idKegiatan) {
            targetInput.removeAttribute('max');
            sisaInfo.textContent = '';
            return;
        }

        fetch(`<?= base_url('adminsurvei/master-kegiatan-wilayah/sisa-target/') ?>${idKegiatan}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    sisaInfo.innerHTML = `<span class="text-red-600"><i class="fas fa-exclamation-triangle mr-1"></i>${data.error}</span>`;
                } else {
                    sisaInfo.innerHTML = `
                    <span class="inline-flex items-center gap-3 text-xs">
                        <span><strong>Target Provinsi:</strong> ${data.target_prov.toLocaleString()}</span>
                        <span class="text-gray-300">|</span>
                        <span><strong>Terpakai:</strong> ${data.terpakai.toLocaleString()}</span>
                        <span class="text-gray-300">|</span>
                        <span class="text-green-600 font-semibold"><strong>Sisa:</strong> ${data.sisa.toLocaleString()}</span>
                    </span>
                `;
                    // Untuk edit, sisa target + target saat ini
                    const currentTarget = parseInt(targetInput.value) || 0;
                    targetInput.max = data.sisa + currentTarget;
                }
            });
    }

    // ==================== FORM SUBMIT HANDLERS ====================
    document.getElementById('formTambah').addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('<?= base_url('adminsurvei/master-kegiatan-wilayah/store') ?>', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: result.message,
                        showConfirmButton: false,
                        timer: 2000
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: result.message || 'Terjadi kesalahan',
                        confirmButtonColor: '#ef4444'
                    });
                }
            });
    });

    document.getElementById('formEdit').addEventListener('submit', function (e) {
        e.preventDefault();
        const id = document.getElementById('editId').value;
        const formData = new FormData(this);

        fetch(`<?= base_url('adminsurvei/master-kegiatan-wilayah/update/') ?>${id}`, {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: result.message,
                        showConfirmButton: false,
                        timer: 2000
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: result.message || 'Terjadi kesalahan',
                        confirmButtonColor: '#ef4444'
                    });
                }
            });
    });

    // ==================== DELETE FUNCTION ====================
    function confirmDelete(id, name) {
        Swal.fire({
            title: 'Hapus Data?',
            html: `Yakin ingin menghapus <strong>${name}</strong>?<br><small class="text-gray-600">Kurva S terkait juga akan dihapus.</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="fas fa-trash mr-2"></i>Ya, Hapus',
            cancelButtonText: 'Batal',
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Menghapus...',
                    html: 'Mohon tunggu sebentar',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                const formData = new FormData();
                formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

                fetch(`<?= base_url('adminsurvei/master-kegiatan-wilayah/delete/') ?>${id}`, {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: result.message,
                                confirmButtonColor: '#3b82f6',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: result.message || 'Terjadi kesalahan saat menghapus data',
                                confirmButtonColor: '#ef4444'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Terjadi kesalahan pada sistem',
                            confirmButtonColor: '#ef4444'
                        });
                    });
            }
        });
    }

    // ==================== IMPORT MODAL FUNCTIONS ====================
    function openImportModal() {
        document.getElementById('modalImport').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeImportModal() {
        document.getElementById('modalImport').classList.add('hidden');
        document.body.style.overflow = 'auto';
        document.getElementById('formImport').reset();
        document.getElementById('importKegiatanProses').value = '';
        document.getElementById('btnDownloadTemplate').disabled = true;
        document.getElementById('importKegiatanProsesId').value = '';
    }

    function downloadTemplate() {
        const kegiatanProsesId = document.getElementById('importKegiatanProses').value;

        if (!kegiatanProsesId) {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian!',
                text: 'Pilih Kegiatan Detail Proses terlebih dahulu',
                confirmButtonColor: '#f59e0b'
            });
            return;
        }

        Swal.fire({
            title: 'Mengunduh Template...',
            html: 'Mohon tunggu sebentar',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        window.location.href = `<?= base_url('adminsurvei/master-kegiatan-wilayah/download-template/') ?>${kegiatanProsesId}`;

        setTimeout(() => {
            Swal.close();
        }, 2000);
    }

    // ==================== EVENT LISTENERS ====================
    // Event listener untuk modal kegiatan detail change
    document.getElementById('modalKegiatanDetail').addEventListener('change', function () {
        loadModalSisaTarget(this.value);
    });

    document.getElementById('editKegiatanDetail').addEventListener('change', function () {
        const editId = document.getElementById('editId').value;
        loadEditSisaTarget(this.value, editId);
    });

    // Event listener untuk import kegiatan proses change
    document.getElementById('importKegiatanProses').addEventListener('change', function () {
        const btnDownload = document.getElementById('btnDownloadTemplate');
        const kegiatanProsesId = this.value;

        if (kegiatanProsesId) {
            btnDownload.disabled = false;
            document.getElementById('importKegiatanProsesId').value = kegiatanProsesId;
        } else {
            btnDownload.disabled = true;
            document.getElementById('importKegiatanProsesId').value = '';
        }
    });

    // Form Import Submit
    document.getElementById('formImport').addEventListener('submit', function (e) {
        e.preventDefault();

        const fileInput = document.getElementById('fileImport');
        const kegiatanProsesId = document.getElementById('importKegiatanProsesId').value;

        if (!fileInput.files.length) {
            Swal.fire({
                icon: 'warning',
                title: 'File Belum Dipilih',
                text: 'Pilih file Excel terlebih dahulu',
                confirmButtonColor: '#f59e0b'
            });
            return;
        }

        if (!kegiatanProsesId) {
            Swal.fire({
                icon: 'warning',
                title: 'Kegiatan Belum Dipilih',
                text: 'Pilih Kegiatan Detail Proses terlebih dahulu',
                confirmButtonColor: '#f59e0b'
            });
            return;
        }

        const file = fileInput.files[0];
        const fileExt = file.name.split('.').pop().toLowerCase();

        if (!['xlsx', 'xls'].includes(fileExt)) {
            Swal.fire({
                icon: 'error',
                title: 'Format File Salah',
                text: 'File harus berformat .xlsx atau .xls',
                confirmButtonColor: '#ef4444'
            });
            return;
        }

        if (file.size > 5 * 1024 * 1024) {
            Swal.fire({
                icon: 'error',
                title: 'File Terlalu Besar',
                text: 'Ukuran file maksimal 5MB',
                confirmButtonColor: '#ef4444'
            });
            return;
        }

        Swal.fire({
            title: 'Import Data?',
            text: 'Data dari file Excel akan diimport ke sistem',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3b82f6',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="fas fa-upload mr-2"></i>Ya, Import',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData(this);

                Swal.fire({
                    title: 'Mengimport Data...',
                    html: 'Mohon tunggu, proses import sedang berjalan',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                fetch('<?= base_url('adminsurvei/master-kegiatan-wilayah/import') ?>', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            let htmlMessage = result.message;
                            if (result.errors) {
                                htmlMessage += '<br><br><div style="text-align: left; max-height: 200px; overflow-y: auto; padding: 10px; background: #fef3c7; border-radius: 5px;"><strong>Catatan:</strong><br><pre style="font-size: 11px; margin: 5px 0;">' + result.errors + '</pre></div>';
                            }

                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                html: htmlMessage,
                                confirmButtonColor: '#3b82f6',
                                width: '600px'
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal Import',
                                html: result.message || 'Terjadi kesalahan saat import data',
                                confirmButtonColor: '#ef4444'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Terjadi kesalahan pada sistem',
                            confirmButtonColor: '#ef4444'
                        });
                    });
            }
        });
    });

    // ==================== FLASHDATA ALERTS ====================
    <?php if (session()->getFlashdata('success')): ?>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: "<?= session()->getFlashdata('success') ?>",
            confirmButtonColor: '#3b82f6'
        });
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: "<?= session()->getFlashdata('error') ?>",
            confirmButtonColor: '#ef4444'
        });
    <?php endif; ?>

    <?php if (session()->getFlashdata('warning')): ?>
        Swal.fire({
            icon: 'warning',
            title: 'Perhatian!',
            text: "<?= session()->getFlashdata('warning') ?>",
            confirmButtonColor: '#f59e0b'
        });
    <?php endif; ?>

    <?php if (session()->getFlashdata('import_errors')): ?>
        Swal.fire({
            icon: 'warning',
            title: 'Import Selesai dengan Catatan',
            html: '<div style="text-align: left; max-height: 300px; overflow-y: auto;"><pre><?= session()->getFlashdata('import_errors') ?></pre></div>',
            confirmButtonColor: '#3b82f6',
            width: '600px'
        });
    <?php endif; ?>
</script>
<?= $this->endSection() ?>