<?= $this->extend('layouts/pemantau_provinsi_layout') ?>
<?= $this->section('content') ?>

<!-- Page Header -->
<div class="mb-6">
    <div class="flex items-center text-sm text-gray-600 mb-4">
        <a href="<?= base_url('pemantau-provinsi') ?>" class="hover:text-blue-600 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Back
        </a>
    </div>
    <h1 class="text-2xl font-bold text-gray-900">Kelola Master Kegiatan Wilayah</h1>
    <p class="text-gray-600 mt-1">Kelola data target survei untuk setiap kab/kota.</p>
</div>

<!-- DESKTOP LAYOUT -->
<div class="card mb-6 hidden lg:block">
    <!-- Search dan Filter Section -->
    <div style="display: grid; grid-template-columns: 1fr 1.2fr 200px; gap: 1rem;">
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
                    placeholder="Cari kegiatan detail atau satuan..." onkeyup="searchTable()">
            </div>
        </div>

        <!-- Filter Kabupaten -->
        <div style="margin-left: 20rem;">
            <label for="kabupatenFilter" class="block text-sm font-medium text-gray-700 mb-1">
                Filter Kabupaten
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-filter text-gray-400"></i>
                </div>
                <select name="kabupaten" id="kabupatenFilter"
                    class="input-field w-full pl-10 pr-10 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 appearance-none cursor-pointer"
                    style="text-overflow: ellipsis;">
                    <option value="">-- Semua Kabupaten --</option>
                    <?php foreach ($kabupatenList as $kab): ?>
                        <option value="<?= $kab['id_kabupaten']; ?>" <?= ($selectedKabupaten == $kab['id_kabupaten']) ? 'selected' : ''; ?>>
                            <?= esc($kab['nama_kabupaten']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <i id="kabupatenChevron"
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
                    class="input-field w-full pr-10 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 appearance-none cursor-pointer">
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
                    placeholder="Cari kegiatan detail atau satuan..." onkeyup="searchTable()">
            </div>
        </div>

        <!-- Filter Kabupaten Mobile -->
        <div class="w-full">
            <label class="block text-sm font-medium text-gray-700 mb-1">Filter Kabupaten</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-filter text-gray-400"></i>
                </div>
                <select name="kabupaten" id="kabupatenFilterMobile"
                    class="input-field w-full pl-10 text-sm border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 appearance-none">
                    <option value="">-- Semua Kabupaten --</option>
                    <?php foreach ($kabupatenList as $kab): ?>
                        <option value="<?= $kab['id_kabupaten']; ?>" <?= ($selectedKabupaten == $kab['id_kabupaten']) ? 'selected' : ''; ?>>
                            <?= esc($kab['nama_kabupaten']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Per Page Mobile -->
        <div class="w-full">
            <label class="block text-sm font-medium text-gray-700 mb-1">Data per Halaman</label>
            <select id="perPageSelectMobile" class="input-field w-full text-sm">
                <option value="5" <?= ($perPage == 5) ? 'selected' : ''; ?>>5</option>
                <option value="10" <?= ($perPage == 10) ? 'selected' : ''; ?>>10</option>
                <option value="25" <?= ($perPage == 25) ? 'selected' : ''; ?>>25</option>
                <option value="50" <?= ($perPage == 50) ? 'selected' : ''; ?>>50</option>
                <option value="100" <?= ($perPage == 100) ? 'selected' : ''; ?>>100</option>
            </select>
        </div>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div class="overflow-x-auto">
        <table class="w-full" id="kegiatanDetailTable">
            <thead>
                <tr class="border-b border-gray-200 bg-gray-50">
                    <th
                        class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase border border-gray-200 w-16">
                        No</th>
                    <th
                        class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase border border-gray-200">
                        Nama Kegiatan Detail Proses</th>
                    <th
                        class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase border border-gray-200">
                        Kab/Kota</th>
                    <th
                        class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase border border-gray-200 w-32">
                        Tanggal Mulai</th>
                    <th
                        class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase border border-gray-200 w-32">
                        Tanggal Selesai</th>
                    <th
                        class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase border border-gray-200 w-64">
                        Keterangan</th>
                    <th
                        class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase border border-gray-200 w-32">
                        Target Wilayah</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (!empty($kegiatanWilayah)): ?>
                    <?php foreach ($kegiatanWilayah as $index => $kg): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-4 border border-gray-200 text-sm text-center text-gray-900">
                                <?= ($pager->getCurrentPage('kegiatanWilayah') - 1) * $pager->getPerPage('kegiatanWilayah') + $index + 1 ?>
                            </td>
                            <td class="px-4 py-4 border border-gray-200 text-sm"><?= esc($kg['nama_kegiatan_detail_proses']) ?>
                            </td>
                            <td class="px-4 py-4 border border-gray-200 text-sm text-gray-600"><?= esc($kg['nama_kabupaten']) ?>
                            </td>
                            <td class="px-4 py-4 border border-gray-200 text-sm text-center text-gray-600">
                                <?= esc($kg['tanggal_mulai']) ?>
                            </td>
                            <td class="px-4 py-4 border border-gray-200 text-sm text-center text-gray-600">
                                <?= esc($kg['tanggal_selesai']) ?>
                            </td>
                            <td class="px-4 py-4 border border-gray-200 text-sm text-gray-600"><?= esc($kg['keterangan']) ?>
                            </td>
                            <td class="px-4 py-4 border border-gray-200 text-center text-sm font-medium">
                                <?= esc($kg['target_wilayah']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-gray-500">
                            Belum ada data kegiatan.
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
                class="font-medium"><?= (($pager->getCurrentPage('kegiatanWilayah') - 1) * $pager->getPerPage('kegiatanWilayah')) + 1 ?></span>-<span
                class="font-medium"><?= min($pager->getCurrentPage('kegiatanWilayah') * $pager->getPerPage('kegiatanWilayah'), $pager->getTotal('kegiatanWilayah')) ?></span>
            dari <span class="font-medium"><?= $pager->getTotal('kegiatanWilayah') ?></span> data
        </p>

        <!-- Custom Pagination -->
        <?php if ($pager->getPageCount('kegiatanWilayah') > 1): ?>
            <?= $pager->links('kegiatanWilayah', 'tailwind_pager') ?>
        <?php endif; ?>
    </div>
</div>

<script>
    // Handle animasi chevron untuk kabupaten filter (Desktop)
    const kabupatenFilter = document.getElementById('kabupatenFilter');
    const kabupatenChevron = document.getElementById('kabupatenChevron');

    if (kabupatenFilter && kabupatenChevron) {
        kabupatenFilter.addEventListener('focus', function () {
            kabupatenChevron.classList.add('rotate-180');
        });

        kabupatenFilter.addEventListener('blur', function () {
            kabupatenChevron.classList.remove('rotate-180');
        });

        kabupatenFilter.addEventListener('change', function () {
            updateFilters();
        });
    }

    // Handle kabupaten filter mobile
    const kabupatenFilterMobile = document.getElementById('kabupatenFilterMobile');
    if (kabupatenFilterMobile) {
        kabupatenFilterMobile.addEventListener('change', function () {
            updateFilters();
        });
    }

    // Handle animasi chevron untuk perPage selector (Desktop)
    const perPageSelect = document.getElementById('perPageSelect');
    const perPageChevron = document.getElementById('perPageChevron');

    if (perPageSelect && perPageChevron) {
        perPageSelect.addEventListener('focus', function () {
            perPageChevron.classList.add('rotate-180');
        });

        perPageSelect.addEventListener('blur', function () {
            perPageChevron.classList.remove('rotate-180');
        });

        perPageSelect.addEventListener('change', function () {
            updateFilters();
        });
    }

    // Handle perPage mobile
    const perPageSelectMobile = document.getElementById('perPageSelectMobile');
    if (perPageSelectMobile) {
        perPageSelectMobile.addEventListener('change', function () {
            updateFilters();
        });
    }

    // Function untuk update filters dengan mempertahankan parameter yang ada
    function updateFilters() {
        // Deteksi apakah desktop atau mobile
        const isDesktop = window.innerWidth >= 1024;

        let kabupaten, perPage;

        if (isDesktop) {
            kabupaten = document.getElementById('kabupatenFilter')?.value || '';
            perPage = document.getElementById('perPageSelect')?.value || '10';
        } else {
            kabupaten = document.getElementById('kabupatenFilterMobile')?.value || '';
            perPage = document.getElementById('perPageSelectMobile')?.value || '10';
        }

        // Build URL dengan parameter
        const params = new URLSearchParams();
        if (kabupaten) params.append('kabupaten', kabupaten);
        if (perPage) params.append('perPage', perPage);

        // Redirect ke URL dengan parameter baru
        window.location.href = '<?= base_url('pemantau-provinsi/kegiatan-wilayah-list') ?>?' + params.toString();
    }

    // Search function - support both desktop and mobile
    function searchTable() {
        // Ambil input dari desktop atau mobile
        const inputDesktop = document.getElementById('searchInput');
        const inputMobile = document.getElementById('searchInputMobile');

        let input = '';
        if (window.innerWidth >= 1024 && inputDesktop) {
            input = inputDesktop.value.toLowerCase();
        } else if (inputMobile) {
            input = inputMobile.value.toLowerCase();
        }

        const rows = document.querySelectorAll('#kegiatanDetailTable tbody tr');
        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(input) ? '' : 'none';
        });
    }
</script>

<?= $this->endSection() ?>