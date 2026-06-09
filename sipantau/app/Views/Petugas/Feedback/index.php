<?= $this->extend('layouts/petugas_layout') ?>
<?= $this->section('content') ?>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Feedback</h1>
    <p class="text-sm text-gray-500 mt-1">Status kegiatan, feedback dari admin, dan kirim feedback Anda</p>
</div>

<!-- ════════════════ KEGIATAN PCL ════════════════ -->
<?php if (!empty($kegiatanPCL)): ?>
<div class="mb-6">
    <h3 class="text-sm font-semibold text-gray-900 mb-3 flex items-center gap-2">
        <i class="fas fa-user text-blue-600"></i> Kegiatan Sebagai PCL
        <span class="bg-blue-100 text-blue-700 text-xs font-medium px-2 py-0.5 rounded-full"><?= count($kegiatanPCL) ?></span>
    </h3>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <?php foreach ($kegiatanPCL as $k): 
            $persen = ($k['target'] ?? 0) > 0 ? round(($k['realisasi_kumulatif'] / $k['target']) * 100, 1) : 0;
            $hasFeedback = !empty($k['feedback_admin']);
            $statusColor = $persen >= 100 ? 'green' : ($persen >= 50 ? 'blue' : 'orange');
        ?>
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
            <!-- Header -->
            <div class="px-5 py-3 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-blue-100">
                <div class="flex items-start justify-between">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900 truncate"><?= esc($k['nama_kegiatan_detail_proses']) ?></p>
                        <p class="text-xs text-gray-500 mt-0.5"><?= esc($k['nama_kegiatan']) ?></p>
                    </div>
                    <span class="ml-2 shrink-0 px-2 py-0.5 rounded text-xs font-semibold bg-<?= $statusColor ?>-100 text-<?= $statusColor ?>-700">
                        <?= $persen ?>%
                    </span>
                </div>
            </div>
            
            <!-- Body -->
            <div class="px-5 py-4">
                <!-- Progress -->
                <div class="mb-3">
                    <div class="flex justify-between text-xs text-gray-500 mb-1">
                        <span>Realisasi: <?= number_format($k['realisasi_kumulatif']) ?> / <?= number_format($k['target']) ?></span>
                        <span><?= $k['total_laporan'] ?> laporan</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="h-2 rounded-full bg-<?= $statusColor ?>-500 transition-all" style="width: <?= min($persen, 100) ?>%"></div>
                    </div>
                </div>

                <!-- Info -->
                <div class="grid grid-cols-2 gap-2 text-xs text-gray-500 mb-3">
                    <div><i class="fas fa-user-tie mr-1 text-gray-400"></i>PML: <?= esc($k['nama_pml']) ?></div>
                    <div><i class="far fa-calendar mr-1 text-gray-400"></i>
                        <?= !empty($k['tanggal_mulai']) ? date('d/m/Y', strtotime($k['tanggal_mulai'])) : '-' ?> - 
                        <?= !empty($k['tanggal_selesai']) ? date('d/m/Y', strtotime($k['tanggal_selesai'])) : '-' ?>
                    </div>
                </div>

                <!-- Feedback Admin -->
                <div class="border-t border-gray-100 pt-3">
                    <p class="text-xs font-semibold text-gray-600 mb-1.5">
                        <i class="fas fa-comment-dots mr-1 text-blue-500"></i>Feedback Admin
                    </p>
                    <?php if ($hasFeedback): ?>
                        <div class="bg-blue-50 rounded-lg p-3 border border-blue-100">
                            <p class="text-sm text-gray-700"><?= esc($k['feedback_admin']) ?></p>
                            <?php if (!empty($k['rating'])): ?>
                            <div class="mt-2 flex items-center gap-1">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star text-xs <?= $i <= $k['rating'] ? 'text-yellow-400' : 'text-gray-300' ?>"></i>
                                <?php endfor; ?>
                                <span class="text-xs text-gray-400 ml-1">(<?= $k['rating'] ?>/5)</span>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($k['updated_at'])): ?>
                            <p class="text-xs text-gray-400 mt-1"><?= date('d/m/Y H:i', strtotime($k['updated_at'])) ?></p>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="flex items-center gap-2 text-gray-400 py-2">
                            <i class="fas fa-clock text-sm"></i>
                            <span class="text-xs italic">Menunggu feedback dari admin</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- ════════════════ KEGIATAN PML ════════════════ -->
<?php if (!empty($kegiatanPML)): ?>
<div class="mb-6">
    <h3 class="text-sm font-semibold text-gray-900 mb-3 flex items-center gap-2">
        <i class="fas fa-user-tie text-yellow-600"></i> Kegiatan Sebagai PML
        <span class="bg-yellow-100 text-yellow-700 text-xs font-medium px-2 py-0.5 rounded-full"><?= count($kegiatanPML) ?></span>
    </h3>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <?php foreach ($kegiatanPML as $k): 
            $persen = ($k['target'] ?? 0) > 0 ? round(($k['realisasi_kumulatif'] / $k['target']) * 100, 1) : 0;
            $hasFeedback = !empty($k['feedback_admin']);
            $statusColor = $persen >= 100 ? 'green' : ($persen >= 50 ? 'blue' : 'orange');
        ?>
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
            <!-- Header -->
            <div class="px-5 py-3 bg-gradient-to-r from-yellow-50 to-amber-50 border-b border-yellow-100">
                <div class="flex items-start justify-between">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900 truncate"><?= esc($k['nama_kegiatan_detail_proses']) ?></p>
                        <p class="text-xs text-gray-500 mt-0.5"><?= esc($k['nama_kegiatan']) ?></p>
                    </div>
                    <span class="ml-2 shrink-0 px-2 py-0.5 rounded text-xs font-semibold bg-<?= $statusColor ?>-100 text-<?= $statusColor ?>-700">
                        <?= $persen ?>%
                    </span>
                </div>
            </div>
            
            <!-- Body -->
            <div class="px-5 py-4">
                <!-- Progress -->
                <div class="mb-3">
                    <div class="flex justify-between text-xs text-gray-500 mb-1">
                        <span>Realisasi: <?= number_format($k['realisasi_kumulatif']) ?> / <?= number_format($k['target']) ?></span>
                        <span><?= $k['jumlah_pcl'] ?? 0 ?> PCL • <?= $k['total_laporan'] ?> laporan</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="h-2 rounded-full bg-<?= $statusColor ?>-500 transition-all" style="width: <?= min($persen, 100) ?>%"></div>
                    </div>
                </div>

                <!-- Info -->
                <div class="text-xs text-gray-500 mb-3">
                    <i class="far fa-calendar mr-1 text-gray-400"></i>
                    <?= !empty($k['tanggal_mulai']) ? date('d/m/Y', strtotime($k['tanggal_mulai'])) : '-' ?> - 
                    <?= !empty($k['tanggal_selesai']) ? date('d/m/Y', strtotime($k['tanggal_selesai'])) : '-' ?>
                </div>

                <!-- Feedback Admin -->
                <div class="border-t border-gray-100 pt-3">
                    <p class="text-xs font-semibold text-gray-600 mb-1.5">
                        <i class="fas fa-comment-dots mr-1 text-yellow-500"></i>Feedback Admin
                    </p>
                    <?php if ($hasFeedback): ?>
                        <div class="bg-yellow-50 rounded-lg p-3 border border-yellow-100">
                            <p class="text-sm text-gray-700"><?= esc($k['feedback_admin']) ?></p>
                            <?php if (!empty($k['updated_at'])): ?>
                            <p class="text-xs text-gray-400 mt-1"><?= date('d/m/Y H:i', strtotime($k['updated_at'])) ?></p>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="flex items-center gap-2 text-gray-400 py-2">
                            <i class="fas fa-clock text-sm"></i>
                            <span class="text-xs italic">Menunggu feedback dari admin</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php if (empty($kegiatanPCL) && empty($kegiatanPML)): ?>
<div class="bg-white rounded-xl border border-gray-200 p-8 text-center mb-6">
    <i class="fas fa-inbox text-4xl text-gray-300 mb-3"></i>
    <p class="text-gray-500">Belum ada kegiatan yang di-assign kepada Anda</p>
</div>
<?php endif; ?>

<!-- ════════════════ KIRIM FEEDBACK ════════════════ -->
<div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
    <h3 class="text-sm font-semibold text-gray-900 mb-4"><i class="fas fa-paper-plane text-green-600 mr-2"></i>Kirim Feedback ke Sistem</h3>
    <form action="<?= base_url('petugas/feedback/store') ?>" method="post">
        <?= csrf_field() ?>
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Rating</label>
            <div class="flex gap-1.5" id="ratingStars">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                <button type="button" onclick="setRating(<?= $i ?>)" class="text-3xl text-gray-300 hover:text-yellow-400 transition-all duration-150 transform hover:scale-110 star-btn" data-star="<?= $i ?>" title="<?= $i ?> bintang">
                    <i class="fas fa-star"></i>
                </button>
                <?php endfor; ?>
                <span id="ratingLabel" class="ml-3 text-sm text-gray-500 self-center font-medium">Sangat Baik</span>
            </div>
            <input type="hidden" name="rating" id="ratingInput" value="5">
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Feedback Anda</label>
            <textarea name="feedback" rows="3" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Tulis feedback, saran, atau keluhan Anda..."></textarea>
        </div>
        <button type="submit" class="bg-green-600 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-green-700 transition-colors">
            <i class="fas fa-paper-plane mr-2"></i>Kirim Feedback
        </button>
    </form>
</div>

<!-- ════════════════ HISTORI FEEDBACK ════════════════ -->
<?php if (!empty($myFeedback)): ?>
<div class="bg-white rounded-xl border border-gray-200 p-6">
    <h3 class="text-sm font-semibold text-gray-900 mb-4">
        <i class="fas fa-history text-gray-600 mr-2"></i>Feedback Saya Sebelumnya
        <span class="bg-gray-100 text-gray-600 text-xs font-medium px-2 py-0.5 rounded-full ml-1"><?= count($myFeedback) ?></span>
    </h3>
    <div class="space-y-3">
        <?php foreach ($myFeedback as $idx => $fb): ?>
        <div class="flex gap-3 <?= $idx > 0 ? 'border-t border-gray-100 pt-3' : '' ?>">
            <!-- Timeline dot -->
            <div class="flex flex-col items-center">
                <div class="w-2.5 h-2.5 rounded-full bg-blue-500 mt-1.5"></div>
                <?php if ($idx < count($myFeedback) - 1): ?>
                <div class="w-0.5 flex-1 bg-gray-200 mt-1"></div>
                <?php endif; ?>
            </div>
            <!-- Content -->
            <div class="flex-1 pb-2">
                <div class="flex items-center justify-between mb-1">
                    <div class="flex items-center gap-1">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="fas fa-star text-xs <?= $i <= $fb['rating'] ? 'text-yellow-400' : 'text-gray-200' ?>"></i>
                        <?php endfor; ?>
                    </div>
                    <span class="text-xs text-gray-400"><?= date('d/m/Y H:i', strtotime($fb['created_at'])) ?></span>
                </div>
                <p class="text-sm text-gray-700"><?= esc($fb['feedback']) ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<script>
const ratingLabels = {1: 'Sangat Buruk', 2: 'Buruk', 3: 'Cukup', 4: 'Baik', 5: 'Sangat Baik'};

function setRating(val) {
    document.getElementById('ratingInput').value = val;
    document.querySelectorAll('.star-btn').forEach(btn => {
        const star = parseInt(btn.dataset.star);
        const icon = btn.querySelector('i');
        if (star <= val) {
            icon.className = 'fas fa-star';
            btn.classList.remove('text-gray-300');
            btn.classList.add('text-yellow-400');
        } else {
            icon.className = 'fas fa-star';
            btn.classList.remove('text-yellow-400');
            btn.classList.add('text-gray-300');
        }
    });
    const label = document.getElementById('ratingLabel');
    if (label) label.textContent = ratingLabels[val] || '';
}
// Set default rating 5
document.addEventListener('DOMContentLoaded', () => setRating(5));
</script>

<?= $this->endSection() ?>
