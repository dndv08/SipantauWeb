<?= $this->extend('layouts/petugas_layout') ?>
<?= $this->section('content') ?>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Pantau Progress</h1>
    <p class="text-sm text-gray-500 mt-1">Grafik Kurva S Target vs Realisasi</p>
</div>

<!-- Pilih Kegiatan -->
<div class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
        <label class="text-sm font-medium text-gray-700">Pilih Kegiatan:</label>
        <select id="selectPCL" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 flex-1 max-w-md">
            <?php foreach ($kegiatanPCL as $k): ?>
            <option value="<?= $k['id'] ?>" <?= $k['id'] == $defaultPCL ? 'selected' : '' ?>>
                <?= esc($k['nama_kegiatan_detail_proses']) ?> (Target: <?= number_format($k['target']) ?>)
            </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<?php if (empty($kegiatanPCL)): ?>
<div class="bg-white rounded-xl border border-gray-200 p-8 text-center">
    <i class="fas fa-chart-line text-4xl text-gray-300 mb-3"></i>
    <p class="text-gray-500">Belum ada kegiatan PCL/PML yang di-assign.</p>
</div>
<?php else: ?>
<!-- Kurva S Chart -->
<div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
    <h3 class="text-sm font-semibold text-gray-900 mb-4"><i class="fas fa-chart-line text-blue-600 mr-2"></i>Kurva S</h3>
    <div style="height: 350px;">
        <canvas id="kurvaChart"></canvas>
    </div>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div class="bg-white rounded-xl border p-4 text-center">
        <p class="text-xs text-gray-500">Target</p>
        <p class="text-xl font-bold text-gray-900" id="targetValue"><?= number_format($kegiatanPCL[0]['target'] ?? 0) ?></p>
    </div>
    <div class="bg-white rounded-xl border p-4 text-center">
        <p class="text-xs text-gray-500">Realisasi</p>
        <p class="text-xl font-bold text-green-600" id="realisasiValue"><?= number_format($kegiatanPCL[0]['realisasi_kumulatif'] ?? 0) ?></p>
    </div>
    <div class="bg-white rounded-xl border p-4 text-center">
        <p class="text-xs text-gray-500">Persentase</p>
        <?php
        $persen = ($kegiatanPCL[0]['target'] ?? 0) > 0 ? round(($kegiatanPCL[0]['realisasi_kumulatif'] / $kegiatanPCL[0]['target']) * 100, 1) : 0;
        ?>
        <p class="text-xl font-bold text-blue-600" id="persenValue"><?= $persen ?>%</p>
    </div>
</div>
<?php endif; ?>

<script>
let kurvaChartInstance = null;

function renderChart(target, realisasi) {
    const ctx = document.getElementById('kurvaChart');
    if (!ctx) return;
    if (kurvaChartInstance) kurvaChartInstance.destroy();

    const labels = target.map(k => k.tanggal_target);
    const targetData = target.map(k => parseInt(k.target_kumulatif_absolut));

    // Map realisasi by date
    const realisasiMap = {};
    realisasi.forEach(r => { realisasiMap[r.tanggal] = parseInt(r.kumulatif); });
    const realisasiData = labels.map(l => realisasiMap[l] ?? null);

    kurvaChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Target Kumulatif',
                    data: targetData,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59,130,246,0.08)',
                    fill: true, tension: 0.3, borderWidth: 2,
                },
                {
                    label: 'Realisasi Kumulatif',
                    data: realisasiData,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16,185,129,0.08)',
                    fill: false, tension: 0.3, borderWidth: 2, spanGaps: true,
                }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            scales: { y: { beginAtZero: true }, x: { display: true, ticks: { maxTicksLimit: 15 } } },
            plugins: { legend: { position: 'top' } }
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const targetData = <?= json_encode($kurvaTarget) ?>;
    const realisasiData = <?= json_encode($kurvaRealisasi) ?>;
    renderChart(targetData, realisasiData);

    document.getElementById('selectPCL')?.addEventListener('change', function() {
        fetch('<?= base_url('petugas/pantau-progress/get-data') ?>?id_pcl=' + this.value)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    renderChart(data.kurvaTarget, data.kurvaRealisasi);
                    const lastReal = data.kurvaRealisasi.length > 0 ? parseInt(data.kurvaRealisasi[data.kurvaRealisasi.length-1].kumulatif) : 0;
                    const target = parseInt(data.target);
                    document.getElementById('targetValue').textContent = target.toLocaleString();
                    document.getElementById('realisasiValue').textContent = lastReal.toLocaleString();
                    document.getElementById('persenValue').textContent = (target > 0 ? (lastReal / target * 100).toFixed(1) : 0) + '%';
                }
            });
    });
});
</script>

<?= $this->endSection() ?>
