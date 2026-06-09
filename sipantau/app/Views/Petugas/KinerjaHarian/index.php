<?= $this->extend('layouts/petugas_layout') ?>
<?= $this->section('content') ?>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Kinerja Harian</h1>
    <p class="text-sm text-gray-500 mt-1">Perbandingan target dan realisasi per hari</p>
</div>

<!-- Pilih Kegiatan -->
<div class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
    <form method="get" class="flex flex-col sm:flex-row sm:items-center gap-3">
        <label class="text-sm font-medium text-gray-700 whitespace-nowrap">Pilih Kegiatan:</label>
        <select name="id_pcl" onchange="this.form.submit()" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 flex-1 max-w-lg">
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
                <option value="<?= $k['id'] ?>" <?= $k['id'] == $selectedPCL ? 'selected' : '' ?>>
                    <?= esc($label) ?>
                </option>
                <?php endforeach; ?>
            </optgroup>
            <?php endforeach; ?>
        </select>
    </form>
</div>

<?php if (empty($kegiatanPCL)): ?>
<div class="bg-white rounded-xl border border-gray-200 p-8 text-center">
    <i class="fas fa-calendar-check text-4xl text-gray-300 mb-3"></i>
    <p class="text-gray-500">Belum ada kegiatan PCL/PML yang di-assign.</p>
</div>
<?php elseif (empty($kinerjaharian)): ?>
<div class="bg-white rounded-xl border border-gray-200 p-8 text-center">
    <i class="fas fa-calendar text-4xl text-gray-300 mb-3"></i>
    <p class="text-gray-500">Belum ada data kurva target untuk kegiatan ini.</p>
    <p class="text-xs text-gray-400 mt-1">Kurva S akan digenerate otomatis atau diatur oleh Admin.</p>
</div>
<?php else: ?>

<!-- Summary -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <?php
    $totalTargetH = array_sum(array_column($kinerjaharian, 'target_harian'));
    $totalRealisasiH = array_sum(array_column($kinerjaharian, 'realisasi_harian'));
    $hariTercapai = count(array_filter($kinerjaharian, fn($k) => $k['realisasi_harian'] >= $k['target_harian'] && $k['target_harian'] > 0));
    ?>
    <div class="bg-white rounded-xl border p-4 text-center">
        <p class="text-xs text-gray-500">Total Target Harian</p>
        <p class="text-xl font-bold text-gray-900"><?= number_format($totalTargetH) ?></p>
    </div>
    <div class="bg-white rounded-xl border p-4 text-center">
        <p class="text-xs text-gray-500">Total Realisasi</p>
        <p class="text-xl font-bold text-green-600"><?= number_format($totalRealisasiH) ?></p>
    </div>
    <div class="bg-white rounded-xl border p-4 text-center">
        <p class="text-xs text-gray-500">Hari Target Tercapai</p>
        <p class="text-xl font-bold text-blue-600"><?= $hariTercapai ?></p>
    </div>
</div>

<!-- Tabel Kinerja -->
<div class="bg-white rounded-xl border border-gray-200 p-6">
    <h3 class="text-sm font-semibold text-gray-900 mb-4"><i class="fas fa-table text-gray-600 mr-2"></i>Tabel Kinerja Harian</h3>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-left">
                    <th class="px-3 py-2 font-medium text-gray-600">Tanggal</th>
                    <th class="px-3 py-2 font-medium text-gray-600 text-right">Target Harian</th>
                    <th class="px-3 py-2 font-medium text-gray-600 text-right">Target Kum.</th>
                    <th class="px-3 py-2 font-medium text-gray-600 text-right">Realisasi Harian</th>
                    <th class="px-3 py-2 font-medium text-gray-600 text-right">Realisasi Kum.</th>
                    <th class="px-3 py-2 font-medium text-gray-600 text-right">Selisih</th>
                    <th class="px-3 py-2 font-medium text-gray-600 text-center">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($kinerjaharian as $k):
                    $isToday = $k['tanggal'] === date('Y-m-d');
                    $isPast = $k['tanggal'] < date('Y-m-d');
                    $statusClass = '';
                    $statusText = '-';
                    if ($k['target_harian'] > 0 && $isPast) {
                        if ($k['realisasi_harian'] >= $k['target_harian']) {
                            $statusClass = 'bg-green-100 text-green-700';
                            $statusText = '✓ Tercapai';
                        } else {
                            $statusClass = 'bg-red-100 text-red-700';
                            $statusText = '✗ Kurang';
                        }
                    } elseif ($isToday) {
                        $statusClass = 'bg-blue-100 text-blue-700';
                        $statusText = 'Hari Ini';
                    } elseif (!$k['is_hari_kerja']) {
                        $statusClass = 'bg-gray-100 text-gray-500';
                        $statusText = 'Libur';
                    }
                ?>
                <tr class="hover:bg-gray-50 <?= $isToday ? 'bg-blue-50/30' : '' ?>">
                    <td class="px-3 py-2 whitespace-nowrap <?= $isToday ? 'font-semibold text-blue-700' : 'text-gray-700' ?>"><?= date('d/m/Y', strtotime($k['tanggal'])) ?></td>
                    <td class="px-3 py-2 text-right"><?= number_format($k['target_harian']) ?></td>
                    <td class="px-3 py-2 text-right text-gray-500"><?= number_format($k['target_kumulatif']) ?></td>
                    <td class="px-3 py-2 text-right font-medium text-green-600"><?= $k['realisasi_harian'] > 0 ? number_format($k['realisasi_harian']) : '-' ?></td>
                    <td class="px-3 py-2 text-right font-medium text-blue-600"><?= $k['realisasi_kumulatif'] > 0 ? number_format($k['realisasi_kumulatif']) : '-' ?></td>
                    <td class="px-3 py-2 text-right <?= $k['selisih_harian'] >= 0 ? 'text-green-600' : 'text-red-600' ?>"><?= $k['realisasi_harian'] > 0 ? ($k['selisih_harian'] >= 0 ? '+' : '') . number_format($k['selisih_harian']) : '-' ?></td>
                    <td class="px-3 py-2 text-center">
                        <?php if ($statusText !== '-'): ?>
                        <span class="px-2 py-0.5 rounded text-xs font-medium <?= $statusClass ?>"><?= $statusText ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>
