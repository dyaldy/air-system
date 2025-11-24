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
                    <h5 class="mb-0">Distribusi Penyimpanan</h5>
                    <p class="text-muted small mb-0">Perbandingan jumlah item per kategori</p>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-7">
                            <div style="position: relative; height: 300px;">
                                <canvas id="stockChart"></canvas>
                            </div>
                        </div>
                        <div class="col-md-5 d-flex flex-column justify-content-center">
                            <div class="mb-4">
                                <h6 class="text-muted mb-3">Ringkasan</h6>
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-boxes text-primary me-2"></i>
                                    <div>
                                        <div class="fw-semibold text-dark"><?= number_format($total_quantity_stored ?? 0); ?></div>
                                        <small class="text-muted">Total Item</small>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-tags text-success me-2"></i>
                                    <div>
                                        <div class="fw-semibold text-dark"><?= count($storage_by_category ?? []); ?></div>
                                        <small class="text-muted">Kategori</small>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h6 class="text-muted mb-3">Detail Kategori</h6>
                                <div style="max-height: 200px; overflow-y: auto;">
                                    <?php if (!empty($storage_by_category)): ?>
                                        <?php foreach ($storage_by_category as $category): ?>
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <div class="d-flex align-items-center">
                                                    <div class="badge me-2" style="background-color: 
                                                        <?php
                                                        $colors = ['#28a745', '#dc3545', '#ffc107', '#17a2b8', '#6f42c1'];
                                                        $index = array_search($category['category'], array_column($storage_by_category, 'category'));
                                                        echo $colors[$index % count($colors)];
                                                        ?>; width: 12px; height: 12px; border-radius: 50%;">
                                                    </div>
                                                    <small class="text-dark">
                                                        <?= $category['category'] === 'pneumatic' ? 'Pneumatic' : ($category['category'] === 'fitting' ? 'Fitting' : ucfirst($category['category'])); ?>
                                                    </small>
                                                </div>
                                                <small class="fw-semibold text-dark">
                                                    <?= number_format($category['total_amount']); ?>
                                                </small>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <small class="text-muted">Tidak ada data penyimpanan</small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
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
                    <div style="position: relative; height: 250px;">
                        <canvas id="activityChart"></canvas>
                    </div>
                    <div class="mt-3 text-center">
                        <small class="text-muted">
                            <i class="fas fa-plus-circle text-success me-1"></i>Penyimpanan
                            <i class="fas fa-minus-circle text-danger ms-3 me-1"></i>Pengambilan
                        </small>
                    </div>

                    <!-- Summary Statistics for 7 days -->
                    <div class="row mt-3 border-top pt-3">
                        <?php
                        $total_store_trans = 0;
                        $total_store_items = 0;
                        $total_retrieve_trans = 0;
                        $total_retrieve_items = 0;

                        foreach ($activity_trend as $day) {
                            $total_store_trans += $day['store'];
                            $total_store_items += $day['store_amount'];
                            $total_retrieve_trans += $day['retrieve'];
                            $total_retrieve_items += $day['retrieve_amount'];
                        }
                        ?>
                        <div class="col-6">
                            <div class="text-center">
                                <div class="text-success fw-bold"><?= number_format($total_store_trans); ?> transaksi</div>
                                <small class="text-muted">Penyimpanan</small>
                                <div class="text-success mt-1"><?= number_format($total_store_items); ?> item</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center">
                                <div class="text-danger fw-bold"><?= number_format($total_retrieve_trans); ?> transaksi</div>
                                <small class="text-muted">Pengambilan</small>
                                <div class="text-danger mt-1"><?= number_format($total_retrieve_items); ?> item</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row mb-4 g-3">
        <!-- System Overview -->
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
                        <i class="fas fa-boxes fa-3x text-info"></i>
                    </div>
                    <h2 class="mb-1 text-info"><?= number_format($total_quantity_stored ?? 0); ?></h2>
                    <h6 class="mb-1 text-dark">Total Quantity</h6>
                    <small class="text-muted">Item tersimpan</small>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 card-hover h-100">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-chart-line fa-3x text-success"></i>
                    </div>
                    <h2 class="mb-1 text-success"><?= number_format($today_activities ?? 0); ?></h2>
                    <h6 class="mb-1 text-dark">Aktivitas Hari Ini</h6>
                    <small class="text-muted">Transaksi hari ini</small>
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

        <!-- Inventory Breakdown -->
        <div class="col-xl-4 col-md-6">
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

        <div class="col-xl-4 col-md-6">
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

        <div class="col-xl-4 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 card-hover h-100">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-warehouse fa-3x text-secondary"></i>
                    </div>
                    <h2 class="mb-1 text-secondary"><?= number_format($total_storage_items ?? 0); ?></h2>
                    <h6 class="mb-1 text-dark">Lokasi Penyimpanan</h6>
                    <small class="text-muted">Total lokasi aktif</small>
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
                                                            <?= htmlspecialchars($item['type'] . ' ' . $item['bore'] . 'x' . $item['stroke']); ?>
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

    <!-- System Status & Quick Actions -->
    <div class="row mb-4">
        <!-- System Status -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-transparent border-0 pt-4 pb-2">
                    <h5 class="mb-0">
                        <i class="fas fa-heartbeat text-success me-2"></i>
                        Status Sistem
                    </h5>
                    <p class="text-muted small mb-0">Informasi sistem terkini</p>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-server text-success fa-lg me-3"></i>
                                <div>
                                    <div class="fw-semibold">Database</div>
                                    <small class="text-success">Terhubung</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-clock text-info fa-lg me-3"></i>
                                <div>
                                    <div class="fw-semibold">Waktu Sistem</div>
                                    <small class="text-muted" id="system-time-status"></small>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="fab fa-php text-primary fa-lg me-3"></i>
                                <div>
                                    <div class="fw-semibold">PHP Version</div>
                                    <small class="text-muted"><?= $php_version ?? 'Unknown'; ?></small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-memory text-warning fa-lg me-3"></i>
                                <div>
                                    <div class="fw-semibold">Memory Usage</div>
                                    <small class="text-muted"><?= $memory_usage ?? 0; ?> MB</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-globe text-secondary fa-lg me-3"></i>
                                <div>
                                    <div class="fw-semibold">Server</div>
                                    <small class="text-muted">Apache/XAMPP</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-users text-info fa-lg me-3"></i>
                                <div>
                                    <div class="fw-semibold">Active Users</div>
                                    <small class="text-muted">1 online</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-transparent border-0 pt-4 pb-2">
                    <h5 class="mb-0">Menu Utama</h5>
                    <p class="text-muted small mb-0">Akses cepat ke fitur utama</p>
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

                        <div class="col-md-3 col-sm-6">
                            <a href="<?= site_url('report'); ?>" class="text-decoration-none">
                                <div class="card border-2 border-secondary bg-secondary bg-opacity-10 h-100 card-hover">
                                    <div class="card-body text-center p-3">
                                        <i class="fas fa-chart-bar fa-2x text-secondary mb-2"></i>
                                        <h6 class="mb-0 text-dark">Laporan</h6>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <div class="col-md-3 col-sm-6">
                            <a href="<?= site_url('storage/store'); ?>" class="text-decoration-none">
                                <div class="card border-2 border-success bg-success bg-opacity-10 h-100 card-hover">
                                    <div class="card-body text-center p-3">
                                        <i class="fas fa-plus-circle fa-2x text-success mb-2"></i>
                                        <h6 class="mb-0 text-dark">Simpan</h6>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <div class="col-md-3 col-sm-6">
                            <a href="<?= site_url('storage/take'); ?>" class="text-decoration-none">
                                <div class="card border-2 border-danger bg-danger bg-opacity-10 h-100 card-hover">
                                    <div class="card-body text-center p-3">
                                        <i class="fas fa-minus-circle fa-2x text-danger mb-2"></i>
                                        <h6 class="mb-0 text-dark">Ambil</h6>
                                    </div>
                                </div>
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js Local -->
    <script src="<?= base_url('assets/js/chart.min.js'); ?>"></script>

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
            document.getElementById('system-time-status').textContent = 'Sinkron - ' + timeString;
        }
        updateClock();
        setInterval(updateClock, 1000);

        // Storage Distribution Chart
        const stockCtx = document.getElementById('stockChart').getContext('2d');
        const storageData = <?= json_encode($storage_by_category ?? []); ?>;

        // Prepare data for chart
        const labels = storageData.map(item => {
            return item.category === 'pneumatic' ? 'Pneumatic' :
                item.category === 'fitting' ? 'Fitting' : item.category;
        });
        const data = storageData.map(item => parseInt(item.total_amount));

        // Add colors for each category
        const colors = ['#28a745', '#dc3545', '#ffc107', '#17a2b8', '#6f42c1'];

        new Chart(stockCtx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: colors.slice(0, labels.length),
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
                                const total = data.reduce((sum, value) => sum + value, 0);
                                const percentage = total > 0 ? Math.round((context.parsed * 100) / total) : 0;
                                return context.label + ': ' + context.parsed + ' item (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });

        // Activity Trend Chart (7 days) - Store vs Retrieve with detailed information
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
                            title: function(context) {
                                return 'Tanggal: ' + context[0].label;
                            },
                            label: function(context) {
                                const dataIndex = context.dataIndex;
                                const dataPoint = activityData[dataIndex];
                                const isStore = context.datasetIndex === 0;

                                if (isStore) {
                                    const count = dataPoint.store;
                                    const amount = dataPoint.store_amount || 0;
                                    return 'Penyimpanan: ' + count + ' transaksi (' + amount.toLocaleString() + ' item)';
                                } else {
                                    const count = dataPoint.retrieve;
                                    const amount = dataPoint.retrieve_amount || 0;
                                    return 'Pengambilan: ' + count + ' transaksi (' + amount.toLocaleString() + ' item)';
                                }
                            },
                            afterLabel: function(context) {
                                const dataIndex = context.dataIndex;
                                const dataPoint = activityData[dataIndex];
                                const isStore = context.datasetIndex === 0;

                                const categories = isStore ? dataPoint.store_by_category : dataPoint.retrieve_by_category;

                                if (categories && Object.keys(categories).length > 0) {
                                    let lines = ['', 'Rincian per Kategori:'];

                                    for (const [category, data] of Object.entries(categories)) {
                                        const categoryName = category === 'pneumatic' ? 'Pneumatic' :
                                            category === 'fitting' ? 'Fitting' :
                                            category.charAt(0).toUpperCase() + category.slice(1);
                                        lines.push('  • ' + categoryName + ': ' + data.count + ' transaksi (' + data.amount.toLocaleString() + ' item)');
                                    }

                                    return lines;
                                }

                                return '';
                            }
                        },
                        backgroundColor: 'rgba(0, 0, 0, 0.85)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        padding: 12,
                        displayColors: true,
                        boxPadding: 6
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
                        },
                        title: {
                            display: true,
                            text: 'Jumlah Transaksi',
                            font: {
                                size: 12,
                                weight: 'bold'
                            }
                        }
                    }
                }
            }
        });
    </script>