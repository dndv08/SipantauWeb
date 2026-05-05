<?= $this->extend('layouts/sadmin_layout') ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="mb-6">
    <div class="flex items-center text-sm text-gray-600 mb-4">
        <a href="<?= base_url('superadmin/kelola-pengguna') ?>" class="hover:text-blue-600 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Kembali ke Kelola Pengguna
        </a>
    </div>
    <h1 class="text-2xl font-bold text-gray-900">Tambah Pengguna</h1>
    <p class="text-gray-600 mt-1">Buat akun pengguna baru untuk sistem SiPantau</p>
</div>

<!-- Form Card -->
<div class="card max-w-3xl">
    <form id="formPengguna" method="POST" action="<?= base_url('superadmin/kelola-pengguna/store') ?>">
        <?= csrf_field() ?>
        
        <!-- Error Messages -->
        <?php if (session()->getFlashdata('errors')): ?>
        <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex">
                <i class="fas fa-exclamation-circle text-red-600 mr-3 mt-0.5"></i>
                <div>
                    <p class="text-sm font-semibold text-red-800 mb-2">Terdapat kesalahan:</p>
                    <ul class="list-disc list-inside text-sm text-red-700">
                        <?php foreach (session()->getFlashdata('errors') as $error): ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Info Alert -->
        <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex">
                <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                <div class="text-sm text-blue-700">
                    <p class="font-medium mb-1">Informasi Penting:</p>
                    <ul class="list-disc list-inside space-y-1">
                        <li>Password otomatis sama dengan Sobat ID</li>
                        <li>Lengkapi semua field yang bertanda <span class="text-red-500 font-semibold">*</span></li>
                        <li>Pengguna dapat memiliki lebih dari satu role</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Informasi Personal -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Personal</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Sobat ID -->
                <div>
                    <label for="sobat_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Sobat ID <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="sobat_id" 
                           name="sobat_id" 
                           class="input-field" 
                           placeholder="Contoh: 3201010101"
                           value="<?= old('sobat_id') ?>"
                           required>
                    <p class="mt-1 text-xs text-gray-500">ID unik pegawai (akan menjadi password)</p>
                </div>
                
                <!-- Nama Lengkap -->
                <div>
                    <label for="nama_user" class="block text-sm font-medium text-gray-700 mb-2">
                        Nama Lengkap <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="nama_user" 
                           name="nama_user" 
                           class="input-field" 
                           placeholder="Contoh: Ahmad Hidayat"
                           value="<?= old('nama_user') ?>"
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
                           placeholder="contoh@bps.go.id"
                           value="<?= old('email') ?>"
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
                           placeholder="08xxxxxxxxxx"
                           pattern="[0-9]{10,13}"
                           value="<?= old('hp') ?>"
                           required>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 mt-4">
                <!-- Pegawai/Mitra -->
                <div>
                    <label for="is_pegawai" class="block text-sm font-medium text-gray-700 mb-2">
                        Pegawai/Mitra <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="is_pegawai" 
                        name="is_pegawai" 
                        class="input-field <?= session('errors.is_pegawai') ? 'border-red-500' : '' ?>" 
                        value="<?= old('is_pegawai') ?>">
                        <option value="">-- Pilih Pegawai/Mitra --</option>
                        <option value="1" <?= old('is_pegawai') == '1' ? 'selected' : '' ?>>Pegawai</option>
                        <option value="0" <?= old('is_pegawai') == '0' ? 'selected' : '' ?>>Mitra</option>
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
                        <option value="<?= $kab['id_kabupaten'] ?>" <?= old('id_kabupaten') == $kab['id_kabupaten'] ? 'selected' : '' ?>>
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
                        $oldRoles = old('roles') ?? [];
                        foreach ($roles as $role): 
                        $isChecked = in_array($role['id_roleuser'], $oldRoles);
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

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-3">
            <button type="button" 
                    onclick="window.location.href='<?= base_url('superadmin/kelola-pengguna') ?>'"
                    class="btn-secondary w-full sm:w-auto order-2 sm:order-1">
                <i class="fas fa-times mr-2"></i>Batal
            </button>
            <button type="button" 
                    onclick="resetForm()"
                    class="btn-secondary w-full sm:w-auto order-3 sm:order-2">
                <i class="fas fa-undo mr-2"></i>Reset
            </button>
            <button type="submit" 
                    class="btn-primary w-full sm:w-auto sm:ml-auto order-1 sm:order-3">
                <i class="fas fa-save mr-2"></i>Simpan Data
            </button>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.getElementById('formPengguna').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const sobatId = document.getElementById('sobat_id').value.trim();
    const nama = document.getElementById('nama_user').value.trim();
    const email = document.getElementById('email').value.trim();
    const hp = document.getElementById('hp').value.trim();
    const kabupaten = document.getElementById('id_kabupaten').value;
    const roles = document.querySelectorAll('input[name="roles[]"]:checked');
    
    if (!sobatId || !nama || !email || !hp || !kabupaten) {
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
    
    if (!(/^\d+$/.test(sobatId))) {
        Swal.fire({
            icon: 'error',
            title: 'Sobat ID Tidak Valid',
            text: 'Sobat ID harus berisi angka saja',
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
    
    const roleNames = Array.from(roles).map(r => r.nextElementSibling.textContent).join(', ');
    
    Swal.fire({
        title: 'Simpan Data Pengguna?',
        html: `
            <div class="text-left">
                <p class="mb-2">Data yang akan disimpan:</p>
                <div class="bg-gray-50 p-3 rounded text-sm">
                    <p><strong>Nama:</strong> ${nama}</p>
                    <p><strong>Email:</strong> ${email}</p>
                    <p><strong>Role:</strong> ${roleNames}</p>
                    <p><strong>Password:</strong> ${sobatId} (sama dengan Sobat ID)</p>
                </div>
            </div>
        `,
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
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('formPengguna').reset();
            Swal.fire({
                icon: 'success',
                title: 'Form Direset',
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
</script>

<?= $this->endSection() ?>