<!-- Card -->
<div class="card mx-auto rounded-5 shadow border-0 mb-5 w-75" style="margin-top: 5rem;">
    <!-- Card Header -->
    <div class="card-header bg-transparent border-0 px-4 pt-3 pb-1">
        <!-- Container -->
        <div class="d-flex align-items-center justify-content-between">
            <!-- Title -->
            <h4 class="m-0"><?= htmlspecialchars($title ?? 'Type Solenoid', ENT_QUOTES, 'UTF-8') ?></h4>

            <!-- Back Button-->
            <a href="<?= site_url('solenoid_type'); ?>" class="btn btn-secondary rounded-pill">Kembali</a>
        </div>
    </div>

    <!-- Card Body -->
    <div class="card-body p-4 pt-3">
        <?php if ($this->session->flashdata('action')) : ?>
            <div class="alert alert-<?= $this->session->flashdata('action')[0]; ?> alert-dismissible fade show" role="alert">
                <?= $this->session->flashdata('action')[1]; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?= form_open_multipart($form_action, ['id' => 'typeForm']); ?>
        <!-- Type Field -->
        <div class="mb-3">
            <label for="type" class="form-label">Type Solenoid <span class="text-danger">*</span></label>
            <input type="text"
                class="form-control <?= form_error('type') ? 'is-invalid' : ''; ?>"
                name="type"
                id="type"
                value="<?= set_value('type', $type['type'] ?? ''); ?>"
                placeholder="Masukkan nama type solenoid (contoh: VALVE, COIL, PILOT)"
                maxlength="15"
                required>
            <?php if (form_error('type')): ?>
                <div class="invalid-feedback">
                    <?= form_error('type'); ?>
                </div>
            <?php endif; ?>
            <div class="form-text">Maksimal 15 karakter. Hanya boleh huruf, angka, spasi, dan karakter - /</div>
        </div>

        <!-- Image Field -->
        <div class="mb-3">
            <label for="image" class="form-label">Gambar Type</label>
            <input type="file"
                class="form-control"
                name="image"
                id="image"
                accept=".jpg,.jpeg,.png">
            <div class="form-text">
                Format yang didukung: JPG, JPEG, PNG. Maksimal 2MB.
                <?php if (!empty($type['image'])): ?>
                    <br>File saat ini: <?= htmlspecialchars($type['image'], ENT_QUOTES, 'UTF-8') ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Current Image Preview (for edit mode) -->
        <?php if (!empty($type['image'])): ?>
            <div class="mb-3">
                <label class="form-label">Gambar Saat Ini</label>
                <div class="current-image-preview">
                    <img src="<?= base_url('assets/img/solenoid_types/' . htmlspecialchars($type['image'], ENT_QUOTES, 'UTF-8')) ?>"
                        alt="<?= htmlspecialchars($type['type'] ?? 'Type Image', ENT_QUOTES, 'UTF-8') ?>"
                        class="img-thumbnail"
                        style="max-width: 200px; max-height: 200px;">
                </div>
                <div class="form-text">Unggah gambar baru untuk mengganti gambar ini.</div>
            </div>
        <?php else: ?>
            <!-- Placeholder Image Preview -->
            <div class="mb-3">
                <label class="form-label">Preview Gambar</label>
                <div class="current-image-preview">
                    <img src="<?= base_url('assets/img/placeholder-image.svg') ?>"
                        alt="Placeholder Image"
                        class="img-thumbnail"
                        style="max-width: 200px; max-height: 200px;">
                </div>
                <div class="form-text">Gambar placeholder akan diganti saat Anda mengunggah gambar.</div>
            </div>
        <?php endif; ?>

        <!-- Submit Button -->
        <div class="d-grid">
            <button type="submit" class="btn btn-primary">
                <?= empty($type) ? 'Tambah Type' : 'Update Type' ?>
            </button>
        </div>
        <?= form_close(); ?>
    </div>
</div>

<script>
    // Preview uploaded image
    document.getElementById('image').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                // Remove existing preview if any
                const existingPreview = document.getElementById('image-preview');
                if (existingPreview) {
                    existingPreview.remove();
                }

                // Create new preview
                const preview = document.createElement('div');
                preview.id = 'image-preview';
                preview.className = 'mb-3';
                preview.innerHTML = `
                    <label class="form-label">Preview Gambar Baru</label>
                    <div>
                        <img src="${e.target.result}" alt="Preview" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                    </div>
                `;

                // Insert after the file input
                document.getElementById('image').parentNode.insertAdjacentElement('afterend', preview);
            };
            reader.readAsDataURL(file);
        }
    });

    // Validate type format
    document.addEventListener('DOMContentLoaded', function() {
        const typeInput = document.getElementById('type');
        if (typeInput) {
            let warningTimeout;

            // Check input on keyup
            typeInput.addEventListener('input', function(e) {
                const value = e.target.value;
                // Only allow alphanumeric, spaces, and - /
                const filteredValue = value.replace(/[^a-zA-Z0-9\s\-\/]/g, '');

                if (value !== filteredValue) {
                    showWarning('Hanya huruf, angka, spasi, dan karakter - / yang diizinkan!');
                    e.target.value = filteredValue;
                }
            });

            // Validate format on blur and hide warning
            typeInput.addEventListener('blur', function(e) {
                const value = e.target.value;
                hideWarning();
                if (value && !value.match(/^[a-zA-Z0-9\s\-\/]+$/)) {
                    e.target.setCustomValidity('Type hanya boleh mengandung huruf, angka, spasi, dan karakter - /');
                } else {
                    e.target.setCustomValidity('');
                }
            });

            function showWarning(message) {
                // Remove existing warning
                hideWarning();

                // Create warning element
                const warning = document.createElement('div');
                warning.className = 'text-danger';
                warning.style.cssText = 'font-size: 0.875rem; margin-top: 0.25rem; font-weight: 500;';
                warning.textContent = message;
                warning.setAttribute('data-warning', 'character-warning');

                // Insert after input
                typeInput.parentNode.appendChild(warning);

                // Add red border to input
                typeInput.style.borderColor = '#dc3545';
                typeInput.style.boxShadow = '0 0 0 0.2rem rgba(220, 53, 69, 0.25)';

                // Auto-hide after 3 seconds
                clearTimeout(warningTimeout);
                warningTimeout = setTimeout(() => {
                    hideWarning();
                }, 3000);
            }

            function hideWarning() {
                const warning = typeInput.parentNode.querySelector('[data-warning="character-warning"]');
                if (warning) {
                    warning.remove();
                }
                // Reset border color
                typeInput.style.borderColor = '';
                typeInput.style.boxShadow = '';
            }
        }
    });
</script>