<?php if ($this->session->flashdata('success')): ?>
    <!-- Flash Notification Alert -->
    <div class="cust-notification m-3">
        <div class="alert alert-success alert-dismissible fade show" id="notification" role="alert">
            <?= $this->session->flashdata('success'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
<?php endif; ?>

<?php if ($this->session->flashdata('error')): ?>
    <!-- Flash Notification Alert -->
    <div class="cust-notification m-3">
        <div class="alert alert-danger alert-dismissible fade show" id="notification" role="alert">
            <?= $this->session->flashdata('error'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
<?php endif; ?>

<!-- Alert Container for JavaScript messages -->
<div id="alert-container" class="mx-3"></div>

<!-- Main Content Card -->
<div class="card mx-auto rounded-5 shadow border-0 mb-5" style="margin-top: 5rem; max-width: 95%;">
    <!-- Card Header with Title -->
    <div class="card-header bg-white border-bottom px-lg-5 px-4 py-4 rounded-top-5">
        <!-- Search Bar -->
        <div class="row mb-3">
            <div class="col-12">
                <form method="get" action="" class="d-flex gap-2">
                    <input type="text" name="keyword" class="form-control" placeholder="Cari penyimpanan..." value="<?= htmlspecialchars($keyword); ?>">
                    <button type="submit" name="find" value="1" class="btn btn-info">
                        <i class="fas fa-search"></i> Cari
                    </button>
                    <?php if ($keyword): ?>
                        <a href="<?= site_url('storage'); ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i> Reset
                        </a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <div class="row g-3 align-items-center">
            <!-- Page Title -->
            <div class="col-12 col-lg-6">
                <h3 class="text-dark m-0">Overview Penyimpanan</h3>
                <p class="text-muted mb-0">Kelola inventaris Anda di semua lokasi penyimpanan</p>
            </div>

            <!-- Action Buttons -->
            <div class="col-12 col-lg-6">
                <div class="d-flex gap-2 flex-wrap justify-content-lg-end">
                    <a href="<?= site_url('storage/store'); ?>" class="btn btn-success">
                        <i class="fas fa-plus"></i> Simpan Barang
                    </a>
                    <a href="<?= site_url('storage/take'); ?>" class="btn btn-warning">
                        <i class="fas fa-minus"></i> Ambil Barang
                    </a>
                    <a href="<?= site_url('storage/reports'); ?>" class="btn btn-secondary">
                        <i class="fas fa-chart-bar"></i> Laporan
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Card Body with Main Content -->
    <div class="card-body px-lg-5 px-4 py-4">

        <!-- Storage Locations Overview -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card rounded-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            Lokasi Penyimpanan
                            <?php if ($keyword): ?>
                                <small class="text-muted">(difilter untuk: "<?= htmlspecialchars($keyword); ?>")</small>
                            <?php endif; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($locations)): ?>
                            <div class="row">
                                <?php foreach ($locations as $location): ?>
                                    <div class="col-md-3 col-sm-6 mb-3">
                                        <div class="card border-primary rounded-3">
                                            <div class="card-body text-center">
                                                <h6 class="card-title"><?= htmlspecialchars($location['location_id']); ?></h6>
                                                <a href="<?= site_url('storage/location/' . $location['location_id']); ?>" class="btn btn-primary btn-sm">
                                                    Lihat Detail
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <?php if ($keyword): ?>
                                    Tidak ada lokasi penyimpanan yang mengandung item dengan kata kunci "<?= htmlspecialchars($keyword); ?>".
                                <?php else: ?>
                                    Tidak ada lokasi penyimpanan ditemukan. Mulai dengan menyimpan beberapa barang!
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inventory Overview -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            Overview Inventaris
                            <?php if ($keyword): ?>
                                <small class="text-muted">(difilter untuk: "<?= htmlspecialchars($keyword); ?>")</small>
                            <?php endif; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($storage_overview)): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Kategori</th>
                                            <th>ID Tipe</th>
                                            <th>Total Stok</th>
                                            <th>Lokasi</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($storage_overview as $item): ?>
                                            <tr>
                                                <td>
                                                    <span class="badge bg-primary"><?= htmlspecialchars($item['category']); ?></span>
                                                </td>
                                                <td><?= htmlspecialchars($item['type_id']); ?></td>
                                                <td>
                                                    <strong><?= number_format($item['total_amount'] ?? 0); ?></strong>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info"><?= $item['location_count']; ?> lokasi</span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <button type="button" class="btn btn-outline-success" onclick="quickStore('<?= $item['category']; ?>', '<?= $item['type_id']; ?>')">
                                                            <i class="fas fa-plus"></i> Simpan
                                                        </button>
                                                        <button type="button" class="btn btn-outline-warning" onclick="quickTake('<?= $item['category']; ?>', '<?= $item['type_id']; ?>')">
                                                            <i class="fas fa-minus"></i> Ambil
                                                        </button>
                                                        <button type="button" class="btn btn-outline-info" onclick="manageItem('<?= $item['category']; ?>', '<?= $item['type_id']; ?>')" title="Kelola item dan batch">
                                                            <i class="fas fa-cogs"></i> Kelola
                                                        </button>
                                                        <?php if ($item['total_amount'] > 0): ?>
                                                            <button type="button" class="btn btn-outline-danger disabled-delete-btn"
                                                                title="Tidak dapat dihapus - masih ada <?= $item['total_amount']; ?> item tersisa. Silakan kosongkan stok terlebih dahulu."
                                                                style="opacity: 0.6; cursor: not-allowed;">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        <?php else: ?>
                                                            <button type="button" class="btn btn-outline-danger" onclick="deleteItem('<?= $item['category']; ?>', '<?= $item['type_id']; ?>')" title="Hapus item">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <?php if ($keyword): ?>
                                    Tidak ada barang di penyimpanan yang mengandung kata kunci "<?= htmlspecialchars($keyword); ?>".
                                <?php else: ?>
                                    Belum ada barang di penyimpanan. <a href="<?= site_url('storage/store'); ?>">Mulai simpan barang</a>!
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="row">
            <div class="col-12">
                <div class="card rounded-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            Transaksi Terbaru
                            <?php if ($keyword): ?>
                                <small class="text-muted">(difilter untuk: "<?= htmlspecialchars($keyword); ?>")</small>
                            <?php endif; ?>
                        </h5>
                        <a href="<?= site_url('storage/reports'); ?>" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($recent_transactions)): ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>ID Penyimpanan</th>
                                            <th>Tanggal/Waktu</th>
                                            <th>Aksi</th>
                                            <th>Jumlah</th>
                                            <th>Lokasi</th>
                                            <th>Kategori</th>
                                            <th>ID Tipe</th>
                                            <th>Project</th>
                                            <th>Pengguna</th>
                                            <th>Catatan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_transactions as $transaction): ?>
                                            <tr>
                                                <td>
                                                    <code><?= htmlspecialchars($transaction['storing_id']); ?></code>
                                                </td>
                                                <td><?= date('M d, Y H:i', strtotime($transaction['datetime'])); ?></td>
                                                <td>
                                                    <?php if ($transaction['action'] == 'store'): ?>
                                                        <span class="badge bg-success">Simpan</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning">Ambil</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info"><?= isset($transaction['amount']) ? (int)$transaction['amount'] : 1; ?></span>
                                                </td>
                                                <td>
                                                    <a href="<?= site_url('storage/location/' . urlencode($transaction['location_id'])); ?>"
                                                        class="text-decoration-none"
                                                        title="Lihat detail lokasi">
                                                        <?= htmlspecialchars($transaction['location_id']); ?>
                                                    </a>
                                                </td>
                                                <td><?= htmlspecialchars($transaction['category']); ?></td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <?= htmlspecialchars($transaction['type_id']); ?>
                                                        <?php if (isset($transaction['comment']) && $transaction['comment'] === 'PROJECT'): ?>
                                                            <i class="fas fa-project-diagram text-primary ms-2" title="Barang Project"></i>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php if (!empty($transaction['project_name'])): ?>
                                                        <span class="text-primary" title="Project: <?= htmlspecialchars($transaction['project_name']); ?>">
                                                            <?= strlen($transaction['project_name']) > 15
                                                                ? substr(htmlspecialchars($transaction['project_name']), 0, 15) . '...'
                                                                : htmlspecialchars($transaction['project_name']); ?>
                                                        </span>
                                                    <?php elseif (!empty($transaction['batch_id'])): ?>
                                                        <span class="text-muted">Batch</span>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($transaction['user_name'] ?? 'Tidak Diketahui'); ?></td>
                                                <td>
                                                    <?php if ($transaction['note']): ?>
                                                        <div class="d-flex align-items-center">
                                                            <span class="text-muted me-2" title="<?= htmlspecialchars($transaction['note']); ?>">
                                                                <?= strlen($transaction['note']) > 20 ? substr(htmlspecialchars($transaction['note']), 0, 20) . '...' : htmlspecialchars($transaction['note']); ?>
                                                            </span>
                                                            <?php if (strlen($transaction['note']) > 20): ?>
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
                        <?php else: ?>
                            <div class="alert alert-info">
                                <?php if ($keyword): ?>
                                    Tidak ada transaksi yang mengandung item dengan kata kunci "<?= htmlspecialchars($keyword); ?>".
                                <?php else: ?>
                                    Belum ada transaksi.
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Action Modals -->
    <!-- Quick Store Modal -->
    <div class="modal fade" id="quickStoreModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Simpan Cepat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="quickStoreForm">
                        <input type="hidden" id="storeCategory" name="category">
                        <input type="hidden" id="storeTypeId" name="type_id">
                        <input type="hidden" name="action" value="store">

                        <div class="mb-3">
                            <label class="form-label">Barang:</label>
                            <div id="storeItemInfo" class="form-control-plaintext"></div>
                        </div>

                        <!-- Type Image Display -->
                        <div class="mb-3" id="storeTypeImageContainer" style="display: none;">
                            <label class="form-label">Gambar Tipe</label>
                            <div class="card" style="max-width: 250px; margin: 0 auto;">
                                <img id="storeTypeImage" src="" alt="Type Image" class="card-img-top" style="object-fit: contain; max-height: 200px;" onerror="this.src='<?= base_url('assets/img/placeholder-image.svg'); ?>'">
                                <div class="card-body py-2 text-center">
                                    <small class="text-muted" id="storeTypeImageLabel"></small>
                                </div>
                            </div>
                        </div>

                        <!-- Branch Selection -->
                        <div class="mb-3" id="storeBranchSelection">
                            <label class="form-label">Tipe Penyimpanan:</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="branch_type" id="storeRegularBranch" value="regular" checked>
                                <label class="btn btn-outline-primary" for="storeRegularBranch">
                                    <i class="fas fa-cube"></i> Stok Regular
                                </label>
                                <input type="radio" class="btn-check" name="branch_type" id="storeProjectBranch" value="project">
                                <label class="btn btn-outline-warning" for="storeProjectBranch">
                                    <i class="fas fa-project-diagram"></i> Stok Project
                                </label>
                            </div>
                            <div class="form-text">Pilih simpan sebagai stok regular atau project</div>
                        </div>

                        <!-- Project Options (shown only when Project is selected) -->
                        <div class="mb-3" id="storeProjectOptions" style="display: none;">
                            <label class="form-label">Opsi Project:</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="project_option" id="storeNewBatch" value="new" checked>
                                <label class="btn btn-outline-success" for="storeNewBatch">
                                    <i class="fas fa-plus"></i> Buat Batch Baru
                                </label>
                                <input type="radio" class="btn-check" name="project_option" id="storeExistingBatch" value="existing">
                                <label class="btn btn-outline-info" for="storeExistingBatch">
                                    <i class="fas fa-layer-group"></i> Tambah ke Batch Ada
                                </label>
                            </div>
                        </div>

                        <!-- New Batch Fields -->
                        <div id="storeNewBatchFields" style="display: none;">
                            <div class="mb-3">
                                <label for="storeProjectName" class="form-label">Nama Project:</label>
                                <input type="text" class="form-control" id="storeProjectName" name="project_name" placeholder="Nama project untuk batch ini">
                            </div>
                            <div class="mb-3">
                                <label for="storeProjectNotes" class="form-label">Catatan Project:</label>
                                <textarea class="form-control" id="storeProjectNotes" name="project_notes" rows="2" placeholder="Deskripsi atau catatan tambahan..."></textarea>
                            </div>
                        </div>

                        <!-- Existing Batch Selection -->
                        <div class="mb-3" id="storeExistingBatchField" style="display: none;">
                            <label for="storeBatchId" class="form-label">Pilih Batch:</label>
                            <select class="form-select" id="storeBatchId" name="batch_id">
                                <option value="">Pilih batch yang ada</option>
                            </select>
                            <div class="form-text">Tambahkan ke batch project yang sudah ada</div>
                        </div>

                        <div class="mb-3">
                            <label for="storeLocationId" class="form-label">ID Lokasi:</label>
                            <select class="form-select" id="storeLocationId" name="location_id" required>
                                <option value="">Pilih Lokasi</option>
                                <?php foreach ($locations as $location): ?>
                                    <option value="<?= $location['location_id']; ?>"><?= $location['location_id']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="storeQuantity" class="form-label">Jumlah:</label>
                            <input type="number" class="form-control" id="storeQuantity" name="quantity" min="1" required>
                        </div>

                        <div class="mb-3">
                            <label for="storeNote" class="form-label">Catatan (Opsional):</label>
                            <textarea class="form-control" id="storeNote" name="note" rows="2"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success" onclick="submitQuickStore()">Simpan Barang</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Take Modal -->
    <div class="modal fade" id="quickTakeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ambil Cepat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="quickTakeForm">
                        <input type="hidden" id="takeCategory" name="category">
                        <input type="hidden" id="takeTypeId" name="type_id">
                        <input type="hidden" name="action" value="take">

                        <div class="mb-3">
                            <label class="form-label">Barang:</label>
                            <div id="takeItemInfo" class="form-control-plaintext"></div>
                        </div>

                        <!-- Type Image Display -->
                        <div class="mb-3" id="takeTypeImageContainer" style="display: none;">
                            <label class="form-label">Gambar Tipe</label>
                            <div class="card" style="max-width: 250px; margin: 0 auto;">
                                <img id="takeTypeImage" src="" alt="Type Image" class="card-img-top" style="object-fit: contain; max-height: 200px;" onerror="this.src='<?= base_url('assets/img/placeholder-image.svg'); ?>'">
                                <div class="card-body py-2 text-center">
                                    <small class="text-muted" id="takeTypeImageLabel"></small>
                                </div>
                            </div>
                        </div>

                        <!-- Branch Selection -->
                        <div class="mb-3" id="takeBranchSelection" style="display: none;">
                            <label class="form-label">Pilih Cabang:</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="branch_type" id="takeRegularBranch" value="regular" checked>
                                <label class="btn btn-outline-primary" for="takeRegularBranch">
                                    <i class="fas fa-cube"></i> Stok Regular
                                </label>
                                <input type="radio" class="btn-check" name="branch_type" id="takeProjectBranch" value="project">
                                <label class="btn btn-outline-warning" for="takeProjectBranch">
                                    <i class="fas fa-project-diagram"></i> Stok Project
                                </label>
                            </div>
                            <div class="form-text">Pilih dari stok regular atau project</div>
                        </div>

                        <div class="mb-3">
                            <label for="takeLocationId" class="form-label">ID Lokasi:</label>
                            <select class="form-select" id="takeLocationId" name="location_id" required>
                                <option value="">Pilih Lokasi</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="takeQuantity" class="form-label">Jumlah:</label>
                            <input type="number" class="form-control" id="takeQuantity" name="quantity" min="1" required>
                            <div class="form-text">
                                Tersedia: <span id="takeAvailableStock">-</span>
                                <span id="takeProjectIndicator" class="badge bg-warning ms-2" style="display: none;">
                                    <i class="fas fa-project-diagram"></i> Project
                                </span>
                            </div>
                        </div>

                        <!-- Batch Selection for Project Items -->
                        <div class="mb-3" id="takeBatchSelection" style="display: none;">
                            <label for="takeBatchId" class="form-label">Pilih Batch Project:</label>
                            <select class="form-select" id="takeBatchId" name="batch_id">
                                <option value="">Pilih batch yang akan diambil</option>
                            </select>
                            <div class="form-text">Pilih batch project spesifik</div>
                        </div>

                        <div class="mb-3">
                            <label for="takeNote" class="form-label">Catatan (Opsional):</label>
                            <textarea class="form-control" id="takeNote" name="note" rows="2"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-warning" onclick="submitQuickTake()">Ambil Barang</button>
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

<!-- Item Management Modal -->
<div class="modal fade" id="itemManagementModal" tabindex="-1" aria-labelledby="itemManagementModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="itemManagementModalLabel">Kelola Item & Batch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Item Info Header -->
                <div class="alert alert-info mb-4">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Item:</strong> <span id="manageItemInfo"></span>
                        </div>
                        <div class="col-md-6 text-end">
                            <strong>Total Stok:</strong> <span id="manageTotalStock" class="badge bg-primary fs-6"></span>
                        </div>
                    </div>
                </div>

                <!-- Tabs Navigation -->
                <ul class="nav nav-tabs mb-4" id="managementTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="batches-tab" data-bs-toggle="tab" data-bs-target="#batches-pane" type="button" role="tab">
                            <i class="fas fa-boxes me-2"></i>Kelola Batch
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="locations-tab" data-bs-toggle="tab" data-bs-target="#locations-pane" type="button" role="tab">
                            <i class="fas fa-map-marker-alt me-2"></i>Kelola Lokasi
                        </button>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content" id="managementTabContent">
                    <!-- Batch Management Tab -->
                    <div class="tab-pane fade show active" id="batches-pane" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Project Batches</h6>
                            <button type="button" class="btn btn-sm btn-success" onclick="createNewBatch()">
                                <i class="fas fa-plus me-1"></i>Buat Batch Baru
                            </button>
                        </div>

                        <div id="batchesList" class="mb-4">
                            <div class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2 text-muted">Memuat data batch...</p>
                            </div>
                        </div>
                    </div>

                    <!-- Location Management Tab -->
                    <div class="tab-pane fade" id="locations-pane" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Storage Locations</h6>
                            <button type="button" class="btn btn-sm btn-success" onclick="addToNewLocation()">
                                <i class="fas fa-plus me-1"></i>Tambah ke Lokasi Baru
                            </button>
                        </div>

                        <div id="locationsList" class="mb-4">
                            <div class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2 text-muted">Memuat data lokasi...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Batch Edit Modal -->
<div class="modal fade" id="batchEditModal" tabindex="-1" aria-labelledby="batchEditModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="batchEditModalLabel">Edit Batch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="batchEditForm">
                    <input type="hidden" id="editBatchId" name="batch_id">

                    <div class="mb-3">
                        <label for="editProjectName" class="form-label">Nama Project <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editProjectName" name="project_name" required maxlength="255" placeholder="Masukkan nama project">
                        <div class="form-text">Nama project untuk identifikasi batch ini</div>
                    </div>

                    <div class="mb-3">
                        <label for="editProjectNotes" class="form-label">Catatan Project</label>
                        <textarea class="form-control" id="editProjectNotes" name="project_notes" rows="3" maxlength="1000" placeholder="Deskripsi atau catatan tambahan untuk project ini..."></textarea>
                        <div class="form-text">Catatan opsional untuk project ini</div>
                    </div>

                    <!-- Information display only (not editable) -->
                    <div class="alert alert-info">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Jumlah Awal:</strong> <span id="displayInitialQuantity" class="badge bg-primary"></span>
                            </div>
                            <div class="col-md-6">
                                <strong>Sisa Stok:</strong> <span id="displayRemainingQuantity" class="badge bg-success"></span>
                            </div>
                        </div>
                        <small class="text-muted mt-2 d-block">
                            <i class="fas fa-info-circle"></i> Jumlah awal dan sisa stok dikelola otomatis oleh sistem berdasarkan transaksi simpan/ambil barang.
                        </small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="saveBatchEdit()">Simpan Perubahan</button>
            </div>
        </div>
    </div>
</div>

<!-- New Batch Modal -->
<div class="modal fade" id="newBatchModal" tabindex="-1" aria-labelledby="newBatchModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newBatchModalLabel">Buat Batch Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="newBatchForm">
                    <input type="hidden" id="newBatchCategory" name="category">
                    <input type="hidden" id="newBatchTypeId" name="type_id">
                    <input type="hidden" id="newBatchLocationId" name="location_id">

                    <div class="mb-3">
                        <label for="newProjectName" class="form-label">Nama Project</label>
                        <input type="text" class="form-control" id="newProjectName" name="project_name" required placeholder="Nama project untuk batch ini">
                    </div>

                    <div class="mb-3">
                        <label for="newProjectNotes" class="form-label">Catatan Project</label>
                        <textarea class="form-control" id="newProjectNotes" name="project_notes" rows="3" placeholder="Deskripsi atau catatan tambahan untuk project ini..."></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <label for="newLocationSelect" class="form-label">Lokasi Storage</label>
                            <select class="form-select" id="newLocationSelect" name="location_id" required>
                                <option value="">Pilih lokasi...</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="newQuantity" class="form-label">Jumlah</label>
                            <input type="number" class="form-control" id="newQuantity" name="quantity" min="1" required placeholder="Jumlah item dalam batch">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" onclick="saveNewBatch()">Buat Batch</button>
            </div>
        </div>
    </div>
</div>
<script>
    function quickStore(category, typeId) {
        const baseTypeId = typeId.replace('_PROJECT', '');

        document.getElementById('storeCategory').value = category;
        document.getElementById('storeTypeId').value = baseTypeId;
        document.getElementById('storeItemInfo').textContent = category + ' - ' + baseTypeId;

        // Reset form
        document.getElementById('storeRegularBranch').checked = true;
        document.getElementById('storeProjectBranch').checked = false;
        document.getElementById('storeProjectOptions').style.display = 'none';
        document.getElementById('storeNewBatchFields').style.display = 'none';
        document.getElementById('storeExistingBatchField').style.display = 'none';
        document.getElementById('storeNewBatch').checked = true;
        document.getElementById('storeProjectName').value = '';
        document.getElementById('storeProjectNotes').value = '';
        document.getElementById('storeLocationId').value = '';
        document.getElementById('storeQuantity').value = '';
        document.getElementById('storeNote').value = '';
        document.getElementById('storeBatchId').innerHTML = '<option value="">Pilih batch yang ada</option>';

        // Store base type_id for later use
        window.currentStoreBaseTypeId = baseTypeId;

        // Show type image
        displayStoreTypeImage(category, baseTypeId);

        // Setup event listeners for branch selection
        setupStoreBranchListeners();

        var modal = new bootstrap.Modal(document.getElementById('quickStoreModal'));
        modal.show();
    }

    function setupStoreBranchListeners() {
        const regularBranch = document.getElementById('storeRegularBranch');
        const projectBranch = document.getElementById('storeProjectBranch');
        const newBatch = document.getElementById('storeNewBatch');
        const existingBatch = document.getElementById('storeExistingBatch');

        // Remove old listeners
        regularBranch.replaceWith(regularBranch.cloneNode(true));
        projectBranch.replaceWith(projectBranch.cloneNode(true));

        // Re-get elements after cloning
        const regularBranchNew = document.getElementById('storeRegularBranch');
        const projectBranchNew = document.getElementById('storeProjectBranch');
        const newBatchNew = document.getElementById('storeNewBatch');
        const existingBatchNew = document.getElementById('storeExistingBatch');

        regularBranchNew.addEventListener('change', function() {
            if (this.checked) {
                const baseTypeId = window.currentStoreBaseTypeId || document.getElementById('storeTypeId').value;
                document.getElementById('storeTypeId').value = baseTypeId;
                document.getElementById('storeProjectOptions').style.display = 'none';
                document.getElementById('storeNewBatchFields').style.display = 'none';
                document.getElementById('storeExistingBatchField').style.display = 'none';

                // Enable location selection for regular items
                document.getElementById('storeLocationId').disabled = false;
                const locationLabel = document.querySelector('label[for="storeLocationId"]');
                if (locationLabel) {
                    locationLabel.textContent = 'ID Lokasi:';
                }
            }
        });

        projectBranchNew.addEventListener('change', function() {
            if (this.checked) {
                const baseTypeId = window.currentStoreBaseTypeId || document.getElementById('storeTypeId').value;
                document.getElementById('storeTypeId').value = baseTypeId + '_PROJECT';
                document.getElementById('storeProjectOptions').style.display = 'block';

                // Show appropriate fields based on project option
                if (document.getElementById('storeNewBatch').checked) {
                    document.getElementById('storeNewBatchFields').style.display = 'block';
                    document.getElementById('storeExistingBatchField').style.display = 'none';
                } else {
                    document.getElementById('storeNewBatchFields').style.display = 'none';
                    document.getElementById('storeExistingBatchField').style.display = 'block';
                    loadExistingBatches();
                }
            }
        });

        newBatchNew.addEventListener('change', function() {
            if (this.checked) {
                document.getElementById('storeNewBatchFields').style.display = 'block';
                document.getElementById('storeExistingBatchField').style.display = 'none';
                // Enable location selection for new batch
                document.getElementById('storeLocationId').disabled = false;
                document.getElementById('storeLocationId').value = '';
            }
        });

        existingBatchNew.addEventListener('change', function() {
            if (this.checked) {
                document.getElementById('storeNewBatchFields').style.display = 'none';
                document.getElementById('storeExistingBatchField').style.display = 'block';
                loadExistingBatches();
            }
        });

        // Add batch selection listener
        const batchSelectElement = document.getElementById('storeBatchId');
        batchSelectElement.addEventListener('change', function() {
            handleBatchSelection(this.value);
        });
    }

    function loadExistingBatches() {
        const category = document.getElementById('storeCategory').value;
        const baseTypeId = window.currentStoreBaseTypeId || document.getElementById('storeTypeId').value.replace('_PROJECT', '');

        fetch('<?= site_url('storage/get_batches'); ?>?category=' + category + '&type_id=' + baseTypeId + '_PROJECT')
            .then(response => response.json())
            .then(data => {
                const batchSelect = document.getElementById('storeBatchId');
                batchSelect.innerHTML = '<option value="">Pilih batch yang ada</option>';

                // Store batch data for later use
                window.batchDataMap = {};

                if (data.success && data.batches && data.batches.length > 0) {
                    data.batches.forEach(batch => {
                        const option = document.createElement('option');
                        option.value = batch.batch_id;
                        option.textContent = batch.project_name + ' - ' + batch.location_id + ' (Qty: ' + batch.remaining_quantity + ')';
                        batchSelect.appendChild(option);

                        // Store batch data
                        window.batchDataMap[batch.batch_id] = {
                            location_id: batch.location_id,
                            project_name: batch.project_name,
                            remaining_quantity: batch.remaining_quantity
                        };
                    });
                }
            })
            .catch(error => {
                console.error('Error loading batches:', error);
            });
    }

    function handleBatchSelection(batchId) {
        const locationSelect = document.getElementById('storeLocationId');

        if (batchId && window.batchDataMap && window.batchDataMap[batchId]) {
            const batchData = window.batchDataMap[batchId];

            // Set location to the batch's location
            locationSelect.value = batchData.location_id;

            // Disable location selection - must use batch's location
            locationSelect.disabled = true;

            // Show info message
            const locationLabel = document.querySelector('label[for="storeLocationId"]');
            if (locationLabel) {
                locationLabel.innerHTML = 'ID Lokasi: <small class="text-muted">(Lokasi batch yang dipilih)</small>';
            }
        } else {
            // Enable location selection if no batch selected
            locationSelect.disabled = false;
            locationSelect.value = '';

            const locationLabel = document.querySelector('label[for="storeLocationId"]');
            if (locationLabel) {
                locationLabel.textContent = 'ID Lokasi:';
            }
        }
    }

    function quickTake(category, typeId) {
        // Store the base type_id (without _PROJECT suffix)
        const baseTypeId = typeId.replace('_PROJECT', '');

        document.getElementById('takeCategory').value = category;
        // Initially set the base type_id, will be updated based on branch selection
        document.getElementById('takeTypeId').value = baseTypeId;
        document.getElementById('takeItemInfo').textContent = category + ' - ' + baseTypeId;

        // Reset form
        document.getElementById('takeLocationId').innerHTML = '<option value="">Pilih Lokasi</option>';
        document.getElementById('takeAvailableStock').textContent = '-';
        document.getElementById('takeQuantity').value = '';
        document.getElementById('takeNote').value = '';
        document.getElementById('takeBatchId').innerHTML = '<option value="">Pilih batch yang akan diambil</option>';

        // Reset branch selection
        document.getElementById('takeRegularBranch').checked = true;
        document.getElementById('takeProjectBranch').checked = false;

        // Store base type_id for later use
        window.currentBaseTypeId = baseTypeId;

        // Show type image
        displayTakeTypeImage(category, baseTypeId);

        // Always show branch selection for consistency across all items
        const branchSelection = document.getElementById('takeBranchSelection');
        branchSelection.style.display = 'block';

        // Load initial locations (will default to regular branch)
        loadTakeLocations();

        // Add event listeners for branch selection
        document.getElementById('takeRegularBranch').addEventListener('change', loadTakeLocations);
        document.getElementById('takeProjectBranch').addEventListener('change', loadTakeLocations);

        var modal = new bootstrap.Modal(document.getElementById('quickTakeModal'));
        modal.show();
    }

    function loadTakeLocations() {
        const category = document.getElementById('takeCategory').value;
        const baseTypeId = window.currentBaseTypeId || document.getElementById('takeTypeId').value.replace('_PROJECT', '');
        const isProject = document.getElementById('takeProjectBranch').checked;
        const typeId = isProject ? baseTypeId + '_PROJECT' : baseTypeId;

        // Update hidden field with actual type_id
        document.getElementById('takeTypeId').value = typeId;

        // Clear form fields when switching branches
        document.getElementById('takeLocationId').innerHTML = '<option value="">Pilih Lokasi</option>';
        document.getElementById('takeQuantity').value = '';
        document.getElementById('takeAvailableStock').textContent = '-';

        fetch('<?= site_url('storage/get_stock'); ?>?category=' + category + '&type_id=' + typeId)
            .then(response => response.json())
            .then(data => {
                const locationSelect = document.getElementById('takeLocationId');
                locationSelect.innerHTML = '<option value="">Pilih Lokasi</option>';

                if (data.success && data.stock_locations.length > 0) {
                    data.stock_locations.forEach(location => {
                        const option = document.createElement('option');
                        option.value = location.location_id;
                        option.textContent = location.location_id + ' (Stok: ' + location.amount + ')';
                        locationSelect.appendChild(option);
                    });

                    document.getElementById('takeAvailableStock').textContent = data.total_stock;
                } else {
                    document.getElementById('takeAvailableStock').textContent = '0';
                }

                // Show/hide project indicator
                const projectIndicator = document.getElementById('takeProjectIndicator');
                projectIndicator.style.display = isProject ? 'inline-block' : 'none';

                // Handle batch selection for project items
                const batchSelection = document.getElementById('takeBatchSelection');
                const batchSelect = document.getElementById('takeBatchId');
                if (isProject) {
                    batchSelection.style.display = 'block';
                } else {
                    batchSelection.style.display = 'none';
                    // Clear batch selection when switching to regular stock
                    batchSelect.value = '';
                    batchSelect.innerHTML = '<option value="">Pilih batch yang akan diambil</option>';
                }
            });
    }

    function submitQuickStore() {
        const isProject = document.getElementById('storeProjectBranch').checked;
        const isNewBatch = document.getElementById('storeNewBatch').checked;
        const locationSelect = document.getElementById('storeLocationId');
        const locationId = locationSelect.value;
        const projectName = document.getElementById('storeProjectName').value;
        const quantity = document.getElementById('storeQuantity').value;

        // Validation
        if (!locationId) {
            AirSystemUtils.showErrorMessage('Pilih lokasi terlebih dahulu');
            return;
        }

        if (!quantity || quantity < 1) {
            AirSystemUtils.showErrorMessage('Masukkan jumlah yang valid');
            return;
        }

        // If project with new batch, validate project name
        if (isProject && isNewBatch && !projectName.trim()) {
            AirSystemUtils.showErrorMessage('Masukkan nama project untuk batch baru');
            return;
        }

        // If project with existing batch, validate batch selection
        if (isProject && !isNewBatch) {
            const batchId = document.getElementById('storeBatchId').value;
            if (!batchId) {
                AirSystemUtils.showErrorMessage('Pilih batch yang ada atau buat batch baru');
                return;
            }
        }

        // Temporarily enable location field if disabled to include it in form data
        const wasDisabled = locationSelect.disabled;
        if (wasDisabled) {
            locationSelect.disabled = false;
        }

        const form = document.getElementById('quickStoreForm');
        const formData = new FormData(form);

        // Re-disable if it was disabled
        if (wasDisabled) {
            locationSelect.disabled = true;
        }

        fetch('<?= site_url('storage/quick_action'); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    AirSystemUtils.showErrorMessage(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                AirSystemUtils.showErrorMessage('Terjadi kesalahan saat menyimpan barang');
            });
    }

    // Initialize event listeners when DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        // Remove previous event listeners to avoid duplication
        const regularBranch = document.getElementById('takeRegularBranch');
        const projectBranch = document.getElementById('takeProjectBranch');

        if (regularBranch) {
            regularBranch.removeEventListener('change', loadTakeLocations);
        }
        if (projectBranch) {
            projectBranch.removeEventListener('change', loadTakeLocations);
        }
    });

    // Helper function to show notifications
    function showNotification(type, message) {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = 'cust-notification m-3';
        notification.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;

        // Insert at top of body
        document.body.insertBefore(notification, document.body.firstChild);

        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            const alert = notification.querySelector('.alert');
            if (alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 5000);
    }

    function submitQuickTake() {
        const form = document.getElementById('quickTakeForm');
        const formData = new FormData(form);

        // Validate required fields
        const locationId = document.getElementById('takeLocationId').value;
        const quantity = document.getElementById('takeQuantity').value;
        const typeId = document.getElementById('takeTypeId').value;

        if (!locationId) {
            AirSystemUtils.showErrorMessage('Pilih lokasi terlebih dahulu');
            return;
        }

        if (!quantity || quantity <= 0) {
            AirSystemUtils.showErrorMessage('Masukkan jumlah yang valid');
            return;
        }

        // Check if batch selection is required for project items
        const isProject = typeId.endsWith('_PROJECT');
        const batchId = document.getElementById('takeBatchId').value;

        if (isProject && !batchId) {
            alert('Pilih batch project terlebih dahulu');
            return;
        }

        // If not a project item, remove batch_id from form data to avoid confusion
        if (!isProject) {
            formData.delete('batch_id');
        }

        // Find the submit button
        const submitBtn = document.querySelector('#quickTakeModal .btn-warning');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Memproses...';
        }

        fetch('<?= site_url('storage/quick_action'); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    const modal = bootstrap.Modal.getInstance(document.getElementById('quickTakeModal'));
                    modal.hide();

                    // Show success notification
                    showNotification('success', 'Barang berhasil diambil dari penyimpanan');

                    // Reload page after short delay
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    alert('Error: ' + data.message);
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Ambil Barang';
                    }
                }
            })
            .catch(error => {
                alert('Terjadi kesalahan: ' + error.message);
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Ambil Barang';
                }
            });
    }

    // Update available stock when location changes in take modal
    document.getElementById('takeLocationId').addEventListener('change', function() {
        const locationId = this.value;
        const category = document.getElementById('takeCategory').value;
        const typeId = document.getElementById('takeTypeId').value;

        if (locationId && category && typeId) {
            fetch('<?= site_url('storage/get_item_details'); ?>?location_id=' + locationId + '&category=' + category + '&type_id=' + typeId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('takeAvailableStock').textContent = data.item.amount;
                        document.getElementById('takeQuantity').max = data.item.amount;

                        // If this is a project item, load batch information
                        const isProject = typeId.endsWith('_PROJECT');
                        if (isProject && data.project_batches) {
                            const batchSelect = document.getElementById('takeBatchId');
                            batchSelect.innerHTML = '<option value="">Pilih batch yang akan diambil</option>';

                            data.project_batches.forEach(batch => {
                                const option = document.createElement('option');
                                option.value = batch.batch_id;
                                option.textContent = `Batch ${batch.batch_id} (${batch.remaining_quantity} pcs) - ${batch.project_name}`;
                                batchSelect.appendChild(option);
                            });
                        }
                    }
                });
        }
    });

    // Function to show full note in modal
    function showFullNote(storingId, note) {
        document.getElementById('modalStoringId').textContent = storingId;
        document.getElementById('modalNoteContent').textContent = note;

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('fullNoteModal'));
        modal.show();
    }

    // Global variables for current item
    let currentCategory = '';
    let currentTypeId = '';

    // Manage item function - opens comprehensive management modal
    function manageItem(category, typeId) {
        currentCategory = category;
        currentTypeId = typeId;

        document.getElementById('manageItemInfo').textContent = category + ' - ' + typeId;

        // Show modal first
        const modal = new bootstrap.Modal(document.getElementById('itemManagementModal'));
        modal.show();

        // Load data after modal is shown
        loadItemBatches(category, typeId);
        loadItemLocations(category, typeId);
    }

    // Load item batches
    function loadItemBatches(category, typeId) {
        document.getElementById('batchesList').innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 text-muted">Memuat data batch...</p>
            </div>
        `;

        fetch(`<?= site_url('storage/get_item_batches'); ?>?category=${category}&type_id=${typeId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayBatches(data.batches, data.total_stock);
                } else {
                    document.getElementById('batchesList').innerHTML = `
                        <div class="alert alert-warning">
                            <h6>Tidak ada batch project</h6>
                            <p class="mb-0">Item ini belum memiliki batch project. Klik "Buat Batch Baru" untuk membuat batch pertama.</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                document.getElementById('batchesList').innerHTML = `
                    <div class="alert alert-danger">
                        Error loading batches: ${error.message}
                    </div>
                `;
            });
    }

    // Display batches
    function displayBatches(batches, totalStock) {
        document.getElementById('manageTotalStock').textContent = totalStock || 0;

        let html = '';

        if (batches.length === 0) {
            html = `
                <div class="alert alert-info">
                    <h6>Tidak ada batch project</h6>
                    <p class="mb-0">Item ini belum memiliki batch project. Klik "Buat Batch Baru" untuk membuat batch pertama.</p>
                </div>
            `;
        } else {
            batches.forEach(batch => {
                const progressPercentage = batch.batch_quantity > 0 ?
                    Math.round((batch.remaining_quantity / batch.batch_quantity) * 100) : 0;

                html += `
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <h6 class="card-title mb-1">${batch.project_name || 'Unnamed Project'}</h6>
                                    <p class="text-muted small mb-2">${batch.batch_id}</p>
                                    <p class="card-text small mb-0">${batch.project_notes || 'Tidak ada catatan'}</p>
                                </div>
                                <div class="col-md-3">
                                    <div class="d-flex flex-column">
                                        <small class="text-muted">Stok Tersisa</small>
                                        <div class="d-flex align-items-center">
                                            <strong class="me-2">${batch.remaining_quantity}</strong>
                                            <span class="text-muted">/ ${batch.batch_quantity}</span>
                                        </div>
                                        <div class="progress mt-1" style="height: 6px;">
                                            <div class="progress-bar ${progressPercentage < 25 ? 'bg-danger' : progressPercentage < 50 ? 'bg-warning' : 'bg-success'}" 
                                                 style="width: ${progressPercentage}%"></div>
                                        </div>
                                        <small class="text-muted mt-1">${progressPercentage}%</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" onclick="editBatch('${batch.batch_id}', '${batch.project_name}', '${batch.project_notes}', ${batch.batch_quantity}, ${batch.remaining_quantity})" title="Edit batch">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        ${batch.remaining_quantity > 0 ? 
                                            `<button class="btn btn-outline-danger disabled-delete-btn" title="Tidak dapat dihapus - masih ada ${batch.remaining_quantity} item tersisa dalam batch ini. Silakan habiskan batch terlebih dahulu." style="opacity: 0.6; cursor: not-allowed;">
                                                <i class="fas fa-trash"></i> Hapus
                                            </button>` : 
                                            `<button class="btn btn-outline-danger" onclick="deleteBatch('${batch.batch_id}', '${batch.project_name}')" title="Hapus batch">
                                                <i class="fas fa-trash"></i> Hapus
                                            </button>`
                                        }
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
        }

        document.getElementById('batchesList').innerHTML = html;
        // Reinitialize tooltips for dynamically created elements
        setTimeout(function() {
            var tooltips = document.querySelectorAll('[title]');
            tooltips.forEach(function(element) {
                if (element.getAttribute('data-bs-original-title')) return; // Skip if already initialized
                new bootstrap.Tooltip(element, {
                    placement: 'top',
                    trigger: 'hover'
                });
            });
        }, 100);
    }

    // Load item locations
    function loadItemLocations(category, typeId) {
        document.getElementById('locationsList').innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 text-muted">Memuat data lokasi...</p>
            </div>
        `;

        fetch(`<?= site_url('storage/get_item_locations'); ?>?category=${category}&type_id=${typeId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayLocations(data.locations);
                } else {
                    document.getElementById('locationsList').innerHTML = `
                        <div class="alert alert-warning">Tidak ada data lokasi tersedia.</div>
                    `;
                }
            });
    }

    // Display locations
    function displayLocations(locations) {
        let html = '';

        if (locations.length === 0) {
            html = `
                <div class="alert alert-info">
                    <h6>Item belum ada di storage</h6>
                    <p class="mb-0">Item ini belum disimpan di lokasi manapun. Gunakan fungsi "Simpan" untuk menambahkan ke lokasi.</p>
                </div>
            `;
        } else {
            locations.forEach(location => {
                html += `
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-4">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                        ${location.location_id}
                                    </h6>
                                </div>
                                <div class="col-md-4 text-center">
                                    <span class="badge bg-info fs-6">${location.amount} items</span>
                                </div>
                                <div class="col-md-4 text-end">
                                    <div class="btn-group btn-group-sm">
                                        ${location.amount > 0 ? 
                                            `<button class="btn btn-outline-danger disabled-delete-btn" title="Tidak dapat dihapus - masih ada ${location.amount} item di lokasi ini. Silakan kosongkan stok terlebih dahulu." style="opacity: 0.6; cursor: not-allowed;">
                                                <i class="fas fa-times"></i> Hapus
                                            </button>` : 
                                            `<button class="btn btn-outline-danger" onclick="removeFromLocation('${location.location_id}')" title="Hapus dari lokasi">
                                                <i class="fas fa-times"></i> Hapus
                                            </button>`
                                        }
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
        }

        document.getElementById('locationsList').innerHTML = html;
        // Reinitialize tooltips for dynamically created elements
        setTimeout(function() {
            var tooltips = document.querySelectorAll('[title]');
            tooltips.forEach(function(element) {
                if (element.getAttribute('data-bs-original-title')) return; // Skip if already initialized
                new bootstrap.Tooltip(element, {
                    placement: 'top',
                    trigger: 'hover'
                });
            });
        }, 100);
    }

    // Edit batch function
    function editBatch(batchId, projectName, projectNotes, initialQty, remainingQty) {
        document.getElementById('editBatchId').value = batchId;
        document.getElementById('editProjectName').value = projectName;
        document.getElementById('editProjectNotes').value = projectNotes || '';

        // Display quantities as read-only information
        document.getElementById('displayInitialQuantity').textContent = initialQty;
        document.getElementById('displayRemainingQuantity').textContent = remainingQty;

        const modal = new bootstrap.Modal(document.getElementById('batchEditModal'));
        modal.show();
    }

    // Save batch edit
    function saveBatchEdit() {
        const form = document.getElementById('batchEditForm');
        const projectName = document.getElementById('editProjectName').value.trim();
        const batchId = document.getElementById('editBatchId').value;

        // Client-side validation
        if (!projectName) {
            alert('Nama project harus diisi!');
            document.getElementById('editProjectName').focus();
            return;
        }

        if (!batchId) {
            alert('Batch ID tidak ditemukan! Silakan tutup dan buka kembali form edit.');
            return;
        }

        const formData = new FormData(form);

        // Show loading state
        const submitButton = document.querySelector('#batchEditModal .btn-primary');
        const originalText = submitButton.textContent;
        submitButton.textContent = 'Menyimpan...';
        submitButton.disabled = true;

        fetch('<?= site_url('storage/update_batch'); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('batchEditModal'));
                    modal.hide();

                    // Reload batches
                    loadItemBatches(currentCategory, currentTypeId);

                    // Show success message
                    alert('Batch berhasil diperbarui!');
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                alert('Terjadi kesalahan saat menyimpan. Silakan coba lagi.');
            })
            .finally(() => {
                // Restore button state
                submitButton.textContent = originalText;
                submitButton.disabled = false;
            });
    }

    // Delete batch function
    function deleteBatch(batchId, projectName) {
        if (confirm(`Apakah Anda yakin ingin menghapus batch "${projectName}"?\n\nTindakan ini akan menghapus batch dan semua data terkait.`)) {
            fetch('<?= site_url('storage/delete_batch'); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        batch_id: batchId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Reload batches
                        loadItemBatches(currentCategory, currentTypeId);
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
        }
    }

    // Create new batch
    function createNewBatch() {
        document.getElementById('newBatchCategory').value = currentCategory;
        document.getElementById('newBatchTypeId').value = currentTypeId;

        // Load available locations
        loadAvailableLocations();

        const modal = new bootstrap.Modal(document.getElementById('newBatchModal'));
        modal.show();
    }

    // Load available locations for new batch
    function loadAvailableLocations() {
        fetch('<?= site_url('storage/get_all_locations'); ?>')
            .then(response => response.json())
            .then(data => {
                const select = document.getElementById('newLocationSelect');
                select.innerHTML = '<option value="">Pilih lokasi...</option>';

                if (data.success && data.locations) {
                    data.locations.forEach(location => {
                        select.innerHTML += `<option value="${location.location_id}">${location.location_id}</option>`;
                    });
                }
            });
    }

    // Save new batch
    function saveNewBatch() {
        const form = document.getElementById('newBatchForm');
        const formData = new FormData(form);

        fetch('<?= site_url('storage/create_batch'); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('newBatchModal'));
                    modal.hide();

                    // Clear form
                    form.reset();

                    // Reload batches and locations
                    loadItemBatches(currentCategory, currentTypeId);
                    loadItemLocations(currentCategory, currentTypeId);
                } else {
                    alert('Error: ' + data.message);
                }
            });
    }

    // Delete item function
    function deleteItem(category, typeId) {
        if (confirm(`Apakah Anda yakin ingin menghapus semua stok untuk ${category} - ${typeId}?\n\nPeringatan: Tindakan ini tidak dapat dibatalkan dan akan menghapus semua data terkait termasuk batch project!`)) {
            fetch('<?= site_url('storage/delete_item'); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        category: category,
                        type_id: typeId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        AirSystemUtils.showSuccessMessage('Item berhasil dihapus');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Terjadi kesalahan: ' + error.message);
                });
        }
    }

    // Remove from location
    function removeFromLocation(locationId) {
        if (confirm(`Apakah Anda yakin ingin menghapus item dari lokasi ${locationId}?`)) {
            fetch('<?= site_url('storage/remove_from_location'); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        category: currentCategory,
                        type_id: currentTypeId,
                        location_id: locationId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadItemLocations(currentCategory, currentTypeId);
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
        }
    }

    function displayStoreTypeImage(category, typeId) {
        const typeImageContainer = document.getElementById('storeTypeImageContainer');
        const typeImage = document.getElementById('storeTypeImage');
        const typeImageLabel = document.getElementById('storeTypeImageLabel');

        // Don't show image container for manifold or regulator
        if (category === 'manifold' || category === 'regulator') {
            typeImageContainer.style.display = 'none';
            return;
        }

        // Fetch type image from server
        fetch(`<?= site_url('storage/get_type_image'); ?>?category=${category}&type_id=${typeId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.type_image) {
                    let imageUrl = null;
                    if (category === 'pneumatic') {
                        imageUrl = '<?= base_url('assets/img/pneumatic_types/'); ?>' + data.type_image;
                    } else if (category === 'fitting') {
                        imageUrl = '<?= base_url('assets/img/fitting_types/'); ?>' + data.type_image;
                    } else if (category === 'solenoid') {
                        imageUrl = '<?= base_url('assets/img/solenoid_types/'); ?>' + data.type_image;
                    }

                    if (imageUrl) {
                        typeImage.src = imageUrl;
                        typeImageLabel.textContent = typeId;
                        typeImageContainer.style.display = 'block';
                        return;
                    }
                }

                // Show placeholder if no image
                typeImage.src = '<?= base_url('assets/img/placeholder-image.svg'); ?>';
                typeImageLabel.textContent = typeId + ' (No image available)';
                typeImageContainer.style.display = 'block';
            })
            .catch(error => {
                console.error('Error loading type image:', error);
                typeImage.src = '<?= base_url('assets/img/placeholder-image.svg'); ?>';
                typeImageLabel.textContent = typeId + ' (No image available)';
                typeImageContainer.style.display = 'block';
            });
    }

    function displayTakeTypeImage(category, typeId) {
        const typeImageContainer = document.getElementById('takeTypeImageContainer');
        const typeImage = document.getElementById('takeTypeImage');
        const typeImageLabel = document.getElementById('takeTypeImageLabel');

        // Don't show image container for manifold or regulator
        if (category === 'manifold' || category === 'regulator') {
            typeImageContainer.style.display = 'none';
            return;
        }

        // Fetch type image from server
        fetch(`<?= site_url('storage/get_type_image'); ?>?category=${category}&type_id=${typeId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.type_image) {
                    let imageUrl = null;
                    if (category === 'pneumatic') {
                        imageUrl = '<?= base_url('assets/img/pneumatic_types/'); ?>' + data.type_image;
                    } else if (category === 'fitting') {
                        imageUrl = '<?= base_url('assets/img/fitting_types/'); ?>' + data.type_image;
                    } else if (category === 'solenoid') {
                        imageUrl = '<?= base_url('assets/img/solenoid_types/'); ?>' + data.type_image;
                    }

                    if (imageUrl) {
                        typeImage.src = imageUrl;
                        typeImageLabel.textContent = typeId;
                        typeImageContainer.style.display = 'block';
                        return;
                    }
                }

                // Show placeholder if no image
                typeImage.src = '<?= base_url('assets/img/placeholder-image.svg'); ?>';
                typeImageLabel.textContent = typeId + ' (No image available)';
                typeImageContainer.style.display = 'block';
            })
            .catch(error => {
                console.error('Error loading type image:', error);
                typeImage.src = '<?= base_url('assets/img/placeholder-image.svg'); ?>';
                typeImageLabel.textContent = typeId + ' (No image available)';
                typeImageContainer.style.display = 'block';
            });
    }

    // Initialize Bootstrap tooltips
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips for all buttons with title attribute
        var tooltips = document.querySelectorAll('[title]');
        tooltips.forEach(function(element) {
            new bootstrap.Tooltip(element, {
                placement: 'top',
                trigger: 'hover'
            });
        });

        // Prevent click events on disabled delete buttons
        var disabledBtns = document.querySelectorAll('.disabled-delete-btn');
        disabledBtns.forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            });
        });
    });
</script>