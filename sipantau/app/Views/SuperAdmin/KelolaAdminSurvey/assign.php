<?= $this->extend('layouts/sadmin_layout') ?>

<?= $this->section('content') ?>

<?php

$assigned_ids = $assigned_ids ?? [];
?>

<!-- Page Header -->
<div class="mb-6">
    <div class="flex items-center text-sm text-gray-600 mb-4">
        <a href="<?= base_url('superadmin/kelola-admin-surveyprov') ?>" class="hover:text-blue-600 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Kembali
        </a>
    </div>
    <h1 class="text-2xl font-bold text-gray-900">
        <?= $is_edit ? 'Edit Admin Survei Provinsi' : 'Assign Admin Survei Provinsi' ?>
    </h1>
    <p class="text-gray-600 mt-1">
        <?= $is_edit ? 'Kelola kegiatan yang di-assign ke admin' : 'Pilih user dan kegiatan detail yang akan di-assign' ?>
    </p>
</div>

<!-- Flash Messages -->
<?php if (session()->getFlashdata('error')): ?>
<div class="mb-4 bg-red-50 border border-red-200 rounded-lg p-4">
    <div class="flex items-center">
        <i class="fas fa-exclamation-circle text-red-600 mr-3"></i>
        <p class="text-sm text-red-700"><?= session()->getFlashdata('error') ?></p>
    </div>
</div>
<?php endif; ?>

<?php if (session()->getFlashdata('success')): ?>
<div class="mb-4 bg-green-50 border border-green-200 rounded-lg p-4">
    <div class="flex items-center">
        <i class="fas fa-check-circle text-green-600 mr-3"></i>
        <p class="text-sm text-green-700"><?= session()->getFlashdata('success') ?></p>
    </div>
</div>
<?php endif; ?>

<?php if ($is_edit && $admin): ?>
    <!-- Admin Info Card (Mode Edit) -->
    <div class="card mb-6">
        <div class="flex items-center gap-4 mb-4 pb-4 border-b border-gray-200">
            <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg">
                <i class="fas fa-user text-white text-2xl"></i>
            </div>
            <div class="flex-1">
                <h2 class="text-xl font-bold text-gray-900"><?= esc($admin['nama_user']) ?></h2>
                <div class="flex flex-wrap gap-3 mt-2 text-sm text-gray-600">
                    <span class="flex items-center">
                        <i class="fas fa-id-badge mr-2 text-gray-400"></i>
                        <?= esc($admin['sobat_id']) ?>
                    </span>
                    <span class="flex items-center">
                        <i class="fas fa-envelope mr-2 text-gray-400"></i>
                        <?= esc($admin['email']) ?>
                    </span>
                    <span class="flex items-center">
                        <i class="fas fa-phone mr-2 text-gray-400"></i>
                        <?= esc($admin['hp']) ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Kegiatan yang sudah di-assign -->
        <?php if (!empty($admin['assigned_kegiatan'])): ?>
        <div class="mb-4">
            <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                <i class="fas fa-clipboard-list mr-2 text-blue-600"></i>
                Kegiatan yang Sudah Di-assign (<?= count($admin['assigned_kegiatan']) ?>)
            </h3>
            <div class="space-y-2">
                <?php foreach ($admin['assigned_kegiatan'] as $kegiatan): ?>
                <div class="flex items-start gap-3 p-3 bg-gray-50 border border-gray-200 rounded-lg hover:border-gray-300 transition-colors">
                    <div class="w-10 h-10 bg-green-600 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-check text-white text-sm"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 mb-1"><?= esc($kegiatan['nama_kegiatan_detail']) ?></p>
                        <p class="text-xs text-gray-600 mb-1.5"><?= esc($kegiatan['nama_kegiatan']) ?></p>
                        <div class="flex flex-wrap gap-1.5">
                            <span class="inline-flex items-center px-2 py-0.5 bg-white border border-gray-300 text-gray-700 rounded text-xs">
                                <i class="fas fa-ruler mr-1"></i><?= esc($kegiatan['satuan']) ?>
                            </span>
                            <span class="inline-flex items-center px-2 py-0.5 bg-white border border-gray-300 text-gray-700 rounded text-xs">
                                <i class="fas fa-calendar mr-1"></i><?= esc($kegiatan['periode']) ?> (<?= esc($kegiatan['tahun']) ?>)
                            </span>
                            <?php if ($kegiatan['tanggal_mulai'] && $kegiatan['tanggal_selesai']): ?>
                            <span class="inline-flex items-center px-2 py-0.5 bg-white border border-gray-300 text-gray-700 rounded text-xs">
                                <i class="fas fa-calendar-check mr-1"></i>
                                <?= date('d M Y', strtotime($kegiatan['tanggal_mulai'])) ?> - <?= date('d M Y', strtotime($kegiatan['tanggal_selesai'])) ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <button type="button" 
                            onclick="deleteAssignment(<?= $admin['id_admin_provinsi'] ?>, <?= $kegiatan['id_kegiatan_detail'] ?>, '<?= esc(addslashes($kegiatan['nama_kegiatan_detail'])) ?>')"
                            class="px-3 py-1.5 text-xs text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors flex-shrink-0"
                            title="Hapus assignment">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Form Tambah Kegiatan Baru -->
        <div class="pt-4 border-t border-gray-200">
            <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                <i class="fas fa-plus-circle mr-2 text-blue-600"></i>
                Tambah Kegiatan Baru
            </h3>
            <form action="<?= base_url('superadmin/kelola-admin-surveyprov/update/' . $admin['id_admin_provinsi']) ?>" method="POST" id="addKegiatanForm">
                <?= csrf_field() ?>
                
                <!-- Pilih Kegiatan Detail -->
                <div class="mb-4">
                    <?php if (empty($kegiatan_details)): ?>
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <i class="fas fa-exclamation-triangle text-yellow-600 mr-3"></i>
                                <p class="text-sm text-yellow-700">Belum ada kegiatan detail yang tersedia</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <?= view('components/select_component', [
                            'label' => 'Pilih Kegiatan Detail',
                            'name' => 'id_kegiatan_detail',
                            'id' => 'id_kegiatan_detail',
                            'required' => true,
                            'placeholder' => 'Cari dan pilih kegiatan...',
                            'options' => array_filter($kegiatan_details, function($kd) use ($assigned_ids) {
                                return !in_array($kd['id_kegiatan_detail'], $assigned_ids);
                            }),
                            'optionValue' => 'id_kegiatan_detail',
                            'optionText' => 'nama_kegiatan_detail',
                            'optionDataAttributes' => ['nama_kegiatan_detail', 'satuan', 'periode', 'tahun', 'tanggal_mulai', 'tanggal_selesai'],
                            'grouped' => true,
                            'groupBy' => 'nama_kegiatan',
                            'onchange' => 'updateKegiatanInfo()',
                            'emptyMessage' => 'Semua kegiatan sudah di-assign',
                            'helpText' => 'Pilih kegiatan yang belum di-assign',
                            'enableSearch' => true
                        ]) ?>
                        
                        <!-- Kegiatan Info Preview -->
                        <div id="kegiatanInfoPreview" class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-lg hidden">
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-clipboard-list text-white text-sm"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 mb-1.5" id="previewKegiatanNama"></p>
                                    <div class="flex flex-wrap gap-1.5">
                                        <span class="inline-flex items-center px-2 py-0.5 bg-white border border-blue-300 text-gray-700 rounded text-xs" id="previewSatuan"></span>
                                        <span class="inline-flex items-center px-2 py-0.5 bg-white border border-blue-300 text-gray-700 rounded text-xs" id="previewPeriode"></span>
                                        <span class="inline-flex items-center px-2 py-0.5 bg-white border border-blue-300 text-gray-700 rounded text-xs" id="previewTanggal"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Action Button -->
                <div class="flex justify-end">
                    <button type="submit" 
                            class="btn-primary"
                            <?= empty($kegiatan_details) || count(array_filter($kegiatan_details, function($kd) use ($assigned_ids) { return !in_array($kd['id_kegiatan_detail'], $assigned_ids); })) == 0 ? 'disabled' : '' ?>>
                        <i class="fas fa-plus mr-2"></i>
                        Tambah Kegiatan
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php else: ?>
    <!-- Form Create (Mode Assign Baru) -->
    <div class="card max-w-3xl">
        <form action="<?= base_url('superadmin/kelola-admin-surveyprov/store-assign') ?>" method="POST" id="assignForm">
            <?= csrf_field() ?>
            
            <!-- Pilih User -->
            <div class="mb-6">
                <?php if (empty($users)): ?>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Pilih User <span class="text-red-500">*</span>
                    </label>
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-triangle text-yellow-600 mr-3"></i>
                            <p class="text-sm text-yellow-700">Tidak ada user yang tersedia</p>
                        </div>
                    </div>
                <?php else: ?>
                    <?= view('components/select_component', [
                        'label' => 'Pilih User',
                        'name' => 'sobat_id',
                        'id' => 'sobat_id',
                        'required' => true,
                        'placeholder' => 'Cari dan pilih user...',
                        'options' => $users,
                        'optionValue' => 'sobat_id',
                        'optionText' => function($user) {
                            return esc($user['nama_user']) . ' (' . esc($user['sobat_id']) . ')';
                        },
                        'optionDataAttributes' => ['nama_user', 'email', 'hp'],
                        'onchange' => 'updateUserInfo()',
                        'emptyMessage' => 'Tidak ada user yang tersedia',
                        'enableSearch' => true
                    ]) ?>
                    
                    <!-- User Info Preview -->
                    <div id="userInfoPreview" class="mt-3 p-3 bg-gray-50 border border-gray-200 rounded-lg hidden">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-user text-white"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900" id="previewNama"></p>
                                <p class="text-xs text-gray-600" id="previewEmail"></p>
                                <p class="text-xs text-gray-500" id="previewHp"></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Pilih Kegiatan Detail -->
            <div class="mb-6">
                <?php if (empty($kegiatan_details)): ?>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Pilih Kegiatan Detail <span class="text-red-500">*</span>
                    </label>
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-triangle text-yellow-600 mr-3"></i>
                            <p class="text-sm text-yellow-700">Belum ada kegiatan detail yang tersedia</p>
                        </div>
                    </div>
                <?php else: ?>
                    <?= view('components/select_component', [
                        'label' => 'Pilih Kegiatan Detail',
                        'name' => 'id_kegiatan_detail',
                        'id' => 'id_kegiatan_detail',
                        'required' => true,
                        'placeholder' => 'Cari dan pilih kegiatan...',
                        'options' => $kegiatan_details,
                        'optionValue' => 'id_kegiatan_detail',
                        'optionText' => 'nama_kegiatan_detail',
                        'optionDataAttributes' => ['nama_kegiatan_detail', 'satuan', 'periode', 'tahun', 'tanggal_mulai', 'tanggal_selesai'],
                        'grouped' => true,
                        'groupBy' => 'nama_kegiatan',
                        'onchange' => 'updateKegiatanInfo()',
                        'emptyMessage' => 'Belum ada kegiatan detail yang tersedia',
                        'helpText' => 'Sistem akan otomatis mencegah jika admin sudah di-assign ke kegiatan yang sama',
                        'enableSearch' => true
                    ]) ?>
                    
                    <!-- Kegiatan Info Preview -->
                    <div id="kegiatanInfoPreview" class="mt-3 p-3 bg-gray-50 border border-gray-200 rounded-lg hidden">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 bg-green-600 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-clipboard-list text-white text-sm"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 mb-1.5" id="previewKegiatanNama"></p>
                                <div class="flex flex-wrap gap-1.5">
                                    <span class="inline-flex items-center px-2 py-0.5 bg-white border border-gray-300 text-gray-700 rounded text-xs" id="previewSatuan"></span>
                                    <span class="inline-flex items-center px-2 py-0.5 bg-white border border-gray-300 text-gray-700 rounded text-xs" id="previewPeriode"></span>
                                    <span class="inline-flex items-center px-2 py-0.5 bg-white border border-gray-300 text-gray-700 rounded text-xs" id="previewTanggal"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-3 pt-4 border-t border-gray-200">
                <a href="<?= base_url('superadmin/kelola-admin-surveyprov') ?>" 
                   class="btn-secondary flex-1 text-center">
                    <i class="fas fa-times mr-2"></i>
                    Batal
                </a>
                <button type="submit" 
                        class="btn-primary flex-1"
                        <?= empty($users) || empty($kegiatan_details) ? 'disabled' : '' ?>>
                    <i class="fas fa-save mr-2"></i>
                    Simpan Assignment
                </button>
            </div>
        </form>
    </div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Update user info preview
function updateUserInfo() {
    const select = document.getElementById('sobat_id');
    const preview = document.getElementById('userInfoPreview');
    
    if (select && select.value) {
        const option = select.options[select.selectedIndex];
        document.getElementById('previewNama').textContent = option.dataset.nama_user;
        document.getElementById('previewEmail').textContent = option.dataset.email;
        document.getElementById('previewHp').textContent = option.dataset.hp;
        preview.classList.remove('hidden');
    } else if (preview) {
        preview.classList.add('hidden');
    }
}

// Update kegiatan info preview
function updateKegiatanInfo() {
    const select = document.getElementById('id_kegiatan_detail');
    const preview = document.getElementById('kegiatanInfoPreview');
    
    if (select && select.value) {
        const option = select.options[select.selectedIndex];
        
        // Set nama kegiatan
        document.getElementById('previewKegiatanNama').textContent = option.dataset.nama_kegiatan_detail;
        
        // Set satuan with icon
        document.getElementById('previewSatuan').innerHTML = '<i class="fas fa-ruler mr-1"></i>' + option.dataset.satuan;
        
        // Set periode dan tahun with icon
        const periode = option.dataset.periode;
        const tahun = option.dataset.tahun;
        document.getElementById('previewPeriode').innerHTML = '<i class="fas fa-calendar mr-1"></i>' + periode + ' (' + tahun + ')';
        
        // Set tanggal range
        if (option.dataset.tanggal_mulai && option.dataset.tanggal_selesai) {
            const mulai = new Date(option.dataset.tanggal_mulai).toLocaleDateString('id-ID', { 
                day: '2-digit', 
                month: 'short', 
                year: 'numeric' 
            });
            const selesai = new Date(option.dataset.tanggal_selesai).toLocaleDateString('id-ID', { 
                day: '2-digit', 
                month: 'short', 
                year: 'numeric' 
            });
            document.getElementById('previewTanggal').innerHTML = '<i class="fas fa-calendar-check mr-1"></i>' + mulai + ' - ' + selesai;
            document.getElementById('previewTanggal').classList.remove('hidden');
        } else {
            document.getElementById('previewTanggal').classList.add('hidden');
        }
        
        preview.classList.remove('hidden');
    } else if (preview) {
        preview.classList.add('hidden');
    }
}

// Delete assignment function
function deleteAssignment(idAdminProvinsi, idKegiatanDetail, namaKegiatan) {
    Swal.fire({
        title: 'Hapus Assignment?',
        html: `Apakah Anda yakin ingin menghapus assignment dari kegiatan:<br><strong>${namaKegiatan}</strong>?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="fas fa-trash mr-2"></i>Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Menghapus...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Send AJAX request
            fetch('<?= base_url('superadmin/kelola-admin-surveyprov/delete-assignment') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `id_admin_provinsi=${idAdminProvinsi}&id_kegiatan_detail=${idKegiatanDetail}&<?= csrf_token() ?>=<?= csrf_hash() ?>`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.message,
                        confirmButtonColor: '#3b82f6'
                    }).then(() => {
                        // Jika tidak ada kegiatan tersisa, redirect ke index
                        if (data.remaining === 0) {
                            window.location.href = '<?= base_url('superadmin/kelola-admin-surveyprov') ?>';
                        } else {
                            // Reload halaman untuk update tampilan
                            window.location.reload();
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: data.message,
                        confirmButtonColor: '#3b82f6'
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Terjadi kesalahan saat menghapus assignment',
                    confirmButtonColor: '#3b82f6'
                });
            });
        }
    });
}

// Form validation untuk create
document.getElementById('assignForm')?.addEventListener('submit', function(e) {
    const sobatId = document.getElementById('sobat_id')?.value;
    const kegiatanDetail = document.getElementById('id_kegiatan_detail')?.value;
    
    if (!sobatId || !kegiatanDetail) {
        e.preventDefault();
        Swal.fire({
            icon: 'warning',
            title: 'Perhatian',
            text: 'Silakan lengkapi semua field yang diperlukan',
            confirmButtonColor: '#3b82f6'
        });
        return false;
    }
});

// Form validation untuk add kegiatan
document.getElementById('addKegiatanForm')?.addEventListener('submit', function(e) {
    const kegiatanDetail = document.getElementById('id_kegiatan_detail')?.value;
    
    if (!kegiatanDetail) {
        e.preventDefault();
        Swal.fire({
            icon: 'warning',
            title: 'Perhatian',
            text: 'Silakan pilih kegiatan detail',
            confirmButtonColor: '#3b82f6'
        });
        return false;
    }
});
</script>

<?= $this->endSection() ?>