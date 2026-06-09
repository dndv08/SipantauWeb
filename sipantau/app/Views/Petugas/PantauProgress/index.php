<?= $this->extend('layouts/petugas_layout') ?>
<?= $this->section('content') ?>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Pantau Progress</h1>
    <p class="text-sm text-gray-500 mt-1">Grafik Kurva S Target vs Realisasi</p>
</div>

<!-- Pilih Kegiatan -->
<div class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
        <label class="text-sm font-medium text-gray-700 whitespace-nowrap">Pilih Kegiatan:</label>
        <select id="selectPCL" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 flex-1 max-w-lg">
            <?php 
            // Group kegiatan by nama_kegiatan for optgroup
            $grouped = [];
            foreach ($kegiatanPCL as $k) {
                $groupName = $k['nama_kegiatan'] ?? 'Lainnya';
                $grouped[$groupName][] = $k;
            }
            foreach ($grouped as $groupName => $items): ?>
            <optgroup label="<?= esc($groupName) ?>">
                <?php foreach ($items as $k): 
                    $labelParts = [];
                    $labelParts[] = $k['nama_kegiatan_detail_proses'];
                    if (!empty($k['nama_kegiatan_detail']) && $k['nama_kegiatan_detail'] !== $k['nama_kegiatan_detail_proses']) {
                        $labelParts[] = '(' . $k['nama_kegiatan_detail'] . ')';
                    }
                    $labelParts[] = '• Target: ' . number_format($k['target']);
                    if (!empty($k['tanggal_mulai']) && !empty($k['tanggal_selesai'])) {
                        $labelParts[] = '• ' . date('d/m/Y', strtotime($k['tanggal_mulai'])) . ' - ' . date('d/m/Y', strtotime($k['tanggal_selesai']));
                    }
                    $label = implode(' ', $labelParts);
                ?>
                <option value="<?= $k['id'] ?>" <?= $k['id'] == $defaultPCL ? 'selected' : '' ?>
                        data-target="<?= $k['target'] ?>"
                        data-realisasi="<?= $k['realisasi_kumulatif'] ?>">
                    <?= esc($label) ?>
                </option>
                <?php endforeach; ?>
            </optgroup>
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
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-semibold text-gray-900"><i class="fas fa-chart-line text-blue-600 mr-2"></i>Kurva S</h3>
        <span class="text-xs text-gray-400 italic" id="chartDateRange"></span>
    </div>
    <div style="height: 380px;">
        <canvas id="kurvaChart"></canvas>
    </div>
</div>

<?php
// Find the selected kegiatan (matching $defaultPCL) for correct initial card values
$selectedKegiatan = null;
foreach ($kegiatanPCL as $_k) {
    if ($_k['id'] == $defaultPCL) { $selectedKegiatan = $_k; break; }
}
if (!$selectedKegiatan) $selectedKegiatan = $kegiatanPCL[0] ?? [];
$initTarget    = (int)($selectedKegiatan['target'] ?? 0);
$initRealisasi = (int)($selectedKegiatan['realisasi_kumulatif'] ?? 0);
$persen = $initTarget > 0 ? round(($initRealisasi / $initTarget) * 100, 1) : 0;
?>
<!-- Summary Cards -->
<div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl border p-4 text-center">
        <p class="text-xs text-gray-500 mb-1">Target</p>
        <p class="text-xl font-bold text-gray-900" id="targetValue"><?= number_format($initTarget) ?></p>
    </div>
    <div class="bg-white rounded-xl border p-4 text-center">
        <p class="text-xs text-gray-500 mb-1">Realisasi</p>
        <p class="text-xl font-bold text-green-600" id="realisasiValue"><?= number_format($initRealisasi) ?></p>
    </div>
    <div class="bg-white rounded-xl border p-4 text-center">
        <p class="text-xs text-gray-500 mb-1">Persentase</p>
        <p class="text-xl font-bold text-blue-600" id="persenValue"><?= $persen ?>%</p>
    </div>
    <div class="bg-white rounded-xl border p-4 text-center">
        <p class="text-xs text-gray-500 mb-1"><i class="fas fa-trophy text-yellow-500 mr-1"></i>Hari Terbaik</p>
        <p class="text-sm font-bold text-yellow-600" id="bestDayValue">-</p>
        <p class="text-xs text-gray-400 mt-0.5" id="bestDayDetail"></p>
    </div>
</div>

<!-- Progress Bar -->
<div class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
    <div class="flex justify-between items-center mb-2">
        <span class="text-sm font-medium text-gray-700">Progress Keseluruhan</span>
        <span class="text-sm font-bold" id="progressPersenText"><?= $persen ?>%</span>
    </div>
    <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
        <div id="progressBar" class="h-3 rounded-full transition-all duration-700 ease-out <?= $persen >= 100 ? 'bg-green-500' : ($persen >= 50 ? 'bg-blue-500' : 'bg-orange-500') ?>"
             style="width: <?= min($persen, 100) ?>%"></div>
    </div>
</div>
<?php endif; ?>

<script>
let kurvaChartInstance = null;

function findBestDay(realisasi) {
    // Find day with the biggest single-day gain in kumulatif
    if (!realisasi || realisasi.length < 2) return null;
    let bestDay = null;
    let bestGain = 0;
    let prevKum = 0;
    for (let i = 0; i < realisasi.length; i++) {
        const kum = parseInt(realisasi[i].kumulatif) || 0;
        const gain = kum - prevKum;
        if (gain > bestGain) {
            bestGain = gain;
            bestDay = { tanggal: realisasi[i].tanggal, gain: gain, kumulatif: kum };
        }
        prevKum = kum; // track all values since server gives full daily data
    }
    return bestGain > 0 ? bestDay : null;
}

function formatLabel(dateStr) {
    // Format YYYY-MM-DD → DD/MM for x-axis ticks
    const parts = dateStr.split('-');
    return parts[2] + '/' + parts[1];
}

function renderChart(target, realisasi) {
    const ctx = document.getElementById('kurvaChart');
    if (!ctx) return;
    if (kurvaChartInstance) kurvaChartInstance.destroy();

    // Server sends full daily data — use directly
    const labels = target.map(k => k.tanggal_target);
    const targetData = target.map(k => parseInt(k.target_kumulatif_absolut));

    // Realisasi: server already sends running kumulatif for every day
    const realisasiByDate = {};
    realisasi.forEach(r => { realisasiByDate[r.tanggal] = parseInt(r.kumulatif); });
    // Use full realisasi array (all days), not null-filled
    const realisasiData = labels.map(l => realisasiByDate[l] !== undefined ? realisasiByDate[l] : 0);

    // Find best day (largest daily gain)
    const bestDay = findBestDay(realisasi);

    // Point styling: only show dot on days with actual changes or best day
    const pointRadii = realisasiData.map((val, idx) => {
        if (bestDay && labels[idx] === bestDay.tanggal) return 8;
        // Show small dot only if value changed from previous
        if (idx > 0 && realisasiData[idx] !== realisasiData[idx-1]) return 4;
        return 0; // hide points on flat days
    });
    const pointColors = realisasiData.map((val, idx) => {
        if (bestDay && labels[idx] === bestDay.tanggal) return '#f59e0b';
        return '#10b981';
    });

    // Max value for Y axis
    const maxTarget = Math.max(...targetData, 0);
    const maxRealisasi = Math.max(...realisasiData, 0);
    const yMax = Math.ceil(Math.max(maxTarget, maxRealisasi) * 1.15) || 10;

    // Adaptive tick limit based on date range
    const totalDays = labels.length;
    const maxTicks = totalDays <= 30 ? totalDays : (totalDays <= 90 ? 15 : 12);

    // Update date range label
    const rangeEl = document.getElementById('chartDateRange');
    if (rangeEl && labels.length > 0) {
        const first = labels[0].split('-').reverse().join('/');
        const last = labels[labels.length-1].split('-').reverse().join('/');
        rangeEl.textContent = first + ' – ' + last;
    }

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
                    fill: true, tension: 0.3, borderWidth: 2.5,
                    pointRadius: 0,
                    pointHoverRadius: 5,
                },
                {
                    label: 'Realisasi Kumulatif',
                    data: realisasiData,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16,185,129,0.10)',
                    fill: true, tension: 0.3, borderWidth: 2.5,
                    pointRadius: pointRadii,
                    pointBackgroundColor: pointColors,
                    pointBorderColor: pointColors,
                    pointHoverRadius: 6,
                }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    suggestedMax: yMax,
                    grid: { color: 'rgba(0,0,0,0.05)' },
                    ticks: { precision: 0 }
                },
                x: {
                    display: true,
                    ticks: {
                        maxTicksLimit: maxTicks,
                        maxRotation: 45,
                        callback: function(val, idx) {
                            return formatLabel(labels[idx]);
                        }
                    },
                    grid: { display: false }
                }
            },
            plugins: {
                legend: { position: 'top' },
                tooltip: {
                    callbacks: {
                        title: function(context) {
                            const d = new Date(context[0].label);
                            return d.toLocaleDateString('id-ID', { weekday: 'short', day: '2-digit', month: 'short', year: 'numeric' });
                        },
                        afterBody: function(context) {
                            if (bestDay && context[0].label === bestDay.tanggal) {
                                return '⭐ Hari Terbaik! (+' + bestDay.gain.toLocaleString() + ')';
                            }
                            return '';
                        }
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });

    // Update best day display
    const bestDayEl = document.getElementById('bestDayValue');
    const bestDayDetailEl = document.getElementById('bestDayDetail');
    if (bestDay && bestDayEl) {
        const dt = new Date(bestDay.tanggal);
        bestDayEl.textContent = dt.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
        bestDayDetailEl.textContent = '+' + bestDay.gain.toLocaleString() + ' realisasi';
    } else if (bestDayEl) {
        bestDayEl.textContent = '-';
        bestDayDetailEl.textContent = '';
    }
}

function updateSummaryCards(targetVal, realisasiVal) {
    const target = parseInt(targetVal) || 0;
    const real = parseInt(realisasiVal) || 0;
    const persen = target > 0 ? (real / target * 100).toFixed(1) : 0;

    document.getElementById('targetValue').textContent = target.toLocaleString();
    document.getElementById('realisasiValue').textContent = real.toLocaleString();
    document.getElementById('persenValue').textContent = persen + '%';

    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressPersenText');
    if (progressBar) {
        progressBar.style.width = Math.min(persen, 100) + '%';
        progressBar.className = 'h-3 rounded-full transition-all duration-700 ease-out ' +
            (persen >= 100 ? 'bg-green-500' : (persen >= 50 ? 'bg-blue-500' : 'bg-orange-500'));
    }
    if (progressText) progressText.textContent = persen + '%';
}

document.addEventListener('DOMContentLoaded', function() {
    const targetData    = <?= json_encode($kurvaTarget) ?>;
    const realisasiData = <?= json_encode($kurvaRealisasi) ?>;
    renderChart(targetData, realisasiData);

    // Read target & realisasi from the SELECTED option's data attributes
    // These values come from sipantau_transaksi counts (accurate per-kegiatan)
    const selectEl = document.getElementById('selectPCL');
    if (selectEl) {
        const selOpt = selectEl.options[selectEl.selectedIndex];
        const initTarget    = parseInt(selOpt?.dataset?.target)    || 0;
        const initRealisasi = parseInt(selOpt?.dataset?.realisasi) || 0;
        updateSummaryCards(initTarget, initRealisasi);
    }

    selectEl?.addEventListener('change', function() {
        const selectedId  = this.value;
        const selOpt      = this.options[this.selectedIndex];

        // Immediately update cards with the option's known target while loading
        const quickTarget = parseInt(selOpt?.dataset?.target) || 0;
        updateSummaryCards(quickTarget, 0); // reset realisasi to 0 until AJAX returns

        fetch('<?= base_url('petugas/pantau-progress/get-data') ?>?id_pcl=' + selectedId)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    renderChart(data.kurvaTarget, data.kurvaRealisasi);
                    // realisasi_aktual = COUNT dari sipantau_transaksi (berapa yang sudah dikerjakan)
                    const realAktual = data.realisasi_aktual !== undefined
                        ? parseInt(data.realisasi_aktual)
                        : parseInt(data.kurvaRealisasi[data.kurvaRealisasi.length - 1]?.kumulatif || 0);
                    updateSummaryCards(data.target, realAktual);
                }
            })
            .catch(err => console.error('Error loading chart data:', err));
    });
});
</script>

<?= $this->endSection() ?>
