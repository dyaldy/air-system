<!-- Card -->
<div class="card mx-auto rounded-5 shadow border-0 mb-5 w-75" style="margin-top: 5rem;">
    <!-- Card Header -->
    <div class="card-header bg-transparent border-0 px-4 pt-3 pb-1">
        <!-- Container -->
        <div class="d-flex align-items-center justify-content-between">
            <!-- Title -->
            <h4 class="m-0">Tambah Solenoid Air System</h4>

            <!-- Back Button-->
            <?php
            // Determine back URL: use referer if available and from solenoid pages, otherwise default to index
            $backUrl = site_url('solenoid');
            if (
                !empty($_SERVER['HTTP_REFERER']) &&
                (strpos($_SERVER['HTTP_REFERER'], site_url('solenoid')) !== false)
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
                    <i class="fas fa-info-circle"></i> Solenoid ID akan dibuat otomatis berdasarkan format: sol-type-subtype
                </div>
            </div>

            <!-- Type Dropdown Section -->
            <div class="mb-2">
                <label for="type" class="form-label">Type</label>
                <div class="position-relative">
                    <select id="type" name="type" class="form-select rounded-pill <?= form_error('type') ? 'is-invalid' : '' ?>">
                        <option value="">Pilih Type...</option>
                        <?php foreach ($solenoid_types as $stype): ?>
                            <?php
                            $isSelected = false;
                            if (set_value('type')) {
                                $isSelected = (set_value('type') == $stype['type']);
                            } elseif (isset($preselected_type) && !empty($preselected_type)) {
                                $isSelected = (strtoupper($preselected_type) == $stype['type']);
                            }
                            ?>
                            <option value="<?= html_escape($stype['type']) ?>" <?= $isSelected ? 'selected' : '' ?>>
                                <?= html_escape($stype['type']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?= form_error('type', "<div class='invalid-feedback'>", "</div>"); ?>
                </div>
            </div>

            <!-- Subtype Input Section -->
            <div class="mb-3">
                <label for="subtype" class="form-label">Subtype</label>
                <div class="position-relative">
                    <input id="subtype" type="text" class="form-control rounded-pill pe-5 <?= form_error('subtype') ? 'is-invalid' : '' ?>" name="subtype" placeholder="5/2 WAY 24V DC" value="<?= set_value('subtype'); ?>" onkeyup="toggleClear('subtype', 'clear-button-subtype')" autocomplete="off">
                    <img src="<?= base_url('assets/img/delete.png'); ?>" alt="delete" class="action-button clear-button" id="clear-button-subtype" onclick="clearInput('subtype', 'clear-button-subtype')" aria-hidden="true">
                    <?= form_error('subtype', "<div class='invalid-feedback'>", "</div>"); ?>
                </div>
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
     */
    const config = {
        subtype: 'clear-button-subtype',
        min_stock: 'clear-button-min_stock'
    };

    /**
     * Toggle clear button visibility.
     */
    function toggleClear(inputId, clearButtonId) {
        const input = document.getElementById(inputId);
        const clearButton = document.getElementById(clearButtonId);
        if (input && clearButton) {
            clearButton.style.display = input.value ? 'block' : 'none';
        }
    }

    /**
     * Clear input field.
     */
    function clearInput(inputId, clearButtonId) {
        const input = document.getElementById(inputId);
        const clearButton = document.getElementById(clearButtonId);
        if (input) {
            input.value = '';
            input.focus();
        }
        if (clearButton) {
            clearButton.style.display = 'none';
        }
    }

    /**
     * Initialize clear buttons on page load.
     */
    document.addEventListener('DOMContentLoaded', function() {
        for (const [inputId, clearButtonId] of Object.entries(config)) {
            toggleClear(inputId, clearButtonId);
        }
    });
</script>