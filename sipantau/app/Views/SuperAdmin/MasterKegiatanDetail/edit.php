<?= $this->extend('layouts/sadmin_layout') ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="mb-6">
    <div class="flex items-center text-sm text-gray-600 mb-4">
        <a href="<?= base_url('superadmin/master-kegiatan-detail') ?>" class="hover:text-blue-600 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Kembali ke Master Kegiatan Detail
        </a>
    </div>
    <h1 class="text-2xl font-bold text-gray-900">Edit Master Kegiatan Detail</h1>
    <p class="text-gray-600 mt-1">Perbarui data detail kegiatan survei/sensus</p>
</div>

<!-- Form Card -->
<div class="card max-w-3xl">
    <form id="formKegiatanDetail" method="POST" action="<?= base_url('superadmin/master-kegiatan-detail/' . $detail['id_kegiatan_detail']) ?>">
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
                        Anda sedang mengedit data: <strong><?= esc($detail['nama_kegiatan_detail']) ?></strong>
                    </p>
                </div>
            </div>
        </div>

        <!-- Master Kegiatan -->
        <div class="mb-6">
            <label for="id_kegiatan" class="block text-sm font-medium text-gray-700 mb-2">
                Master Kegiatan <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <select id="id_kegiatan" 
                        name="id_kegiatan" 
                        class="input-field appearance-none pr-10 <?= session('errors.id_kegiatan') ? 'border-red-500' : '' ?>"
                        required>
                    <option value="">-- Pilih Master Kegiatan --</option>
                    <?php if (!empty($masterKegiatans)): ?>
                        <?php foreach ($masterKegiatans as $kegiatan): ?>
                            <option value="<?= $kegiatan['id_kegiatan'] ?>" 
                                    <?= (old('id_kegiatan', $detail['id_kegiatan']) == $kegiatan['id_kegiatan']) ? 'selected' : '' ?>>
                                <?= esc($kegiatan['nama_kegiatan']) ?>
                                <?php if (!empty($kegiatan['nama_output'])): ?>
                                    (<?= esc($kegiatan['nama_output']) ?>)
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <i class="fas fa-chevron-down text-gray-400 text-sm"></i>
                </div>
            </div>
            <?php if (session('errors.id_kegiatan')): ?>
                <p class="mt-1 text-xs text-red-500"><?= session('errors.id_kegiatan') ?></p>
            <?php else: ?>
                <p class="mt-1 text-xs text-gray-500">Pilih master kegiatan yang sesuai</p>
            <?php endif; ?>
        </div>

        <!-- Nama Kegiatan Detail -->
        <div class="mb-6">
            <label for="nama_kegiatan_detail" class="block text-sm font-medium text-gray-700 mb-2">
                Nama Kegiatan Detail <span class="text-red-500">*</span>
            </label>
            <input type="text" 
                   id="nama_kegiatan_detail" 
                   name="nama_kegiatan_detail" 
                   class="input-field <?= session('errors.nama_kegiatan_detail') ? 'border-red-500' : '' ?>"
                   value="<?= old('nama_kegiatan_detail', $detail['nama_kegiatan_detail']) ?>"
                   required>
            <?php if (session('errors.nama_kegiatan_detail')): ?>
                <p class="mt-1 text-xs text-red-500"><?= session('errors.nama_kegiatan_detail') ?></p>
            <?php else: ?>
                <p class="mt-1 text-xs text-gray-500">Masukkan nama detail kegiatan yang akan dilaksanakan</p>
            <?php endif; ?>
        </div>

        <!-- Satuan -->
        <div class="mb-6">
            <label for="satuan" class="block text-sm font-medium text-gray-700 mb-2">
                Satuan <span class="text-red-500">*</span>
            </label>
            <input type="text" 
                   id="satuan" 
                   name="satuan" 
                   class="input-field <?= session('errors.satuan') ? 'border-red-500' : '' ?>"
                   value="<?= old('satuan', $detail['satuan']) ?>"
                   required>
            <?php if (session('errors.satuan')): ?>
                <p class="mt-1 text-xs text-red-500"><?= session('errors.satuan') ?></p>
            <?php else: ?>
                <p class="mt-1 text-xs text-gray-500">Masukkan satuan pengukuran kegiatan</p>
            <?php endif; ?>
        </div>

        <!-- Grid for Periode and Tahun -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Periode -->
            <div>
                <label for="periode" class="block text-sm font-medium text-gray-700 mb-2">
                    Periode <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       id="periode" 
                       name="periode" 
                       class="input-field <?= session('errors.periode') ? 'border-red-500' : '' ?>"
                       value="<?= old('periode', $detail['periode']) ?>"
                       required>
                <?php if (session('errors.periode')): ?>
                    <p class="mt-1 text-xs text-red-500"><?= session('errors.periode') ?></p>
                <?php else: ?>
                    <p class="mt-1 text-xs text-gray-500">Periode pelaksanaan</p>
                <?php endif; ?>
            </div>

            <!-- Tahun -->
            <div>
                <label for="tahun" class="block text-sm font-medium text-gray-700 mb-2">
                    Tahun <span class="text-red-500">*</span>
                </label>
                <input type="number" 
                       id="tahun" 
                       name="tahun" 
                       class="input-field <?= session('errors.tahun') ? 'border-red-500' : '' ?>"
                       value="<?= old('tahun', $detail['tahun']) ?>"
                       min="2020"
                       max="2030"
                       required>
                <?php if (session('errors.tahun')): ?>
                    <p class="mt-1 text-xs text-red-500"><?= session('errors.tahun') ?></p>
                <?php else: ?>
                    <p class="mt-1 text-xs text-gray-500">Tahun pelaksanaan</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Grid for Tanggal Mulai and Tanggal Selesai -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Tanggal Mulai -->
            <div>
                <label for="tanggal_mulai" class="block text-sm font-medium text-gray-700 mb-2">
                    Tanggal Mulai
                </label>
                <input type="date" 
                       id="tanggal_mulai" 
                       name="tanggal_mulai" 
                       class="input-field <?= session('errors.tanggal_mulai') ? 'border-red-500' : '' ?>"
                       value="<?= old('tanggal_mulai', $detail['tanggal_mulai']) ?>">
                <?php if (session('errors.tanggal_mulai')): ?>
                    <p class="mt-1 text-xs text-red-500"><?= session('errors.tanggal_mulai') ?></p>
                <?php else: ?>
                    <p class="mt-1 text-xs text-gray-500">Tanggal mulai kegiatan</p>
                <?php endif; ?>
            </div>

            <!-- Tanggal Selesai -->
            <div>
                <label for="tanggal_selesai" class="block text-sm font-medium text-gray-700 mb-2">
                    Tanggal Selesai
                </label>
                <input type="date" 
                       id="tanggal_selesai" 
                       name="tanggal_selesai" 
                       class="input-field <?= session('errors.tanggal_selesai') ? 'border-red-500' : '' ?>"
                       value="<?= old('tanggal_selesai', $detail['tanggal_selesai']) ?>">
                <?php if (session('errors.tanggal_selesai')): ?>
                    <p class="mt-1 text-xs text-red-500"><?= session('errors.tanggal_selesai') ?></p>
                <?php else: ?>
                    <p class="mt-1 text-xs text-gray-500">Tanggal selesai kegiatan</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Keterangan -->
        <div class="mb-6">
            <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-2">
                Keterangan
            </label>
            <textarea id="keterangan" 
                      name="keterangan" 
                      rows="3" 
                      class="input-field resize-none <?= session('errors.keterangan') ? 'border-red-500' : '' ?>"><?= old('keterangan', $detail['keterangan']) ?></textarea>
            <?php if (session('errors.keterangan')): ?>
                <p class="mt-1 text-xs text-red-500"><?= session('errors.keterangan') ?></p>
            <?php else: ?>
                <p class="mt-1 text-xs text-gray-500">Keterangan tambahan untuk kegiatan detail ini</p>
            <?php endif; ?>
        </div>

        <!-- Divider -->
        <div class="border-t border-gray-200 my-6"></div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-3">
            <button type="button" 
                    onclick="window.location.href='<?= base_url('superadmin/master-kegiatan-detail') ?>'"
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
// Store original values
const originalValues = {
    id_kegiatan: '<?= $detail['id_kegiatan'] ?>',
    nama_kegiatan_detail: '<?= esc($detail['nama_kegiatan_detail'], 'js') ?>',
    satuan: '<?= esc($detail['satuan'], 'js') ?>',
    periode: '<?= esc($detail['periode'], 'js') ?>',
    tahun: '<?= $detail['tahun'] ?>',
    tanggal_mulai: '<?= $detail['tanggal_mulai'] ?>',
    tanggal_selesai: '<?= $detail['tanggal_selesai'] ?>',
    keterangan: '<?= esc($detail['keterangan'], 'js') ?>'
};

// Form validation dan submit
document.getElementById('formKegiatanDetail').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const idKegiatan = document.getElementById('id_kegiatan').value;
    const namaKegiatanDetail = document.getElementById('nama_kegiatan_detail').value.trim();
    const satuan = document.getElementById('satuan').value.trim();
    const periode = document.getElementById('periode').value.trim();
    const tahun = document.getElementById('tahun').value.trim();
    
    if (!idKegiatan || !namaKegiatanDetail || !satuan || !periode || !tahun) {
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
    const tanggalMulai = document.getElementById('tanggal_mulai').value;
    const tanggalSelesai = document.getElementById('tanggal_selesai').value;
    const keterangan = document.getElementById('keterangan').value;
    
    const hasChanges = 
        idKegiatan !== originalValues.id_kegiatan ||
        namaKegiatanDetail !== originalValues.nama_kegiatan_detail ||
        satuan !== originalValues.satuan ||
        periode !== originalValues.periode ||
        tahun !== originalValues.tahun ||
        tanggalMulai !== originalValues.tanggal_mulai ||
        tanggalSelesai !== originalValues.tanggal_selesai ||
        keterangan !== originalValues.keterangan;
    
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
            
            // Submit form
            this.submit();
        }
    });
});

// Reset to original values
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
            document.getElementById('id_kegiatan').value = originalValues.id_kegiatan;
            document.getElementById('nama_kegiatan_detail').value = originalValues.nama_kegiatan_detail;
            document.getElementById('satuan').value = originalValues.satuan;
            document.getElementById('periode').value = originalValues.periode;
            document.getElementById('tahun').value = originalValues.tahun;
            document.getElementById('tanggal_mulai').value = originalValues.tanggal_mulai;
            document.getElementById('tanggal_selesai').value = originalValues.tanggal_selesai;
            document.getElementById('keterangan').value = originalValues.keterangan;
            
            Swal.fire({
                icon: 'success',
                title: 'Data Dikembalikan',
                text: 'Data telah dikembalikan ke nilai awal',
                timer: 1500,
                showConfirmButton: false,
                customClass: {
                    popup: 'rounded-xl'
                }
            });
        }
    });
}

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