<div class="container-fluid pt-5 mt-3">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="text-primary">Laporan Penyimpanan</h2>
                    <p class="text-muted">Riwayat transaksi dan analitik</p>
                </div>
                <a href="<?= site_url('storage'); ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali ke Penyimpanan
                </a>
            </div>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Filter</h6>
                </div>
                <div class="card-body">
                    <?= form_open('storage/reports', ['method' => 'GET', 'class' => 'row g-3']); ?>
                    <div class="col-md-3">
                        <label for="start_date" class="form-label">Tanggal Mulai</label>
                        <input type="date" class="form-control" id="start_date" name="start_date"
                            value="<?= $filters['start_date'] ?? ''; ?>">
                    </div>

                    <div class="col-md-3">
                        <label for="end_date" class="form-label">Tanggal Berakhir</label>
                        <input type="date" class="form-control" id="end_date" name="end_date"
                            value="<?= $filters['end_date'] ?? ''; ?>">
                    </div>

                    <div class="col-md-2">
                        <label for="action" class="form-label">Aksi</label>
                        <select class="form-select" id="action" name="action">
                            <option value="">Semua Aksi</option>
                            <option value="store" <?= isset($filters['action']) && $filters['action'] == 'store' ? 'selected' : ''; ?>>Simpan</option>
                            <option value="take" <?= isset($filters['action']) && $filters['action'] == 'take' ? 'selected' : ''; ?>>Ambil</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label for="location" class="form-label">Lokasi</label>
                        <select class="form-select" id="location" name="location">
                            <option value="">Semua Lokasi</option>
                            <?php foreach ($locations as $location): ?>
                                <option value="<?= $location['location_id']; ?>"
                                    <?= isset($filters['location_id']) && $filters['location_id'] == $location['location_id'] ? 'selected' : ''; ?>>
                                    <?= $location['location_id']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label for="category" class="form-label">Kategori</label>
                        <select class="form-select" id="category" name="category">
                            <option value="">Semua Kategori</option>
                            <option value="pneumatic" <?= isset($filters['category']) && $filters['category'] == 'pneumatic' ? 'selected' : ''; ?>>Pneumatic</option>
                            <option value="valve" <?= isset($filters['category']) && $filters['category'] == 'valve' ? 'selected' : ''; ?>>Valve</option>
                            <option value="fitting" <?= isset($filters['category']) && $filters['category'] == 'fitting' ? 'selected' : ''; ?>>Fitting</option>
                            <option value="sensor" <?= isset($filters['category']) && $filters['category'] == 'sensor' ? 'selected' : ''; ?>>Sensor</option>
                            <option value="other" <?= isset($filters['category']) && $filters['category'] == 'other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Terapkan Filter
                        </button>
                        <a href="<?= site_url('storage/reports'); ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i> Hapus Filter
                        </a>
                        <button type="button" class="btn btn-success" onclick="exportToExcel()">
                            <i class="fas fa-download"></i> Ekspor Excel
                        </button>
                    </div>
                    <?= form_close(); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <?php if (!empty($stats)): ?>
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Total Transaksi</h6>
                                <h3><?= number_format($stats['total_transactions']); ?></h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-exchange-alt fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Operasi Simpan</h6>
                                <h3><?= number_format($stats['store_transactions']); ?></h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-plus fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Operasi Ambil</h6>
                                <h3><?= number_format($stats['take_transactions']); ?></h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-minus fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Pengguna Aktif</h6>
                                <h3><?= number_format($stats['users_involved']); ?></h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-users fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Daily Summary Chart -->
    <?php if (!empty($daily_summary)): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Ringkasan Aktivitas Harian</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="dailyChart" width="400" height="100"></canvas>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Transactions Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Riwayat Transaksi</h6>
                    <small class="text-muted"><?= count($transactions); ?> transaksi ditemukan</small>
                </div>
                <div class="card-body">
                    <?php if (!empty($transactions)): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="transactionsTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID Penyimpanan</th>
                                        <th>Tanggal/Waktu</th>
                                        <th>Aksi</th>
                                        <th>Lokasi</th>
                                        <th>Kategori</th>
                                        <th>ID Tipe</th>
                                        <th>Pengguna</th>
                                        <th>Catatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($transactions as $transaction): ?>
                                        <tr>
                                            <td>
                                                <code><?= htmlspecialchars($transaction['storing_id']); ?></code>
                                            </td>
                                            <td>
                                                <?= date('M d, Y H:i', strtotime($transaction['datetime'])); ?>
                                            </td>
                                            <td>
                                                <?php if ($transaction['action'] == 'store'): ?>
                                                    <span class="badge bg-success">Simpan</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning">Ambil</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong><?= htmlspecialchars($transaction['location_id']); ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary"><?= htmlspecialchars($transaction['category']); ?></span>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars($transaction['type_id']); ?>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars($transaction['user_name'] ?? 'Tidak Diketahui'); ?>
                                            </td>
                                            <td>
                                                <?php if ($transaction['note']): ?>
                                                    <span class="text-muted" title="<?= htmlspecialchars($transaction['note']); ?>">
                                                        <?= strlen($transaction['note']) > 30 ? substr(htmlspecialchars($transaction['note']), 0, 30) . '...' : htmlspecialchars($transaction['note']); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info text-center">
                            <i class="fas fa-chart-line fa-3x mb-3"></i>
                            <h5>Tidak ada transaksi ditemukan</h5>
                            <p>Tidak ada transaksi yang cocok dengan filter saat ini. Coba sesuaikan filter atau <a href="<?= site_url('storage/store'); ?>">mulai dengan menyimpan beberapa barang</a>.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Daily Activity Chart
    <?php if (!empty($daily_summary)): ?>
        const dailyData = <?= json_encode($daily_summary); ?>;
        const ctx = document.getElementById('dailyChart').getContext('2d');

        const chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: dailyData.map(day => day.transaction_date),
                datasets: [{
                        label: 'Operasi Simpan',
                        data: dailyData.map(day => day.store_count),
                        borderColor: 'rgb(40, 167, 69)',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        tension: 0.4
                    },
                    {
                        label: 'Operasi Ambil',
                        data: dailyData.map(day => day.take_count),
                        borderColor: 'rgb(255, 193, 7)',
                        backgroundColor: 'rgba(255, 193, 7, 0.1)',
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Aktivitas Penyimpanan Harian'
                    }
                }
            }
        });
    <?php endif; ?>

    function exportToExcel() {
        // Get current filter parameters
        const startDate = document.querySelector('input[name="start_date"]')?.value || '';
        const endDate = document.querySelector('input[name="end_date"]')?.value || '';

        // Build URL with parameters
        let url = '<?= site_url("storage/export_transactions_excel"); ?>';
        let params = [];

        if (startDate) params.push('start_date=' + encodeURIComponent(startDate));
        if (endDate) params.push('end_date=' + encodeURIComponent(endDate));

        if (params.length > 0) {
            url += '?' + params.join('&');
        }

        // Open in new window to trigger download
        window.open(url, '_blank');
    }

    // Auto-set end date when start date is selected
    document.getElementById('start_date').addEventListener('change', function() {
        const endDate = document.getElementById('end_date');
        if (!endDate.value && this.value) {
            endDate.value = this.value;
        }
    });

    // Quick date filters
    function setDateFilter(days) {
        const endDate = new Date();
        const startDate = new Date();
        startDate.setDate(endDate.getDate() - days);

        document.getElementById('start_date').value = startDate.toISOString().split('T')[0];
        document.getElementById('end_date').value = endDate.toISOString().split('T')[0];
    }

    // Add quick filter buttons
    document.addEventListener('DOMContentLoaded', function() {
        const filterCard = document.querySelector('.card-body');
        const quickFilters = document.createElement('div');
        quickFilters.className = 'mb-3';
        quickFilters.innerHTML = `
        <label class="form-label">Filter Cepat:</label><br>
        <div class="btn-group btn-group-sm" role="group">
            <button type="button" class="btn btn-outline-primary" onclick="setDateFilter(7)">7 hari terakhir</button>
            <button type="button" class="btn btn-outline-primary" onclick="setDateFilter(30)">30 hari terakhir</button>
            <button type="button" class="btn btn-outline-primary" onclick="setDateFilter(90)">3 bulan terakhir</button>
        </div>
    `;

        const firstRow = filterCard.querySelector('.row');
        filterCard.insertBefore(quickFilters, firstRow);
    });
</script>