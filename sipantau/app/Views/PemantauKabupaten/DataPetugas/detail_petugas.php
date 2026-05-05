<?= $this->extend('layouts/pemantau_kabupaten_layout') ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="mb-6">
    <div class="flex items-center mb-2">
        <a href="<?= base_url('pemantau-kabupaten/data-petugas') ?>" class="text-gray-600 hover:text-gray-900 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Kembali
        </a>
    </div>
    <h1 class="text-2xl font-bold text-gray-900">Detail Petugas</h1>
    <p class="text-gray-600 mt-1">Kegiatan yang pernah diikuti oleh <?= esc($petugas['nama_user']) ?></p>
</div>

<!-- Petugas Info Card -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
            <p class="text-sm text-gray-600">Nama Lengkap</p>
            <p class="text-lg font-semibold text-gray-900"><?= esc($petugas['nama_user']) ?></p>
        </div>
        <div>
            <p class="text-sm text-gray-600">Sobat ID</p>
            <p class="text-lg font-semibold text-gray-900"><?= esc($petugas['sobat_id']) ?></p>
        </div>
        <div>
            <p class="text-sm text-gray-600">Email</p>
            <p class="text-lg font-semibold text-gray-900"><?= esc($petugas['email']) ?></p>
        </div>
    </div>
</div>

<!-- Kegiatan List -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">Daftar Kegiatan yang Diikuti</h2>
    
    <?php if (!empty($kegiatanList)) : ?>
        <div class="space-y-4">
            <?php foreach ($kegiatanList as $kegiatan) : ?>
                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <?php
                            // Tentukan URL berdasarkan role
                            if ($kegiatan['role'] === 'PML') {
                                $detailUrl = base_url('pemantau-kabupaten/data-petugas/detail-pml/' . $kegiatan['id']);
                            } else {
                                $detailUrl = base_url('pemantau-kabupaten/data-petugas/detail-pcl/' . $kegiatan['id']);
                            }
                            ?>
                            <a href="<?= $detailUrl ?>" class="text-blue-600 hover:text-blue-800 hover:underline">
                                <h3 class="text-base font-semibold text-gray-900 mb-2">
                                    <?= esc($kegiatan['nama_kegiatan']) ?>
                                </h3>
                            </a>
                            <p class="text-sm text-gray-700 mb-2">
                                <span class="font-medium">Detail:</span> <?= esc($kegiatan['nama_kegiatan_detail']) ?>
                            </p>
                            <p class="text-sm text-gray-700 mb-2">
                                <span class="font-medium">Proses:</span> <?= esc($kegiatan['nama_kegiatan_detail_proses']) ?>
                            </p>
                            <div class="flex items-center gap-4 text-sm text-gray-600">
                                <span>
                                    <i class="fas fa-calendar mr-1"></i>
                                    <?= date('d M Y', strtotime($kegiatan['tanggal_mulai'])) ?> - 
                                    <?= date('d M Y', strtotime($kegiatan['tanggal_selesai'])) ?>
                                </span>
                                <span>
                                    <i class="fas fa-bullseye mr-1"></i>
                                    Target: <?= number_format($kegiatan['target']) ?>
                                </span>
                                <?php if ($kegiatan['role'] === 'PCL' && !empty($kegiatan['nama_pml'])) : ?>
                                    <span>
                                        <i class="fas fa-user-tie mr-1"></i>
                                        PML: <?= esc($kegiatan['nama_pml']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="ml-4">
                            <?php 
                            $roleColor = $kegiatan['role'] === 'PML' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800';
                            ?>
                            <span class="inline-flex px-3 py-1 <?= $roleColor ?> text-xs font-semibold rounded-full">
                                <?= $kegiatan['role'] ?>
                            </span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else : ?>
        <div class="text-center py-8 text-gray-500">
            <i class="fas fa-inbox text-4xl mb-4"></i>
            <p>Belum ada kegiatan yang diikuti.</p>
        </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>