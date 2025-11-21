<!-- Card -->
<div class="card mx-auto rounded-5 shadow border-0 mb-5 w-75" style="margin-top: 5rem;">
    <!-- Card Header -->
    <div class="card-header bg-transparent border-0 px-4 pt-3 pb-1">
        <!-- Container -->
        <div class="d-flex align-items-center justify-content-between">
            <!-- Title -->
            <h4 class="m-0">Edit Manifold Air System</h4>

            <!-- Back Button-->
            <a href="<?= site_url('manifold'); ?>" class="btn btn-secondary rounded-pill">Kembali</a>
        </div>
    </div>

    <!-- Card Body -->
    <div class="card-body p-4 pt-3">
        <form action="" method="post">
            <!-- Manifold ID Display Section (Read-only) -->
            <div class="mb-2">
                <label for="manifold_id" class="form-label">Manifold ID (Auto-generated)</label>
                <input id="manifold_id" type="text" class="form-control rounded-pill" name="manifold_id" value="<?= $manifold['manifold_id'] ?>" readonly>
                <small class="text-muted">ID akan diperbarui secara otomatis jika block diubah</small>
            </div>

            <!-- Block Input Section -->
            <div class="mb-3">
                <label for="block" class="form-label">Block</label>
                <div class="position-relative">
                    <input id="block" type="number" class="form-control rounded-pill pe-5 <?= form_error('block') ? 'is-invalid' : '' ?>" name="block" placeholder="5" value="<?= set_value('block') ? set_value('block') : $manifold['block']; ?>" onkeyup="toggleClear('block', 'clear-button-block')" autocomplete="off" min="1">
                    <img src="<?= base_url('assets/img/delete.png'); ?>" alt="delete" class="action-button clear-button" id="clear-button-block" onclick="clearInput('block', 'clear-button-block')" aria-hidden="true">
                    <?= form_error('block', "<div class='invalid-feedback'>", "</div>"); ?>
                </div>
            </div>

            <!-- Minimum Stock Input Section -->
            <div class="mb-3">
                <label for="min_stock" class="form-label">Minimum Stock (Opsional)</label>
                <div class="position-relative">
                    <input id="min_stock" type="number" class="form-control rounded-pill pe-5 <?= form_error('min_stock') ? 'is-invalid' : '' ?>" name="min_stock" placeholder="10" value="<?= set_value('min_stock') ? set_value('min_stock') : $manifold['min_stock']; ?>" onkeyup="toggleClear('min_stock', 'clear-button-min_stock')" autocomplete="off" min="0">
                    <img src="<?= base_url('assets/img/delete.png'); ?>" alt="delete" class="action-button clear-button" id="clear-button-min_stock" onclick="clearInput('min_stock', 'clear-button-min_stock')" aria-hidden="true">
                    <?= form_error('min_stock', "<div class='invalid-feedback'>", "</div>"); ?>
                </div>
                <small class="form-text text-muted">Kosongkan jika tidak ingin menggunakan minimum stock</small>
            </div>

            <!-- Save and Delete Button -->
            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-primary rounded-pill">Simpan</button>
                <a href="<?= site_url('manifold/delete/') . urlencode($manifold['manifold_id']); ?>" onclick="return confirm('Apakah anda yakin ingin menghapus manifold ini?')" class="btn btn-danger rounded-pill">Hapus</a>
            </div>
        </form>
    </div>
</div>

<script>
    /**
     * Input-clear configuration mapping for form inputs with clear buttons.
     * 
     * @type {Array<{id: string, button: string}>}
     */
    window.inputConfigs = [{
            id: 'block',
            button: 'clear-button-block'
        },
        {
            id: 'min_stock',
            button: 'clear-button-min_stock'
        }
    ];
</script>
<script src="<?= base_url('assets/js/forminput.js'); ?>"></script>