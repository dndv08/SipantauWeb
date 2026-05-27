<?= $this->extend('layouts/petugas_layout') ?>
<?= $this->section('content') ?>

<style>
/* ── Camera Modal ── */
#cameraModal {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 9999;
    background: rgba(0,0,0,0.85);
    align-items: center;
    justify-content: center;
}
#cameraModal.active { display: flex; }
#videoStream {
    width: 100%;
    max-width: 480px;
    border-radius: 12px;
    background: #000;
}
#captureCanvas { display: none; }

/* ── Preview Foto ── */
#fotoPreviewWrapper {
    display: none;
    position: relative;
}
#fotoPreview {
    width: 100%;
    max-height: 220px;
    object-fit: cover;
    border-radius: 10px;
    border: 2px solid #3b82f6;
}
#removeFoto {
    position: absolute;
    top: 6px;
    right: 6px;
    background: rgba(239,68,68,0.9);
    color: #fff;
    border: none;
    border-radius: 50%;
    width: 28px;
    height: 28px;
    cursor: pointer;
    font-size: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* ── GPS Status ── */
.gps-status-dot {
    width: 10px; height: 10px;
    border-radius: 50%;
    display: inline-block;
    margin-right: 6px;
}
.gps-waiting  { background: #f59e0b; animation: pulse 1.2s infinite; }
.gps-found    { background: #22c55e; }
.gps-error    { background: #ef4444; }
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50%       { opacity: 0.3; }
}

/* ── Section Card ── */
.form-section {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}
.form-section-title {
    font-size: 0.875rem;
    font-weight: 600;
    color: #1e3a5f;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 8px;
    padding-bottom: 0.625rem;
    border-bottom: 1px solid #f1f5f9;
}

/* ── Histori Table ── */
.histori-img {
    width: 50px; height: 50px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    cursor: pointer;
}
.badge-wilayah {
    font-size: 0.7rem;
    background: #eff6ff;
    color: #1d4ed8;
    border-radius: 999px;
    padding: 2px 8px;
    white-space: nowrap;
}
</style>

<!-- Page Header -->
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Lapor Aktivitas</h1>
    <p class="text-sm text-gray-500 mt-1">Laporkan aktivitas harian beserta foto dan lokasi Anda</p>
</div>

<!-- ════════════════ FORM LAPORAN ════════════════ -->
<div class="form-section">
    <div class="form-section-title">
        <i class="fas fa-plus-circle text-blue-600"></i> Buat Laporan Baru
    </div>

    <form action="<?= base_url('petugas/lapor-aktivitas/store') ?>" method="post"
          enctype="multipart/form-data" id="formLaporan">
        <?= csrf_field() ?>

        <!-- Baris 1: Kegiatan + Jumlah -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Kegiatan (PCL) <span class="text-red-500">*</span>
                </label>
                <select name="id_pcl" id="id_pcl" required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                           focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">-- Pilih Kegiatan --</option>
                    <?php foreach ($kegiatanList as $k): ?>
                    <option value="<?= $k['id_pcl'] ?>"
                            data-target="<?= esc($k['target'] ?? 0) ?>"
                            data-kumulatif="<?= esc($k['realisasi_kumulatif'] ?? 0) ?>">
                        <?= esc($k['nama_kegiatan_detail_proses']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Jumlah Realisasi <span class="text-red-500">*</span>
                </label>
                <input type="number" name="jumlah" id="jumlah" min="1" required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                           focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Contoh: 5">
            </div>
        </div>

        <!-- Catatan -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Catatan Aktivitas</label>
            <textarea name="catatan" id="catatan" rows="2"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                       focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="Catatan tambahan (opsional)"></textarea>
        </div>

        <!-- ── FOTO AKTIVITAS ── -->
        <div class="form-section-title mt-2">
            <i class="fas fa-camera text-purple-600"></i> Foto Aktivitas
        </div>

        <!-- Preview -->
        <div id="fotoPreviewWrapper" class="mb-3">
            <img id="fotoPreview" src="" alt="Preview Foto">
            <button type="button" id="removeFoto" title="Hapus foto">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Input file tersembunyi -->
        <input type="file" name="foto_aktivitas" id="fotoInput" accept="image/*"
               class="hidden" capture="environment">

        <!-- Tombol Foto -->
        <div class="flex flex-wrap gap-3 mb-1">
            <button type="button" id="btnKamera"
                class="flex items-center gap-2 px-4 py-2 bg-purple-600 text-white text-sm
                       font-medium rounded-lg hover:bg-purple-700 transition-colors">
                <i class="fas fa-camera"></i> Ambil Foto Kamera
            </button>
            <button type="button" id="btnUpload"
                class="flex items-center gap-2 px-4 py-2 bg-gray-200 text-gray-700 text-sm
                       font-medium rounded-lg hover:bg-gray-300 transition-colors">
                <i class="fas fa-upload"></i> Upload dari Galeri
            </button>
        </div>
        <p class="text-xs text-gray-400 mb-4">
            Format: JPG, PNG, WEBP &bull; Maks. 5 MB &bull; Opsional
        </p>

        <!-- ── LOKASI GPS ── -->
        <div class="form-section-title">
            <i class="fas fa-map-marker-alt text-green-600"></i> Titik Koordinat Lokasi
        </div>

        <div class="flex flex-wrap items-center gap-3 mb-3">
            <button type="button" id="btnGPS"
                class="flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm
                       font-medium rounded-lg hover:bg-green-700 transition-colors">
                <i class="fas fa-crosshairs"></i> Ambil Lokasi Saya
            </button>
            <div id="gpsStatus" class="flex items-center text-sm text-gray-500">
                <span class="gps-status-dot bg-gray-300"></span>
                <span id="gpsStatusText">Belum diambil</span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Latitude</label>
                <input type="text" name="latitude" id="latInput" readonly
                    placeholder="Klik 'Ambil Lokasi' terlebih dahulu"
                    class="w-full border border-gray-200 bg-gray-50 rounded-lg px-3 py-2 text-sm text-gray-600">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Longitude</label>
                <input type="text" name="longitude" id="lngInput" readonly
                    placeholder="Klik 'Ambil Lokasi' terlebih dahulu"
                    class="w-full border border-gray-200 bg-gray-50 rounded-lg px-3 py-2 text-sm text-gray-600">
            </div>
        </div>

        <!-- Link Google Maps preview -->
        <div id="mapsLink" class="hidden mb-4">
            <a id="mapsAnchor" href="#" target="_blank"
               class="inline-flex items-center gap-1 text-xs text-blue-600 hover:underline">
                <i class="fas fa-external-link-alt"></i>
                Lihat di Google Maps
            </a>
        </div>

        <!-- ── WILAYAH ── -->
        <div class="form-section-title">
            <i class="fas fa-map text-orange-600"></i> Wilayah Kunjungan
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">

            <!-- Kecamatan -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kecamatan</label>
                <select name="id_kecamatan" id="selKecamatan"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                           focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">-- Pilih Kecamatan --</option>
                    <?php foreach ($kecamatanList as $kec): ?>
                    <option value="<?= $kec['id_kecamatan'] ?>">
                        <?= esc($kec['nama_kecamatan']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Kelurahan/Desa -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kelurahan / Desa</label>
                <select name="id_desa" id="selDesa"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                           focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    disabled>
                    <option value="">-- Pilih Kelurahan --</option>
                </select>
            </div>

            <!-- SLS -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">SLS</label>
                <select name="id_sls" id="selSLS"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                           focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    disabled>
                    <option value="">-- Pilih SLS --</option>
                </select>
            </div>

            <!-- Sub-SLS -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sub SLS</label>
                <select name="id_sub_sls" id="selSubSLS"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                           focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    disabled>
                    <option value="">-- Pilih Sub SLS --</option>
                </select>
            </div>
        </div>

        <!-- Submit -->
        <div class="flex justify-end pt-2 border-t border-gray-100">
            <button type="submit" id="btnSubmit"
                class="flex items-center gap-2 bg-blue-600 text-white px-6 py-2.5 rounded-lg
                       text-sm font-medium hover:bg-blue-700 transition-colors">
                <i class="fas fa-paper-plane"></i> Kirim Laporan
            </button>
        </div>
    </form>
</div>

<!-- ════════════════ HISTORI LAPORAN ════════════════ -->
<div class="form-section">
    <div class="form-section-title">
        <i class="fas fa-history text-gray-600"></i> Histori Laporan (20 Terbaru)
    </div>

    <?php if (empty($histori)): ?>
        <div class="text-center py-10 text-gray-400">
            <i class="fas fa-inbox text-4xl mb-3 block"></i>
            <p class="text-sm">Belum ada laporan aktivitas</p>
        </div>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-left">
                    <th class="px-3 py-3 font-medium text-gray-600 whitespace-nowrap">Tanggal</th>
                    <th class="px-3 py-3 font-medium text-gray-600">Kegiatan</th>
                    <th class="px-3 py-3 font-medium text-gray-600 text-right">Harian</th>
                    <th class="px-3 py-3 font-medium text-gray-600 text-right">Kumulatif</th>
                    <th class="px-3 py-3 font-medium text-gray-600">Wilayah</th>
                    <th class="px-3 py-3 font-medium text-gray-600">Lokasi GPS</th>
                    <th class="px-3 py-3 font-medium text-gray-600">Foto</th>
                    <th class="px-3 py-3 font-medium text-gray-600">Catatan</th>
                    <th class="px-3 py-3 font-medium text-gray-600 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($histori as $h): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-3 py-3 text-gray-700 whitespace-nowrap">
                        <?= date('d/m/Y', strtotime($h['created_at'])) ?><br>
                        <span class="text-xs text-gray-400"><?= date('H:i', strtotime($h['created_at'])) ?></span>
                    </td>
                    <td class="px-3 py-3 text-gray-700 max-w-xs">
                        <div class="font-medium text-xs"><?= esc($h['nama_kegiatan'] ?? '-') ?></div>
                        <div class="text-gray-400 text-xs"><?= esc($h['nama_kegiatan_detail_proses'] ?? '-') ?></div>
                    </td>
                    <td class="px-3 py-3 text-right font-semibold text-green-600">
                        <?= number_format($h['jumlah_realisasi_absolut'] ?? 0) ?>
                    </td>
                    <td class="px-3 py-3 text-right font-semibold text-blue-600">
                        <?= number_format($h['jumlah_realisasi_kumulatif'] ?? 0) ?>
                    </td>
                    <td class="px-3 py-3">
                        <?php if (!empty($h['nama_kecamatan'])): ?>
                            <span class="badge-wilayah">
                                <i class="fas fa-map-pin mr-1"></i><?= esc($h['nama_kecamatan']) ?>
                            </span><br>
                        <?php endif; ?>
                        <?php if (!empty($h['nama_desa'])): ?>
                            <span class="badge-wilayah mt-1 inline-block"><?= esc($h['nama_desa']) ?></span><br>
                        <?php endif; ?>
                        <?php if (!empty($h['nama_sls'])): ?>
                            <span class="badge-wilayah mt-1 inline-block"><?= esc($h['nama_sls']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($h['nama_sub_sls'])): ?>
                            <span class="badge-wilayah mt-1 inline-block"><?= esc($h['nama_sub_sls']) ?></span>
                        <?php endif; ?>
                        <?php if (empty($h['nama_kecamatan']) && empty($h['nama_desa'])): ?>
                            <span class="text-gray-300 text-xs">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-3 py-3 whitespace-nowrap">
                        <?php if (!empty($h['latitude']) && !empty($h['longitude'])): ?>
                            <a href="https://maps.google.com/?q=<?= $h['latitude'] ?>,<?= $h['longitude'] ?>"
                               target="_blank"
                               class="inline-flex items-center gap-1 text-xs text-green-600 hover:underline">
                                <i class="fas fa-location-arrow"></i>
                                <?= number_format((float)$h['latitude'], 5) ?>,
                                <?= number_format((float)$h['longitude'], 5) ?>
                            </a>
                        <?php else: ?>
                            <span class="text-gray-300 text-xs">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-3 py-3">
                        <?php if (!empty($h['foto_aktivitas'])): ?>
                            <?php
                                $fotoUrl = base_url('petugas/lapor-aktivitas/foto?path=' . urlencode($h['foto_aktivitas']));
                            ?>
                            <img src="<?= $fotoUrl ?>" alt="Foto Aktivitas"
                                 class="histori-img"
                                 onclick="openImgModal('<?= $fotoUrl ?>')"
                                 onerror="this.src=''; this.style.display='none'; this.nextElementSibling.style.display='block';">
                            <span style="display:none; font-size:0.7rem; color:#9ca3af;">Foto error</span>
                        <?php else: ?>
                            <span class="text-gray-300 text-xs">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-3 py-3 text-gray-500 max-w-xs text-xs">
                        <?= esc(mb_strimwidth($h['catatan_aktivitas'] ?? '-', 0, 60, '…')) ?>
                    </td>
                    <td class="px-3 py-3 text-center whitespace-nowrap">
                        <div class="flex items-center justify-center gap-1.5">
                            <button type="button" onclick="showRowDetail(<?= esc(json_encode($h)) ?>)"
                                class="text-blue-600 hover:text-blue-800 text-xs px-2.5 py-1 rounded
                                       border border-blue-200 hover:bg-blue-50 transition-colors"
                                title="Lihat Detail">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button type="button" onclick="confirmDelete(<?= $h['id_pantau_progess'] ?>)"
                                class="text-red-500 hover:text-red-700 text-xs px-2 py-1 rounded
                                       border border-red-200 hover:bg-red-50 transition-colors"
                                title="Hapus Laporan">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                        <form id="deleteForm-<?= $h['id_pantau_progess'] ?>"
                              action="<?= base_url('petugas/lapor-aktivitas/' . $h['id_pantau_progess']) ?>"
                              method="post" class="hidden">
                            <?= csrf_field() ?>
                            <input type="hidden" name="_method" value="DELETE">
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- ════════════════ CAMERA MODAL ════════════════ -->
<div id="cameraModal">
    <div class="flex flex-col items-center gap-4 p-4 w-full max-w-lg">
        <div class="w-full flex items-center justify-between mb-1">
            <h3 class="text-white font-semibold text-lg">Kamera</h3>
            <button id="btnCloseCamera" class="text-white hover:text-red-400 text-xl">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <video id="videoStream" autoplay playsinline></video>
        <canvas id="captureCanvas"></canvas>
        <div class="flex gap-3">
            <button id="btnCapture"
                class="flex items-center gap-2 px-6 py-3 bg-white text-gray-900
                       font-semibold rounded-xl hover:bg-gray-100 transition-colors text-sm">
                <i class="fas fa-circle text-red-500 text-lg"></i> Ambil Foto
            </button>
            <button id="btnFlip"
                class="flex items-center gap-2 px-4 py-3 bg-gray-700 text-white
                       rounded-xl hover:bg-gray-600 transition-colors text-sm">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
    </div>
</div>

<!-- ════════════════ IMAGE ZOOM MODAL ════════════════ -->
<div id="imgModal" onclick="closeImgModal()"
     class="hidden fixed inset-0 bg-black/80 z-[9998] flex items-center justify-center cursor-zoom-out p-4">
    <img id="imgModalSrc" src="" alt="Preview" class="max-w-full max-h-full rounded-xl shadow-2xl">
</div>

<!-- ════════════════ DETAIL MODAL ════════════════ -->
<div id="detailModal" class="hidden fixed inset-0 bg-black/50 z-[9997] flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
        <!-- Header -->
        <div class="px-6 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 text-white flex justify-between items-center">
            <h3 class="font-bold text-lg"><i class="fas fa-info-circle mr-2"></i>Detail Laporan Aktivitas</h3>
            <button onclick="closeDetailModal()" class="text-white/80 hover:text-white text-xl">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <!-- Body -->
        <div class="p-6 overflow-y-auto space-y-4 flex-1">
            <!-- Kegiatan & Waktu -->
            <div class="bg-blue-50/50 rounded-xl p-4 border border-blue-100">
                <div class="text-xs text-blue-600 font-semibold tracking-wide uppercase mb-1">Kegiatan</div>
                <h4 id="detailKegiatan" class="font-bold text-gray-900 text-sm"></h4>
                <p id="detailKegiatanProses" class="text-xs text-gray-500 mt-1"></p>
                <div class="mt-3 flex items-center gap-2 text-xs text-gray-500">
                    <i class="far fa-clock"></i>
                    <span id="detailTanggal"></span>
                </div>
            </div>

            <!-- Realisasi -->
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-green-50/50 rounded-xl p-4 border border-green-100 text-center">
                    <div class="text-xs text-green-600 font-medium mb-1">Realisasi Harian</div>
                    <span id="detailRealisasiHarian" class="text-2xl font-bold text-green-700"></span>
                </div>
                <div class="bg-blue-50/50 rounded-xl p-4 border border-blue-100 text-center">
                    <div class="text-xs text-blue-600 font-medium mb-1">Realisasi Kumulatif</div>
                    <span id="detailRealisasiKumulatif" class="text-2xl font-bold text-blue-700"></span>
                </div>
            </div>

            <!-- Wilayah -->
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                <div class="text-xs text-gray-500 font-semibold uppercase mb-2"><i class="fas fa-map mr-1 text-orange-500"></i>Wilayah Kunjungan</div>
                <div class="grid grid-cols-2 gap-y-2 gap-x-4 text-xs">
                    <div>
                        <span class="text-gray-400 block">Kecamatan</span>
                        <strong id="detailKecamatan" class="text-gray-800"></strong>
                    </div>
                    <div>
                        <span class="text-gray-400 block">Kelurahan / Desa</span>
                        <strong id="detailDesa" class="text-gray-800"></strong>
                    </div>
                    <div class="mt-2">
                        <span class="text-gray-400 block">SLS</span>
                        <strong id="detailSLS" class="text-gray-800"></strong>
                    </div>
                    <div class="mt-2">
                        <span class="text-gray-400 block">Sub SLS</span>
                        <strong id="detailSubSLS" class="text-gray-800"></strong>
                    </div>
                </div>
            </div>

            <!-- GPS & Foto -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- GPS -->
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 flex flex-col justify-between">
                    <div>
                        <div class="text-xs text-gray-500 font-semibold uppercase mb-2"><i class="fas fa-map-marker-alt mr-1 text-red-500"></i>Lokasi GPS</div>
                        <div class="text-xs space-y-1">
                            <div class="flex justify-between"><span class="text-gray-400">Lat:</span> <span id="detailLat" class="font-medium text-gray-700"></span></div>
                            <div class="flex justify-between"><span class="text-gray-400">Lng:</span> <span id="detailLng" class="font-medium text-gray-700"></span></div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a id="detailGpsLink" href="#" target="_blank" class="w-full text-center block text-xs bg-white border border-gray-200 text-blue-600 py-1.5 rounded-lg font-medium hover:bg-gray-50 transition-all">
                            <i class="fas fa-external-link-alt mr-1"></i>Lihat di Peta
                        </a>
                    </div>
                </div>
                <!-- Foto -->
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 flex flex-col items-start justify-center min-h-[120px]">
                    <div class="text-xs text-gray-500 font-semibold uppercase mb-2 w-full text-left"><i class="fas fa-camera mr-1 text-purple-500"></i>Foto Aktivitas</div>
                    <div id="detailFotoContainer" class="w-full flex justify-center">
                        <img id="detailFoto" src="" alt="Foto Laporan" class="max-h-[100px] object-cover rounded-lg shadow-sm border border-gray-200 cursor-pointer" onclick="openDetailImgZoom()">
                        <span id="detailNoFoto" class="text-gray-400 text-xs italic">- Tidak ada foto -</span>
                    </div>
                </div>
            </div>

            <!-- Catatan -->
            <div class="bg-yellow-50/50 rounded-xl p-4 border border-yellow-100">
                <div class="text-xs text-yellow-700 font-semibold uppercase mb-1"><i class="far fa-comment-alt mr-1"></i>Catatan Aktivitas</div>
                <p id="detailCatatan" class="text-xs text-gray-700 leading-relaxed italic"></p>
            </div>
        </div>
        <!-- Footer -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
            <button onclick="closeDetailModal()" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg text-sm font-semibold transition-all">
                Tutup
            </button>
        </div>
    </div>
</div>

<!-- ════════════════ SCRIPTS ════════════════ -->
<script>
const CSRF_TOKEN = '<?= csrf_hash() ?>';
const CSRF_NAME  = '<?= csrf_token() ?>';

// ─────────────── FOTO: Upload Galeri ───────────────
document.getElementById('btnUpload').addEventListener('click', () => {
    document.getElementById('fotoInput').click();
});

document.getElementById('fotoInput').addEventListener('change', function () {
    if (this.files && this.files[0]) {
        showPreview(URL.createObjectURL(this.files[0]));
    }
});

document.getElementById('removeFoto').addEventListener('click', () => {
    document.getElementById('fotoInput').value = '';
    document.getElementById('fotoPreviewWrapper').style.display = 'none';
    document.getElementById('fotoPreview').src = '';
});

function showPreview(src) {
    document.getElementById('fotoPreview').src = src;
    document.getElementById('fotoPreviewWrapper').style.display = 'block';
}

// ─────────────── FOTO: Kamera ───────────────
let videoStream   = null;
let facingMode    = 'environment'; // mulai dengan kamera belakang

document.getElementById('btnKamera').addEventListener('click', openCamera);
document.getElementById('btnCloseCamera').addEventListener('click', closeCamera);
document.getElementById('btnFlip').addEventListener('click', flipCamera);
document.getElementById('btnCapture').addEventListener('click', capturePhoto);

async function openCamera() {
    document.getElementById('cameraModal').classList.add('active');
    await startStream();
}

async function startStream() {
    if (videoStream) {
        videoStream.getTracks().forEach(t => t.stop());
    }
    try {
        videoStream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: facingMode, width: { ideal: 1280 }, height: { ideal: 720 } }
        });
        document.getElementById('videoStream').srcObject = videoStream;
    } catch (err) {
        alert('Kamera tidak dapat diakses: ' + err.message);
        closeCamera();
    }
}

function closeCamera() {
    if (videoStream) videoStream.getTracks().forEach(t => t.stop());
    videoStream = null;
    document.getElementById('cameraModal').classList.remove('active');
    document.getElementById('videoStream').srcObject = null;
}

async function flipCamera() {
    facingMode = facingMode === 'environment' ? 'user' : 'environment';
    await startStream();
}

function capturePhoto() {
    const video  = document.getElementById('videoStream');
    const canvas = document.getElementById('captureCanvas');
    canvas.width  = video.videoWidth;
    canvas.height = video.videoHeight;
    canvas.getContext('2d').drawImage(video, 0, 0);

    canvas.toBlob(blob => {
        const file = new File([blob], 'kamera_' + Date.now() + '.jpg', { type: 'image/jpeg' });
        const dt   = new DataTransfer();
        dt.items.add(file);
        document.getElementById('fotoInput').files = dt.files;
        showPreview(URL.createObjectURL(blob));
        closeCamera();
    }, 'image/jpeg', 0.90);
}

// ─────────────── GPS ───────────────
document.getElementById('btnGPS').addEventListener('click', getLocation);

function setGPSStatus(state, text) {
    const dot  = document.querySelector('#gpsStatus .gps-status-dot');
    const span = document.getElementById('gpsStatusText');
    dot.className = 'gps-status-dot ' + state;
    span.textContent = text;
}

function getLocation() {
    if (!navigator.geolocation) {
        setGPSStatus('gps-error', 'Browser tidak mendukung GPS');
        return;
    }
    setGPSStatus('gps-waiting', 'Mengambil lokasi…');
    document.getElementById('btnGPS').disabled = true;

    navigator.geolocation.getCurrentPosition(
        pos => {
            const lat = pos.coords.latitude.toFixed(7);
            const lng = pos.coords.longitude.toFixed(7);
            document.getElementById('latInput').value = lat;
            document.getElementById('lngInput').value = lng;
            setGPSStatus('gps-found', 'Lokasi berhasil diambil ✓');
            document.getElementById('mapsLink').classList.remove('hidden');
            document.getElementById('mapsAnchor').href =
                'https://maps.google.com/?q=' + lat + ',' + lng;
            document.getElementById('btnGPS').disabled = false;
        },
        err => {
            let msg = 'Gagal ambil lokasi';
            if (err.code === 1) msg = 'Izin lokasi ditolak';
            else if (err.code === 2) msg = 'Lokasi tidak tersedia';
            else if (err.code === 3) msg = 'Timeout, coba lagi';
            setGPSStatus('gps-error', msg);
            document.getElementById('btnGPS').disabled = false;
        },
        { enableHighAccuracy: true, timeout: 12000, maximumAge: 0 }
    );
}

// ─────────────── CASCADING WILAYAH ───────────────
const selKecamatan = document.getElementById('selKecamatan');
const selDesa      = document.getElementById('selDesa');
const selSLS       = document.getElementById('selSLS');
const selSubSLS    = document.getElementById('selSubSLS');
const BASE         = '<?= base_url('petugas/lapor-aktivitas') ?>';

function resetSelect(sel, placeholder) {
    sel.innerHTML = '<option value="">' + placeholder + '</option>';
    sel.disabled  = true;
}

selKecamatan.addEventListener('change', async function () {
    resetSelect(selDesa, '-- Pilih Kelurahan --');
    resetSelect(selSLS, '-- Pilih SLS --');
    resetSelect(selSubSLS, '-- Pilih Sub SLS --');
    if (!this.value) return;

    try {
        const res  = await fetch(BASE + '/get-desa/' + this.value);
        const json = await res.json();
        if (json.success && json.data.length) {
            selDesa.innerHTML = '<option value="">-- Pilih Kelurahan --</option>';
            json.data.forEach(d => {
                selDesa.innerHTML +=
                    `<option value="${d.id_desa}">${d.nama_desa}</option>`;
            });
            selDesa.disabled = false;
        }
    } catch (e) { console.error('Gagal ambil desa:', e); }
});

selDesa.addEventListener('change', async function () {
    resetSelect(selSLS, '-- Pilih SLS --');
    resetSelect(selSubSLS, '-- Pilih Sub SLS --');
    if (!this.value) return;

    try {
        const res  = await fetch(BASE + '/get-sls/' + this.value);
        const json = await res.json();
        if (json.success && json.data.length) {
            selSLS.innerHTML = '<option value="">-- Pilih SLS --</option>';
            json.data.forEach(s => {
                selSLS.innerHTML +=
                    `<option value="${s.id_sls}">${s.nama_sls}</option>`;
            });
            selSLS.disabled = false;
        }
    } catch (e) { console.error('Gagal ambil SLS:', e); }
});

selSLS.addEventListener('change', async function () {
    resetSelect(selSubSLS, '-- Pilih Sub SLS --');
    if (!this.value) return;

    try {
        const res  = await fetch(BASE + '/get-sub-sls/' + this.value);
        const json = await res.json();
        if (json.success && json.data.length) {
            selSubSLS.innerHTML = '<option value="">-- Pilih Sub SLS --</option>';
            json.data.forEach(ss => {
                selSubSLS.innerHTML +=
                    `<option value="${ss.id_sub_sls}">${ss.nama_sls}</option>`;
            });
            selSubSLS.disabled = false;
        }
    } catch (e) { console.error('Gagal ambil Sub SLS:', e); }
});

// ─────────────── DELETE ───────────────
function confirmDelete(id) {
    if (confirm('Yakin ingin menghapus laporan ini?')) {
        document.getElementById('deleteForm-' + id).submit();
    }
}

// ─────────────── IMAGE ZOOM MODAL ───────────────
function openImgModal(src) {
    document.getElementById('imgModalSrc').src = src;
    document.getElementById('imgModal').classList.remove('hidden');
}
function closeImgModal() {
    document.getElementById('imgModal').classList.add('hidden');
}

// ─────────────── DETAIL MODAL JS ───────────────
function showRowDetail(data) {
    document.getElementById('detailKegiatan').textContent = data.nama_kegiatan || '-';
    document.getElementById('detailKegiatanProses').textContent = data.nama_kegiatan_detail_proses || '-';
    
    // Format tanggal
    if (data.created_at) {
        const dt = new Date(data.created_at);
        const options = { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' };
        document.getElementById('detailTanggal').textContent = dt.toLocaleDateString('id-ID', options).replace(',', '');
    } else {
        document.getElementById('detailTanggal').textContent = '-';
    }

    document.getElementById('detailRealisasiHarian').textContent = Number(data.jumlah_realisasi_absolut || 0).toLocaleString('id-ID');
    document.getElementById('detailRealisasiKumulatif').textContent = Number(data.jumlah_realisasi_kumulatif || 0).toLocaleString('id-ID');
    
    document.getElementById('detailKecamatan').textContent = data.nama_kecamatan || '-';
    document.getElementById('detailDesa').textContent = data.nama_desa || '-';
    document.getElementById('detailSLS').textContent = data.nama_sls || '-';
    document.getElementById('detailSubSLS').textContent = data.nama_sub_sls || '-';

    document.getElementById('detailLat').textContent = data.latitude || '-';
    document.getElementById('detailLng').textContent = data.longitude || '-';
    
    const gpsLink = document.getElementById('detailGpsLink');
    if (data.latitude && data.longitude) {
        gpsLink.href = `https://maps.google.com/?q=${data.latitude},${data.longitude}`;
        gpsLink.style.display = 'block';
    } else {
        gpsLink.style.display = 'none';
    }

    // Foto
    const fotoImg = document.getElementById('detailFoto');
    const noFotoSpan = document.getElementById('detailNoFoto');
    if (data.foto_aktivitas) {
        const url = `<?= base_url('petugas/lapor-aktivitas/foto?path=') ?>${encodeURIComponent(data.foto_aktivitas)}`;
        fotoImg.src = url;
        fotoImg.style.display = 'block';
        noFotoSpan.style.display = 'none';
    } else {
        fotoImg.src = '';
        fotoImg.style.display = 'none';
        noFotoSpan.style.display = 'block';
    }

    document.getElementById('detailCatatan').textContent = data.catatan_aktivitas || 'Tidak ada kendala';

    document.getElementById('detailModal').classList.remove('hidden');
}

function closeDetailModal() {
    document.getElementById('detailModal').classList.add('hidden');
}

function openDetailImgZoom() {
    const src = document.getElementById('detailFoto').src;
    if (src) {
        openImgModal(src);
    }
}

// ─────────────── FORM SUBMIT PROTECTION ───────────────
document.getElementById('formLaporan').addEventListener('submit', function (e) {
    const selPcl = document.getElementById('id_pcl');
    const selectedOption = selPcl.options[selPcl.selectedIndex];
    
    if (selectedOption && selectedOption.value) {
        const target = parseInt(selectedOption.getAttribute('data-target') || 0);
        const kumulatif = parseInt(selectedOption.getAttribute('data-kumulatif') || 0);
        const jumlahBaru = parseInt(document.getElementById('jumlah').value || 0);
        const totalBaru = kumulatif + jumlahBaru;
        
        if (target > 0 && totalBaru > target) {
            const proceed = confirm(`Warning: realisasi komulatif (${totalBaru}) melebihi target (${target}). Apakah Anda yakin ingin mengirim laporan ini?`);
            if (!proceed) {
                e.preventDefault();
                return false;
            }
        }
    }

    const btn = document.getElementById('btnSubmit');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Mengirim…';
});
</script>

<?= $this->endSection() ?>
