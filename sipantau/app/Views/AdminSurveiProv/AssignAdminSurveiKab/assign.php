<?= $this->extend('layouts/adminprov_layout') ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="mb-6">
    <div class="flex items-center text-sm text-gray-600 mb-4">
        <a href="<?= base_url('adminsurvei/admin-survei-kab') ?>" class="hover:text-blue-600 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Kembali
        </a>
    </div>
    <h1 class="text-2xl font-bold text-gray-900">
        <?= $is_edit ? 'Edit Assignment Admin Survei Kabupaten' : 'Assign Admin Survei Kabupaten' ?>
    </h1>
    <p class="text-gray-600 mt-1">
        <?= $is_edit ? 'Perbarui assignment kegiatan wilayah untuk admin' : 'Pilih kabupaten terlebih dahulu, lalu assign admin ke kegiatan wilayah' ?>
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

<!-- Form Card -->
<div class="card max-w-6xl">
    <form action="<?= $is_edit ? base_url('adminsurvei/admin-survei-kab/update/' . $admin['id_admin_kabupaten']) : base_url('adminsurvei/admin-survei-kab/store') ?>" 
          method="POST" id="assignForm">
        <?= csrf_field() ?>
        
        <?php if ($is_edit): ?>
            <!-- Admin Info (Read-only untuk edit) -->
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
        <?php else: ?>
            <!-- Step 1: Pilih Kabupaten -->
            <div class="mb-6">
                <?php if (empty($allKabupaten)): ?>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <span class="inline-flex items-center justify-center w-6 h-6 bg-blue-600 text-white rounded-full text-xs mr-2">1</span>
                        Pilih Kabupaten <span class="text-red-500">*</span>
                    </label>
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-triangle text-yellow-600 mr-3"></i>
                            <p class="text-sm text-yellow-700">Tidak ada kabupaten yang tersedia</p>
                        </div>
                    </div>
                <?php else: ?>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <span class="inline-flex items-center justify-center w-6 h-6 bg-blue-600 text-white rounded-full text-xs mr-2">1</span>
                        Pilih Kabupaten <span class="text-red-500">*</span>
                    </label>
                    <?= view('components/select_component', [
                        'name' => 'kabupaten_filter',
                        'id' => 'filterKabupaten',
                        'required' => true,
                        'placeholder' => 'Cari dan pilih kabupaten...',
                        'options' => $allKabupaten,
                        'optionValue' => 'id_kabupaten',
                        'optionText' => 'nama_kabupaten',
                        'onchange' => 'handleKabupatenChange()',
                        'emptyMessage' => 'Tidak ada kabupaten yang tersedia',
                        'enableSearch' => true
                    ]) ?>
                <?php endif; ?>
            </div>

            <!-- Step 2: Pilih User (Hidden until kabupaten selected) -->
            <div id="userSection" class="mb-6" style="display: none;">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <span class="inline-flex items-center justify-center w-6 h-6 bg-blue-600 text-white rounded-full text-xs mr-2">2</span>
                    Pilih User <span class="text-red-500">*</span>
                </label>
                
                <!-- Search Box untuk User -->
                <div class="relative mb-3">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" id="searchUser"
                        class="input-field w-full pl-10"
                        placeholder="Cari user berdasarkan nama, email, atau sobat ID..."
                        onkeyup="filterUsers()">
                </div>
                
                <div id="userListContainer">
                    <!-- User options will be loaded here -->
                </div>
            </div>
        <?php endif; ?>

        <!-- Step 3: Pilih Kegiatan Wilayah (Hidden until user selected in create mode, or visible in edit mode) -->
        <div id="kegiatanSection" class="mb-6" <?= $is_edit ? '' : 'style="display: none;"' ?>>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                <span class="inline-flex items-center justify-center w-6 h-6 bg-blue-600 text-white rounded-full text-xs mr-2"><?= $is_edit ? '' : '3' ?></span>
                Pilih Kegiatan Wilayah <span class="text-red-500">*</span>
            </label>
            
            <div id="kegiatanListContainer">
                <?php if ($is_edit): ?>
                    <!-- For edit mode, show kegiatan based on admin's kabupaten -->
                    <?php
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
                        
                        <!-- Kegiatan List dengan Checkbox (Max 2 items visible, scrollable) -->
                        <div class="border border-gray-200 rounded-lg overflow-hidden">
                            <div id="kegiatanList" class="divide-y divide-gray-100" style="max-height: 240px; overflow-y: auto;">
                                <?php foreach ($filteredKegiatan as $kegiatan): ?>
                                    <label class="kegiatan-item flex items-start p-3 hover:bg-gray-50 cursor-pointer"
                                           data-kegiatan-text="<?= strtolower(esc($kegiatan['nama_kegiatan_detail_proses'] . ' ' . $kegiatan['nama_kegiatan'] . ' ' . $kegiatan['nama_kegiatan_detail'])) ?>">
                                        <input type="checkbox" 
                                               name="kegiatan_wilayah[]" 
                                               value="<?= $kegiatan['id_kegiatan_wilayah'] ?>"
                                               <?= in_array($kegiatan['id_kegiatan_wilayah'], $assigned_ids) ? 'checked' : '' ?>
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
                <?php else: ?>
                    <!-- For create mode, kegiatan will be loaded dynamically -->
                    <div class="text-center text-gray-500 py-8">
                        <i class="fas fa-tasks text-4xl mb-2"></i>
                        <p>Pilih user terlebih dahulu untuk melihat kegiatan wilayah</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-3 pt-4 border-t border-gray-200">
            <button type="button" 
                    onclick="clearSelection(); return false;"
                    class="btn-secondary flex-1">
                <i class="fas fa-redo mr-2"></i>
                Reset Semua
            </button>
            <a href="<?= base_url('adminsurvei/admin-survei-kab') ?>" 
               class="btn-secondary flex-1 text-center">
                <i class="fas fa-times mr-2"></i>
                Batal
            </a>
            <button type="submit" 
                    class="btn-primary flex-1"
                    id="submitBtn"
                    disabled>
                <i class="fas fa-save mr-2"></i>
                <?= $is_edit ? 'Update Assignment' : 'Simpan Assignment' ?>
            </button>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Data dari PHP
const allUsers = <?= json_encode($users) ?>;
const allKegiatan = <?= json_encode($kegiatan_wilayah) ?>;
const isEdit = <?= $is_edit ? 'true' : 'false' ?>;
const assignedIds = <?= json_encode($assigned_ids) ?>;

let selectedKabupaten = null;
let selectedUser = null;

// Handle kabupaten change
function handleKabupatenChange() {
    const kabupatenSelect = document.getElementById('filterKabupaten');
    selectedKabupaten = kabupatenSelect.value;
    
    if (selectedKabupaten) {
        loadUsersByKabupaten(selectedKabupaten);
        document.getElementById('userSection').style.display = 'block';
        document.getElementById('kegiatanSection').style.display = 'none';
        document.getElementById('submitBtn').disabled = true;
    } else {
        document.getElementById('userSection').style.display = 'none';
        document.getElementById('kegiatanSection').style.display = 'none';
        selectedUser = null;
    }
}

// Load users by kabupaten
function loadUsersByKabupaten(idKabupaten) {
    const filteredUsers = allUsers.filter(u => u.id_kabupaten == idKabupaten);
    const container = document.getElementById('userListContainer');
    
    if (filteredUsers.length === 0) {
        container.innerHTML = `
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle text-yellow-600 mr-3"></i>
                    <p class="text-sm text-yellow-700">Tidak ada user yang tersedia untuk kabupaten ini</p>
                </div>
            </div>
        `;
        return;
    }
    
    let html = '<div class="border border-gray-200 rounded-lg overflow-hidden"><div id="userList" class="divide-y divide-gray-100" style="max-height: 190px; overflow-y: auto;">';
    filteredUsers.forEach(user => {
        html += `
            <label class="user-item flex items-start p-3 hover:bg-gray-50 cursor-pointer"
                   data-user-text="${escapeHtml(user.nama_user.toLowerCase() + ' ' + user.email.toLowerCase() + ' ' + user.sobat_id.toLowerCase())}"
                   data-assigned-kegiatan='${JSON.stringify(user.assigned_kegiatan_ids || [])}'>
                <input type="radio" 
                       name="sobat_id" 
                       value="${user.sobat_id}"
                       onchange="handleUserSelect()"
                       class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                <div class="ml-3 flex-1">
                    <p class="text-sm font-medium text-gray-900">${escapeHtml(user.nama_user)}</p>
                    <p class="text-xs text-gray-600 mt-1">${escapeHtml(user.email)}</p>
                    <p class="text-xs text-gray-500 mt-0.5">
                        <i class="fas fa-id-card mr-1"></i>
                        Sobat ID: ${escapeHtml(user.sobat_id)}
                    </p>
                </div>
            </label>
        `;
    });
    html += '</div></div>';
    
    container.innerHTML = html;
}

// Filter users by search
function filterUsers() {
    const searchValue = document.getElementById('searchUser').value.toLowerCase();
    const items = document.querySelectorAll('.user-item');
    let visibleCount = 0;
    
    items.forEach(item => {
        const text = item.dataset.userText;
        if (text.includes(searchValue)) {
            item.style.display = '';
            visibleCount++;
        } else {
            item.style.display = 'none';
        }
    });
    
    // Show alert if no users found
    const userList = document.getElementById('userList');
    let noResultAlert = document.getElementById('noUserResultAlert');
    
    if (visibleCount === 0 && searchValue !== '') {
        if (!noResultAlert) {
            noResultAlert = document.createElement('div');
            noResultAlert.id = 'noUserResultAlert';
            noResultAlert.className = 'p-4 text-center';
            noResultAlert.innerHTML = `
                <div class="text-gray-400 mb-2">
                    <i class="fas fa-user-slash text-3xl"></i>
                </div>
                <p class="text-sm text-gray-600">User tidak ditemukan</p>
                <p class="text-xs text-gray-500 mt-1">Coba kata kunci lain</p>
            `;
            userList.appendChild(noResultAlert);
        }
        noResultAlert.style.display = 'block';
    } else {
        if (noResultAlert) {
            noResultAlert.style.display = 'none';
        }
    }
}

// Handle user selection
function handleUserSelect() {
    const selectedRadio = document.querySelector('input[name="sobat_id"]:checked');
    if (selectedRadio) {
        selectedUser = selectedRadio.value;
        
        // Get assigned kegiatan from data attribute
        const userLabel = selectedRadio.closest('.user-item');
        const assignedKegiatanIds = JSON.parse(userLabel.dataset.assignedKegiatan || '[]');
        
        loadKegiatanByKabupaten(selectedKabupaten, assignedKegiatanIds);
        document.getElementById('kegiatanSection').style.display = 'block';
    }
}

// Load kegiatan by kabupaten (exclude already assigned to selected user)
function loadKegiatanByKabupaten(idKabupaten, assignedKegiatanIds) {
    console.log('Kabupaten ID:', idKabupaten);
    console.log('Assigned Kegiatan IDs:', assignedKegiatanIds);
    
    const filteredKegiatan = allKegiatan.filter(k => {
        // Convert to string for comparison
        const kegiatanId = k.id_kegiatan_wilayah.toString();
        const isInKabupaten = k.id_kabupaten == idKabupaten;
        const isNotAssigned = !assignedKegiatanIds.map(String).includes(kegiatanId);
        
        return isInKabupaten && isNotAssigned;
    });
    
    console.log('Filtered Kegiatan:', filteredKegiatan);
    renderKegiatanList(filteredKegiatan);
}

// Render kegiatan list
function renderKegiatanList(filteredKegiatan) {
    const container = document.getElementById('kegiatanListContainer');
    
    if (filteredKegiatan.length === 0) {
        container.innerHTML = `
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle text-yellow-600 mr-3"></i>
                    <p class="text-sm text-yellow-700">Semua kegiatan wilayah sudah di-assign ke user ini atau belum ada kegiatan yang tersedia untuk kabupaten ini</p>
                </div>
            </div>
        `;
        document.getElementById('submitBtn').disabled = true;
        return;
    }
    
    let html = `
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
        
        <!-- Kegiatan List dengan Checkbox (Max 2 items visible, scrollable) -->
        <div class="border border-gray-200 rounded-lg overflow-hidden">
            <div id="kegiatanList" class="divide-y divide-gray-100" style="max-height: 240px; overflow-y: auto;">
    `;
    
    filteredKegiatan.forEach(kegiatan => {
        html += `
            <label class="kegiatan-item flex items-start p-3 hover:bg-gray-50 cursor-pointer"
                   data-kegiatan-text="${escapeHtml(kegiatan.nama_kegiatan_detail_proses.toLowerCase() + ' ' + kegiatan.nama_kegiatan.toLowerCase() + ' ' + kegiatan.nama_kegiatan_detail.toLowerCase())}">
                <input type="checkbox" 
                       name="kegiatan_wilayah[]" 
                       value="${kegiatan.id_kegiatan_wilayah}"
                       onchange="updateSelectedCount()"
                       class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                <div class="ml-3 flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900">
                        ${escapeHtml(kegiatan.nama_kegiatan_detail_proses)}
                    </p>
                    <p class="text-xs text-gray-600 mt-1">
                        ${escapeHtml(kegiatan.nama_kegiatan)} - ${escapeHtml(kegiatan.nama_kegiatan_detail)}
                    </p>
                    <div class="flex flex-wrap gap-1.5 mt-2">
                        <span class="inline-flex items-center px-2 py-0.5 bg-white border border-gray-300 text-gray-700 rounded text-xs">
                            <i class="fas fa-bullseye mr-1 text-gray-500"></i>
                            Target: ${formatNumber(kegiatan.target_wilayah)}
                        </span>
                        <span class="inline-flex items-center px-2 py-0.5 bg-white border border-gray-300 text-gray-700 rounded text-xs">
                            <i class="fas fa-calendar mr-1 text-gray-500"></i>
                            ${formatDate(kegiatan.tanggal_mulai)} - ${formatDate(kegiatan.tanggal_selesai)}
                        </span>
                    </div>
                </div>
            </label>
        `;
    });
    
    html += `
            </div>
        </div>

        <!-- Selected Count -->
        <div class="mt-3 flex items-center justify-between text-sm">
            <span class="text-gray-600">
                <span id="selectedCount">0</span> kegiatan wilayah terpilih
            </span>
            <button type="button" onclick="clearSelection(); return false;" class="text-red-600 hover:text-red-700">
                <i class="fas fa-times-circle mr-1"></i>
                Clear All
            </button>
        </div>
    `;
    
    container.innerHTML = html;
    updateSelectedCount();
}

// Filter kegiatan by search
function filterKegiatan() {
    const searchValue = document.getElementById('searchKegiatan').value.toLowerCase();
    const items = document.querySelectorAll('.kegiatan-item');
    
    items.forEach(item => {
        const text = item.dataset.kegiatanText;
        if (text.includes(searchValue)) {
            item.style.display = '';
        } else {
            item.style.display = 'none';
        }
    });
}

// Update selected count
function updateSelectedCount() {
    const checkboxes = document.querySelectorAll('input[name="kegiatan_wilayah[]"]:checked');
    const countElement = document.getElementById('selectedCount');
    const submitBtn = document.getElementById('submitBtn');
    
    if (countElement) {
        countElement.textContent = checkboxes.length;
    }
    
    // Enable submit button only if at least one kegiatan selected
    if (isEdit) {
        submitBtn.disabled = checkboxes.length === 0;
    } else {
        submitBtn.disabled = !selectedUser || checkboxes.length === 0;
    }
}

// Clear all selection
function clearSelection() {
    // Clear kegiatan checkboxes
    const checkboxes = document.querySelectorAll('input[name="kegiatan_wilayah[]"]');
    checkboxes.forEach(cb => cb.checked = false);
    
    // Clear user selection
    const userRadios = document.querySelectorAll('input[name="sobat_id"]');
    userRadios.forEach(radio => radio.checked = false);
    
    // Clear kabupaten selection (Select2)
    const kabupatenSelect = document.getElementById('filterKabupaten');
    if (kabupatenSelect) {
        // If using Select2
        if ($(kabupatenSelect).data('select2')) {
            $(kabupatenSelect).val('').trigger('change');
        } else {
            // Regular select
            kabupatenSelect.value = '';
        }
    }
    
    // Hide user and kegiatan sections
    document.getElementById('userSection').style.display = 'none';
    document.getElementById('kegiatanSection').style.display = 'none';
    
    // Reset variables
    selectedKabupaten = null;
    selectedUser = null;
    
    // Update count and button state
    updateSelectedCount();
    document.getElementById('submitBtn').disabled = true;
    
    // Show success message
    Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: 'Semua pilihan telah direset',
        timer: 1500,
        showConfirmButton: false
    });
}

// Listen to checkbox changes
document.addEventListener('change', function(e) {
    if (e.target.name === 'kegiatan_wilayah[]') {
        updateSelectedCount();
    }
});

// Helper functions
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

function formatNumber(num) {
    return new Intl.NumberFormat('id-ID').format(num);
}

function formatDate(dateStr) {
    const date = new Date(dateStr);
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    return `${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear()}`;
}

// Initialize for edit mode
document.addEventListener('DOMContentLoaded', function() {
    if (isEdit) {
        updateSelectedCount();
    }
    
    // Initialize Select2 with autofocus on open
    $(document).on('select2:open', function(e) {
        const selectId = e.target.id;
        const searchField = document.querySelector(
            '.select2-search__field'
        );
        if (searchField) {
            searchField.focus();
        }
    });
});

// Form validation
document.getElementById('assignForm')?.addEventListener('submit', function(e) {
    if (!isEdit) {
        const sobatId = document.querySelector('input[name="sobat_id"]:checked');
        if (!sobatId) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: 'Silakan pilih user terlebih dahulu',
                confirmButtonColor: '#3b82f6'
            });
            return false;
        }
    }
    
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