<!-- Card -->
<div class="card mx-auto rounded-5 shadow border-0 mb-5 w-75" style="margin-top: 5rem;">
    <!-- Card Header -->
    <div class="card-header bg-transparent border-0 px-4 pt-3 pb-1">
        <!-- Container -->
        <div class="d-flex align-items-center justify-content-between">
            <!-- Title -->
            <h4 class="m-0">Edit Regulator Air System</h4>

            <!-- Back Button-->
            <a href="<?= site_url('regulator'); ?>" class="btn btn-secondary rounded-pill">Kembali</a>
        </div>
    </div>

    <!-- Card Body -->
    <div class="card-body p-4 pt-3">
        <form action="" method="post">
            <!-- Regulator ID Display Section (Read-only) -->
            <div class="mb-2">
                <label for="regulator_id" class="form-label">Regulator ID (Auto-generated)</label>
                <input id="regulator_id" type="text" class="form-control rounded-pill" name="regulator_id" value="<?= $regulator['regulator_id'] ?>" readonly>
            </div>

            <!-- Type Display Section (Read-only) -->
            <div class="mb-3">
                <label for="type" class="form-label">Type</label>
                <input id="type" type="text" class="form-control rounded-pill" name="type_display" value="<?= $regulator['type'] ?>" readonly>
                <small class="text-muted">Type tidak dapat diubah</small>
            </div>

            <!-- Minimum Stock Input Section -->
            <div class="mb-3">
                <label for="min_stock" class="form-label">Minimum Stock (Opsional)</label>
                <div class="position-relative">
                    <input id="min_stock" type="number" class="form-control rounded-pill pe-5 <?= form_error('min_stock') ? 'is-invalid' : '' ?>" name="min_stock" placeholder="10" value="<?= set_value('min_stock') ? set_value('min_stock') : $regulator['min_stock']; ?>" onkeyup="toggleClear('min_stock', 'clear-button-min_stock')" autocomplete="off" min="0">
                    <img src="<?= base_url('assets/img/delete.png'); ?>" alt="delete" class="action-button clear-button" id="clear-button-min_stock" onclick="clearInput('min_stock', 'clear-button-min_stock')" aria-hidden="true">
                    <?= form_error('min_stock', "<div class='invalid-feedback'>", "</div>"); ?>
                </div>
                <small class="form-text text-muted">Kosongkan jika tidak ingin menggunakan minimum stock</small>
            </div>

            <!-- Save and Delete Button -->
            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-primary rounded-pill">Simpan</button>
                <a href="<?= site_url('regulator/delete/') . urlencode($regulator['regulator_id']); ?>" onclick="return confirm('Apakah anda yakin ingin menghapus regulator ini?')" class="btn btn-danger rounded-pill">Hapus</a>
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
        id: 'min_stock',
        button: 'clear-button-min_stock'
    }];
</script>
<script src="<?= base_url('assets/js/forminput.js'); ?>"></script>