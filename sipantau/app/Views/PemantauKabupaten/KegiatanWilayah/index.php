<?= $this->extend('layouts/pemantau_kabupaten_layout') ?>
<?= $this->section('content') ?>

<!-- Page Header -->
<div class="mb-6">
    <div class="flex items-center text-sm text-gray-600 mb-4">
        <a href="<?= base_url('pemantau-kabupaten') ?>" class="hover:text-blue-600 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Back
        </a>
    </div>
    <h1 class="text-2xl font-bold text-gray-900">Kegiatan Wilayah</h1>
    <p class="text-gray-600 mt-1">Data target survei untuk <?= esc($kabupaten['nama_kabupaten'] ?? 'Kabupaten') ?></p>
</div>

<!-- DESKTOP LAYOUT -->
<div class="card mb-6 hidden lg:block">
    <!-- Search dan Per Page Section -->
    <div style="display: grid; grid-template-columns: 1fr 200px; gap: 1rem;">
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
                    placeholder="Cari kegiatan detail atau keterangan..." onkeyup="searchTable()">
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
                    placeholder="Cari kegiatan detail atau keterangan..." onkeyup="searchTable()">
            </div>
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
    </div>
</div>

<!-- Table -->
<div class="card">
    <div class="overflow-x-auto">
        <table class="w-full" id="kegiatanWilayahTable">
            <thead>
                <tr class="border-b border-gray-200 bg-gray-50">
                    <th
                        class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase border border-gray-200 w-16">
                        No</th>
                    <th
                        class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase border border-gray-200">
                        Nama Kegiatan Detail Proses</th>
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
                        <td colspan="6" class="px-4 py-6 text-center text-gray-500">
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
        const perPage = document.getElementById('perPageSelect').value;

        // Build URL dengan parameter
        const params = new URLSearchParams();
        if (perPage) params.append('perPage', perPage);

        // Redirect ke URL dengan parameter baru
        window.location.href = '<?= base_url('pemantau-kabupaten/kegiatan-wilayah') ?>?' + params.toString();
    }

    // Search function
    function searchTable() {
        const input = document.getElementById('searchInput').value.toLowerCase();
        const rows = document.querySelectorAll('#kegiatanWilayahTable tbody tr');
        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(input) ? '' : 'none';
        });
    }
</script>

<?= $this->endSection() ?>