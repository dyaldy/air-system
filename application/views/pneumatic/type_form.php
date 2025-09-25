<!-- Card -->
<div class="card mx-auto rounded-5 shadow border-0 mb-5 w-75" style="margin-top: 5rem;">
    <!-- Card Header -->
    <div class="card-header bg-transparent border-0 px-4 pt-3 pb-1">
        <!-- Container -->
        <div class="d-flex align-items-center justify-content-between">
            <!-- Title -->
            <h4 class="m-0"><?= htmlspecialchars($title ?? 'Type Pneumatic', ENT_QUOTES, 'UTF-8') ?></h4>

            <!-- Back Button-->
            <a href="<?= site_url('pneumatic_type'); ?>" class="btn btn-secondary rounded-pill">Kembali</a>
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
                <label for="type" class="form-label">Type Pneumatic <span class="text-danger">*</span></label>
                <input type="text"
                    class="form-control <?= form_error('type') ? 'is-invalid' : ''; ?>"
                    name="type"
                    id="type"
                    value="<?= set_value('type', $type['type'] ?? ''); ?>"
                    placeholder="Masukkan nama type pneumatic (contoh: CYLINDER, VALVE, ACTUATOR)"
                    maxlength="10"
                    required>
                <?php if (form_error('type')): ?>
                    <div class="invalid-feedback">
                        <?= form_error('type'); ?>
                    </div>
                <?php endif; ?>
                <div class="form-text">Maksimal 10 karakter. Gunakan huruf besar dan underscore untuk pemisah kata.</div>
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
                        <img src="<?= base_url('assets/img/pneumatic_types/' . htmlspecialchars($type['image'], ENT_QUOTES, 'UTF-8')) ?>"
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
</script>