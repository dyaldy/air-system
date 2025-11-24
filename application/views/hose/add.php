<!-- Card -->
<div class="card mx-auto rounded-5 shadow border-0 mb-5 w-75" style="margin-top: 5rem;">
    <!-- Card Header -->
    <div class="card-header bg-transparent border-0 px-4 pt-3 pb-1">
        <!-- Container -->
        <div class="d-flex align-items-center justify-content-between">
            <!-- Title -->
            <h4 class="m-0">Tambah Hose Air System</h4>

            <!-- Back Button-->
            <?php
            // Determine back URL: use referer if available and from hose pages, otherwise default to index
            $backUrl = site_url('hose');
            if (
                !empty($_SERVER['HTTP_REFERER']) &&
                (strpos($_SERVER['HTTP_REFERER'], site_url('hose')) !== false)
            ) {
                $backUrl = $_SERVER['HTTP_REFERER'];
            }
            ?>
            <a href="<?= $backUrl; ?>" class="btn btn-secondary rounded-pill">Kembali</a>
        </div>
    </div>

    <!-- Card Body -->
    <div class="card-body p-4 pt-3">
        <form action="" method="post">
            <!-- Note about auto-generated ID -->
            <div class="mb-3">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Hose ID akan dibuat otomatis berdasarkan format: hose-diameter
                </div>
            </div>

            <!-- Diameter Input Section -->
            <div class="mb-3">
                <label for="diameter" class="form-label">Diameter</label>
                <div class="position-relative">
                    <input id="diameter" type="text" class="form-control rounded-pill pe-5 <?= form_error('diameter') ? 'is-invalid' : '' ?>" name="diameter" placeholder="6mm" value="<?= set_value('diameter'); ?>" onkeyup="toggleClear('diameter', 'clear-button-diameter')" autocomplete="off" maxlength="20">
                    <img src="<?= base_url('assets/img/delete.png'); ?>" alt="delete" class="action-button clear-button" id="clear-button-diameter" onclick="clearInput('diameter', 'clear-button-diameter')" aria-hidden="true">
                    <?= form_error('diameter', "<div class='invalid-feedback'>", "</div>"); ?>
                </div>
                <small class="form-text text-muted">Maksimal 20 karakter</small>
            </div>

            <!-- Minimum Stock Input Section -->
            <div class="mb-3">
                <label for="min_stock" class="form-label">Minimum Stock (Opsional)</label>
                <div class="position-relative">
                    <input id="min_stock" type="number" class="form-control rounded-pill pe-5 <?= form_error('min_stock') ? 'is-invalid' : '' ?>" name="min_stock" placeholder="10" value="<?= set_value('min_stock'); ?>" onkeyup="toggleClear('min_stock', 'clear-button-min_stock')" autocomplete="off" min="0">
                    <img src="<?= base_url('assets/img/delete.png'); ?>" alt="delete" class="action-button clear-button" id="clear-button-min_stock" onclick="clearInput('min_stock', 'clear-button-min_stock')" aria-hidden="true">
                    <?= form_error('min_stock', "<div class='invalid-feedback'>", "</div>"); ?>
                </div>
                <small class="form-text text-muted">Kosongkan jika tidak ingin menggunakan minimum stock</small>
            </div>

            <!-- Submit Button -->
            <div class="text-center">
                <button type="submit" class="btn btn-primary rounded-pill">Tambah</button>
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
            id: 'diameter',
            button: 'clear-button-diameter'
        },
        {
            id: 'min_stock',
            button: 'clear-button-min_stock'
        }
    ];
</script>
<script src="<?= base_url('assets/js/forminput.js'); ?>"></script>