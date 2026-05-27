<?= $this->extend('layouts/petugas_layout') ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
    <p class="text-sm text-gray-500 mt-1">Selamat datang, <?= esc(session()->get('nama_user')) ?>!</p>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <!-- Target -->
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase">Total Target</p>
                <p class="text-2xl font-bold text-gray-900 mt-1"><?= number_format($totalTargetPCL) ?></p>
            </div>
            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-bullseye text-blue-600"></i>
            </div>
        </div>
        <div class="mt-3">
            <div class="w-full bg-gray-200 rounded-full h-1.5">
                <div class="bg-blue-600 h-1.5 rounded-full" style="width: <?= min($persentasePCL, 100) ?>%"></div>
            </div>
            <p class="text-xs text-gray-500 mt-1"><?= $persentasePCL ?>% tercapai</p>
        </div>
    </div>

    <!-- Realisasi -->
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase">Realisasi</p>
                <p class="text-2xl font-bold text-green-600 mt-1"><?= number_format($totalRealisasiPCL) ?></p>
            </div>
            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-check-double text-green-600"></i>
            </div>
        </div>
        <p class="text-xs text-gray-500 mt-3">Dari <?= number_format($totalTargetPCL) ?> target</p>
    </div>

    <!-- Laporan Hari Ini -->
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase">Laporan Hari Ini</p>
                <p class="text-2xl font-bold text-orange-600 mt-1"><?= $laporanHariIni ?></p>
            </div>
            <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-file-alt text-orange-600"></i>
            </div>
        </div>
        <p class="text-xs text-gray-500 mt-3"><?= date('d M Y') ?></p>
    </div>

    <!-- Kegiatan Aktif -->
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase">Kegiatan Aktif</p>
                <p class="text-2xl font-bold text-purple-600 mt-1"><?= $kegiatanAktifPCL + $kegiatanAktifPML ?></p>
            </div>
            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-briefcase text-purple-600"></i>
            </div>
        </div>
        <p class="text-xs text-gray-500 mt-3">
            <?php if ($kegiatanAktifPCL > 0): ?><span class="text-blue-600"><?= $kegiatanAktifPCL ?> PCL</span><?php endif; ?>
            <?php if ($kegiatanAktifPCL > 0 && $kegiatanAktifPML > 0): ?> · <?php endif; ?>
            <?php if ($kegiatanAktifPML > 0): ?><span class="text-yellow-600"><?= $kegiatanAktifPML ?> PML</span><?php endif; ?>
            <?php if ($kegiatanAktifPCL == 0 && $kegiatanAktifPML == 0): ?>Belum ada kegiatan aktif<?php endif; ?>
        </p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <!-- Chart: Aktivitas 7 Hari -->
    <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="text-sm font-semibold text-gray-900 mb-4">
            <i class="fas fa-chart-bar text-blue-600 mr-2"></i>Aktivitas 7 Hari Terakhir
        </h3>
        <div style="height: 250px;">
            <canvas id="chartAktivitas"></canvas>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="text-sm font-semibold text-gray-900 mb-4">
            <i class="fas fa-star text-yellow-500 mr-2"></i>Ringkasan
        </h3>
        <div class="space-y-4">
            <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                <div class="flex items-center">
                    <i class="fas fa-clipboard text-blue-600 mr-3"></i>
                    <span class="text-sm text-gray-700">Total Transaksi</span>
                </div>
                <span class="text-sm font-bold text-blue-600"><?= number_format($totalTransaksi) ?></span>
            </div>
            <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg">
                <div class="flex items-center">
                    <i class="fas fa-trophy text-yellow-600 mr-3"></i>
                    <span class="text-sm text-gray-700">Achievement</span>
                </div>
                <span class="text-sm font-bold text-yellow-600"><?= $achievementCount ?></span>
            </div>
            <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                <div class="flex items-center">
                    <i class="fas fa-tasks text-green-600 mr-3"></i>
                    <span class="text-sm text-gray-700">Progres</span>
                </div>
                <span class="text-sm font-bold text-green-600"><?= $persentasePCL ?>%</span>
            </div>
        </div>
    </div>
</div>

<!-- Kegiatan Sebagai PCL & PML -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <?php if (!empty($asPCL)): ?>
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="text-sm font-semibold text-gray-900 mb-4">
            <i class="fas fa-user text-blue-600 mr-2"></i>Kegiatan Sebagai PCL
        </h3>
        <div class="space-y-3">
            <?php foreach (array_slice($asPCL, 0, 5) as $pcl): ?>
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate"><?= esc($pcl['nama_kegiatan_detail_proses']) ?></p>
                    <p class="text-xs text-gray-500">PML: <?= esc($pcl['nama_pml']) ?></p>
                </div>
                <div class="ml-3 text-right">
                    <?php
                    $persen = $pcl['target'] > 0 ? round(($pcl['realisasi_kumulatif'] / $pcl['target']) * 100, 1) : 0;
                    $colorClass = $persen >= 100 ? 'text-green-600 bg-green-100' : ($persen >= 50 ? 'text-blue-600 bg-blue-100' : 'text-orange-600 bg-orange-100');
                    ?>
                    <span class="inline-block px-2 py-0.5 rounded text-xs font-semibold <?= $colorClass ?>"><?= $persen ?>%</span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($asPML)): ?>
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="text-sm font-semibold text-gray-900 mb-4">
            <i class="fas fa-user-tie text-yellow-600 mr-2"></i>Kegiatan Sebagai PML
        </h3>
        <div class="space-y-3">
            <?php foreach (array_slice($asPML, 0, 5) as $pml): ?>
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate"><?= esc($pml['nama_kegiatan_detail_proses']) ?></p>
                    <p class="text-xs text-gray-500"><?= esc($pml['nama_kabupaten']) ?></p>
                </div>
                <div class="ml-3 text-right flex flex-col items-end">
                    <?php
                    $persen = $pml['target'] > 0 ? round(($pml['realisasi_kumulatif'] / $pml['target']) * 100, 1) : 0;
                    $colorClass = $persen >= 100 ? 'text-green-600 bg-green-100' : ($persen >= 50 ? 'text-blue-600 bg-blue-100' : 'text-orange-600 bg-orange-100');
                    ?>
                    <span class="inline-block px-2 py-0.5 rounded text-xs font-semibold <?= $colorClass ?> mb-1"><?= $persen ?>%</span>
                    <span class="text-[10px] text-gray-400"><?= $pml['jumlah_pcl'] ?> PCL</span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if (empty($asPCL) && empty($asPML)): ?>
    <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-8 text-center">
        <i class="fas fa-inbox text-4xl text-gray-300 mb-3"></i>
        <p class="text-gray-500">Belum ada kegiatan yang di-assign kepada Anda.</p>
        <p class="text-sm text-gray-400 mt-1">Hubungi admin kabupaten untuk mendapatkan penugasan.</p>
    </div>
    <?php endif; ?>
</div>

<!-- Feedback dari Admin -->
<?php if (!empty($feedbackList)): ?>
<div class="bg-white rounded-xl border border-gray-200 p-5 mb-6">
    <h3 class="text-sm font-semibold text-gray-900 mb-4">
        <i class="fas fa-comment-dots text-purple-600 mr-2"></i>Feedback Terbaru dari Admin
    </h3>
    <div class="space-y-3">
        <?php foreach (array_slice($feedbackList, 0, 3) as $fb): ?>
        <div class="p-3 bg-purple-50 rounded-lg border border-purple-100">
            <div class="flex items-center justify-between mb-1">
                <span class="text-xs font-medium text-purple-700"><?= esc($fb['kegiatan']) ?></span>
                <span class="text-xs text-purple-500"><?= $fb['role'] ?></span>
            </div>
            <p class="text-sm text-gray-700"><?= esc($fb['feedback']) ?></p>
            <?php if (!empty($fb['rating'])): ?>
            <div class="mt-1">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <i class="fas fa-star text-xs <?= $i <= $fb['rating'] ? 'text-yellow-400' : 'text-gray-300' ?>"></i>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Chart Aktivitas 7 Hari
    const progressData = <?= json_encode($progressHarian) ?>;
    const labels = [];
    const values = [];

    // Generate 7 hari terakhir
    for (let i = 6; i >= 0; i--) {
        const d = new Date();
        d.setDate(d.getDate() - i);
        const dateStr = d.toISOString().split('T')[0];
        const dayName = d.toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric' });
        labels.push(dayName);

        const found = progressData.find(p => p.tanggal === dateStr);
        values.push(found ? parseInt(found.jumlah) : 0);
    }

    const ctx = document.getElementById('chartAktivitas');
    if (ctx) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Laporan',
                    data: values,
                    backgroundColor: 'rgba(59, 130, 246, 0.7)',
                    borderRadius: 6,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } },
                    x: { grid: { display: false } }
                }
            }
        });
    }
});
</script>

<?= $this->endSection() ?>
