<!-- Card -->
<div class="card mx-auto rounded-5 shadow border-0 mb-5 w-75" style="margin-top: 5rem;">
    <!-- Card Header -->
    <div class="card-header bg-transparent border-0 px-4 pt-3 pb-1">
        <!-- Container -->
        <div class="d-flex align-items-center justify-content-between">
            <!-- Title -->
            <h4 class="m-0">Edit Solenoid Air System</h4>

            <!-- Back Button-->
            <a href="<?= site_url('solenoid'); ?>" class="btn btn-secondary rounded-pill">Kembali</a>
        </div>
    </div>

    <!-- Card Body -->
    <div class="card-body p-4 pt-3">
        <form action="" method="post">
            <!-- Solenoid ID Display Section (Read-only) -->
            <div class="mb-2">
                <label for="solenoid_id" class="form-label">Solenoid ID (Auto-generated)</label>
                <input id="solenoid_id" type="text" class="form-control rounded-pill" name="solenoid_id" value="<?= $solenoid['solenoid_id'] ?>" readonly>
                <small class="text-muted">ID akan diperbarui secara otomatis jika type atau subtype diubah</small>
            </div>

            <!-- Type Dropdown Section -->
            <div class="mb-2">
                <label for="type" class="form-label">Type</label>
                <div class="position-relative">
                    <select id="type" name="type" class="form-select rounded-pill <?= form_error('type') ? 'is-invalid' : '' ?>">
                        <option value="">Pilih Type...</option>
                        <?php foreach ($solenoid_types as $stype): ?>
                            <option value="<?= html_escape($stype['type']) ?>" <?= set_select('type', $stype['type'], (set_value('type') ? set_value('type') : $solenoid['type']) == $stype['type']) ?>>
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
                    <input id="subtype" type="text" class="form-control rounded-pill pe-5 <?= form_error('subtype') ? 'is-invalid' : '' ?>" name="subtype" placeholder="5/2 WAY 24V DC" value="<?= set_value('subtype') ? set_value('subtype') : $solenoid['subtype']; ?>" onkeyup="toggleClear('subtype', 'clear-button-subtype')" autocomplete="off">
                    <img src="<?= base_url('assets/img/delete.png'); ?>" alt="delete" class="action-button clear-button" id="clear-button-subtype" onclick="clearInput('subtype', 'clear-button-subtype')" aria-hidden="true">
                    <?= form_error('subtype', "<div class='invalid-feedback'>", "</div>"); ?>
                </div>
            </div>

            <!-- Minimum Stock Input Section -->
            <div class="mb-3">
                <label for="min_stock" class="form-label">Minimum Stock (Opsional)</label>
                <div class="position-relative">
                    <input id="min_stock" type="number" class="form-control rounded-pill pe-5 <?= form_error('min_stock') ? 'is-invalid' : '' ?>" name="min_stock" placeholder="10" value="<?= set_value('min_stock') ? set_value('min_stock') : $solenoid['min_stock']; ?>" onkeyup="toggleClear('min_stock', 'clear-button-min_stock')" autocomplete="off" min="0">
                    <img src="<?= base_url('assets/img/delete.png'); ?>" alt="delete" class="action-button clear-button" id="clear-button-min_stock" onclick="clearInput('min_stock', 'clear-button-min_stock')" aria-hidden="true">
                    <?= form_error('min_stock', "<div class='invalid-feedback'>", "</div>"); ?>
                </div>
                <small class="form-text text-muted">Kosongkan jika tidak ingin menggunakan minimum stock</small>
            </div>

            <!-- Save and Delete Button -->
            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-primary rounded-pill">Simpan</button>
                <a href="<?= site_url('solenoid/delete/') . base64_encode($solenoid['solenoid_id']); ?>" onclick="return confirm('Apakah anda yakin ingin menghapus solenoid ini?')" class="btn btn-danger rounded-pill">Hapus</a>
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