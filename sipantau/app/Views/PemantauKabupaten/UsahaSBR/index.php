<?= $this->extend('layouts/pemantau_kabupaten_layout') ?>

<?= $this->section('content') ?>

<!-- Header & Back Button -->
<div class="mb-6">
    <a href="<?= base_url('pemantau-kabupaten') ?>" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-4 transition-colors">
        <i class="fas fa-arrow-left mr-2"></i> Back
    </a>
    <h1 class="text-2xl font-bold text-gray-900">Data Usaha SBR</h1>
    <p class="text-sm text-gray-600">Daftar usaha SBR beserta identitas wilayahnya untuk <?= esc($nama_kabupaten) ?></p>
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
    <form action="<?= base_url('pemantau-kabupaten/usaha-sbr') ?>" method="get" class="flex flex-wrap items-center gap-4">
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
            <a href="<?= base_url('pemantau-kabupaten/usaha-sbr') ?>" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm font-semibold rounded-lg transition-all flex items-center">
                Reset
            </a>
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
                                <p class="text-sm">Silakan ubah filter pencarian.</p>
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
<script>
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
            fetch(`<?= base_url('pemantau-kabupaten/usaha-sbr/get-desa/') ?>${idKecamatan}`)
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
            fetch(`<?= base_url('pemantau-kabupaten/usaha-sbr/get-sls/') ?>${idDesa}`)
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
