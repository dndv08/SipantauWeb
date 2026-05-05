<?= $this->extend('layouts/adminkab_layout') ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="mb-6">
    <div class="flex items-center mb-2">
        <a href="<?= base_url('adminsurvei-kab') ?>" class="text-gray-600 hover:text-gray-900 mr-2">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
    <h1 class="text-2xl font-bold text-gray-900">Assign Petugas ke Level SLS/Sub-SLS</h1>
    <p class="text-sm text-gray-600 mt-1">Kelola penugasan petugas per wilayah SLS/Sub-SLS untuk <?= esc($admin['nama_kabupaten']) ?></p>
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
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-1">Total Assignment</p>
                <h3 class="text-3xl font-bold text-gray-900"><?= $stats['total_assignments'] ?></h3>
            </div>
            <div class="w-14 h-14 bg-blue-50 rounded-lg flex items-center justify-center flex-shrink-0">
                <i class="fas fa-clipboard-list text-2xl text-blue-600"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-1">Petugas Terlibat</p>
                <h3 class="text-3xl font-bold text-gray-900"><?= $stats['total_petugas'] ?></h3>
            </div>
            <div class="w-14 h-14 bg-green-50 rounded-lg flex items-center justify-center flex-shrink-0">
                <i class="fas fa-user-check text-2xl text-green-600"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-1">Sub-SLS Tercover</p>
                <h3 class="text-3xl font-bold text-gray-900"><?= $stats['total_sub_sls'] ?></h3>
            </div>
            <div class="w-14 h-14 bg-purple-50 rounded-lg flex items-center justify-center flex-shrink-0">
                <i class="fas fa-map-marked-alt text-2xl text-purple-600"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-1">Total Kegiatan</p>
                <h3 class="text-3xl font-bold text-gray-900"><?= $stats['total_kegiatan'] ?></h3>
            </div>
            <div class="w-14 h-14 bg-orange-50 rounded-lg flex items-center justify-center flex-shrink-0">
                <i class="fas fa-briefcase text-2xl text-orange-600"></i>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Card -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <!-- DESKTOP LAYOUT -->
    <div class="hidden lg:flex flex-wrap gap-3 mb-6 items-end">
        <!-- Search Box -->
        <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-medium text-gray-700 mb-1">Pencarian</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                <input type="text" id="searchInput" placeholder="Cari wilayah atau petugas..."
                    class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                    onkeyup="searchTable()">
            </div>
        </div>

        <!-- Filter Kegiatan -->
        <div class="flex-1 min-w-[250px]">
            <label class="block text-sm font-medium text-gray-700 mb-1">Filter Kegiatan</label>
            <form action="<?= base_url('adminsurvei-kab/assign-sub-sls') ?>" method="get" id="filterForm">
                <select name="kegiatan" onchange="this.form.submit()" class="w-full border-gray-300 rounded-lg py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Kegiatan</option>
                    <?php foreach ($kegiatanList as $k) : ?>
                        <option value="<?= $k['id_kegiatan_wilayah'] ?>" <?= $selectedKegiatan == $k['id_kegiatan_wilayah'] ? 'selected' : '' ?>>
                            <?= esc($k['nama_kegiatan_detail']) ?> - <?= esc($k['nama_kegiatan_detail_proses']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-2">
            <a href="<?= base_url('adminsurvei-kab/assign-sub-sls/create') . ($selectedKegiatan ? '?kegiatan=' . $selectedKegiatan : '') ?>"
                class="inline-flex items-center justify-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors whitespace-nowrap">
                <i class="fas fa-plus mr-2"></i> Tambah Assignment
            </a>
            <button onclick="openImportModal()"
                class="inline-flex items-center justify-center px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors whitespace-nowrap">
                <i class="fas fa-file-excel mr-2"></i> Import Excel
            </button>
            <button onclick="openCopyConfigModal()"
                class="inline-flex items-center justify-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors whitespace-nowrap">
                <i class="fas fa-copy mr-2"></i> Copy Konfigurasi
            </button>
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto rounded-lg border border-gray-200">
        <table class="w-full border-collapse" id="assignTable">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200 text-left">
                    <th class="px-4 py-3 text-sm font-semibold text-gray-700">No</th>
                    <th class="px-4 py-3 text-sm font-semibold text-gray-700">Kegiatan</th>
                    <th class="px-4 py-3 text-sm font-semibold text-gray-700">Wilayah (Kec/Des/SLS)</th>
                    <th class="px-4 py-3 text-sm font-semibold text-gray-700">Petugas</th>
                    <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php if (empty($assignments)) : ?>
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-2 block text-gray-300"></i>
                            <p>Belum ada data assignment</p>
                        </td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($assignments as $i => $row) : ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-sm text-gray-700"><?= $i + 1 ?></td>
                            <td class="px-4 py-3">
                                <div class="text-sm font-medium text-gray-900"><?= esc($row['nama_kegiatan_detail']) ?></div>
                                <div class="text-xs text-gray-500"><?= esc($row['nama_kegiatan_detail_proses']) ?></div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm font-semibold text-gray-900"><?= esc($row['nama_kecamatan']) ?></div>
                                <div class="text-xs text-gray-500"><?= esc($row['real_nama_desa']) ?></div>
                                <div class="text-xs text-blue-600 font-mono mt-1">SLS: <?= esc($row['nama_sls']) ?> (<?= esc($row['id_sub_sls']) ?>)</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm font-semibold text-gray-900"><?= esc($row['nama_petugas']) ?></div>
                                <div class="text-xs text-gray-500"><?= esc($row['sobat_id']) ?></div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center space-x-2">
                                    <a href="<?= base_url('adminsurvei-kab/assign-sub-sls/detail/' . $row['id_assignment_sub_sls']) ?>"
                                        class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded transition-colors shadow-sm">
                                        Detail
                                    </a>
                                    <a href="<?= base_url('adminsurvei-kab/assign-sub-sls/edit/' . $row['id_assignment_sub_sls']) ?>"
                                        class="px-3 py-1.5 bg-yellow-600 hover:bg-yellow-700 text-white text-xs font-medium rounded transition-colors shadow-sm">
                                        Edit
                                    </a>
                                    <button onclick="confirmDelete(<?= $row['id_assignment_sub_sls'] ?>)"
                                        class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-medium rounded transition-colors shadow-sm">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Import -->
<div id="modalImport" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4 backdrop-blur-sm transition-all">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xl overflow-hidden transform transition-all">
        <div class="px-6 py-4 bg-white border-b border-gray-100 flex justify-between items-center">
            <h3 class="text-xl font-bold text-gray-900">Import Data Petugas dari Excel</h3>
            <button onclick="closeImportModal()" class="text-gray-400 hover:text-gray-600 p-2 rounded-full hover:bg-gray-100 transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="p-6">
            <div class="mb-6 bg-blue-50 border border-blue-100 text-blue-800 px-4 py-3 rounded-xl flex items-start">
                <i class="fas fa-info-circle mt-1 mr-3 text-blue-500"></i>
                <span class="text-sm font-medium">Langkah 1: Pilih Kegiatan untuk mengunduh template yang sesuai.</span>
            </div>

            <form id="formImport" class="space-y-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Pilih Kegiatan Survei *</label>
                    <select name="id_kegiatan_wilayah" id="import_id_kegiatan" required 
                        class="w-full border-gray-300 rounded-xl py-3 focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all">
                        <option value="">-- Pilih Kegiatan --</option>
                        <?php foreach ($kegiatanList as $k) : ?>
                            <option value="<?= $k['id_kegiatan_wilayah'] ?>"><?= esc($k['nama_kegiatan_detail']) ?> - <?= esc($k['nama_kegiatan_detail_proses']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="flex justify-center">
                    <button type="button" onclick="downloadTemplate()" 
                        class="w-full inline-flex items-center justify-center px-6 py-3 bg-blue-100 hover:bg-blue-200 text-blue-700 font-bold rounded-xl transition-all">
                        <i class="fas fa-download mr-2"></i> Download Template Excel
                    </button>
                </div>

                <hr class="border-gray-100">

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">File Excel *</label>
                    <div class="relative group">
                        <input type="file" name="file" accept=".xlsx, .xls" required 
                            class="w-full px-4 py-3 border-2 border-dashed border-gray-300 rounded-xl group-hover:border-blue-400 transition-all cursor-pointer">
                    </div>
                    <p class="text-[10px] text-gray-500 mt-2 italic">Format: .xlsx atau .xls (Maksimal 5MB)</p>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="button" onclick="closeImportModal()" 
                        class="flex-1 px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl transition-all">
                        Batal
                    </button>
                    <button type="submit" 
                        class="flex-1 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-lg shadow-blue-200 transition-all">
                        <i class="fas fa-upload mr-2"></i> Upload & Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Copy Config -->
<div id="modalCopyConfig" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4 backdrop-blur-sm transition-all">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden transform transition-all">
        <div class="px-6 py-4 bg-white border-b border-gray-100 flex justify-between items-center">
            <h3 class="text-xl font-bold text-gray-900">Copy Konfigurasi Petugas</h3>
            <button onclick="closeCopyConfigModal()" class="text-gray-400 hover:text-gray-600 p-2 rounded-full hover:bg-gray-100 transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="p-6">
            <div id="stepSelect">
                <div class="mb-6 bg-blue-50 border border-blue-100 text-blue-800 px-4 py-3 rounded-xl flex items-start">
                    <i class="fas fa-info-circle mt-1 mr-3 text-blue-500"></i>
                    <span class="text-sm font-medium">Pilih kegiatan yang ingin dicopy konfigurasinya, kemudian pilih kegiatan tujuan.</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Kegiatan Sumber *</label>
                        <select id="copy_source" class="w-full border-gray-300 rounded-xl py-3 focus:ring-4 focus:ring-blue-100 transition-all">
                            <option value="">-- Pilih Kegiatan Sumber --</option>
                        </select>
                        <p class="text-[10px] text-gray-500 mt-1 italic">Kegiatan yang sudah ada assignment</p>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Kegiatan Tujuan *</label>
                        <select id="copy_target" class="w-full border-gray-300 rounded-xl py-3 focus:ring-4 focus:ring-blue-100 transition-all">
                            <option value="">-- Pilih Kegiatan Tujuan --</option>
                        </select>
                        <p class="text-[10px] text-gray-500 mt-1 italic">Kegiatan yang akan diterapkan konfigurasi</p>
                    </div>
                </div>

                <button type="button" onclick="previewCopy()" 
                    class="w-full px-6 py-4 bg-blue-500 hover:bg-blue-600 text-white font-bold rounded-xl shadow-lg shadow-blue-100 flex items-center justify-center transition-all">
                    <i class="fas fa-eye mr-2"></i> Preview Konfigurasi
                </button>
            </div>

            <div id="stepPreview" class="hidden">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="font-bold text-gray-900">Preview Data Penugasan</h4>
                    <span id="previewCount" class="px-3 py-1 bg-blue-100 text-blue-700 text-xs font-bold rounded-full">0 Data</span>
                </div>
                <div id="previewContent" class="max-h-80 overflow-y-auto mb-8 space-y-3 p-4 bg-gray-50 rounded-2xl border border-gray-100"></div>
                
                <div class="flex gap-4">
                    <button type="button" onclick="backToSelect()" 
                        class="flex-1 px-6 py-4 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl transition-all">
                        Batal
                    </button>
                    <button type="button" onclick="executeCopy()" 
                        class="flex-[2] px-6 py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-lg shadow-indigo-100 transition-all">
                        <i class="fas fa-copy mr-2"></i> Eksekusi Copy
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function searchTable() {
    const input = document.getElementById("searchInput");
    const filter = input.value.toUpperCase();
    const table = document.getElementById("assignTable");
    const tr = table.getElementsByTagName("tr");

    for (let i = 1; i < tr.length; i++) {
        let textContent = tr[i].textContent || tr[i].innerText;
        if (textContent.toUpperCase().indexOf(filter) > -1) {
            tr[i].style.display = "";
        } else {
            tr[i].style.display = "none";
        }
    }
}

function openImportModal() {
    document.getElementById('modalImport').classList.remove('hidden');
}
function closeImportModal() {
    document.getElementById('modalImport').classList.add('hidden');
}
function downloadTemplate() {
    const id = document.getElementById('import_id_kegiatan').value;
    if (!id) {
        Swal.fire('Peringatan', 'Pilih kegiatan terlebih dahulu untuk mengunduh template.', 'warning');
        return;
    }
    window.location.href = '<?= base_url('adminsurvei-kab/assign-sub-sls/download-template/') ?>' + id;
}

document.getElementById('formImport').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

    Swal.fire({
        title: 'Sedang Memproses...',
        html: 'Mohon tunggu sebentar',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading() }
    });

    fetch('<?= base_url('adminsurvei-kab/assign-sub-sls/import') ?>', {
        method: 'POST',
        body: formData,
        headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then(res => res.json())
    .then(data => {
        Swal.close();
        if (data.success) {
            Swal.fire('Berhasil!', data.message, 'success').then(() => window.location.reload());
        } else {
            Swal.fire('Gagal!', data.message, 'error');
        }
    })
    .catch(err => {
        Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
    });
});

function openCopyConfigModal() {
    document.getElementById('modalCopyConfig').classList.remove('hidden');
    fetch('<?= base_url('adminsurvei-kab/assign-sub-sls/get-kegiatan-for-copy') ?>')
    .then(res => res.json())
    .then(data => {
        const source = document.getElementById('copy_source');
        const target = document.getElementById('copy_target');
        source.innerHTML = '<option value="">-- Pilih Kegiatan Sumber --</option>';
        target.innerHTML = '<option value="">-- Pilih Kegiatan Tujuan --</option>';
        data.kegiatan_source.forEach(k => {
            source.innerHTML += `<option value="${k.id_kegiatan_wilayah}">${k.nama_kegiatan_detail} (${k.jumlah_assign})</option>`;
        });
        data.kegiatan_target.forEach(k => {
            target.innerHTML += `<option value="${k.id_kegiatan_wilayah}">${k.nama_kegiatan_detail}</option>`;
        });
    });
}
function closeCopyConfigModal() {
    document.getElementById('modalCopyConfig').classList.add('hidden');
    backToSelect();
}
function previewCopy() {
    const sourceId = document.getElementById('copy_source').value;
    if (!sourceId) {
        Swal.fire('Info', 'Pilih kegiatan sumber terlebih dahulu.', 'info');
        return;
    }
    
    Swal.fire({title: 'Memuat Preview...', didOpen: () => Swal.showLoading()});

    fetch('<?= base_url('adminsurvei-kab/assign-sub-sls/preview-copy-configuration') ?>', {
        method: 'POST',
        body: JSON.stringify({id_kegiatan_source: sourceId, '<?= csrf_token() ?>': '<?= csrf_hash() ?>'}),
        headers: {'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest'}
    })
    .then(res => res.json())
    .then(data => {
        Swal.close();
        const content = document.getElementById('previewContent');
        const count = document.getElementById('previewCount');
        content.innerHTML = '';
        
        if (data.preview.length === 0) {
            content.innerHTML = '<p class="text-center text-gray-500 py-4 italic">Tidak ada data untuk dicopy</p>';
            count.textContent = '0 Data';
        } else {
            count.textContent = data.preview.length + ' Data';
            data.preview.forEach(row => {
                content.innerHTML += `
                <div class="p-3 bg-white border border-gray-100 rounded-xl shadow-sm text-sm">
                    <div class="flex justify-between">
                        <span class="font-bold text-gray-900">${row.nama_sls}</span>
                        <span class="text-blue-600 font-mono text-[10px]">${row.id_sub_sls}</span>
                    </div>
                    <div class="text-gray-600 mt-1">Petugas: <span class="font-medium">${row.nama_user}</span></div>
                </div>`;
            });
        }
        document.getElementById('stepSelect').classList.add('hidden');
        document.getElementById('stepPreview').classList.remove('hidden');
    });
}
function backToSelect() {
    document.getElementById('stepPreview').classList.add('hidden');
    document.getElementById('stepSelect').classList.remove('hidden');
}
function executeCopy() {
    const sourceId = document.getElementById('copy_source').value;
    const targetId = document.getElementById('copy_target').value;
    
    if (!targetId) {
        Swal.fire('Info', 'Pilih kegiatan tujuan.', 'info');
        return;
    }

    Swal.fire({
        title: 'Konfirmasi',
        text: 'Apakah Anda yakin ingin menyalin konfigurasi ini?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Eksekusi',
        confirmButtonColor: '#4f46e5'
    }).then(res => {
        if (res.isConfirmed) {
            Swal.fire({title: 'Sedang Menyalin...', didOpen: () => Swal.showLoading()});
            fetch('<?= base_url('adminsurvei-kab/assign-sub-sls/execute-copy-configuration') ?>', {
                method: 'POST',
                body: JSON.stringify({id_kegiatan_source: sourceId, id_kegiatan_target: targetId, '<?= csrf_token() ?>': '<?= csrf_hash() ?>'}),
                headers: {'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest'}
            })
            .then(res => res.json())
            .then(data => {
                Swal.close();
                if (data.success) {
                    Swal.fire('Berhasil!', data.message, 'success').then(() => window.location.reload());
                } else {
                    Swal.fire('Gagal!', data.message, 'error');
                }
            });
        }
    });
}

function confirmDelete(id) {
    Swal.fire({
        title: 'Hapus Assignment?',
        text: "Tindakan ini tidak dapat dibatalkan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Hapus!'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?= base_url('adminsurvei-kab/assign-sub-sls/delete/') ?>' + id;
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
</script>
<?= $this->endSection() ?>
