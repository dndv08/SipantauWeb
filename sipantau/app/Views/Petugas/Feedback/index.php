<?= $this->extend('layouts/petugas_layout') ?>
<?= $this->section('content') ?>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Feedback</h1>
    <p class="text-sm text-gray-500 mt-1">Feedback dari admin dan kirim feedback Anda</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Feedback dari Admin (PCL) -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-sm font-semibold text-gray-900 mb-4"><i class="fas fa-comment text-blue-600 mr-2"></i>Feedback Admin (PCL)</h3>
        <?php if (empty($feedbackPCL)): ?>
            <div class="text-center py-6 text-gray-400">
                <i class="fas fa-comments text-2xl mb-2"></i>
                <p class="text-sm">Belum ada feedback dari admin</p>
            </div>
        <?php else: ?>
        <div class="space-y-3">
            <?php foreach ($feedbackPCL as $fb): ?>
            <div class="p-3 bg-blue-50 rounded-lg border border-blue-100">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-medium text-blue-700"><?= esc($fb['nama_kegiatan_detail_proses']) ?></span>
                    <span class="text-xs text-gray-400"><?= date('d/m/Y', strtotime($fb['updated_at'])) ?></span>
                </div>
                <p class="text-sm text-gray-700"><?= esc($fb['feedback_admin']) ?></p>
                <?php if (!empty($fb['rating'])): ?>
                <div class="mt-1">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                    <i class="fas fa-star text-xs <?= $i <= $fb['rating'] ? 'text-yellow-400' : 'text-gray-300' ?>"></i>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>
                <p class="text-xs text-gray-400 mt-1">PML: <?= esc($fb['nama_pml']) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Feedback dari Admin (PML) -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-sm font-semibold text-gray-900 mb-4"><i class="fas fa-comment-dots text-yellow-600 mr-2"></i>Feedback Admin (PML)</h3>
        <?php if (empty($feedbackPML)): ?>
            <div class="text-center py-6 text-gray-400">
                <i class="fas fa-comments text-2xl mb-2"></i>
                <p class="text-sm">Belum ada feedback dari admin</p>
            </div>
        <?php else: ?>
        <div class="space-y-3">
            <?php foreach ($feedbackPML as $fb): ?>
            <div class="p-3 bg-yellow-50 rounded-lg border border-yellow-100">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-medium text-yellow-700"><?= esc($fb['nama_kegiatan_detail_proses']) ?></span>
                    <span class="text-xs text-gray-400"><?= date('d/m/Y', strtotime($fb['updated_at'])) ?></span>
                </div>
                <p class="text-sm text-gray-700"><?= esc($fb['feedback_admin']) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Kirim Feedback -->
<div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
    <h3 class="text-sm font-semibold text-gray-900 mb-4"><i class="fas fa-paper-plane text-green-600 mr-2"></i>Kirim Feedback ke Sistem</h3>
    <form action="<?= base_url('petugas/feedback/store') ?>" method="post">
        <?= csrf_field() ?>
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Rating</label>
            <div class="flex gap-2" id="ratingStars">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                <button type="button" onclick="setRating(<?= $i ?>)" class="text-2xl text-gray-300 hover:text-yellow-400 transition-colors star-btn" data-star="<?= $i ?>">
                    <i class="fas fa-star"></i>
                </button>
                <?php endfor; ?>
            </div>
            <input type="hidden" name="rating" id="ratingInput" value="5">
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Feedback Anda</label>
            <textarea name="feedback" rows="3" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Tulis feedback, saran, atau keluhan Anda..."></textarea>
        </div>
        <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-green-700 transition-colors">
            <i class="fas fa-paper-plane mr-2"></i>Kirim Feedback
        </button>
    </form>
</div>

<!-- Histori Feedback Saya -->
<?php if (!empty($myFeedback)): ?>
<div class="bg-white rounded-xl border border-gray-200 p-6">
    <h3 class="text-sm font-semibold text-gray-900 mb-4"><i class="fas fa-history text-gray-600 mr-2"></i>Feedback Saya Sebelumnya</h3>
    <div class="space-y-3">
        <?php foreach ($myFeedback as $fb): ?>
        <div class="p-3 bg-gray-50 rounded-lg border border-gray-100">
            <div class="flex items-center justify-between mb-1">
                <div>
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                    <i class="fas fa-star text-xs <?= $i <= $fb['rating'] ? 'text-yellow-400' : 'text-gray-300' ?>"></i>
                    <?php endfor; ?>
                </div>
                <span class="text-xs text-gray-400"><?= date('d/m/Y H:i', strtotime($fb['created_at'])) ?></span>
            </div>
            <p class="text-sm text-gray-700"><?= esc($fb['feedback']) ?></p>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<script>
function setRating(val) {
    document.getElementById('ratingInput').value = val;
    document.querySelectorAll('.star-btn').forEach(btn => {
        const star = parseInt(btn.dataset.star);
        btn.querySelector('i').className = star <= val ? 'fas fa-star text-yellow-400' : 'fas fa-star text-gray-300';
    });
}
// Set default rating 5
document.addEventListener('DOMContentLoaded', () => setRating(5));
</script>

<?= $this->endSection() ?>
