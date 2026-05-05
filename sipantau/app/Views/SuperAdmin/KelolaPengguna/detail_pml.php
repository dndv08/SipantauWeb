<?= $this->extend('layouts/sadmin_layout') ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="mb-6">
    <div class="flex items-center mb-2">
        <a href="<?= base_url('superadmin/kelola-pengguna/detail/' . $pml['sobat_id']) ?>" 
           class="text-gray-600 hover:text-gray-900 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Kembali
        </a>
    </div>
    <h1 class="text-2xl font-bold text-gray-900">Detail PML</h1>
    <p class="text-gray-600 mt-1"><?= esc($pml['nama_pml']) ?> - <?= esc($pml['nama_kegiatan_detail_proses']) ?></p>
</div>

<!-- Info Card -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div>
            <p class="text-sm text-gray-600">Target PML</p>
            <p class="text-2xl font-bold text-blue-600"><?= number_format($pml['target']) ?></p>
        </div>
        <div>
            <p class="text-sm text-gray-600">Jumlah PCL</p>
            <p class="text-2xl font-bold text-purple-600"><?= count($pclList) ?></p>
        </div>
        <div>
            <p class="text-sm text-gray-600">Tanggal Mulai</p>
            <p class="text-lg font-semibold text-gray-900">
                <?= date('d M Y', strtotime($pml['tanggal_mulai'])) ?>
            </p>
        </div>
        <div>
            <p class="text-sm text-gray-600">Tanggal Selesai</p>
            <p class="text-lg font-semibold text-gray-900">
                <?= date('d M Y', strtotime($pml['tanggal_selesai'])) ?>
            </p>
        </div>
    </div>
</div>

<!-- PCL List -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">Daftar PCL di Bawah PML Ini</h2>
    
    <?php if (!empty($pclList)) : ?>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 border w-16">No</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 border">Nama PCL</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 border">Target</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 border">Realisasi</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 border">Progress</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 border">Status</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 border">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $totalTarget = 0;
                    $totalRealisasi = 0;
                    foreach ($pclList as $index => $pcl) : 
                        $realisasi = (int)($pcl['realisasi_kumulatif'] ?? 0);
                        $target = (int)$pcl['target'];
                        $persentase = $target > 0 ? round(($realisasi / $target) * 100, 2) : 0;
                        
                        $totalTarget += $target;
                        $totalRealisasi += $realisasi;
                        
                        // Determine status color
                        if ($persentase >= 100) {
                            $statusClass = 'bg-green-100 text-green-800';
                            $statusText = 'Complete';
                        } elseif ($persentase >= 80) {
                            $statusClass = 'bg-yellow-100 text-yellow-800';
                            $statusText = 'Hampir Selesai';
                        } elseif ($persentase >= 50) {
                            $statusClass = 'bg-blue-100 text-blue-800';
                            $statusText = 'On Progress';
                        } else {
                            $statusClass = 'bg-red-100 text-red-800';
                            $statusText = 'Perlu Perhatian';
                        }
                    ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-center text-sm border"><?= $index + 1 ?></td>
                            <td class="px-4 py-3 text-sm border">
                                <span class="font-medium text-gray-900"><?= esc($pcl['nama_pcl']) ?></span>
                                <br>
                                <small class="text-gray-500"><?= esc($pcl['email']) ?></small>
                            </td>
                            <td class="px-4 py-3 text-center text-sm font-semibold border">
                                <?= number_format($target) ?>
                            </td>
                            <td class="px-4 py-3 text-center text-sm font-semibold text-blue-600 border">
                                <?= number_format($realisasi) ?>
                            </td>
                            <td class="px-4 py-3 border">
                                <div class="flex flex-col items-center">
                                    <div class="w-full bg-gray-200 rounded-full h-2.5 mb-1">
                                        <div class="bg-blue-600 h-2.5 rounded-full" 
                                             style="width: <?= min($persentase, 100) ?>%"></div>
                                    </div>
                                    <span class="text-xs font-medium text-gray-700">
                                        <?= number_format($persentase, 1) ?>%
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center border">
                                <span class="inline-flex px-3 py-1 <?= $statusClass ?> text-xs font-semibold rounded-full">
                                    <?= $statusText ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center border">
                                <a href="<?= base_url('superadmin/kelola-pengguna/detail-pcl/' . $pcl['id_pcl']) ?>" 
                                   class="inline-flex items-center px-3 py-1 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition-colors">
                                    <i class="fas fa-eye mr-1"></i>
                                    Detail
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    
                    <!-- Summary Row -->
                    <tr class="bg-blue-50 font-semibold">
                        <td colspan="2" class="px-4 py-3 text-right text-sm border">TOTAL</td>
                        <td class="px-4 py-3 text-center text-sm border">
                            <?= number_format($totalTarget) ?>
                        </td>
                        <td class="px-4 py-3 text-center text-sm text-blue-600 border">
                            <?= number_format($totalRealisasi) ?>
                        </td>
                        <td class="px-4 py-3 border">
                            <?php 
                            $totalPersentase = $totalTarget > 0 ? round(($totalRealisasi / $totalTarget) * 100, 2) : 0;
                            ?>
                            <div class="flex flex-col items-center">
                                <div class="w-full bg-gray-200 rounded-full h-2.5 mb-1">
                                    <div class="bg-blue-600 h-2.5 rounded-full" 
                                         style="width: <?= min($totalPersentase, 100) ?>%"></div>
                                </div>
                                <span class="text-xs font-medium text-gray-700">
                                    <?= number_format($totalPersentase, 1) ?>%
                                </span>
                            </div>
                        </td>
                        <td colspan="2" class="px-4 py-3 border"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    <?php else : ?>
        <div class="text-center py-8 text-gray-500">
            <i class="fas fa-inbox text-4xl mb-4"></i>
            <p>Belum ada PCL yang ditugaskan.</p>
        </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>