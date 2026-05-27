<?= $this->extend('layouts/petugas_layout') ?>
<?= $this->section('content') ?>

<div class="mb-6">
    <div class="flex items-center gap-3">
        <a href="<?= base_url('petugas/daftar-kegiatan') ?>" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left"></i></a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Detail Kegiatan PCL</h1>
            <p class="text-sm text-gray-500 mt-1"><?= esc($pcl['nama_kegiatan_detail_proses']) ?></p>
        </div>
    </div>
</div>

<!-- Info -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl border p-4">
        <p class="text-xs text-gray-500">Target</p>
        <p class="text-xl font-bold text-gray-900"><?= number_format($pcl['target']) ?></p>
    </div>
    <div class="bg-white rounded-xl border p-4">
        <p class="text-xs text-gray-500">PML</p>
        <p class="text-sm font-medium text-gray-900"><?= esc($pcl['nama_pml']) ?></p>
        <p class="text-xs text-gray-400"><?= esc($pcl['hp_pml'] ?? '-') ?></p>
    </div>
    <div class="bg-white rounded-xl border p-4">
        <p class="text-xs text-gray-500">Periode</p>
        <p class="text-sm font-medium text-gray-900"><?= date('d/m/Y', strtotime($pcl['tanggal_mulai'])) ?></p>
        <p class="text-xs text-gray-400">s/d <?= date('d/m/Y', strtotime($pcl['tanggal_selesai'])) ?></p>
    </div>
    <div class="bg-white rounded-xl border p-4">
        <p class="text-xs text-gray-500">Kabupaten</p>
        <p class="text-sm font-medium text-gray-900"><?= esc($pcl['nama_kabupaten']) ?></p>
    </div>
</div>

<!-- Feedback Admin -->
<?php if (!empty($pcl['feedback_admin'])): ?>
<div class="bg-purple-50 rounded-xl border border-purple-200 p-4 mb-6">
    <p class="text-xs font-medium text-purple-700 mb-1"><i class="fas fa-comment mr-1"></i>Feedback Admin</p>
    <p class="text-sm text-gray-700"><?= esc($pcl['feedback_admin']) ?></p>
</div>
<?php endif; ?>

<!-- Kurva S Chart -->
<div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
    <h3 class="text-sm font-semibold text-gray-900 mb-4"><i class="fas fa-chart-line text-blue-600 mr-2"></i>Kurva S</h3>
    <div style="height: 300px;">
        <canvas id="kurvaChart"></canvas>
    </div>
</div>

<!-- Realisasi Harian -->
<div class="bg-white rounded-xl border border-gray-200 p-6">
    <h3 class="text-sm font-semibold text-gray-900 mb-4"><i class="fas fa-table text-gray-600 mr-2"></i>Realisasi Harian</h3>
    <?php if (empty($realisasi)): ?>
        <p class="text-center text-gray-400 py-4">Belum ada data realisasi</p>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="bg-gray-50"><th class="px-4 py-2 text-left">Tanggal</th><th class="px-4 py-2 text-right">Harian</th><th class="px-4 py-2 text-right">Kumulatif</th></tr></thead>
            <tbody class="divide-y">
                <?php foreach ($realisasi as $r): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2"><?= date('d/m/Y', strtotime($r['tanggal_realisasi'])) ?></td>
                    <td class="px-4 py-2 text-right font-medium"><?= number_format($r['jumlah_harian']) ?></td>
                    <td class="px-4 py-2 text-right text-blue-600 font-medium"><?= number_format($r['kumulatif']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const kurvaData = <?= json_encode($kurva) ?>;
    const realisasiData = <?= json_encode($realisasi) ?>;

    if (kurvaData.length > 0 || realisasiData.length > 0) {
        new Chart(document.getElementById('kurvaChart'), {
            type: 'line',
            data: {
                labels: kurvaData.map(k => k.tanggal_target),
                datasets: [
                    {
                        label: 'Target Kumulatif',
                        data: kurvaData.map(k => k.target_kumulatif_absolut),
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59,130,246,0.1)',
                        fill: true,
                        tension: 0.3,
                    },
                    {
                        label: 'Realisasi Kumulatif',
                        data: realisasiData.map(r => ({ x: r.tanggal_realisasi, y: r.kumulatif })),
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16,185,129,0.1)',
                        fill: false,
                        tension: 0.3,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true } }
            }
        });
    }
});
</script>

<?= $this->endSection() ?>
