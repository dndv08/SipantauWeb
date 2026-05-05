<?= $this->extend('layouts/adminprov_layout') ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="mb-6">
    <div class="flex items-center text-sm text-gray-600 mb-4">
        <a href="<?= base_url('adminsurvei/admin-survei-kab') ?>" class="hover:text-blue-600 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Kembali
        </a>
    </div>
    <h1 class="text-2xl font-bold text-gray-900">Edit Assignment Admin Survei Kabupaten</h1>
    <p class="text-gray-600 mt-1">Perbarui assignment kegiatan wilayah untuk admin</p>
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

<!-- Form Card -->
<div class="card max-w-6xl">
    <form action="<?= base_url('adminsurvei/admin-survei-kab/update/' . $admin['id_admin_kabupaten']) ?>" 
          method="POST" id="assignForm">
        <?= csrf_field() ?>
        
        <!-- Admin Info (Read-only) -->
        <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-start">
                <div class="w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-user text-white text-lg"></i>
                </div>
                <div class="ml-4 flex-1">
                    <p class="text-sm font-medium text-gray-900"><?= esc($admin['nama_user']) ?></p>
                    <p class="text-xs text-gray-600 mt-1"><?= esc($admin['email']) ?> • <?= esc($admin['hp']) ?></p>
                    <p class="text-xs text-gray-600 mt-1">
                        <i class="fas fa-map-marker-alt mr-1"></i>
                        <?= esc($admin['nama_kabupaten']) ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Pilih Kegiatan Wilayah -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Pilih Kegiatan Wilayah <span class="text-red-500">*</span>
            </label>
            
            <?php
            // Filter kegiatan berdasarkan kabupaten admin
            $filteredKegiatan = array_filter($kegiatan_wilayah, function($k) use ($admin) {
                return $k['id_kabupaten'] == $admin['id_kabupaten'];
            });
            ?>
            
            <?php if (empty($filteredKegiatan)): ?>
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-triangle text-yellow-600 mr-3"></i>
                        <p class="text-sm text-yellow-700">Belum ada kegiatan wilayah yang tersedia untuk kabupaten ini</p>
                    </div>
                </div>
            <?php else: ?>
                <!-- Search Box -->
                <div class="relative mb-3">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" id="searchKegiatan"
                        class="input-field w-full pl-10"
                        placeholder="Cari kegiatan wilayah..."
                        onkeyup="filterKegiatan()">
                </div>

                <p class="text-sm text-gray-500 mb-3">
                    <i class="fas fa-info-circle mr-1"></i>
                    Pilih kegiatan wilayah yang akan di-assign ke admin
                </p>
                
                <!-- Kegiatan List dengan Checkbox -->
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div id="kegiatanList" class="divide-y divide-gray-100" style="max-height: 400px; overflow-y: auto;">
                        <?php foreach ($filteredKegiatan as $kegiatan): ?>
                            <label class="kegiatan-item flex items-start p-3 hover:bg-gray-50 cursor-pointer"
                                   data-kegiatan-text="<?= strtolower(esc($kegiatan['nama_kegiatan_detail_proses'] . ' ' . $kegiatan['nama_kegiatan'] . ' ' . $kegiatan['nama_kegiatan_detail'])) ?>">
                                <input type="checkbox" 
                                       name="kegiatan_wilayah[]" 
                                       value="<?= $kegiatan['id_kegiatan_wilayah'] ?>"
                                       <?= in_array($kegiatan['id_kegiatan_wilayah'], $assigned_ids) ? 'checked' : '' ?>
                                       onchange="updateSelectedCount()"
                                       class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <div class="ml-3 flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900">
                                        <?= esc($kegiatan['nama_kegiatan_detail_proses']) ?>
                                    </p>
                                    <p class="text-xs text-gray-600 mt-1">
                                        <?= esc($kegiatan['nama_kegiatan']) ?> - <?= esc($kegiatan['nama_kegiatan_detail']) ?>
                                    </p>
                                    <div class="flex flex-wrap gap-1.5 mt-2">
                                        <span class="inline-flex items-center px-2 py-0.5 bg-white border border-gray-300 text-gray-700 rounded text-xs">
                                            <i class="fas fa-bullseye mr-1 text-gray-500"></i>
                                            Target: <?= number_format($kegiatan['target_wilayah']) ?>
                                        </span>
                                        <span class="inline-flex items-center px-2 py-0.5 bg-white border border-gray-300 text-gray-700 rounded text-xs">
                                            <i class="fas fa-calendar mr-1 text-gray-500"></i>
                                            <?= date('d M Y', strtotime($kegiatan['tanggal_mulai'])) ?> - <?= date('d M Y', strtotime($kegiatan['tanggal_selesai'])) ?>
                                        </span>
                                    </div>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Selected Count -->
                <div class="mt-3 flex items-center justify-between text-sm">
                    <span class="text-gray-600">
                        <span id="selectedCount">0</span> kegiatan wilayah terpilih
                    </span>
                    <button type="button" onclick="clearSelection()" class="text-red-600 hover:text-red-700">
                        <i class="fas fa-times-circle mr-1"></i>
                        Clear All
                    </button>
                </div>
            <?php endif; ?>
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-3 pt-4 border-t border-gray-200">
            <a href="<?= base_url('adminsurvei/admin-survei-kab') ?>" 
               class="btn-secondary flex-1 text-center">
                <i class="fas fa-times mr-2"></i>
                Batal
            </a>
            <button type="submit" 
                    class="btn-primary flex-1"
                    id="submitBtn">
                <i class="fas fa-save mr-2"></i>
                Update Assignment
            </button>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
const assignedIds = <?= json_encode($assigned_ids) ?>;

// Filter kegiatan by search
function filterKegiatan() {
    const searchValue = document.getElementById('searchKegiatan').value.toLowerCase();
    const items = document.querySelectorAll('.kegiatan-item');
    let visibleCount = 0;
    
    items.forEach(item => {
        const text = item.dataset.kegiatanText;
        if (text.includes(searchValue)) {
            item.style.display = '';
            visibleCount++;
        } else {
            item.style.display = 'none';
        }
    });
    
    // Show no results message
    const kegiatanList = document.getElementById('kegiatanList');
    let noResultAlert = document.getElementById('noKegiatanResultAlert');
    
    if (visibleCount === 0 && searchValue !== '') {
        if (!noResultAlert) {
            noResultAlert = document.createElement('div');
            noResultAlert.id = 'noKegiatanResultAlert';
            noResultAlert.className = 'p-4 text-center';
            noResultAlert.innerHTML = `
                <div class="text-gray-400 mb-2">
                    <i class="fas fa-search-minus text-3xl"></i>
                </div>
                <p class="text-sm text-gray-600">Kegiatan tidak ditemukan</p>
                <p class="text-xs text-gray-500 mt-1">Coba kata kunci lain</p>
            `;
            kegiatanList.appendChild(noResultAlert);
        }
        noResultAlert.style.display = 'block';
    } else {
        if (noResultAlert) {
            noResultAlert.style.display = 'none';
        }
    }
}

// Update selected count
function updateSelectedCount() {
    const checkboxes = document.querySelectorAll('input[name="kegiatan_wilayah[]"]:checked');
    const countElement = document.getElementById('selectedCount');
    const submitBtn = document.getElementById('submitBtn');
    
    if (countElement) {
        countElement.textContent = checkboxes.length;
    }
    
    // Enable/disable submit button
    submitBtn.disabled = checkboxes.length === 0;
}

// Clear all selection
function clearSelection() {
    const checkboxes = document.querySelectorAll('input[name="kegiatan_wilayah[]"]');
    checkboxes.forEach(cb => cb.checked = false);
    updateSelectedCount();
    
    Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: 'Semua pilihan telah direset',
        timer: 1500,
        showConfirmButton: false
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateSelectedCount();
});

// Form validation
document.getElementById('assignForm')?.addEventListener('submit', function(e) {
    const kegiatanChecked = document.querySelectorAll('input[name="kegiatan_wilayah[]"]:checked');
    if (kegiatanChecked.length === 0) {
        e.preventDefault();
        Swal.fire({
            icon: 'warning',
            title: 'Perhatian',
            text: 'Silakan pilih minimal satu kegiatan wilayah',
            confirmButtonColor: '#3b82f6'
        });
        return false;
    }
});
</script>

<?= $this->endSection() ?>