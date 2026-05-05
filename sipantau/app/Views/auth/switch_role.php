<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ganti Role - SiPantau</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            
            <!-- Card -->
            <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
                
                <!-- Header -->
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-6 text-center">
                    <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-exchange-alt text-4xl text-blue-600"></i>
                    </div>
                    <h1 class="text-2xl font-bold text-white mb-2">Ganti Role</h1>
                    <p class="text-blue-100 text-sm">
                        <?php
                        $currentTypeName = '';
                        switch ($current_role_type ?? 'default') {
                            case 'admin_provinsi':
                                $currentTypeName = 'Admin Survei Provinsi';
                                break;
                            case 'pemantau_provinsi':
                                $currentTypeName = 'Pemantau Provinsi';
                                break;
                            case 'admin_kabupaten':
                                $currentTypeName = 'Admin Survei Kabupaten';
                                break;
                            case 'pemantau_kabupaten':
                                $currentTypeName = 'Pemantau Kabupaten';
                                break;
                            default:
                                $currentTypeName = 'User';
                        }
                        ?>
                        Role Aktif: <strong><?= $currentTypeName ?></strong>
                    </p>
                </div>

                <!-- Content -->
                <div class="p-6">
                    
                    <!-- Info Alert -->
                    <div class="mb-6 bg-amber-50 border border-amber-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <i class="fas fa-info-circle text-amber-600 mt-0.5 mr-3"></i>
                            <div class="text-sm text-amber-700">
                                <p class="font-medium mb-1">Ganti Role Aktif</p>
                                <p>Pilih role yang ingin Anda gunakan. Data yang ditampilkan akan menyesuaikan dengan role yang dipilih.</p>
                            </div>
                        </div>
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

                    <?php if (session()->getFlashdata('info')): ?>
                    <div class="mb-4 bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <i class="fas fa-info-circle text-blue-600 mr-3"></i>
                            <p class="text-sm text-blue-700"><?= session()->getFlashdata('info') ?></p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Form -->
                    <form action="<?= base_url('login/process-switch-role') ?>" method="POST">
                        <?= csrf_field() ?>

                        <div class="space-y-3 mb-6">
                            <?php 
                            $roleIcons = [
                                'superadmin' => ['icon' => 'fa-crown', 'color' => 'blue', 'desc' => 'Akses penuh sistem'],
                                'admin_provinsi' => ['icon' => 'fa-user-shield', 'color' => 'indigo', 'desc' => 'Mengelola survei provinsi'],
                                'pemantau_provinsi' => ['icon' => 'fa-eye', 'color' => 'cyan', 'desc' => 'Melihat data provinsi'],
                                'admin_kabupaten' => ['icon' => 'fa-user-tie', 'color' => 'purple', 'desc' => 'Mengelola survei kabupaten'],
                                'pemantau_kabupaten' => ['icon' => 'fa-eye', 'color' => 'teal', 'desc' => 'Melihat data kabupaten'],
                            ];
                            
                            foreach ($roles as $role): 
                                $roleId = $role['id_roleuser'];
                                $roleType = $role['role_type'] ?? 'default';
                                
                                // Tentukan key untuk icon
                                if ($roleType === 'default') {
                                    $iconKey = 'default_' . $roleId;
                                } else {
                                    $iconKey = $roleType;
                                }
                                
                                $iconData = $roleIcons[$iconKey] ?? ['icon' => 'fa-user', 'color' => 'gray', 'desc' => 'Role user'];
                                
                                // Check jika ini adalah role yang sedang aktif
                                $isActive = ($roleType === $current_role_type);
                                $borderClass = $isActive ? 'border-' . $iconData['color'] . '-500 bg-' . $iconData['color'] . '-50' : 'border-gray-200';
                            ?>
                            <label class="flex items-center p-4 border-2 <?= $borderClass ?> rounded-xl cursor-pointer hover:border-<?= $iconData['color'] ?>-500 hover:bg-<?= $iconData['color'] ?>-50 transition-all duration-200 group">
                                <input type="radio" 
                                       name="selected_role" 
                                       value="<?= $roleId ?>" 
                                       data-role-type="<?= $roleType ?>"
                                       class="w-5 h-5 text-<?= $iconData['color'] ?>-600 focus:ring-<?= $iconData['color'] ?>-500 role-radio"
                                       <?= $isActive ? 'checked' : '' ?>
                                       required>
                                
                                <div class="ml-4 flex-1">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-<?= $iconData['color'] ?>-100 rounded-lg flex items-center justify-center mr-3">
                                            <i class="fas <?= $iconData['icon'] ?> text-<?= $iconData['color'] ?>-600"></i>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-900"><?= esc($role['roleuser']) ?></p>
                                            <p class="text-xs text-gray-500"><?= esc($role['keterangan']) ?></p>
                                        </div>
                                    </div>
                                </div>
                                
                                <?php if ($isActive): ?>
                                <span class="px-2 py-1 bg-blue-100 text-blue-700 text-xs font-medium rounded-full mr-2">
                                    <i class="fas fa-check-circle mr-1"></i>Aktif
                                </span>
                                <?php elseif ($roleType === 'admin_provinsi' || $roleType === 'admin_kabupaten'): ?>
                                <span class="px-2 py-1 bg-green-100 text-green-700 text-xs font-medium rounded-full mr-2">
                                    <i class="fas fa-shield-alt mr-1"></i>Admin
                                </span>
                                <?php endif; ?>
                                
                                <i class="fas fa-chevron-right text-gray-400 group-hover:text-<?= $iconData['color'] ?>-600 transition-colors"></i>
                            </label>
                            <?php endforeach; ?>
                        </div>

                        <!-- Hidden input untuk role type -->
                        <input type="hidden" name="selected_role_type" id="selected_role_type" value="<?= $current_role_type ?>">

                        <!-- Buttons -->
                        <div class="flex gap-3">
                            <button type="button" 
                                    onclick="window.history.back()"
                                    class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200 transition-colors text-center">
                                <i class="fas fa-arrow-left mr-2"></i>
                                Batal
                            </button>
                            <button type="submit" 
                                    class="flex-1 px-4 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg font-medium hover:from-blue-700 hover:to-indigo-700 transition-all shadow-lg hover:shadow-xl">
                                <i class="fas fa-exchange-alt mr-2"></i>
                                Ganti Role
                            </button>
                        </div>
                    </form>

                </div>
            </div>

            <!-- Footer -->
            <p class="text-center text-gray-600 text-sm mt-6">
                © <?= date('Y') ?> SiPantau - Sistem Informasi Pemantauan
            </p>

        </div>
    </div>

    <script>
        // Update hidden input saat radio button dipilih
        document.querySelectorAll('.role-radio').forEach(radio => {
            radio.addEventListener('change', function() {
                const roleType = this.getAttribute('data-role-type');
                document.getElementById('selected_role_type').value = roleType;
            });
        });

        // Set initial value untuk role yang sudah checked
        const checkedRadio = document.querySelector('.role-radio:checked');
        if (checkedRadio) {
            document.getElementById('selected_role_type').value = checkedRadio.getAttribute('data-role-type');
        }
    </script>

</body>
</html>