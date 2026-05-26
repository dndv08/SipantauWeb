<?= $this->extend('layouts/adminkab_layout') ?>

<?= $this->section('content') ?>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Hasil Assignment ke Sub-SLS</h1>
    <p class="text-sm text-gray-600">Lihat ringkasan penugasan petugas pada level Sub-SLS.</p>
</div>

<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-6">
    <div class="p-4 border-b border-gray-200 bg-gray-50">
        <form action="<?= base_url('adminsurvei-kab/view-assign-sub-sls') ?>" method="get" class="flex flex-wrap items-end gap-4">
            <div class="w-full md:w-64">
                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Pilih Kegiatan</label>
                <select name="kegiatan" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                    <option value="">Semua Kegiatan</option>
                    <?php foreach ($kegiatanList as $k) : ?>
                        <option value="<?= $k['id_kegiatan_wilayah'] ?>" <?= $selectedKegiatan == $k['id_kegiatan_wilayah'] ? 'selected' : '' ?>>
                            <?= esc($k['nama_kegiatan_detail']) ?> - <?= esc($k['nama_kegiatan_detail_proses']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="w-full md:w-48">
                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Status Penugasan</label>
                <select name="status" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                    <option value="">Semua Status</option>
                    <option value="assigned" <?= $selectedStatus == 'assigned' ? 'selected' : '' ?>>Sudah di-Assign</option>
                    <option value="unassigned" <?= $selectedStatus == 'unassigned' ? 'selected' : '' ?>>Belum di-Assign</option>
                </select>
            </div>
            <div>
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded shadow-sm transition-colors">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-r">No</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-r">Kegiatan</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-r">Wilayah</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-r">Sub-SLS ID</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-r">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-r">Nama Petugas</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Sobat ID</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php if (empty($assignments)) : ?>
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-gray-500 italic">Data tidak ditemukan</td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($assignments as $i => $row) : ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-900 border-r"><?= $i + 1 ?></td>
                            <td class="px-4 py-3 text-sm text-gray-900 border-r">
                                <?php if ($row['id_assignment_sub_sls']) : ?>
                                    <div class="font-medium text-blue-700"><?= esc($row['nama_kegiatan_detail']) ?></div>
                                    <div class="text-[10px] text-gray-500"><?= esc($row['nama_kegiatan_detail_proses']) ?></div>
                                <?php else : ?>
                                    <span class="text-gray-400">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900 border-r">
                                <div class="font-semibold"><?= esc($row['nama_kecamatan']) ?></div>
                                <div class="text-xs text-gray-500"><?= esc($row['real_nama_desa'] ?: $row['mss_nama_desa']) ?></div>
                                <div class="text-xs text-gray-400">SLS: <?= esc($row['nama_sls']) ?></div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900 border-r">
                                <span class="font-mono font-bold text-blue-600"><?= substr($row['id_sub_sls'], -2) ?></span>
                                <div class="text-[10px] text-gray-400"><?= esc($row['id_sub_sls']) ?></div>
                            </td>
                            <td class="px-4 py-3 text-sm border-r">
                                <?php if ($row['id_assignment_sub_sls']) : ?>
                                    <span class="px-2 py-1 bg-green-100 text-green-800 text-[10px] font-bold rounded">ASSIGNED</span>
                                <?php else : ?>
                                    <span class="px-2 py-1 bg-red-100 text-red-800 text-[10px] font-bold rounded">UNASSIGNED</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900 border-r">
                                <?= esc($row['nama_petugas'] ?: '-') ?>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900">
                                <?= esc($row['sobat_id'] ?: '-') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>
