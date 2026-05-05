<?php

$label = $label ?? '';
$name = $name ?? 'select_field';
$id = $id ?? $name;
$required = $required ?? false;
$placeholder = $placeholder ?? '-- Pilih --';
$options = $options ?? [];
$optionValue = $optionValue ?? 'id';
$optionText = $optionText ?? 'name';
$optionDataAttributes = $optionDataAttributes ?? [];
$onchange = $onchange ?? '';
$emptyMessage = $emptyMessage ?? 'Tidak ada data tersedia';
$grouped = $grouped ?? false;
$groupBy = $groupBy ?? '';
$value = $value ?? '';
$helpText = $helpText ?? '';
$enableSearch = $enableSearch ?? true;

// Generate unique ID for component instance
$componentId = 'select-component-' . uniqid();
?>

<div class="form-group">
    <?php if (!empty($label)): ?>
    <label class="block text-sm font-medium text-gray-700 mb-2">
        <?= esc($label) ?>
        <?php if ($required): ?>
            <span class="text-red-500">*</span>
        <?php endif; ?>
    </label>
    <?php endif; ?>
    
    <?php if (empty($options)): ?>
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex items-center">
                <i class="fas fa-exclamation-triangle text-yellow-600 mr-3"></i>
                <p class="text-sm text-yellow-700"><?= esc($emptyMessage) ?></p>
            </div>
        </div>
    <?php else: ?>
        <select 
            name="<?= esc($name) ?>" 
            id="<?= esc($id) ?>" 
            class="input-field select2-field <?= esc($componentId) ?>"
            <?= $required ? 'required' : '' ?>
            <?= $onchange ? 'onchange="' . esc($onchange) . '"' : '' ?>
            data-placeholder="<?= esc($placeholder) ?>"
            data-enable-search="<?= $enableSearch ? 'true' : 'false' ?>">
            
            <option value=""><?= esc($placeholder) ?></option>
            
            <?php if ($grouped && $groupBy): ?>
                <?php
                $currentGroup = '';
                foreach ($options as $option):
                    $groupValue = $option[$groupBy] ?? '';
                    
                    if ($currentGroup != $groupValue):
                        if ($currentGroup != ''): ?>
                            </optgroup>
                        <?php endif; ?>
                        <optgroup label="<?= esc($groupValue) ?>">
                        <?php $currentGroup = $groupValue;
                    endif;
                    
                    $optValue = $option[$optionValue] ?? '';
                    $optText = is_callable($optionText) 
                        ? $optionText($option) 
                        : ($option[$optionText] ?? '');
                    $isSelected = ($value == $optValue) ? 'selected' : '';
                ?>
                    <option value="<?= esc($optValue) ?>" <?= $isSelected ?>
                        <?php foreach ($optionDataAttributes as $dataKey): ?>
                            data-<?= esc($dataKey) ?>="<?= esc($option[$dataKey] ?? '') ?>"
                        <?php endforeach; ?>>
                        <?= esc($optText) ?>
                    </option>
                <?php endforeach; ?>
                </optgroup>
            <?php else: ?>
                <?php foreach ($options as $option):
                    $optValue = $option[$optionValue] ?? '';
                    $optText = is_callable($optionText) 
                        ? $optionText($option) 
                        : ($option[$optionText] ?? '');
                    $isSelected = ($value == $optValue) ? 'selected' : '';
                ?>
                    <option value="<?= esc($optValue) ?>" <?= $isSelected ?>
                        <?php foreach ($optionDataAttributes as $dataKey): ?>
                            data-<?= esc($dataKey) ?>="<?= esc($option[$dataKey] ?? '') ?>"
                        <?php endforeach; ?>>
                        <?= esc($optText) ?>
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
        
        <?php if ($helpText): ?>
            <p class="text-xs text-gray-500 mt-2">
                <i class="fas fa-info-circle mr-1"></i>
                <?= esc($helpText) ?>
            </p>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Select2 CSS (only load once) -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
/* Modern Select2 Styling - Component Specific */
.select2-container--default .select2-selection--single {
    height: 42px;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    padding: 0.5rem 0.75rem;
    transition: all 0.2s;
}

.select2-container--default .select2-selection--single:hover {
    border-color: #9ca3af;
}

.select2-container--default.select2-container--focus .select2-selection--single {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    outline: none;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 26px;
    padding-left: 0;
    color: #111827;
}

.select2-container--default .select2-selection--single .select2-selection__placeholder {
    color: #9ca3af;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 40px;
    right: 8px;
}

.select2-container--default .select2-selection--single .select2-selection__arrow b {
    border-color: #6b7280 transparent transparent transparent;
}

.select2-dropdown {
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.select2-search--dropdown {
    padding: 8px;
}

.select2-search--dropdown .select2-search__field {
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
}

.select2-search--dropdown .select2-search__field:focus {
    border-color: #3b82f6;
    outline: none;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.select2-results__options {
    max-height: 300px;
}

.select2-results__option {
    padding: 8px 12px;
    font-size: 0.875rem;
    color: #374151;
}

.select2-results__option--highlighted {
    background-color: #3b82f6 !important;
    color: white !important;
}

.select2-results__option--selected {
    background-color: #eff6ff;
    color: #1e40af;
}

.select2-container--default .select2-results__group {
    font-weight: 600;
    font-size: 0.75rem;
    color: #6b7280;
    background-color: #f9fafb;
    padding: 6px 12px;
    text-transform: uppercase;
    letter-spacing: 0.025em;
}

.select2-container--open .select2-selection__arrow b {
    border-color: transparent transparent #6b7280 transparent !important;
}
</style>

<!-- jQuery & Select2 JS (load only once) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize Select2 for this specific field
    var $select = $('.<?= esc($componentId) ?>');
    var enableSearch = $select.data('enable-search');
    
    $select.select2({
        placeholder: '<?= esc($placeholder) ?>',
        allowClear: true,
        width: '100%',
        language: {
            noResults: function() {
                return "Data tidak ditemukan";
            },
            searching: function() {
                return "Mencari...";
            }
        },
        // Always enable search, no minimum threshold
        minimumResultsForSearch: enableSearch ? 0 : -1
    });
});
</script>