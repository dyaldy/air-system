<?php if ($this->session->flashdata('action')) : ?>
    <!-- Flash Notification Alert -->
    <div class="cust-notification m-3">
        <div class="alert alert-<?= $this->session->flashdata('action')[0]; ?> alert-dismissible fade show" id="notification" role="alert">
            <?= $this->session->flashdata('action')[1]; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
<?php endif; ?>

<!-- Main Content Card -->
<div class="card mx-auto rounded-5 shadow border-0 mb-5" style="margin-top: 5rem; max-width: 95%;">
    <!-- Card Header with Title -->
    <div class="card-header bg-white border-bottom px-lg-5 px-4 py-4 rounded-top-5">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3 class="text-dark m-0">Laporan Penyimpanan</h3>
                <p class="text-muted mb-0">Riwayat transaksi dan analitik</p>
            </div>
            <a href="<?= site_url('storage'); ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali ke Penyimpanan
            </a>
        </div>
    </div>

    <!-- Card Body with Main Content -->
    <div class="card-body px-lg-5 px-4 py-4">

        <!-- Filter Toggle -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0">Filter & Laporan</h6>
            <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
                <i class="fas fa-filter"></i> Toggle Filter
            </button>
        </div>

        <!-- Filter Form -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="card border rounded-4 collapse" id="filterCollapse">
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



            <!-- Transactions Table -->
            <?php if (!empty($transactions)): ?>
                <div class="table-responsive">
                    <table class="table table-borderless table-hover table-striped mb-0" id="transactionsTable">
                        <thead>
                            <tr>
                                <th scope="col" class="text-center ps-lg-5 ps-4">ID Penyimpanan</th>
                                <th scope="col" class="text-center">Tanggal/Waktu</th>
                                <th scope="col" class="text-center">Aksi</th>
                                <th scope="col" class="text-center">Jumlah</th>
                                <th scope="col" class="text-center">Lokasi</th>
                                <th scope="col" class="text-center">Kategori</th>
                                <th scope="col" class="text-center">ID Tipe</th>
                                <th scope="col" class="text-center">Pengguna</th>
                                <th scope="col" class="text-center pe-lg-5 pe-4">Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transactions as $transaction): ?>
                                <tr>
                                    <th scope="row" class="text-center ps-lg-5 ps-4">
                                        <code><?= htmlspecialchars($transaction['storing_id']); ?></code>
                                    </th>
                                    <td class="text-center">
                                        <?= date('d M Y, H:i:s', strtotime($transaction['datetime'])); ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge rounded-pill text-bg-<?= $transaction['action'] == 'store' ? 'success' : 'warning' ?>">
                                            <?= $transaction['action'] == 'store' ? 'Simpan' : 'Ambil'; ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info"><?= isset($transaction['amount']) ? (int)$transaction['amount'] : 1; ?></span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary"><?= htmlspecialchars($transaction['location_id']); ?></span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary"><?= htmlspecialchars($transaction['category']); ?></span>
                                    </td>
                                    <td class="text-center">
                                        <?= htmlspecialchars($transaction['type_id']); ?>
                                    </td>
                                    <td class="text-center">
                                        <?= htmlspecialchars($transaction['user_name'] ?? 'Tidak Diketahui'); ?>
                                    </td>
                                    <td class="text-center pe-lg-5 pe-4">
                                        <?php if ($transaction['note']): ?>
                                            <div class="d-flex align-items-center justify-content-center">
                                                <span class="text-muted me-2" title="<?= htmlspecialchars($transaction['note']); ?>">
                                                    <?= strlen($transaction['note']) > 30 ? substr(htmlspecialchars($transaction['note']), 0, 30) . '...' : htmlspecialchars($transaction['note']); ?>
                                                </span>
                                                <?php if (strlen($transaction['note']) > 30): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary"
                                                        onclick="showFullNote('<?= htmlspecialchars(addslashes($transaction['storing_id'])); ?>', '<?= htmlspecialchars(addslashes($transaction['note'])); ?>')"
                                                        title="Lihat catatan lengkap">
                                                        <img src="<?= base_url('assets/img/eye.svg'); ?>" alt="View" style="width: 14px; height: 14px;">
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Footer -->
                <?php if (isset($pagination_links) && !empty($pagination_links)): ?>
                    <div class="card-footer bg-white border-0 px-lg-5 px-4 py-3">
                        <div class="d-flex flex-column flex-lg-row align-items-center justify-content-center gap-3">
                            <!-- Record Count Display -->
                            <div class="text-muted">
                                Menampilkan <strong><?= $display; ?></strong>
                            </div>

                            <!-- Pagination Links -->
                            <?= $pagination_links; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="alert alert-info text-center">
                    <i class="fas fa-chart-line fa-3x mb-3"></i>
                    <h5>Tidak ada transaksi ditemukan</h5>
                    <p>Tidak ada transaksi yang cocok dengan filter saat ini. Coba sesuaikan filter atau <a href="<?= site_url('storage/store'); ?>">mulai dengan menyimpan beberapa barang</a>.</p>
                </div>
            <?php endif; ?>

            <!-- Statistics Cards -->
            <?php if (!empty($stats)): ?>
                <div class="row mt-2">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white border-0">
                            <div class="card-body py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <small class="text-white-50">Total Transaksi</small>
                                        <h4 class="mb-0"><?= number_format($stats['total_transactions']); ?></h4>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-exchange-alt fa-lg"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card bg-success text-white border-0">
                            <div class="card-body py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <small class="text-white-50">Operasi Simpan</small>
                                        <h4 class="mb-0"><?= number_format($stats['store_transactions']); ?></h4>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-plus fa-lg"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card bg-warning text-white border-0">
                            <div class="card-body py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <small class="text-white-50">Operasi Ambil</small>
                                        <h4 class="mb-0"><?= number_format($stats['take_transactions']); ?></h4>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-minus fa-lg"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card bg-info text-white border-0">
                            <div class="card-body py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <small class="text-white-50">Pengguna Aktif</small>
                                        <h4 class="mb-0"><?= number_format($stats['users_involved']); ?></h4>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-users fa-lg"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</div>
</div>
</div>

<!-- Full Note Modal -->
<div class="modal fade" id="fullNoteModal" tabindex="-1" aria-labelledby="fullNoteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fullNoteModalLabel">Catatan Lengkap</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <strong>ID Penyimpanan:</strong>
                    <code id="modalStoringId"></code>
                </div>
                <div>
                    <strong>Catatan:</strong>
                    <div class="mt-2 p-3 bg-light rounded" id="modalNoteContent" style="white-space: pre-wrap; word-wrap: break-word;"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
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

    // Function to show full note in modal
    function showFullNote(storingId, note) {
        document.getElementById('modalStoringId').textContent = storingId;
        document.getElementById('modalNoteContent').textContent = note;

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('fullNoteModal'));
        modal.show();
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