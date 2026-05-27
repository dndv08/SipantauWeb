<?= $this->extend('layouts/petugas_layout') ?>
<?= $this->section('content') ?>

<div class="mb-6">
    <div class="flex items-center gap-3">
        <a href="<?= base_url('petugas/daftar-kegiatan') ?>" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left"></i></a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Detail Kegiatan PML</h1>
            <p class="text-sm text-gray-500 mt-1"><?= esc($pml['nama_kegiatan_detail_proses']) ?></p>
        </div>
    </div>
</div>

<!-- Info -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-xl border p-4">
        <p class="text-xs text-gray-500">Target PML</p>
        <p class="text-xl font-bold text-gray-900"><?= number_format($pml['target']) ?></p>
    </div>
    <div class="bg-white rounded-xl border p-4">
        <p class="text-xs text-gray-500">Periode</p>
        <p class="text-sm font-medium text-gray-900"><?= date('d/m/Y', strtotime($pml['tanggal_mulai'])) ?> - <?= date('d/m/Y', strtotime($pml['tanggal_selesai'])) ?></p>
    </div>
    <div class="bg-white rounded-xl border p-4">
        <p class="text-xs text-gray-500">Kabupaten</p>
        <p class="text-sm font-medium text-gray-900"><?= esc($pml['nama_kabupaten']) ?></p>
    </div>
</div>

<!-- Feedback -->
<?php if (!empty($pml['feedback_admin'])): ?>
<div class="bg-purple-50 rounded-xl border border-purple-200 p-4 mb-6">
    <p class="text-xs font-medium text-purple-700 mb-1"><i class="fas fa-comment mr-1"></i>Feedback Admin</p>
    <p class="text-sm text-gray-700"><?= esc($pml['feedback_admin']) ?></p>
</div>
<?php endif; ?>

<!-- Daftar PCL -->
<div class="bg-white rounded-xl border border-gray-200 p-6">
    <h3 class="text-sm font-semibold text-gray-900 mb-4"><i class="fas fa-users text-blue-600 mr-2"></i>PCL di bawah Anda (<?= count($pclList) ?>)</h3>
    <?php if (empty($pclList)): ?>
        <p class="text-center text-gray-400 py-4">Belum ada PCL yang di-assign</p>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="bg-gray-50 text-left">
                <th class="px-4 py-3 font-medium text-gray-600">Nama PCL</th>
                <th class="px-4 py-3 font-medium text-gray-600">HP</th>
                <th class="px-4 py-3 font-medium text-gray-600">Target</th>
                <th class="px-4 py-3 font-medium text-gray-600">Realisasi</th>
                <th class="px-4 py-3 font-medium text-gray-600">Progress</th>
                <th class="px-4 py-3 font-medium text-gray-600">Laporan</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($pclList as $pcl):
                    $persen = $pcl['target'] > 0 ? round(($pcl['realisasi_kumulatif'] / $pcl['target']) * 100, 1) : 0;
                    $colorClass = $persen >= 100 ? 'bg-green-100 text-green-700' : ($persen >= 50 ? 'bg-blue-100 text-blue-700' : 'bg-orange-100 text-orange-700');
                ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-900"><?= esc($pcl['nama_pcl']) ?></td>
                    <td class="px-4 py-3 text-gray-500"><?= esc($pcl['hp'] ?? '-') ?></td>
                    <td class="px-4 py-3"><?= number_format($pcl['target']) ?></td>
                    <td class="px-4 py-3 text-green-600 font-medium"><?= number_format($pcl['realisasi_kumulatif']) ?></td>
                    <td class="px-4 py-3"><span class="px-2 py-0.5 rounded text-xs font-semibold <?= $colorClass ?>"><?= $persen ?>%</span></td>
                    <td class="px-4 py-3 text-gray-500"><?= $pcl['total_laporan'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
