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
<div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm mb-6">
    <form action="<?= base_url('pemantau-kabupaten/usaha-sbr') ?>" method="get" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <!-- Pencarian -->
            <div class="lg:col-span-2">
                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Pencarian</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" name="search" value="<?= esc($filters['search'] ?? '') ?>" placeholder="Cari nama usaha atau pemilik..." class="w-full pl-10 pr-4 py-2 border-gray-200 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm transition-all">
                </div>
            </div>

            <!-- Kabupaten -->
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Kabupaten</label>
                <select name="kabupaten" id="filter_kabupaten" class="w-full border-gray-200 rounded-lg py-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition-all">
                    <option value="">Semua Kabupaten</option>
                    <?php foreach ($kabupatenList as $k) : ?>
                        <option value="<?= $k['id_kabupaten'] ?>" <?= ($filters['id_kabupaten'] ?? '') == $k['id_kabupaten'] ? 'selected' : '' ?>>
                            <?= esc($k['nama_kabupaten']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Kecamatan -->
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Kecamatan</label>
                <select name="kecamatan" id="filter_kecamatan" class="w-full border-gray-200 rounded-lg py-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition-all">
                    <option value="">Semua Kecamatan</option>
                    <?php foreach ($kecamatanList as $kec) : ?>
                        <option value="<?= $kec['id_kecamatan'] ?>" <?= ($filters['id_kecamatan'] ?? '') == $kec['id_kecamatan'] ? 'selected' : '' ?>>
                            <?= esc($kec['nama_kecamatan']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Desa -->
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Desa</label>
                <select name="desa" id="filter_desa" class="w-full border-gray-200 rounded-lg py-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition-all">
                    <option value="">Semua Desa</option>
                    <?php foreach ($desaList as $desa) : ?>
                        <option value="<?= $desa['id_desa'] ?>" <?= ($filters['id_desa'] ?? '') == $desa['id_desa'] ? 'selected' : '' ?>>
                            <?= esc($desa['nama_desa']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-4 pt-2 border-t border-gray-100">
            <!-- SLS (pindah baris agar lega) -->
            <div class="w-full md:w-72">
                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">SLS</label>
                <select name="sls" id="filter_sls" class="w-full border-gray-200 rounded-lg py-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition-all">
                    <option value="">Semua SLS</option>
                    <?php foreach ($slsList as $sls) : ?>
                        <option value="<?= $sls['id_sls'] ?>" <?= ($filters['id_sls'] ?? '') == $sls['id_sls'] ? 'selected' : '' ?>>
                            <?= esc($sls['nama_sls']) ?> (<?= substr($sls['id_sls'], -4) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Buttons -->
            <div class="flex items-center gap-2">
                <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-sm transition-all flex items-center">
                    <i class="fas fa-filter mr-2"></i> Filter
                </button>
                <a href="<?= base_url('pemantau-kabupaten/usaha-sbr') ?>" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm font-semibold rounded-lg transition-all flex items-center">
                    <i class="fas fa-undo mr-2"></i> Reset
                </a>
            </div>
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
                    <th class="px-6 py-4 text-left tracking-wider">Nama Usaha</th>
                    <th class="px-6 py-4 text-left tracking-wider">Alamat</th>
                    <th class="px-6 py-4 text-left tracking-wider">Kabupaten</th>
                    <th class="px-6 py-4 text-left tracking-wider">Kecamatan</th>
                    <th class="px-6 py-4 text-left tracking-wider">Desa</th>
                    <th class="px-6 py-4 text-left tracking-wider">SLS</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (empty($usahaList)) : ?>
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
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
                            <td class="px-6 py-4 text-gray-500 font-medium"><?= $start_number + $i + 1 ?></td>
                            <td class="px-6 py-4 font-semibold text-gray-900"><?= esc($row['nama_usaha']) ?></td>
                            <td class="px-6 py-4 text-gray-600"><?= esc($row['alamat_usaha'] ?? '-') ?></td>
                            <td class="px-6 py-4 text-gray-600"><?= esc($row['kabupaten'] ?? '-') ?></td>
                            <td class="px-6 py-4 text-gray-600"><?= esc($row['kecamatan'] ?? '-') ?></td>
                            <td class="px-6 py-4 text-gray-600"><?= esc($row['desa'] ?? '-') ?></td>
                            <td class="px-6 py-4 text-gray-600 font-medium"><?= esc($row['sls'] ?? '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Footer dengan Pagination -->
    <div class="p-4 border-t border-gray-200 flex flex-col sm:flex-row items-center justify-between gap-4">
        <p class="text-sm text-gray-600">
            Menampilkan data
            <span class="font-medium"><?= (($pager->getCurrentPage('usaha_sbr') - 1) * $pager->getPerPage('usaha_sbr')) + 1 ?></span>-<span class="font-medium"><?= min($pager->getCurrentPage('usaha_sbr') * $pager->getPerPage('usaha_sbr'), $pager->getTotal('usaha_sbr')) ?></span>
            dari <span class="font-medium"><?= $pager->getTotal('usaha_sbr') ?></span> data
        </p>

        <!-- Custom Pagination -->
        <?php if ($pager->getPageCount('usaha_sbr') > 1): ?>
            <?= $pager->links('usaha_sbr', 'tailwind_pager') ?>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const kabupatenSelect = document.getElementById('filter_kabupaten');
    const kecamatanSelect = document.getElementById('filter_kecamatan');
    const desaSelect = document.getElementById('filter_desa');
    const slsSelect = document.getElementById('filter_sls');

    kabupatenSelect.addEventListener('change', function() {
        const idKab = this.value;
        kecamatanSelect.innerHTML = '<option value="">Semua Kecamatan</option>';
        desaSelect.innerHTML = '<option value="">Semua Desa</option>';
        slsSelect.innerHTML = '<option value="">Semua SLS</option>';

        if (idKab) {
            fetch(`<?= base_url('pemantau-kabupaten/usaha-sbr/get-kecamatan') ?>/${idKab}`)
                .then(res => res.json())
                .then(data => {
                    data.forEach(kec => {
                        const opt = document.createElement('option');
                        opt.value = kec.id_kecamatan;
                        opt.textContent = kec.nama_kecamatan;
                        kecamatanSelect.appendChild(opt);
                    });
                });
        }
    });

    kecamatanSelect.addEventListener('change', function() {
        const idKec = this.value;
        desaSelect.innerHTML = '<option value="">Semua Desa</option>';
        slsSelect.innerHTML = '<option value="">Semua SLS</option>';

        if (idKec) {
            fetch(`<?= base_url('pemantau-kabupaten/usaha-sbr/get-desa') ?>/${idKec}`)
                .then(res => res.json())
                .then(data => {
                    data.forEach(desa => {
                        const opt = document.createElement('option');
                        opt.value = desa.id_desa;
                        opt.textContent = desa.nama_desa;
                        desaSelect.appendChild(opt);
                    });
                });
        }
    });

    desaSelect.addEventListener('change', function() {
        const idDesa = this.value;
        slsSelect.innerHTML = '<option value="">Semua SLS</option>';

        if (idDesa) {
            fetch(`<?= base_url('pemantau-kabupaten/usaha-sbr/get-sls') ?>/${idDesa}`)
                .then(res => res.json())
                .then(data => {
                    data.forEach(sls => {
                        const opt = document.createElement('option');
                        opt.value = sls.id_sls;
                        opt.textContent = `${sls.nama_sls} (${sls.id_sls.slice(-4)})`;
                        slsSelect.appendChild(opt);
                    });
                });
        }
    });
});
</script>

<?= $this->endSection() ?>
