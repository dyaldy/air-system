<!-- Card -->
<div class="card mx-auto rounded-5 shadow border-0 mb-5 w-75" style="margin-top: 5rem;">
    <!-- Card Header -->
    <div class="card-header bg-transparent border-0 px-4 pt-3 pb-1">
        <!-- Container -->
        <div class="d-flex align-items-center justify-content-between">
            <!-- Title -->
            <h4 class="m-0">Tambah Fitting Air System</h4>

            <!-- Back Button-->
            <a href="<?= site_url('fitting'); ?>" class="btn btn-secondary rounded-pill">Kembali</a>
        </div>
    </div>

    <!-- Card Body -->
    <div class="card-body p-4 pt-3">
        <?php if (validation_errors()): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Terjadi kesalahan validasi:</strong>
                <?= validation_errors(); ?>
            </div>
        <?php endif; ?>

        <?php if ($this->session->flashdata('message')): ?>
            <?php list($type, $message) = $this->session->flashdata('message'); ?>
            <div class="alert alert-<?= $type === 'danger' ? 'danger' : ($type === 'warning' ? 'warning' : 'info') ?>">
                <i class="fas fa-info-circle"></i> <?= $message ?>
            </div>
        <?php endif; ?>

        <form action="" method="post">
            <!-- Note about auto-generated ID -->
            <div class="mb-3">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Fitting ID akan dibuat otomatis berdasarkan format: fit-type-subtype-D1-D2-D3-R(DRAT)
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
                            <?= (set_value('type', $preselected_type ?? '') == $fitting_type['type']) ? 'selected' : ''; ?>>
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
                                <?= (set_value('subtype') == $subtype['subtype']) ? 'selected' : ''; ?>>
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

            <!-- D1 Field -->
            <div class="mb-3">
                <label for="D1" class="form-label">D1 <span class="text-danger">*</span></label>
                <input type="number"
                    class="form-control <?= form_error('D1') ? 'is-invalid' : ''; ?>"
                    name="D1"
                    id="D1"
                    value="<?= set_value('D1'); ?>"
                    step="0.01"
                    min="0.01"
                    placeholder="Masukkan dimensi D1"
                    required>
                <?php if (form_error('D1')): ?>
                    <div class="invalid-feedback">
                        <?= form_error('D1'); ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- D2 Field -->
            <div class="mb-3">
                <label for="D2" class="form-label">D2 <span class="text-danger">*</span></label>
                <input type="number"
                    class="form-control <?= form_error('D2') ? 'is-invalid' : ''; ?>"
                    name="D2"
                    id="D2"
                    value="<?= set_value('D2'); ?>"
                    step="0.01"
                    min="0.01"
                    placeholder="Masukkan dimensi D2"
                    required>
                <?php if (form_error('D2')): ?>
                    <div class="invalid-feedback">
                        <?= form_error('D2'); ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- D3 Field -->
            <div class="mb-3">
                <label for="D3" class="form-label">D3 <span class="text-danger">*</span></label>
                <input type="number"
                    class="form-control <?= form_error('D3') ? 'is-invalid' : ''; ?>"
                    name="D3"
                    id="D3"
                    value="<?= set_value('D3'); ?>"
                    step="0.01"
                    min="0.01"
                    placeholder="Masukkan dimensi D3"
                    required>
                <?php if (form_error('D3')): ?>
                    <div class="invalid-feedback">
                        <?= form_error('D3'); ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- R(DRAT) Field -->
            <div class="mb-3">
                <label for="R_DRAT" class="form-label">R(DRAT) <span class="text-danger">*</span></label>
                <input type="text"
                    class="form-control <?= form_error('R_DRAT') ? 'is-invalid' : ''; ?>"
                    name="R_DRAT"
                    id="R_DRAT"
                    value="<?= set_value('R_DRAT'); ?>"
                    placeholder="Contoh: 1/4&quot;, 3/8&quot;, 1/2&quot;"
                    maxlength="20"
                    required>
                <?php if (form_error('R_DRAT')): ?>
                    <div class="invalid-feedback">
                        <?= form_error('R_DRAT'); ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Submit Button -->
            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Simpan Fitting</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Handle dynamic subtype loading and preview fitting ID
    document.addEventListener('DOMContentLoaded', function() {
        const typeSelect = document.getElementById('type');
        const subtypeSelect = document.getElementById('subtype');
        const D1Input = document.getElementById('D1');
        const D2Input = document.getElementById('D2');
        const D3Input = document.getElementById('D3');
        const RDratInput = document.getElementById('R_DRAT');

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
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: 'type=' + encodeURIComponent(selectedType)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success' && data.subtypes) {
                            const currentSubtype = '<?= set_value('subtype'); ?>'; // Get the current subtype value

                            data.subtypes.forEach(function(subtype) {
                                const option = document.createElement('option');
                                option.value = subtype.subtype;
                                option.textContent = subtype.subtype;

                                // Pre-select the subtype if it matches form data
                                if (subtype.subtype === currentSubtype) {
                                    option.selected = true;
                                }

                                subtypeSelect.appendChild(option);
                            });

                            updatePreview(); // Update preview after loading subtypes
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
            const D1 = parseFloat(D1Input.value) || 0;
            const D2 = parseFloat(D2Input.value) || 0;
            const D3 = parseFloat(D3Input.value) || 0;
            const rDrat = RDratInput.value.replace(/"/g, '');

            if (type && subtype && D1 && D2 && D3 && rDrat) {
                const previewId = `fit-${type}-${subtype}-${D1.toFixed(1)}-${D2.toFixed(1)}-${D3.toFixed(1)}-${rDrat}`;

                // Update or create preview element
                let preview = document.getElementById('id-preview');
                if (!preview) {
                    preview = document.createElement('div');
                    preview.id = 'id-preview';
                    preview.className = 'alert alert-secondary mt-2';
                    document.querySelector('.alert-info').parentNode.appendChild(preview);
                }
                preview.innerHTML = `<i class="fas fa-eye"></i> Preview ID: <code>${previewId}</code>`;
            }
        }

        [typeSelect, subtypeSelect, D1Input, D2Input, D3Input, RDratInput].forEach(input => {
            input.addEventListener('input', updatePreview);
            input.addEventListener('change', updatePreview);
        });

        // Initialize subtypes on page load if type is already selected (from form validation failure)
        if (typeSelect.value) {
            typeSelect.dispatchEvent(new Event('change'));
        }
    });
</script>