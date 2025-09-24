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

            <!-- D1 Field -->
            <div class="mb-3">
                <label for="D1" class="form-label">D1 <span class="text-danger">*</span></label>
                <input type="number"
                    class="form-control <?= form_error('D1') ? 'is-invalid' : ''; ?>"
                    name="D1"
                    id="D1"
                    value="<?= set_value('D1', $fitting['D1']); ?>"
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
                    value="<?= set_value('D2', $fitting['D2']); ?>"
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
                    value="<?= set_value('D3', $fitting['D3']); ?>"
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
                    value="<?= set_value('R_DRAT', $fitting['R_DRAT']); ?>"
                    placeholder="Contoh: 1/4&quot;, 3/8&quot;, 1/2&quot;"
                    maxlength="20"
                    required>
                <?php if (form_error('R_DRAT')): ?>
                    <div class="invalid-feedback">
                        <?= form_error('R_DRAT'); ?>
                    </div>
                <?php endif; ?>
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
    // Preview new fitting ID as user edits
    document.addEventListener('DOMContentLoaded', function() {
        const typeInput = document.getElementById('type');
        const D1Input = document.getElementById('D1');
        const D2Input = document.getElementById('D2');
        const D3Input = document.getElementById('D3');
        const RDratInput = document.getElementById('R_DRAT');
        const currentIdInput = document.getElementById('fitting_id');

        function updatePreview() {
            const type = typeInput.value.toLowerCase().replace(/\s+/g, '_');
            const D1 = parseFloat(D1Input.value) || 0;
            const D2 = parseFloat(D2Input.value) || 0;
            const D3 = parseFloat(D3Input.value) || 0;
            const rDrat = RDratInput.value.replace(/"/g, '');

            if (type && D1 && D2 && D3 && rDrat) {
                const newId = `fit-${type}-${D1.toFixed(1)}-${D2.toFixed(1)}-${D3.toFixed(1)}-${rDrat}`;

                // Update the current ID display if different
                if (newId !== currentIdInput.value) {
                    currentIdInput.value = newId;
                    currentIdInput.style.backgroundColor = '#fff3cd'; // Light yellow to indicate change
                } else {
                    currentIdInput.style.backgroundColor = ''; // Reset background
                }
            }
        }

        [typeInput, D1Input, D2Input, D3Input, RDratInput].forEach(input => {
            input.addEventListener('input', updatePreview);
            input.addEventListener('change', updatePreview);
        });
    });
</script>