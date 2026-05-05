<?= $this->extend('layouts/sadmin_layout') ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="mb-6">
    <div class="flex items-center text-sm text-gray-600 mb-4">
        <a href="<?= base_url('superadmin/master-output') ?>" class="hover:text-blue-600 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Kembali ke Master Output
        </a>
    </div>
    <h1 class="text-2xl font-bold text-gray-900">Edit Master Output</h1>
    <p class="text-gray-600 mt-1">Perbarui data master output kegiatan survei/sensus</p>
</div>

<!-- Form Card -->
<div class="card max-w-3xl">
    <form id="formMasterOutput" method="POST" action="<?= base_url('superadmin/master-output/' . $output['id_output']) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="_method" value="PUT">
        
        <!-- Info Alert -->
        <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-info-circle text-blue-600"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        Anda sedang mengedit data: <strong><?= esc($output['nama_output']) ?></strong>
                    </p>
                </div>
            </div>
        </div>

        <!-- Nama Output -->
        <div class="mb-6">
            <label for="nama_output" class="block text-sm font-medium text-gray-700 mb-2">
                Nama Output <span class="text-red-500">*</span>
            </label>
            <input type="text" 
                   id="nama_output" 
                   name="nama_output" 
                   class="input-field <?= session('errors.nama_output') ? 'border-red-500' : '' ?>" 
                   value="<?= old('nama_output', $output['nama_output']) ?>"
                   required>
            <?php if (session('errors.nama_output')): ?>
                <p class="mt-1 text-sm text-red-600"><?= session('errors.nama_output') ?></p>
            <?php endif; ?>
            <p class="mt-1 text-xs text-gray-500">Masukkan nama lengkap kegiatan survei/sensus</p>
        </div>

        <!-- Fungsi -->
        <div class="mb-6">
            <label for="fungsi" class="block text-sm font-medium text-gray-700 mb-2">
                Fungsi <span class="text-red-500">*</span>
            </label>
            <textarea id="fungsi" 
                      name="fungsi" 
                      rows="4" 
                      class="input-field resize-none <?= session('errors.fungsi') ? 'border-red-500' : '' ?>" 
                      required><?= old('fungsi', $output['fungsi']) ?></textarea>
            <?php if (session('errors.fungsi')): ?>
                <p class="mt-1 text-sm text-red-600"><?= session('errors.fungsi') ?></p>
            <?php endif; ?>
            <p class="mt-1 text-xs text-gray-500">Jelaskan fungsi atau tujuan dari kegiatan ini</p>
        </div>

        <!-- Divider -->
        <div class="border-t border-gray-200 my-6"></div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-3">
            <button type="button" 
                    onclick="window.location.href='<?= base_url('superadmin/master-output') ?>'"
                    class="btn-secondary w-full sm:w-auto order-2 sm:order-1">
                <i class="fas fa-times mr-2"></i>
                Batal
            </button>
            <button type="button" 
                    onclick="resetToOriginal()"
                    class="btn-secondary w-full sm:w-auto order-3 sm:order-2">
                <i class="fas fa-undo mr-2"></i>
                Kembalikan
            </button>
            <button type="submit" 
                    class="btn-primary w-full sm:w-auto sm:ml-auto order-1 sm:order-3">
                <i class="fas fa-save mr-2"></i>
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// ====================================================================
// Store Original Values
// ====================================================================
const originalValues = {
    nama_output: '<?= esc($output['nama_output']) ?>',
    fungsi: '<?= esc($output['fungsi']) ?>',
};

// ====================================================================
// Form Submit with Confirmation
// ====================================================================
document.getElementById('formMasterOutput').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const namaOutput = document.getElementById('nama_output').value.trim();
    const fungsi = document.getElementById('fungsi').value.trim();
    
    if (!namaOutput || !fungsi) {
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
    
    // Check for changes
    const hasChanges = 
        namaOutput !== originalValues.nama_output || 
        fungsi !== originalValues.fungsi || 
    
    if (!hasChanges) {
        Swal.fire({
            icon: 'info',
            title: 'Tidak Ada Perubahan',
            text: 'Tidak ada data yang diubah',
            confirmButtonColor: '#3b82f6',
            customClass: {
                popup: 'rounded-xl',
                confirmButton: 'px-6 py-2.5 rounded-lg font-medium'
            }
        });
        return;
    }
    
    Swal.fire({
        title: 'Simpan Perubahan?',
        text: 'Apakah Anda yakin ingin menyimpan perubahan data ini?',
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
            Swal.fire({
                title: 'Menyimpan Perubahan...',
                html: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });
            
            this.submit();
        }
    });
});

// ====================================================================
// Reset to Original Values
// ====================================================================
function resetToOriginal() {
    Swal.fire({
        title: 'Kembalikan Data?',
        text: 'Semua perubahan akan dikembalikan ke data awal',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3b82f6',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Kembalikan',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        customClass: {
            popup: 'rounded-xl',
            confirmButton: 'px-6 py-2.5 rounded-lg font-medium',
            cancelButton: 'px-6 py-2.5 rounded-lg font-medium'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('nama_output').value = originalValues.nama_output;
            document.getElementById('fungsi').value = originalValues.fungsi;
            
            // Remove highlight
            document.querySelectorAll('.border-blue-500, .bg-blue-50').forEach(el => {
                el.classList.remove('border-blue-500', 'bg-blue-50');
            });
            
            Swal.fire({
                icon: 'success',
                title: 'Data Dikembalikan',
                text: 'Data telah dikembalikan ke nilai awal',
                showConfirmButton: false,
                timer: 1500,
                timerProgressBar: true,
                customClass: {
                    popup: 'rounded-xl'
                }
            });
        }
    });
}

// ====================================================================
// Track Changes for Highlight
// ====================================================================
const formInputs = ['nama_output', 'fungsi'];
formInputs.forEach(inputId => {
    const input = document.getElementById(inputId);
    input.addEventListener('input', function() {
        const originalValue = originalValues[inputId];
        if (this.value !== originalValue) {
            this.classList.add('border-blue-500', 'bg-blue-50');
        } else {
            this.classList.remove('border-blue-500', 'bg-blue-50');
        }
    });
});
</script>

<?= $this->endSection() ?>