<!-- Main Content Card -->
<div class="card mx-auto rounded-5 shadow border-0 mb-5" style="margin-top: 5rem; max-width: 95%;">
    <!-- Card Header with Title and Action -->
    <div class="card-header bg-white border-bottom px-lg-5 px-4 py-4 rounded-top-5">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h3 class="m-0 mb-1">Pilih Type Solenoid</h3>
                <p class="text-muted mb-0">Pilih sebuah type untuk melihat daftar atau menambah solenoid baru pada type tersebut.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="<?= site_url('solenoid') . '?clear_type=1'; ?>" class="btn btn-secondary rounded-pill">Lihat Semua</a>
                <a href="<?= site_url('solenoid_type'); ?>" class="btn btn-primary rounded-pill px-4">Kelola Type</a>
            </div>
        </div>
    </div>

    <!-- Card Body with Type Grid -->
    <div class="card-body px-lg-5 px-4 py-4">
        <?php if (empty($type_options)) : ?>
            <div class="alert alert-info">Tidak ada type solenoid tersedia.</div>
        <?php else : ?>
            <div class="row solenoid-type-grid row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
                <?php foreach ($type_options as $row) :
                    $type = $row['type'] ?? '';
                    $imgUrl = $row['image_url'] ?? base_url('assets/img/placeholder-image.svg');
                    $typeSafe = htmlspecialchars($type, ENT_QUOTES, 'UTF-8');
                ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm rounded-4">
                            <div class="solenoid-thumb">
                                <img src="<?= htmlspecialchars($imgUrl, ENT_QUOTES, 'UTF-8') ?>" alt="<?= $typeSafe ?>" class="img-fluid">
                            </div>
                            <div class="card-body text-center">
                                <h5 class="card-title solenoid-type-label mb-2"><?= $typeSafe ?></h5>
                                <div class="d-grid gap-2">
                                    <a href="<?= site_url('solenoid') . '?type=' . urlencode($type); ?>" class="btn btn-outline-primary btn-sm">Lihat Daftar</a>
                                    <a href="<?= site_url('solenoid/add') . '?type=' . urlencode($type); ?>" class="btn btn-primary btn-sm">Tambah Baru</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    /* Additional solenoid type-specific styles */
    .solenoid-type-label {
        font-weight: 600;
        letter-spacing: .2px;
        color: #495057;
    }

    .solenoid-type-grid a:focus .card {
        outline: 3px solid rgba(0, 123, 255, 0.12);
        border-color: #007bff;
    }

    .solenoid-type-grid a {
        text-decoration: none !important;
    }

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

<!-- Small accessibility tweak: ensure keyboard focus shows card state -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.solenoid-type-grid a').forEach(function(a) {
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