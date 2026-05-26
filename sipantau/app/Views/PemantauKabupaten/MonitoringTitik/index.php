<?= $this->extend($layout ?? 'layouts/pemantau_kabupaten_layout') ?>

<?= $this->section('content') ?>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Monitoring Titik Kegiatan</h1>
    <p class="text-sm text-gray-600 mt-1">Pantau lokasi kegiatan petugas dan unduh dokumentasi pelaporan.</p>
</div>

<!-- Flash Alerts -->
<?php if (session()->getFlashdata('error')): ?>
    <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded shadow-sm">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-circle-xmark text-red-500 text-lg"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm text-red-700 font-medium"><?= esc(session()->getFlashdata('error')) ?></p>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php if (session()->getFlashdata('success')): ?>
    <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded shadow-sm">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-circle-check text-green-500 text-lg"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm text-green-700 font-medium"><?= esc(session()->getFlashdata('success')) ?></p>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Filter Section -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="select_kegiatan" class="block text-sm font-medium text-gray-700 mb-1">Pilih Kegiatan (Proses)</label>
            <select id="select_kegiatan" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                <option value="">-- Pilih Kegiatan --</option>
                <?php foreach ($kegiatanProsesList as $kegiatan): ?>
                    <option value="<?= $kegiatan['id_kegiatan_detail_proses'] ?>">
                        <?= esc($kegiatan['nama_kegiatan_detail']) ?> - <?= esc($kegiatan['nama_kegiatan_detail_proses'] ?? 'Proses') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="select_petugas" class="block text-sm font-medium text-gray-700 mb-1">Pilih Petugas (PCL)</label>
            <select id="select_petugas" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" disabled>
                <option value="">-- Pilih Petugas --</option>
            </select>
        </div>
    </div>
</div>

<!-- Info & Action Section -->
<div id="section_detail" class="hidden animate-fadeIn">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Info Card -->
        <div class="lg:col-span-2 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 id="display_nama_kegiatan" class="text-lg font-bold text-gray-900">Nama Kegiatan</h2>
                    <p id="display_tanggal" class="text-sm text-gray-600 mt-1">01 Jan 2024 - 31 Jan 2024</p>
                    <div class="mt-4 flex items-center">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-user text-blue-600"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase font-semibold">Petugas Lapangan (PCL)</p>
                            <p id="display_nama_petugas" class="text-base font-bold text-gray-900">Nama Petugas</p>
                        </div>
                    </div>
                </div>
                <div>
                    <button id="btn_download" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        <i class="fas fa-file-excel mr-2"></i>
                        Download Dokumentasi (Excel)
                    </button>
                </div>
            </div>
        </div>

        <!-- Stats Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 flex flex-col justify-center items-center">
            <p class="text-sm text-gray-500 mb-1 text-center">Total Titik Terpantau</p>
            <p id="display_total_titik" class="text-5xl font-black text-blue-600">0</p>
            <p class="text-xs text-gray-400 mt-2">Data koordinat dari aplikasi mobile</p>
        </div>
    </div>

    <!-- Map Section -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="font-bold text-gray-900 flex items-center">
                <i class="fas fa-map-marked-alt mr-2 text-blue-600"></i>
                Sebaran Lokasi Kegiatan
            </h3>
            <span class="text-xs text-gray-500 italic">* Klik pada marker untuk melihat detail dokumentasi</span>
        </div>
        <div id="map" class="w-full h-[500px] z-0"></div>
    </div>
</div>

<!-- Placeholder -->
<div id="section_placeholder" class="bg-gray-50 rounded-lg border-2 border-dashed border-gray-300 p-12 text-center">
    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <i class="fas fa-map-location-dot text-gray-400 text-3xl"></i>
    </div>
    <h3 class="text-lg font-medium text-gray-900">Belum Ada Data Terpilih</h3>
    <p class="text-sm text-gray-500 mt-1 max-w-xs mx-auto">Silakan pilih kegiatan dan petugas terlebih dahulu untuk menampilkan monitoring titik lokasi.</p>
</div>

<!-- Leaflet CSS & JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
    .animate-fadeIn {
        animation: fadeIn 0.5s ease-out forwards;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .leaflet-popup-content-wrapper {
        border-radius: 8px;
        padding: 0;
        overflow: hidden;
    }
    .leaflet-popup-content {
        margin: 0;
        width: 250px !important;
    }
</style>

<script>
    let map = null;
    let markersLayer = null;

    document.addEventListener('DOMContentLoaded', function() {
        const selectKegiatan = document.getElementById('select_kegiatan');
        const selectPetugas = document.getElementById('select_petugas');
        const sectionDetail = document.getElementById('section_detail');
        const sectionPlaceholder = document.getElementById('section_placeholder');
        const btnDownload = document.getElementById('btn_download');

        // Detect current group (admin or pemantau)
        const currentPath = window.location.pathname;
        const group = currentPath.includes('adminsurvei-kab') ? 'adminsurvei-kab' : 'pemantau-kabupaten';
        const apiBase = `<?= base_url() ?>/${group}/monitoring-titik`;

        // Handle Select Kegiatan
        selectKegiatan.addEventListener('change', async function() {
            const idProses = this.value;
            selectPetugas.innerHTML = '<option value="">-- Pilih Petugas --</option>';
            selectPetugas.disabled = true;
            hideDetail();

            if (!idProses) return;

            try {
                const response = await fetch(`${apiBase}/get-petugas/${idProses}`);
                const result = await response.json();

                if (result.success && result.data.length > 0) {
                    // Tambahkan opsi "Semua Petugas" di awal
                    const optAll = document.createElement('option');
                    optAll.value = 'all';
                    optAll.textContent = 'Semua Petugas';
                    selectPetugas.appendChild(optAll);

                    result.data.forEach(p => {
                        const opt = document.createElement('option');
                        opt.value = p.id_pcl;
                        opt.textContent = p.nama_pcl;
                        selectPetugas.appendChild(opt);
                    });
                    selectPetugas.disabled = false;
                } else if (result.success && result.data.length === 0) {
                    alert('Tidak ada petugas yang ditugaskan pada kegiatan ini di kabupaten Anda.');
                }
            } catch (error) {
                console.error('Error fetching petugas:', error);
            }
        });

        // Handle Select Petugas
        selectPetugas.addEventListener('change', async function() {
            const idPCL = this.value;
            const idProses = selectKegiatan.value;

            if (!idPCL || !idProses) {
                hideDetail();
                return;
            }

            loadData(idProses, idPCL);
        });

        // Handle Download
        btnDownload.addEventListener('click', function() {
            const idProses = selectKegiatan.value;
            const idPCL = selectPetugas.value;
            const totalTitik = parseInt(document.getElementById('display_total_titik').textContent || '0');

            if (totalTitik === 0) {
                alert('Tidak ada data dokumentasi untuk diunduh (jumlah titik = 0).');
                return;
            }

            if (idProses && idPCL) {
                window.location.href = `${apiBase}/download-dokumentasi?id_kegiatan_detail_proses=${idProses}&id_pcl=${idPCL}`;
            }
        });

        async function loadData(idProses, idPCL) {
            try {
                const response = await fetch(`${apiBase}/get-data?id_kegiatan_detail_proses=${idProses}&id_pcl=${idPCL}`);
                const result = await response.json();

                if (result.success) {
                    showDetail(result.info, result.points);
                } else {
                    alert(result.message);
                    hideDetail();
                }
            } catch (error) {
                console.error('Error loading monitoring data:', error);
                alert('Terjadi kesalahan saat memuat data.');
            }
        }

        function showDetail(info, points) {
            sectionPlaceholder.classList.add('hidden');
            sectionDetail.classList.remove('hidden');

            document.getElementById('display_nama_kegiatan').textContent = info.nama_kegiatan_detail_proses;
            document.getElementById('display_tanggal').textContent = `${formatDate(info.tanggal_mulai)} - ${formatDate(info.tanggal_selesai)}`;
            document.getElementById('display_nama_petugas').textContent = info.nama_pcl;
            document.getElementById('display_total_titik').textContent = points.length;

            initMap(points);
        }

        function hideDetail() {
            sectionPlaceholder.classList.remove('hidden');
            sectionDetail.classList.add('hidden');
        }

        function initMap(points) {
            // Destroy existing map if any
            if (map) {
                map.remove();
            }

            // Create new map
            map = L.map('map').setView([-0.5071, 101.4478], 8); // Default Riau

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            markersLayer = L.featureGroup().addTo(map);

            if (points.length > 0) {
                points.forEach(point => {
                    if (point.latitude && point.longitude) {
                        const popupContent = `
                            <div class="bg-white">
                                ${point.imagepath ? `<img src="<?= base_url() ?>/${point.imagepath}" class="w-full h-40 object-cover border-b" onerror="this.src='<?= base_url('assets/gambar/no-image.png') ?>'">` : '<div class="h-40 bg-gray-100 flex items-center justify-center border-b"><i class="fas fa-image text-gray-300 text-3xl"></i></div>'}
                                <div class="p-3">
                                    <p class="text-xs text-gray-500 mb-1 font-semibold">${formatDateTime(point.created_at)}</p>
                                    <p class="text-sm font-bold text-gray-800 mb-1">${point.nama_kecamatan}, ${point.nama_desa}</p>
                                    <p class="text-xs text-blue-600 font-semibold mb-2">Petugas: ${point.nama_pcl || '-'}</p>
                                    <div class="bg-gray-50 p-2 rounded text-xs text-gray-600 italic">
                                        "${point.resume || 'Tidak ada catatan'}"
                                    </div>
                                    <div class="mt-2 flex justify-between items-center text-[10px] text-gray-400">
                                        <span>Lat: ${point.latitude}</span>
                                        <span>Long: ${point.longitude}</span>
                                    </div>
                                </div>
                            </div>
                        `;

                        L.marker([point.latitude, point.longitude])
                            .bindPopup(popupContent)
                            .addTo(markersLayer);
                    }
                });

                // Auto zoom to markers
                map.fitBounds(markersLayer.getBounds(), { padding: [50, 50] });
            }
        }

        function formatDate(dateStr) {
            const date = new Date(dateStr);
            return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
        }

        function formatDateTime(dateStr) {
            const date = new Date(dateStr);
            return date.toLocaleDateString('id-ID', { 
                day: '2-digit', 
                month: 'short', 
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
    });
</script>

<?= $this->endSection() ?>
