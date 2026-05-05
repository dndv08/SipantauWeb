<?= $this->extend('layouts/adminkab_layout') ?>

<?= $this->section('content') ?>

<!-- Header & Back Button -->
<div class="mb-6">
    <a href="<?= base_url('adminsurvei-kab') ?>" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-4 transition-colors">
        <i class="fas fa-arrow-left mr-2"></i> Back
    </a>
    <h1 class="text-2xl font-bold text-gray-900">Data Usaha SBR</h1>
    <p class="text-sm text-gray-600">Daftar usaha SBR beserta identitas wilayahnya untuk <?= esc($admin['nama_kabupaten']) ?></p>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <!-- Total Usaha -->
    <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Total Usaha SBR</p>
            <h3 class="text-2xl font-bold text-gray-900"><?= number_format($stats['total_usaha']) ?></h3>
        </div>
        <div class="w-12 h-12 bg-blue-50 rounded-lg flex items-center justify-center">
            <i class="fas fa-store text-blue-600 text-xl"></i>
        </div>
    </div>

    <!-- Kecamatan -->
    <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Kecamatan Tercover</p>
            <h3 class="text-2xl font-bold text-gray-900"><?= number_format($stats['total_kecamatan']) ?></h3>
        </div>
        <div class="w-12 h-12 bg-green-50 rounded-lg flex items-center justify-center">
            <i class="fas fa-map-marked-alt text-green-600 text-xl"></i>
        </div>
    </div>

    <!-- Desa -->
    <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Desa Tercover</p>
            <h3 class="text-2xl font-bold text-gray-900"><?= number_format($stats['total_desa']) ?></h3>
        </div>
        <div class="w-12 h-12 bg-purple-50 rounded-lg flex items-center justify-center">
            <i class="fas fa-city text-purple-600 text-xl"></i>
        </div>
    </div>

    <!-- SLS -->
    <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs font-semibold text-gray-500 uppercase mb-1">SLS Tercover</p>
            <h3 class="text-2xl font-bold text-gray-900"><?= number_format($stats['total_sls']) ?></h3>
        </div>
        <div class="w-12 h-12 bg-orange-50 rounded-lg flex items-center justify-center">
            <i class="fas fa-map-pin text-orange-600 text-xl"></i>
        </div>
    </div>
</div>

<!-- Filter & Search Bar -->
<div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm mb-6">
    <form action="<?= base_url('adminsurvei-kab/usaha-sbr') ?>" method="get" class="flex flex-wrap items-center gap-4">
        <!-- Pencarian -->
        <div class="flex-1 min-w-[250px]">
            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Pencarian</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text" name="search" value="<?= esc($filters['search'] ?? '') ?>" placeholder="Cari nama usaha atau pemilik..." class="w-full pl-10 pr-4 py-2 border-gray-200 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm transition-all">
            </div>
        </div>

        <!-- Filter Wilayah -->
        <div class="w-full lg:w-auto flex flex-wrap gap-4">
            <div class="w-40">
                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Kecamatan</label>
                <select name="kecamatan" id="kecamatan" class="w-full border-gray-200 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua</option>
                    <?php foreach ($kecamatanList as $k) : ?>
                        <option value="<?= $k['id_kecamatan'] ?>" <?= ($filters['id_kecamatan'] ?? '') == $k['id_kecamatan'] ? 'selected' : '' ?>>
                            <?= esc($k['nama_kecamatan']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="w-40">
                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Desa</label>
                <select name="desa" id="desa" class="w-full border-gray-200 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500" <?= empty($filters['id_kecamatan']) ? 'disabled' : '' ?>>
                    <option value="">Semua</option>
                    <?php foreach ($desaList as $d) : ?>
                        <option value="<?= $d['id_desa'] ?>" <?= ($filters['id_desa'] ?? '') == $d['id_desa'] ? 'selected' : '' ?>>
                            <?= esc($d['nama_desa']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Buttons -->
        <div class="flex items-end gap-2 ml-auto">
            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-sm transition-all flex items-center">
                Filter
            </button>
            <a href="<?= base_url('adminsurvei-kab/usaha-sbr') ?>" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm font-semibold rounded-lg transition-all flex items-center">
                Reset
            </a>
            <div class="border-l border-gray-200 h-8 mx-2"></div>
            <button type="button" onclick="openImportModal()" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg shadow-sm transition-all flex items-center">
                <i class="fas fa-file-import mr-2"></i> Import Excel
            </button>
        </div>
    </form>
</div>

<!-- Table Container -->
<div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200 text-gray-600 font-semibold uppercase text-xs">
                    <th class="px-6 py-4 text-left tracking-wider w-16">No</th>
                    <th class="px-6 py-4 text-left tracking-wider">Wilayah (Kec/Des/SLS)</th>
                    <th class="px-6 py-4 text-left tracking-wider">Identitas Usaha</th>
                    <th class="px-6 py-4 text-left tracking-wider">Jenis Usaha</th>
                    <th class="px-6 py-4 text-left tracking-wider">Pemilik</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (empty($usahaList)) : ?>
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center text-gray-400">
                                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                                    <i class="fas fa-store-slash text-2xl"></i>
                                </div>
                                <p class="text-base font-medium">Belum ada data usaha SBR</p>
                                <p class="text-sm">Silakan lakukan import data atau ubah filter pencarian.</p>
                            </div>
                        </td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($usahaList as $i => $row) : ?>
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 text-gray-500 font-medium"><?= $i + 1 ?></td>
                            <td class="px-6 py-4">
                                <div class="text-gray-900 font-semibold mb-1"><?= esc($row['nama_kecamatan'] ?? '-') ?></div>
                                <div class="flex flex-col gap-0.5 text-xs">
                                    <span class="text-gray-500">Desa: <span class="text-gray-700"><?= esc($row['nama_desa'] ?? '-') ?></span></span>
                                    <span class="text-gray-500">SLS: <span class="text-gray-700"><?= esc($row['nama_sls'] ?? '-') ?></span></span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-blue-600 font-bold text-base mb-1"><?= esc($row['nama_usaha']) ?></div>
                                <div class="text-xs text-gray-500 italic max-w-xs truncate" title="<?= esc($row['alamat_usaha'] ?? '-') ?>">
                                    <i class="fas fa-map-marker-alt mr-1"></i> <?= esc($row['alamat_usaha'] ?? '-') ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-md text-xs font-semibold bg-gray-100 text-gray-700 border border-gray-200">
                                    <?= esc($row['jenis_usaha'] ?? 'N/A') ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center mr-3 text-blue-600">
                                        <i class="fas fa-user text-xs"></i>
                                    </div>
                                    <div class="text-gray-900 font-medium">
                                        <?= esc($row['nama_pemilik'] ?? '-') ?>
                                    </div>
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
<div id="importModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75" onclick="closeImportModal()"></div>
        </div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas fa-file-excel text-blue-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Import Data Usaha SBR
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500 mb-4">
                                Pastikan file Excel Anda menggunakan format yang sesuai.
                            </p>
                            
                            <a href="<?= base_url('adminsurvei-kab/usaha-sbr/download-template') ?>" class="text-blue-600 hover:text-blue-800 text-sm font-medium inline-flex items-center mb-4">
                                <i class="fas fa-download mr-1 text-xs"></i> Download Template Excel
                            </a>

                            <div class="mt-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Pilih File Excel</label>
                                <input type="file" id="importFile" accept=".xlsx, .xls" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-gray-300 rounded-md">
                            </div>

                            <!-- Progress Bar -->
                            <div id="importProgress" class="hidden mt-4">
                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                    <div id="progressBar" class="bg-blue-600 h-2.5 rounded-full" style="width: 0%"></div>
                                </div>
                                <p id="progressText" class="text-xs text-gray-500 mt-1">Mengupload...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" id="btnDoImport" onclick="doImport()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm transition-all disabled:bg-gray-400">
                    Mulai Import
                </button>
                <button type="button" onclick="closeImportModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Batal
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function openImportModal() {
    document.getElementById('importModal').classList.remove('hidden');
}

function closeImportModal() {
    document.getElementById('importModal').classList.add('hidden');
}

function doImport() {
    const fileInput = document.getElementById('importFile');
    const file = fileInput.files[0];
    
    if (!file) {
        alert('Pilih file terlebih dahulu');
        return;
    }

    const formData = new FormData();
    formData.append('file', file);
    formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

    const btn = document.getElementById('btnDoImport');
    const progressDiv = document.getElementById('importProgress');
    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Memproses...';
    progressDiv.classList.remove('hidden');

    const xhr = new XMLHttpRequest();
    xhr.open('POST', '<?= base_url('adminsurvei-kab/usaha-sbr/import') ?>', true);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

    xhr.upload.onprogress = function(e) {
        if (e.lengthComputable) {
            const percent = (e.loaded / e.total) * 100;
            progressBar.style.width = percent + '%';
            progressText.innerText = 'Mengupload: ' + Math.round(percent) + '%';
        }
    };

    xhr.onload = function() {
        btn.disabled = false;
        btn.innerHTML = 'Mulai Import';
        
        try {
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
                alert(response.message);
                location.reload();
            } else {
                alert('Gagal: ' + response.message);
            }
        } catch (e) {
            console.error('Parse error:', xhr.responseText);
            alert('Terjadi kesalahan pada server');
        }
    };

    xhr.onerror = function() {
        btn.disabled = false;
        btn.innerHTML = 'Mulai Import';
        alert('Kesalahan jaringan');
    };

    xhr.send(formData);
}

document.addEventListener('DOMContentLoaded', function() {
    const kecamatanSelect = document.getElementById('kecamatan');
    const desaSelect = document.getElementById('desa');
    const slsSelect = document.getElementById('sls');

    kecamatanSelect.addEventListener('change', function() {
        const idKecamatan = this.value;
        
        desaSelect.innerHTML = '<option value="">Semua Desa</option>';
        slsSelect.innerHTML = '<option value="">Semua SLS</option>';
        slsSelect.disabled = true;

        if (idKecamatan) {
            desaSelect.disabled = false;
            fetch(`<?= base_url('adminsurvei-kab/usaha-sbr/get-desa/') ?>${idKecamatan}`)
                .then(response => response.json())
                .then(data => {
                    data.forEach(desa => {
                        const option = document.createElement('option');
                        option.value = desa.id_desa;
                        option.textContent = desa.nama_desa;
                        desaSelect.appendChild(option);
                    });
                })
                .catch(error => console.error('Error fetching desa:', error));
        } else {
            desaSelect.disabled = true;
        }
    });

    desaSelect.addEventListener('change', function() {
        const idDesa = this.value;
        
        slsSelect.innerHTML = '<option value="">Semua SLS</option>';

        if (idDesa) {
            slsSelect.disabled = false;
            fetch(`<?= base_url('adminsurvei-kab/usaha-sbr/get-sls/') ?>${idDesa}`)
                .then(response => response.json())
                .then(data => {
                    data.forEach(sls => {
                        const option = document.createElement('option');
                        option.value = sls.id_sls;
                        option.textContent = sls.nama_sls;
                        slsSelect.appendChild(option);
                    });
                })
                .catch(error => console.error('Error fetching sls:', error));
        } else {
            slsSelect.disabled = true;
        }
    });
});
</script>

<?= $this->endSection() ?>
