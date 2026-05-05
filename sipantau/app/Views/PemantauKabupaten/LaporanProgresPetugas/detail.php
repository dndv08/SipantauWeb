<?= $this->extend('layouts/pemantau_kabupaten_layout') ?>

<?= $this->section('content') ?>

<!-- Back Button & Title -->
<div class="mb-6">
    <a href="<?= base_url('pemantau-kabupaten/laporan-progress-petugas') ?>"
        class="inline-flex items-center text-gray-600 hover:text-gray-900 mb-4">
        <i class="fas fa-arrow-left mr-2"></i>
        <span>Kembali</span>
    </a>
    <h1 class="text-2xl font-bold text-gray-900">Detail Progress Kumulatif PCL</h1>
    <p class="text-xs text-gray-500 mt-1">
        <i class="fas fa-info-circle mr-1"></i>
        Data progress kumulatif dari pantau progress harian
    </p>
</div>

<!-- Info Cards -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div>
            <p class="text-sm text-gray-500 mb-1">Nama PCL</p>
            <p class="text-base font-bold text-gray-900"><?= esc($pcl['nama_pcl']) ?></p>
        </div>
        <div>
            <p class="text-sm text-gray-500 mb-1">PML</p>
            <p class="text-base font-semibold text-gray-900"><?= esc($pcl['nama_pml']) ?></p>
        </div>
        <div>
            <p class="text-sm text-gray-500 mb-1">Nama Survei</p>
            <p class="text-base font-semibold text-gray-900"><?= esc($pcl['nama_kegiatan_detail_proses']) ?></p>
        </div>
        <div>
            <p class="text-sm text-gray-500 mb-1">Wilayah</p>
            <p class="text-base font-semibold text-gray-900"><?= esc($pcl['nama_kabupaten']) ?></p>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <!-- Target Card -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-2">Target</p>
                <p class="text-4xl font-bold text-gray-900"><?= number_format($target) ?></p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                <i class="fas fa-bullseye text-blue-600 text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Aktual Card -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-2">Realisasi Kumulatif</p>
                <p class="text-4xl font-bold text-gray-900"><?= number_format($realisasi) ?></p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                <i class="fas fa-check-circle text-green-600 text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Pencapaian Card -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-2">Pencapaian</p>
                <p class="text-4xl font-bold <?= $persentase >= 100 ? 'text-green-600' : 'text-orange-600' ?>">
                    <?= number_format($persentase, 1) ?>%
                </p>
            </div>
            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                <i class="fas fa-chart-pie text-purple-600 text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Selisih Card -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-2">Selisih</p>
                <p class="text-4xl font-bold <?= $selisih <= 0 ? 'text-green-600' : 'text-red-600' ?>">
                    <?= $selisih > 0 ? '-' : '+' ?><?= number_format(abs($selisih)) ?>
                </p>
            </div>
            <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                <i class="fas fa-balance-scale text-orange-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Chart Section -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-lg font-bold text-gray-900 mb-1">Kurva S - Target vs Realisasi Kumulatif PCL</h2>
            <p class="text-sm text-gray-600">Progress Kumulatif Harian</p>
        </div>
        <div class="text-sm text-gray-600">
            <i class="far fa-calendar-alt mr-2"></i>
            <?= date('d M Y', strtotime($pcl['tanggal_mulai'])) ?> -
            <?= date('d M Y', strtotime($pcl['tanggal_selesai'])) ?>
        </div>
    </div>

    <div class="relative">
        <div id="kurvaChart"></div>
    </div>
</div>

<!-- Pantau Progress Section -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <!-- Header -->
    <div class="border-b border-gray-200 px-6 py-4">
        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
            <i class="fas fa-chart-line mr-2 text-blue-600"></i>
            Data Pantau Progress Kumulatif
        </h3>
        <p class="text-sm text-gray-500 mt-1">Riwayat pelaporan progress harian dengan nilai kumulatif</p>
    </div>

    <!-- Content -->
    <div class="p-6">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-gray-50 border border-gray-200">
                        <th class="px-4 py-3 text-left text-xs font-semibold border-r border-gray-200 text-gray-700">
                            No</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold border-r border-gray-200 text-gray-700">
                            Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold border-r border-gray-200 text-gray-700">
                            Waktu</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold border-r border-gray-200 text-gray-700">
                            Realisasi Harian</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold border-r border-gray-200 text-gray-700">
                            <i class="fas fa-arrow-up mr-1"></i>Realisasi Kumulatif</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold border-r border-gray-200 text-gray-700">
                            Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200" id="pantauTableBody">
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center">
                            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4">
                            </div>
                            <p class="text-gray-600">Memuat data...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="flex flex-col sm:flex-row items-center justify-between mt-6 gap-4" id="pantauPagination"></div>
    </div>
</div>

<!-- ApexCharts CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/apexcharts/3.44.0/apexcharts.min.js"></script>

<script>
    const idPCL = <?= $idPCL ?>;
    const baseUrl = '<?= base_url('pemantau-kabupaten/laporan-progress-petugas') ?>';
    const kurvaData = <?= json_encode($kurvaData) ?>;

    let chartInstance = null;

    // Initialize Chart
    function renderChart() {
        const isMobile = window.innerWidth < 640;

        const options = {
            series: [
                { name: 'Target (Kurva S)', data: kurvaData.target, type: 'area' },
                { name: 'Realisasi Kumulatif', data: kurvaData.realisasi, type: 'area' }
            ],
            chart: {
                height: isMobile ? 300 : 380,
                type: 'area',
                fontFamily: 'Poppins, sans-serif',
                toolbar: { show: !isMobile }
            },
            colors: ['#1e88e5', '#43a047'],
            dataLabels: { enabled: false },
            stroke: { width: isMobile ? [2, 2] : [3, 3], curve: 'smooth', dashArray: [0, 5] },
            fill: {
                type: 'gradient',
                gradient: {
                    shade: 'light',
                    type: 'vertical',
                    shadeIntensity: 0.3,
                    gradientToColors: ['#bbdefb', '#c8e6c9'],
                    opacityFrom: 0.5,
                    opacityTo: 0.1
                }
            },
            markers: { size: 0, hover: { size: isMobile ? 5 : 7 } },
            xaxis: {
                categories: kurvaData.labels,
                title: { text: 'Tanggal', style: { fontSize: isMobile ? '11px' : '12px', fontWeight: 600 } },
                labels: { rotate: isMobile ? -45 : 0, style: { fontSize: isMobile ? '10px' : '11px' } }
            },
            yaxis: {
                title: { text: isMobile ? '' : 'Jumlah', style: { fontSize: '12px', fontWeight: 600 } },
                labels: {
                    style: { fontSize: isMobile ? '9px' : '11px' },
                    formatter: value => Math.round(value).toLocaleString('id-ID')
                }
            },
            tooltip: {
                shared: true,
                intersect: false,
                y: { formatter: value => value ? value.toLocaleString('id-ID') : '0' }
            },
            legend: {
                position: isMobile ? 'bottom' : 'top',
                horizontalAlign: isMobile ? 'center' : 'left',
                fontSize: isMobile ? '11px' : '13px'
            },
            grid: { borderColor: '#f3f4f6', strokeDashArray: 3 }
        };

        if (chartInstance) chartInstance.destroy();
        chartInstance = new ApexCharts(document.querySelector("#kurvaChart"), options);
        chartInstance.render();
    }

    // Load Pantau Progress
    async function loadPantauProgress(page = 1) {
        const tbody = document.getElementById('pantauTableBody');
        tbody.innerHTML = '<tr><td colspan="6" class="px-4 py-12 text-center"><div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div><p class="text-gray-600">Memuat data...</p></td></tr>';

        try {
            const response = await fetch(`${baseUrl}/pantau-progress?id_pcl=${idPCL}&page=${page}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();

            if (result.success) {
                renderPantauTable(result.data, result.pagination);
            } else {
                throw new Error(result.message || 'Gagal memuat data');
            }
        } catch (e) {
            console.error('Error:', e);
            tbody.innerHTML = '<tr><td colspan="6" class="px-4 py-12 text-center text-red-500">Gagal memuat data: ' + e.message + '</td></tr>';
        }
    }

    // Render Pantau Table
    function renderPantauTable(data, pagination) {
        const tbody = document.getElementById('pantauTableBody');
        if (data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="px-4 py-12 text-center text-gray-500">Belum ada data</td></tr>';
            return;
        }

        const startNo = (pagination.currentPage - 1) * pagination.perPage + 1;
        tbody.innerHTML = data.map((item, index) => `
        <tr class="hover:bg-gray-50">
            <td class="px-4 py-3 border border-gray-200 text-sm">${startNo + index}</td>
            <td class="px-4 py-3 border border-gray-200 text-sm">${formatDate(item.created_at)}</td>
            <td class="px-4 py-3 border border-gray-200 text-sm">${formatTime(item.created_at)}</td>
            <td class="px-4 py-3 border border-gray-200 text-sm text-center">
                <span class="font-semibold text-gray-700">${formatNumber(item.jumlah_realisasi_absolut || 0)}</span>
            </td>
            <td class="px-4 py-3 border border-gray-200 text-sm text-center">
                <span class="font-bold text-blue-600 text-base">${formatNumber(item.jumlah_realisasi_kumulatif || 0)}</span>
            </td>
            <td class="px-4 py-3 border border-gray-200 text-sm">${escapeHtml(item.catatan_aktivitas || '-')}</td>
        </tr>
    `).join('');

        renderPagination(pagination);
    }

    // Render Pagination
    function renderPagination(pagination) {
        const container = document.getElementById('pantauPagination');
        if (pagination.totalPages <= 1) {
            container.innerHTML = '';
            return;
        }

        const showing = `Menampilkan ${(pagination.currentPage - 1) * pagination.perPage + 1} - ${Math.min(pagination.currentPage * pagination.perPage, pagination.total)} dari ${pagination.total} data`;
        let buttons = '';
        
        // Previous button
        if (pagination.currentPage > 1) {
            buttons += `<button onclick="loadPantauProgress(${pagination.currentPage - 1})" class="px-3 py-1 border rounded text-sm hover:bg-gray-50"><i class="fas fa-chevron-left"></i></button>`;
        }
        
        for (let i = 1; i <= pagination.totalPages; i++) {
            buttons += i === pagination.currentPage ?
                `<button class="px-3 py-1 bg-blue-600 text-white rounded text-sm">${i}</button>` :
                `<button onclick="loadPantauProgress(${i})" class="px-3 py-1 border rounded text-sm hover:bg-gray-50">${i}</button>`;
        }
        
        // Next button
        if (pagination.currentPage < pagination.totalPages) {
            buttons += `<button onclick="loadPantauProgress(${pagination.currentPage + 1})" class="px-3 py-1 border rounded text-sm hover:bg-gray-50"><i class="fas fa-chevron-right"></i></button>`;
        }

        container.innerHTML = `<p class="text-sm text-gray-600">${showing}</p><div class="flex gap-1">${buttons}</div>`;
    }

    function escapeHtml(text) {
        if (!text) return '-';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text).replace(/[&<>"']/g, m => map[m]);
    }

    // Utility Functions
    function formatDate(datetime) {
        if (!datetime) return '-';
        return new Date(datetime).toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' });
    }

    function formatTime(datetime) {
        if (!datetime) return '-';
        return new Date(datetime).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    }

    function formatNumber(num) {
        return parseInt(num).toLocaleString('id-ID');
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', function () {
        renderChart();
        loadPantauProgress(1);
    });

    let resizeTimer;
    window.addEventListener('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => chartInstance && renderChart(), 250);
    });
</script>

<?= $this->endSection() ?>