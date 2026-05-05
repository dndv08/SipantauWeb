<?= $this->extend('layouts/adminkab_layout') ?>

<?= $this->section('content') ?>
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Detail Assignment Sub-SLS</h1>
        <p class="text-sm text-gray-600">Informasi lengkap penugasan petugas ke level Sub-SLS</p>
    </div>
    <a href="<?= base_url('adminsurvei-kab/assign-sub-sls') ?>" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm font-semibold rounded transition-colors">
        <i class="fas fa-arrow-left mr-2"></i> Kembali
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left Column: Info Petugas & Kegiatan -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Info Kegiatan -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex items-center">
                <i class="fas fa-tasks text-blue-600 mr-3"></i>
                <h2 class="font-bold text-gray-900">Informasi Kegiatan</h2>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Nama Survei</label>
                    <p class="text-sm font-bold text-gray-900"><?= esc($assignment['nama_kegiatan_detail']) ?></p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Proses</label>
                    <p class="text-sm font-bold text-gray-900"><?= esc($assignment['nama_kegiatan_detail_proses']) ?></p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Target Wilayah</label>
                    <p class="text-sm font-bold text-gray-900"><?= number_format($assignment['target_wilayah']) ?> unit</p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">ID Kegiatan Wilayah</label>
                    <p class="text-sm text-gray-600 font-mono">#<?= esc($assignment['id_kegiatan_wilayah']) ?></p>
                </div>
            </div>
        </div>

        <!-- Info Wilayah -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex items-center">
                <i class="fas fa-map-marker-alt text-red-600 mr-3"></i>
                <h2 class="font-bold text-gray-900">Informasi Wilayah Kerja</h2>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Kecamatan</label>
                    <p class="text-sm font-bold text-gray-900"><?= esc($assignment['nama_kecamatan']) ?></p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Desa/Kelurahan</label>
                    <p class="text-sm font-bold text-gray-900"><?= esc($assignment['real_nama_desa']) ?></p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">SLS</label>
                    <p class="text-sm font-bold text-gray-900"><?= esc($assignment['nama_sls']) ?></p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">ID Sub-SLS</label>
                    <p class="text-sm font-bold text-blue-600 font-mono"><?= esc($assignment['id_sub_sls']) ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Petugas -->
    <div class="space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex items-center">
                <i class="fas fa-user-tie text-purple-600 mr-3"></i>
                <h2 class="font-bold text-gray-900">Petugas Terpilih</h2>
            </div>
            <div class="p-6 flex flex-col items-center text-center">
                <div class="w-24 h-24 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-full flex items-center justify-center text-white text-3xl font-bold mb-4 shadow-lg">
                    <?= strtoupper(substr($assignment['nama_petugas'], 0, 1)) ?>
                </div>
                <h3 class="text-lg font-bold text-gray-900"><?= esc($assignment['nama_petugas']) ?></h3>
                <p class="text-sm text-gray-500 mb-4"><?= esc($assignment['sobat_id']) ?></p>
                
                <div class="w-full space-y-3 text-left bg-gray-50 p-4 rounded-lg border border-gray-100">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Email</label>
                        <p class="text-sm text-gray-700"><?= esc($assignment['email'] ?: '-') ?></p>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">No. HP</label>
                        <p class="text-sm text-gray-700"><?= esc($assignment['hp'] ?: '-') ?></p>
                    </div>
                </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex gap-2">
                <a href="<?= base_url('adminsurvei-kab/assign-sub-sls/edit/' . $assignment['id_assignment_sub_sls']) ?>" class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white text-xs font-semibold rounded transition-colors shadow-sm">
                    <i class="fas fa-edit mr-1"></i> Edit
                </a>
                <button onclick="confirmDelete(<?= $assignment['id_assignment_sub_sls'] ?>)" class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded transition-colors shadow-sm">
                    <i class="fas fa-trash-alt mr-1"></i> Hapus
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmDelete(id) {
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Data assignment akan dihapus permanen!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, hapus!'
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
