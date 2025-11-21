<!-- Main Content Card -->
<div class="card mx-auto rounded-5 shadow border-0 mb-5" style="margin-top: 5rem; max-width: 95%;">
    <!-- Card Header with Title and Add Button -->
    <div class="card-header bg-white border-bottom px-lg-5 px-4 py-4 rounded-top-5">
        <div class="d-flex align-items-center justify-content-between">
            <h3 class="m-0"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h3>
            <div class="d-flex align-items-center gap-2">
                <a href="<?= site_url('solenoid/type'); ?>" class="btn btn-secondary rounded-pill">Kembali</a>
                <a class="btn btn-primary rounded-pill px-4" href="<?= site_url('solenoid_type/add') ?>">Tambah Type</a>
            </div>
        </div>
    </div>

    <!-- Card Body with Content -->
    <div class="card-body px-lg-5 px-4 py-4">
        <?php if (!empty($types)): ?>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
                <?php foreach ($types as $t): ?>
                    <?php $img = !empty($t['image']) ? base_url('assets/img/solenoid_types/' . $t['image']) : base_url('assets/img/placeholder-image.svg'); ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm rounded-4">
                            <div class="solenoid-thumb">
                                <img src="<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($t['type'], ENT_QUOTES, 'UTF-8') ?>" class="img-fluid">
                            </div>
                            <div class="card-body text-center">
                                <h5 class="card-title mb-2"><?= htmlspecialchars($t['type'], ENT_QUOTES, 'UTF-8') ?></h5>

                                <?php if ($t['is_in_use']): ?>
                                    <!-- Show usage count if type is in use -->
                                    <p class="text-muted mb-2">
                                        <i class="fas fa-info-circle"></i>
                                        Digunakan pada <?= $t['usage_count'] ?> solenoid
                                    </p>

                                    <!-- Edit button only (no delete for used types) -->
                                    <div class="d-grid gap-2">
                                        <a href="<?= site_url('solenoid_type/edit/' . $t['id']) ?>" class="btn btn-outline-primary btn-sm rounded-pill">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <button class="btn btn-outline-secondary btn-sm rounded-pill" disabled title="Type sedang digunakan">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <!-- Full edit and delete options for unused types -->
                                    <p class="text-muted mb-2">
                                        <i class="fas fa-check-circle text-success"></i>
                                        Tidak digunakan
                                    </p>

                                    <div class="d-grid gap-2">
                                        <a href="<?= site_url('solenoid_type/edit/' . $t['id']) ?>" class="btn btn-outline-primary btn-sm rounded-pill">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="<?= site_url('solenoid_type/delete/' . $t['id']) ?>"
                                            class="btn btn-outline-danger btn-sm rounded-pill"
                                            onclick="return confirm('Apakah Anda yakin ingin menghapus type <?= htmlspecialchars($t['type'], ENT_QUOTES, 'UTF-8') ?>?')">
                                            <i class="fas fa-trash"></i> Hapus
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center">
                <h4>Belum Ada Type Solenoid</h4>
                <p class="mb-3">Tambahkan type solenoid pertama untuk mulai mengelola data solenoid.</p>
                <a href="<?= site_url('solenoid_type/add') ?>" class="btn btn-primary rounded-pill px-4">Tambah Type Solenoid</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .solenoid-thumb {
        height: 200px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        border-radius: 1rem 1rem 0 0;
        background-color: #f8f9fa;
    }

    .solenoid-thumb img {
        max-height: 100%;
        max-width: 100%;
        object-fit: cover;
    }

    .card:hover {
        transform: translateY(-2px);
        transition: transform 0.2s ease-in-out;
    }
</style>