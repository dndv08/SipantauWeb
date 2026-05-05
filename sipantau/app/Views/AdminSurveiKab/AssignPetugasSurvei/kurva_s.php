<?= $this->extend('layouts/adminkab_layout') ?>

<?= $this->section('content') ?>

<!-- Back Button & Title -->
<div class="mb-6">
    <a href="<?= base_url('adminsurvei-kab/data-petugas/detail/' . $pcl['sobat_id']) ?>"
        class="inline-flex items-center text-gray-600 hover:text-gray-900 mb-4">
        <i class="fas fa-arrow-left mr-2"></i>
        <span>Kembali</span>
    </a>
    <h1 class="text-2xl font-bold text-gray-900">Detail Progress PCL</h1>
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
                <p class="text-sm text-gray-600 mb-2">Aktual</p>
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
                <p class="text-4xl font-bold <?= $persentase >= 100 ? 'text-green-600' : 'text-red-600' ?>">
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
            <h2 class="text-lg font-bold text-gray-900 mb-1">Kurva S - Target vs Aktual PCL</h2>
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

<!-- Tabs Section -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <!-- Tab Headers -->
    <div class="border-b border-gray-200">
        <nav class="flex -mb-px">
            <button onclick="switchTab('pantau')" id="tabPantau"
                class="tab-button active px-6 py-4 text-sm font-medium border-b-2 border-blue-600 text-blue-600">
                <i class="fas fa-chart-line mr-2"></i>Pantau Progress
            </button>
            <button onclick="switchTab('transaksi')" id="tabTransaksi"
                class="tab-button px-6 py-4 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                <i class="fas fa-clipboard-list mr-2"></i>Laporan Transaksi
            </button>
            <button onclick="switchTab('feedback')" id="tabFeedback"
                class="tab-button px-6 py-4 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                <i class="fas fa-comment-dots mr-2"></i>Feedback
            </button>
        </nav>
    </div>

    <!-- Tab Content -->
    <div class="p-6">
        <!-- Pantau Progress Tab -->
        <div id="contentPantau" class="tab-content">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Data Pantau Progress</h3>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border border-gray-200">
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold border-r border-gray-200 text-gray-700">
                                No</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold border-r border-gray-200 text-gray-700">
                                Tanggal</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold border-r border-gray-200 text-gray-700">
                                Waktu</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold border-r border-gray-200 text-gray-700">
                                Realisasi</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold border-r border-gray-200 text-gray-700">
                                Realisasi Kumulatif</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold border-r border-gray-200 text-gray-700">
                                Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200" id="pantauTableBody">
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center">
                                <div
                                    class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4">
                                </div>
                                <p class="text-gray-600">Memuat data...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-between mt-6 gap-4" id="pantauPagination"></div>
        </div>

        <!-- Laporan Transaksi Tab -->
        <div id="contentTransaksi" class="tab-content hidden">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Data Laporan Transaksi</h3>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="px-4 py-3 text-left text-xs font-semibold border border-gray-200 text-gray-700">
                                No</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold border border-gray-200 text-gray-700">
                                Tanggal</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold border border-gray-200 text-gray-700">
                                Waktu</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold border border-gray-200 text-gray-700">
                                Kecamatan</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold border border-gray-200 text-gray-700">
                                Desa</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold border border-gray-200 text-gray-700">
                                Lokasi</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold border border-gray-200 text-gray-700">
                                Foto</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold border border-gray-200 text-gray-700">
                                Resume</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200" id="transaksiTableBody">
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center">
                                <div
                                    class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4">
                                </div>
                                <p class="text-gray-600">Memuat data...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-between mt-6 gap-4" id="transaksiPagination">
            </div>
        </div>

        <!-- Feedback Tab -->
        <div id="contentFeedback" class="tab-content hidden">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Feedback untuk PCL</h3>

            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-500"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            Berikan feedback dan rating kepada PCL terkait kinerja dan laporan mereka.
                        </p>
                    </div>
                </div>
            </div>

            <form id="feedbackForm" class="space-y-4">
                <!-- Rating Section -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Rating Kinerja <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-center gap-2">
                        <div id="ratingStars" class="flex gap-1">
                            <i class="fas fa-star text-3xl cursor-pointer text-gray-300 hover:text-yellow-400 transition-colors"
                                data-rating="1"></i>
                            <i class="fas fa-star text-3xl cursor-pointer text-gray-300 hover:text-yellow-400 transition-colors"
                                data-rating="2"></i>
                            <i class="fas fa-star text-3xl cursor-pointer text-gray-300 hover:text-yellow-400 transition-colors"
                                data-rating="3"></i>
                            <i class="fas fa-star text-3xl cursor-pointer text-gray-300 hover:text-yellow-400 transition-colors"
                                data-rating="4"></i>
                            <i class="fas fa-star text-3xl cursor-pointer text-gray-300 hover:text-yellow-400 transition-colors"
                                data-rating="5"></i>
                        </div>
                        <span id="ratingText" class="text-sm text-gray-600 ml-2">(Belum dipilih)</span>
                    </div>
                    <input type="hidden" id="ratingValue" name="rating" value="0">
                </div>

                <div>
                    <label for="feedbackText" class="block text-sm font-medium text-gray-700 mb-2">
                        Feedback <span class="text-red-500">*</span>
                    </label>
                    <textarea id="feedbackText" name="feedback" rows="6" class="input-field w-full"
                        placeholder="Tulis feedback Anda di sini..." required></textarea>
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" onclick="resetFeedback()"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                        Reset
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-paper-plane mr-2"></i>Kirim Feedback
                    </button>
                </div>
            </form>

            <!-- Current Feedback Display -->
            <div class="mt-8 border-t pt-6">
                <h4 class="text-md font-semibold text-gray-900 mb-4">Feedback Saat Ini</h4>

                <!-- Rating Display -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Rating:</label>
                    <div class="flex items-center gap-2">
                        <div id="currentRatingStars" class="flex gap-1">
                            <?php
                            $currentRating = $pcl['rating'] ?? 3;
                            for ($i = 1; $i <= 5; $i++):
                                ?>
                                <i class="fas fa-star text-xl"
                                    style="color: <?= $i <= $currentRating ? '#fbbf24' : '#d1d5db' ?>;"></i>
                            <?php endfor; ?>
                        </div>
                        <span class="text-sm text-gray-600">(<?= $currentRating ?>/5)</span>
                    </div>
                </div>

                <!-- Feedback Display -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Feedback:</label>
                    <p class="text-sm text-gray-700" id="currentFeedback">
                        <?= !empty($pcl['feedback_admin']) ? esc($pcl['feedback_admin']) : '<em class="text-gray-400">Belum ada feedback</em>' ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ApexCharts CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/apexcharts/3.44.0/apexcharts.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    const idPCL = <?= $idPCL ?>;
    const baseUrl = '<?= base_url('adminsurvei-kab/data-petugas') ?>';
    const kurvaData = <?= json_encode($kurvaData) ?>;

    let chartInstance = null;
    let tabDataCache = {
        pantau: null,
        transaksi: null
    };

    // Initialize Chart
    function renderChart() {
        const isMobile = window.innerWidth < 640;

        const options = {
            series: [
                { name: 'Target (Kurva S)', data: kurvaData.target, type: 'area' },
                { name: 'Realisasi (Kumulatif)', data: kurvaData.realisasi, type: 'area' }
            ],
            chart: {
                height: isMobile ? 300 : 380,
                type: 'area',
                fontFamily: 'Poppins, sans-serif',
                toolbar: { show: !isMobile }
            },
            colors: ['#1e88e5', '#e53935'],
            dataLabels: { enabled: false },
            stroke: { width: isMobile ? [2, 2] : [3, 3], curve: 'smooth', dashArray: [0, 5] },
            fill: {
                type: 'gradient',
                gradient: {
                    shade: 'light',
                    type: 'vertical',
                    shadeIntensity: 0.3,
                    gradientToColors: ['#bbdefb', '#ffcdd2'],
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

    // Switch Tab
    function switchTab(tab) {
        document.querySelectorAll('.tab-button').forEach(btn => {
            btn.classList.remove('active', 'border-blue-600', 'text-blue-600');
            btn.classList.add('border-transparent', 'text-gray-500');
        });

        document.getElementById('tab' + tab.charAt(0).toUpperCase() + tab.slice(1)).classList.add('active', 'border-blue-600', 'text-blue-600');
        document.getElementById('tab' + tab.charAt(0).toUpperCase() + tab.slice(1)).classList.remove('border-transparent', 'text-gray-500');

        document.querySelectorAll('.tab-content').forEach(content => content.classList.add('hidden'));
        document.getElementById('content' + tab.charAt(0).toUpperCase() + tab.slice(1)).classList.remove('hidden');

        if (tab === 'pantau' && !tabDataCache.pantau) loadPantauProgress(1);
        else if (tab === 'transaksi' && !tabDataCache.transaksi) loadLaporanTransaksi(1);
    }

    // Load Pantau Progress
    async function loadPantauProgress(page = 1) {
        const tbody = document.getElementById('pantauTableBody');
        tbody.innerHTML = '<tr><td colspan="6" class="px-4 py-12 text-center"><div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div><p class="text-gray-600">Memuat data...</p></td></tr>';

        try {
            const response = await fetch(`${baseUrl}/get-pantau-progress?id_pcl=${idPCL}&page=${page}`);
            const result = await response.json();

            if (result.success) {
                renderPantauTable(result.data, result.pagination);
                if (page === 1) tabDataCache.pantau = true;
            }
        } catch (e) {
            console.error('Error:', e);
            tbody.innerHTML = '<tr><td colspan="6" class="px-4 py-12 text-center text-red-500">Gagal memuat data</td></tr>';
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
            <td class="px-4 py-3 border border-gray-200 text-sm text-center font-semibold">${item.jumlah_realisasi_absolut || 0}</td>
            <td class="px-4 py-3 border border-gray-200 text-sm text-center font-semibold text-blue-600">${item.jumlah_realisasi_kumulatif || 0}</td>
            <td class="px-4 py-3 border border-gray-200 text-sm">${item.catatan_aktivitas || '-'}</td>
        </tr>
    `).join('');

        renderPagination('pantau', pagination);
    }

    // Load Laporan Transaksi
    async function loadLaporanTransaksi(page = 1) {
        const tbody = document.getElementById('transaksiTableBody');
        tbody.innerHTML = '<tr><td colspan="8" class="px-4 py-12 text-center"><div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div><p class="text-gray-600">Memuat data...</p></td></tr>';

        try {
            const response = await fetch(`${baseUrl}/get-laporan-transaksi?id_pcl=${idPCL}&page=${page}`);
            const result = await response.json();

            if (result.success) {
                renderTransaksiTable(result.data, result.pagination);
                if (page === 1) tabDataCache.transaksi = true;
            }
        } catch (e) {
            console.error('Error:', e);
            tbody.innerHTML = '<tr><td colspan="8" class="px-4 py-12 text-center text-red-500">Gagal memuat data</td></tr>';
        }
    }

    // Render Transaksi Table
    function renderTransaksiTable(data, pagination) {
        const tbody = document.getElementById('transaksiTableBody');
        if (data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="px-4 py-12 text-center text-gray-500">Belum ada data</td></tr>';
            return;
        }

        const startNo = (pagination.currentPage - 1) * pagination.perPage + 1;
        tbody.innerHTML = data.map((item, index) => `
        <tr class="hover:bg-gray-50">
            <td class="px-4 py-3 border text-sm">${startNo + index}</td>
            <td class="px-4 py-3 border text-sm">${formatDate(item.created_at)}</td>
            <td class="px-4 py-3 border text-sm">${formatTime(item.created_at)}</td>
            <td class="px-4 py-3 border text-sm">${item.nama_kecamatan || '-'}</td>
            <td class="px-4 py-3 border text-sm">${item.nama_desa || '-'}</td>
            <td class="px-4 py-3 border text-sm">
                ${item.latitude && item.longitude ? `<a href="https://www.google.com/maps?q=${item.latitude},${item.longitude}" target="_blank" class="text-blue-600 hover:underline"><i class="fas fa-map-marker-alt mr-1"></i>Lihat</a>` : '-'}
            </td>
            <td class="px-4 py-3 border">
                ${item.imagepath ? `<img src="<?= base_url() ?>${item.imagepath}" alt="Foto" loading="lazy" class="w-16 h-16 object-cover rounded border cursor-pointer" onclick="showImage('<?= base_url() ?>${item.imagepath}')">` : '<span class="text-gray-400 text-xs">Tidak ada foto</span>'}
            </td>
            <td class="px-4 py-3 border text-sm"><div class="max-w-xs truncate" title="${item.resume || '-'}">${item.resume || '-'}</div></td>
        </tr>
    `).join('');

        renderPagination('transaksi', pagination);
    }

    // Render Pagination
    function renderPagination(type, pagination) {
        const container = document.getElementById(`${type}Pagination`);
        if (pagination.totalPages <= 1) {
            container.innerHTML = '';
            return;
        }

        const showing = `Menampilkan ${(pagination.currentPage - 1) * pagination.perPage + 1} - ${Math.min(pagination.currentPage * pagination.perPage, pagination.total)} dari ${pagination.total} data`;
        let buttons = '';
        for (let i = 1; i <= pagination.totalPages; i++) {
            buttons += i === pagination.currentPage ?
                `<button class="px-3 py-1 bg-blue-600 text-white rounded text-sm">${i}</button>` :
                `<button onclick="load${type === 'pantau' ? 'PantauProgress' : 'LaporanTransaksi'}(${i})" class="px-3 py-1 border rounded text-sm hover:bg-gray-50">${i}</button>`;
        }

        container.innerHTML = `<p class="text-sm text-gray-600">${showing}</p><div class="flex gap-1">${buttons}</div>`;
    }

    // Feedback Form Handler
    // Feedback Form Handler
    document.getElementById('feedbackForm').addEventListener('submit', async function (e) {
        e.preventDefault();

        const feedback = document.getElementById('feedbackText').value.trim();
        const rating = parseInt(document.getElementById('ratingValue').value);

        if (!feedback) {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: 'Feedback tidak boleh kosong',
                confirmButtonColor: '#2563eb'
            });
            return;
        }

        if (rating === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: 'Silakan pilih rating terlebih dahulu',
                confirmButtonColor: '#2563eb'
            });
            return;
        }

        // Show loading
        Swal.fire({
            title: 'Menyimpan...',
            text: 'Mohon tunggu sebentar',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const formData = new FormData();
        formData.append('id_pcl', idPCL);
        formData.append('feedback', feedback);
        formData.append('rating', rating);

        const response = await fetch(`${baseUrl}/save-feedback-pcl`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            // Update tampilan feedback
            document.getElementById('currentFeedback').innerHTML = escapeHtml(result.feedback);

            // Update rating display
            const currentRatingStars = document.getElementById('currentRatingStars');
            currentRatingStars.innerHTML = '';
            for (let i = 1; i <= 5; i++) {
                const star = document.createElement('i');
                star.className = 'fas fa-star text-xl';
                star.style.color = i <= result.rating ? '#fbbf24' : '#d1d5db'; // PENTING: gunakan inline style
                currentRatingStars.appendChild(star);
            }
            const ratingLabel = currentRatingStars.parentElement.querySelector('span');
            if (ratingLabel) {
                ratingLabel.textContent = `(${result.rating}/5)`;
            }

            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: result.message || 'Feedback dan rating berhasil disimpan',
                confirmButtonColor: '#2563eb',
                timer: 2000,
                showConfirmButton: false
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: result.message || 'Gagal menyimpan feedback dan rating',
                confirmButtonColor: '#dc2626'
            });
        }
    });

    function resetFeedback() {
        const currentFeedbackText = document.getElementById('currentFeedback').textContent.trim();

        if (currentFeedbackText === 'Belum ada feedback') {
            document.getElementById('feedbackText').value = '';
            selectedRating = 0;
            updateRatingDisplay(0);
            document.getElementById('ratingValue').value = 0;
        } else {
            Swal.fire({
                title: 'Reset Feedback?',
                text: 'Apakah Anda yakin ingin mereset form?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2563eb',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Reset',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('feedbackText').value = '';
                    selectedRating = <?= $pcl['rating'] ?? 0 ?>;
                    updateRatingDisplay(selectedRating);
                    document.getElementById('ratingValue').value = selectedRating;
                }
            });
        }
    }

    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
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

    function showImage(url) {
        window.open(url, '_blank');
    }

    // Rating Stars Handler
    let selectedRating = <?= $pcl['rating'] ?? 0 ?>;

    document.querySelectorAll('#ratingStars i').forEach(star => {
        star.addEventListener('click', function () {
            selectedRating = parseInt(this.getAttribute('data-rating'));
            updateRatingDisplay(selectedRating);
            document.getElementById('ratingValue').value = selectedRating;
        });

        star.addEventListener('mouseenter', function () {
            const hoverRating = parseInt(this.getAttribute('data-rating'));
            updateRatingDisplay(hoverRating, true);
        });
    });

    document.getElementById('ratingStars').addEventListener('mouseleave', function () {
        updateRatingDisplay(selectedRating);
    });

    function updateRatingDisplay(rating, isHover = false) {
        const stars = document.querySelectorAll('#ratingStars i');
        const ratingText = document.getElementById('ratingText');

        const labels = ['', 'Sangat Buruk', 'Buruk', 'Cukup', 'Baik', 'Sangat Baik'];

        stars.forEach((star, index) => {
            if (index < rating) {
                star.style.color = '#fbbf24'; // yellow-400
                star.classList.add('active');
            } else {
                star.style.color = '#d1d5db'; // gray-300
                star.classList.remove('active');
            }
        });

        if (rating > 0) {
            ratingText.textContent = `(${labels[rating]})`;
        } else {
            ratingText.textContent = '(Belum dipilih)';
        }
    }

    // Initialize rating display
    if (selectedRating > 0) {
        updateRatingDisplay(selectedRating);
        document.getElementById('ratingValue').value = selectedRating;
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

<style>
    .tab-button.active {
        border-bottom: 2px solid #2563eb;
        color: #2563eb;
    }
</style>

<?= $this->endSection() ?>