<?= $this->extend('layouts/petugas_layout') ?>
<?= $this->section('content') ?>

<style>
/* ── PML Industry Tag Styles ── */
.pml-badge-item {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #f3e8ff;
    color: #6b21a8;
    border: 1px solid #d8b4fe;
    font-size: 0.85rem;
    font-weight: 500;
    padding: 4px 10px;
    border-radius: 9999px;
    transition: all 0.2s ease;
    animation: scaleUp 0.15s ease-out;
}
.pml-badge-item:hover {
    background: #ebd5ff;
}
.pml-badge-delete {
    color: #a855f7;
    cursor: pointer;
    font-weight: 700;
}
.pml-badge-delete:hover {
    color: #7e22ce;
}
@keyframes scaleUp {
    from { transform: scale(0.9); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}

/* ── Histori Badge ── */
.badge-role {
    font-size: 0.7rem;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 999px;
    text-transform: uppercase;
}
.badge-role-pcl {
    background: #eff6ff;
    color: #1d4ed8;
}
.badge-role-pml {
    background: #faf5ff;
    color: #7e22ce;
}
</style>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Lapor Industri Digital SE2026</h1>
    <p class="text-sm text-gray-500 mt-1">Laporkan data industri digital dan pantau progress anggota PCL Anda</p>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm flex items-center gap-2">
        <i class="fas fa-check-circle"></i>
        <?= session()->getFlashdata('success') ?>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm flex items-center gap-2">
        <i class="fas fa-exclamation-circle"></i>
        <?= session()->getFlashdata('error') ?>
    </div>
<?php endif; ?>

<!-- Form Lapor Industri -->
<div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
    <h3 class="text-sm font-semibold text-gray-900 mb-4"><i class="fas fa-industry text-blue-600 mr-2"></i>Form Pelaporan</h3>
    
    <form action="<?= base_url('petugas/lapor-industri/store') ?>" method="post" id="formLaporIndustri">
        <?= csrf_field() ?>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <!-- Dropdown Kegiatan -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Kegiatan <span class="text-red-500">*</span></label>
                <select name="id_pcl" id="selKegiatan" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">-- Pilih Kegiatan --</option>
                    <?php foreach ($kegiatanListCombined as $k): ?>
                        <option value="<?= $k['id_combined'] ?>"><?= esc($k['nama_kegiatan_detail_proses']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- ════════════════ PCL SPECIFIC FIELDS ════════════════ -->
        <div id="pclFields" class="hidden grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Kunjungan</label>
                <input type="date" name="tanggal" value="<?= date('Y-m-d') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Usaha / Industri <span class="text-red-500">*</span></label>
                <input type="text" name="nama_usaha" id="namaUsahaPCL" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Contoh: Toko Maju Jaya">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Usaha <span class="text-red-500">*</span></label>
                <select name="jenis_usaha" id="jenisUsahaPCL" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">-- Pilih --</option>
                    <option value="Industri Besar">Industri Besar</option>
                    <option value="Industri Sedang">Industri Sedang</option>
                    <option value="Industri Kecil">Industri Kecil</option>
                    <option value="Industri Mikro">Industri Mikro</option>
                    <option value="Perdagangan">Perdagangan</option>
                    <option value="Jasa">Jasa</option>
                    <option value="Lainnya">Lainnya</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Entri</label>
                <input type="number" name="jumlah" min="1" value="1" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Usaha</label>
                <input type="text" name="alamat" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Alamat lengkap usaha">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan Tambahan</label>
                <input type="text" name="keterangan" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Opsional">
            </div>
        </div>

        <!-- ════════════════ PML SPECIFIC FIELDS ════════════════ -->
        <div id="pmlFields" class="hidden space-y-4">
            <!-- Multiple Industry Name Input -->
            <div class="bg-purple-50/50 rounded-xl p-5 border border-purple-100">
                <label class="block text-sm font-semibold text-purple-950 mb-2">Tuliskan Nama-nama Industri Digital</label>
                <p class="text-xs text-purple-700 mb-3">Tuliskan nama industri digital satu per satu, kemudian klik tombol "Tambah". Anda bisa melaporkan lebih dari 1 nama usaha.</p>
                <div class="flex gap-2">
                    <input type="text" id="pmlInputNama" class="flex-1 border border-gray-300 rounded-lg px-3.5 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500" placeholder="Contoh: Shopee Express Benai">
                    <button type="button" id="pmlBtnAdd" class="bg-purple-600 hover:bg-purple-700 text-white px-5 py-2 rounded-lg text-sm font-semibold transition-colors flex items-center gap-1.5">
                        <i class="fas fa-plus"></i> Tambah
                    </button>
                </div>
                <!-- Dynamic List Badges -->
                <div id="pmlNamaList" class="flex flex-wrap gap-2.5 mt-3.5"></div>
                <!-- Hidden inputs container for form post -->
                <div id="pmlHiddenInputs"></div>
            </div>

            <!-- Dynamic Mismatch Warning Notice -->
            <div id="mismatchWarning" class="hidden p-4 bg-amber-50 border border-amber-200 text-amber-900 rounded-xl text-sm flex items-start gap-2.5 shadow-sm">
                <i class="fas fa-exclamation-triangle text-amber-600 mt-0.5 text-base"></i>
                <div>
                    <h4 class="font-bold text-amber-950">Peringatan: Selisih Jumlah Laporan</h4>
                    <p id="warningText" class="text-xs text-amber-800 mt-0.5"></p>
                </div>
            </div>

            <!-- PCL Supervision Recap Section -->
            <div class="bg-gray-50 rounded-xl p-5 border border-gray-200 mt-2">
                <h4 class="text-sm font-semibold text-gray-900 mb-3 flex items-center gap-2">
                    <i class="fas fa-users text-gray-600"></i> Rekap Laporan Industri Anggota PCL
                </h4>
                <div id="pclRecapLoading" class="text-xs text-gray-500 italic py-2 hidden">
                    <i class="fas fa-spinner fa-spin mr-1"></i> Memuat data rekap PCL...
                </div>
                <div id="pclRecapEmpty" class="text-xs text-gray-400 italic py-4 text-center hidden">
                    <i class="fas fa-folder-open text-2xl mb-1.5 block"></i> Belum ada PCL anggota yang melaporkan industri digital pada kegiatan ini.
                </div>
                <div id="pclRecapContent" class="space-y-3"></div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="mt-6 pt-4 border-t border-gray-150 flex justify-end">
            <button type="submit" id="btnSubmit" class="bg-blue-600 text-white px-6 py-2.5 rounded-lg text-sm font-semibold hover:bg-blue-700 transition-colors shadow-sm">
                <i class="fas fa-paper-plane mr-2"></i>Kirim Laporan
            </button>
        </div>
    </form>
</div>

<!-- Histori -->
<div class="bg-white rounded-xl border border-gray-200 p-6">
    <h3 class="text-sm font-semibold text-gray-900 mb-4"><i class="fas fa-history text-gray-600 mr-2"></i>Histori Laporan Industri</h3>
    <?php if (empty($histori)): ?>
        <div class="text-center py-10 text-gray-400">
            <i class="fas fa-inbox text-4xl mb-3 block"></i>
            <p class="text-sm">Belum ada laporan industri</p>
        </div>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-left">
                    <th class="px-4 py-3 font-medium text-gray-600 whitespace-nowrap">Tanggal</th>
                    <th class="px-4 py-3 font-medium text-gray-600">Kegiatan</th>
                    <th class="px-4 py-3 font-medium text-gray-600">Petugas</th>
                    <th class="px-4 py-3 font-medium text-gray-600">Resume Laporan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($histori as $h): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-gray-700 whitespace-nowrap">
                        <?= date('d/m/Y', strtotime($h['created_at'])) ?><br>
                        <span class="text-xs text-gray-400"><?= date('H:i', strtotime($h['created_at'])) ?></span>
                    </td>
                    <td class="px-4 py-3 text-gray-700 text-xs">
                        <?= esc($h['nama_kegiatan_detail_proses'] ?? '-') ?>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <?php if (!empty($h['id_pml'])): ?>
                            <span class="badge-role badge-role-pml">PML</span>
                        <?php else: ?>
                            <span class="badge-role badge-role-pcl">PCL</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-gray-600 text-xs font-mono">
                        <?= esc($h['resume'] ?? '-') ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- JS Implementation -->
<script>
let pmlIndustries = [];
let pclTotalCount = 0;

const selKegiatan       = document.getElementById('selKegiatan');
const pclFields         = document.getElementById('pclFields');
const pmlFields         = document.getElementById('pmlFields');
const btnSubmit         = document.getElementById('btnSubmit');

const namaUsahaPCL      = document.getElementById('namaUsahaPCL');
const jenisUsahaPCL     = document.getElementById('jenisUsahaPCL');

const pmlInputNama      = document.getElementById('pmlInputNama');
const pmlBtnAdd         = document.getElementById('pmlBtnAdd');
const pmlNamaList       = document.getElementById('pmlNamaList');
const pmlHiddenInputs   = document.getElementById('pmlHiddenInputs');

const pclRecapLoading   = document.getElementById('pclRecapLoading');
const pclRecapEmpty     = document.getElementById('pclRecapEmpty');
const pclRecapContent   = document.getElementById('pclRecapContent');

const mismatchWarning   = document.getElementById('mismatchWarning');
const warningText       = document.getElementById('warningText');

// ─────────────── Handle Dynamic Inputs ───────────────

function handleKegiatanChange() {
    const val = selKegiatan.value;
    
    // Reset PML specific
    pmlIndustries = [];
    renderPMLList();
    pclTotalCount = 0;
    pclRecapContent.innerHTML = '';
    mismatchWarning.classList.add('hidden');
    
    if (!val) {
        pclFields.classList.add('hidden');
        pmlFields.classList.add('hidden');
        return;
    }
    
    if (val.startsWith('pcl_')) {
        // PCL Mode
        pclFields.classList.remove('hidden');
        pmlFields.classList.add('hidden');
        
        namaUsahaPCL.required = true;
        jenisUsahaPCL.required = true;
    } else if (val.startsWith('pml_')) {
        // PML Mode
        pclFields.classList.add('hidden');
        pmlFields.classList.remove('hidden');
        
        namaUsahaPCL.required = false;
        jenisUsahaPCL.required = false;
        
        // Load PCL Recap
        const idPML = val.replace('pml_', '');
        loadPclRecap(idPML);
    }
}

selKegiatan.addEventListener('change', handleKegiatanChange);

// ─────────────── PML Multiple Industry List ───────────────

pmlBtnAdd.addEventListener('click', () => {
    const name = pmlInputNama.value.trim();
    if (!name) return;
    
    if (pmlIndustries.includes(name)) {
        alert('Nama industri digital sudah ada di daftar.');
        return;
    }
    
    pmlIndustries.push(name);
    pmlInputNama.value = '';
    pmlInputNama.focus();
    
    renderPMLList();
    checkWarning();
});

pmlInputNama.addEventListener('keypress', (e) => {
    if (e.key === 'Enter') {
        e.preventDefault();
        pmlBtnAdd.click();
    }
});

function removePmlIndustry(idx) {
    pmlIndustries.splice(idx, 1);
    renderPMLList();
    checkWarning();
}

function renderPMLList() {
    pmlNamaList.innerHTML = '';
    pmlHiddenInputs.innerHTML = '';
    
    if (pmlIndustries.length === 0) {
        pmlNamaList.innerHTML = '<span class="text-xs text-gray-400 italic">Belum ada industri digital yang ditambahkan.</span>';
        return;
    }
    
    pmlIndustries.forEach((name, idx) => {
        // Badge element
        const badge = document.createElement('span');
        badge.className = 'pml-badge-item';
        badge.innerHTML = `
            <span>${name}</span>
            <span class="pml-badge-delete" onclick="removePmlIndustry(${idx})">&times;</span>
        `;
        pmlNamaList.appendChild(badge);
        
        // Hidden Input for Form Submission
        const hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = 'pml_nama_industri[]';
        hidden.value = name;
        pmlHiddenInputs.appendChild(hidden);
    });
}

// ─────────────── Load PCL Recap ───────────────

async function loadPclRecap(idPML) {
    pclRecapLoading.classList.remove('hidden');
    pclRecapEmpty.classList.add('hidden');
    pclRecapContent.innerHTML = '';
    
    try {
        const res = await fetch(`<?= base_url('petugas/lapor-industri/get-pcl-recap/') ?>${idPML}`);
        const json = await res.json();
        
        pclRecapLoading.classList.add('hidden');
        
        if (json.success && json.data && json.data.length > 0) {
            pclTotalCount = 0;
            let hasReports = false;
            
            json.data.forEach(pcl => {
                pclTotalCount += pcl.count;
                if (pcl.count > 0) {
                    hasReports = true;
                }
                
                const card = document.createElement('div');
                card.className = 'bg-white rounded-lg p-3 border border-gray-150 shadow-sm flex flex-col gap-1.5';
                
                let reportNames = '-';
                if (pcl.industries && pcl.industries.length > 0) {
                    reportNames = pcl.industries.map(ind => `<span class="bg-gray-100 text-gray-800 text-xs px-2 py-0.5 rounded border border-gray-200 inline-block mt-1 mr-1">${ind.name}</span>`).join(' ');
                }
                
                card.innerHTML = `
                    <div class="flex justify-between items-center">
                        <span class="font-semibold text-xs text-gray-800">${pcl.nama_user}</span>
                        <span class="bg-blue-100 text-blue-700 text-xs font-bold px-2 py-0.5 rounded-full">${pcl.count} Laporan</span>
                    </div>
                    <div class="text-xs text-gray-600">
                        <strong>Daftar Industri:</strong><br>
                        ${reportNames}
                    </div>
                `;
                pclRecapContent.appendChild(card);
            });
            
            if (!hasReports) {
                pclRecapEmpty.classList.remove('hidden');
                pclRecapContent.innerHTML = '';
            }
            
            checkWarning();
        } else {
            pclRecapEmpty.classList.remove('hidden');
        }
    } catch (err) {
        console.error('Error loading PCL recap:', err);
        pclRecapLoading.classList.add('hidden');
        pclRecapEmpty.classList.remove('hidden');
    }
}

// ─────────────── Warning & Validation ───────────────

function checkWarning() {
    const pmlCount = pmlIndustries.length;
    
    if (selKegiatan.value.startsWith('pml_') && pmlCount !== pclTotalCount) {
        mismatchWarning.classList.remove('hidden');
        warningText.textContent = `Jumlah industri digital hasil list PML (${pmlCount} industri) tidak sama dengan list nama industri digital yang dilaporkan oleh PCL anggota Anda (${pclTotalCount} industri).`;
    } else {
        mismatchWarning.classList.add('hidden');
    }
}

// Form Submission check
document.getElementById('formLaporIndustri').addEventListener('submit', function (e) {
    const val = selKegiatan.value;
    
    if (val.startsWith('pml_')) {
        if (pmlIndustries.length === 0) {
            e.preventDefault();
            alert('Silakan masukkan minimal satu nama industri digital sebelum mengirim laporan.');
            return false;
        }
        
        const pmlCount = pmlIndustries.length;
        if (pmlCount !== pclTotalCount) {
            const proceed = confirm(`Warning: Jumlah industri digital hasil list PML (${pmlCount}) tidak sama dengan rekap jumlah dari PCL anggota (${pclTotalCount}). Apakah Anda yakin ingin tetap mengirim laporan ini?`);
            if (!proceed) {
                e.preventDefault();
                return false;
            }
        }
    }
    
    btnSubmit.disabled = true;
    btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Mengirim…';
});
</script>

<?= $this->endSection() ?>
