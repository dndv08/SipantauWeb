<?= $this->extend('layouts/sadmin_layout') ?>

<?= $this->section('content') ?>

<style>
.stat-card {
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
}
</style>

<!-- Page Header -->
<div class="mb-6">
    <div class="flex items-center text-sm text-gray-600 mb-4">
        <a href="<?= base_url('superadmin/kelola-admin-surveyprov') ?>" class="hover:text-blue-600 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Kembali
        </a>
    </div>
    <h1 class="text-2xl font-bold text-gray-900">Detail Admin Survei Provinsi</h1>
    <p class="text-gray-600 mt-1">Informasi lengkap assignment, kegiatan, dan progress</p>
</div>

<!-- Admin Info Card -->
<div class="card mb-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
        <div class="flex items-center mb-4 md:mb-0">
            <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center mr-4">
                <span class="text-white text-xl font-bold">
                    <?= strtoupper(substr($admin['nama_user'], 0, 2)) ?>
                </span>
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-900"><?= esc($admin['nama_user']) ?></h2>
                <p class="text-sm text-gray-600"><?= esc($admin['email']) ?></p>
                <div class="mt-1 flex items-center gap-2 flex-wrap">
                    <span class="inline-flex items-center px-2 py-1 bg-blue-50 text-blue-700 rounded text-xs font-medium">
                        <i class="fas fa-id-card mr-1"></i>
                        Sobat ID: <?= esc($admin['sobat_id']) ?>
                    </span>
                    <span class="inline-flex items-center px-2 py-1 bg-green-50 text-green-700 rounded text-xs font-medium">
                        <i class="fas fa-shield-alt mr-1"></i>
                        Admin Provinsi
                    </span>
                    <?php if (!empty($admin['hp'])): ?>
                    <span class="inline-flex items-center px-2 py-1 bg-purple-50 text-purple-700 rounded text-xs font-medium">
                        <i class="fas fa-phone mr-1"></i>
                        <?= esc($admin['hp']) ?>
                    </span>
                    <?php endif; ?>
                    <?php if (!empty($admin['nama_kabupaten'])): ?>
                    <span class="inline-flex items-center px-2 py-1 bg-orange-50 text-orange-700 rounded text-xs font-medium">
                        <i class="fas fa-map-marker-alt mr-1"></i>
                        <?= esc($admin['nama_kabupaten']) ?>
                    </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="flex gap-2">
            <a href="<?= base_url('superadmin/kelola-admin-surveyprov/assign/' . $admin['id_admin_provinsi']) ?>" 
               class="btn-primary">
                <i class="fas fa-edit mr-2"></i>
                Edit Assignment
            </a>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="card hover:shadow-md transition-shadow duration-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-1">Total Kegiatan</p>
                <h3 class="text-3xl font-bold text-gray-900"><?= count($kegiatan) ?></h3>
            </div>
            <div class="w-14 h-14 bg-blue-50 rounded-lg flex items-center justify-center">
                <i class="fas fa-tasks text-2xl text-[#1e88e5]"></i>
            </div>
        </div>
    </div>
    
    <div class="card hover:shadow-md transition-shadow duration-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-1">Kegiatan Aktif</p>
                <h3 class="text-3xl font-bold text-gray-900">
                    <?php 
                    $aktif = 0;
                    $today = date('Y-m-d');
                    foreach ($kegiatan as $k) {
                        if (!empty($k['tanggal_mulai']) && !empty($k['tanggal_selesai'])) {
                            if ($k['tanggal_mulai'] <= $today && $k['tanggal_selesai'] >= $today) {
                                $aktif++;
                            }
                        }
                    }
                    echo $aktif;
                    ?>
                </h3>
            </div>
            <div class="w-14 h-14 bg-green-50 rounded-lg flex items-center justify-center">
                <i class="fas fa-check-circle text-2xl text-[#43a047]"></i>
            </div>
        </div>
    </div>
    
    <div class="card hover:shadow-md transition-shadow duration-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-1">Total Proses</p>
                <h3 class="text-3xl font-bold text-gray-900">
                    <?php 
                    $totalProses = 0;
                    foreach ($kegiatan as $k) {
                        $totalProses += $k['total_proses'];
                    }
                    echo $totalProses;
                    ?>
                </h3>
            </div>
            <div class="w-14 h-14 bg-purple-50 rounded-lg flex items-center justify-center">
                <i class="fas fa-list-check text-2xl text-[#8e24aa]"></i>
            </div>
        </div>
    </div>
    
    <div class="card hover:shadow-md transition-shadow duration-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-1">Rata-rata Progress</p>
                <h3 class="text-3xl font-bold text-gray-900">
                    <?php 
                    $totalProgress = 0;
                    $countKegiatan = count($kegiatan);
                    foreach ($kegiatan as $k) {
                        $totalProgress += $k['overall_progress'];
                    }
                    echo $countKegiatan > 0 ? round($totalProgress / $countKegiatan, 1) : 0;
                    ?>%
                </h3>
            </div>
            <div class="w-14 h-14 bg-orange-50 rounded-lg flex items-center justify-center">
                <i class="fas fa-chart-line text-2xl text-[#fb8c00]"></i>
            </div>
        </div>
    </div>
</div>

<!-- Kegiatan List with Progress -->
<div class="card">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
        <div>
            <h3 class="text-lg font-semibold text-gray-900">Daftar Kegiatan yang Di-assign</h3>
            <p class="text-sm text-gray-600 mt-1">Monitoring progres berdasarkan laporan petugas lapangan</p>
        </div>
        
        <!-- Search Box -->
        <div class="relative w-full sm:w-64">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
            </div>
            <input type="text" id="searchKegiatan" 
                   class="input-field w-full pl-10 py-2" 
                   placeholder="Cari kegiatan...">
        </div>
    </div>
    
    <?php if (empty($kegiatan)): ?>
        <div class="text-center py-12">
            <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
            <p class="text-gray-500">Belum ada kegiatan yang di-assign</p>
        </div>
    <?php else: ?>
        <div class="space-y-6" id="kegiatanList">
            <?php foreach ($kegiatan as $index => $k): 
                $isActive = false;
                $statusClass = 'gray';
                $statusText = 'Belum Dimulai';
                $statusIcon = 'clock';
                
                if (!empty($k['tanggal_mulai']) && !empty($k['tanggal_selesai'])) {
                    $today = date('Y-m-d');
                    if ($k['tanggal_mulai'] <= $today && $k['tanggal_selesai'] >= $today) {
                        $isActive = true;
                        $statusClass = 'green';
                        $statusText = 'Sedang Berlangsung';
                        $statusIcon = 'play-circle';
                    } elseif ($k['tanggal_selesai'] < $today) {
                        $statusClass = 'blue';
                        $statusText = 'Selesai';
                        $statusIcon = 'check-circle';
                    }
                }
            ?>
            <div class="kegiatan-card border border-gray-200 rounded-xl overflow-hidden <?= $isActive ? 'border-green-200 shadow-sm' : '' ?> hover:shadow-md transition-shadow duration-200">
                <!-- Kegiatan Header -->
                <div class="bg-gradient-to-r from-gray-50 to-blue-50 p-4 border-b border-gray-200">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="w-10 h-10 bg-blue-50 border border-blue-200 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-clipboard-list text-[#1e88e5]"></i>
                                </div>
                                <div class="flex-1">
                                    <h4 class="text-base font-semibold text-gray-900 kegiatan-name">
                                        <?= esc($k['nama_kegiatan_detail']) ?>
                                    </h4>
                                    <p class="text-sm text-gray-600">
                                        <i class="fas fa-folder text-[#1e88e5] mr-1"></i>
                                        <?= esc($k['nama_kegiatan']) ?>
                                    </p>
                                </div>
                            </div>
                            
                            <div class="flex flex-wrap gap-2 mt-3">
                                <span class="inline-flex items-center px-2.5 py-1 bg-<?= $statusClass ?>-50 text-<?= $statusClass ?>-700 border border-<?= $statusClass ?>-200 rounded text-xs font-medium">
                                    <i class="fas fa-<?= $statusIcon ?> mr-1"></i>
                                    <?= $statusText ?>
                                </span>
                                <span class="inline-flex items-center px-2.5 py-1 bg-blue-50 text-blue-700 border border-blue-200 rounded text-xs font-medium">
                                    <i class="fas fa-ruler mr-1"></i>
                                    <?= esc($k['satuan']) ?>
                                </span>
                                <span class="inline-flex items-center px-2.5 py-1 bg-purple-50 text-purple-700 border border-purple-200 rounded text-xs font-medium">
                                    <i class="fas fa-calendar mr-1"></i>
                                    <?= esc($k['periode']) ?> <?= esc($k['tahun']) ?>
                                </span>
                                <?php if (!empty($k['tanggal_mulai']) && !empty($k['tanggal_selesai'])): ?>
                                <span class="inline-flex items-center px-2.5 py-1 bg-orange-50 text-orange-700 border border-orange-200 rounded text-xs font-medium">
                                    <i class="fas fa-calendar-check mr-1"></i>
                                    <?= date('d M Y', strtotime($k['tanggal_mulai'])) ?> - <?= date('d M Y', strtotime($k['tanggal_selesai'])) ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="ml-4 text-right">
                            <div class="text-3xl font-bold text-[#1e88e5]"><?= $k['overall_progress'] ?>%</div>
                            <div class="text-xs text-gray-500">Progress Keseluruhan</div>
                        </div>
                    </div>
                    
                    <!-- Overall Progress Bar -->
                    <div class="mt-4">
                        <div class="flex items-center justify-between text-xs text-gray-600 mb-1">
                            <span><?= $k['total_proses'] ?> proses kegiatan</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="bg-[#1e88e5] h-2.5 rounded-full transition-all duration-500" 
                                 style="width: <?= $k['overall_progress'] ?>%"></div>
                        </div>
                    </div>
                    
                    <?php if (!empty($k['keterangan'])): ?>
                    <div class="mt-3 p-2 bg-white rounded text-xs text-gray-600 border border-gray-200">
                        <i class="fas fa-info-circle text-[#1e88e5] mr-1"></i>
                        <?= esc($k['keterangan']) ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Proses List -->
                <div class="p-4">
                    <?php if (empty($k['proses_list'])): ?>
                        <div class="text-center py-6 bg-gray-50 rounded-lg">
                            <i class="fas fa-info-circle text-gray-400 text-2xl mb-2"></i>
                            <p class="text-sm text-gray-500">Belum ada proses untuk kegiatan ini</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between mb-3">
                                <h5 class="text-sm font-semibold text-gray-700">
                                    <i class="fas fa-list-check text-[#1e88e5] mr-2"></i>
                                    Detail Proses (<?= count($k['proses_list']) ?>)
                                </h5>
                                <button onclick="toggleProses('proses-<?= $k['id_kegiatan_detail'] ?>')" 
                                        class="text-xs text-[#1e88e5] hover:text-blue-700 font-medium transition-colors">
                                    <i class="fas fa-chevron-down mr-1" id="icon-proses-<?= $k['id_kegiatan_detail'] ?>"></i>
                                    <span id="text-proses-<?= $k['id_kegiatan_detail'] ?>">Lihat Detail</span>
                                </button>
                            </div>
                            
                            <div id="proses-<?= $k['id_kegiatan_detail'] ?>" class="space-y-3 hidden">
                                <?php foreach ($k['proses_list'] as $prosesIndex => $proses): ?>
                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 hover:border-blue-200 transition-colors">
                                    <div class="flex items-start justify-between mb-3">
                                        <div class="flex items-start gap-3 flex-1">
                                            <div class="w-8 h-8 bg-blue-50 text-[#1e88e5] border border-blue-200 rounded-lg flex items-center justify-center flex-shrink-0 text-sm font-semibold">
                                                <?= $prosesIndex + 1 ?>
                                            </div>
                                            <div class="flex-1">
                                                <p class="text-sm font-medium text-gray-900 mb-2"><?= esc($proses['nama_kegiatan_detail_proses']) ?></p>
                                                
                                                <!-- Info Grid -->
                                                <div class="grid grid-cols-2 gap-2 mb-2">
                                                    <?php if (!empty($proses['satuan'])): ?>
                                                    <div class="text-xs text-gray-600">
                                                        <i class="fas fa-ruler mr-1 text-gray-400"></i>
                                                        <span class="font-medium">Satuan:</span> <?= esc($proses['satuan']) ?>
                                                    </div>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($proses['periode'])): ?>
                                                    <div class="text-xs text-gray-600">
                                                        <i class="fas fa-calendar-alt mr-1 text-gray-400"></i>
                                                        <span class="font-medium">Periode:</span> <?= esc($proses['periode']) ?>
                                                    </div>
                                                    <?php endif; ?>
                                                    
                                                    <div class="text-xs text-gray-600">
                                                        <i class="fas fa-bullseye mr-1 text-gray-400"></i>
                                                        <span class="font-medium">Target:</span> <?= number_format($proses['target']) ?>
                                                    </div>
                                                    
                                                    <div class="text-xs text-gray-600">
                                                        <i class="fas fa-check-double mr-1 text-gray-400"></i>
                                                        <span class="font-medium">Realisasi:</span> <?= number_format($proses['realisasi']) ?>
                                                    </div>
                                                </div>
                                                
                                                <!-- Stats Grid -->
                                                <div class="grid grid-cols-3 gap-2 mb-2">
                                                    <div class="bg-white rounded p-2 border border-gray-200">
                                                        <div class="text-xs text-gray-500 mb-0.5">Wilayah</div>
                                                        <div class="text-sm font-bold text-gray-900"><?= $proses['jumlah_wilayah'] ?></div>
                                                    </div>
                                                    <div class="bg-white rounded p-2 border border-gray-200">
                                                        <div class="text-xs text-gray-500 mb-0.5">PML</div>
                                                        <div class="text-sm font-bold text-gray-900"><?= $proses['jumlah_pml'] ?></div>
                                                    </div>
                                                    <div class="bg-white rounded p-2 border border-gray-200">
                                                        <div class="text-xs text-gray-500 mb-0.5">PCL</div>
                                                        <div class="text-sm font-bold text-gray-900"><?= $proses['jumlah_pcl'] ?></div>
                                                    </div>
                                                </div>
                                                
                                                <?php if (!empty($proses['tanggal_mulai']) && !empty($proses['tanggal_selesai_target'])): ?>
                                                <div class="text-xs text-gray-500 bg-white px-2 py-1 rounded border border-gray-200 inline-block">
                                                    <i class="fas fa-calendar-check mr-1"></i>
                                                    <?= date('d M Y', strtotime($proses['tanggal_mulai'])) ?> - <?= date('d M Y', strtotime($proses['tanggal_selesai_target'])) ?>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <span class="inline-flex items-center px-2.5 py-1 bg-<?= $proses['status_class'] ?>-50 text-<?= $proses['status_class'] ?>-700 border border-<?= $proses['status_class'] ?>-200 rounded text-xs font-medium ml-2">
                                            <?= $proses['status'] ?>
                                        </span>
                                    </div>
                                    
                                    <!-- Progress Bar -->
                                    <div class="mt-3">
                                        <div class="flex items-center justify-between text-xs text-gray-600 mb-1">
                                            <span class="font-medium">Progress Laporan PCL</span>
                                            <span class="font-bold text-[#1e88e5]"><?= $proses['progress'] ?>%</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                                            <div class="h-2.5 rounded-full transition-all duration-500 <?= $proses['status_class'] == 'green' ? 'bg-[#43a047]' : ($proses['status_class'] == 'red' ? 'bg-[#e53935]' : 'bg-[#1e88e5]') ?>" 
                                                 style="width: <?= $proses['progress'] ?>%"></div>
                                        </div>
                                    </div>
                                    
                                    <?php if (!empty($proses['keterangan'])): ?>
                                    <div class="mt-2 text-xs text-gray-500 bg-white p-2 rounded border border-gray-100">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        <?= esc($proses['keterangan']) ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Pagination Info -->
        <div class="mt-6 pt-4 border-t border-gray-200">
            <p class="text-sm text-gray-600 text-center">
                Menampilkan <span class="font-medium" id="showingCount"><?= count($kegiatan) ?></span> dari <span class="font-medium"><?= count($kegiatan) ?></span> kegiatan
            </p>
        </div>
    <?php endif; ?>
</div>

<script>
// Toggle proses visibility
function toggleProses(id) {
    const element = document.getElementById(id);
    const icon = document.getElementById('icon-' + id);
    const text = document.getElementById('text-' + id);
    
    if (element.classList.contains('hidden')) {
        element.classList.remove('hidden');
        icon.classList.remove('fa-chevron-down');
        icon.classList.add('fa-chevron-up');
        text.textContent = 'Sembunyikan';
    } else {
        element.classList.add('hidden');
        icon.classList.remove('fa-chevron-up');
        icon.classList.add('fa-chevron-down');
        text.textContent = 'Lihat Detail';
    }
}

// Search functionality
document.getElementById('searchKegiatan')?.addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const items = document.querySelectorAll('.kegiatan-card');
    let visibleCount = 0;
    
    items.forEach(item => {
        const name = item.querySelector('.kegiatan-name').textContent.toLowerCase();
        const content = item.textContent.toLowerCase();
        
        if (name.includes(searchTerm) || content.includes(searchTerm)) {
            item.style.display = 'block';
            visibleCount++;
        } else {
            item.style.display = 'none';
        }
    });
    
    document.getElementById('showingCount').textContent = visibleCount;
});
</script>

<?= $this->endSection() ?>