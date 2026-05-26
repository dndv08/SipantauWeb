<?= $this->extend('layouts/adminkab_layout') ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="mb-6">
    <div class="flex items-center mb-2">
        <a href="<?= base_url('adminsurvei-kab') ?>" class="text-gray-600 hover:text-gray-900 mr-2">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
    <h1 class="text-2xl font-bold text-gray-900">Assign Petugas Survey</h1>
    <p class="text-sm text-gray-600 mt-1">Kelola assignment PML dan PCL untuk <?= esc($admin['nama_kabupaten']) ?></p>
</div>

<!-- Alert Messages -->
<?php if (session()->getFlashdata('success')): ?>
    <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg flex items-center">
        <i class="fas fa-check-circle mr-3"></i>
        <span><?= session()->getFlashdata('success') ?></span>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg flex items-center">
        <i class="fas fa-exclamation-circle mr-3"></i>
        <span><?= session()->getFlashdata('error') ?></span>
    </div>
<?php endif; ?>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <!-- Total PML -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-1">Total PML</p>
                <h3 class="text-3xl font-bold text-gray-900"><?= count($dataPML) ?></h3>
            </div>
            <div class="w-14 h-14 bg-blue-50 rounded-lg flex items-center justify-center flex-shrink-0">
                <i class="fas fa-users text-2xl text-blue-600"></i>
            </div>
        </div>
    </div>

    <!-- Total PCL -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-1">Total PCL</p>
                <h3 class="text-3xl font-bold text-gray-900">
                    <?= array_sum(array_column($dataPML, 'jumlah_pcl')) ?>
                </h3>
            </div>
            <div class="w-14 h-14 bg-green-50 rounded-lg flex items-center justify-center flex-shrink-0">
                <i class="fas fa-user-friends text-2xl text-green-600"></i>
            </div>
        </div>
    </div>

    <!-- Total Target PML -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-1">Total Target PML</p>
                <h3 class="text-3xl font-bold text-gray-900">
                    <?= number_format(array_sum(array_column($dataPML, 'target'))) ?>
                </h3>
            </div>
            <div class="w-14 h-14 bg-purple-50 rounded-lg flex items-center justify-center flex-shrink-0">
                <i class="fas fa-bullseye text-2xl text-purple-600"></i>
            </div>
        </div>
    </div>

    <!-- Total Target PCL -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-1">Total Target PCL</p>
                <h3 class="text-3xl font-bold text-gray-900">
                    <?= number_format(array_sum(array_column($dataPML, 'total_target_pcl'))) ?>
                </h3>
            </div>
            <div class="w-14 h-14 bg-orange-50 rounded-lg flex items-center justify-center flex-shrink-0">
                <i class="fas fa-chart-line text-2xl text-orange-600"></i>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Card -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">

    <!-- Search, Filter, PerPage and Add Button -->
    <!-- DESKTOP LAYOUT -->
    <div class="hidden lg:flex flex-wrap gap-3 mb-6 items-end">
        <!-- Search Box -->
        <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-medium text-gray-700 mb-1">Pencarian</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                <input type="text" id="searchInput" placeholder="Cari nama survei atau PML..."
                    class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                    onkeyup="searchTable()">
            </div>
        </div>

        <!-- Filter Kegiatan -->
        <div class="flex-1 min-w-[250px]">
            <?= view('components/select_component', [
                'label' => 'Filter Kegiatan',
                'name' => 'filter_kegiatan',
                'id' => 'filterKegiatan',
                'placeholder' => 'Semua Kegiatan',
                'options' => $kegiatanList,
                'optionValue' => 'id_kegiatan_wilayah',
                'optionText' => function ($item) {
                                return $item['nama_kegiatan_detail'] . ' - ' . $item['nama_kegiatan_detail_proses'] . ' (' . date('Y', strtotime($item['tanggal_mulai'])) . ')';
                            },
                'value' => $selectedKegiatan,
                'enableSearch' => true,
                'allowClear' => true
            ]) ?>
        </div>

        <!-- Per Page Selector -->
        <div class="w-32">
            <label for="perPageSelect" class="block text-sm font-medium text-gray-700 mb-1">
                Per Halaman
            </label>
            <div class="relative">
                <select name="perPage" id="perPageSelect"
                    class="w-full pl-3 pr-8 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 appearance-none cursor-pointer text-sm"
                    onchange="updatePerPage()">
                    <option value="5" <?= ($perPage == 5) ? 'selected' : ''; ?>>5</option>
                    <option value="10" <?= ($perPage == 10) ? 'selected' : ''; ?>>10</option>
                    <option value="25" <?= ($perPage == 25) ? 'selected' : ''; ?>>25</option>
                    <option value="50" <?= ($perPage == 50) ? 'selected' : ''; ?>>50</option>
                    <option value="100" <?= ($perPage == 100) ? 'selected' : ''; ?>>100</option>
                </select>
                <div class="absolute inset-y-0 right-0 pr-2 flex items-center pointer-events-none">
                    <i id="perPageChevron"
                        class="fas fa-chevron-down text-gray-400 text-xs transition-transform duration-300"></i>
                </div>
            </div>
        </div>

        <!-- Add Button -->
        <div class="ml-auto">
            <label class="block text-sm font-medium text-gray-700 mb-1">&nbsp;</label>
            <a href="<?= base_url('adminsurvei-kab/assign-petugas/create') ?>"
                class="inline-flex items-center justify-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors whitespace-nowrap">
                <i class="fas fa-plus mr-2"></i>
                Tambah Petugas Survei
            </a>
        </div>

        <!-- Import Button -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">&nbsp;</label>
            <button onclick="openImportModal()"
                class="inline-flex items-center justify-center px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors whitespace-nowrap">
                <i class="fas fa-file-excel mr-2"></i>
                Import Excel
            </button>
        </div>

        <!-- Copy Configuration Button -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">&nbsp;</label>
            <button onclick="openCopyConfigModal()"
                class="inline-flex items-center justify-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors whitespace-nowrap">
                <i class="fas fa-copy mr-2"></i>
                Copy Konfigurasi
            </button>
        </div>
    </div>

    <!-- MOBILE LAYOUT -->
    <div class="flex flex-col gap-3 mb-6 lg:hidden">
        <!-- Search Box -->
        <div class="w-full">
            <label class="block text-sm font-medium text-gray-700 mb-1">Pencarian</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                <input type="text" id="searchInputMobile" placeholder="Cari nama survei atau PML..."
                    class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-sm"
                    onkeyup="searchTable()">
            </div>
        </div>

        <!-- Filter -->
        <div class="w-full overflow-hidden">
            <?= view('components/select_component', [
                'label' => 'Filter Kegiatan',
                'name' => 'filter_kegiatan_mobile',
                'id' => 'filterKegiatanMobile',
                'placeholder' => 'Semua Kegiatan',
                'options' => $kegiatanList,
                'optionValue' => 'id_kegiatan_wilayah',
                'optionText' => function ($item) {
                    return $item['nama_kegiatan_detail'] . ' (' . date('Y', strtotime($item['tanggal_mulai'])) . ')';
                },
                'value' => $selectedKegiatan,
                'enableSearch' => true,
                'allowClear' => true
            ]) ?>
        </div>

        <!-- Per Page -->
        <div class="w-full sm:w-32">
            <label class="block text-sm font-medium text-gray-700 mb-1">Per Halaman</label>
            <select id="perPageSelectMobile" class="w-full py-2.5 border border-gray-300 rounded-lg text-sm"
                onchange="updatePerPage()">
                <option value="5" <?= ($perPage == 5) ? 'selected' : ''; ?>>5</option>
                <option value="10" <?= ($perPage == 10) ? 'selected' : ''; ?>>10</option>
                <option value="25" <?= ($perPage == 25) ? 'selected' : ''; ?>>25</option>
                <option value="50" <?= ($perPage == 50) ? 'selected' : ''; ?>>50</option>
                <option value="100" <?= ($perPage == 100) ? 'selected' : ''; ?>>100</option>
            </select>
        </div>

        <!-- Tombol Action Mobile -->
        <div class="flex gap-2">
            <a href="<?= base_url('adminsurvei-kab/assign-petugas/create') ?>"
                class="flex-1 inline-flex items-center justify-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                <i class="fas fa-plus mr-2"></i> Tambah
            </a>
            <button onclick="openImportModal()"
                class="flex-1 inline-flex items-center justify-center px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
                <i class="fas fa-file-excel mr-2"></i> Import
            </button>
            <button onclick="openCopyConfigModal()"
                class="flex-1 inline-flex items-center justify-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                <i class="fas fa-copy mr-2"></i> Copy
            </button>
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full border-collapse" id="assignTable">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 border-r border-gray-200">No</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 border-r border-gray-200">Nama
                        Survei</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 border-r border-gray-200">Nama
                        PML</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 border-r border-gray-200">Target
                        PML</th>
                    <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700 border-r border-gray-200">
                        Jumlah PCL</th>
                    <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php if (empty($dataPML)): ?>
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-2"></i>
                            <p>Belum ada data assignment</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($dataPML as $index => $pml): ?>
                        <tr class="hover:bg-gray-50" data-kegiatan="<?= $pml['id_kegiatan_wilayah'] ?>">
                            <td class="px-4 py-3 text-sm text-gray-700 border-r border-gray-200">
                                <?= ($pager->getCurrentPage('pml_list') - 1) * $pager->getPerPage('pml_list') + $index + 1 ?>
                            </td>
                            <td class="px-4 py-3 border-r border-gray-200">
                                <div>
                                    <p class="text-sm font-medium text-gray-900"><?= esc($pml['nama_kegiatan_detail']) ?></p>
                                    <p class="text-xs text-gray-500"><?= esc($pml['nama_kegiatan_detail_proses']) ?></p>
                                </div>
                            </td>
                            <td class="px-4 py-3 border-r border-gray-200">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900"><?= esc($pml['nama_pml']) ?></p>
                                    <p class="text-xs text-gray-500"><?= esc($pml['email']) ?></p>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700 border-r border-gray-200">
                                <span class="font-semibold"><?= number_format($pml['target']) ?></span>
                            </td>
                            <td class="px-4 py-3 text-center border-r border-gray-200">
                                <div class="flex items-center justify-center gap-1.5">
                                    <span
                                        class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-800 text-sm font-medium rounded-full">
                                        <i class="fas fa-users mr-1"></i>
                                        <?= $pml['jumlah_pcl'] ?> PCL
                                    </span>
                                    <?php if (!empty($pml['has_transaction_data'])): ?>
                                        <span class="inline-flex items-center px-2 py-1 bg-amber-100 text-amber-800 text-xs font-semibold rounded-full border border-amber-300" 
                                              title="PML ini memiliki <?= $pml['tx_total_progress'] ?> data progress dan <?= $pml['tx_total_transaksi'] ?> data transaksi">
                                            <i class="fas fa-lock mr-1 text-[10px]"></i>
                                            Data Aktif
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <a href="<?= base_url('adminsurvei-kab/assign-petugas/detail/' . $pml['id_pml']) ?>"
                                        class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded transition-colors"
                                        title="Detail">
                                        Detail
                                    </a>
                                    <?php if (!empty($pml['has_transaction_data'])): ?>
                                        <!-- Edit & Delete DISABLED karena ada data transaksi -->
                                        <button type="button"
                                            onclick="showLockedInfo(<?= $pml['id_pml'] ?>, '<?= esc($pml['nama_pml'], 'js') ?>', <?= $pml['tx_total_progress'] ?>, <?= $pml['tx_total_transaksi'] ?>, <?= $pml['tx_total_realisasi'] ?>)"
                                            class="px-3 py-1 bg-gray-400 text-white text-xs font-medium rounded cursor-not-allowed opacity-70 inline-flex items-center gap-1"
                                            title="Tidak dapat diedit — sudah ada data transaksi">
                                            <i class="fas fa-lock text-[10px]"></i> Edit
                                        </button>
                                        <button type="button"
                                            onclick="showLockedInfo(<?= $pml['id_pml'] ?>, '<?= esc($pml['nama_pml'], 'js') ?>', <?= $pml['tx_total_progress'] ?>, <?= $pml['tx_total_transaksi'] ?>, <?= $pml['tx_total_realisasi'] ?>)"
                                            class="px-3 py-1 bg-gray-400 text-white text-xs font-medium rounded cursor-not-allowed opacity-70 inline-flex items-center gap-1"
                                            title="Tidak dapat dihapus — sudah ada data transaksi">
                                            <i class="fas fa-lock text-[10px]"></i> Delete
                                        </button>
                                    <?php else: ?>
                                        <!-- Edit & Delete normal -->
                                        <a href="<?= base_url('adminsurvei-kab/assign-petugas/edit/' . $pml['id_pml']) ?>"
                                            class="px-3 py-1 bg-yellow-600 hover:bg-yellow-700 text-white text-xs font-medium rounded transition-colors"
                                            title="Edit Assignment">
                                            Edit
                                        </a>
                                        <button
                                            onclick="confirmDelete(<?= $pml['id_pml'] ?>, '<?= esc($pml['nama_pml'], 'js') ?>', <?= $pml['jumlah_pcl'] ?>)"
                                            class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white text-xs font-medium rounded transition-colors"
                                            title="Hapus Assignment">
                                            Delete
                                        </button>
                                    <?php endif; ?>
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
                class="font-medium"><?= (($pager->getCurrentPage('pml_list') - 1) * $pager->getPerPage('pml_list')) + 1 ?></span>-<span
                class="font-medium"><?= min($pager->getCurrentPage('pml_list') * $pager->getPerPage('pml_list'), $pager->getTotal('pml_list')) ?></span>
            dari <span class="font-medium"><?= $pager->getTotal('pml_list') ?></span> data
            <?php if ($selectedKegiatan): ?>
                <span class="text-blue-600">(terfilter)</span>
            <?php endif; ?>
        </p>

        <!-- Custom Pagination -->
        <?php if ($pager->getPageCount('pml_list') > 1): ?>
            <?= $pager->links('pml_list', 'tailwind_pager') ?>
        <?php endif; ?>
    </div>

    <!-- No Results Message (Hidden by default) -->
    <div id="noResults" class="hidden text-center py-8">
        <i class="fas fa-search text-gray-400 text-4xl mb-3"></i>
        <p class="text-gray-500">Tidak ada data yang ditemukan</p>
    </div>
</div>

<!-- Modal Import Excel -->
<div id="modalImport" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full">
        <div
            class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center rounded-t-lg">
            <h3 class="text-lg font-semibold text-gray-900">Import Data Petugas dari Excel</h3>
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
                        <strong>Langkah 1:</strong> Pilih Kegiatan untuk mengunduh template
                    </p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Pilih Kegiatan Survei <span class="text-red-500">*</span>
                    </label>
                    <select id="importKegiatanWilayah"
                        class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">-- Pilih Kegiatan --</option>
                        <?php foreach ($kegiatanList as $kg): ?>
                            <option value="<?= $kg['id_kegiatan_wilayah'] ?>">
                                <?= esc($kg['nama_kegiatan_detail']) ?> - <?= esc($kg['nama_kegiatan_detail_proses']) ?>
                                (<?= date('Y', strtotime($kg['tanggal_mulai'])) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button onclick="downloadTemplate()" id="btnDownloadTemplate" disabled
                    class="w-full px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
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
                    <input type="hidden" id="importKegiatanWilayahId" name="id_kegiatan_wilayah">

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            File Excel <span class="text-red-500">*</span>
                        </label>
                        <input type="file" id="fileImport" name="file" accept=".xlsx,.xls"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            required>
                        <p class="mt-1 text-xs text-gray-500">
                            Format file: .xlsx atau .xls (Maksimal 5MB)
                        </p>
                    </div>

                    <div class="flex gap-3">
                        <button type="button" onclick="closeImportModal()"
                            class="flex-1 px-4 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-lg transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                            class="flex-1 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                            <i class="fas fa-upload mr-2"></i>
                            Upload & Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Copy Configuration -->
<div id="modalCopyConfig" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-3xl w-full max-h-[90vh] overflow-hidden flex flex-col">
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900">Copy Konfigurasi Petugas</h3>
            <button onclick="closeCopyConfigModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <div class="p-6 overflow-y-auto flex-1">
            <!-- Step 1: Pilih Kegiatan Source dan Target -->
            <div id="stepSelectKegiatan">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <p class="text-sm text-blue-700">
                        <i class="fas fa-info-circle mr-2"></i>
                        Pilih kegiatan yang ingin dicopy konfigurasinya, kemudian pilih kegiatan tujuan
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <!-- Kegiatan Sumber -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Kegiatan Sumber <span class="text-red-500">*</span>
                        </label>
                        <select id="copyKegiatanSource"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">-- Pilih Kegiatan Sumber --</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Kegiatan yang sudah ada assignment</p>
                    </div>

                    <!-- Kegiatan Target -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Kegiatan Tujuan <span class="text-red-500">*</span>
                        </label>
                        <select id="copyKegiatanTarget"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">-- Pilih Kegiatan Tujuan --</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Kegiatan yang akan diterapkan konfigurasi</p>
                    </div>
                </div>

                <button onclick="previewConfiguration()" id="btnPreviewConfig" disabled
                    class="w-full px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-eye mr-2"></i>
                    Preview Konfigurasi
                </button>
            </div>

            <!-- Step 2: Preview Configuration -->
            <div id="stepPreview" class="hidden">
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                    <p class="text-sm text-yellow-700">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Periksa konfigurasi yang akan dicopy di bawah ini
                    </p>
                </div>

                <div id="previewContent" class="space-y-4 mb-6">
                    <!-- Content akan diisi via JavaScript -->
                </div>

                <div class="flex gap-3">
                    <button onclick="backToSelectKegiatan()"
                        class="flex-1 px-4 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-lg transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Kembali
                    </button>
                    <button onclick="executeCopy()"
                        class="flex-1 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors">
                        <i class="fas fa-copy mr-2"></i>
                        Copy Konfigurasi
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* Prevent icon animation/flicker on stats cards */
    .fa-users,
    .fa-user-friends,
    .fa-bullseye,
    .fa-chart-line {
        -webkit-backface-visibility: hidden;
        backface-visibility: hidden;
        -webkit-transform: translateZ(0);
        transform: translateZ(0);
    }

    /* Smooth transition for select2 without page jump */
    .select2-container {
        transition: none !important;
    }

    .select2-container--default .select2-selection--single {
        height: 2.80rem !important;
        /* sama dengan h-11 */
    }
</style>

<script>
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
        const perPage = document.getElementById('perPageSelect').value;
        const params = new URLSearchParams(window.location.search);

        // Set perPage baru
        params.set('perPage', perPage);

        // Redirect dengan parameter yang sudah ada
        window.location.href = '<?= base_url('adminsurvei-kab/assign-petugas') ?>?' + params.toString();
    }

    // Prevent page jump/glitch when changing filter
    let isChangingFilter = false;

    // Handle filter kegiatan change
    document.addEventListener('DOMContentLoaded', function () {
        const filterKegiatan = document.getElementById('filterKegiatan');
        const filterKegiatanMobile = document.getElementById('filterKegiatanMobile');

        if (filterKegiatan) {
            // Listen to Select2 change event
            $(filterKegiatan).on('change', function () {
                if (isChangingFilter) return;
                isChangingFilter = true;

                const selectedValue = this.value;
                const params = new URLSearchParams(window.location.search);

                // Update parameter kegiatan    
                if (selectedValue) {
                    params.set('kegiatan', selectedValue);
                } else {
                    params.delete('kegiatan');
                }

                // Redirect dengan parameter yang sudah ada termasuk perPage
                window.location.href = '<?= base_url('adminsurvei-kab/assign-petugas') ?>?' + params.toString();
            });
        }

        // Handle filter kegiatan mobile change
        if (filterKegiatanMobile) {
            $(filterKegiatanMobile).on('change', function () {
                if (isChangingFilter) return;
                isChangingFilter = true;

                const selectedValue = this.value;
                const params = new URLSearchParams(window.location.search);

                if (selectedValue) {
                    params.set('kegiatan', selectedValue);
                } else {
                    params.delete('kegiatan');
                }

                window.location.href = '<?= base_url('adminsurvei-kab/assign-petugas') ?>?' + params.toString();
            });
        }
    });

    // Search Function
    function searchTable() {
        const input = document.getElementById('searchInput');
        const filter = input.value.toLowerCase();
        const table = document.getElementById('assignTable');
        const tbody = table.getElementsByTagName('tbody')[0];
        const rows = tbody.getElementsByTagName('tr');
        const noResults = document.getElementById('noResults');

        let visibleRows = 0;

        // Loop through all table rows
        for (let i = 0; i < rows.length; i++) {
            // Skip if it's the "no data" row
            if (rows[i].cells.length === 1) continue;

            const surveiCell = rows[i].getElementsByTagName('td')[1]; // Nama Survei column
            const pmlCell = rows[i].getElementsByTagName('td')[2]; // Nama PML column

            if (surveiCell && pmlCell) {
                const surveiText = surveiCell.textContent || surveiCell.innerText;
                const pmlText = pmlCell.textContent || pmlCell.innerText;

                // Check if search term exists in Survei or PML name
                if (surveiText.toLowerCase().indexOf(filter) > -1 ||
                    pmlText.toLowerCase().indexOf(filter) > -1) {
                    rows[i].style.display = '';
                    visibleRows++;
                } else {
                    rows[i].style.display = 'none';
                }
            }
        }

        // Show/hide no results message
        if (visibleRows === 0 && filter !== '') {
            table.style.display = 'none';
            noResults.classList.remove('hidden');
        } else {
            table.style.display = 'table';
            noResults.classList.add('hidden');
        }
    }

    // Delete Confirmation with SweetAlert2
    function confirmDelete(idPML, namaPML, jumlahPCL) {
        Swal.fire({
            title: 'Konfirmasi Hapus',
            html: `
            <div class="text-left">
                <p class="text-gray-700 mb-3">
                    Apakah Anda yakin ingin menghapus assignment PML 
                    <strong class="text-gray-900">${namaPML}</strong>?
                </p>
                <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                    <p class="text-sm text-red-800">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <strong>Perhatian:</strong>
                    </p>
                    <ul class="text-sm text-red-700 mt-2 ml-6 list-disc space-y-1">
                        <li>Semua <strong>${jumlahPCL} PCL</strong> yang terkait akan dihapus</li>
                        <li>Data progress dan laporan akan hilang</li>
                        <li>Tindakan ini tidak dapat dibatalkan</li>
                    </ul>
                </div>
            </div>
        `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="fas fa-trash mr-2"></i>Ya, Hapus',
            cancelButtonText: '<i class="fas fa-times mr-2"></i>Batal',
            customClass: {
                popup: 'rounded-lg',
                confirmButton: 'px-4 py-2 rounded-lg',
                cancelButton: 'px-4 py-2 rounded-lg'
            },
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Menghapus...',
                    text: 'Mohon tunggu sebentar',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Create form and submit
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '<?= base_url('adminsurvei-kab/assign-petugas/delete/') ?>' + idPML;

                // Add CSRF token
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '<?= csrf_token() ?>';
                csrfInput.value = '<?= csrf_hash() ?>';
                form.appendChild(csrfInput);

                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    // Tampilkan info detail mengapa assignment terkunci (ada data transaksi)
    function showLockedInfo(idPML, namaPML, totalProgress, totalTransaksi, totalRealisasi) {
        Swal.fire({
            title: '<i class="fas fa-lock text-amber-500 mr-2"></i> Assignment Terkunci',
            html: `
            <div class="text-left">
                <p class="text-gray-700 mb-3">
                    Assignment PML <strong class="text-gray-900">${namaPML}</strong> 
                    <span class="text-red-600 font-semibold">tidak dapat diedit atau dihapus</span> 
                    karena sudah memiliki data transaksi aktif.
                </p>
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-3">
                    <p class="text-sm font-semibold text-amber-900 mb-2">
                        <i class="fas fa-chart-bar mr-1"></i> Ringkasan Data Transaksi:
                    </p>
                    <div class="grid grid-cols-3 gap-3">
                        <div class="bg-white rounded-lg p-2 text-center border border-amber-100">
                            <p class="text-2xl font-bold text-blue-600">${totalProgress}</p>
                            <p class="text-xs text-gray-500">Data Progress</p>
                        </div>
                        <div class="bg-white rounded-lg p-2 text-center border border-amber-100">
                            <p class="text-2xl font-bold text-purple-600">${totalTransaksi}</p>
                            <p class="text-xs text-gray-500">Data Transaksi</p>
                        </div>
                        <div class="bg-white rounded-lg p-2 text-center border border-amber-100">
                            <p class="text-2xl font-bold text-green-600">${totalRealisasi}</p>
                            <p class="text-xs text-gray-500">Total Realisasi</p>
                        </div>
                    </div>
                </div>
                <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                    <p class="text-xs text-red-800">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        <strong>Opsi Admin:</strong> Jika Anda benar-benar ingin menghapus assignment ini beserta SELURUH data aktivitasnya, klik tombol di bawah.
                    </p>
                    <button onclick="forceDeletePML(${idPML})" class="mt-2 w-full px-3 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded transition-colors">
                        FORCE DELETE SEMUA DATA
                    </button>
                </div>
            </div>
        `,
            icon: 'info',
            showConfirmButton: false,
            showCancelButton: true,
            cancelButtonText: 'Tutup',
            customClass: {
                popup: 'rounded-lg',
                cancelButton: 'px-4 py-2 rounded-lg'
            },
            width: '500px'
        });
    }

    function forceDeletePML(idPML) {
        Swal.fire({
            title: 'KONFIRMASI FORCE DELETE',
            text: 'PERINGATAN: Tindakan ini akan menghapus PML beserta SELURUH PCL di bawahnya, dan SEMUA DATA progress serta transaksi yang sudah mereka input. Data tidak dapat dikembalikan!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Hapus Segalanya!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Buat form dinamis untuk method POST
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '<?= base_url('adminsurvei-kab/assign-petugas/delete/') ?>' + idPML + '?force=true';

                // Tambahkan CSRF Token
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '<?= csrf_token() ?>';
                csrfInput.value = '<?= csrf_hash() ?>';
                form.appendChild(csrfInput);

                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    // Auto-hide flash messages after 5 seconds
    document.addEventListener('DOMContentLoaded', function () {
        const alerts = document.querySelectorAll('.bg-green-50, .bg-red-50');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.transition = 'opacity 0.5s ease-out';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            }, 5000);
        });
    });

    // ==================== IMPORT MODAL FUNCTIONS ====================
    function openImportModal() {
        document.getElementById('modalImport').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeImportModal() {
        document.getElementById('modalImport').classList.add('hidden');
        document.body.style.overflow = 'auto';
        document.getElementById('formImport').reset();
        document.getElementById('importKegiatanWilayah').value = '';
        document.getElementById('btnDownloadTemplate').disabled = true;
        document.getElementById('importKegiatanWilayahId').value = '';
    }

    function downloadTemplate() {
        const kegiatanWilayahId = document.getElementById('importKegiatanWilayah').value;

        if (!kegiatanWilayahId) {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian!',
                text: 'Pilih Kegiatan terlebih dahulu',
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

        window.location.href = `<?= base_url('adminsurvei-kab/assign-petugas/download-template/') ?>${kegiatanWilayahId}`;

        setTimeout(() => {
            Swal.close();
        }, 2000);
    }

    // Event listener untuk import kegiatan change
    document.getElementById('importKegiatanWilayah').addEventListener('change', function () {
        const btnDownload = document.getElementById('btnDownloadTemplate');
        const kegiatanWilayahId = this.value;

        if (kegiatanWilayahId) {
            btnDownload.disabled = false;
            document.getElementById('importKegiatanWilayahId').value = kegiatanWilayahId;
        } else {
            btnDownload.disabled = true;
            document.getElementById('importKegiatanWilayahId').value = '';
        }
    });

    // Form Import Submit
    document.getElementById('formImport').addEventListener('submit', function (e) {
        e.preventDefault();

        const fileInput = document.getElementById('fileImport');
        const kegiatanWilayahId = document.getElementById('importKegiatanWilayahId').value;

        if (!fileInput.files.length) {
            Swal.fire({
                icon: 'warning',
                title: 'File Belum Dipilih',
                text: 'Pilih file Excel terlebih dahulu',
                confirmButtonColor: '#f59e0b'
            });
            return;
        }

        if (!kegiatanWilayahId) {
            Swal.fire({
                icon: 'warning',
                title: 'Kegiatan Belum Dipilih',
                text: 'Pilih Kegiatan terlebih dahulu',
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

                fetch('<?= base_url('adminsurvei-kab/assign-petugas/import') ?>', {
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

    // ==================== COPY CONFIGURATION FUNCTIONS ====================
    let kegiatanSourceList = [];
    let kegiatanTargetList = [];

    function openCopyConfigModal() {
        document.getElementById('modalCopyConfig').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        loadKegiatanForCopy();
    }

    function closeCopyConfigModal() {
        document.getElementById('modalCopyConfig').classList.add('hidden');
        document.body.style.overflow = 'auto';
        resetCopyConfigModal();
    }

    function resetCopyConfigModal() {
        document.getElementById('copyKegiatanSource').value = '';
        document.getElementById('copyKegiatanTarget').value = '';
        document.getElementById('btnPreviewConfig').disabled = true;
        document.getElementById('stepSelectKegiatan').classList.remove('hidden');
        document.getElementById('stepPreview').classList.add('hidden');
        document.getElementById('previewContent').innerHTML = '';
    }

    function loadKegiatanForCopy() {
        fetch('<?= base_url('adminsurvei-kab/assign-petugas/get-kegiatan-for-copy') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
            })
        })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    kegiatanSourceList = result.kegiatan_source;
                    kegiatanTargetList = result.kegiatan_target;

                    // Populate source dropdown
                    const sourceSelect = document.getElementById('copyKegiatanSource');
                    sourceSelect.innerHTML = '<option value="">-- Pilih Kegiatan Sumber --</option>';
                    result.kegiatan_source.forEach(kg => {
                        const option = document.createElement('option');
                        option.value = kg.id_kegiatan_wilayah;
                        option.textContent = `${kg.nama_kegiatan_detail} - ${kg.nama_kegiatan_detail_proses} (${kg.jumlah_pml} PML)`;
                        sourceSelect.appendChild(option);
                    });

                    // Populate target dropdown
                    const targetSelect = document.getElementById('copyKegiatanTarget');
                    targetSelect.innerHTML = '<option value="">-- Pilih Kegiatan Tujuan --</option>';
                    result.kegiatan_target.forEach(kg => {
                        const option = document.createElement('option');
                        option.value = kg.id_kegiatan_wilayah;
                        option.textContent = `${kg.nama_kegiatan_detail} - ${kg.nama_kegiatan_detail_proses} (${new Date(kg.tanggal_mulai).getFullYear()})`;
                        targetSelect.appendChild(option);
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Terjadi kesalahan saat memuat data kegiatan',
                    confirmButtonColor: '#ef4444'
                });
            });
    }

    // Enable preview button when both selects have values
    document.addEventListener('DOMContentLoaded', function () {
        const sourceSelect = document.getElementById('copyKegiatanSource');
        const targetSelect = document.getElementById('copyKegiatanTarget');
        const btnPreview = document.getElementById('btnPreviewConfig');

        function checkSelections() {
            if (sourceSelect && targetSelect && btnPreview) {
                const sourceValue = sourceSelect.value;
                const targetValue = targetSelect.value;
                btnPreview.disabled = !sourceValue || !targetValue || sourceValue === targetValue;
            }
        }

        if (sourceSelect) sourceSelect.addEventListener('change', checkSelections);
        if (targetSelect) targetSelect.addEventListener('change', checkSelections);
    });

    function previewConfiguration() {
        const sourceId = document.getElementById('copyKegiatanSource').value;
        const targetId = document.getElementById('copyKegiatanTarget').value;

        if (!sourceId || !targetId) {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian!',
                text: 'Pilih kedua kegiatan terlebih dahulu',
                confirmButtonColor: '#f59e0b'
            });
            return;
        }

        if (sourceId === targetId) {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian!',
                text: 'Kegiatan sumber dan tujuan tidak boleh sama',
                confirmButtonColor: '#f59e0b'
            });
            return;
        }

        Swal.fire({
            title: 'Memuat Preview...',
            html: 'Mohon tunggu sebentar',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch('<?= base_url('adminsurvei-kab/assign-petugas/preview-copy-configuration') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>',
                id_kegiatan_source: sourceId
            })
        })
            .then(response => response.json())
            .then(result => {
                Swal.close();

                if (result.success) {
                    if (!result.preview || result.preview.length === 0) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Tidak Ada Data',
                            text: 'Kegiatan sumber tidak memiliki konfigurasi petugas',
                            confirmButtonColor: '#f59e0b'
                        });
                        return;
                    }

                    displayPreview(result.preview);
                    document.getElementById('stepSelectKegiatan').classList.add('hidden');
                    document.getElementById('stepPreview').classList.remove('hidden');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: result.error || 'Tidak dapat memuat preview',
                        confirmButtonColor: '#ef4444'
                    });
                }
            })
            .catch(error => {
                Swal.close();
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Terjadi kesalahan pada sistem',
                    confirmButtonColor: '#ef4444'
                });
            });
    }

    function displayPreview(preview) {
        const container = document.getElementById('previewContent');
        let html = '';

        let totalPML = 0;
        let totalPCL = 0;
        let totalTarget = 0;

        preview.forEach((item, index) => {
            totalPML++;
            totalPCL += item.pcl.length;
            totalTarget += parseInt(item.pml.target);

            html += `
            <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex-1">
                        <h4 class="font-semibold text-gray-900 flex items-center">
                            <span class="inline-flex items-center justify-center w-6 h-6 bg-blue-600 text-white text-xs rounded-full mr-2">
                                ${index + 1}
                            </span>
                            PML: ${item.pml.nama_pml}
                        </h4>
                        <p class="text-sm text-gray-600 mt-1">Target: <strong>${parseInt(item.pml.target).toLocaleString()}</strong></p>
                    </div>
                </div>
                
                ${item.pcl.length > 0 ? `
                    <div class="mt-3 pl-8">
                        <p class="text-xs font-medium text-gray-700 mb-2">PCL (${item.pcl.length}):</p>
                        <div class="space-y-2">
                            ${item.pcl.map(pcl => `
                                <div class="flex items-center justify-between bg-white px-3 py-2 rounded border border-gray-200">
                                    <span class="text-sm text-gray-700">${pcl.nama_pcl}</span>
                                    <span class="text-sm font-medium text-gray-900">${parseInt(pcl.target).toLocaleString()}</span>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                ` : ''}
            </div>
        `;
        });

        // Summary
        html = `
            <div class="bg-gradient-to-r from-indigo-50 to-indigo-100 border border-indigo-200 rounded-lg p-4 mb-4">
                <h4 class="font-semibold text-indigo-900 mb-2">Ringkasan Konfigurasi</h4>
                <div class="grid grid-cols-3 gap-4 text-center">
                    <div>
                        <p class="text-2xl font-bold text-indigo-700">${totalPML}</p>
                        <p class="text-xs text-indigo-600">PML</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-indigo-700">${totalPCL}</p>
                        <p class="text-xs text-indigo-600">PCL</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-indigo-700">${totalTarget.toLocaleString()}</p>
                        <p class="text-xs text-indigo-600">Total Target</p>
                    </div>
                </div>
            </div>
        ` + html;

        container.innerHTML = html;
    }

    function backToSelectKegiatan() {
        document.getElementById('stepPreview').classList.add('hidden');
        document.getElementById('stepSelectKegiatan').classList.remove('hidden');
    }

    function executeCopy() {
        const sourceId = document.getElementById('copyKegiatanSource').value;
        const targetId = document.getElementById('copyKegiatanTarget').value;

        if (!sourceId || !targetId) {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian!',
                text: 'Data kegiatan tidak lengkap',
                confirmButtonColor: '#f59e0b'
            });
            return;
        }

        Swal.fire({
            title: 'Konfirmasi Copy',
            html: `
            <p class="text-gray-700 mb-3">
                Apakah Anda yakin ingin menyalin konfigurasi petugas?
            </p>
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 text-left">
                <p class="text-sm text-yellow-800">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <strong>Perhatian:</strong>
                </p>
                <ul class="text-sm text-yellow-700 mt-2 ml-6 list-disc space-y-1">
                    <li>Konfigurasi akan dicopy ke kegiatan tujuan</li>
                    <li>PML/PCL yang sudah ada tidak akan diganti</li>
                    <li>Pastikan target kegiatan tujuan mencukupi</li>
                </ul>
            </div>
        `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#4f46e5',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="fas fa-copy mr-2"></i>Ya, Copy',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Menyalin Konfigurasi...',
                    html: 'Mohon tunggu, proses sedang berjalan',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                fetch('<?= base_url('adminsurvei-kab/assign-petugas/execute-copy-configuration') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        '<?= csrf_token() ?>': '<?= csrf_hash() ?>',
                        id_kegiatan_source: sourceId,
                        id_kegiatan_target: targetId
                    })
                })
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                html: result.message,
                                confirmButtonColor: '#4f46e5'
                            }).then(() => {
                                closeCopyConfigModal();
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                html: result.message || 'Terjadi kesalahan saat menyalin konfigurasi',
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
</script>

<?= $this->endSection() ?>