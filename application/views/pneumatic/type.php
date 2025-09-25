<!-- Main Content Card -->
<div class="card mx-auto rounded-5 shadow border-0 mb-5" style="margin-top: 5rem; max-width: 95%;">
    <!-- Card Header with Title and Action -->
    <div class="card-header bg-white border-bottom px-lg-5 px-4 py-4 rounded-top-5">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h3 class="m-0 mb-1">Pilih Type Pneumatic</h3>
                <p class="text-muted mb-0">Pilih sebuah type untuk melihat daftar atau menambah pneumatic baru pada type tersebut.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="<?= site_url('pneumatic') . '?clear_type=1'; ?>" class="btn btn-secondary rounded-pill">Lihat Semua</a>
                <a href="<?= site_url('pneumatic_type'); ?>" class="btn btn-primary rounded-pill px-4">Kelola Type</a>
            </div>
        </div>
    </div>

    <!-- Card Body with Type Grid -->
    <div class="card-body px-lg-5 px-4 py-4">
        <?php if (empty($type_options)) : ?>
            <div class="alert alert-info">Tidak ada type pneumatic tersedia.</div>
        <?php else : ?>
            <div class="row pneumatic-type-grid row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
                <?php foreach ($type_options as $row) :
                    $type = $row['type'] ?? '';
                    $imgUrl = $row['image_url'] ?? base_url('assets/img/placeholder-image.svg');
                    $typeSafe = htmlspecialchars($type, ENT_QUOTES, 'UTF-8');
                ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm rounded-4">
                            <div class="pneumatic-thumb">
                                <img src="<?= htmlspecialchars($imgUrl, ENT_QUOTES, 'UTF-8') ?>" alt="<?= $typeSafe ?>" class="img-fluid">
                            </div>
                            <div class="card-body text-center">
                                <h5 class="card-title pneumatic-type-label mb-2"><?= $typeSafe ?></h5>
                                <div class="d-grid gap-2">
                                    <a href="<?= site_url('pneumatic') . '?type=' . urlencode($type); ?>" class="btn btn-outline-primary btn-sm">Lihat Daftar</a>
                                    <a href="<?= site_url('pneumatic/add') . '?type=' . urlencode($type); ?>" class="btn btn-primary btn-sm">Tambah Baru</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
</div>

<style>
    /* Additional pneumatic type-specific styles */
    .pneumatic-type-label {
        font-weight: 600;
        letter-spacing: .2px;
        color: #495057;
    }

    .pneumatic-type-grid a:focus .card {
        outline: 3px solid rgba(0, 123, 255, 0.12);
        border-color: #007bff;
    }

    .pneumatic-type-grid a {
        text-decoration: none !important;
    }
</style><!-- Small accessibility tweak: ensure keyboard focus shows card state -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.pneumatic-type-grid a').forEach(function(a) {
            a.addEventListener('focus', function() {
                var c = a.querySelector('.card');
                if (c) c.classList.add('focus');
            });
            a.addEventListener('blur', function() {
                var c = a.querySelector('.card');
                if (c) c.classList.remove('focus');
            });
        });
    });
</script>