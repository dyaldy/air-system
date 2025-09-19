<!-- Main Content Card -->
<div class="card mx-auto rounded-5 shadow border-0 mb-5" style="margin-top: 5rem; max-width: 95%;">
    <!-- Card Header with Title and Add Button -->
    <div class="card-header bg-white border-bottom px-lg-5 px-4 py-4">
        <div class="d-flex align-items-center justify-content-between">
            <h3 class="m-0"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h3>
            <a class="btn btn-primary rounded-pill px-4" href="<?= site_url('pneumatic_type/add') ?>">Tambah Type</a>
        </div>
    </div>

    <!-- Card Body with Content -->
    <div class="card-body px-lg-5 px-4 py-4">
        <?php if (!empty($types)): ?>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
                <?php foreach ($types as $t): ?>
                    <?php $img = !empty($t['image']) ? base_url('assets/img/pneumatic_types/' . $t['image']) : base_url('assets/img/pneumatic-default.jpg'); ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm">
                            <div class="pneumatic-thumb">
                                <img src="<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($t['type'], ENT_QUOTES, 'UTF-8') ?>" class="img-fluid">
                            </div>
                            <div class="card-body text-center">
                                <h5 class="card-title mb-2"><?= htmlspecialchars($t['type'], ENT_QUOTES, 'UTF-8') ?></h5>
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="<?= site_url('pneumatic_type/edit/' . $t['id']) ?>" class="btn btn-sm btn-outline-warning">Edit</a>
                                    <a href="<?= site_url('pneumatic_type/delete/' . $t['id']) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus type ini?')">Hapus</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">Tidak ada type.</div>
        <?php endif; ?>
    </div>
</div>