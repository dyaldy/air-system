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

    <!-- Charts Section -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-transparent border-0 pt-4 pb-2">
                    <h5 class="mb-0">Status Stok vs Minimum</h5>
                    <p class="text-muted small mb-0">Perbandingan stok aktual dengan minimum yang diset</p>
                </div>
                <div class="card-body p-4">
                    <div style="position: relative; height: 300px;">
                        <canvas id="stockChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-transparent border-0 pt-4 pb-2">
                    <h5 class="mb-0">Tren Aktivitas Penyimpanan</h5>
                    <p class="text-muted small mb-0">Perbandingan aktivitas penyimpanan vs pengambilan dalam 7 hari terakhir</p>
                </div>
                <div class="card-body p-4">
                    <div style="position: relative; height: 300px;">
                        <canvas id="activityChart"></canvas>
                    </div>
                    <div class="mt-3 text-center">
                        <small class="text-muted">
                            <i class="fas fa-plus-circle text-success me-1"></i>Penyimpanan
                            <i class="fas fa-minus-circle text-danger ms-3 me-1"></i>Pengambilan
                        </small>
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
                        <i class="fas fa-chart-pie fa-3x text-secondary"></i>
                    </div>
                    <h2 class="mb-1 text-secondary"><?= number_format($storage_utilization ?? 0, 1); ?>%</h2>
                    <h6 class="mb-1 text-dark">Utilisasi Storage</h6>
                    <small class="text-muted">Kapasitas terpakai</small>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 card-hover h-100">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-exclamation-triangle fa-3x text-danger"></i>
                    </div>
                    <h2 class="mb-1 text-danger"><?= number_format(count($low_stock_items ?? [])); ?></h2>
                    <h6 class="mb-1 text-dark">Stok Rendah</h6>
                    <small class="text-muted">Perlu perhatian</small>
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

    <!-- Low Stock Alerts -->
    <?php if (!empty($low_stock_items)): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 border-start border-danger border-4">
                    <div class="card-header bg-danger bg-opacity-10 border-0 pt-4 pb-2">
                        <h5 class="mb-0 text-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Peringatan Stok Rendah
                        </h5>
                        <p class="text-muted small mb-0">Item yang perlu segera diisi ulang</p>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <?php foreach (array_slice($low_stock_items, 0, 6) as $item): ?>
                                <div class="col-md-6 col-lg-4">
                                    <div class="card border border-warning bg-warning bg-opacity-10">
                                        <div class="card-body p-3">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="mb-1 text-truncate">
                                                        <?php if ($item['category'] === 'pneumatic'): ?>
                                                            <?= htmlspecialchars($item['brand'] . ' ' . $item['type'] . ' ' . $item['bore'] . 'x' . $item['stroke']); ?>
                                                        <?php else: ?>
                                                            <?= htmlspecialchars($item['fitting_type'] . ' ' . ($item['subtype'] ?? '')); ?>
                                                        <?php endif; ?>
                                                    </h6>
                                                    <small class="text-muted">Lokasi: <?= htmlspecialchars($item['location_id']); ?></small>
                                                </div>
                                                <span class="badge bg-danger">
                                                    <?= $item['amount']; ?>/<?= $item['min_stock']; ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if (count($low_stock_items) > 6): ?>
                            <div class="text-center mt-3">
                                <small class="text-muted">Dan <?= count($low_stock_items) - 6; ?> item lainnya...</small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Recent Activities and Quick Actions -->
    <div class="row mb-4">
        <!-- Recent Activities -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-transparent border-0 pt-4 pb-2">
                    <h5 class="mb-0">Aktivitas Terbaru</h5>
                    <p class="text-muted small mb-0">5 aktivitas terakhir</p>
                </div>
                <div class="card-body p-4">
                    <?php if (!empty($recent_activities)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($recent_activities as $activity): ?>
                                <div class="list-group-item px-0 py-3 border-0">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 me-3">
                                            <?php if ($activity['action'] === 'store'): ?>
                                                <i class="fas fa-plus-circle text-success fa-lg"></i>
                                            <?php else: ?>
                                                <i class="fas fa-minus-circle text-danger fa-lg"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <small class="text-muted">
                                                        <?= htmlspecialchars($activity['user_name'] ?? 'Unknown'); ?>
                                                    </small>
                                                    <div class="fw-semibold small">
                                                        <?php if ($activity['action'] === 'store'): ?>
                                                            Menyimpan
                                                        <?php else: ?>
                                                            Mengambil
                                                        <?php endif; ?>
                                                        <?= $activity['amount']; ?> item
                                                    </div>
                                                </div>
                                                <small class="text-muted">
                                                    <?= date('d/m H:i', strtotime($activity['datetime'])); ?>
                                                </small>
                                            </div>
                                            <small class="text-muted">
                                                Lokasi: <?= htmlspecialchars($activity['location_id']); ?> |
                                                Kategori: <?= htmlspecialchars($activity['category']); ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-2x mb-2"></i>
                            <p>Belum ada aktivitas</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-transparent border-0 pt-4 pb-2">
                    <h5 class="mb-0">Menu Utama</h5>
                    <p class="text-muted small mb-0">Akses cepat ke fitur utama</p>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6 col-sm-6">
                            <a href="<?= site_url('user'); ?>" class="text-decoration-none">
                                <div class="card border-2 border-primary bg-primary bg-opacity-10 h-100 card-hover">
                                    <div class="card-body text-center p-3">
                                        <i class="fas fa-users fa-2x text-primary mb-2"></i>
                                        <h6 class="mb-0 text-dark">Pengguna</h6>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <div class="col-md-6 col-sm-6">
                            <a href="<?= site_url('pneumatic/type'); ?>" class="text-decoration-none">
                                <div class="card border-2 border-success bg-success bg-opacity-10 h-100 card-hover">
                                    <div class="card-body text-center p-3">
                                        <i class="fas fa-cog fa-2x text-success mb-2"></i>
                                        <h6 class="mb-0 text-dark">Pneumatic</h6>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <div class="col-md-6 col-sm-6">
                            <a href="<?= site_url('fitting/type'); ?>" class="text-decoration-none">
                                <div class="card border-2 border-warning bg-warning bg-opacity-10 h-100 card-hover">
                                    <div class="card-body text-center p-3">
                                        <i class="fas fa-puzzle-piece fa-2x text-warning mb-2"></i>
                                        <h6 class="mb-0 text-dark">Fitting</h6>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <div class="col-md-6 col-sm-6">
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

        // Stock vs Minimum Chart
        const stockCtx = document.getElementById('stockChart').getContext('2d');
        const lowStockCount = <?= count($low_stock_items ?? []); ?>;
        const normalStockCount = <?= ($total_pneumatics + $total_fittings) ?? 0; ?> - lowStockCount;

        new Chart(stockCtx, {
            type: 'doughnut',
            data: {
                labels: ['Stok Normal', 'Stok Rendah'],
                datasets: [{
                    data: [normalStockCount, lowStockCount],
                    backgroundColor: ['#28a745', '#dc3545'],
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
                                const total = normalStockCount + lowStockCount;
                                const percentage = total > 0 ? Math.round((context.parsed * 100) / total) : 0;
                                const status = context.label === 'Stok Normal' ? 'normal' : 'rendah';
                                return context.label + ': ' + context.parsed + ' item (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });

        // Activity Trend Chart (7 days) - Store vs Retrieve
        const activityCtx = document.getElementById('activityChart').getContext('2d');
        const activityData = <?= json_encode($activity_trend ?? []); ?>;

        new Chart(activityCtx, {
            type: 'line',
            data: {
                labels: activityData.map(item => item.date),
                datasets: [{
                    label: 'Penyimpanan',
                    data: activityData.map(item => item.store),
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    borderWidth: 3,
                    fill: false,
                    tension: 0.4,
                    pointBackgroundColor: '#28a745',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }, {
                    label: 'Pengambilan',
                    data: activityData.map(item => item.retrieve),
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    borderWidth: 3,
                    fill: false,
                    tension: 0.4,
                    pointBackgroundColor: '#dc3545',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7
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
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.dataset.label || '';
                                const value = context.parsed.y;
                                return label + ': ' + value + ' aktivitas';
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