<!-- Card -->
<div class="card mx-auto rounded-5 shadow border-0 mb-5 w-75" style="margin-top: 5rem;">
    <!-- Card Header -->
    <div class="card-header bg-transparent border-0 px-4 pt-3 pb-1">
        <!-- Container -->
        <div class="d-flex align-items-center justify-content-between">
            <!-- Title -->
            <h4 class="m-0">Edit Fitting Air System</h4>

            <!-- Back Button-->
            <a href="<?= site_url('fitting'); ?>" class="btn btn-secondary rounded-pill">Kembali</a>
        </div>
    </div>

    <!-- Card Body -->
    <div class="card-body p-4 pt-3">
        <form action="" method="post">
            <!-- Fitting ID Display Section (Read-only) -->
            <div class="mb-2">
                <label for="fitting_id" class="form-label">Fitting ID (Auto-generated)</label>
                <input type="text" class="form-control" id="fitting_id" value="<?= $fitting['fitting_id']; ?>" readonly>
                <small class="text-muted">ID akan diperbarui otomatis berdasarkan perubahan data</small>
            </div>

            <!-- Alert for ID Update Warning -->
            <div class="mb-3">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    Perhatian: Mengubah data akan menghasilkan Fitting ID baru. Pastikan tidak ada referensi ke ID lama di sistem lain.
                </div>
            </div>

            <!-- Type Field -->
            <div class="mb-3">
                <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                <select class="form-select <?= form_error('type') ? 'is-invalid' : ''; ?>"
                    name="type" id="type" required>
                    <option value="">Pilih Type</option>
                    <?php foreach ($fitting_types as $fitting_type): ?>
                        <option value="<?= $fitting_type['type']; ?>"
                            <?= (set_value('type', $fitting['type']) == $fitting_type['type']) ? 'selected' : ''; ?>>
                            <?= $fitting_type['type']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (form_error('type')): ?>
                    <div class="invalid-feedback">
                        <?= form_error('type'); ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Subtype Field -->
            <div class="mb-3">
                <label for="subtype" class="form-label">Subtype <span class="text-danger">*</span></label>
                <select class="form-select <?= form_error('subtype') ? 'is-invalid' : ''; ?>"
                    name="subtype" id="subtype" required>
                    <option value="">Pilih Subtype</option>
                    <?php if (!empty($subtypes)): ?>
                        <?php foreach ($subtypes as $subtype): ?>
                            <option value="<?= $subtype['subtype']; ?>"
                                <?= (set_value('subtype', $fitting['subtype'] ?? '') == $subtype['subtype']) ? 'selected' : ''; ?>>
                                <?= $subtype['subtype']; ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <?php if (form_error('subtype')): ?>
                    <div class="invalid-feedback">
                        <?= form_error('subtype'); ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Field Selection Section -->
            <div class="mb-4">
                <label class="form-label">Pilih Field yang akan diisi <span class="text-danger">*</span></label>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Pilih field dimensi yang ingin Anda isi. Minimal satu field harus dipilih.
                </div>

                <div class="row">
                    <div class="col-md-6 col-lg-3 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="enable_d1" id="enable_d1" value="1" <?= set_checkbox('enable_d1', '1', !empty($fitting['D1'])); ?>>
                            <label class="form-check-label" for="enable_d1">
                                D1
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="enable_d2" id="enable_d2" value="1" <?= set_checkbox('enable_d2', '1', !empty($fitting['D2'])); ?>>
                            <label class="form-check-label" for="enable_d2">
                                D2
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="enable_d3" id="enable_d3" value="1" <?= set_checkbox('enable_d3', '1', !empty($fitting['D3'])); ?>>
                            <label class="form-check-label" for="enable_d3">
                                D3
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="enable_r_drat" id="enable_r_drat" value="1" <?= set_checkbox('enable_r_drat', '1', !empty($fitting['R_DRAT'])); ?>>
                            <label class="form-check-label" for="enable_r_drat">
                                R(DRAT)
                            </label>
                        </div>
                    </div>
                </div>
                <?php if (form_error('enable_d1')): ?>
                    <div class="text-danger mt-1">
                        <?= form_error('enable_d1'); ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- D1 Field -->
            <div class="mb-3" id="d1_field" style="display: none;">
                <label for="D1" class="form-label">D1 <span class="text-danger">*</span></label>
                <input type="number"
                    class="form-control <?= form_error('D1') ? 'is-invalid' : ''; ?>"
                    name="D1"
                    id="D1"
                    value="<?= set_value('D1', $fitting['D1']); ?>"
                    step="0.01"
                    min="0.01"
                    placeholder="Masukkan dimensi D1">
                <?php if (form_error('D1')): ?>
                    <div class="invalid-feedback">
                        <?= form_error('D1'); ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- D2 Field -->
            <div class="mb-3" id="d2_field" style="display: none;">
                <label for="D2" class="form-label">D2 <span class="text-danger">*</span></label>
                <input type="number"
                    class="form-control <?= form_error('D2') ? 'is-invalid' : ''; ?>"
                    name="D2"
                    id="D2"
                    value="<?= set_value('D2', $fitting['D2']); ?>"
                    step="0.01"
                    min="0.01"
                    placeholder="Masukkan dimensi D2">
                <?php if (form_error('D2')): ?>
                    <div class="invalid-feedback">
                        <?= form_error('D2'); ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- D3 Field -->
            <div class="mb-3" id="d3_field" style="display: none;">
                <label for="D3" class="form-label">D3 <span class="text-danger">*</span></label>
                <input type="number"
                    class="form-control <?= form_error('D3') ? 'is-invalid' : ''; ?>"
                    name="D3"
                    id="D3"
                    value="<?= set_value('D3', $fitting['D3']); ?>"
                    step="0.01"
                    min="0.01"
                    placeholder="Masukkan dimensi D3">
                <?php if (form_error('D3')): ?>
                    <div class="invalid-feedback">
                        <?= form_error('D3'); ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- R(DRAT) Field -->
            <div class="mb-3" id="r_drat_field" style="display: none;">
                <label for="R_DRAT" class="form-label">R(DRAT) <span class="text-danger">*</span></label>
                <input type="text"
                    class="form-control <?= form_error('R_DRAT') ? 'is-invalid' : ''; ?>"
                    name="R_DRAT"
                    id="R_DRAT"
                    value="<?= set_value('R_DRAT', $fitting['R_DRAT']); ?>"
                    placeholder="Contoh: M5, M6, 01, 02, 03"
                    maxlength="20">
                <?php if (form_error('R_DRAT')): ?>
                    <div class="invalid-feedback">
                        <?= form_error('R_DRAT'); ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Minimum Stock Input Section -->
            <div class="mb-3">
                <label for="min_stock" class="form-label">Minimum Stock (Opsional)</label>
                <input type="number" class="form-control <?= form_error('min_stock') ? 'is-invalid' : ''; ?>" name="min_stock" id="min_stock" value="<?= set_value('min_stock', $fitting['min_stock']); ?>" min="0" placeholder="Masukkan minimum stock">
                <?php if (form_error('min_stock')): ?>
                    <div class="invalid-feedback">
                        <?= form_error('min_stock'); ?>
                    </div>
                <?php endif; ?>
                <small class="form-text text-muted">Kosongkan jika tidak ingin menggunakan minimum stock</small>
            </div>

            <!-- Submit and Delete Buttons -->
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill">Update Fitting</button>
                <a href="<?= site_url('fitting/delete/' . urlencode($fitting['fitting_id'])); ?>"
                    class="btn btn-danger"
                    onclick="return confirm('Apakah Anda yakin ingin menghapus fitting ini?')">
                    Hapus
                </a>
            </div>
        </form>
    </div>
</div>

<script>
    // Handle dynamic subtype loading, field visibility, and preview new fitting ID as user edits
    document.addEventListener('DOMContentLoaded', function() {
        const typeSelect = document.getElementById('type');
        const subtypeSelect = document.getElementById('subtype');
        const D1Input = document.getElementById('D1');
        const D2Input = document.getElementById('D2');
        const D3Input = document.getElementById('D3');
        const RDratInput = document.getElementById('R_DRAT');
        const currentIdInput = document.getElementById('fitting_id');

        // Checkbox elements
        const enableD1 = document.getElementById('enable_d1');
        const enableD2 = document.getElementById('enable_d2');
        const enableD3 = document.getElementById('enable_d3');
        const enableRDrat = document.getElementById('enable_r_drat');

        // Field containers
        const d1Field = document.getElementById('d1_field');
        const d2Field = document.getElementById('d2_field');
        const d3Field = document.getElementById('d3_field');
        const rDratField = document.getElementById('r_drat_field');

        // Handle checkbox changes to show/hide fields
        function toggleField(checkbox, fieldContainer, input) {
            fieldContainer.style.display = checkbox.checked ? 'block' : 'none';
            input.required = checkbox.checked;
            if (!checkbox.checked) {
                input.value = '';
            }
            updatePreview();
        }

        enableD1.addEventListener('change', function() {
            toggleField(this, d1Field, D1Input);
        });

        enableD2.addEventListener('change', function() {
            toggleField(this, d2Field, D2Input);
        });

        enableD3.addEventListener('change', function() {
            toggleField(this, d3Field, D3Input);
        });

        enableRDrat.addEventListener('change', function() {
            toggleField(this, rDratField, RDratInput);
        });

        // Initialize field visibility based on checkbox state
        toggleField(enableD1, d1Field, D1Input);
        toggleField(enableD2, d2Field, D2Input);
        toggleField(enableD3, d3Field, D3Input);
        toggleField(enableRDrat, rDratField, RDratInput);

        // Load subtypes when type changes
        typeSelect.addEventListener('change', function() {
            const selectedType = this.value;

            // Clear subtype dropdown
            subtypeSelect.innerHTML = '<option value="">Pilih Subtype</option>';

            if (selectedType) {
                // Make AJAX request to get subtypes
                fetch('<?= site_url('fitting/getSubtypes'); ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'type=' + encodeURIComponent(selectedType)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success' && data.subtypes) {
                            data.subtypes.forEach(function(subtype) {
                                const option = document.createElement('option');
                                option.value = subtype.subtype;
                                option.textContent = subtype.subtype;
                                // Re-select the current subtype if it matches
                                if (subtype.subtype === '<?= $fitting['subtype'] ?? ''; ?>') {
                                    option.selected = true;
                                }
                                subtypeSelect.appendChild(option);
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error loading subtypes:', error);
                    });
            }

            updatePreview();
        });

        function updatePreview() {
            const type = typeSelect.value.toLowerCase().replace(/\s+/g, '_');
            const subtype = subtypeSelect.value.toLowerCase().replace(/\s+/g, '_');

            // Only include enabled fields in preview
            const idParts = ['fit', type, subtype];

            if (enableD1.checked && D1Input.value) {
                idParts.push(parseFloat(D1Input.value).toFixed(1));
            }
            if (enableD2.checked && D2Input.value) {
                idParts.push(parseFloat(D2Input.value).toFixed(1));
            }
            if (enableD3.checked && D3Input.value) {
                idParts.push(parseFloat(D3Input.value).toFixed(1));
            }
            if (enableRDrat.checked && RDratInput.value) {
                idParts.push(RDratInput.value.replace(/"/g, ''));
            }

            if (type && subtype && idParts.length > 3) {
                const newId = idParts.filter(part => part && part !== '').join('-');

                // Update the current ID display if different
                if (newId !== currentIdInput.value) {
                    currentIdInput.value = newId;
                    currentIdInput.style.backgroundColor = '#fff3cd'; // Light yellow to indicate change
                } else {
                    currentIdInput.style.backgroundColor = ''; // Reset background
                }
            }
        }

        [typeSelect, subtypeSelect, D1Input, D2Input, D3Input, RDratInput].forEach(input => {
            input.addEventListener('input', updatePreview);
            input.addEventListener('change', updatePreview);
        });

        // Add change listeners for checkboxes
        [enableD1, enableD2, enableD3, enableRDrat].forEach(checkbox => {
            checkbox.addEventListener('change', updatePreview);
        });

        // Initialize subtypes on page load if type is already selected
        if (typeSelect.value) {
            typeSelect.dispatchEvent(new Event('change'));
        }
    });
</script>