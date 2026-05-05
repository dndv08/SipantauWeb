<?= $this->extend('layouts/sadmin_layout') ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="mb-6">
    <div class="flex items-center text-sm text-gray-600 mb-4">
        <a href="<?= base_url('superadmin') ?>" class="hover:text-blue-600 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Back
        </a>
    </div>
    <h1 class="text-2xl font-bold text-gray-900">Kelola Master Output</h1>
    <p class="text-gray-600 mt-1">Kelola data master output kegiatan survei/sensus</p>
</div>

<!-- DESKTOP LAYOUT -->
<div class="card mb-6 hidden lg:block">
    <!-- Search and PerPage Section -->
    <div style="display: grid; grid-template-columns: 1fr 200px 200px; gap: 1rem; margin-bottom: 1.5rem;">
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
                    placeholder="Cari nama output, fungsi..." onkeyup="searchTable()">
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
            <a href="<?= base_url('superadmin/master-output/create') ?>"
                class="btn-primary whitespace-nowrap w-full text-center inline-block">
                <i class="fas fa-plus mr-2"></i>
                Tambah Output
            </a>
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
                    placeholder="Cari nama output, fungsi..." onkeyup="searchTable()">
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

        <!-- Add Button Mobile -->
        <div class="w-full">
            <a href="<?= base_url('superadmin/master-output/create') ?>"
                class="btn-primary w-full text-center inline-block">
                <i class="fas fa-plus mr-2"></i>
                Tambah Output
            </a>
        </div>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div class="overflow-x-auto">
        <table class="w-full" id="masterOutputTable">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th
                        class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-r border-gray-200 w-16">
                        No
                    </th>
                    <th
                        class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-r border-gray-200">
                        Nama Output
                    </th>
                    <th
                        class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-r border-gray-200">
                        Fungsi
                    </th>
                    <th
                        class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider border-r border-gray-200 w-32">
                        Aksi
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (empty($outputs)): ?>
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-2"></i>
                            <p>Belum ada data master output</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($outputs as $index => $output): ?>
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-4 py-4 border-r border-gray-200 text-sm text-gray-900">
                                <?= ($pager->getCurrentPage('outputs') - 1) * $pager->getPerPage('outputs') + $index + 1 ?>
                            </td>
                            <td class="px-4 py-4 border-r border-gray-200">
                                <p class="text-sm font-medium text-gray-900"><?= esc($output['nama_output']) ?></p>
                            </td>
                            <td class="px-4 py-4 border-r border-gray-200">
                                <p class="text-sm text-gray-600 line-clamp-2"><?= esc($output['fungsi']) ?></p>
                            </td>

                            <td class="px-4 py-4 border-r border-gray-200">
                                <div class="flex items-center justify-center space-x-2">
                                    <a href="<?= base_url('superadmin/master-output/' . $output['id_output'] . '/edit') ?>"
                                        class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors duration-200"
                                        title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button
                                        onclick="confirmDelete(<?= $output['id_output'] ?>, '<?= esc($output['nama_output']) ?>')"
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
                class="font-medium"><?= (($pager->getCurrentPage('outputs') - 1) * $pager->getPerPage('outputs')) + 1 ?></span>-<span
                class="font-medium"><?= min($pager->getCurrentPage('outputs') * $pager->getPerPage('outputs'), $pager->getTotal('outputs')) ?></span>
            dari <span class="font-medium"><?= $pager->getTotal('outputs') ?></span> data
        </p>

        <!-- Custom Pagination -->
        <?php if ($pager->getPageCount('outputs') > 1): ?>
            <?= $pager->links('outputs', 'tailwind_pager') ?>
        <?php endif; ?>
    </div>
</div>

<!-- SweetAlert2 Script -->
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

    // Function untuk update perPage
    function updatePerPage() {
        // Deteksi apakah desktop atau mobile
        const isDesktop = window.innerWidth >= 1024;

        let perPage;
        if (isDesktop) {
            perPage = document.getElementById('perPageSelect')?.value || '10';
        } else {
            perPage = document.getElementById('perPageSelectMobile')?.value || '10';
        }

        const params = new URLSearchParams();
        if (perPage) params.append('perPage', perPage);
        window.location.href = '<?= base_url('superadmin/master-output') ?>?' + params.toString();
    }
    // ====================================================================
    // Show SweetAlert on Page Load (Success/Error from Session)
    // ====================================================================
    <?php if (session()->getFlashdata('success')): ?>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '<?= session()->getFlashdata('success') ?>',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true,
            customClass: {
                popup: 'rounded-xl'
            }
        });
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: '<?= session()->getFlashdata('error') ?>',
            confirmButtonColor: '#3b82f6',
            customClass: {
                popup: 'rounded-xl',
                confirmButton: 'px-6 py-2.5 rounded-lg font-medium'
            }
        });
    <?php endif; ?>

    // ====================================================================
    // Search Table Function
    // ====================================================================
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

        const table = document.getElementById('masterOutputTable');
        const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];
            const cells = row.getElementsByTagName('td');
            let found = false;

            // Skip jika row adalah "tidak ada data"
            if (cells.length === 1 && cells[0].getAttribute('colspan')) {
                continue;
            }

            for (let j = 1; j < cells.length - 1; j++) {
                const cell = cells[j];
                if (cell) {
                    const textValue = cell.textContent || cell.innerText;
                    if (textValue.toLowerCase().indexOf(filter) > -1) {
                        found = true;
                        break;
                    }
                }
            }

            row.style.display = found ? '' : 'none';
        }
    }

    // ====================================================================
    // Delete Confirmation with SweetAlert2
    // ====================================================================
    function confirmDelete(id, name) {
        Swal.fire({
            title: 'Hapus Data Output?',
            html: `Apakah Anda yakin ingin menghapus data <strong>"${name}"</strong>?<br><span class="text-sm text-gray-600">Tindakan ini tidak dapat dibatalkan.</span>`,
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

    // ====================================================================
    // Delete Data Function
    // ====================================================================
    function deleteData(id, name) {
        // Show loading
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

        // AJAX request untuk delete
        fetch(`<?= base_url('superadmin/master-output/') ?>${id}`, {
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
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true,
                        customClass: {
                            popup: 'rounded-xl'
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