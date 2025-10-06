<!-- Card -->
<div class="card mx-auto rounded-5 shadow border-0 mb-5 w-75" style="margin-top: 5rem;">
    <!-- Card Header -->
    <div class="card-header bg-transparent border-0 px-4 pt-3 pb-1">
        <!-- Container -->
        <div class="d-flex align-items-center justify-content-between">
            <!-- Title -->
            <h4 class="m-0">Tambah Pneumatic Air System</h4>

            <!-- Back Button-->
            <a href="<?= site_url('pneumatic'); ?>" class="btn btn-secondary rounded-pill">Kembali</a>
        </div>
    </div>

    <!-- Card Body -->
    <div class="card-body p-4 pt-3">
        <form action="" method="post">
            <!-- Note about auto-generated ID -->
            <div class="mb-3">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Pneumatic ID akan dibuat otomatis berdasarkan format: pnm-type-bore-stroke
                </div>
            </div>

            <!-- Type Dropdown Section -->
            <div class="mb-2">
                <label for="type" class="form-label">Type</label>
                <div class="position-relative">
                    <select id="type" name="type" class="form-select rounded-pill <?= form_error('type') ? 'is-invalid' : '' ?>">
                        <option value="">Pilih Type...</option>
                        <?php foreach ($pneumatic_types as $ptype): ?>
                            <?php
                            $isSelected = false;
                            if (set_value('type')) {
                                $isSelected = (set_value('type') == $ptype['type']);
                            } elseif (isset($preselected_type) && !empty($preselected_type)) {
                                $isSelected = (strtoupper($preselected_type) == $ptype['type']);
                            }
                            ?>
                            <option value="<?= html_escape($ptype['type']) ?>" <?= $isSelected ? 'selected' : '' ?>>
                                <?= html_escape($ptype['type']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?= form_error('type', "<div class='invalid-feedback'>", "</div>"); ?>
                </div>
            </div>

            <!-- Bore Input Section -->
            <div class="mb-2">
                <label for="bore" class="form-label">Bore (mm)</label>
                <div class="position-relative">
                    <input id="bore" type="number" class="form-control rounded-pill pe-5 <?= form_error('bore') ? 'is-invalid' : '' ?>" name="bore" placeholder="25" value="<?= set_value('bore'); ?>" onkeyup="toggleClear('bore', 'clear-button-bore')" autocomplete="off" min="1">
                    <img src="<?= base_url('assets/img/delete.png'); ?>" alt="delete" class="action-button clear-button" id="clear-button-bore" onclick="clearInput('bore', 'clear-button-bore')" aria-hidden="true">
                    <?= form_error('bore', "<div class='invalid-feedback'>", "</div>"); ?>
                </div>
            </div>

            <!-- Stroke Input Section -->
            <div class="mb-3">
                <label for="stroke" class="form-label">Stroke (mm)</label>
                <div class="position-relative">
                    <input id="stroke" type="number" class="form-control rounded-pill pe-5 <?= form_error('stroke') ? 'is-invalid' : '' ?>" name="stroke" placeholder="100" value="<?= set_value('stroke'); ?>" onkeyup="toggleClear('stroke', 'clear-button-stroke')" autocomplete="off" min="1">
                    <img src="<?= base_url('assets/img/delete.png'); ?>" alt="delete" class="action-button clear-button" id="clear-button-stroke" onclick="clearInput('stroke', 'clear-button-stroke')" aria-hidden="true">
                    <?= form_error('stroke', "<div class='invalid-feedback'>", "</div>"); ?>
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
     * 
     * @type {Array<{id: string, button: string}>}
     */
    window.inputConfigs = [{
            id: 'brand',
            button: 'clear-button-brand'
        },
        {
            id: 'bore',
            button: 'clear-button-bore'
        },
        {
            id: 'stroke',
            button: 'clear-button-stroke'
        },
        {
            id: 'min_stock',
            button: 'clear-button-min_stock'
        }
    ];
</script>
<script src="<?= base_url('assets/js/forminput.js'); ?>"></script>