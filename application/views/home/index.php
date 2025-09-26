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
<div class="container-fluid mb-5" style="margin-top: 5rem;">
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
    <!-- Charts Section -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-transparent border-0 pt-4 pb-2">
                    <h5 class="mb-0">Distribusi Komponen</h5>
                    <p class="text-muted small mb-0">Perbandingan pneumatic dan fitting</p>
                </div>
                <div class="card-body p-4">
                    <div style="position: relative; height: 300px;">
                        <canvas id="componentChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-transparent border-0 pt-4 pb-2">
                    <h5 class="mb-0">Status Penyimpanan</h5>
                    <p class="text-muted small mb-0">Tingkat penyimpanan komponen</p>
                </div>
                <div class="card-body p-4">
                    <div style="position: relative; height: 300px;">
                        <canvas id="storageChart"></canvas>
                    </div>
                    <div class="mt-3 text-center">
                        <small class="text-muted">
                            <?= $total_storage_items ?? 0; ?> dari <?= ($total_pneumatics + $total_fittings) ?? 0; ?> komponen tersimpan
                            (<?= round((($total_storage_items ?? 0) / max(1, ($total_pneumatics + $total_fittings))) * 100, 1); ?>%)
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-transparent border-0 pt-4 pb-2">
                    <h5 class="mb-0">Menu Utama</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-3 col-sm-6">
                            <a href="<?= site_url('user'); ?>" class="text-decoration-none">
                                <div class="card border-2 border-primary bg-primary bg-opacity-10 h-100 card-hover">
                                    <div class="card-body text-center p-3">
                                        <i class="fas fa-users fa-2x text-primary mb-2"></i>
                                        <h6 class="mb-0 text-dark">Pengguna</h6>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <div class="col-md-3 col-sm-6">
                            <a href="<?= site_url('pneumatic/type'); ?>" class="text-decoration-none">
                                <div class="card border-2 border-success bg-success bg-opacity-10 h-100 card-hover">
                                    <div class="card-body text-center p-3">
                                        <i class="fas fa-cog fa-2x text-success mb-2"></i>
                                        <h6 class="mb-0 text-dark">Pneumatic</h6>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <div class="col-md-3 col-sm-6">
                            <a href="<?= site_url('fitting/type'); ?>" class="text-decoration-none">
                                <div class="card border-2 border-warning bg-warning bg-opacity-10 h-100 card-hover">
                                    <div class="card-body text-center p-3">
                                        <i class="fas fa-puzzle-piece fa-2x text-warning mb-2"></i>
                                        <h6 class="mb-0 text-dark">Fitting</h6>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <div class="col-md-3 col-sm-6">
                            <a href="<?= site_url('storage'); ?>" class="text-decoration-none">
                                <div class="card border-2 border-info bg-info bg-opacity-10 h-100 card-hover">
                                    <div class="card-body text-center p-3">
                                        <i class="fas fa-warehouse fa-2x text-info mb-2"></i>
                                        <h6 class="mb-0 text-dark">Penyimpanan</h6>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Custom CSS and Scripts -->
<style>
    .card-hover {
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .card-hover:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15) !important;
    }
</style>

<script>
    // Real-time clock
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
    updateClock();
    setInterval(updateClock, 1000);

    // Component Distribution Donut Chart
    const componentCtx = document.getElementById('componentChart').getContext('2d');
    const pneumaticCount = <?= $total_pneumatics ?? 0; ?>;
    const fittingCount = <?= $total_fittings ?? 0; ?>;

    new Chart(componentCtx, {
        type: 'doughnut',
        data: {
            labels: ['Pneumatic', 'Fitting'],
            datasets: [{
                data: [pneumaticCount, fittingCount],
                backgroundColor: ['#28a745', '#ffc107'],
                borderWidth: 2,
                borderColor: '#fff',
                cutout: '60%'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        font: {
                            size: 14
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = pneumaticCount + fittingCount;
                            const percentage = total > 0 ? Math.round((context.parsed * 100) / total) : 0;
                            return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });

    // Storage Progress Bar Chart
    const storageCtx = document.getElementById('storageChart').getContext('2d');
    const totalComponents = pneumaticCount + fittingCount;
    const storedItems = <?= $total_storage_items ?? 0; ?>;
    const unstored = Math.max(0, totalComponents - storedItems);

    new Chart(storageCtx, {
        type: 'bar',
        data: {
            labels: ['Tersimpan', 'Belum Tersimpan'],
            datasets: [{
                label: 'Jumlah Item',
                data: [storedItems, unstored],
                backgroundColor: ['#28a745', '#dc3545'],
                borderRadius: 6,
                borderSkipped: false,
                maxBarThickness: 60
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const percentage = totalComponents > 0 ? Math.round((context.parsed.y * 100) / totalComponents) : 0;
                            return context.label + ': ' + context.parsed.y + ' item (' + percentage + '%)';
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: 12
                        }
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0,0,0,0.1)'
                    },
                    ticks: {
                        precision: 0,
                        font: {
                            size: 12
                        }
                    }
                }
            }
        }
    });
</script>