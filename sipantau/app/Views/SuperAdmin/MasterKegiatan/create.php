<?= $this->extend('layouts/sadmin_layout') ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="mb-6">
    <div class="flex items-center text-sm text-gray-600 mb-4">
        <a href="<?= base_url('superadmin/master-kegiatan') ?>" class="hover:text-blue-600 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Kembali ke Master Kegiatan
        </a>
    </div>
    <h1 class="text-2xl font-bold text-gray-900">Tambah Master Kegiatan</h1>
    <p class="text-gray-600 mt-1">Buat data master kegiatan survei/sensus baru</p>
</div>

<!-- Form Card -->
<div class="card max-w-3xl">
    <form id="formMasterKegiatan" method="POST" action="<?= base_url('superadmin/master-kegiatan') ?>">
        <?= csrf_field() ?>
        
        <!-- Info Alert -->
        <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-info-circle text-blue-600"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        Lengkapi semua informasi kegiatan dengan benar. Field bertanda <span class="text-red-500 font-semibold">*</span> wajib diisi.
                    </p>
                </div>
            </div>
        </div>

        <!-- Master Output -->
        <div class="mb-6">
            <label for="id_output" class="block text-sm font-medium text-gray-700 mb-2">
                Master Output <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <select id="id_output" 
                        name="id_output" 
                        class="input-field appearance-none pr-10 <?= session('errors.id_output') ? 'border-red-500' : '' ?>" 
                        required>
                    <option value="">-- Pilih Master Output --</option>
                    <?php if (isset($masterOutputs) && !empty($masterOutputs)): ?>
                        <?php foreach ($masterOutputs as $output): ?>
                            <option value="<?= $output['id_output'] ?>" <?= old('id_output') == $output['id_output'] ? 'selected' : '' ?>>
                                <?= esc($output['nama_output']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="" disabled>Belum ada data master output</option>
                    <?php endif; ?>
                </select>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <i class="fas fa-chevron-down text-gray-400 text-sm"></i>
                </div>
            </div>
            <?php if (session('errors.id_output')): ?>
                <p class="mt-1 text-xs text-red-500"><?= session('errors.id_output') ?></p>
            <?php elseif (!isset($masterOutputs) || empty($masterOutputs)): ?>
                <p class="mt-1 text-xs text-red-500">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    Belum ada data master output. <a href="<?= base_url('superadmin/master-output/create') ?>" class="text-blue-600 hover:underline">Tambah Master Output</a> terlebih dahulu.
                </p>
            <?php else: ?>
                <p class="mt-1 text-xs text-gray-500">Pilih master output untuk kegiatan ini</p>
            <?php endif; ?>
        </div>

        <!-- Nama Kegiatan -->
        <div class="mb-6">
            <label for="nama_kegiatan" class="block text-sm font-medium text-gray-700 mb-2">
                Nama Kegiatan <span class="text-red-500">*</span>
            </label>
            <input type="text" 
                   id="nama_kegiatan" 
                   name="nama_kegiatan" 
                   class="input-field <?= session('errors.nama_kegiatan') ? 'border-red-500' : '' ?>" 
                   value="<?= old('nama_kegiatan') ?>"
                   required>
            <?php if (session('errors.nama_kegiatan')): ?>
                <p class="mt-1 text-xs text-red-500"><?= session('errors.nama_kegiatan') ?></p>
            <?php else: ?>
                <p class="mt-1 text-xs text-gray-500">Masukkan nama lengkap kegiatan survei/sensus</p>
            <?php endif; ?>
        </div>

        <!-- Keterangan -->
        <div class="mb-6">
            <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-2">
                Keterangan
            </label>
            <textarea id="keterangan" 
                      name="keterangan" 
                      rows="3" 
                      class="input-field resize-none" ><?= old('keterangan') ?></textarea>
            <p class="mt-1 text-xs text-gray-500">Tambahkan keterangan atau detail tambahan kegiatan (opsional)</p>
        </div>

        <!-- Periode -->
        <div class="mb-6">
            <label for="periode" class="block text-sm font-medium text-gray-700 mb-2">
                Periode <span class="text-red-500">*</span>
            </label>
            <select 
                   id="periode" 
                   name="periode" 
                   class="input-field <?= session('errors.periode') ? 'border-red-500' : '' ?>" 
                   value="<?= old('periode') ?>"
                   required>
                <option value="">-- Pilih Periode --</option>
                <option value="Tahunan" <?= old('periode') == 'Tahunan' ? 'selected' : '' ?>>Tahunan</option>
                <option value="Semesteran" <?= old('periode') == 'Semesteran' ? 'selected' : '' ?>>Semesteran</option>
                <option value="Triwulanan" <?= old('periode') == 'Triwulanan' ? 'selected' : '' ?>>Triwulanan</option>
                <option value="Bulanan" <?= old('periode') == 'Bulanan' ? 'selected' : '' ?>>Bulanan</option>
                <option value="Lainnya" <?= old('periode') == 'Lainnya' ? 'selected' : '' ?>>Lainnya</option>
            </select>
            <?php if (session('errors.periode')): ?>
                <p class="mt-1 text-xs text-red-500"><?= session('errors.periode') ?></p>
            <?php else: ?>
                <p class="mt-1 text-xs text-gray-500">Masukkan periode pelaksanaan kegiatan (tahun, triwulan, semester, atau bulan)</p>
            <?php endif; ?>
        </div>

        <!-- Pelaksana -->
        <div class="mb-6">
            <label for="pelaksana" class="block text-sm font-medium text-gray-700 mb-2">
                Pelaksana
            </label>
            <select 
                   id="pelaksana" 
                   name="pelaksana" 
                   class="input-field <?= session('errors.pelaksana') ? 'border-red-500' : '' ?>" 
                   value="<?= old('pelaksana') ?>">
                <option value="">-- Pilih Pelaksana --</option>
                <option value="Provinsi" <?= old('pelaksana') == 'Provinsi' ? 'selected' : '' ?>>Provinsi</option>
                <option value="Kabupaten" <?= old('pelaksana') == 'Kabupaten' ? 'selected' : '' ?>>Kabupaten</option>
            </select>
            <?php if (session('errors.pelaksana')): ?>
                <p class="mt-1 text-xs text-red-500"><?= session('errors.pelaksana') ?></p>
            <?php else: ?>
                <p class="mt-1 text-xs text-gray-500">Tim pelaksana kegiatan</p>
            <?php endif; ?>
        </div>

        <!-- Divider -->
        <div class="border-t border-gray-200 my-6"></div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-3">
            <button type="button" 
                    onclick="window.location.href='<?= base_url('superadmin/master-kegiatan') ?>'"
                    class="btn-secondary w-full sm:w-auto order-2 sm:order-1">
                <i class="fas fa-times mr-2"></i>
                Batal
            </button>
            <button type="button" 
                    onclick="resetForm()"
                    class="btn-secondary w-full sm:w-auto order-3 sm:order-2">
                <i class="fas fa-undo mr-2"></i>
                Reset
            </button>
            <button type="submit" 
                    class="btn-primary w-full sm:w-auto sm:ml-auto order-1 sm:order-3">
                <i class="fas fa-save mr-2"></i>
                Simpan Data
            </button>
        </div>
    </form>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Form validation dan submit
document.getElementById('formMasterKegiatan').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Validasi form
    const idOutput = document.getElementById('id_output').value.trim();
    const namaKegiatan = document.getElementById('nama_kegiatan').value.trim();
    const periode = document.getElementById('periode').value.trim();
    
    if (!idOutput) {
        Swal.fire({
            icon: 'error',
            title: 'Master Output Belum Dipilih',
            text: 'Harap pilih master output terlebih dahulu!',
            confirmButtonColor: '#3b82f6',
            customClass: {
                popup: 'rounded-xl',
                confirmButton: 'px-6 py-2.5 rounded-lg font-medium'
            }
        });
        return;
    }
    
    if (!namaKegiatan || !periode) {
        Swal.fire({
            icon: 'error',
            title: 'Form Tidak Lengkap',
            text: 'Harap lengkapi semua field yang wajib diisi!',
            confirmButtonColor: '#3b82f6',
            customClass: {
                popup: 'rounded-xl',
                confirmButton: 'px-6 py-2.5 rounded-lg font-medium'
            }
        });
        return;
    }
    
    // Validasi minimal panjang input
    if (namaKegiatan.length < 5) {
        Swal.fire({
            icon: 'error',
            title: 'Nama Kegiatan Terlalu Pendek',
            text: 'Nama kegiatan minimal 5 karakter',
            confirmButtonColor: '#3b82f6',
            customClass: {
                popup: 'rounded-xl',
                confirmButton: 'px-6 py-2.5 rounded-lg font-medium'
            }
        });
        return;
    }
    
    // Konfirmasi sebelum menyimpan
    Swal.fire({
        title: 'Simpan Data Kegiatan?',
        html: `Apakah Anda yakin ingin menyimpan kegiatan <strong>"${namaKegiatan}"</strong>?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3b82f6',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="fas fa-save mr-2"></i>Ya, Simpan',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        customClass: {
            popup: 'rounded-xl',
            confirmButton: 'px-6 py-2.5 rounded-lg font-medium',
            cancelButton: 'px-6 py-2.5 rounded-lg font-medium'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Tampilkan loading
            Swal.fire({
                title: 'Menyimpan Data...',
                html: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Submit form
            this.submit();
        }
    });
});

// Reset form
function resetForm() {
    Swal.fire({
        title: 'Reset Form?',
        text: 'Semua data yang telah diisi akan dihapus',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3b82f6',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Reset',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        customClass: {
            popup: 'rounded-xl',
            confirmButton: 'px-6 py-2.5 rounded-lg font-medium',
            cancelButton: 'px-6 py-2.5 rounded-lg font-medium'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('formMasterKegiatan').reset();
            
            Swal.fire({
                icon: 'success',
                title: 'Form Direset',
                text: 'Semua data telah dihapus',
                timer: 1500,
                showConfirmButton: false,
                customClass: {
                    popup: 'rounded-xl'
                }
            });
        }
    });
}

// Real-time character counter untuk textarea
const textareas = ['keterangan'];
textareas.forEach(id => {
    const textarea = document.getElementById(id);
    textarea.addEventListener('input', function() {
        const length = this.value.length;
        const hint = this.nextElementSibling;
        
        if (id === 'keterangan' && length < 10 && length > 0) {
            hint.classList.add('text-red-500');
            hint.classList.remove('text-gray-500');
        } else {
            hint.classList.remove('text-red-500');
            hint.classList.add('text-gray-500');
        }
    });
});

// Auto-capitalize first letter untuk nama kegiatan
document.getElementById('nama_kegiatan').addEventListener('blur', function() {
    if (this.value) {
        this.value = this.value.charAt(0).toUpperCase() + this.value.slice(1);
    }
});

// Show validation errors from session
<?php if (session('errors')): ?>
    Swal.fire({
        icon: 'error',
        title: 'Validasi Gagal',
        html: '<?= implode("<br>", array_map('esc', session('errors'))) ?>',
        confirmButtonColor: '#3b82f6',
        customClass: {
            popup: 'rounded-xl',
            confirmButton: 'px-6 py-2.5 rounded-lg font-medium'
        }
    });
<?php endif; ?>
</script>

<?= $this->endSection() ?>