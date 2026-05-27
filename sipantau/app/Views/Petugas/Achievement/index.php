<?= $this->extend('layouts/petugas_layout') ?>
<?= $this->section('content') ?>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Achievement</h1>
    <p class="text-sm text-gray-500 mt-1">Badge dan penghargaan yang telah Anda raih</p>
</div>

<!-- Your Rank -->
<div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-xl p-6 mb-6 text-white">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-blue-200 text-sm">Total Achievement</p>
            <p class="text-3xl font-bold mt-1"><?= count($userAchievements) ?> / <?= count($allAchievements) ?></p>
        </div>
        <div class="text-right">
            <p class="text-blue-200 text-sm">Peringkat Anda</p>
            <p class="text-3xl font-bold mt-1"><?= $myRank > 0 ? '#' . $myRank : '-' ?></p>
        </div>
    </div>
    <div class="mt-4">
        <div class="w-full bg-blue-400/30 rounded-full h-2">
            <?php $achievePersen = count($allAchievements) > 0 ? round((count($userAchievements) / count($allAchievements)) * 100) : 0; ?>
            <div class="bg-white h-2 rounded-full" style="width: <?= $achievePersen ?>%"></div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Achievement yang sudah diraih -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-sm font-semibold text-gray-900 mb-4"><i class="fas fa-medal text-yellow-500 mr-2"></i>Semua Badge</h3>
            <?php if (empty($allAchievements)): ?>
                <div class="text-center py-8 text-gray-400">
                    <i class="fas fa-trophy text-3xl mb-2"></i>
                    <p>Belum ada achievement yang tersedia</p>
                </div>
            <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <?php foreach ($allAchievements as $a):
                    $achieved = in_array($a['id_achievement'], $achievedIds);
                ?>
                <div class="p-4 rounded-lg border <?= $achieved ? 'bg-yellow-50 border-yellow-200' : 'bg-gray-50 border-gray-200 opacity-60' ?>">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center <?= $achieved ? 'bg-yellow-200' : 'bg-gray-200' ?>">
                            <i class="fas fa-trophy <?= $achieved ? 'text-yellow-600' : 'text-gray-400' ?>"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium <?= $achieved ? 'text-gray-900' : 'text-gray-500' ?>"><?= esc($a['nama_achievement']) ?></p>
                            <p class="text-xs text-gray-500 mt-0.5"><?= esc($a['deskripsi'] ?? '') ?></p>
                            <?php if (!empty($a['kategori'])): ?>
                            <span class="inline-block mt-1 px-2 py-0.5 rounded text-xs <?= $achieved ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-500' ?>"><?= esc($a['kategori']) ?></span>
                            <?php endif; ?>
                            <?php if ($achieved): ?>
                            <span class="inline-block ml-1 px-2 py-0.5 rounded text-xs bg-green-100 text-green-700">✓ Diraih</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Leaderboard -->
    <div>
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-sm font-semibold text-gray-900 mb-4"><i class="fas fa-ranking-star text-purple-600 mr-2"></i>Leaderboard Top 10</h3>
            <?php if (empty($leaderboard)): ?>
                <p class="text-center py-4 text-gray-400 text-sm">Belum ada data</p>
            <?php else: ?>
            <div class="space-y-2">
                <?php foreach ($leaderboard as $i => $lb):
                    $isMe = $lb['sobat_id'] == session()->get('sobat_id');
                    $rankColor = $i === 0 ? 'text-yellow-500' : ($i === 1 ? 'text-gray-400' : ($i === 2 ? 'text-orange-400' : 'text-gray-500'));
                ?>
                <div class="flex items-center gap-3 p-2 rounded-lg <?= $isMe ? 'bg-blue-50 border border-blue-200' : '' ?>">
                    <span class="w-6 text-center font-bold text-sm <?= $rankColor ?>">
                        <?php if ($i < 3): ?>
                            <i class="fas fa-trophy"></i>
                        <?php else: ?>
                            <?= $i + 1 ?>
                        <?php endif; ?>
                    </span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium <?= $isMe ? 'text-blue-700' : 'text-gray-900' ?> truncate"><?= esc($lb['nama_user']) ?></p>
                    </div>
                    <span class="text-sm font-bold <?= $isMe ? 'text-blue-600' : 'text-gray-700' ?>"><?= $lb['total_achievement'] ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
