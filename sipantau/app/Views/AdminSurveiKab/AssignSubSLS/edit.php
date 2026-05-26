<?= $this->extend('layouts/adminkab_layout') ?>

<?= $this->section('content') ?>
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Edit Assignment Sub-SLS</h1>
        <p class="text-sm text-gray-600">Ubah penugasan petugas untuk wilayah kerja tertentu</p>
    </div>
    <a href="<?= base_url('adminsurvei-kab/assign-sub-sls') ?>" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm font-semibold rounded transition-colors">
        <i class="fas fa-arrow-left mr-2"></i> Kembali
    </a>
</div>

<div class="max-w-3xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <h2 class="font-bold text-gray-900">Form Perubahan Assignment</h2>
        </div>
        <form action="<?= base_url('adminsurvei-kab/assign-sub-sls/update/' . $assignment['id_assignment_sub_sls']) ?>" method="post" class="p-6">
            <?= csrf_field() ?>

            <div class="space-y-6">
                <!-- Kegiatan -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Kegiatan Survei</label>
                    <select name="id_kegiatan_wilayah" id="id_kegiatan_wilayah" required class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        <option value="">-- Pilih Kegiatan --</option>
                        <?php foreach ($kegiatanList as $k) : ?>
                            <option value="<?= $k['id_kegiatan_wilayah'] ?>" <?= $assignment['id_kegiatan_wilayah'] == $k['id_kegiatan_wilayah'] ? 'selected' : '' ?>>
                                <?= esc($k['nama_kegiatan_detail']) ?> - <?= esc($k['nama_kegiatan_detail_proses']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Kecamatan -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Kecamatan</label>
                        <select id="id_kecamatan" required class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            <option value="">-- Pilih Kecamatan --</option>
                            <?php foreach ($kecamatanList as $kec) : ?>
                                <option value="<?= $kec['id_kecamatan'] ?>" <?= ($currentDesa['id_kecamatan'] ?? '') == $kec['id_kecamatan'] ? 'selected' : '' ?>><?= esc($kec['nama_kecamatan']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Desa -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Desa/Kelurahan</label>
                        <select id="id_desa" required class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            <option value="">-- Pilih Desa --</option>
                            <option value="<?= $currentDesa['id_desa'] ?? '' ?>" selected><?= esc($currentDesa['nama_desa'] ?? '') ?></option>
                        </select>
                    </div>

                    <!-- SLS -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">SLS</label>
                        <select id="id_sls" required class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            <option value="">-- Pilih SLS --</option>
                            <option value="<?= $currentSls['id_sls'] ?? '' ?>" selected><?= esc($currentSls['nama_sls'] ?? '') ?></option>
                        </select>
                    </div>

                    <!-- Sub-SLS -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Sub-SLS</label>
                        <select name="id_sub_sls" id="id_sub_sls" required class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            <option value="">-- Pilih Sub-SLS --</option>
                            <option value="<?= $currentSubSls['id_sub_sls'] ?? '' ?>" selected><?= esc(isset($currentSubSls['id_sub_sls']) ? substr($currentSubSls['id_sub_sls'], -2) : '') ?> - <?= esc($currentSls['nama_sls'] ?? '') ?></option>
                        </select>
                    </div>
                </div>

                <!-- Petugas -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Petugas (SOBAT)</label>
                    <select name="sobat_id" id="sobat_id" required class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        <option value="">-- Pilih Petugas --</option>
                        <option value="<?= $currentPetugas['sobat_id'] ?>" selected><?= esc($currentPetugas['nama_user']) ?> (<?= esc($currentPetugas['sobat_id']) ?>)</option>
                    </select>
                </div>

                <div class="pt-4 border-t border-gray-200 flex justify-end gap-3">
                    <a href="<?= base_url('adminsurvei-kab/assign-sub-sls') ?>" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm font-semibold rounded transition-colors">Batal</a>
                    <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded shadow-sm transition-colors">Simpan Perubahan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('id_kecamatan').addEventListener('change', function() {
    const id = this.value;
    const desaSelect = document.getElementById('id_desa');
    desaSelect.innerHTML = '<option value="">-- Pilih Desa --</option>';
    
    if (!id) return;
    
    fetch('<?= base_url('adminsurvei-kab/assign-sub-sls/get-desa/') ?>' + id)
        .then(res => res.json())
        .then(data => {
            data.forEach(d => {
                desaSelect.innerHTML += `<option value="${d.id_desa}">${d.nama_desa}</option>`;
            });
        });
});

document.getElementById('id_desa').addEventListener('change', function() {
    const id = this.value;
    const slsSelect = document.getElementById('id_sls');
    slsSelect.innerHTML = '<option value="">-- Pilih SLS --</option>';
    
    if (!id) return;
    
    fetch('<?= base_url('adminsurvei-kab/assign-sub-sls/get-sls/') ?>' + id)
        .then(res => res.json())
        .then(data => {
            data.forEach(s => {
                slsSelect.innerHTML += `<option value="${s.id_sls}">${s.nama_sls}</option>`;
            });
        });
});

document.getElementById('id_sls').addEventListener('change', function() {
    const id = this.value;
    const subSlsSelect = document.getElementById('id_sub_sls');
    subSlsSelect.innerHTML = '<option value="">-- Pilih Sub-SLS --</option>';
    
    if (!id) return;
    
    fetch('<?= base_url('adminsurvei-kab/assign-sub-sls/get-sub-sls/') ?>' + id)
        .then(res => res.json())
        .then(data => {
            data.forEach(ss => {
                subSlsSelect.innerHTML += `<option value="${ss.id_sub_sls}">${ss.display_name}</option>`;
            });
        });
});

// Load available petugas when kegiatan is changed (if needed) or on load
function loadPetugas() {
    const idKegiatan = document.getElementById('id_kegiatan_wilayah').value;
    const petugasSelect = document.getElementById('sobat_id');
    const currentPetugasId = '<?= $currentPetugas['sobat_id'] ?>';

    petugasSelect.innerHTML = '<option value="">-- Pilih Petugas --</option>';
    
    fetch('<?= base_url('adminsurvei-kab/assign-sub-sls/get-available-petugas') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id_kabupaten=<?= $admin['id_kabupaten'] ?>&id_kegiatan_wilayah=${idKegiatan}&<?= csrf_token() ?>=<?= csrf_hash() ?>`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            data.data.forEach(p => {
                petugasSelect.innerHTML += `<option value="${p.sobat_id}" ${p.sobat_id == currentPetugasId ? 'selected' : ''}>${p.nama_user} (${p.sobat_id})</option>`;
            });
        }
    });
}

document.getElementById('id_kegiatan_wilayah').addEventListener('change', loadPetugas);
window.onload = loadPetugas;
</script>
<?= $this->endSection() ?>
