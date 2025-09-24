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
        <form action="" method="post">
            <!-- Note about auto-generated ID -->
            <div class="mb-3">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Fitting ID akan dibuat otomatis berdasarkan format: fit-type-D1-D2-D3-R(DRAT)
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
    // Preview fitting ID as user types
    document.addEventListener('DOMContentLoaded', function() {
        const typeInput = document.getElementById('type');
        const D1Input = document.getElementById('D1');
        const D2Input = document.getElementById('D2');
        const D3Input = document.getElementById('D3');
        const RDratInput = document.getElementById('R_DRAT');

        function updatePreview() {
            const type = typeInput.value.toLowerCase().replace(/\s+/g, '_');
            const D1 = parseFloat(D1Input.value) || 0;
            const D2 = parseFloat(D2Input.value) || 0;
            const D3 = parseFloat(D3Input.value) || 0;
            const rDrat = RDratInput.value.replace(/"/g, '');

            if (type && D1 && D2 && D3 && rDrat) {
                const previewId = `fit-${type}-${D1.toFixed(1)}-${D2.toFixed(1)}-${D3.toFixed(1)}-${rDrat}`;

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

        [typeInput, D1Input, D2Input, D3Input, RDratInput].forEach(input => {
            input.addEventListener('input', updatePreview);
            input.addEventListener('change', updatePreview);
        });
    });
</script>