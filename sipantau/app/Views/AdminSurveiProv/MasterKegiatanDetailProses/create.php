<?= $this->extend('layouts/adminprov_layout') ?>
<?= $this->section('content') ?>

<!-- Page Header -->
<div class="mb-6">
    <div class="flex items-center text-sm text-gray-600 mb-4">
        <?php
        // Build back URL dengan filter jika ada
        $backUrl = base_url('adminsurvei/master-kegiatan-detail-proses');
        if ($kegiatanDetailFilter) {
            $backUrl .= '?kegiatan_detail=' . $kegiatanDetailFilter;
        }
        ?>
        <a href="<?= $backUrl ?>" class="hover:text-blue-600 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Kembali ke Master Kegiatan Detail Proses
        </a>
    </div>
    <h1 class="text-2xl font-bold text-gray-900">Tambah Master Kegiatan Detail Proses</h1>
    <p class="text-gray-600 mt-1">Buat data master kegiatan detail proses baru</p>
</div>

<!-- Form Card -->
<div class="card max-w-5xl">
    <form id="formMasterKegiatan" method="POST" action="<?= base_url('adminsurvei/master-kegiatan-detail-proses/store') ?>">
        <?= csrf_field() ?>
        
        <!-- Info -->
        <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <p class="text-sm text-blue-700">
                Lengkapi semua informasi kegiatan dengan benar. Field bertanda
                <span class="text-red-500 font-semibold">*</span> wajib diisi.
            </p>
        </div>
        
        <!-- Pilih Kegiatan Detail -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Pilih Kegiatan Detail<span class="text-red-500">*</span>
            </label>
            <select name="kegiatan_detail" class="input-field" required>
                <option value="">-- Pilih Kegiatan Detail --</option>
                <?php foreach ($kegiatanDetailList as $item): ?>
                    <option value="<?= esc($item['id_kegiatan_detail']) ?>" 
                            <?= (old('kegiatan_detail') == $item['id_kegiatan_detail'] || $kegiatanDetailFilter == $item['id_kegiatan_detail']) ? 'selected' : '' ?>>
                        <?= esc($item['nama_kegiatan_detail']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Nama Kegiatan Detail Proses -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Nama Kegiatan Detail Proses <span class="text-red-500">*</span>
            </label>
            <input type="text" name="nama_proses" value="<?= old('nama_proses') ?>" class="input-field" required>
        </div>

        <!-- Tanggal -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Tanggal Mulai<span class="text-red-500">*</span>
                </label>
                <input type="date" id="tanggal_mulai" name="tanggal_mulai" value="<?= old('tanggal_mulai') ?>" class="input-field" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Tanggal Selesai<span class="text-red-500">*</span>
                </label>
                <input type="date" id="tanggal_selesai" name="tanggal_selesai" value="<?= old('tanggal_selesai') ?>" class="input-field" required>
            </div>
        </div>

        <!-- Satuan -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Satuan<span class="text-red-500">*</span>
            </label>
            <input type="text" name="satuan" value="<?= old('satuan') ?>" class="input-field" required>
        </div>  

        <!-- Keterangan -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Keterangan<span class="text-red-500">*</span>
            </label>
            <textarea name="keterangan" rows="3" class="input-field resize-none" required><?= old('keterangan') ?></textarea>
        </div>

        <!-- Periode -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Periode<span class="text-red-500">*</span>
            </label>
            <input type="text" name="periode" value="<?= old('periode') ?>" class="input-field" required>
        </div>

        <!-- Target -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Target<span class="text-red-500">*</span>
            </label>
            <input type="number" name="target" value="<?= old('target') ?>" class="input-field" min="0" required>
        </div>

        <!-- Target Hari Pertama & Selesai -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Target Hari Pertama (Dalam Persen %)<span class="text-red-500">*</span>
                </label>
                <input type="number" name="persentase_target_awal" value="<?= old('persentase_target_awal') ?>" class="input-field" min="0" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Target Tanggal Selesai<span class="text-red-500">*</span>
                </label>
                <input type="date" id="tanggal_selesai_target" name="tanggal_selesai_target" value="<?= old('tanggal_selesai_target') ?>" class="input-field" required>
            </div>
        </div>

        <div class="flex justify-center mt-8">
            <button type="submit" class="btn-primary px-24 py-3 text-base">Simpan</button>
        </div>
    </form>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.getElementById('formMasterKegiatan').addEventListener('submit', function(e) {
    e.preventDefault();

    const tanggalMulai = new Date(document.getElementById('tanggal_mulai').value);
    const tanggalSelesai = new Date(document.getElementById('tanggal_selesai').value);
    const tanggal100 = new Date(document.getElementById('tanggal_selesai_target').value);

    // Validasi tanggal selesai tidak boleh < tanggal mulai
    if (tanggalSelesai < tanggalMulai) {
        Swal.fire({
            icon: 'error',
            title: 'Tanggal Tidak Valid',
            text: 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai!',
            confirmButtonColor: '#3b82f6',
        });
        return;
    }

    // Validasi target 100% tidak boleh < tanggal mulai
    if (tanggal100 < tanggalMulai) {
        Swal.fire({
            icon: 'error',
            title: 'Target 100% Tidak Valid',
            text: 'Tanggal target 100% tidak boleh lebih awal dari tanggal mulai!',
            confirmButtonColor: '#3b82f6',
        });
        return;
    }

    // Validasi target 100% tidak boleh > tanggal selesai
    if (tanggal100 > tanggalSelesai) {
        Swal.fire({
            icon: 'error',
            title: 'Target 100% Tidak Valid',
            text: 'Tanggal target 100% tidak boleh melebihi tanggal selesai!',
            confirmButtonColor: '#3b82f6',
        });
        return;
    }

    // Jika semua valid → konfirmasi simpan
    Swal.fire({
        title: 'Konfirmasi Simpan',
        text: 'Apakah data yang diisi sudah benar?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Simpan',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#3b82f6',
        cancelButtonColor: '#6b7280',
    }).then((result) => {
        if (result.isConfirmed) this.submit();
    });
});
</script>

<?= $this->endSection() ?>