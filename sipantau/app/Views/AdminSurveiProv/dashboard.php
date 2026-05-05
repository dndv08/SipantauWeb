<?= $this->extend('layouts/adminprov_layout') ?>
<?= $this->section('content') ?>

<div class="mb-6">
  <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
  <p class="text-gray-600 mt-1">Selamat datang di SiPantau - Sistem Pelaporan Kegiatan Lapangan</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
  <?php
  $statsConfig = [
    ['Total Kegiatan', $stats['total_kegiatan'], 'fas fa-clipboard-list', 'bg-green-50', '#43a047'],
    ['Kegiatan Aktif', $stats['kegiatan_aktif'], 'fas fa-chart-line', 'bg-orange-50', '#fb8c00'],
    ['Target Tercapai', $stats['target_tercapai'] . '%', 'fas fa-bullseye', 'bg-purple-50', '#8e24aa'],
  ];
  foreach ($statsConfig as [$label, $value, $icon, $bg, $color]): ?>
    <div class="card hover:shadow-md transition-shadow duration-200">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-gray-600 mb-1"><?= esc($label) ?></p>
          <h3 class="text-3xl font-bold text-gray-900"><?= esc($value) ?></h3>
        </div>
        <div class="w-14 h-14 <?= $bg ?> rounded-lg flex items-center justify-center">
          <i class="<?= $icon ?> text-2xl" style="color: <?= $color ?>;"></i>
        </div>
      </div>
    </div>
  <?php endforeach ?>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
  <div class="lg:col-span-2 card">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-3">
      <div>
        <h3 class="text-lg font-semibold text-gray-900">Kurva S - Target vs Realisasi</h3>
        <p class="text-sm text-gray-600">Progres Kumulatif Kegiatan</p>
      </div>

      <div class="flex flex-col sm:flex-row gap-2">
        <select id="filterKegiatanProses" class="input-field text-sm w-full sm:w-80">
          <option value="">-- Pilih Kegiatan --</option>
          <?php foreach ($kegiatanList as $item): ?>
            <option value="<?= esc($item['id_kegiatan_detail_proses']) ?>">
      <?= esc($item['nama_kegiatan_detail']) ?> (<?= esc($item['nama_kegiatan_detail_proses']) ?>)
            </option>
          <?php endforeach; ?>
        </select>

        <select id="filterKabupaten" class="input-field text-sm w-full sm:w-48" disabled>
          <option value="">-- Pilih Kabupaten --</option>
        </select>
      </div>
    </div>

    <div id="chartLoadingState" class="flex justify-center items-center py-16">
      <div class="text-center">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
        <p class="text-gray-600">Memuat data kurva S...</p>
      </div>
    </div>

    <div id="kurvaSProvChart" class="w-full" style="display: none; min-height: 320px;"></div>
    <div id="chartErrorState" class="text-center py-16" style="display: none;">
      <i class="fas fa-exclamation-triangle text-5xl text-red-500 mb-4"></i>
      <p class="text-gray-600">Gagal memuat data. Silakan refresh halaman.</p>
    </div>
    <div id="chartPlaceholder" class="flex justify-center items-center py-16" style="display: none;">
      <p class="text-gray-400">Pilih kegiatan untuk menampilkan kurva S</p>
    </div>
  </div>

  <div class="card">
    <h3 class="text-lg font-semibold text-gray-900 mb-6">Progres Kegiatan</h3>
    <div class="space-y-4">
      <?php if (!empty($progressKegiatan)): ?>
        <?php foreach ($progressKegiatan as $index => $prog): ?>
          <div class="<?= $index < count($progressKegiatan) - 1 ? 'pb-4 border-b border-gray-100' : 'pb-4' ?>">
            <div class="flex items-center justify-between mb-2">
              <span class="text-sm font-medium text-gray-900"><?= esc($prog['nama']) ?></span>
              <span class="text-sm font-semibold" style="color: <?= $prog['color'] ?>;"><?= $prog['progress'] ?>%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
              <div class="h-2 rounded-full transition-all duration-500"
                style="width: 0%; background-color: <?= $prog['color'] ?>;" data-width="<?= $prog['progress'] ?>"></div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="text-center py-8">
          <i class="fas fa-inbox text-gray-300 text-4xl mb-2"></i>
          <p class="text-gray-500 text-sm">Belum ada data kegiatan</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="card">
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4">
    <div>
      <h3 class="text-lg font-semibold text-gray-900">Data Petugas</h3>
      <p class="text-sm text-gray-600 mt-1">Monitoring progres petugas lapangan</p>
    </div>
  </div>

  <div class="border-b border-gray-200 mb-6">
    <nav class="-mb-px flex flex-wrap gap-4 sm:gap-8" aria-label="Tabs">
      <button onclick="switchDataTab('petugas')" id="datatab-petugas"
        class="data-tab-button active whitespace-nowrap py-3 px-3 border-b-2 font-medium text-sm">
        <i class="fas fa-users mr-2"></i>Data Petugas
      </button>
      <button onclick="switchDataTab('kepatuhan')" id="datatab-kepatuhan"
        class="data-tab-button whitespace-nowrap py-3 px-3 border-b-2 font-medium text-sm text-gray-600">
        <i class="fas fa-chart-bar mr-2"></i>Tingkat Kepatuhan
      </button>
    </nav>
  </div>

  <div id="datacontent-petugas" class="data-tab-content">

    <div class="hidden lg:flex flex-col sm:flex-row gap-4 mb-4">
      <div class="flex-1">
        <label for="searchPetugasInput" class="block text-sm font-medium text-gray-700 mb-1">
          Pencarian
        </label>
        <div class="relative">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <i class="fas fa-search text-gray-400"></i>
          </div>
          <input type="text" id="searchPetugasInput" class="input-field w-full pl-10"
            placeholder="Cari nama atau sobat ID..." onkeyup="handlePetugasSearch(event)">
        </div>
      </div>

      <div class="w-full sm:w-48">
        <label for="perPagePetugasSelect" class="block text-sm font-medium text-gray-700 mb-1">
          Data per Halaman
        </label>
        <div class="relative">
          <select id="perPagePetugasSelect" class="input-field w-full pr-10 appearance-none cursor-pointer"
            onchange="handlePetugasPerPageChange()">
            <option value="5">5</option>
            <option value="10" selected>10</option>
            <option value="25">25</option>
            <option value="50">50</option>
            <option value="100">100</option>
          </select>
          <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
            <i class="fas fa-chevron-down text-gray-400 text-sm"></i>
          </div>
        </div>
      </div>
    </div>

    <div class="lg:hidden space-y-3 mb-4">
      <div class="w-full">
        <label class="block text-sm font-medium text-gray-700 mb-1">Pencarian</label>
        <div class="relative">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <i class="fas fa-search text-gray-400"></i>
          </div>
          <input type="text" id="searchPetugasInputMobile" class="input-field w-full pl-10 text-sm"
            placeholder="Cari nama atau sobat ID..." onkeyup="handlePetugasSearch(event)">
        </div>
      </div>

      <div class="w-full">
        <label class="block text-sm font-medium text-gray-700 mb-1">Data per Halaman</label>
        <select id="perPagePetugasSelectMobile" class="input-field w-full text-sm"
          onchange="handlePetugasPerPageChange()">
          <option value="5">5</option>
          <option value="10" selected>10</option>
          <option value="25">25</option>
          <option value="50">50</option>
          <option value="100">100</option>
        </select>
      </div>
    </div>

    <div class="overflow-x-auto hidden lg:block">
      <table class="w-full" id="petugasTable">
        <thead>
          <tr class="border-b border-gray-200">
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
              <i class="fas fa-user mr-2"></i>Petugas
            </th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
              <i class="fas fa-calendar-check mr-2"></i>Status Kegiatan
            </th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
              <i class="fas fa-clipboard-check mr-2"></i>Status Harian
            </th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
              <button onclick="toggleProgressSort()"
                class="flex items-center gap-2 hover:text-blue-600 transition-colors">
                <i class="fas fa-tasks"></i>
                <span>Progress</span>
                <i id="sortIcon" class="fas fa-sort text-gray-400"></i>
              </button>
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100" id="petugasTableBody">
          <tr>
            <td colspan="4" class="px-4 py-12 text-center">
              <i class="fas fa-info-circle text-gray-300 text-4xl mb-2"></i>
              <p class="text-gray-500">Pilih kegiatan untuk menampilkan data petugas</p>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="lg:hidden space-y-3" id="petugasCardContainer">
      <div class="text-center py-12">
        <i class="fas fa-info-circle text-gray-300 text-4xl mb-2"></i>
        <p class="text-gray-500 text-sm">Pilih kegiatan untuk menampilkan data petugas</p>
      </div>
    </div>

    <div class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-4">
      <p class="text-sm text-gray-600" id="petugasDataInfo">
        Menampilkan 0 dari 0 total data
      </p>
      <div id="petugasPagination" class="flex gap-1"></div>
    </div>
  </div>

  <div id="datacontent-kepatuhan" class="data-tab-content hidden">
    <div id="kepatuhanStatsCards" class="grid grid-cols-2 lg:grid-cols-5 gap-3 lg:gap-4 mb-6">
      </div>

    <div class="mb-6">
      <h4 class="text-sm lg:text-base font-semibold text-gray-900 mb-4">
        <i class="fas fa-chart-line mr-2"></i>Grafik Kepatuhan
      </h4>
      <div id="kepatuhanChartContainer" class="relative">
        <div id="kepatuhanChart"></div>
        <div id="kepatuhanChartPlaceholder" class="flex justify-center items-center py-16">
          <p class="text-gray-400 text-sm lg:text-base">Pilih kegiatan untuk menampilkan grafik kepatuhan</p>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-6">
      <div>
        <div class="flex items-center justify-between mb-3 lg:mb-4">
          <h4 class="text-sm lg:text-base font-semibold text-gray-900">
            <i class="fas fa-trophy text-yellow-500 mr-2"></i>Leaderboard Kepatuhan
          </h4>
        </div>
        <div id="leaderboardContainer"
          class="space-y-2 lg:space-y-3 max-h-[500px] lg:max-h-[600px] overflow-y-auto custom-scrollbar pr-2">
          <p class="text-center text-gray-400 py-8 text-sm">Pilih kegiatan untuk melihat data</p>
        </div>
      </div>

      <div>
        <div class="flex items-center justify-between mb-3 lg:mb-4">
          <h4 class="text-sm lg:text-base font-semibold text-gray-900">
            <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>Petugas Tidak Patuh
          </h4>
        </div>
        <div id="tidakPatuhContainer"
          class="space-y-2 lg:space-y-3 max-h-[500px] lg:max-h-[600px] overflow-y-auto custom-scrollbar pr-2">
          <p class="text-center text-gray-400 py-8 text-sm">Pilih kegiatan untuk melihat data</p>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/apexcharts/3.44.0/apexcharts.min.js"></script>

<script>
  let chartInstance = null;
  const defaultKegiatan = "<?= esc($latestKegiatanId) ?>";
  
  // ENDPOINT API (Disesuaikan untuk Admin Survei)
  const baseKurvaS = "<?= base_url('adminsurvei/get-kurva-s-with-realisasi') ?>";
  const baseWilayah = "<?= base_url('adminsurvei/kegiatan-wilayah') ?>";
  const basePetugas = "<?= base_url('adminsurvei/get-petugas') ?>";
  const baseKepatuhan = "<?= base_url('adminsurvei/get-kepatuhan-data') ?>";

  // ==================== PAGINATION & SEARCH VARIABLES ====================
  let currentPetugasPage = 1;
  let petugasPerPage = 10;
  let petugasSearchQuery = '';
  let totalPetugasData = 0;
  let totalPetugasPages = 0;
  let kepatuhanChartInstance = null;
  let currentKegiatanId = null;
  let currentWilayahId = null;
  let abortController = null;
  let currentSortOrder = 'none';

  // Load Kabupaten Dropdown
  async function loadKabupatenDropdown(idProses) {
    const kabSelect = document.getElementById("filterKabupaten");
    kabSelect.innerHTML = `<option value="">-- Pilih Kabupaten --</option>`;
    kabSelect.disabled = true;

    if (!idProses) return;

    try {
      const res = await fetch(`${baseWilayah}?id_kegiatan_detail_proses=${idProses}`);
      const data = await res.json();

      if (data.length) {
        data.forEach(item => {
          const opt = document.createElement("option");
          opt.value = item.id_kegiatan_wilayah;
          opt.textContent = item.nama_kabupaten;
          kabSelect.appendChild(opt);
        });
        kabSelect.disabled = false;
      }
    } catch (e) {
      console.error('Error loading kabupaten:', e);
    }
  }

  // Load dan Render Kurva S dengan Realisasi
  async function loadAndRenderKurvaS(idProses = "", idWilayah = "") {
    try {
      let url = `${baseKurvaS}?id_kegiatan_detail_proses=${idProses}&nocache=${Date.now()}`;
      if (idWilayah) {
        url += `&id_kegiatan_wilayah=${idWilayah}`;
      }

      // Tampilkan Loading, Sembunyikan Chart & Error
      document.getElementById('chartLoadingState').style.display = 'flex';
      document.getElementById('kurvaSProvChart').style.display = 'none';
      document.getElementById('chartErrorState').style.display = 'none';
      document.getElementById('chartPlaceholder').style.display = 'none';

      const response = await fetch(url, { cache: "no-store" });
      const result = await response.json();

      if (!result.success) throw new Error(result.message || 'Failed to load data');

      // Delay kecil untuk transisi halus
      await new Promise(res => setTimeout(res, 200));

      document.getElementById('chartLoadingState').style.display = 'none';
      document.getElementById('kurvaSProvChart').style.display = 'block';
      renderChart(result.data);
    } catch (e) {
      console.error(e);
      document.getElementById('chartLoadingState').style.display = 'none';
      document.getElementById('chartErrorState').style.display = 'block';
    }
  }

  // Render Chart dengan Target dan Realisasi
  function renderChart(data) {
    if (chartInstance) {
      chartInstance.destroy();
      chartInstance = null;
    }

    const isMobile = window.innerWidth < 640;

    const options = {
      series: [
        {
          name: 'Target (Kurva S)',
          data: data.target,
          type: 'area'
        },
        {
          name: 'Realisasi (Kumulatif)',
          data: data.realisasi,
          type: 'area'
        }
      ],
      chart: {
        type: 'area',
        height: isMobile ? 300 : 420,
        fontFamily: 'Poppins, sans-serif',
        toolbar: {
          show: true,
          tools: {
            download: true,
            selection: true,
            zoom: true,
            zoomin: true,
            zoomout: true,
            pan: true,
            reset: true
          },
          autoSelected: 'zoom'
        },
        zoom: {
          enabled: true,
          type: 'x',
          autoScaleYaxis: true,
          zoomedArea: {
            fill: { color: '#90CAF9', opacity: 0.4 },
            stroke: { color: '#0D47A1', opacity: 0.4, width: 1 }
          }
        },
        animations: { enabled: true, speed: 600 }
      },
      colors: ['#1e88e5', '#e53935'],
      dataLabels: { enabled: false },
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
          opacityFrom: 0.5,
          opacityTo: 0.05
        }
      },
      markers: { size: 0, hover: { size: isMobile ? 5 : 7 } },
      xaxis: {
        categories: data.labels,
        title: { text: 'Tanggal', style: { fontSize: isMobile ? '11px' : '12px' } },
        labels: {
          rotate: isMobile ? -45 : 0,
          style: { fontSize: isMobile ? '10px' : '11px' }
        }
      },
      yaxis: {
        title: { text: isMobile ? '' : 'Jumlah', style: { fontSize: '12px' } },
        labels: {
          style: { fontSize: isMobile ? '9px' : '11px' },
          formatter: value => Math.round(value).toLocaleString('id-ID')
        }
      },
      tooltip: {
        shared: true,
        intersect: false,
        y: {
          formatter: value => value ? value.toLocaleString('id-ID') : '0'
        }
      },
      legend: {
        position: isMobile ? 'bottom' : 'top',
        horizontalAlign: isMobile ? 'center' : 'left',
        fontSize: isMobile ? '11px' : '13px'
      },
      grid: { borderColor: '#f3f4f6', strokeDashArray: 3 },
      annotations: {
        xaxis: isMobile ? [] : [
          {
            x: data.config.tanggal_mulai,
            borderColor: '#43a047',
            label: {
              text: 'Mulai',
              style: { color: '#fff', background: '#43a047', fontSize: '10px' }
            }
          },
          {
            x: data.config.tanggal_selesai,
            borderColor: '#e53935',
            label: {
              text: 'Selesai',
              style: { color: '#fff', background: '#e53935', fontSize: '10px' }
            }
          }
        ]
      }
    };

    chartInstance = new ApexCharts(document.querySelector("#kurvaSProvChart"), options);
    chartInstance.render();
  }

  // Load Petugas dengan Pagination dan AbortController
  async function loadPetugas(page = 1) {
    const kegiatanId = document.getElementById('filterKegiatanProses').value;
    const wilayahId = document.getElementById('filterKabupaten').value;

    const isDesktop = window.innerWidth >= 1024;
    const perPageElement = isDesktop ?
      document.getElementById('perPagePetugasSelect') :
      document.getElementById('perPagePetugasSelectMobile');
    const searchElement = isDesktop ?
      document.getElementById('searchPetugasInput') :
      document.getElementById('searchPetugasInputMobile');

    const perPage = perPageElement?.value || '10';
    const search = searchElement?.value || '';

    if (!kegiatanId) {
      resetPetugasTable();
      return;
    }

    const requestKegiatanId = kegiatanId;
    const requestWilayahId = wilayahId || 'all';

    currentPetugasPage = page;
    petugasPerPage = perPage;
    petugasSearchQuery = search;

    try {
      const wilayahParam = wilayahId || 'all';

      const response = await fetch(
        `${basePetugas}?id_kegiatan_detail_proses=${kegiatanId}&id_kegiatan_wilayah=${wilayahParam}&page=${page}&perPage=${perPage}&search=${encodeURIComponent(search)}`,
        { signal: abortController?.signal }
      );
      const result = await response.json();

      const currentWilayahCheck = currentWilayahId || 'all';
      if (currentKegiatanId !== requestKegiatanId || currentWilayahCheck !== requestWilayahId) {
        return; // Abaikan jika filter sudah berubah
      }

      if (result.success) {
        totalPetugasData = result.pagination.total;
        totalPetugasPages = result.pagination.total_pages;

        renderPetugasTable(result.data);
        renderPetugasCards(result.data);
        renderPetugasPagination(result.pagination);
        updatePetugasDataInfo(result.data.length, result.pagination.total, result.pagination.current_page, result.pagination.per_page);
      }
    } catch (e) {
      if (e.name !== 'AbortError') {
        console.error('Error loading petugas:', e);
      }
    }
  }

  function resetPetugasTable() {
      document.getElementById('petugasTableBody').innerHTML = `
      <tr>
        <td colspan="4" class="px-4 py-12 text-center">
          <i class="fas fa-info-circle text-gray-300 text-4xl mb-2"></i>
          <p class="text-gray-500">Pilih kegiatan untuk menampilkan data petugas</p>
        </td>
      </tr>`;
      document.getElementById('petugasCardContainer').innerHTML = `
      <div class="text-center py-12">
        <i class="fas fa-info-circle text-gray-300 text-4xl mb-2"></i>
        <p class="text-gray-500 text-sm">Pilih kegiatan untuk menampilkan data petugas</p>
      </div>`;
      document.getElementById('petugasDataInfo').innerHTML = 'Menampilkan 0 dari 0 total data';
      document.getElementById('petugasPagination').innerHTML = '';
  }

  function updatePetugasDataInfo(showing, total, currentPage, perPage) {
    const infoElement = document.getElementById('petugasDataInfo');
    if (total === 0) {
      infoElement.innerHTML = 'Menampilkan 0 dari 0 total data';
      return;
    }
    const start = (currentPage - 1) * perPage + 1;
    const end = Math.min(currentPage * perPage, total);
    infoElement.innerHTML = `Menampilkan data <span class="font-medium">${start}-${end}</span> dari <span class="font-medium">${total}</span> data`;
  }

  function renderPetugasPagination(pagination) {
    const container = document.getElementById('petugasPagination');
    if (pagination.total_pages <= 1) {
      container.innerHTML = '';
      return;
    }

    let html = '';
    const currentPage = pagination.current_page;
    const totalPages = pagination.total_pages;

    html += `<button onclick="loadPetugas(${currentPage - 1})" class="px-3 py-2 text-sm font-medium rounded-lg ${currentPage === 1 ? 'text-gray-400 cursor-not-allowed' : 'text-gray-700 hover:bg-gray-100'}" ${currentPage === 1 ? 'disabled' : ''}><i class="fas fa-chevron-left"></i></button>`;

    const maxVisible = 5;
    let startPage = Math.max(1, currentPage - Math.floor(maxVisible / 2));
    let endPage = Math.min(totalPages, startPage + maxVisible - 1);

    if (endPage - startPage < maxVisible - 1) {
      startPage = Math.max(1, endPage - maxVisible + 1);
    }

    if (startPage > 1) {
      html += `<button onclick="loadPetugas(1)" class="px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg">1</button>`;
      if (startPage > 2) html += `<span class="px-2 py-2 text-gray-400">...</span>`;
    }

    for (let i = startPage; i <= endPage; i++) {
      html += `<button onclick="loadPetugas(${i})" class="px-3 py-2 text-sm font-medium rounded-lg ${i === currentPage ? 'bg-blue-600 text-white' : 'text-gray-700 hover:bg-gray-100'}">${i}</button>`;
    }

    if (endPage < totalPages) {
      if (endPage < totalPages - 1) html += `<span class="px-2 py-2 text-gray-400">...</span>`;
      html += `<button onclick="loadPetugas(${totalPages})" class="px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg">${totalPages}</button>`;
    }

    html += `<button onclick="loadPetugas(${currentPage + 1})" class="px-3 py-2 text-sm font-medium rounded-lg ${currentPage === totalPages ? 'text-gray-400 cursor-not-allowed' : 'text-gray-700 hover:bg-gray-100'}" ${currentPage === totalPages ? 'disabled' : ''}><i class="fas fa-chevron-right"></i></button>`;

    container.innerHTML = html;
  }

  let petugasSearchTimeout;
  function handlePetugasSearch(event) {
    clearTimeout(petugasSearchTimeout);
    petugasSearchTimeout = setTimeout(function () {
      loadPetugas(1);
    }, 500);
  }

  function handlePetugasPerPageChange() {
    loadPetugas(1);
  }

  // ==================== TAB SWITCHING ====================
  function switchDataTab(tabName) {
    const tabs = document.querySelectorAll('.data-tab-button');
    tabs.forEach(tab => {
      tab.classList.remove('active', 'border-[#1e88e5]', 'text-[#1e88e5]');
      tab.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
    });

    const activeTab = document.getElementById(`datatab-${tabName}`);
    activeTab.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
    activeTab.classList.add('active', 'border-[#1e88e5]', 'text-[#1e88e5]');

    const contents = document.querySelectorAll('.data-tab-content');
    contents.forEach(content => content.classList.add('hidden'));
    document.getElementById(`datacontent-${tabName}`).classList.remove('hidden');

    if (tabName === 'kepatuhan') {
      const kegiatanId = document.getElementById('filterKegiatanProses').value;
      const wilayahId = document.getElementById('filterKabupaten').value;
      if (kegiatanId) {
        loadKepatuhanData(kegiatanId, wilayahId);
      }
    }
  }

  // ==================== SORTING ====================
  function toggleProgressSort() {
    const sortIcon = document.getElementById('sortIcon');

    if (currentSortOrder === 'none') {
      currentSortOrder = 'desc';
      sortIcon.className = 'fas fa-sort-down text-blue-600';
    } else if (currentSortOrder === 'desc') {
      currentSortOrder = 'asc';
      sortIcon.className = 'fas fa-sort-up text-blue-600';
    } else {
      currentSortOrder = 'none';
      sortIcon.className = 'fas fa-sort text-gray-400';
    }

    applySorting();
  }

  function applySorting() {
    const tbody = document.getElementById('petugasTableBody');
    const rows = Array.from(tbody.querySelectorAll('tr'));

    if (rows.length === 0 || rows[0].cells.length === 1) {
      return;
    }

    if (currentSortOrder === 'none') {
      loadPetugas(currentPetugasPage);
      return;
    }

    rows.sort((a, b) => {
      const progressA = parseFloat(a.cells[3].querySelector('.text-gray-900').textContent);
      const progressB = parseFloat(b.cells[3].querySelector('.text-gray-900').textContent);

      if (currentSortOrder === 'asc') {
        return progressA - progressB;
      } else {
        return progressB - progressA;
      }
    });

    tbody.innerHTML = '';
    rows.forEach(row => tbody.appendChild(row));
  }

  // ==================== KEPATUHAN FUNCTIONS ====================
  async function loadKepatuhanData(kegiatanId = null, wilayahId = null) {
    if (!kegiatanId) {
      kegiatanId = document.getElementById('filterKegiatanProses').value;
    }
    if (!wilayahId) {
      wilayahId = document.getElementById('filterKabupaten').value || 'all';
    }

    if (!kegiatanId) {
      resetKepatuhanDisplay();
      return;
    }

    const requestKegiatanId = kegiatanId;
    const requestWilayahId = wilayahId || 'all';

    try {
      const wilayahParam = wilayahId || 'all';

      const response = await fetch(
        `${baseKepatuhan}?id_kegiatan_detail_proses=${kegiatanId}&id_kegiatan_wilayah=${wilayahParam}`,
        { signal: abortController?.signal }
      );
      const result = await response.json();

      const currentWilayahCheck = currentWilayahId || 'all';
      if (currentKegiatanId !== requestKegiatanId || currentWilayahCheck !== requestWilayahId) {
        return;
      }

      if (result.success) {
        if (result.data.stats.total_pcl === 0) {
          showNoDataKepatuhan();
          return;
        }

        const noDataMsg = document.querySelector('.no-data-kepatuhan-message');
        if (noDataMsg) noDataMsg.style.display = 'none';

        document.getElementById('kepatuhanStatsCards').style.display = 'grid';
        document.getElementById('kepatuhanChartContainer').style.display = 'block';
        const leaderboardParent = document.getElementById('leaderboardContainer').parentElement.parentElement;
        leaderboardParent.style.display = 'grid';

        renderKepatuhanStats(result.data.stats);
        renderKepatuhanChart(result.data.chart);
        renderLeaderboard(result.data.leaderboard);
        renderTidakPatuh(result.data.tidak_patuh);
      } else {
        showNoDataKepatuhan();
      }
    } catch (error) {
      if (error.name !== 'AbortError') {
        showNoDataKepatuhan();
      }
    }
  }

  function showNoDataKepatuhan() {
    document.getElementById('leaderboardContainer').innerHTML = '<p class="text-center text-gray-400 py-8">Belum ada data</p>';
    document.getElementById('tidakPatuhContainer').innerHTML = '<p class="text-center text-gray-400 py-8">Belum ada data</p>';
    document.getElementById('kepatuhanStatsCards').innerHTML = '';

    if (kepatuhanChartInstance) {
      kepatuhanChartInstance.destroy();
      kepatuhanChartInstance = null;
    }

    document.getElementById('kepatuhanChartPlaceholder').innerHTML = '<p class="text-gray-400">Pilih kegiatan untuk menampilkan grafik kepatuhan</p>';
    document.getElementById('kepatuhanStatsCards').style.display = 'none';
    document.getElementById('kepatuhanChartContainer').style.display = 'none';
    const leaderboardParent = document.getElementById('leaderboardContainer').parentElement.parentElement;
    leaderboardParent.style.display = 'none';

    const contentDiv = document.getElementById('datacontent-kepatuhan');
    let existingMessage = contentDiv.querySelector('.no-data-kepatuhan-message');

    if (!existingMessage) {
      const messageDiv = document.createElement('div');
      messageDiv.className = 'no-data-kepatuhan-message flex flex-col items-center justify-center py-20';
      messageDiv.innerHTML = `
      <i class="fas fa-inbox text-gray-300 text-6xl mb-4"></i>
      <p class="text-gray-500 text-lg">Belum ada data petugas</p>
    `;
      contentDiv.appendChild(messageDiv);
    } else {
      existingMessage.style.display = 'flex';
    }
  }

  function resetKepatuhanDisplay() {
    document.getElementById('kepatuhanStatsCards').innerHTML = '';
    document.getElementById('leaderboardContainer').innerHTML = '<p class="text-center text-gray-400 py-8">Pilih kegiatan untuk melihat data</p>';
    document.getElementById('tidakPatuhContainer').innerHTML = '<p class="text-center text-gray-400 py-8">Pilih kegiatan untuk melihat data</p>';
    if (kepatuhanChartInstance) {
      kepatuhanChartInstance.destroy();
      kepatuhanChartInstance = null;
    }
    document.getElementById('kepatuhanChartPlaceholder').style.display = 'flex';
    document.getElementById('kepatuhanChartPlaceholder').innerHTML = '<p class="text-gray-400">Pilih kegiatan untuk menampilkan grafik kepatuhan</p>';
  }

  function renderKepatuhanStats(stats) {
    const container = document.getElementById('kepatuhanStatsCards');
    container.style.display = 'grid';

    const persentasePatuh = stats.persentase_patuh || 0;
    const persentaseKurangPatuh = stats.persentase_kurang_patuh || 0;
    const persentaseTidakPatuh = stats.persentase_tidak_patuh || 0;
    const rataRata = stats.rata_rata_kepatuhan || 0;

    const statsHtml = `
    <div class="bg-white rounded-lg border border-gray-200 p-3 lg:p-4 shadow-sm hover:shadow-md transition-shadow">
      <div class="flex items-center justify-between mb-2">
        <div class="w-8 h-8 lg:w-10 lg:h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
          <i class="fas fa-user-friends text-sm lg:text-lg text-blue-600"></i>
        </div>
        <p class="text-xs text-gray-600">Total PCL</p>
      </div>
      <p class="text-2xl lg:text-3xl font-bold text-gray-900">${stats.total_pcl || 0}</p>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 p-3 lg:p-4 shadow-sm hover:shadow-md transition-shadow">
      <div class="flex items-center justify-between mb-2">
        <div class="w-8 h-8 lg:w-10 lg:h-10 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
          <i class="fas fa-check-circle text-sm lg:text-lg text-green-600"></i>
        </div>
        <p class="text-xs text-gray-600">Patuh</p>
      </div>
      <p class="text-2xl lg:text-3xl font-bold text-gray-900">${stats.patuh || 0}</p>
      <p class="text-xs text-gray-500 mt-1">${persentasePatuh.toFixed(1)}%</p>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 p-3 lg:p-4 shadow-sm hover:shadow-md transition-shadow">
      <div class="flex items-center justify-between mb-2">
        <div class="w-8 h-8 lg:w-10 lg:h-10 bg-amber-50 rounded-lg flex items-center justify-center flex-shrink-0">
          <i class="fas fa-exclamation-circle text-sm lg:text-lg text-amber-600"></i>
        </div>
        <p class="text-xs text-gray-600">Kurang Patuh</p>
      </div>
      <p class="text-2xl lg:text-3xl font-bold text-gray-900">${stats.kurang_patuh || 0}</p>
      <p class="text-xs text-gray-500 mt-1">${persentaseKurangPatuh.toFixed(1)}%</p>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 p-3 lg:p-4 shadow-sm hover:shadow-md transition-shadow">
      <div class="flex items-center justify-between mb-2">
        <div class="w-8 h-8 lg:w-10 lg:h-10 bg-red-100 rounded-lg flex items-center justify-center flex-shrink-0">
          <i class="fas fa-times-circle text-sm lg:text-lg text-red-600"></i>
        </div>
        <p class="text-xs text-gray-600">Tidak Patuh</p>
      </div>
      <p class="text-2xl lg:text-3xl font-bold text-gray-900">${stats.tidak_patuh || 0}</p>
      <p class="text-xs text-gray-500 mt-1">${persentaseTidakPatuh.toFixed(1)}%</p>
    </div>

    <div class="col-span-2 lg:col-span-1 bg-gradient-to-r from-purple-50 to-indigo-50 rounded-lg border border-purple-200 p-3 lg:p-4 shadow-sm hover:shadow-md transition-shadow">
      <div class="flex items-center justify-between lg:flex-col lg:items-start">
        <div class="flex items-center gap-3 lg:flex-col lg:items-start lg:w-full">
          <div class="w-8 h-8 lg:w-10 lg:h-10 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0 lg:mb-2">
            <i class="fas fa-chart-bar text-sm lg:text-lg text-purple-600"></i>
          </div>
          <div>
            <p class="text-xs text-gray-600 mb-1">Rata-rata</p>
            <p class="text-2xl lg:text-3xl font-bold text-purple-700">${rataRata.toFixed(1)}%</p>
            <p class="text-xs text-gray-500 mt-1 hidden lg:block">Tingkat kepatuhan</p>
          </div>
        </div>
      </div>
    </div>
  `;

    container.innerHTML = statsHtml;
  }

  function renderKepatuhanChart(chartData) {
    document.getElementById('kepatuhanChartContainer').style.display = 'block';
    document.getElementById('kepatuhanChartPlaceholder').style.display = 'none';

    if (kepatuhanChartInstance) {
      kepatuhanChartInstance.destroy();
    }

    const isMobile = window.innerWidth < 640;

    if (chartData.type === 'bar') {
      const options = {
        series: [{ name: 'Kepatuhan (%)', data: chartData.data.map(item => item.persentase) }],
        chart: { type: 'bar', height: 400, fontFamily: 'Poppins, sans-serif', toolbar: { show: true } },
        plotOptions: { bar: { horizontal: true, distributed: true, dataLabels: { position: 'top' } } },
        colors: chartData.data.map(item => item.persentase >= 80 ? '#10b981' : item.persentase >= 50 ? '#f59e0b' : '#ef4444'),
        dataLabels: { enabled: true, formatter: val => val.toFixed(1) + '%', offsetX: -6, style: { fontSize: '12px', colors: ['#fff'] } },
        xaxis: { categories: chartData.data.map(item => item.nama_kabupaten), max: 100 },
        yaxis: { labels: { style: { fontSize: isMobile ? '10px' : '12px' } } },
        legend: { show: false },
        tooltip: { y: { formatter: val => val.toFixed(1) + '%' } }
      };

      kepatuhanChartInstance = new ApexCharts(document.querySelector("#kepatuhanChart"), options);
      kepatuhanChartInstance.render();
    } else {
      const options = {
        series: [{ name: 'Kepatuhan Harian', data: chartData.data.map(item => item.persentase) }],
        chart: { type: 'area', height: 380, fontFamily: 'Poppins, sans-serif', toolbar: { show: true }, zoom: { enabled: true } },
        colors: ['#10b981'],
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 3 },
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.7, opacityTo: 0.2, stops: [0, 100] } },
        xaxis: { categories: chartData.data.map(item => item.label || item.tanggal), labels: { style: { fontSize: isMobile ? '10px' : '11px' } } },
        yaxis: { max: 100, labels: { formatter: val => val.toFixed(0) + '%' } },
        tooltip: {
          y: {
            formatter: function (val, opts) {
              const dataPoint = chartData.data[opts.dataPointIndex];
              return val.toFixed(1) + '% (' + dataPoint.jumlah_lapor + '/' + dataPoint.total_pcl + ' PCL)';
            }
          }
        },
        markers: { size: 4, hover: { size: 6 } },
        grid: { borderColor: '#f3f4f6' }
      };

      kepatuhanChartInstance = new ApexCharts(document.querySelector("#kepatuhanChart"), options);
      kepatuhanChartInstance.render();
    }
  }

  function renderLeaderboard(data) {
    const container = document.getElementById('leaderboardContainer');
    if (!data || data.length === 0) {
      container.innerHTML = '<p class="text-center text-gray-400 py-8">Belum ada data</p>';
      return;
    }
    const html = data.map((item, index) => {
      const rankColor = index === 0 ? 'bg-yellow-100 text-yellow-800' : index === 1 ? 'bg-gray-100 text-gray-700' : index === 2 ? 'bg-orange-100 text-orange-700' : 'bg-blue-50 text-blue-700';
      const persentase = parseFloat(item.persentase_kepatuhan || 0);
      const statusClass = persentase >= 80 ? 'bg-green-100 text-green-800' : persentase >= 50 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800';
      return `
      <div class="flex items-center justify-between p-3 rounded-lg border border-gray-200 hover:shadow-sm transition-shadow">
        <div class="flex items-center gap-3 flex-1">
          <div class="w-8 h-8 rounded-full ${rankColor} flex items-center justify-center font-bold text-sm flex-shrink-0">${index + 1}</div>
          <div class="min-w-0 flex-1">
            <p class="text-sm font-medium text-gray-900 truncate">${item.nama_pcl || '-'}</p>
            ${item.nama_kabupaten ? `<p class="text-xs text-gray-500">${item.nama_kabupaten}</p>` : ''}
          </div>
        </div>
        <div class="flex items-center gap-3">
          <div class="text-right">
            <p class="text-sm font-semibold text-gray-900">${item.jumlah_laporan || 0} / ${item.total_hari_kerja || 0}</p>
            <p class="text-xs text-gray-500">hari</p>
          </div>
          <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium ${statusClass}">${persentase.toFixed(1)}%</span>
        </div>
      </div>
    `;
    }).join('');
    container.innerHTML = html;
  }

  function renderTidakPatuh(data) {
    const container = document.getElementById('tidakPatuhContainer');
    if (!data || data.length === 0) {
      container.innerHTML = `
      <div class="text-center py-8">
        <i class="fas fa-check-circle text-green-300 text-4xl mb-2"></i>
        <p class="text-gray-500">Semua petugas patuh!</p>
      </div>
    `;
      return;
    }

    const html = data.map(item => {
      const persentase = parseFloat(item.persentase_kepatuhan || 0);
      return `
      <div class="p-4 rounded-lg border-2 border-red-200 bg-red-50">
        <div class="flex items-start justify-between mb-2">
          <div class="flex-1">
            <p class="font-medium text-gray-900">${item.nama_pcl || '-'}</p>
            ${item.nama_kabupaten ? `<p class="text-xs text-gray-600">${item.nama_kabupaten}</p>` : ''}
          </div>
          <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">${persentase.toFixed(1)}%</span>
        </div>
        <div class="space-y-1">
          <div class="flex items-center text-sm text-gray-700">
            <i class="fas fa-calendar-times text-red-500 mr-2 w-4"></i>
            <span>Terakhir lapor: ${item.terakhir_lapor || 'Belum pernah lapor'}</span>
          </div>
          <div class="flex items-center text-sm text-gray-700">
            <i class="fas fa-clock text-red-500 mr-2 w-4"></i>
            <span>Tidak lapor: ${item.hari_tidak_lapor || 0} hari</span>
          </div>
          <div class="flex items-center text-sm text-gray-700">
            <i class="fas fa-chart-line text-red-500 mr-2 w-4"></i>
            <span>Laporan: ${item.jumlah_laporan || 0} / ${item.total_hari_kerja || 0} hari</span>
          </div>
        </div>
      </div>
    `;
    }).join('');

    container.innerHTML = html;
  }

  // Render Petugas Table
  function renderPetugasTable(data) {
    const tbody = document.getElementById('petugasTableBody');

    if (data.length === 0) {
      tbody.innerHTML = `
      <tr>
        <td colspan="4" class="px-4 py-12 text-center">
          <i class="fas fa-inbox text-gray-300 text-4xl mb-2"></i>
          <p class="text-gray-500">Belum ada data petugas</p>
        </td>
      </tr>
    `;
      return;
    }

    const colors = ['#3b82f6', '#10b981', '#8b5cf6', '#f59e0b', '#ef4444'];

    tbody.innerHTML = data.map((p, index) => `
    <tr class="hover:bg-gray-50 transition-colors duration-150">
      <td class="px-4 py-4">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0" 
               style="background-color: ${colors[index % colors.length]};">
            <span class="text-white text-sm font-semibold">${p.nama_user.substring(0, 2).toUpperCase()}</span>
          </div>
          <div class="min-w-0">
            <p class="text-sm font-medium text-gray-900 truncate">${p.nama_user}</p>
            <p class="text-xs text-gray-500 truncate">${p.role} - ${p.nama_kabupaten}</p>
          </div>
        </div>
      </td>
      <td class="px-4 py-4">
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${p.status_kegiatan_class === 'badge-danger' ? 'bg-red-100 text-red-800' : ''} ${p.status_kegiatan_class === 'badge-success' ? 'bg-green-100 text-green-800' : ''} ${p.status_kegiatan_class === 'badge-warning' ? 'bg-yellow-100 text-yellow-800' : ''} ${p.status_kegiatan_class === 'badge-secondary' ? 'bg-gray-100 text-gray-700' : ''}">
          ${p.status_kegiatan}
        </span>
      </td>
      <td class="px-4 py-4">
        <div class="flex flex-col gap-1 items-start">
          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${p.status_harian_class === 'badge-danger' ? 'bg-red-100 text-red-800' : ''} ${p.status_harian_class === 'badge-success' ? 'bg-green-100 text-green-800' : ''} ${p.status_harian_class === 'badge-warning' ? 'bg-yellow-100 text-yellow-800' : ''} ${p.status_harian_class === 'badge-info' ? 'bg-blue-100 text-blue-800' : ''} ${p.status_harian_class === 'badge-secondary' ? 'bg-gray-100 text-gray-700' : ''}">
            ${p.status_harian}
          </span>
          ${p.status_harian === 'Belum Lapor' && p.target_harian > 0 ? `
            <span class="text-xs text-gray-600">
              <i class="fas fa-bullseye mr-1"></i>
              Target: ${p.target_harian}
            </span>
          ` : ''}
          ${p.status_harian !== 'Tidak Perlu Lapor' && p.status_harian !== 'Belum Lapor' && (p.realisasi_hari_ini > 0 || p.target_harian > 0) ? `
            <span class="text-xs text-gray-600">
              <i class="fas fa-check-circle mr-1"></i>
              ${p.realisasi_hari_ini}${p.target_harian > 0 ? ' / ' + p.target_harian : ''}
            </span>
          ` : ''}
        </div>
      </td>
      <td class="px-4 py-4">
        <div class="flex items-center gap-3">
          <div class="flex-1">
            <div class="w-full bg-gray-200 rounded-full h-2">
              <div class="h-2 rounded-full transition-all duration-300" 
                   style="width: ${p.progress}%; background-color: ${colors[index % colors.length]};"></div>
            </div>
          </div>
          <span class="text-sm font-semibold text-gray-900 min-w-[3rem] text-right">${p.progress}%</span>
        </div>
      </td>
    </tr>
  `).join('');
  }

  function renderPetugasCards(data) {
    const container = document.getElementById('petugasCardContainer');

    if (data.length === 0) {
      container.innerHTML = `
      <div class="text-center py-12">
        <i class="fas fa-inbox text-gray-300 text-4xl mb-2"></i>
        <p class="text-gray-500 text-sm">Belum ada data petugas</p>
      </div>
    `;
      return;
    }

    const colors = ['#3b82f6', '#10b981', '#8b5cf6', '#f59e0b', '#ef4444'];

    container.innerHTML = data.map((p, index) => `
    <div class="bg-white rounded-lg border border-gray-200 p-3 shadow-sm">
      <div class="flex items-center gap-2.5 mb-3 pb-3 border-b border-gray-100">
        <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0" 
          style="background-color: ${colors[index % colors.length]};">
          <span class="text-white text-sm font-semibold">${p.nama_user.substring(0, 2).toUpperCase()}</span>
        </div>
        <div class="min-w-0 flex-1">
          <p class="text-sm font-semibold text-gray-900 truncate">${p.nama_user}</p>
          <p class="text-xs text-gray-500 truncate">${p.role} • ${p.nama_kabupaten}</p>
        </div>
      </div>
      <div class="space-y-2 mb-3">
        <div class="flex items-center justify-between">
          <span class="text-xs text-gray-600">
            <i class="fas fa-calendar-check mr-1.5 text-gray-400"></i>Status Kegiatan
          </span>
          <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium ${p.status_kegiatan_class === 'badge-danger' ? 'bg-red-100 text-red-800' : ''} ${p.status_kegiatan_class === 'badge-success' ? 'bg-green-100 text-green-800' : ''} ${p.status_kegiatan_class === 'badge-warning' ? 'bg-yellow-100 text-yellow-800' : ''} ${p.status_kegiatan_class === 'badge-secondary' ? 'bg-gray-100 text-gray-700' : ''}">
            ${p.status_kegiatan}
          </span>
        </div>
        <div class="flex items-start justify-between">
          <span class="text-xs text-gray-600">
            <i class="fas fa-clipboard-check mr-1.5 text-gray-400"></i>Status Harian
          </span>
          <div class="flex flex-col gap-1 items-end">
            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium ${p.status_harian_class === 'badge-danger' ? 'bg-red-100 text-red-800' : ''} ${p.status_harian_class === 'badge-success' ? 'bg-green-100 text-green-800' : ''} ${p.status_harian_class === 'badge-warning' ? 'bg-yellow-100 text-yellow-800' : ''} ${p.status_harian_class === 'badge-info' ? 'bg-blue-100 text-blue-800' : ''} ${p.status_harian_class === 'badge-secondary' ? 'bg-gray-100 text-gray-700' : ''}">
              ${p.status_harian}
            </span>
            ${p.status_harian === 'Belum Lapor' && p.target_harian > 0 ? `
              <span class="text-xs text-gray-600">
                <i class="fas fa-bullseye mr-0.5"></i>Target: ${p.target_harian}
              </span>
            ` : ''}
            ${p.status_harian !== 'Tidak Perlu Lapor' && p.status_harian !== 'Belum Lapor' && (p.realisasi_hari_ini > 0 || p.target_harian > 0) ? `
              <span class="text-xs text-gray-600">
                <i class="fas fa-check-circle mr-0.5"></i>${p.realisasi_hari_ini}${p.target_harian > 0 ? ' / ' + p.target_harian : ''}
              </span>
            ` : ''}
          </div>
        </div>
      </div>
      <div class="pt-2 border-t border-gray-100">
        <div class="flex items-center justify-between mb-1.5">
          <span class="text-xs font-medium text-gray-600">
            <i class="fas fa-tasks mr-1.5 text-gray-400"></i>Progress
          </span>
          <span class="text-sm font-bold text-gray-900">${p.progress}%</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2">
          <div class="h-2 rounded-full transition-all duration-300" 
            style="width: ${p.progress}%; background-color: ${colors[index % colors.length]};"></div>
        </div>
      </div>
    </div>
  `).join('');
  }

  // Event Listeners
  document.addEventListener("DOMContentLoaded", function () {
    if (defaultKegiatan) {
      document.getElementById('filterKegiatanProses').value = defaultKegiatan;
      currentKegiatanId = defaultKegiatan;
      currentWilayahId = 'all';

      loadAndRenderKurvaS(defaultKegiatan, '');
      loadKabupatenDropdown(defaultKegiatan);
      loadPetugas(1);
    } else {
      document.getElementById('chartLoadingState').style.display = 'none';
      document.getElementById('chartPlaceholder').style.display = 'flex';
    }

    document.getElementById('filterKegiatanProses').addEventListener('change', function () {
      const idProses = this.value;

      if (abortController) {
        abortController.abort();
      }
      abortController = new AbortController();

      currentKegiatanId = idProses;
      currentWilayahId = 'all';

      document.getElementById('searchPetugasInput').value = '';
      document.getElementById('perPagePetugasSelect').value = '10';

      currentSortOrder = 'none';
      const sortIcon = document.getElementById('sortIcon');
      if (sortIcon) {
        sortIcon.className = 'fas fa-sort text-gray-400';
      }

      if (!idProses) {
        if (chartInstance) {
          chartInstance.destroy();
          chartInstance = null;
        }
        document.getElementById('chartLoadingState').style.display = 'none';
        document.getElementById('chartPlaceholder').style.display = 'flex';
        document.getElementById('filterKabupaten').disabled = true;
        document.getElementById('filterKabupaten').innerHTML = '<option value="">-- Pilih Kabupaten --</option>';

        resetPetugasTable();
        resetKepatuhanDisplay();
        return;
      }

      // Reset dropdown kabupaten
      document.getElementById('filterKabupaten').value = '';

      loadKabupatenDropdown(idProses);
      loadAndRenderKurvaS(idProses, '');
      loadPetugas(1);

      // Load kepatuhan jika tab aktif
      const activeTab = document.querySelector('.data-tab-button.active');
      if (activeTab && activeTab.id === 'datatab-kepatuhan') {
        loadKepatuhanData(idProses, 'all');
      }
    });

    document.getElementById('filterKabupaten').addEventListener('change', function () {
      const idKab = this.value;
      const idProses = document.getElementById('filterKegiatanProses').value;

      if (!idProses) return;

      if (abortController) {
        abortController.abort();
      }
      abortController = new AbortController();

      currentKegiatanId = idProses;
      currentWilayahId = idKab || 'all';

      document.getElementById('searchPetugasInput').value = '';
      document.getElementById('perPagePetugasSelect').value = '10';

      currentSortOrder = 'none';
      const sortIcon = document.getElementById('sortIcon');
      if (sortIcon) {
        sortIcon.className = 'fas fa-sort text-gray-400';
      }

      loadAndRenderKurvaS(idProses, idKab);
      loadPetugas(1);

      const activeTab = document.querySelector('.data-tab-button.active');
      if (activeTab && activeTab.id === 'datatab-kepatuhan') {
        loadKepatuhanData(idProses, idKab || 'all');
      }
    });

    // Animate progress bars
    setTimeout(() => {
      document.querySelectorAll('[data-width]').forEach(bar => {
        bar.style.width = bar.dataset.width + '%';
      });
    }, 400);
  });

  // Window resize handler
  let resizeTimer;
  window.addEventListener('resize', function () {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(function () {
      const idProses = document.getElementById('filterKegiatanProses').value;
      const idKab = document.getElementById('filterKabupaten').value;
      if (chartInstance && idProses) {
        loadAndRenderKurvaS(idProses, idKab);
      }
    }, 250);
  });
</script>

<style>
  .data-tab-button {
    transition: all 0.2s ease;
    min-width: 140px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }

  .data-tab-button.active {
    border-color: #1e88e5 !important;
    color: #1e88e5 !important;
  }

  #kepatuhanStatsCards {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.75rem;
  }

  #kepatuhanStatsCards>div {
    transition: all 0.3s ease;
  }

  #kepatuhanStatsCards>div:hover {
    transform: translateY(-2px);
  }

  @media (min-width: 1024px) {
    #kepatuhanStatsCards {
      grid-template-columns: repeat(5, 1fr);
      gap: 1rem;
    }
  }

  @media (max-width: 640px) {
    .data-tab-button {
      min-width: 120px;
      font-size: 0.813rem;
    }
  }

  #petugasCardContainer>div:not(:last-child) {
    margin-bottom: 0.75rem;
  }

  /* Custom Scrollbar */
  .custom-scrollbar::-webkit-scrollbar {
    width: 6px;
  }

  .custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
  }

  .custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e0;
    border-radius: 10px;
  }

  .custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #a0aec0;
  }

  .custom-scrollbar {
    scrollbar-width: thin;
    scrollbar-color: #cbd5e0 #f1f1f1;
  }
</style>

<?= $this->endSection() ?>