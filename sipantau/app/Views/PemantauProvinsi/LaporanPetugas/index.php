<?= $this->extend('layouts/pemantau_provinsi_layout') ?>

<?= $this->section('content') ?>

<!-- Back Button & Title -->
<div class="mb-6">
    <a href="<?= base_url('pemantau-provinsi') ?>"
        class="inline-flex items-center text-gray-600 hover:text-gray-900 mb-3">
        <i class="fas fa-arrow-left mr-2"></i>
        <span>Back</span>
    </a>
    <h1 class="text-3xl font-bold text-gray-900">Laporan Petugas</h1>
</div>

<!-- Main Card -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="p-6">
        <!-- Filter Section -->
        <div class="mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                <!-- Filter Kegiatan Proses -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Kegiatan Proses:</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-tasks text-gray-400"></i>
                        </div>
                        <select id="filterKegiatanProses"
                            class="input-field w-full pl-10 pr-10 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 appearance-none cursor-pointer">
                            <option value="">-- Pilih Kegiatan Proses --</option>
                            <?php foreach ($kegiatanProsesList as $proses): ?>
                                <option value="<?= $proses['id_kegiatan_detail_proses'] ?>"
                                    <?= ($selectedKegiatanProses == $proses['id_kegiatan_detail_proses']) ? 'selected' : '' ?>>
                                    <?= esc($proses['nama_kegiatan_detail_proses']) ?>
                                    (<?= esc($proses['nama_kegiatan_detail']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <i class="fas fa-chevron-down text-gray-400 text-sm"></i>
                        </div>
                    </div>
                </div>

                <!-- Filter Kabupaten -->
                <div class="md:col-span-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1 margin">Filter Kabupaten:</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-filter text-gray-400"></i>
                        </div>
                        <select id="kabupatenFilter"
                            class="input-field w-full pl-10 pr-10 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 appearance-none cursor-pointer">
                            <option value="">-- Semua Kabupaten --</option>
                            <?php foreach ($kabupatenList as $kab): ?>
                                <option value="<?= $kab['id_kabupaten'] ?>" <?= ($selectedKabupaten == $kab['id_kabupaten']) ? 'selected' : '' ?>>
                                    <?= esc($kab['nama_kabupaten']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <i class="fas fa-chevron-down text-gray-400 text-sm"></i>
                        </div>
                    </div>
                </div>

                <!-- Per Page -->
                <div class="md:col-span-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Data per Halaman:</label>
                    <select id="perPageSelect"
                        class="input-field w-full border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="5" <?= ($perPage == 5) ? 'selected' : '' ?>>5</option>
                        <option value="10" <?= ($perPage == 10) ? 'selected' : '' ?>>10</option>
                        <option value="25" <?= ($perPage == 25) ? 'selected' : '' ?>>25</option>
                        <option value="50" <?= ($perPage == 50) ? 'selected' : '' ?>>50</option>
                        <option value="100" <?= ($perPage == 100) ? 'selected' : '' ?>>100</option>
                    </select>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
                <!-- Search Box -->
                <div class="w-full sm:w-96">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input type="text" id="searchInput" placeholder="Cari nama petugas..."
                            value="<?= esc($search ?? '') ?>"
                            class="input-field w-full pl-10 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <!-- Button Tampilkan & Export -->
                <div class="flex gap-2">
                    <button onclick="updateFilters()"
                        class="bg-blue-600 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">
                        <i class="fas fa-sync-alt mr-2"></i>Tampilkan
                    </button>
                    <?php if ($selectedKegiatanProses): ?>
                        <button onclick="exportCSV()"
                            class="bg-green-600 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-green-700 transition-colors">
                            <i class="fas fa-file-csv mr-2"></i>Export CSV
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if (!$selectedKegiatanProses): ?>
            <div class="text-center py-12 text-gray-500">
                <i class="fas fa-info-circle text-5xl mb-4"></i>
                <p class="text-lg">Silakan pilih kegiatan proses terlebih dahulu untuk melihat laporan</p>
            </div>
        <?php elseif (empty($dataPetugas)): ?>
            <div class="text-center py-12 text-gray-500">
                <i class="fas fa-inbox text-5xl mb-4"></i>
                <p class="text-lg">Tidak ada data petugas untuk kegiatan ini</p>
            </div>
        <?php else: ?>
            <!-- Table Container dengan scroll horizontal -->
            <div class="overflow-x-auto border border-gray-200 rounded-lg">
                <table class="min-w-full divide-y divide-gray-200 table-sticky-columns" id="petugasTable">
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                class="col-no sticky left-0 bg-gray-50 px-4 py-3 text-left text-xs font-semibold text-gray-700 border-r border-gray-200">
                                No
                            </th>
                            <th
                                class="col-nama sticky bg-gray-50 px-4 py-3 text-left text-xs font-semibold text-gray-700 border-r border-gray-200">
                                Nama Petugas
                            </th>
                            <?php foreach ($dateHeaders as $date): ?>
                                <th
                                    class="px-4 py-3 text-center text-xs font-semibold text-gray-700 border-r border-gray-200 whitespace-nowrap bg-gray-50">
                                    <?= $date['display'] ?>
                                </th>
                            <?php endforeach; ?>
                            <th
                                class="col-total sticky right-0 bg-gray-50 px-4 py-3 text-center text-xs font-semibold text-gray-700 border-l border-gray-200">
                                TOTAL
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php
                        $no = (($currentPage - 1) * $perPage) + 1;
                        foreach ($dataPetugas as $petugas):
                            ?>
                            <tr class="hover:bg-gray-50">
                                <td
                                    class="col-no sticky left-0 bg-white px-4 py-3 text-center text-sm text-gray-900 border-r border-gray-200">
                                    <?= $no++ ?>
                                </td>
                                <td class="col-nama sticky bg-white px-4 py-3 text-sm border-r border-gray-200">
                                    <a href="<?= base_url('pemantau-provinsi/laporan-petugas/detail/' . $petugas['id_pcl']) ?>"
                                        <div
                                        class="font-medium text-blue-600 hover:text-blue-800 hover:underline"><?= esc($petugas['nama_user']) ?>
                </div>
                <div class="text-xs text-gray-500"><?= esc($petugas['nama_kabupaten']) ?></div>
                </a>
                </td>
                <?php foreach ($petugas['progress_data'] as $count): ?>
                    <td class="px-4 py-3 text-center text-sm text-gray-700 border-r border-gray-200 bg-white">
                        <?= $count ?>
                    </td>
                <?php endforeach; ?>
                <td
                    class="col-total sticky right-0 bg-white px-4 py-3 text-center font-semibold text-gray-900 border-l border-gray-200">
                    <?= $petugas['total_realisasi'] ?>
                    <?php if ($petugas['status_complete']): ?>
                        <i class="fas fa-check-circle text-green-500 ml-2"></i>
                    <?php endif; ?>
                </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="flex flex-col sm:flex-row items-center justify-between mt-6 gap-4">
            <p class="text-sm text-gray-600">
                Menampilkan data
                <span class="font-medium"><?= (($currentPage - 1) * $perPage) + 1 ?></span>-<span
                    class="font-medium"><?= min($currentPage * $perPage, $totalData) ?></span>
                dari <span class="font-medium"><?= $totalData ?></span> data
            </p>

            <?php if ($totalData > $perPage): ?>
                <div class="flex gap-1">
                    <?php
                    $totalPages = ceil($totalData / $perPage);
                    $currentPage = (int) ($currentPage ?? 1);

                    // Previous button
                    $prevDisabled = $currentPage <= 1 ? 'opacity-50 pointer-events-none' : '';
                    $prevParams = array_merge($_GET, ['page' => max(1, $currentPage - 1)]);
                    ?>

                    <a href="?<?= http_build_query($prevParams) ?>"
                        class="px-3 py-1 border border-gray-300 rounded text-sm hover:bg-gray-50 transition-colors <?= $prevDisabled ?>">
                        <i class="fas fa-chevron-left"></i> Previous
                    </a>

                    <?php
                    // Determine page range to show
                    $startPage = max(1, $currentPage - 2);
                    $endPage = min($totalPages, $currentPage + 2);

                    // Show first page if not in range
                    if ($startPage > 1): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>"
                            class="px-3 py-1 border border-gray-300 rounded text-sm hover:bg-gray-50 transition-colors">
                            1
                        </a>
                        <?php if ($startPage > 2): ?>
                            <span class="px-3 py-1 text-gray-400">...</span>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php
                    // Show page numbers
                    for ($i = $startPage; $i <= $endPage; $i++):
                        $isActive = $i == $currentPage;
                        $pageClass = $isActive
                            ? 'bg-blue-600 text-white'
                            : 'border border-gray-300 hover:bg-gray-50';
                        ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"
                            class="px-3 py-1 <?= $pageClass ?> rounded text-sm transition-colors">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>

                    <?php
                    // Show last page if not in range
                    if ($endPage < $totalPages): ?>
                        <?php if ($endPage < $totalPages - 1): ?>
                            <span class="px-3 py-1 text-gray-400">...</span>
                        <?php endif; ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $totalPages])) ?>"
                            class="px-3 py-1 border border-gray-300 rounded text-sm hover:bg-gray-50 transition-colors">
                            <?= $totalPages ?>
                        </a>
                    <?php endif; ?>

                    <?php
                    // Next button
                    $nextDisabled = $currentPage >= $totalPages ? 'opacity-50 pointer-events-none' : '';
                    $nextParams = array_merge($_GET, ['page' => min($totalPages, $currentPage + 1)]);
                    ?>

                    <a href="?<?= http_build_query($nextParams) ?>"
                        class="px-3 py-1 border border-gray-300 rounded text-sm hover:bg-gray-50 transition-colors <?= $nextDisabled ?>">
                        Next <i class="fas fa-chevron-right"></i>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
</div>

<script>
    function updateFilters() {
        const kegiatanProses = document.getElementById('filterKegiatanProses').value;
        const kabupaten = document.getElementById('kabupatenFilter').value;
        const perPage = document.getElementById('perPageSelect').value;
        const search = document.getElementById('searchInput').value;

        const params = new URLSearchParams();
        if (kegiatanProses) params.append('kegiatan_proses', kegiatanProses);
        if (kabupaten) params.append('kabupaten', kabupaten);
        if (perPage) params.append('perPage', perPage);
        if (search) params.append('search', search);

        window.location.href = '<?= base_url('pemantau-provinsi/laporan-petugas') ?>?' + params.toString();
    }

    function exportCSV() {
        const kegiatanProses = document.getElementById('filterKegiatanProses').value;
        const kabupaten = document.getElementById('kabupatenFilter').value;
        const search = document.getElementById('searchInput').value;

        const params = new URLSearchParams();
        if (kegiatanProses) params.append('kegiatan_proses', kegiatanProses);
        if (kabupaten) params.append('kabupaten', kabupaten);
        if (search) params.append('search', search);

        window.location.href = '<?= base_url('pemantau-provinsi/laporan-petugas/export-csv') ?>?' + params.toString();
    }

    // Handle search dengan debounce
    let searchTimeout;
    document.getElementById('searchInput').addEventListener('input', function () {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function () {
            updateFilters();
        }, 500);
    });

    // Handle Enter key pada search
    document.getElementById('searchInput').addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            clearTimeout(searchTimeout);
            updateFilters();
        }
    });

    // Auto update ketika filter berubah
    document.getElementById('filterKegiatanProses').addEventListener('change', updateFilters);
    document.getElementById('kabupatenFilter').addEventListener('change', updateFilters);
    document.getElementById('perPageSelect').addEventListener('change', updateFilters);
</script>

<style>
    /* Sticky column styling */
    .table-sticky-columns {
        position: relative;
    }

    /* Kolom No - sticky kiri */
    .col-no {
        position: sticky !important;
        left: 0;
        z-index: 10;
        min-width: 60px;
        width: 60px;
    }

    /* Kolom Nama - sticky kiri setelah No */
    .col-nama {
        position: sticky !important;
        left: 60px;
        z-index: 10;
        min-width: 220px;
    }

    /* Kolom Total - sticky kanan */
    .col-total {
        position: sticky !important;
        right: 0;
        z-index: 10;
        min-width: 100px;
    }

    /* Shadow untuk kolom sticky */
    .col-no,
    .col-nama {
        box-shadow: 2px 0 5px -2px rgba(0, 0, 0, 0.1);
    }

    .col-total {
        box-shadow: -2px 0 5px -2px rgba(0, 0, 0, 0.1);
    }

    /* Header sticky columns - z-index lebih tinggi */
    thead .col-no,
    thead .col-nama,
    thead .col-total {
        z-index: 20 !important;
    }

    /* Memastikan hover tetap bekerja dengan baik */
    tbody tr:hover td {
        background-color: rgb(249, 250, 251) !important;
    }

    tbody tr:hover td.col-no,
    tbody tr:hover td.col-nama,
    tbody tr:hover td.col-total {
        background-color: rgb(249, 250, 251) !important;
    }

    /* Smooth scrolling */
    .overflow-x-auto {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    /* Kolom tanggal yang di-scroll */
    tbody td:not(.col-no):not(.col-nama):not(.col-total),
    thead th:not(.col-no):not(.col-nama):not(.col-total) {
        min-width: 80px;
    }
</style>

<?= $this->endSection() ?>