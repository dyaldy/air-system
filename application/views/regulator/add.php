<?php if ($this->session->flashdata('action')) : ?>
    <div class="cust-notification m-3">
        <div class="alert alert-<?= $this->session->flashdata('action')[0]; ?> alert-dismissible fade show" id="notification" role="alert">
            <?= $this->session->flashdata('action')[1]; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
<?php endif; ?>

<div class="card mx-auto rounded-5 shadow border-0 mb-5" style="margin-top: 5rem; max-width: 600px;">
    <div class="card-header bg-white border-bottom px-4 py-4">
        <h3 class="m-0">Tambah Regulator</h3>
    </div>

    <div class="card-body p-4">
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> Regulator ID akan dibuat otomatis dengan format: <strong>reg-{type}</strong><br>
            Contoh: Type "AR2000" → ID akan menjadi "reg-ar2000"
        </div>

        <?php echo validation_errors('<div class="alert alert-danger">', '</div>'); ?>

        <form action="<?= site_url('regulator/add'); ?>" method="post">
            <div class="mb-3">
                <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="type" name="type" value="<?= set_value('type'); ?>" required maxlength="20">
                <small class="text-muted">Contoh: AR2000, AR3000, dll</small>
            </div>

            <div class="mb-3">
                <label for="min_stock" class="form-label">Minimum Stock <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="min_stock" name="min_stock" value="<?= set_value('min_stock', 5); ?>" required min="0">
                <small class="text-muted">Jumlah minimum stock untuk peringatan</small>
            </div>

            <div class="d-flex gap-2 justify-content-end">
                <a href="<?= site_url('regulator'); ?>" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script src="<?= base_url('assets/js/forminput.js'); ?>"></script>