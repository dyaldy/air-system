/**
* Pneumatic type selection view
* Expects: $type_options (array of rows with keys 'type' and optional 'image')
*/
?>
<?php
/**
 * Pneumatic type selection view (improved UI)
 * Expects: $type_options (array of rows with keys 'type' and 'image_url')
 */
?>

<!-- Main Content Card -->
<div class="card mx-auto rounded-5 shadow border-0 mb-5" style="margin-top: 5rem; max-width: 95%;">
    <!-- Card Header with Title and Action -->
    <div class="card-header bg-white border-bottom px-lg-5 px-4 py-4">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h3 class="m-0 mb-1">Pilih Type Pneumatic</h3>
                <p class="text-muted mb-0">Klik sebuah type untuk melihat daftar pneumatic pada type tersebut.</p>
            </div>
            <div>
                <a href="<?= site_url('pneumatic') . '?clear_type=1'; ?>" class="btn btn-outline-secondary rounded-pill px-4">Lihat Semua</a>
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
                    $imgUrl = $row['image_url'] ?? base_url('assets/img/pneumatic-default.jpg');
                    $typeSafe = htmlspecialchars($type, ENT_QUOTES, 'UTF-8');
                ?>
                    <div class="col">
                        <a href="<?= site_url('pneumatic') . '?type=' . urlencode($type); ?>" class="text-decoration-none">
                            <div class="card h-100 shadow-sm">
                                <div class="pneumatic-thumb">
                                    <img src="<?= htmlspecialchars($imgUrl, ENT_QUOTES, 'UTF-8') ?>" alt="<?= $typeSafe ?>" class="img-fluid">
                                </div>
                                <div class="card-body text-center">
                                    <h5 class="card-title pneumatic-type-label mb-1"><?= $typeSafe ?></h5>
                                    <p class="text-muted small mb-0">Klik untuk lihat daftar</p>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
</div>

<style>
    /* Scoped styles for pneumatic type pages */
    .pneumatic-thumb {
        width: 100%;
        height: 160px;
        overflow: hidden;
        background: #f5f6f7;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .pneumatic-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .pneumatic-type-grid .card {
        border-radius: 0.6rem;
        overflow: hidden;
        transition: transform .12s ease, box-shadow .12s ease;
    }

    .pneumatic-type-grid .card:hover {
        transform: translateY(-6px);
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.10);
    }

    .pneumatic-type-label {
        font-weight: 600;
        letter-spacing: .2px;
    }

    .pneumatic-type-grid a:focus .card {
        outline: 3px solid rgba(0, 123, 255, 0.12);
    }
</style>

<!-- Small accessibility tweak: ensure keyboard focus shows card state -->
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