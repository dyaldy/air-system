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
        <h3 class="m-0">Edit Regulator</h3>
    </div>

    <div class="card-body p-4">
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> Jika type diubah, Regulator ID akan diperbarui otomatis.<br>
            Format: <strong>reg-{type}</strong>
        </div>

        <?php echo validation_errors('<div class="alert alert-danger">', '</div>'); ?>

        <form action="<?= site_url('regulator/edit/' . $regulator['regulator_id']); ?>" method="post">
            <div class="mb-3">
                <label for="regulator_id" class="form-label">Regulator ID (Otomatis)</label>
                <input type="text" class="form-control" id="regulator_id" value="<?= $regulator['regulator_id']; ?>" disabled>
                <small class="text-muted">ID akan diperbarui otomatis berdasarkan type</small>
            </div>

            <div class="mb-3">
                <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="type" name="type" value="<?= set_value('type', $regulator['type']); ?>" required maxlength="20">
                <small class="text-muted">Contoh: AR2000, AR3000, dll</small>
            </div>

            <div class="mb-3">
                <label for="min_stock" class="form-label">Minimum Stock <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="min_stock" name="min_stock" value="<?= set_value('min_stock', $regulator['min_stock']); ?>" required min="0">
                <small class="text-muted">Jumlah minimum stock untuk peringatan</small>
            </div>

            <div class="d-flex gap-2 justify-content-between">
                <a href="<?= site_url('regulator/delete/' . $regulator['regulator_id']); ?>" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus regulator ini?');">Hapus</a>
                <div class="d-flex gap-2">
                    <a href="<?= site_url('regulator'); ?>" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="<?= base_url('assets/js/forminput.js'); ?>"></script>