<?= $this->extend('layouts/adminkab_layout') ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="mb-6">
    <div class="flex items-center mb-2">
        <a href="<?= base_url('adminsurvei-kab/assign-sub-sls') ?>" class="text-gray-600 hover:text-gray-900 mr-2">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
    <h1 class="text-2xl font-bold text-gray-900">Tambah Assignment Wilayah</h1>
    <p class="text-sm text-gray-600 mt-1">Lakukan penugasan petugas ke level wilayah (SLS atau Sub-SLS)</p>
</div>

<div class="max-w-4xl">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-8 py-6 bg-gray-50 border-b border-gray-200">
            <h2 class="text-lg font-bold text-gray-900 flex items-center">
                <i class="fas fa-edit text-blue-600 mr-3"></i> Form Penugasan Wilayah
            </h2>
        </div>
        
        <form action="<?= base_url('adminsurvei-kab/assign-sub-sls/store') ?>" method="post" class="p-8 space-y-8">
            <?= csrf_field() ?>
            
            <!-- Section 1: Kegiatan & Level -->
            <div class="space-y-6">
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100 pb-2">Informasi Kegiatan</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Pilih Kegiatan Survei *</label>
                        <select name="id_kegiatan_wilayah" id="id_kegiatan_wilayah" required 
                            class="w-full border-gray-300 rounded-xl py-3 focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all">
                            <option value="">-- Pilih Kegiatan --</option>
                            <?php foreach ($kegiatanList as $k) : ?>
                                <option value="<?= $k['id_kegiatan_wilayah'] ?>" <?= ($selectedKegiatan ?? old('id_kegiatan_wilayah')) == $k['id_kegiatan_wilayah'] ? 'selected' : '' ?>>
                                    <?= esc($k['nama_kegiatan_detail']) ?> - <?= esc($k['nama_kegiatan_detail_proses']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Level Penugasan (REMOVED - Always Sub-SLS) -->
                    <input type="hidden" name="assignment_level" value="sub_sls">
                </div>
            </div>

            <!-- Section 2: Wilayah Kerja -->
            <div class="space-y-6">
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100 pb-2">Lokasi Wilayah Kerja</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Kecamatan *</label>
                        <select id="id_kecamatan" required 
                            class="w-full border-gray-300 rounded-xl py-3 focus:ring-4 focus:ring-blue-100 transition-all">
                            <option value="">-- Pilih Kecamatan --</option>
                            <?php foreach ($kecamatanList as $kec) : ?>
                                <option value="<?= $kec['id_kecamatan'] ?>"><?= esc($kec['nama_kecamatan'] ?? '') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Desa/Kelurahan *</label>
                        <select id="id_desa" required disabled
                            class="w-full border-gray-300 rounded-xl py-3 focus:ring-4 focus:ring-blue-100 disabled:bg-gray-50 disabled:cursor-not-allowed transition-all">
                            <option value="">-- Pilih Desa --</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">SLS *</label>
                        <select name="id_sls" id="id_sls" required disabled
                            class="w-full border-gray-300 rounded-xl py-3 focus:ring-4 focus:ring-blue-100 disabled:bg-gray-50 disabled:cursor-not-allowed transition-all">
                            <option value="">-- Pilih SLS --</option>
                        </select>
                    </div>

                    <div id="sub_sls_container">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Sub-SLS *</label>
                        <select name="id_sub_sls" id="id_sub_sls" required disabled
                            class="w-full border-gray-300 rounded-xl py-3 focus:ring-4 focus:ring-blue-100 disabled:bg-gray-50 disabled:cursor-not-allowed transition-all">
                            <option value="">-- Pilih Sub-SLS --</option>
                        </select>
                        <p class="text-[10px] text-gray-500 mt-2 italic">Wilayah tugas spesifik petugas</p>
                    </div>
                </div>
            </div>

            <!-- Section 3: Petugas -->
            <div class="space-y-6">
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100 pb-2">Penugasan Petugas</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Pilih Petugas (SOBAT) *</label>
                        <select name="sobat_id" id="sobat_id" required disabled
                            class="w-full border-gray-300 rounded-xl py-3 focus:ring-4 focus:ring-blue-100 disabled:bg-gray-50 disabled:cursor-not-allowed transition-all">
                            <option value="">-- Pilih Petugas --</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-8 border-t border-gray-100">
                <a href="<?= base_url('adminsurvei-kab/assign-sub-sls') ?>" 
                    class="px-8 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl transition-all">
                    Batal
                </a>
                <button type="submit" 
                    class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-lg shadow-blue-200 transition-all flex items-center">
                    <i class="fas fa-save mr-2"></i> Simpan Assignment
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const kecSelect = document.getElementById('id_kecamatan');
    const desaSelect = document.getElementById('id_desa');
    const slsSelect = document.getElementById('id_sls');
    const subSlsSelect = document.getElementById('id_sub_sls');
    const kegiatanSelect = document.getElementById('id_kegiatan_wilayah');
    const petugasSelect = document.getElementById('sobat_id');

    // Level Penugasan is now always sub_sls

    // Helper to toggle loading state
    function setLoading(el, loading = true) {
        if (loading) {
            el.innerHTML = '<option value="">Memuat...</option>';
            el.disabled = true;
        }
    }

    kecSelect.addEventListener('change', function() {
        const idKec = this.value;
        setLoading(desaSelect);
        slsSelect.innerHTML = '<option value="">-- Pilih SLS --</option>';
        subSlsSelect.innerHTML = '<option value="">-- Pilih Sub-SLS --</option>';
        slsSelect.disabled = true;
        subSlsSelect.disabled = true;
        
        if (idKec) {
            fetch('<?= base_url('adminsurvei-kab/assign-sub-sls/get-desa/') ?>' + idKec)
                .then(res => res.json())
                .then(data => {
                    desaSelect.innerHTML = '<option value="">-- Pilih Desa --</option>';
                    data.forEach(d => {
                        const opt = document.createElement('option');
                        opt.value = d.id_desa;
                        opt.textContent = d.nama_desa;
                        desaSelect.appendChild(opt);
                    });
                    desaSelect.disabled = false;
                });
        } else {
            desaSelect.innerHTML = '<option value="">-- Pilih Desa --</option>';
        }
    });

    desaSelect.addEventListener('change', function() {
        const idDesa = this.value;
        setLoading(slsSelect);
        subSlsSelect.innerHTML = '<option value="">-- Pilih Sub-SLS --</option>';
        subSlsSelect.disabled = true;
        
        if (idDesa) {
            fetch('<?= base_url('adminsurvei-kab/assign-sub-sls/get-sls/') ?>' + idDesa)
                .then(res => res.json())
                .then(data => {
                    slsSelect.innerHTML = '<option value="">-- Pilih SLS --</option>';
                    data.forEach(d => {
                        const opt = document.createElement('option');
                        opt.value = d.id_sls;
                        opt.textContent = d.nama_sls;
                        slsSelect.appendChild(opt);
                    });
                    slsSelect.disabled = false;
                });
        } else {
            slsSelect.innerHTML = '<option value="">-- Pilih SLS --</option>';
        }
    });

    slsSelect.addEventListener('change', function() {
        const idSLS = this.value;
        setLoading(subSlsSelect);
        
        if (idSLS) {
            fetch('<?= base_url('adminsurvei-kab/assign-sub-sls/get-sub-sls/') ?>' + idSLS)
                .then(res => res.json())
                .then(data => {
                    subSlsSelect.innerHTML = '<option value="">-- Pilih Sub-SLS --</option>';
                    data.forEach(d => {
                        const opt = document.createElement('option');
                        opt.value = d.id_sub_sls;
                        opt.textContent = d.display_name; // Use formatted display_name
                        subSlsSelect.appendChild(opt);
                    });
                    subSlsSelect.disabled = false;
                });
        } else {
            subSlsSelect.innerHTML = '<option value="">-- Pilih Sub-SLS --</option>';
        }
    });

    function loadPetugas() {
        const idKeg = kegiatanSelect.value;
        if (idKeg) {
            setLoading(petugasSelect);
            const formData = new FormData();
            formData.append('id_kabupaten', '<?= $admin['id_kabupaten'] ?>');
            formData.append('id_kegiatan_wilayah', idKeg);
            formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

            fetch('<?= base_url('adminsurvei-kab/assign-sub-sls/get-available-petugas') ?>', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                petugasSelect.innerHTML = '<option value="">-- Pilih Petugas --</option>';
                if (data.success) {
                    data.data.forEach(u => {
                        const opt = document.createElement('option');
                        opt.value = u.sobat_id;
                        opt.textContent = u.nama_user + ' (' + u.sobat_id + ')';
                        petugasSelect.appendChild(opt);
                    });
                    petugasSelect.disabled = false;
                }
            });
        } else {
            petugasSelect.innerHTML = '<option value="">-- Pilih Petugas --</option>';
            petugasSelect.disabled = true;
        }
    }

    kegiatanSelect.addEventListener('change', loadPetugas);
    if (kegiatanSelect.value) loadPetugas();
});
</script>

<?= $this->endSection() ?>
