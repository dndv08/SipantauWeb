<?= $this->extend('layouts/sadmin_layout') ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="mb-6">
    <div class="flex items-center gap-4">
        <a href="<?= base_url('superadmin/rating-aplikasi') ?>" class="text-gray-600 hover:text-gray-900">
            <i class="fas fa-arrow-left text-xl"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Detail Rating Aplikasi</h1>
            <p class="text-gray-600 mt-1">Informasi lengkap feedback dari pengguna</p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Content -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Rating Card -->
        <div class="card">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Rating</h3>
                <span class="text-sm text-gray-500">
                    <?= date('d M Y, H:i', strtotime($feedback['created_at'])) ?>
                </span>
            </div>

            <!-- Rating Display -->
            <div class="flex items-center gap-6 p-6 bg-gradient-to-br from-yellow-50 to-orange-50 rounded-lg mb-6">
                <div class="text-center">
                    <div class="text-6xl font-bold text-gray-900 mb-2"><?= $feedback['rating'] ?></div>
                    <div class="text-yellow-500 text-2xl">
                        <?php for($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star<?= $i <= $feedback['rating'] ? '' : '-o' ?>"></i>
                        <?php endfor; ?>
                    </div>
                    <p class="text-sm text-gray-600 mt-2">dari 5 bintang</p>
                </div>
                <div class="flex-1">
                    <div class="space-y-2">
                        <?php for($i = 5; $i >= 1; $i--): ?>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-600 w-8"><?= $i ?> ★</span>
                            <div class="flex-1 bg-gray-200 rounded-full h-2">
                                <div class="h-2 rounded-full transition-all duration-500 <?= $i == $feedback['rating'] ? 'bg-yellow-500' : 'bg-gray-300' ?>" 
                                     style="width: <?= $i == $feedback['rating'] ? '100' : '0' ?>%"></div>
                            </div>
                        </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>

            <!-- Feedback Content -->
            <div class="mb-6">
                <h4 class="text-sm font-semibold text-gray-900 mb-3">Feedback dari User:</h4>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-gray-700 leading-relaxed"><?= esc($feedback['feedback']) ?></p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                <a href="<?= base_url('superadmin/rating-aplikasi') ?>" class="btn-primary">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali ke Daftar
                </a>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="lg:col-span-1 space-y-6">
        <!-- User Info -->
        <div class="card">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi User</h3>
            
            <div class="space-y-4">
                <!-- Avatar -->
                <div class="flex justify-center">
                    <div class="w-20 h-20 bg-blue-600 rounded-full flex items-center justify-center">
                        <?php
                        $nameParts = explode(' ', $feedback['nama_user']);
                        $initials = count($nameParts) >= 2 
                            ? strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1))
                            : strtoupper(substr($feedback['nama_user'], 0, 2));
                        ?>
                        <span class="text-white text-2xl font-bold"><?= $initials ?></span>
                    </div>
                </div>

                <!-- User Details -->
                <div class="text-center">
                    <p class="text-lg font-semibold text-gray-900"><?= esc($feedback['nama_user']) ?></p>
                    <p class="text-sm text-gray-600"><?= esc($feedback['nama_kabupaten']) ?></p>
                </div>

                <div class="border-t border-gray-200 pt-4 space-y-3">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-id-card text-gray-400 mt-1"></i>
                        <div class="flex-1">
                            <p class="text-xs text-gray-500">Sobat ID</p>
                            <p class="text-sm font-medium text-gray-900"><?= esc($feedback['sobat_id_user']) ?></p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <i class="fas fa-envelope text-gray-400 mt-1"></i>
                        <div class="flex-1">
                            <p class="text-xs text-gray-500">Email</p>
                            <p class="text-sm font-medium text-gray-900 break-words"><?= esc($feedback['email']) ?></p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <i class="fas fa-phone text-gray-400 mt-1"></i>
                        <div class="flex-1">
                            <p class="text-xs text-gray-500">No. HP</p>
                            <p class="text-sm font-medium text-gray-900"><?= esc($feedback['hp']) ?></p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <i class="fas fa-user-tag text-gray-400 mt-1"></i>
                        <div class="flex-1">
                            <p class="text-xs text-gray-500">Role</p>
                            <p class="text-sm font-medium text-gray-900">
                                <?php
                                if (!empty($feedback['role'])) {
                                    $roleIds = json_decode($feedback['role'], true);
                                    if (is_array($roleIds)) {
                                        $roleModel = new \App\Models\RoleModel();
                                        $roles = $roleModel->whereIn('id_roleuser', $roleIds)->findAll();
                                        echo implode(', ', array_column($roles, 'roleuser'));
                                    }
                                } else {
                                    echo '-';
                                }
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rating Statistics -->
        <div class="card">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Statistik Rating User</h3>
            
            <?php
            // Get user's rating history
            $feedbackUserModel = new \App\Models\FeedbackUserModel();
            $userRatings = $feedbackUserModel->where('sobat_id', $feedback['sobat_id'])->findAll();
            $totalRatings = count($userRatings);
            $avgRating = $totalRatings > 0 ? array_sum(array_column($userRatings, 'rating')) / $totalRatings : 0;
            ?>
            
            <div class="space-y-4">
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <span class="text-sm text-gray-600">Total Rating Diberikan</span>
                    <span class="text-lg font-bold text-gray-900"><?= $totalRatings ?></span>
                </div>
                
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <span class="text-sm text-gray-600">Rata-rata Rating</span>
                    <div class="flex items-center gap-2">
                        <span class="text-lg font-bold text-gray-900"><?= number_format($avgRating, 1) ?></span>
                        <span class="text-yellow-500">★</span>
                    </div>
                </div>

                <?php if ($totalRatings > 1): ?>
                <div class="border-t border-gray-200 pt-4">
                    <p class="text-xs text-gray-500 mb-2">Riwayat Rating Terakhir:</p>
                    <div class="space-y-2 max-h-32 overflow-y-auto">
                        <?php 
                        $latestRatings = array_slice($userRatings, -5);
                        foreach (array_reverse($latestRatings) as $rating): 
                            if ($rating['id_feedback'] != $feedback['id_feedback']):
                        ?>
                        <div class="flex items-center justify-between text-sm p-2 bg-white rounded">
                            <span class="text-yellow-500">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star<?= $i <= $rating['rating'] ? '' : '-o' ?>"></i>
                                <?php endfor; ?>
                            </span>
                            <span class="text-xs text-gray-500">
                                <?= date('d M Y', strtotime($rating['created_at'])) ?>
                            </span>
                        </div>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Metadata -->
        <div class="card">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Lainnya</h3>
            <div class="space-y-3 text-sm">
                <div class="flex items-center justify-between">
                    <span class="text-gray-600">Tanggal Dibuat</span>
                    <span class="font-medium text-gray-900">
                        <?= date('d M Y', strtotime($feedback['created_at'])) ?>
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-gray-600">Waktu</span>
                    <span class="font-medium text-gray-900">
                        <?= date('H:i', strtotime($feedback['created_at'])) ?> WIB
                    </span>
                </div>
                <?php if ($feedback['updated_at'] && $feedback['updated_at'] != $feedback['created_at']): ?>
                <div class="flex items-center justify-between">
                    <span class="text-gray-600">Terakhir Diperbarui</span>
                    <span class="font-medium text-gray-900">
                        <?= date('d M Y H:i', strtotime($feedback['updated_at'])) ?>
                    </span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>


<?= $this->endSection() ?>