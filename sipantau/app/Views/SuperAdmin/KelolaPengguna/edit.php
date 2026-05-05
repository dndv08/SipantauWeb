<?= $this->extend('layouts/sadmin_layout') ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="mb-6">
    <div class="flex items-center text-sm text-gray-600 mb-4">
        <a href="<?= base_url('superadmin/kelola-pengguna') ?>" class="hover:text-blue-600 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Kembali ke Kelola Pengguna
        </a>
    </div>
    <h1 class="text-2xl font-bold text-gray-900">Edit Pengguna</h1>
    <p class="text-gray-600 mt-1">Perbarui informasi pengguna sistem SiPantau</p>
</div>

<!-- Form Card -->
<div class="card max-w-3xl">
    <form id="formPengguna" method="POST" action="<?= base_url('superadmin/kelola-pengguna/update/' . $user['sobat_id']) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="_method" value="PUT">
        
        <!-- Info Alert -->
        <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex">
                <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                <p class="text-sm text-blue-700">
                    Anda sedang mengedit data: <strong><?= esc($user['nama_user']) ?></strong>
                </p>
            </div>
        </div>

        <!-- Informasi Personal -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Personal</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Nama Lengkap -->
                <div>
                    <label for="nama_user" class="block text-sm font-medium text-gray-700 mb-2">
                        Nama Lengkap <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="nama_user" 
                           name="nama_user" 
                           class="input-field" 
                           value="<?= esc($user['nama_user']) ?>"
                           required>
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email <span class="text-red-500">*</span>
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           class="input-field" 
                           value="<?= esc($user['email']) ?>"
                           required>
                </div>

                <!-- No HP -->
                <div>
                    <label for="hp" class="block text-sm font-medium text-gray-700 mb-2">
                        No HP <span class="text-red-500">*</span>
                    </label>
                    <input type="tel" 
                           id="hp" 
                           name="hp" 
                           class="input-field" 
                           value="<?= esc($user['hp']) ?>"
                           pattern="[0-9]{10,13}"
                           required>
                </div>

                <!-- Sobat ID (Read Only) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Sobat ID
                    </label>
                    <input type="text" 
                           class="input-field bg-gray-100" 
                           value="<?= esc($user['sobat_id']) ?>"
                           readonly>
                    <p class="mt-1 text-xs text-gray-500">Tidak dapat diubah</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 mt-4">
                <!-- Pegawai/Mitra -->
                <div>
                    <label for="is_pegawai" class="block text-sm font-medium text-gray-700 mb-2">
                        Pegawai/Mitra <span class="text-red-500">*</span>
                    </label>
                    <?php $selectedIsPegawai = (string) old('is_pegawai', (string) $user['is_pegawai']); ?>
                    <select 
                        id="is_pegawai" 
                        name="is_pegawai" 
                        class="input-field <?= session('errors.is_pegawai') ? 'border-red-500' : '' ?>" required>
                        <option value="" disabled <?= $selectedIsPegawai === '' ? 'selected' : '' ?>>-- Pilih Status --</option>
                        <option value="1" <?= $selectedIsPegawai === '1' ? 'selected' : '' ?>>Pegawai</option>
                        <option value="0" <?= $selectedIsPegawai === '0' ? 'selected' : '' ?>>Mitra</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Divider -->
        <div class="border-t border-gray-200 my-6"></div>

        <!-- Informasi Wilayah & Role -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Wilayah & Role</h3>
            
            <div class="grid grid-cols-1 gap-4">
                <!-- Kabupaten/Kota -->
                <div>
                    <label for="id_kabupaten" class="block text-sm font-medium text-gray-700 mb-2">
                        Kabupaten/Kota <span class="text-red-500">*</span>
                    </label>
                    <select id="id_kabupaten" name="id_kabupaten" class="input-field" required>
                        <option value="">-- Pilih Kabupaten/Kota --</option>
                        <?php foreach ($kabupaten as $kab): ?>
                        <option value="<?= $kab['id_kabupaten'] ?>" <?= $user['id_kabupaten'] == $kab['id_kabupaten'] ? 'selected' : '' ?>>
                            <?= esc($kab['nama_kabupaten']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Role Multi-Select -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Role(s) <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2 p-3 border border-gray-300 rounded-lg bg-gray-50">
                        <?php 
                        $userRoles = !empty($user['role_ids']) ? $user['role_ids'] : [];
                        foreach ($roles as $role): 
                        $isChecked = in_array($role['id_roleuser'], $userRoles);
                        ?>
                        <label class="flex items-center p-2 hover:bg-white rounded cursor-pointer transition-colors">
                            <input type="checkbox" 
                                   name="roles[]" 
                                   value="<?= $role['id_roleuser'] ?>"
                                   <?= $isChecked ? 'checked' : '' ?>
                                   class="mr-2 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <span class="text-sm text-gray-700"><?= esc($role['roleuser']) ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    <p class="mt-2 text-xs text-gray-500">
                        <i class="fas fa-info-circle mr-1"></i>
                        Pilih satu atau lebih role untuk pengguna
                    </p>
                </div>
            </div>
        </div>

        <!-- Divider -->
        <div class="border-t border-gray-200 my-6"></div>

        <!-- Ubah Password (Optional) -->
        <div class="mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Ubah Password</h3>
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" id="changePassword" class="mr-2">
                    <span class="text-sm text-gray-600">Ubah password</span>
                </label>
            </div>
            
            <div id="passwordFields" class="hidden space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Password Baru -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            Password Baru
                        </label>
                        <div class="relative">
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   class="input-field pr-10" 
                                   placeholder="Minimal 8 karakter"
                                   minlength="8"
                                   disabled>
                            <button type="button" 
                                    onclick="togglePassword('password')"
                                    class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-600 hover:text-gray-900">
                                <i class="fas fa-eye" id="password-icon"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Konfirmasi Password -->
                    <div>
                        <label for="password_confirm" class="block text-sm font-medium text-gray-700 mb-2">
                            Konfirmasi Password
                        </label>
                        <div class="relative">
                            <input type="password" 
                                   id="password_confirm" 
                                   name="password_confirm" 
                                   class="input-field pr-10" 
                                   placeholder="Ulangi password"
                                   minlength="8"
                                   disabled>
                            <button type="button" 
                                    onclick="togglePassword('password_confirm')"
                                    class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-600 hover:text-gray-900">
                                <i class="fas fa-eye" id="password_confirm-icon"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                    <div class="flex">
                        <i class="fas fa-exclamation-triangle text-yellow-600 mt-0.5 mr-2"></i>
                        <p class="text-sm text-yellow-700">
                            Kosongkan field password jika tidak ingin mengubah password
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Divider -->
        <div class="border-t border-gray-200 my-6"></div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-3">
            <button type="button" 
                    onclick="window.location.href='<?= base_url('superadmin/kelola-pengguna') ?>'"
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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
const originalValues = {
    nama_user: '<?= esc($user['nama_user']) ?>',
    email: '<?= esc($user['email']) ?>',
    hp: '<?= esc($user['hp']) ?>',
    id_kabupaten: '<?= $user['id_kabupaten'] ?>',
    is_pegawai: '<?= (string) $user['is_pegawai'] ?>', 
    roles: <?= json_encode($user['role_ids']) ?>
};

function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '-icon');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

document.getElementById('changePassword').addEventListener('change', function() {
    const passwordFields = document.getElementById('passwordFields');
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('password_confirm');
    
    if (this.checked) {
        passwordFields.classList.remove('hidden');
        passwordInput.disabled = false;
        passwordInput.required = true;
        confirmInput.disabled = false;
        confirmInput.required = true;
    } else {
        passwordFields.classList.add('hidden');
        passwordInput.disabled = true;
        passwordInput.required = false;
        passwordInput.value = '';
        confirmInput.disabled = true;
        confirmInput.required = false;
        confirmInput.value = '';
    }
});

document.getElementById('formPengguna').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const nama = document.getElementById('nama_user').value.trim();
    const email = document.getElementById('email').value.trim();
    const hp = document.getElementById('hp').value.trim();
    const kabupaten = document.getElementById('id_kabupaten').value;
    const roles = document.querySelectorAll('input[name="roles[]"]:checked');
    const isPegawai = document.getElementById('is_pegawai').value; 
    const changePassword = document.getElementById('changePassword').checked;
    const password = document.getElementById('password').value;
    const passwordConfirm = document.getElementById('password_confirm').value;
    
    if (!nama || !email || !hp || !kabupaten) {
        Swal.fire({
            icon: 'error',
            title: 'Form Tidak Lengkap',
            text: 'Harap lengkapi semua field yang wajib diisi!',
            confirmButtonColor: '#3b82f6'
        });
        return;
    }
    
    if (roles.length === 0) {
        Swal.fire({
            icon: 'error',
            title: 'Role Belum Dipilih',
            text: 'Pilih minimal satu role untuk pengguna!',
            confirmButtonColor: '#3b82f6'
        });
        return;
    }
    
    const selectedRoles = Array.from(roles).map(r => parseInt(r.value));
    const hasChanges = 
        nama !== originalValues.nama_user || 
        email !== originalValues.email || 
        hp !== originalValues.hp ||
        kabupaten !== originalValues.id_kabupaten.toString() ||
        isPegawai !== originalValues.is_pegawai ||  
        JSON.stringify(selectedRoles.sort()) !== JSON.stringify(originalValues.roles.sort()) ||
        changePassword;
    
    if (!hasChanges) {
        Swal.fire({
            icon: 'info',
            title: 'Tidak Ada Perubahan',
            text: 'Tidak ada data yang diubah',
            confirmButtonColor: '#3b82f6'
        });
        return;
    }
    
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        Swal.fire({
            icon: 'error',
            title: 'Email Tidak Valid',
            text: 'Format email tidak sesuai',
            confirmButtonColor: '#3b82f6'
        });
        return;
    }
    
    const phoneRegex = /^[0-9]{10,13}$/;
    if (!phoneRegex.test(hp)) {
        Swal.fire({
            icon: 'error',
            title: 'No HP Tidak Valid',
            text: 'No HP harus berisi 10-13 digit angka',
            confirmButtonColor: '#3b82f6'
        });
        return;
    }
    
    if (changePassword) {
        if (password.length < 8) {
            Swal.fire({
                icon: 'error',
                title: 'Password Terlalu Pendek',
                text: 'Password minimal 8 karakter',
                confirmButtonColor: '#3b82f6'
            });
            return;
        }
        
        if (password !== passwordConfirm) {
            Swal.fire({
                icon: 'error',
                title: 'Password Tidak Cocok',
                text: 'Password dan Konfirmasi Password harus sama',
                confirmButtonColor: '#3b82f6'
            });
            return;
        }
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
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            this.submit();
        }
    });
});

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
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('nama_user').value = originalValues.nama_user;
            document.getElementById('email').value = originalValues.email;
            document.getElementById('hp').value = originalValues.hp;
            document.getElementById('id_kabupaten').value = originalValues.id_kabupaten;
            
            // Reset checkboxes
            document.querySelectorAll('input[name="roles[]"]').forEach(checkbox => {
                checkbox.checked = originalValues.roles.includes(parseInt(checkbox.value));
            });
            
            document.getElementById('changePassword').checked = false;
            document.getElementById('passwordFields').classList.add('hidden');
            document.getElementById('password').value = '';
            document.getElementById('password_confirm').value = '';
            document.getElementById('password').disabled = true;
            document.getElementById('password_confirm').disabled = true;
            
            Swal.fire({
                icon: 'success',
                title: 'Data Dikembalikan',
                timer: 1500,
                showConfirmButton: false
            });
        }
    });
}

document.getElementById('nama_user').addEventListener('blur', function() {
    if (this.value) {
        this.value = this.value.split(' ').map(word => 
            word.charAt(0).toUpperCase() + word.slice(1).toLowerCase()
        ).join(' ');
    }
});

document.getElementById('password_confirm').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirm = this.value;
    
    if (confirm && password !== confirm) {
        this.classList.add('border-red-500');
        this.classList.remove('border-gray-300');
    } else {
        this.classList.remove('border-red-500');
        this.classList.add('border-gray-300');
    }
});
</script>

<?= $this->endSection() ?>