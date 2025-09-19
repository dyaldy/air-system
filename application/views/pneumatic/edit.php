<!-- Card -->
<div class="card mx-auto rounded-5 shadow border-0 mb-5 w-75" style="margin-top: 5rem;">
    <!-- Card Header -->
    <div class="card-header bg-transparent border-0 px-4 pt-3 pb-1">
        <!-- Container -->
        <div class="d-flex align-items-center justify-content-between">
            <!-- Title -->
            <h4 class="m-0">Edit Pneumatic Air System</h4>

            <!-- Back Button-->
            <a href="<?= site_url('pneumatic'); ?>" class="btn btn-secondary rounded-pill">Kembali</a>
        </div>
    </div>

    <!-- Card Body -->
    <div class="card-body p-4 pt-3">
        <form action="" method="post">
            <!-- Pneumatic ID Display Section (Read-only) -->
            <div class="mb-2">
                <label for="pneumatic_id" class="form-label">Pneumatic ID (Auto-generated)</label>
                <input id="pneumatic_id" type="text" class="form-control rounded-pill" name="pneumatic_id" value="<?= $pneumatic['pneumatic_id'] ?>" readonly>
                <small class="text-muted">ID akan diperbarui secara otomatis jika brand, type, bore, atau stroke diubah</small>
            </div>

            <!-- Brand Input Section -->
            <div class="mb-2">
                <label for="brand" class="form-label">Brand</label>
                <div class="position-relative">
                    <input id="brand" type="text" class="form-control rounded-pill pe-5 <?= form_error('brand') ? 'is-invalid' : '' ?>" name="brand" placeholder="SMC" value="<?= set_value('brand') ? set_value('brand') : $pneumatic['brand']; ?>" onkeyup="toggleClear('brand', 'clear-button-brand')" autocomplete="off">
                    <img src="<?= base_url('assets/img/delete.png'); ?>" alt="delete" class="action-button clear-button" id="clear-button-brand" onclick="clearInput('brand', 'clear-button-brand')" style="top: 7px; <?= form_error('brand') ? 'right: 1.9rem;' : 'right: 1.25rem;' ?>">
                    <?= form_error('brand', "<div class='invalid-feedback'>", "</div>"); ?>
                </div>
            </div>

            <!-- Type Dropdown Section -->
            <div class="mb-2">
                <label for="type" class="form-label">Type</label>
                <div class="position-relative">
                    <select id="type" name="type" class="form-select rounded-pill <?= form_error('type') ? 'is-invalid' : '' ?>">
                        <option value="">Pilih Type...</option>
                        <?php foreach ($pneumatic_types as $ptype): ?>
                            <option value="<?= html_escape($ptype['type']) ?>" <?= set_select('type', $ptype['type'], (set_value('type') ? set_value('type') : $pneumatic['type']) == $ptype['type']) ?>>
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
                    <input id="bore" type="number" class="form-control rounded-pill pe-5 <?= form_error('bore') ? 'is-invalid' : '' ?>" name="bore" placeholder="25" value="<?= set_value('bore') ? set_value('bore') : $pneumatic['bore']; ?>" onkeyup="toggleClear('bore', 'clear-button-bore')" autocomplete="off" min="1">
                    <img src="<?= base_url('assets/img/delete.png'); ?>" alt="delete" class="action-button clear-button" id="clear-button-bore" onclick="clearInput('bore', 'clear-button-bore')" style="top: 7px; <?= form_error('bore') ? 'right: 1.9rem;' : 'right: 1.25rem;' ?>">
                    <?= form_error('bore', "<div class='invalid-feedback'>", "</div>"); ?>
                </div>
            </div>

            <!-- Stroke Input Section -->
            <div class="mb-3">
                <label for="stroke" class="form-label">Stroke (mm)</label>
                <div class="position-relative">
                    <input id="stroke" type="number" class="form-control rounded-pill pe-5 <?= form_error('stroke') ? 'is-invalid' : '' ?>" name="stroke" placeholder="100" value="<?= set_value('stroke') ? set_value('stroke') : $pneumatic['stroke']; ?>" onkeyup="toggleClear('stroke', 'clear-button-stroke')" autocomplete="off" min="1">
                    <img src="<?= base_url('assets/img/delete.png'); ?>" alt="delete" class="action-button clear-button" id="clear-button-stroke" onclick="clearInput('stroke', 'clear-button-stroke')" style="top: 7px; <?= form_error('stroke') ? 'right: 1.9rem;' : 'right: 1.25rem;' ?>">
                    <?= form_error('stroke', "<div class='invalid-feedback'>", "</div>"); ?>
                </div>
            </div>

            <!-- Save and Delete Button -->
            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-primary rounded-pill">Simpan</button>
                <a href="<?= site_url('pneumatic/delete/') . urlencode($pneumatic['pneumatic_id']); ?>" onclick="return confirm('Apakah anda yakin ingin menghapus pneumatic ini?')" class="btn btn-danger rounded-pill">Hapus</a>
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
        }
    ];
</script>
<script src="<?= base_url('assets/js/forminput.js'); ?>"></script>