<?= $this->extend('layouts/adminkab_layout') ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Approval Laporan</h1>
    <p class="text-gray-600 mt-1">Setujui laporan PML yang sudah memenuhi syarat</p>
</div>



<!-- DESKTOP LAYOUT -->
<div class="card mb-6 hidden lg:block">
    <!-- Search and PerPage Section -->
    <div style="display: grid; grid-template-columns: 1fr 200px; gap: 1rem; margin-bottom: 1.5rem;">
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
                    placeholder="Cari nama PML, kegiatan..." onkeyup="searchTable()">
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
    </div>
    <form method="GET" action="<?= base_url('adminsurvei-kab/approval-laporan') ?>" id="filterForm">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Filter Kegiatan -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Kegiatan Wilayah</label>
                <select name="kegiatan_wilayah" id="filterKegiatan" class="input-field" onchange="updateFilters()">
                    <option value="">Semua Kegiatan</option>
                    <?php foreach ($kegiatanWilayahList as $kw): ?>
                        <option value="<?= $kw['id_kegiatan_wilayah'] ?>" <?= ($filterKegiatan == $kw['id_kegiatan_wilayah']) ? 'selected' : '' ?>>
                            <?= esc($kw['nama_kegiatan_detail_proses']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filter Status -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select name="status" id="filterStatus" class="input-field" onchange="updateFilters()">
                    <option value="">Semua Status</option>
                    <option value="eligible" <?= ($filterStatus == 'eligible') ? 'selected' : '' ?>>
                        Siap Disetujui
                    </option>
                    <option value="approved" <?= ($filterStatus == 'approved') ? 'selected' : '' ?>>
                        Sudah Disetujui
                    </option>
                    <option value="not_eligible" <?= ($filterStatus == 'not_eligible') ? 'selected' : '' ?>>
                        Belum Memenuhi Syarat
                    </option>
                </select>
            </div>

            <!-- Reset Filter -->
            <div class="flex items-end">
                <?php if (!empty($filterKegiatan) || !empty($filterStatus)): ?>
                    <a href="<?= base_url('adminsurvei-kab/approval-laporan') ?>" class="btn-secondary w-full text-center">
                        <i class="fas fa-times-circle mr-2"></i>
                        Reset Filter
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </form>
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
                    placeholder="Cari nama PML, kegiatan..." onkeyup="searchTable()">
            </div>
        </div>

        <!-- Filter Kegiatan Mobile -->
        <div class="w-full">
            <label class="block text-sm font-medium text-gray-700 mb-1">Kegiatan Wilayah</label>
            <select name="kegiatan_wilayah_mobile" id="filterKegiatanMobile" class="input-field w-full text-sm"
                onchange="updateFilters()">
                <option value="">Semua Kegiatan</option>
                <?php foreach ($kegiatanWilayahList as $kw): ?>
                    <option value="<?= $kw['id_kegiatan_wilayah'] ?>" <?= ($filterKegiatan == $kw['id_kegiatan_wilayah']) ? 'selected' : '' ?>>
                        <?= esc($kw['nama_kegiatan_detail_proses']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Filter Status Mobile -->
        <div class="w-full">
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select name="status_mobile" id="filterStatusMobile" class="input-field w-full text-sm"
                onchange="updateFilters()">
                <option value="">Semua Status</option>
                <option value="eligible" <?= ($filterStatus == 'eligible') ? 'selected' : '' ?>>
                    Siap Disetujui
                </option>
                <option value="approved" <?= ($filterStatus == 'approved') ? 'selected' : '' ?>>
                    Sudah Disetujui
                </option>
                <option value="not_eligible" <?= ($filterStatus == 'not_eligible') ? 'selected' : '' ?>>
                    Belum Memenuhi Syarat
                </option>
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

        <!-- Reset Filter Mobile -->
        <?php if (!empty($filterKegiatan) || !empty($filterStatus)): ?>
            <div class="w-full">
                <a href="<?= base_url('adminsurvei-kab/approval-laporan') ?>"
                    class="btn-secondary w-full text-center inline-block">
                    <i class="fas fa-times-circle mr-2"></i>
                    Reset Filter
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- PML List -->
<div class="card">
    <?php if (empty($pmlData)): ?>
        <div class="text-center py-12">
            <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
            <p class="text-gray-500 text-lg">Belum ada data PML</p>
        </div>
    <?php else: ?>
        <!-- DESKTOP TABLE VIEW -->
        <div class="hidden lg:block overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th
                            class="px-4 py-3 border-r border-gray-200 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            No
                        </th>
                        <th
                            class="px-4 py-3 border-r border-gray-200 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Nama PML
                        </th>
                        <th
                            class="px-4 py-3 border-r border-gray-200 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Kegiatan
                        </th>
                        <th
                            class="px-4 py-3 border-r border-gray-200 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Jumlah PCL
                        </th>
                        <th
                            class="px-4 py-3 border-r border-gray-200 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Progress PCL
                        </th>
                        <th
                            class="px-4 py-3 border-r border-gray-200 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Status
                        </th>
                        <th
                            class="px-4 py-3 border-r border-gray-200 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($pmlData as $index => $pml): ?>
                        <?php $isEligible = isset($pml['is_eligible']) ? $pml['is_eligible'] : false; ?>
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-4 py-3 border-r border-gray text-sm text-gray-900">
                                <?= ($pager->currentPage - 1) * $pager->perPage + $index + 1 ?>
                            </td>
                            <td class="px-4 py-3 border-r border-gray">
                                <button onclick="showDetail(<?= $pml['id_pml'] ?>)"
                                    class="text-left hover:text-blue-600 transition-colors duration-150">
                                    <div
                                        class="text-sm font-medium text-blue-600 hover:text-blue-800 hover:underline cursor-pointer">
                                        <?= esc($pml['nama_pml']) ?>
                                    </div>
                                    <div class="text-xs text-gray-500"><?= esc($pml['email']) ?></div>
                                </button>
                            </td>
                            <td class="px-4 py-3 border-r border-gray">
                                <div class="text-sm text-gray-900"><?= esc($pml['nama_kegiatan_detail_proses']) ?></div>
                                <div class="text-xs text-gray-500"><?= esc($pml['nama_kegiatan']) ?></div>
                            </td>
                            <td class="px-4 py-3 border-r border-gray text-center">
                                <span
                                    class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-800 text-sm font-medium rounded-full">
                                    <i class="fas fa-users mr-1"></i>
                                    <?= $pml['total_pcl'] ?> PCL
                                </span>
                            </td>
                            <td class="px-4 py-3 border-r border-gray">
                                <div class="flex items-center justify-center">
                                    <div class="w-full max-w-xs">
                                        <div class="flex items-center justify-between mb-1">
                                            <span class="text-xs text-gray-600 font-medium">Progress Rata-rata</span>
                                            <span class="text-xs font-semibold text-gray-900">
                                                <?= number_format($pml['average_progress'], 1) ?>%
                                            </span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-3">
                                            <?php
                                            $avgProgress = $pml['average_progress'];
                                            $progressColor = $avgProgress >= 80 ? '#10b981' : ($avgProgress >= 50 ? '#3b82f6' : '#f59e0b');
                                            ?>
                                            <div class="h-3 rounded-full transition-all duration-300"
                                                style="width: <?= min(100, $avgProgress) ?>%; background-color: <?= $progressColor ?>;">
                                            </div>
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1 text-center">
                                            <?= $pml['pcl_completed'] ?> dari <?= $pml['total_pcl'] ?> PCL selesai
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 border-r border-gray text-center">
                                <span class="badge <?= $pml['status_class'] ?>">
                                    <?= $pml['status_label'] ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 border-r border-gray text-center">
                                <div class="flex items-center justify-center gap-3">
                                    <label class="switch-button"
                                        title="<?= $isEligible ? 'Klik untuk approve/reject' : 'Belum memenuhi syarat untuk disetujui' ?>">
                                        <input type="checkbox" <?= $pml['pml_status_approval'] == 1 ? 'checked' : '' ?>
                                            <?= !$isEligible ? 'disabled' : '' ?>
                                            onchange="toggleApproval(<?= $pml['id_pml'] ?>, '<?= esc($pml['nama_pml']) ?>', this)">
                                        <span class="switch-slider">
                                            <span class="switch-on">ON</span>
                                            <span class="switch-off">OFF</span>
                                        </span>
                                    </label>

                                    <?php if ($pml['pml_status_approval'] == 1 && !empty($pml['feedback_admin'])): ?>
                                        <button
                                            onclick="showFeedback('<?= esc($pml['feedback_admin']) ?>', '<?= esc($pml['tanggal_approval']) ?>')"
                                            class="p-2 text-purple-600 hover:bg-purple-50 rounded-lg transition-colors"
                                            title="Lihat Feedback">
                                            <i class="fas fa-comment text-sm"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- MOBILE CARD VIEW -->
        <div class="lg:hidden space-y-3">
            <?php foreach ($pmlData as $index => $pml): ?>
                <?php $isEligible = isset($pml['is_eligible']) ? $pml['is_eligible'] : false; ?>
                <div
                    class="bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                    <!-- Header Card -->
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-4 py-3 border-b border-gray-200">
                        <button onclick="showDetail(<?= $pml['id_pml'] ?>)" class="text-left w-full">
                            <h3 class="text-base font-semibold text-gray-900 mb-1">
                                <?= esc($pml['nama_pml']) ?>
                            </h3>
                            <p class="text-xs text-gray-600"><?= esc($pml['email']) ?></p>
                        </button>
                    </div>

                    <!-- Content Card -->
                    <div class="px-4 py-3 space-y-3">
                        <!-- Kegiatan -->
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase mb-1">Kegiatan</p>
                            <p class="text-sm font-semibold text-gray-900"><?= esc($pml['nama_kegiatan_detail_proses']) ?></p>
                            <p class="text-xs text-gray-600 mt-0.5"><?= esc($pml['nama_kegiatan']) ?></p>
                        </div>

                        <!-- Stats Grid -->
                        <div class="grid grid-cols-2 gap-2">
                            <!-- Jumlah PCL -->
                            <div class="bg-blue-50 border border-blue-100 rounded-lg p-3">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-xs text-blue-600 font-medium mb-1">Jumlah PCL</p>
                                        <p class="text-2xl font-bold text-blue-700"><?= $pml['total_pcl'] ?></p>
                                    </div>
                                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-users text-blue-600"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- PCL Selesai -->
                            <div class="bg-green-50 border border-green-100 rounded-lg p-3">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-xs text-green-600 font-medium mb-1">PCL Selesai</p>
                                        <p class="text-2xl font-bold text-green-700"><?= $pml['pcl_completed'] ?></p>
                                    </div>
                                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-check-circle text-green-600"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Progress Section -->
                        <!-- Progress Section -->
                        <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-semibold text-gray-700">Progress Rata-rata</span>
                                <span class="text-sm font-bold text-gray-900">
                                    <?= number_format($pml['average_progress'], 1) ?>%
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3 mb-1">
                                <?php
                                $avgProgress = $pml['average_progress'];
                                $progressColor = $avgProgress >= 80 ? '#10b981' : ($avgProgress >= 50 ? '#3b82f6' : '#f59e0b');
                                ?>
                                <div class="h-3 rounded-full transition-all duration-300"
                                    style="width: <?= min(100, $avgProgress) ?>%; background-color: <?= $progressColor ?>;">
                                </div>
                            </div>
                            <p class="text-xs text-gray-600 text-center">
                                <?= $pml['pcl_completed'] ?> dari <?= $pml['total_pcl'] ?> PCL selesai
                            </p>
                        </div>
                    </div>

                    <!-- Footer Card -->
                    <div class="bg-gray-50 px-4 py-3 border-t border-gray-200">
                        <div class="flex items-center justify-between">
                            <!-- Status Badge -->
                            <div>
                                <p class="text-xs text-gray-600 font-medium mb-1">Status</p>
                                <span class="badge <?= $pml['status_class'] ?> text-xs">
                                    <?= $pml['status_label'] ?>
                                </span>
                            </div>

                            <!-- Approval Switch -->
                            <div class="text-right">
                                <p class="text-xs text-gray-600 font-medium mb-2">Approval</p>
                                <div class="flex items-center gap-2">
                                    <label class="switch-button"
                                        title="<?= $isEligible ? 'Klik untuk approve/reject' : 'Belum memenuhi syarat untuk disetujui' ?>">
                                        <input type="checkbox" <?= $pml['pml_status_approval'] == 1 ? 'checked' : '' ?>
                                            <?= !$isEligible ? 'disabled' : '' ?>
                                            onchange="toggleApproval(<?= $pml['id_pml'] ?>, '<?= esc($pml['nama_pml']) ?>', this)">
                                        <span class="switch-slider">
                                            <span class="switch-on">ON</span>
                                            <span class="switch-off">OFF</span>
                                        </span>
                                    </label>

                                    <?php if ($pml['pml_status_approval'] == 1 && !empty($pml['feedback_admin'])): ?>
                                        <button
                                            onclick="showFeedback('<?= esc($pml['feedback_admin']) ?>', '<?= esc($pml['tanggal_approval']) ?>')"
                                            class="w-8 h-8 flex items-center justify-center text-purple-600 hover:bg-purple-50 rounded-lg transition-colors"
                                            title="Lihat Feedback">
                                            <i class="fas fa-comment text-sm"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Footer dengan Pagination -->
        <div class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-sm text-gray-600">
                Menampilkan data
                <span class="font-medium"><?= (($pager->currentPage - 1) * $pager->perPage) + 1 ?></span>-<span
                    class="font-medium"><?= min($pager->currentPage * $pager->perPage, $pager->total) ?></span>
                dari <span class="font-medium"><?= $pager->total ?></span> data
            </p>

            <!-- Custom Pagination -->
            <?php if ($pager->totalPages > 1): ?>
                <div class="flex items-center gap-2">
                    <!-- Previous Button -->
                    <?php if ($pager->currentPage > 1): ?>
                        <a href="?page_pml=<?= $pager->currentPage - 1 ?><?= !empty($filterKegiatan) ? '&kegiatan_wilayah=' . $filterKegiatan : '' ?><?= !empty($filterStatus) ? '&status=' . $filterStatus : '' ?>&perPage=<?= $perPage ?>"
                            class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    <?php endif; ?>

                    <!-- Page Numbers -->
                    <?php
                    $startPage = max(1, $pager->currentPage - 2);
                    $endPage = min($pager->totalPages, $pager->currentPage + 2);

                    for ($i = $startPage; $i <= $endPage; $i++):
                        ?>
                        <a href="?page_pml=<?= $i ?><?= !empty($filterKegiatan) ? '&kegiatan_wilayah=' . $filterKegiatan : '' ?><?= !empty($filterStatus) ? '&status=' . $filterStatus : '' ?>&perPage=<?= $perPage ?>"
                            class="px-4 py-2 text-sm font-medium <?= $i == $pager->currentPage ? 'text-white bg-blue-600' : 'text-gray-700 bg-white border border-gray-300 hover:bg-gray-50' ?> rounded-lg">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>

                    <!-- Next Button -->
                    <?php if ($pager->currentPage < $pager->totalPages): ?>
                        <a href="?page_pml=<?= $pager->currentPage + 1 ?><?= !empty($filterKegiatan) ? '&kegiatan_wilayah=' . $filterKegiatan : '' ?><?= !empty($filterStatus) ? '&status=' . $filterStatus : '' ?>&perPage=<?= $perPage ?>"
                            class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Detail PML -->
<div id="modalDetail" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900">Detail PML</h3>
            <button onclick="closeDetailModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <div id="detailContent" class="p-6">
            <!-- Content will be loaded here -->
        </div>
    </div>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* Switch Button Styles */
    .switch-button {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 28px;
        vertical-align: middle;
    }

    .switch-button input {
        opacity: 0;
        width: 0;
        height: 0;
        position: absolute;
    }

    .switch-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #cbd5e0;
        transition: all .3s ease;
        border-radius: 28px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 6px;
        box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .switch-slider:before {
        position: absolute;
        content: "";
        height: 22px;
        width: 22px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: all .3s ease;
        border-radius: 50%;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        z-index: 2;
    }

    .switch-on,
    .switch-off {
        font-size: 9px;
        font-weight: 700;
        color: white;
        z-index: 1;
        transition: opacity .3s ease;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .switch-on {
        opacity: 0;
        margin-left: 2px;
    }

    .switch-off {
        opacity: 1;
        margin-right: 2px;
    }

    input:checked+.switch-slider {
        background-color: #3b82f6;
        box-shadow: inset 0 1px 3px rgba(59, 130, 246, 0.3);
    }

    input:checked+.switch-slider:before {
        transform: translateX(32px);
    }

    input:checked+.switch-slider .switch-on {
        opacity: 1;
    }

    input:checked+.switch-slider .switch-off {
        opacity: 0;
    }

    input:disabled+.switch-slider {
        opacity: 0.4;
        cursor: not-allowed;
        background-color: #e5e7eb;
    }

    input:disabled+.switch-slider:before {
        background-color: #f3f4f6;
    }

    input:disabled+.switch-slider .switch-off {
        color: #9ca3af;
    }

    .switch-button:has(input:disabled) {
        cursor: not-allowed;
    }

    .switch-slider:has(input:disabled):hover {
        box-shadow: none;
    }

    input:focus+.switch-slider {
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
    }

    .switch-slider:hover:not(:has(input:disabled)) {
        box-shadow: 0 0 8px rgba(59, 130, 246, 0.4);
    }
</style>
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

    function processApproval(idPML, feedback, checkbox) {
        Swal.fire({
            title: 'Memproses...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const formData = new FormData();
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
        formData.append('id_pml', idPML);
        formData.append('feedback', feedback);

        fetch('<?= base_url('adminsurvei-kab/approval-laporan/approve') ?>', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(result => {
                if (result.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: result.message,
                        confirmButtonColor: '#3b82f6'
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    checkbox.checked = false;
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: result.message,
                        confirmButtonColor: '#ef4444'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                checkbox.checked = false;
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Terjadi kesalahan: ' + error.message,
                    confirmButtonColor: '#ef4444'
                });
            });
    }

    // Function untuk update filters dengan mempertahankan parameter
    // Function untuk update filters dengan mempertahankan parameter
    function updateFilters() {
        // Deteksi apakah desktop atau mobile
        const isDesktop = window.innerWidth >= 1024;

        let kegiatan, status, perPage;

        if (isDesktop) {
            kegiatan = document.querySelector('select[name="kegiatan_wilayah"]')?.value || '';
            status = document.querySelector('select[name="status"]')?.value || '';
            perPage = document.getElementById('perPageSelect')?.value || '10';
        } else {
            kegiatan = document.getElementById('filterKegiatanMobile')?.value || '';
            status = document.getElementById('filterStatusMobile')?.value || '';
            perPage = document.getElementById('perPageSelectMobile')?.value || '10';
        }

        const params = new URLSearchParams();
        if (kegiatan) params.append('kegiatan_wilayah', kegiatan);
        if (status) params.append('status', status);
        if (perPage) params.append('perPage', perPage);

        window.location.href = '<?= base_url('adminsurvei-kab/approval-laporan') ?>?' + params.toString();
    }

    // Search function
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

        // Search di table (desktop)
        const rows = document.querySelectorAll('table tbody tr');
        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(input) ? '' : 'none';
        });

        // Search di card (mobile)
        const cards = document.querySelectorAll('.lg\\:hidden > div.bg-white');
        cards.forEach(card => {
            const text = card.innerText.toLowerCase();
            card.style.display = text.includes(input) ? '' : 'none';
        });
    }

    // Toggle Approval
    function toggleApproval(idPML, namaPML, checkbox) {
        const isApproving = checkbox.checked;

        if (isApproving) {
            // Show approval modal with feedback
            Swal.fire({
                title: 'Setujui Laporan PML?',
                html: `Anda akan menyetujui laporan dari <strong>${namaPML}</strong>`,
                icon: 'question',
                input: 'textarea',
                inputLabel: 'Catatan (Opsional)',
                inputPlaceholder: 'Tambahkan catatan jika diperlukan...',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#6b7280',
                confirmButtonText: '<i class="fas fa-check mr-2"></i>Ya, Setujui',
                cancelButtonText: 'Batal',
                preConfirm: (feedback) => {
                    return { feedback: feedback || '' };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    processApproval(idPML, result.value.feedback, checkbox);
                } else {
                    // Reset checkbox if cancelled
                    checkbox.checked = false;
                }
            });
        } else {
            // Show rejection modal
            Swal.fire({
                title: 'Batalkan Approval?',
                html: `Anda akan membatalkan approval untuk <strong>${namaPML}</strong>`,
                icon: 'warning',
                input: 'textarea',
                inputLabel: 'Alasan Pembatalan (Wajib)',
                inputPlaceholder: 'Jelaskan alasan pembatalan...',
                inputValidator: (value) => {
                    if (!value) {
                        return 'Alasan pembatalan harus diisi!';
                    }
                },
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: '<i class="fas fa-times mr-2"></i>Ya, Batalkan',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    processReject(idPML, result.value, checkbox);
                } else {
                    // Reset checkbox if cancelled
                    checkbox.checked = true;
                }
            });
        }
    }

    // Show Detail Modal
    function showDetail(idPML) {
        console.log('Opening detail for PML ID:', idPML);

        Swal.fire({
            title: 'Memuat Data...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const url = `<?= base_url('adminsurvei-kab/approval-laporan/detail/') ?>${idPML}`;
        console.log('Fetching URL:', url);

        fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers.get('content-type'));

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                return response.json();
            })
            .then(result => {
                console.log('Result:', result);
                Swal.close();

                if (result.success) {
                    displayDetail(result.data);
                    document.getElementById('modalDetail').classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: result.message,
                        confirmButtonColor: '#ef4444'
                    });
                }
            })
            .catch(error => {
                console.error('Error details:', error);
                Swal.close();
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Terjadi kesalahan saat memuat data: ' + error.message,
                    confirmButtonColor: '#ef4444'
                });
            });
    }

    function displayDetail(data) {
        let pclHTML = '';
        let allCompleted = true;
        let allApproved = true;

        data.pcl_list.forEach((pcl, index) => {
            const statusBadge = pcl.is_completed
                ? '<span class="badge badge-success">Selesai</span>'
                : '<span class="badge badge-warning">Belum Selesai</span>';

            const approvalBadge = pcl.status_approval == 1
                ? '<span class="badge badge-success">Disetujui</span>'
                : '<span class="badge badge-secondary">Belum Disetujui</span>';

            if (!pcl.is_completed) allCompleted = false;
            if (pcl.status_approval != 1) allApproved = false;

            pclHTML += `
            <tr class="border-b border-gray-100">
                <td class="px-4 py-3 text-sm text-gray-900">${index + 1}</td>
                <td class="px-4 py-3 text-sm text-gray-900">${pcl.nama_user}</td>
                <td class="px-4 py-3 text-center text-sm text-gray-900">${pcl.target.toLocaleString()}</td>
                <td class="px-4 py-3 text-center text-sm text-gray-900">${pcl.realisasi.toLocaleString()}</td>
                <td class="px-4 py-3 text-center">
                    <div class="flex items-center justify-center">
                        <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: ${Math.min(100, pcl.progress)}%"></div>
                        </div>
                        <span class="text-sm font-semibold">${pcl.progress}%</span>
                    </div>
                </td>
                <td class="px-4 py-3 text-center">${statusBadge}</td>
                <td class="px-4 py-3 text-center">${approvalBadge}</td>
            </tr>
        `;
        });

        const eligibilityHTML = (allCompleted && allApproved)
            ? '<div class="bg-green-50 border border-green-200 rounded-lg p-4"><p class="text-sm text-green-700"><i class="fas fa-check-circle mr-2"></i><strong>Memenuhi Syarat:</strong> Semua PCL sudah menyelesaikan target 100% dan telah disetujui oleh PML.</p></div>'
            : '<div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4"><p class="text-sm text-yellow-700"><i class="fas fa-exclamation-triangle mr-2"></i><strong>Belum Memenuhi Syarat:</strong> PML ini belum bisa disetujui karena belum semua PCL menyelesaikan target atau belum semua PCL disetujui oleh PML.</p></div>';

        const content = `
        <div class="space-y-6">
            <!-- PML Info -->
            <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="font-semibold text-gray-900 mb-3">Informasi PML</h4>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Nama PML</p>
                        <p class="text-sm font-medium text-gray-900">${data.nama_pml}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Email</p>
                        <p class="text-sm font-medium text-gray-900">${data.email}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Kegiatan</p>
                        <p class="text-sm font-medium text-gray-900">${data.nama_kegiatan_detail_proses}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Target PML</p>
                        <p class="text-sm font-medium text-gray-900">${data.target.toLocaleString()}</p>
                    </div>
                </div>
            </div>

            <!-- Eligibility Status -->
            ${eligibilityHTML}

            <!-- PCL List -->
            <div>
                <h4 class="font-semibold text-gray-900 mb-3">Daftar PCL</h4>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">No</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Nama PCL</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Target</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Realisasi</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Progress</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Status</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Approval</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${pclHTML}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    `;

        document.getElementById('detailContent').innerHTML = content;
    }

    function closeDetailModal() {
        document.getElementById('modalDetail').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    function processApproval(idPML, feedback, checkbox) {
        Swal.fire({
            title: 'Memproses...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const formData = new FormData();
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
        formData.append('id_pml', idPML);
        formData.append('feedback', feedback);

        fetch('<?= base_url('adminsurvei-kab/approval-laporan/approve') ?>', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(result => {
                if (result.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: result.message,
                        confirmButtonColor: '#3b82f6'
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    checkbox.checked = false;
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: result.message,
                        confirmButtonColor: '#ef4444'
                    });
                }
            })
            .catch(error => {
                checkbox.checked = false;
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Terjadi kesalahan: ' + error.message,
                    confirmButtonColor: '#ef4444'
                });
            });
    }

    function processReject(idPML, feedback, checkbox) {
        Swal.fire({
            title: 'Memproses...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const formData = new FormData();
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
        formData.append('id_pml', idPML);
        formData.append('feedback', feedback);

        fetch('<?= base_url('adminsurvei-kab/approval-laporan/reject') ?>', {
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
                        confirmButtonColor: '#3b82f6'
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    checkbox.checked = true;
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: result.message,
                        confirmButtonColor: '#ef4444'
                    });
                }
            })
            .catch(error => {
                checkbox.checked = true;
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Terjadi kesalahan pada sistem',
                    confirmButtonColor: '#ef4444'
                });
            });
    }

    // Show Feedback
    function showFeedback(feedback, tanggalApproval) {
        Swal.fire({
            title: 'Feedback Approval',
            html: `
            <div class="text-left">
                <p class="text-sm text-gray-600 mb-2">
                    <i class="fas fa-calendar mr-2"></i>
                    <strong>Tanggal Approval:</strong> ${new Date(tanggalApproval).toLocaleDateString('id-ID')}
                </p>
                <div class="bg-gray-50 rounded-lg p-4 mt-3">
                    <p class="text-sm text-gray-700 whitespace-pre-wrap">${feedback}</p>
                </div>
            </div>
        `,
            icon: 'info',
            confirmButtonColor: '#3b82f6',
            confirmButtonText: 'Tutup'
        });
    }

    // Flashdata
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
</script>

<?= $this->endSection() ?>