<?= $this->extend('layouts/sadmin_layout') ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="mb-6">
    <div class="flex items-center text-sm text-gray-600 mb-4">
        <a href="<?= base_url('superadmin/master-kegiatan-detail/show/' . $detailProses['id_kegiatan_detail']) ?>"
            class="hover:text-blue-600 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Kembali ke Detail Kegiatan
        </a>
    </div>
    <h1 class="text-2xl font-bold text-gray-900">Daftar Kegiatan Wilayah</h1>
    <p class="text-gray-600 mt-1">Wilayah yang terkait dengan proses kegiatan ini</p>
</div>

<!-- Info Card - Kegiatan Detail Proses -->
<div class="card mb-6 bg-gradient-to-r from-blue-50 to-indigo-50 border-blue-200">
    <div class="flex items-start justify-between">
        <div class="flex-1">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">
                <?= esc($detailProses['nama_kegiatan_detail_proses']) ?>
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div>
                    <span class="text-gray-600">Kegiatan Detail:</span>
                    <p class="font-medium text-gray-900"><?= esc($detailProses['nama_kegiatan_detail']) ?></p>
                </div>
                <div>
                    <span class="text-gray-600">Satuan:</span>
                    <p class="font-medium text-gray-900"><?= esc($detailProses['satuan']) ?></p>
                </div>
                <div>
                    <span class="text-gray-600">Target:</span>
                    <p class="font-medium text-gray-900"><?= number_format($detailProses['target']) ?></p>
                </div>
            </div>
            <?php if (!empty($detailProses['keterangan'])): ?>
                <div class="mt-3 pt-3 border-t border-blue-200">
                    <span class="text-gray-600 text-sm">Keterangan:</span>
                    <p class="text-sm text-gray-700 mt-1"><?= esc($detailProses['keterangan']) ?></p>
                </div>
            <?php endif; ?>
        </div>
        <div class="ml-4">
            <span class="badge badge-info text-base px-4 py-2">
                <?= esc($detailProses['periode']) ?>
            </span>
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Wilayah -->
    <div class="card hover:shadow-md transition-shadow duration-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-1">Total Wilayah</p>
                <h3 class="text-3xl font-bold text-gray-900"><?= count($kegiatanWilayah) ?></h3>
            </div>
            <div class="w-14 h-14 bg-blue-50 rounded-lg flex items-center justify-center">
                <i class="fas fa-map-marked-alt text-2xl text-[#1e88e5]"></i>
            </div>
        </div>
    </div>

    <!-- Total Target -->
    <div class="card hover:shadow-md transition-shadow duration-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-1">Total Target</p>
                <h3 class="text-3xl font-bold text-gray-900"><?= number_format($totalTarget) ?></h3>
            </div>
            <div class="w-14 h-14 bg-green-50 rounded-lg flex items-center justify-center">
                <i class="fas fa-clipboard-list text-2xl text-[#43a047]"></i>
            </div>
        </div>
    </div>

    <!-- Total Realisasi -->
    <div class="card hover:shadow-md transition-shadow duration-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-1">Total Realisasi</p>
                <h3 class="text-3xl font-bold text-gray-900"><?= number_format($totalRealisasi) ?></h3>
            </div>
            <div class="w-14 h-14 bg-orange-50 rounded-lg flex items-center justify-center">
                <i class="fas fa-chart-line text-2xl text-[#fb8c00]"></i>
            </div>
        </div>
    </div>

    <!-- Target Tercapai -->
    <div class="card hover:shadow-md transition-shadow duration-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 mb-1">Target Tercapai</p>
                <h3 class="text-3xl font-bold text-gray-900"><?= number_format($avgProgress, 0) ?>%</h3>
            </div>
            <div class="w-14 h-14 bg-purple-50 rounded-lg flex items-center justify-center">
                <i class="fas fa-bullseye text-2xl text-[#8e24aa]"></i>
            </div>
        </div>
    </div>
</div>

<!-- Kurva S Chart -->
<div class="card mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-3">
        <div>
            <h3 class="text-lg font-semibold text-gray-900">Kurva S - Target vs Realisasi</h3>
            <p class="text-sm text-gray-600">Progres Kumulatif Kegiatan</p>
        </div>
    </div>

    <!-- Chart Container -->
    <div class="relative -mx-2 sm:mx-0">
        <div id="kurvaProvinsiChart" class="w-full"></div>
        <div id="chartPlaceholder" class="flex justify-center items-center py-16" style="display: none;">
            <p class="text-gray-400">Memuat data kurva S...</p>
        </div>
    </div>
</div>

<!-- Main Card -->
<div class="card mb-6">
    <!-- Search dan PerPage Section -->
    <div style="display: grid; grid-template-columns: 1fr 200px; gap: 1rem; margin-bottom: 1.5rem;">
        <!-- Search Box -->
        <div>
            <label for="searchInput" class="block text-sm font-medium text-gray-700 mb-1">
                Pencarian Wilayah
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                <input type="text" id="searchInput" class="input-field w-full pl-10" placeholder="Cari kabupaten..."
                    onkeyup="searchTable()">
            </div>
        </div>

        <!-- Per Page Selector -->
        <div>
            <label for="perPageSelect" class="block text-sm font-medium text-gray-700 mb-1">
                Data per Halaman
            </label>
            <div class="relative">
                <select name="perPage" id="perPageSelect"
                    class="input-field w-full pr-10 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 appearance-none cursor-pointer"
                    onchange="updatePerPage()">
                    <option value="5" <?= ($perPage == 5) ? 'selected' : ''; ?>>5</option>
                    <option value="10" <?= ($perPage == 10) ? 'selected' : ''; ?>>10</option>
                    <option value="25" <?= ($perPage == 25) ? 'selected' : ''; ?>>25</option>
                    <option value="50" <?= ($perPage == 50) ? 'selected' : ''; ?>>50</option>
                    <option value="100" <?= ($perPage == 100) ? 'selected' : ''; ?>>100</option>
                </select>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <i id="perPageChevron"
                        class="fas fa-chevron-down text-gray-400 text-sm transition-transform duration-300"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full" id="kegiatanWilayahTable">
            <thead>
                <tr class="border-b border-gray-200 bg-gray-50">
                    <th
                        class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-12 border-r border-gray-200">
                        No</th>
                    <th
                        class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-r border-gray-200">
                        Kabupaten/Kota</th>
                    <th
                        class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-r border-gray-200">
                        Keterangan</th>
                    <th
                        class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-32 border-r border-gray-200">
                        Target</th>
                    <th
                        class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-32 border-r border-gray-200">
                        Realisasi</th>
                    <th
                        class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-24 border-r border-gray-200">
                        Progress</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (!empty($kegiatanWilayah)): ?>
                    <?php $no = 1; ?>
                    <?php foreach ($kegiatanWilayah as $kg): ?>
                        <tr class="hover:bg-gray-50 transition-colors duration-150 border-r border-gray-200">
                            <td class="px-4 py-4 text-sm text-gray-900 text-center border-r border-gray-200">
                                <?= ($pager->getCurrentPage('kegiatanWilayah') - 1) * $pager->getPerPage('kegiatanWilayah') + $no++ ?>
                            </td>
                            <td class="px-4 py-4 border-r border-gray-200">
                                <div>
                                    <p class="text-sm font-medium text-gray-900"><?= esc($kg['nama_kabupaten']) ?></p>
                                    <p class="text-xs text-gray-500">Kode: <?= esc($kg['id_kabupaten']) ?></p>
                                </div>
                            </td>
                            <td class="px-4 py-4 border-r border-gray-200">
                                <p class="text-sm text-gray-600 line-clamp-2">
                                    <?= !empty($kg['keterangan']) ? esc($kg['keterangan']) : '-' ?>
                                </p>
                            </td>
                            <td class="px-4 py-4 text-center border-r border-gray-200">
                                <span class="text-sm font-semibold text-gray-900">
                                    <?= number_format($kg['target_wilayah']) ?>
                                </span>
                            </td>
                            <td class="px-4 py-4 text-center border-r border-gray-200">
                                <span class="text-sm font-semibold text-gray-900">
                                    <?= number_format($kg['realisasi']) ?>
                                </span>
                            </td>
                            <td class="px-4 py-4 border-r border-gray-200">
                                <div class="flex flex-col items-center">
                                    <!-- Circular Progress -->
                                    <div class="relative inline-flex items-center justify-center">
                                        <svg class="transform -rotate-90" width="50" height="50">
                                            <circle cx="25" cy="25" r="20" stroke="#e5e7eb" stroke-width="4" fill="none" />
                                            <circle cx="25" cy="25" r="20" stroke="<?= $kg['progress_color'] ?>"
                                                stroke-width="4" fill="none" stroke-dasharray="<?= (2 * 3.14159 * 20) ?>"
                                                stroke-dashoffset="<?= (2 * 3.14159 * 20) * (1 - ($kg['progress'] / 100)) ?>"
                                                stroke-linecap="round" />
                                        </svg>
                                        <span class="absolute text-xs font-semibold"
                                            style="color: <?= $kg['progress_color'] ?>">
                                            <?= number_format($kg['progress'], 1) ?>%
                                        </span>
                                    </div>
                                    <!-- Status Label -->
                                    <div class="mt-1 text-xs text-center">
                                        <?php if ($kg['progress'] >= 80): ?>
                                            <span class="text-green-600 font-medium">Sangat Baik</span>
                                        <?php elseif ($kg['progress'] >= 50): ?>
                                            <span class="text-blue-600 font-medium">Baik</span>
                                        <?php elseif ($kg['progress'] >= 25): ?>
                                            <span class="text-orange-600 font-medium">Sedang</span>
                                        <?php else: ?>
                                            <span class="text-red-600 font-medium">Rendah</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center">
                            <i class="fas fa-inbox text-gray-300 text-5xl mb-4"></i>
                            <p class="text-gray-500">Belum ada kegiatan wilayah untuk proses ini</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Footer dengan Pagination -->
    <div class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-4 border-t border-gray-200 pt-4">
        <p class="text-sm text-gray-600">
            Menampilkan data
            <span
                class="font-medium"><?= (($pager->getCurrentPage('kegiatanWilayah') - 1) * $pager->getPerPage('kegiatanWilayah')) + 1 ?></span>-<span
                class="font-medium"><?= min($pager->getCurrentPage('kegiatanWilayah') * $pager->getPerPage('kegiatanWilayah'), $pager->getTotal('kegiatanWilayah')) ?></span>
            dari <span class="font-medium"><?= $pager->getTotal('kegiatanWilayah') ?></span> wilayah
        </p>

        <!-- Custom Pagination -->
        <?php if ($pager->getPageCount('kegiatanWilayah') > 1): ?>
            <?= $pager->links('kegiatanWilayah', 'tailwind_pager') ?>
        <?php endif; ?>
    </div>
</div>

<!-- ApexCharts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/apexcharts/3.44.0/apexcharts.min.js"></script>

<script>
    let chartInstance = null;

    // Search function
    function searchTable() {
        const input = document.getElementById('searchInput');
        const filter = input.value.toLowerCase();
        const table = document.getElementById('kegiatanWilayahTable');
        const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];
            const cells = row.getElementsByTagName('td');
            let found = false;

            // Skip empty state row
            if (cells.length === 1 && cells[0].getAttribute('colspan')) {
                continue;
            }

            // Search in kabupaten column (index 1)
            if (cells[1]) {
                const textValue = cells[1].textContent || cells[1].innerText;
                if (textValue.toLowerCase().indexOf(filter) > -1) {
                    found = true;
                }
            }

            row.style.display = found ? '' : 'none';
        }
    }

    // Handle animasi chevron untuk perPage selector
    const perPageSelect = document.getElementById('perPageSelect');
    const perPageChevron = document.getElementById('perPageChevron');

    if (perPageSelect && perPageChevron) {
        perPageSelect.addEventListener('focus', function () {
            perPageChevron.classList.add('rotate-180');
        });

        perPageSelect.addEventListener('blur', function () {
            perPageChevron.classList.remove('rotate-180');
        });
    }

    // Function untuk update perPage dengan save scroll position
    function updatePerPage() {
        const perPage = document.getElementById('perPageSelect').value;

        // Simpan posisi scroll
        sessionStorage.setItem('scrollPosition', window.scrollY);

        const params = new URLSearchParams();
        if (perPage) params.append('perPage', perPage);
        window.location.href = '<?= base_url('superadmin/master-kegiatan-detail/kegiatan-wilayah/' . $detailProses['id_kegiatan_detail_proses']) ?>?' + params.toString();
    }

    // Restore posisi scroll setelah halaman load
    window.addEventListener('load', function () {
        const scrollPosition = sessionStorage.getItem('scrollPosition');
        if (scrollPosition) {
            // Tambahkan delay untuk memastikan konten sudah di-render (termasuk chart)
            setTimeout(() => {
                window.scrollTo({
                    top: parseInt(scrollPosition),
                    behavior: 'instant'
                });
                sessionStorage.removeItem('scrollPosition');
            }, 300);
        }
    });

    // TAMBAHAN: Handle klik pada pagination links
    document.addEventListener('DOMContentLoaded', function () {
        // Load kurva chart terlebih dahulu
        loadKurvaProvinsi();

        // Tambahkan event listener untuk semua pagination links
        const paginationLinks = document.querySelectorAll('.pagination a');
        paginationLinks.forEach(link => {
            link.addEventListener('click', function (e) {
                // Simpan posisi scroll saat ini sebelum navigasi
                sessionStorage.setItem('scrollPosition', window.scrollY);
            });
        });
    });

    async function loadKurvaProvinsi() {
        const chartContainer = document.getElementById('kurvaProvinsiChart');

        try {
            const idProses = <?= $detailProses['id_kegiatan_detail_proses'] ?>;

            // Show loading state
            chartContainer.innerHTML = `
            <div class="flex justify-center items-center py-16">
                <div class="text-center">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
                    <p class="text-gray-600">Memuat data kurva S...</p>
                </div>
            </div>
        `;

            const response = await fetch(`<?= base_url('superadmin/master-kegiatan-detail/kurva-provinsi') ?>?id_kegiatan_detail_proses=${idProses}&nocache=${Date.now()}`, {
                cache: "no-store"
            });

            const data = await response.json();

            if (data && data.labels && data.labels.length > 0) {
                // Clear loading state
                chartContainer.innerHTML = '';
                await new Promise(resolve => setTimeout(resolve, 200));
                renderKurvaProvinsi(data);
            } else {
                chartContainer.innerHTML = `
                <div class="text-center py-16">
                    <i class="fas fa-chart-line text-5xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500">Belum ada data kurva S untuk kegiatan ini</p>
                </div>
            `;
            }
        } catch (error) {
            console.error('Error loading kurva provinsi:', error);
            chartContainer.innerHTML = `
            <div class="text-center py-16">
                <i class="fas fa-exclamation-triangle text-5xl text-red-500 mb-4"></i>
                <p class="text-gray-600">Gagal memuat data. Silakan refresh halaman.</p>
            </div>
        `;
        }
    }

    function renderKurvaProvinsi(data) {
        // Destroy existing chart instance
        if (chartInstance) {
            chartInstance.destroy();
            chartInstance = null;
        }

        const isMobile = window.innerWidth < 640;

        const options = {
            series: [
                {
                    name: 'Target (Kurva S)',
                    data: data.targetAbsolut,
                    type: 'area'
                },
                {
                    name: 'Realisasi (Kumulatif)',
                    data: data.realisasiAbsolut,
                    type: 'area'
                }
            ],
            chart: {
                height: isMobile ? 300 : 380,
                type: 'area',
                fontFamily: 'Poppins, sans-serif',
                toolbar: {
                    show: true
                },
                zoom: {
                    enabled: true,
                    type: 'x',
                    autoScaleYaxis: true
                },
                animations: {
                    enabled: true,
                    speed: 800
                }
            },
            colors: ['#1e88e5', '#e53935'],
            dataLabels: {
                enabled: false
            },
            stroke: {
                width: isMobile ? [2, 2] : [3, 3],
                curve: 'smooth',
                dashArray: [0, 5]
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shade: 'light',
                    type: 'vertical',
                    shadeIntensity: 0.3,
                    gradientToColors: ['#bbdefb', '#ffcdd2'],
                    inverseColors: false,
                    opacityFrom: 0.5,
                    opacityTo: 0.1,
                    stops: [0, 100]
                }
            },
            markers: {
                size: 0,
                hover: {
                    size: isMobile ? 5 : 7
                }
            },
            xaxis: {
                categories: data.labels,
                title: {
                    text: 'Periode',
                    style: {
                        fontSize: isMobile ? '11px' : '12px',
                        fontWeight: 600
                    }
                },
                labels: {
                    rotate: 0,
                    style: {
                        fontSize: isMobile ? '10px' : '11px'
                    }
                }
            },
            yaxis: {
                title: {
                    text: isMobile ? '' : 'Jumlah',
                    style: {
                        fontSize: '12px',
                        fontWeight: 600
                    }
                },
                labels: {
                    style: {
                        fontSize: isMobile ? '9px' : '11px'
                    },
                    formatter: function (value) {
                        return Math.round(value).toLocaleString('id-ID');
                    }
                }
            },
            tooltip: {
                shared: true,
                intersect: false,
                y: {
                    formatter: function (value) {
                        return value.toLocaleString('id-ID');
                    }
                }
            },
            legend: {
                position: isMobile ? 'bottom' : 'top',
                horizontalAlign: isMobile ? 'center' : 'left',
                fontSize: isMobile ? '11px' : '13px'
            },
            grid: {
                borderColor: '#f3f4f6',
                strokeDashArray: 3
            }
        };

        chartInstance = new ApexCharts(document.querySelector("#kurvaProvinsiChart"), options);
        chartInstance.render();
    }

    let resizeTimer;
    window.addEventListener('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function () {
            if (chartInstance) {
                loadKurvaProvinsi();
            }
        }, 250);
    });
</script>

<?= $this->endSection() ?>