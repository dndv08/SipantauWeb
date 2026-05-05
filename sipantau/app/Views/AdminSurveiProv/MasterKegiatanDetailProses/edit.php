<?= $this->extend('layouts/adminprov_layout') ?>
<?= $this->section('content') ?>

<!-- Page Header -->
<div class="mb-6">
    <div class="flex items-center text-sm text-gray-600 mb-4">
        <a href="<?= base_url('adminsurvei/master-kegiatan-detail-proses') ?>"
            class="hover:text-blue-600 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Kembali ke Master Kegiatan Detail Proses
        </a>
    </div>
    <h1 class="text-2xl font-bold text-gray-900">Edit Master Kegiatan Detail Proses</h1>
    <p class="text-gray-600 mt-1">Ubah data master kegiatan detail proses</p>
</div>

<!-- Form Card -->
<div class="card max-w-5xl">
    <form id="formMasterKegiatan" method="POST"
        action="<?= base_url('adminsurvei/master-kegiatan-detail-proses/update/' . $detailProses['id_kegiatan_detail_proses']) ?>">
        <?= csrf_field() ?>

        <!-- Pilih Kegiatan Detail -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Kegiatan Detail<span
                    class="text-red-500">*</span></label>
            <select name="kegiatan_detail" class="input-field" required>
                <option value="">-- Pilih Kegiatan Detail --</option>
                <?php foreach ($kegiatanDetailList as $item): ?>
                    <option value="<?= esc($item['id_kegiatan_detail']) ?>"
                        <?= $detailProses['id_kegiatan_detail'] == $item['id_kegiatan_detail'] ? 'selected' : '' ?>>
                        <?= esc($item['nama_kegiatan_detail']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Nama Kegiatan Detail Proses -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Kegiatan Detail Proses <span
                    class="text-red-500">*</span></label>
            <input type="text" name="nama_proses" value="<?= esc($detailProses['nama_kegiatan_detail_proses']) ?>"
                class="input-field" required>
        </div>

        <!-- Tanggal -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai<span
                        class="text-red-500">*</span></label>
                <input type="date" id="tanggal_mulai" name="tanggal_mulai"
                    value="<?= esc($detailProses['tanggal_mulai']) ?>" class="input-field" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Selesai<span
                        class="text-red-500">*</span></label>
                <input type="date" id="tanggal_selesai" name="tanggal_selesai"
                    value="<?= esc($detailProses['tanggal_selesai']) ?>" class="input-field" required>
            </div>
        </div>

        <!-- Satuan -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Satuan<span
                    class="text-red-500">*</span></label>
            <input type="text" name="satuan" value="<?= esc($detailProses['satuan']) ?>" class="input-field" required>
        </div>

        <!-- Keterangan -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan<span
                    class="text-red-500">*</span></label>
            <textarea name="keterangan" rows="3" class="input-field resize-none"
                required><?= esc($detailProses['keterangan']) ?></textarea>
        </div>

        <!-- Periode -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Periode<span
                    class="text-red-500">*</span></label>
            <input type="text" name="periode" value="<?= esc($detailProses['periode']) ?>" class="input-field" required>
        </div>

        <!-- Target -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Target<span
                    class="text-red-500">*</span></label>
            <input type="number" name="target" value="<?= esc($detailProses['target']) ?>" class="input-field" min="0"
                required>
        </div>

        <!-- Target Hari Pertama & Selesai -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Target Hari Pertama (Dalam Persen %)<span
                        class="text-red-500">*</span></label>
                <input type="number" name="persentase_target_awal" id="persentase_target_awal"
                    value="<?= esc($detailProses['persentase_target_awal']) ?>" class="input-field" min="0" max="100" step="0.01" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Target Tanggal Selesai (100%)<span
                        class="text-red-500">*</span></label>
                <input type="date" id="tanggal_selesai_target" name="tanggal_selesai_target"
                    value="<?= esc($detailProses['tanggal_selesai_target']) ?>" class="input-field" required>
            </div>
        </div>

        <div class="flex justify-center mt-8">
            <button type="submit" class="btn-primary px-24 py-3 text-base">
                <i class="fas fa-save mr-2"></i>Perbarui Data
            </button>
        </div>
    </form>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.getElementById('formMasterKegiatan').addEventListener('submit', function (e) {
        e.preventDefault();

        const form = this; 
        const tanggalMulai = new Date(document.getElementById('tanggal_mulai').value);
        const tanggalSelesai = new Date(document.getElementById('tanggal_selesai').value);
        const tanggal100 = new Date(document.getElementById('tanggal_selesai_target').value);

        // Validasi tanggal selesai
        if (tanggalSelesai < tanggalMulai) {
            Swal.fire({
                icon: 'error',
                title: 'Tanggal Tidak Valid',
                text: 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai!',
                confirmButtonColor: '#3b82f6',
            });
            return;
        }

        // Validasi tanggal target 100%
        if (tanggal100 < tanggalMulai) {
            Swal.fire({
                icon: 'error',
                title: 'Target 100% Tidak Valid',
                text: 'Tanggal target 100% tidak boleh lebih awal dari tanggal mulai!',
                confirmButtonColor: '#3b82f6',
            });
            return;
        }

        if (tanggal100 > tanggalSelesai) {
            Swal.fire({
                icon: 'error',
                title: 'Target 100% Tidak Valid',
                text: 'Tanggal target 100% tidak boleh melebihi tanggal selesai!',
                confirmButtonColor: '#3b82f6',
            });
            return;
        }

        // Konfirmasi sebelum submit
        Swal.fire({
            title: 'Konfirmasi Perubahan',
            text: 'Apakah Anda yakin data yang diubah sudah benar?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Simpan Perubahan',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#3b82f6',
            cancelButtonColor: '#6b7280',
        }).then((result) => {
            if (result.isConfirmed) {
                // Tampilkan loading
                Swal.fire({
                    title: 'Menyimpan...',
                    text: 'Mohon tunggu sebentar',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                form.submit(); 
            }
        });
    });

    // Notifikasi sukses/error dari session
    <?php if (session()->getFlashdata('success')): ?>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '<?= session()->getFlashdata('success') ?>',
            confirmButtonColor: '#3b82f6',
        });
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: '<?= session()->getFlashdata('error') ?>',
            confirmButtonColor: '#3b82f6',
        });
    <?php endif; ?>

    <?php if (session()->getFlashdata('errors')): ?>
        let errorList = '<ul class="text-left">';
        <?php foreach (session()->getFlashdata('errors') as $error): ?>
            errorList += '<li><?= $error ?></li>';
        <?php endforeach; ?>
        errorList += '</ul>';
        
        Swal.fire({
            icon: 'error',
            title: 'Validasi Gagal!',
            html: errorList,
            confirmButtonColor: '#3b82f6',
        });
    <?php endif; ?>
</script>

<?= $this->endSection() ?>