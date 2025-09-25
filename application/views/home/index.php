<!-- Flash Notification -->
<?php if ($this->session->flashdata('action')) : ?>
    <div class="cust-notification m-3">
        <div class="alert alert-<?= $this->session->flashdata('action')[0]; ?> alert-dismissible fade show" id="notification" role="alert">
            <?= $this->session->flashdata('action')[1]; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
<?php endif; ?>

<!-- Main Dashboard -->
<div class="container-fluid" style="margin-top: 5rem;">
    <!-- Welcome Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4" style="background: linear-gradient(135deg, #004274 0%, #0056a6 100%);">
                <div class="card-body text-white p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-1">Selamat Datang, <strong><?= htmlspecialchars($user_name); ?></strong>!</h2>
                            <p class="mb-0 opacity-90">Air System - Storage Management Workshop Automation</p>
                            <small class="opacity-75">Apparel One Indonesia</small>
                        </div>
                        <div class="col-md-4 text-end">
                            <img src="<?= base_url('assets/img/logo-aoi.png'); ?>" alt="AOI Logo" class="img-fluid mb-2" style="max-height: 60px;">
                            <div class="text-white opacity-75 small">
                                <div><i class="fas fa-calendar-alt me-1"></i><?= $current_date; ?></div>
                                <div><i class="fas fa-clock me-1"></i><span id="current-time"><?= $current_time; ?></span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row mb-4 g-3">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 card-hover h-100">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-users fa-3x text-primary"></i>
                    </div>
                    <h2 class="mb-1 text-primary"><?= number_format($total_users ?? 0); ?></h2>
                    <h6 class="mb-1 text-dark">Total Pengguna</h6>
                    <small class="text-muted">Pengguna terdaftar</small>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 card-hover h-100">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-cog fa-3x text-success"></i>
                    </div>
                    <h2 class="mb-1 text-success"><?= number_format($total_pneumatics ?? 0); ?></h2>
                    <h6 class="mb-1 text-dark">Total Pneumatic</h6>
                    <small class="text-muted">Komponen pneumatic</small>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 card-hover h-100">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-puzzle-piece fa-3x text-warning"></i>
                    </div>
                    <h2 class="mb-1 text-warning"><?= number_format($total_fittings ?? 0); ?></h2>
                    <h6 class="mb-1 text-dark">Total Fitting</h6>
                    <small class="text-muted">Komponen fitting</small>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 card-hover h-100">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-warehouse fa-3x text-info"></i>
                    </div>
                    <h2 class="mb-1 text-info"><?= number_format($total_storage_items ?? 0); ?></h2>
                    <h6 class="mb-1 text-dark">Item Penyimpanan</h6>
                    <small class="text-muted">Total item tersimpan</small>
                </div>
            </div>
        </div>
    </div>
    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-transparent border-0 pt-4 pb-2">
                    <h4 class="mb-0">Aksi Cepat</h4>
                    <p class="text-muted mb-0">Akses cepat ke fitur utama sistem</p>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-3 col-sm-6">
                            <a href="<?= site_url('user'); ?>" class="text-decoration-none">
                                <div class="card border-2 border-primary bg-primary bg-opacity-10 h-100 card-hover">
                                    <div class="card-body text-center p-3">
                                        <i class="fas fa-users fa-2x text-primary mb-2"></i>
                                        <h6 class="mb-0 text-dark">Kelola Pengguna</h6>
                                        <small class="text-muted">Tambah, edit pengguna</small>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <div class="col-md-3 col-sm-6">
                            <a href="<?= site_url('pneumatic/type'); ?>" class="text-decoration-none">
                                <div class="card border-2 border-success bg-success bg-opacity-10 h-100 card-hover">
                                    <div class="card-body text-center p-3">
                                        <i class="fas fa-cog fa-2x text-success mb-2"></i>
                                        <h6 class="mb-0 text-dark">Kelola Pneumatic</h6>
                                        <small class="text-muted">Komponen pneumatic</small>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <div class="col-md-3 col-sm-6">
                            <a href="<?= site_url('fitting/type'); ?>" class="text-decoration-none">
                                <div class="card border-2 border-warning bg-warning bg-opacity-10 h-100 card-hover">
                                    <div class="card-body text-center p-3">
                                        <i class="fas fa-puzzle-piece fa-2x text-warning mb-2"></i>
                                        <h6 class="mb-0 text-dark">Kelola Fitting</h6>
                                        <small class="text-muted">Komponen fitting</small>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <div class="col-md-3 col-sm-6">
                            <a href="<?= site_url('storage'); ?>" class="text-decoration-none">
                                <div class="card border-2 border-info bg-info bg-opacity-10 h-100 card-hover">
                                    <div class="card-body text-center p-3">
                                        <i class="fas fa-warehouse fa-2x text-info mb-2"></i>
                                        <h6 class="mb-0 text-dark">Kelola Penyimpanan</h6>
                                        <small class="text-muted">Inventaris barang</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Overview -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-transparent border-0 pt-4 pb-2">
                    <h4 class="mb-0">Ringkasan Sistem</h4>
                    <p class="text-muted mb-0">Informasi utama sistem Air System</p>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center p-3 rounded-3 bg-light">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-boxes fa-2x text-primary"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">Total Komponen</h6>
                                    <h4 class="mb-0 text-primary"><?= number_format(($total_pneumatics + $total_fittings) ?? 0); ?></h4>
                                    <small class="text-muted">Pneumatic + Fitting</small>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex align-items-center p-3 rounded-3 bg-light">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-chart-pie fa-2x text-success"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">Tingkat Penyimpanan</h6>
                                    <h4 class="mb-0 text-success"><?= round((($total_storage_items ?? 0) / max(1, ($total_pneumatics + $total_fittings))) * 100, 1); ?>%</h4>
                                    <small class="text-muted">Barang tersimpan</small>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex align-items-center p-3 rounded-3 bg-light">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-user-cog fa-2x text-info"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">Komponen per Pengguna</h6>
                                    <h4 class="mb-0 text-info"><?= $total_users > 0 ? round(($total_pneumatics + $total_fittings) / $total_users, 1) : 0; ?></h4>
                                    <small class="text-muted">Rata-rata per user</small>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex align-items-center p-3 rounded-3 bg-light">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">Belum Tersimpan</h6>
                                    <h4 class="mb-0 text-warning"><?= max(0, ($total_pneumatics + $total_fittings) - ($total_storage_items ?? 0)); ?></h4>
                                    <small class="text-muted">Item belum disimpan</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-transparent border-0 pt-4 pb-2">
                    <h4 class="mb-0">Menu Utama</h4>
                    <p class="text-muted mb-0">Navigasi cepat</p>
                </div>
                <div class="card-body p-4">
                    <div class="list-group list-group-flush">
                        <a href="<?= site_url('storage/reports'); ?>" class="list-group-item list-group-item-action border-0 rounded-3 mb-2">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-chart-bar text-primary me-3"></i>
                                <div>
                                    <h6 class="mb-0">Laporan Sistem</h6>
                                    <small class="text-muted">Laporan penyimpanan</small>
                                </div>
                            </div>
                        </a>

                        <a href="<?= site_url('pneumatic_type'); ?>" class="list-group-item list-group-item-action border-0 rounded-3 mb-2">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-tags text-success me-3"></i>
                                <div>
                                    <h6 class="mb-0">Kelola Type Pneumatic</h6>
                                    <small class="text-muted">Manajemen kategori</small>
                                </div>
                            </div>
                        </a>

                        <a href="<?= site_url('fitting_type'); ?>" class="list-group-item list-group-item-action border-0 rounded-3 mb-2">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-layer-group text-warning me-3"></i>
                                <div>
                                    <h6 class="mb-0">Kelola Type Fitting</h6>
                                    <small class="text-muted">Manajemen kategori</small>
                                </div>
                            </div>
                        </a>

                        <div class="list-group-item border-0 rounded-3 bg-light">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-tools text-muted me-3"></i>
                                <div>
                                    <h6 class="mb-0 text-muted">Fitur Lainnya</h6>
                                    <small class="text-muted">Dalam pengembangan</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Footer -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 bg-light">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8 text-center text-md-start">
                            <h6 class="mb-1 text-dark">Air System - Storage Management Workshop Automation</h6>
                            <p class="text-muted mb-0 small">Sistem manajemen komponen pneumatic dan fitting untuk Apparel One Indonesia | Version 2.0</p>
                        </div>
                        <div class="col-md-4 text-center text-md-end">
                            <small class="text-muted">Operation Excellence 2025</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom CSS for improved design -->
<style>
    .card-hover {
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .card-hover:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15) !important;
    }

    .card-hover:hover .card-body i {
        transform: scale(1.1);
        transition: transform 0.3s ease;
    }

    .list-group-item-action:hover {
        background-color: #f8f9fa;
        transform: translateX(5px);
        transition: all 0.3s ease;
    }

    .bg-gradient-primary {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
    }
</style>

<!-- Real-time clock script -->
<script>
    function updateClock() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('id-ID', {
            hour12: false,
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
        document.getElementById('current-time').textContent = timeString;
    }

    // Update clock immediately and then every second
    updateClock();
    setInterval(updateClock, 1000);

    // Add smooth scroll for better UX
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });
        });
    });
</script>