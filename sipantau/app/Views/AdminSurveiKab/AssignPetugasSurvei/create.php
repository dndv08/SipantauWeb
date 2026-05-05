<?= $this->extend('layouts/adminkab_layout') ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="mb-6">
    <div class="flex items-center text-sm text-gray-600 mb-4">
        <a href="<?= base_url('adminsurvei-kab/assign-petugas') ?>" class="hover:text-blue-600 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Kembali
        </a>
    </div>
    <h1 class="text-2xl font-bold text-gray-900">Assign Petugas Survei</h1>
    <p class="text-gray-600 mt-1">Assign PML dan PCL ke kegiatan survei</p>
</div>

<!-- Flash Messages -->
<?php if (session()->getFlashdata('error')): ?>
    <div class="mb-4 bg-red-50 border border-red-200 rounded-lg p-4">
        <div class="flex items-center">
            <i class="fas fa-exclamation-circle text-red-600 mr-3"></i>
            <p class="text-sm text-red-700"><?= session()->getFlashdata('error') ?></p>
        </div>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('success')): ?>
    <div class="mb-4 bg-green-50 border border-green-200 rounded-lg p-4">
        <div class="flex items-center">
            <i class="fas fa-check-circle text-green-600 mr-3"></i>
            <p class="text-sm text-green-700"><?= session()->getFlashdata('success') ?></p>
        </div>
    </div>
<?php endif; ?>

<!-- Form Card -->
<div class="card max-w-6xl">
    <form action="<?= base_url('adminsurvei-kab/assign-petugas/store') ?>" method="POST" id="assignForm">
        <?= csrf_field() ?>

        <!-- Step 1: Pilih Kegiatan Survei -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                <span
                    class="inline-flex items-center justify-center w-6 h-6 bg-blue-600 text-white rounded-full text-xs mr-2">1</span>
                Pilih Kegiatan Survei <span class="text-red-500">*</span>
            </label>
            <?php if (empty($kegiatanList)): ?>
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-triangle text-yellow-600 mr-3"></i>
                        <p class="text-sm text-yellow-700">Tidak ada kegiatan survei yang tersedia</p>
                    </div>
                </div>
            <?php else: ?>
                <?= view('components/select_component', [
                    'name' => 'kegiatan_survei',
                    'id' => 'kegiatanSurvei',
                    'required' => true,
                    'placeholder' => 'Cari dan pilih kegiatan survei...',
                    'options' => $kegiatanList,
                    'optionValue' => 'id_kegiatan_wilayah',
                    'optionText' => function ($k) {
                                        return $k['nama_kegiatan_detail_proses'] . ' - ' . $k['nama_kegiatan'];
                                    },
                    'onchange' => 'handleKegiatanChange()',
                    'emptyMessage' => 'Tidak ada kegiatan yang tersedia',
                    'enableSearch' => true
                ]) ?>
            <?php endif; ?>
        </div>

        <!-- Step 2: Pilih PML -->
        <div id="pmlSection" class="mb-6" style="display: none;">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                <span
                    class="inline-flex items-center justify-center w-6 h-6 bg-blue-600 text-white rounded-full text-xs mr-2">2</span>
                Pilih PML <span class="text-red-500">*</span>
            </label>
            <div id="pmlSelectContainer">
                <!-- PML select will be loaded here dynamically -->
                <div class="text-center text-gray-500 py-4">
                    <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                    <p class="text-sm">Memuat data PML...</p>
                </div>
            </div>
            <p class="text-sm text-gray-500 mt-2">
                <i class="fas fa-info-circle mr-1"></i>
                PML yang ditampilkan adalah yang belum di-assign ke kegiatan ini
            </p>
        </div>

        <!-- Step 3: Input Target PML -->
        <div id="targetPMLSection" class="mb-6" style="display: none;">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                <span
                    class="inline-flex items-center justify-center w-6 h-6 bg-blue-600 text-white rounded-full text-xs mr-2">3</span>
                Target PML <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <input type="number" name="pml_target" id="pmlTarget" class="input-field pr-16"
                    placeholder="Masukkan target PML..." min="1" oninput="updateSisaTarget()">
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <span class="text-gray-500 text-sm">unit</span>
                </div>
            </div>
            <p id="sisaTargetInfo" class="text-sm text-gray-500 mt-1"></p>
            <p class="text-sm text-blue-600 mt-2" id="sisaTargetPML" style="display: none;">
                <i class="fas fa-chart-line mr-1"></i>
                Sisa target tersedia untuk PCL: <span class="font-bold" id="sisaTargetValue">0</span> unit
            </p>
        </div>

        <!-- Step 4: Tambah PCL -->
        <div id="pclSection" class="mb-6" style="display: none;">
            <div class="flex items-center justify-between mb-3">
                <label class="block text-sm font-medium text-gray-700">
                    <span
                        class="inline-flex items-center justify-center w-6 h-6 bg-blue-600 text-white rounded-full text-xs mr-2">4</span>
                    Daftar PCL (Opsional)
                </label>
                <button type="button" onclick="addPCLRow()" class="btn-primary btn-sm">
                    <i class="fas fa-plus mr-2"></i>
                    Tambah PCL
                </button>
            </div>

            <div id="pclContainer">
                <!-- PCL rows will be added here -->
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-4">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-blue-600 mr-3 mt-0.5"></i>
                    <div class="flex-1">
                        <p class="text-sm text-blue-900 font-medium">Informasi Target</p>
                        <p class="text-xs text-blue-700 mt-1">
                            Total target PCL: <span class="font-bold" id="totalTargetPCL">0</span> unit
                        </p>
                        <p class="text-xs text-blue-700">
                            Sisa target PML: <span class="font-bold" id="sisaTargetPMLForPCL">0</span> unit
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-3 pt-4 border-t border-gray-200">
            <a href="<?= base_url('adminsurvei-kab/assign-petugas') ?>" class="btn-secondary flex-1 text-center">
                <i class="fas fa-times mr-2"></i>
                Batal
            </a>
            <button type="submit" class="btn-primary flex-1" id="submitBtn" disabled>
                <i class="fas fa-save mr-2"></i>
                Simpan Assignment
            </button>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Global variables
    let pclCounter = 0;
    let selectedKegiatan = null;
    let selectedPML = null;
    let targetPML = 0;
    let availablePCL = [];
    let sisaTargetWilayah = 0;
    let targetWilayah = 0;
    let targetTerpakai = 0;
    let csrfName = '<?= csrf_token() ?>';
    let csrfHash = '<?= csrf_hash() ?>';

    // Handle kegiatan change
    function handleKegiatanChange() {
        const kegiatanValue = $('#kegiatanSurvei').val();
        selectedKegiatan = kegiatanValue;

        if (selectedKegiatan) {
            loadSisaTargetWilayah(selectedKegiatan);
        } else {
            resetForm();
        }
    }

    // Load sisa target wilayah
    function loadSisaTargetWilayah(idKegiatanWilayah) {
        $.ajax({
            url: '<?= base_url('adminsurvei-kab/assign-petugas/get-sisa-target-wilayah') ?>',
            method: 'POST',
            data: {
                id_kegiatan_wilayah: idKegiatanWilayah,
                [csrfName]: csrfHash
            },
            success: function (response) {
                csrfHash = response.csrf_hash || csrfHash;

                if (response.success) {
                    targetWilayah = response.target_wilayah;
                    targetTerpakai = response.target_terpakai;
                    sisaTargetWilayah = response.sisa_target;

                    if (sisaTargetWilayah <= 0) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Target Habis',
                            text: 'Target kegiatan wilayah sudah habis. Tidak dapat menambah PML baru.',
                            confirmButtonColor: '#3b82f6'
                        });
                        resetForm();
                    } else {
                        loadAvailablePML(idKegiatanWilayah);
                        document.getElementById('pmlSection').style.display = 'block';
                        document.getElementById('targetPMLSection').style.display = 'none';
                        document.getElementById('pclSection').style.display = 'none';
                        selectedPML = null;
                        targetPML = 0;
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.error || 'Gagal memuat data target wilayah',
                        confirmButtonColor: '#ef4444'
                    });
                    resetForm();
                }
            },
            error: function (xhr, status, error) {
                console.error('Error loading sisa target:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Gagal memuat data target wilayah. Silakan coba lagi.',
                    confirmButtonColor: '#ef4444'
                });
                resetForm();
            }
        });
    }

    // Load available PML
    function loadAvailablePML(idKegiatanWilayah) {
        const container = document.getElementById('pmlSelectContainer');

        container.innerHTML = `
        <div class="text-center text-gray-500 py-4">
            <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
            <p class="text-sm">Memuat data PML...</p>
        </div>
    `;

        $.ajax({
            url: '<?= base_url('adminsurvei-kab/assign-petugas/get-available-pml') ?>',
            method: 'POST',
            data: {
                id_kegiatan_wilayah: idKegiatanWilayah,
                [csrfName]: csrfHash
            },
            success: function (response) {
                csrfHash = response.csrf_hash || csrfHash;

                if (response.success && response.data.length > 0) {
                    const selectHtml = `
                    <select name="pml_sobat_id" id="pmlSelect" class="select2-pml" style="width: 100%" onchange="handlePMLChange()">
                        <option value="">Pilih PML...</option>
                        ${response.data.map(pml =>
                        `<option value="${pml.sobat_id}">${pml.nama_user} - ${pml.email}</option>`
                    ).join('')}
                    </select>
                `;

                    container.innerHTML = selectHtml;

                    $('.select2-pml').select2({
                        placeholder: 'Cari dan pilih PML...',
                        allowClear: true,
                        width: '100%'
                    });
                } else {
                    container.innerHTML = `
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-triangle text-yellow-600 mr-3"></i>
                            <p class="text-sm text-yellow-700">Tidak ada PML yang tersedia untuk kegiatan ini</p>
                        </div>
                    </div>
                `;
                }
            },
            error: function (xhr, status, error) {
                console.error('Error loading PML:', error);
                container.innerHTML = `
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle text-red-600 mr-3"></i>
                        <p class="text-sm text-red-700">Gagal memuat data PML. Silakan coba lagi.</p>
                    </div>
                </div>
            `;
            }
        });
    }

    // Handle PML change
    function handlePMLChange() {
        const pmlSelect = document.getElementById('pmlSelect');
        selectedPML = pmlSelect ? pmlSelect.value : null;

        if (selectedPML) {
            document.getElementById('targetPMLSection').style.display = 'block';

            const targetInput = document.getElementById('pmlTarget');
            targetInput.max = sisaTargetWilayah;
            targetInput.placeholder = `Maksimal sisa target: ${sisaTargetWilayah.toLocaleString()}`;

            updateSisaTargetInfo();
            $('#pmlTarget').focus();
        } else {
            document.getElementById('targetPMLSection').style.display = 'none';
            document.getElementById('pclSection').style.display = 'none';
            targetPML = 0;
            document.getElementById('pclContainer').innerHTML = '';
        }

        updateSubmitButton();
    }

    // Update sisa target info
    function updateSisaTargetInfo() {
        const sisaInfo = document.getElementById('sisaTargetInfo');
        if (sisaInfo) {
            sisaInfo.innerHTML = `
            <span class="inline-flex items-center gap-3 text-xs">
                <span><strong>Target Wilayah:</strong> ${targetWilayah.toLocaleString()}</span>
                <span class="text-gray-300">|</span>
                <span><strong>Terpakai:</strong> ${targetTerpakai.toLocaleString()}</span>
                <span class="text-gray-300">|</span>
                <span class="text-green-600 font-semibold"><strong>Sisa:</strong> ${sisaTargetWilayah.toLocaleString()}</span>
            </span>
        `;
        }
    }

    // Update sisa target
    function updateSisaTarget() {
        const pmlTargetInput = document.getElementById('pmlTarget');
        targetPML = parseInt(pmlTargetInput.value) || 0;

        if (targetPML > sisaTargetWilayah) {
            Swal.fire({
                icon: 'error',
                title: 'Target Melebihi Batas',
                text: `Target PML (${targetPML.toLocaleString()}) melebihi sisa target wilayah (${sisaTargetWilayah.toLocaleString()})`,
                confirmButtonColor: '#ef4444'
            });
            pmlTargetInput.value = sisaTargetWilayah;
            targetPML = sisaTargetWilayah;
            return;
        }

        if (targetPML > 0) {
            document.getElementById('pclSection').style.display = 'block';
            document.getElementById('sisaTargetPML').style.display = 'block';
            document.getElementById('sisaTargetValue').textContent = targetPML;
            loadAvailablePCL(); // Load PCL dengan filter kegiatan dan PML
            updateTargetInfo();
        } else {
            document.getElementById('pclSection').style.display = 'none';
            document.getElementById('sisaTargetPML').style.display = 'none';
            document.getElementById('pclContainer').innerHTML = '';
            pclCounter = 0;
        }

        updateSubmitButton();
    }

    // Load available PCL - FIXED: kirim id_kegiatan_wilayah dan pml_sobat_id
    function loadAvailablePCL() {
        if (!selectedPML || !selectedKegiatan) {
            console.error('selectedPML atau selectedKegiatan belum diisi');
            return;
        }

        $.ajax({
            url: '<?= base_url('adminsurvei-kab/assign-petugas/get-available-pcl') ?>',
            method: 'POST',
            data: {
                id_kegiatan_wilayah: selectedKegiatan, // WAJIB
                pml_sobat_id: selectedPML, // Exclude PML yang baru dipilih
                [csrfName]: csrfHash
            },
            success: function (response) {
                csrfHash = response.csrf_hash || csrfHash;

                if (response.success) {
                    availablePCL = response.data || [];
                    console.log('Available PCL loaded:', availablePCL.length);
                } else {
                    console.error('Error loading PCL:', response.error);
                    availablePCL = [];
                }
            },
            error: function (xhr, status, error) {
                console.error('Error loading PCL:', error);
                availablePCL = [];
            }
        });
    }

    // Add PCL row - UPDATED
    function addPCLRow() {
        if (!selectedKegiatan) {
            Swal.fire({
                icon: 'warning',
                title: 'Pilih Kegiatan Dulu',
                text: 'Silakan pilih kegiatan survei terlebih dahulu',
                confirmButtonColor: '#3b82f6'
            });
            return;
        }

        if (!selectedPML) {
            Swal.fire({
                icon: 'warning',
                title: 'Pilih PML Dulu',
                text: 'Silakan pilih PML terlebih dahulu',
                confirmButtonColor: '#3b82f6'
            });
            return;
        }

        if (availablePCL.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Tidak Ada PCL',
                text: 'Tidak ada PCL yang tersedia untuk kegiatan ini',
                confirmButtonColor: '#3b82f6'
            });
            return;
        }

        const sisaTarget = targetPML - getTotalTargetPCL();
        if (sisaTarget <= 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Target Penuh',
                text: 'Target PML sudah terpenuhi. Tidak dapat menambah PCL lagi.',
                confirmButtonColor: '#3b82f6'
            });
            return;
        }

        pclCounter++;
        const container = document.getElementById('pclContainer');
        const pclNumber = document.querySelectorAll('.pcl-row').length + 1;

        const row = document.createElement('div');
        row.className = 'pcl-row border border-gray-200 rounded-lg p-4 mb-3';
        row.id = `pclRow${pclCounter}`;
        row.dataset.pclNumber = pclNumber;

        // Filter: Exclude PCL yang sudah dipilih
        const selectedPCLIds = [];
        document.querySelectorAll('.pcl-select').forEach(select => {
            if (select.value) selectedPCLIds.push(select.value);
        });

        let optionsHTML = '<option value="">Pilih PCL...</option>';
        availablePCL.forEach(pcl => {
            if (!selectedPCLIds.includes(pcl.sobat_id.toString())) {
                optionsHTML += `<option value="${pcl.sobat_id}">${escapeHtml(pcl.nama_user)} - ${escapeHtml(pcl.email)}</option>`;
            }
        });

        row.innerHTML = `
        <div class="flex items-center gap-3 mb-3">
            <span class="inline-flex items-center justify-center w-8 h-8 bg-blue-600 text-white rounded-full text-sm font-semibold pcl-number">
                ${pclNumber}
            </span>
            <h3 class="text-sm font-medium text-gray-900">PCL #${pclNumber}</h3>
        </div>
        <div class="flex gap-4">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">Nama PCL</label>
                <select name="pcl[${pclCounter}][sobat_id]" 
                        class="select2-pcl pcl-select" 
                        style="width: 100%"
                        data-row="${pclCounter}"
                        onchange="handlePCLSelectChange()">
                    ${optionsHTML}
                </select>
            </div>
            <div class="w-48">
                <label class="block text-sm font-medium text-gray-700 mb-2">Target</label>
                <input type="number" 
                       name="pcl[${pclCounter}][target]" 
                       class="input-field pcl-target"
                       placeholder="Target..."
                       min="1"
                       max="${sisaTarget}"
                       oninput="validatePCLTarget(this)">
            </div>
            <div class="w-12 flex items-end">
                <button type="button" 
                        onclick="removePCLRow(${pclCounter})"
                        class="btn-danger btn-sm w-full h-10">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
        <p class="text-xs text-gray-500 mt-2">
            <i class="fas fa-info-circle mr-1"></i>
            Maksimal target: <span class="font-medium text-gray-700">${sisaTarget}</span> unit
        </p>
    `;

        container.appendChild(row);

        $(`#pclRow${pclCounter} .select2-pcl`).select2({
            placeholder: 'Cari dan pilih PCL...',
            allowClear: true,
            width: '100%'
        });

        updateTargetInfo();
    }

    // Handle PCL select change
    function handlePCLSelectChange() {
        const selectedPCLIds = [];
        document.querySelectorAll('.pcl-select').forEach(select => {
            if (select.value) selectedPCLIds.push(select.value);
        });

        document.querySelectorAll('.pcl-select').forEach(select => {
            const currentValue = select.value;
            const $select = $(select);

            $select.select2('destroy');
            select.innerHTML = '<option value="">Pilih PCL...</option>';

            availablePCL.forEach(pcl => {
                if (pcl.sobat_id.toString() === currentValue || !selectedPCLIds.includes(pcl.sobat_id.toString())) {
                    const option = new Option(
                        `${pcl.nama_user} - ${pcl.email}`,
                        pcl.sobat_id,
                        false,
                        pcl.sobat_id.toString() === currentValue
                    );
                    select.add(option);
                }
            });

            $select.select2({
                placeholder: 'Cari dan pilih PCL...',
                allowClear: true,
                width: '100%'
            });
        });
    }

    // Remove PCL row
    function removePCLRow(id) {
        Swal.fire({
            title: 'Hapus PCL?',
            text: 'PCL akan dihapus dari daftar',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const row = document.getElementById(`pclRow${id}`);
                if (row) {
                    $(row).find('.select2-pcl').select2('destroy');
                    row.remove();
                    renumberPCLRows();
                    handlePCLSelectChange();
                    updateTargetInfo();
                }
            }
        });
    }

    // Renumber PCL rows
    function renumberPCLRows() {
        const rows = document.querySelectorAll('.pcl-row');
        rows.forEach((row, index) => {
            const number = index + 1;
            row.dataset.pclNumber = number;

            const numberBadge = row.querySelector('.pcl-number');
            if (numberBadge) numberBadge.textContent = number;

            const heading = row.querySelector('h3');
            if (heading) heading.textContent = `PCL #${number}`;
        });
    }

    // Validate PCL target
    function validatePCLTarget(input) {
        const target = parseInt(input.value) || 0;
        const currentTotal = getTotalTargetPCL();
        const otherTargets = currentTotal - target;
        const sisaTarget = targetPML - otherTargets;

        if (target > sisaTarget) {
            Swal.fire({
                icon: 'warning',
                title: 'Target Melebihi Batas',
                text: `Target PCL tidak boleh melebihi sisa target PML (${sisaTarget} unit)`,
                confirmButtonColor: '#3b82f6'
            });
            input.value = sisaTarget;
        }

        updateTargetInfo();
    }

    // Get total target PCL
    function getTotalTargetPCL() {
        let total = 0;
        document.querySelectorAll('.pcl-target').forEach(input => {
            total += parseInt(input.value) || 0;
        });
        return total;
    }

    // Update target info
    function updateTargetInfo() {
        const totalTargetPCL = getTotalTargetPCL();
        const sisaTarget = targetPML - totalTargetPCL;

        document.getElementById('totalTargetPCL').textContent = totalTargetPCL;
        document.getElementById('sisaTargetPMLForPCL').textContent = sisaTarget;
        document.getElementById('sisaTargetValue').textContent = sisaTarget;

        document.querySelectorAll('.pcl-target').forEach(input => {
            const currentValue = parseInt(input.value) || 0;
            const otherTargets = totalTargetPCL - currentValue;
            input.max = targetPML - otherTargets;

            const row = input.closest('.pcl-row');
            if (row) {
                const infoText = row.querySelector('.text-gray-700');
                if (infoText) infoText.textContent = targetPML - otherTargets;
            }
        });
    }

    // Update submit button
    function updateSubmitButton() {
        const submitBtn = document.getElementById('submitBtn');
        const hasKegiatan = selectedKegiatan !== null && selectedKegiatan !== '';
        const hasPML = selectedPML !== null && selectedPML !== '';
        const hasTarget = targetPML > 0;

        submitBtn.disabled = !(hasKegiatan && hasPML && hasTarget);
    }

    // Reset form
    function resetForm() {
        document.getElementById('pmlSection').style.display = 'none';
        document.getElementById('targetPMLSection').style.display = 'none';
        document.getElementById('pclSection').style.display = 'none';

        $('.select2-pml').select2('destroy');
        $('.select2-pcl').select2('destroy');

        document.getElementById('pmlSelectContainer').innerHTML = '';
        document.getElementById('pmlTarget').value = '';
        document.getElementById('pclContainer').innerHTML = '';

        const sisaInfo = document.getElementById('sisaTargetInfo');
        if (sisaInfo) sisaInfo.innerHTML = '';

        selectedKegiatan = null;
        selectedPML = null;
        targetPML = 0;
        pclCounter = 0;
        availablePCL = [];
        sisaTargetWilayah = 0;
        targetWilayah = 0;
        targetTerpakai = 0;

        updateSubmitButton();
    }

    // Helper function
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

    // Form validation
    document.getElementById('assignForm').addEventListener('submit', function (e) {
        const totalTargetPCL = getTotalTargetPCL();

        if (totalTargetPCL > targetPML) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Target Tidak Valid',
                text: `Total target PCL (${totalTargetPCL}) melebihi target PML (${targetPML})`,
                confirmButtonColor: '#3b82f6'
            });
            return false;
        }

        if (targetPML > sisaTargetWilayah) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Target Melebihi Batas',
                text: `Target PML (${targetPML.toLocaleString()}) melebihi sisa target wilayah (${sisaTargetWilayah.toLocaleString()})`,
                confirmButtonColor: '#ef4444'
            });
            return false;
        }

        Swal.fire({
            title: 'Menyimpan...',
            text: 'Mohon tunggu sebentar',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    });

    // Initialize
    $(document).ready(function () {
        if ($('#kegiatanSurvei').length && !$('#kegiatanSurvei').data('select2')) {
            $('#kegiatanSurvei').select2({
                placeholder: 'Cari dan pilih kegiatan survei...',
                allowClear: true,
                width: '100%'
            });
        }
    });
</script>

<?= $this->endSection() ?>