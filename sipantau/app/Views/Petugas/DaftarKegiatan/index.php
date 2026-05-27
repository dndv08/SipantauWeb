<?= $this->extend('layouts/petugas_layout') ?>
<?= $this->section('content') ?>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Daftar Kegiatan</h1>
    <p class="text-sm text-gray-500 mt-1">Kegiatan yang di-assign kepada Anda</p>
</div>

<!-- Kegiatan PCL -->
<?php if (!empty($kegiatanPCL)): ?>
<div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
    <h3 class="text-sm font-semibold text-gray-900 mb-4"><i class="fas fa-user text-blue-600 mr-2"></i>Kegiatan Sebagai PCL</h3>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="bg-gray-50 text-left">
                <th class="px-4 py-3 font-medium text-gray-600">Kegiatan</th>
                <th class="px-4 py-3 font-medium text-gray-600">PML</th>
                <th class="px-4 py-3 font-medium text-gray-600">Target</th>
                <th class="px-4 py-3 font-medium text-gray-600">Realisasi</th>
                <th class="px-4 py-3 font-medium text-gray-600">Progress</th>
                <th class="px-4 py-3 font-medium text-gray-600">Periode</th>
                <th class="px-4 py-3 font-medium text-gray-600">Aksi</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($kegiatanPCL as $k):
                    $persen = $k['target'] > 0 ? round(($k['realisasi_kumulatif'] / $k['target']) * 100, 1) : 0;
                    $colorClass = $persen >= 100 ? 'bg-green-100 text-green-700' : ($persen >= 50 ? 'bg-blue-100 text-blue-700' : 'bg-orange-100 text-orange-700');
                ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-900"><?= esc($k['nama_kegiatan_detail_proses']) ?></p>
                        <p class="text-xs text-gray-500"><?= esc($k['nama_kabupaten']) ?></p>
                    </td>
                    <td class="px-4 py-3 text-gray-700"><?= esc($k['nama_pml']) ?></td>
                    <td class="px-4 py-3 font-medium"><?= number_format($k['target']) ?></td>
                    <td class="px-4 py-3 font-medium text-green-600"><?= number_format($k['realisasi_kumulatif']) ?></td>
                    <td class="px-4 py-3"><span class="px-2 py-0.5 rounded text-xs font-semibold <?= $colorClass ?>"><?= $persen ?>%</span></td>
                    <td class="px-4 py-3 text-xs text-gray-500"><?= date('d/m/Y', strtotime($k['tanggal_mulai'])) ?> - <?= date('d/m/Y', strtotime($k['tanggal_selesai'])) ?></td>
                    <td class="px-4 py-3">
                        <a href="<?= base_url('petugas/daftar-kegiatan/detail-pcl/' . $k['id_pcl']) ?>" class="text-blue-600 hover:underline text-xs">Detail</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Kegiatan PML -->
<?php if (!empty($kegiatanPML)): ?>
<div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
    <h3 class="text-sm font-semibold text-gray-900 mb-4"><i class="fas fa-user-tie text-yellow-600 mr-2"></i>Kegiatan Sebagai PML</h3>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="bg-gray-50 text-left">
                <th class="px-4 py-3 font-medium text-gray-600">Kegiatan</th>
                <th class="px-4 py-3 font-medium text-gray-600">Target PML</th>
                <th class="px-4 py-3 font-medium text-gray-600">Jumlah PCL</th>
                <th class="px-4 py-3 font-medium text-gray-600">Target PCL</th>
                <th class="px-4 py-3 font-medium text-gray-600">Periode</th>
                <th class="px-4 py-3 font-medium text-gray-600">Aksi</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($kegiatanPML as $k): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-900"><?= esc($k['nama_kegiatan_detail_proses']) ?></p>
                        <p class="text-xs text-gray-500"><?= esc($k['nama_kabupaten']) ?></p>
                    </td>
                    <td class="px-4 py-3 font-medium"><?= number_format($k['target']) ?></td>
                    <td class="px-4 py-3"><?= $k['jumlah_pcl'] ?></td>
                    <td class="px-4 py-3"><?= number_format($k['total_target_pcl']) ?></td>
                    <td class="px-4 py-3 text-xs text-gray-500"><?= date('d/m/Y', strtotime($k['tanggal_mulai'])) ?> - <?= date('d/m/Y', strtotime($k['tanggal_selesai'])) ?></td>
                    <td class="px-4 py-3">
                        <a href="<?= base_url('petugas/daftar-kegiatan/detail-pml/' . $k['id_pml']) ?>" class="text-blue-600 hover:underline text-xs">Detail</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php if (empty($kegiatanPCL) && empty($kegiatanPML)): ?>
<div class="bg-white rounded-xl border border-gray-200 p-8 text-center">
    <i class="fas fa-inbox text-4xl text-gray-300 mb-3"></i>
    <p class="text-gray-500">Belum ada kegiatan yang di-assign.</p>
</div>
<?php endif; ?>

<?= $this->endSection() ?>
