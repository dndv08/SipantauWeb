<?= $this->extend('layouts/pemantau_provinsi_layout') ?>
<?= $this->section('content') ?>

<!-- Page Header -->
<div class="mb-6">
    <div class="flex items-center text-sm text-gray-600 mb-4">
        <a href="<?= base_url('pemantau-provinsi') ?>" class="hover:text-blue-600 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Back
        </a>
    </div>
    <h1 class="text-2xl font-bold text-gray-900">Kelola Master Kegiatan Detail Proses</h1>
    <p class="text-gray-600 mt-1">
        Lihat data detail kegiatan survei.
    </p>
</div>

<!-- DESKTOP LAYOUT -->
<div class="card mb-6 hidden lg:block">
    <!-- Search dan Filter Section -->
    <div style="display: grid; grid-template-columns: 1fr 200px 400px; gap: 1rem;">
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

        <!-- Filter Kegiatan Detail -->
        <div>
            <label for="kegiatanDetailFilter" class="block text-sm font-medium text-gray-700 mb-1">
                Filter Kegiatan Detail
            </label>
            <form id="filterForm" method="get" class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-filter text-gray-400"></i>
                </div>

                <select name="kegiatan_detail" id="kegiatanDetailFilter"
                    class="w-full border border-gray-300 rounded-lg pl-10 pr-8 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 appearance-none">
                    <option value="">Semua Kegiatan Detail</option>
                    <?php foreach ($kegiatanDetailList as $kd): ?>
                        <option value="<?= $kd['id_kegiatan_detail']; ?>"
                            <?= ($selectedKegiatanDetail == $kd['id_kegiatan_detail']) ? 'selected' : ''; ?>>
                            <?= esc($kd['nama_kegiatan_detail']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MOBILE LAYOUT -->
<div class="card mb-6 lg:hidden">
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
                    placeholder="Cari kegiatan detail atau satuan..." onkeyup="searchTable()">
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

        <!-- Filter Kegiatan Detail Mobile -->
        <div class="w-full">
            <label for="kegiatanDetailFilterMobile" class="block text-sm font-medium text-gray-700 mb-1">
                Filter Kegiatan Detail
            </label>
            <form id="filterFormMobile" method="get">
                <select name="kegiatan_detail" id="kegiatanDetailFilterMobile" class="input-field w-full text-sm">
                    <option value="">Semua Kegiatan Detail</option>
                    <?php foreach ($kegiatanDetailList as $kd): ?>
                        <option value="<?= $kd['id_kegiatan_detail']; ?>"
                            <?= ($selectedKegiatanDetail == $kd['id_kegiatan_detail']) ? 'selected' : ''; ?>>
                            <?= esc($kd['nama_kegiatan_detail']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
    </div>
</div>

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
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (!empty($kegiatanDetails)): ?>
                    <?php foreach ($kegiatanDetails as $index => $detail): ?>
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-4 py-4 text-sm text-gray-900">
                                <?= ($pager->getCurrentPage('detail_proses') - 1) * $pager->getPerPage('detail_proses') + $index + 1 ?>
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
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="px-4 py-6 text-center text-gray-500">Belum ada data kegiatan detail proses.
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
                class="font-medium"><?= (($pager->getCurrentPage('detail_proses') - 1) * $pager->getPerPage('detail_proses')) + 1 ?></span>-<span
                class="font-medium"><?= min($pager->getCurrentPage('detail_proses') * $pager->getPerPage('detail_proses'), $pager->getTotal('detail_proses')) ?></span>
            dari <span class="font-medium"><?= $pager->getTotal('detail_proses') ?></span> data
        </p>

        <!-- Custom Pagination -->
        <?php if ($pager->getPageCount('detail_proses') > 1): ?>
            <?= $pager->links('detail_proses', 'tailwind_pager') ?>
        <?php endif; ?>
    </div>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Auto submit filter kegiatan detail - Desktop
    document.getElementById('kegiatanDetailFilter').addEventListener('change', function () {
        const perPage = document.getElementById('perPageSelect').value;
        const kegiatanDetail = this.value;
        const params = new URLSearchParams();

        if (perPage) params.append('perPage', perPage);
        if (kegiatanDetail) params.append('kegiatan_detail', kegiatanDetail);

        window.location.href = '<?= base_url('pemantau-provinsi/detail-proses') ?>?' + params.toString();
    });

    // Auto submit filter kegiatan detail - Mobile
    document.getElementById('kegiatanDetailFilterMobile').addEventListener('change', function () {
        const perPage = document.getElementById('perPageSelectMobile').value;
        const kegiatanDetail = this.value;
        const params = new URLSearchParams();

        if (perPage) params.append('perPage', perPage);
        if (kegiatanDetail) params.append('kegiatan_detail', kegiatanDetail);

        window.location.href = '<?= base_url('pemantau-provinsi/detail-proses') ?>?' + params.toString();
    });

    // Fitur pencarian tabel
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

    // Alert sukses
    <?php if (session()->getFlashdata('success')): ?>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '<?= session()->getFlashdata('success') ?>',
            showConfirmButton: false,
            timer: 2000
        });
    <?php endif; ?>

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
        // Deteksi apakah desktop atau mobile
        const isDesktop = window.innerWidth >= 1024;

        let perPage;
        let kegiatanDetail;

        if (isDesktop) {
            perPage = document.getElementById('perPageSelect')?.value || '10';
            kegiatanDetail = document.getElementById('kegiatanDetailFilter')?.value || '';
        } else {
            perPage = document.getElementById('perPageSelectMobile')?.value || '10';
            kegiatanDetail = document.getElementById('kegiatanDetailFilterMobile')?.value || '';
        }

        const params = new URLSearchParams();
        if (perPage) params.append('perPage', perPage);
        if (kegiatanDetail) params.append('kegiatan_detail', kegiatanDetail);

        window.location.href = '<?= base_url('pemantau-provinsi/detail-proses') ?>?' + params.toString();
    }

    // Update submit filter untuk preserve perPage
    document.getElementById('kegiatanDetailFilter').addEventListener('change', function () {
        const perPage = document.getElementById('perPageSelect').value;
        const kegiatanDetail = this.value;
        const params = new URLSearchParams();

        if (perPage) params.append('perPage', perPage);
        if (kegiatanDetail) params.append('kegiatan_detail', kegiatanDetail);

        window.location.href = '<?= base_url('pemantau-provinsi/detail-proses') ?>?' + params.toString();
    });
</script>

<?= $this->endSection() ?>