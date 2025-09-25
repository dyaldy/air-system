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
                        <h5 class="mb-0">Lokasi Penyimpanan</h5>
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
                                Tidak ada lokasi penyimpanan ditemukan. Mulai dengan menyimpan beberapa barang!
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
                        <h5 class="mb-0">Overview Inventaris</h5>
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
                                Belum ada barang di penyimpanan. <a href="<?= site_url('storage/store'); ?>">Mulai simpan barang</a>!
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
                        <h5 class="mb-0">Transaksi Terbaru</h5>
                        <a href="<?= site_url('storage/reports'); ?>" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($recent_transactions)): ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
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
                                        <?php foreach ($recent_transactions as $transaction): ?>
                                            <tr>
                                                <td><?= date('M d, Y H:i', strtotime($transaction['datetime'])); ?></td>
                                                <td>
                                                    <?php if ($transaction['action'] == 'store'): ?>
                                                        <span class="badge bg-success">Simpan</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning">Ambil</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($transaction['location_id']); ?></td>
                                                <td><?= htmlspecialchars($transaction['category']); ?></td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <?= htmlspecialchars($transaction['type_id']); ?>
                                                        <?php if (isset($transaction['comment']) && $transaction['comment'] === 'PROJECT'): ?>
                                                            <i class="fas fa-project-diagram text-primary ms-2" title="Barang Project"></i>
                                                        <?php endif; ?>
                                                    </div>
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
                                Belum ada transaksi.
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

                        <div class="mb-3">
                            <label for="takeLocationId" class="form-label">ID Lokasi:</label>
                            <select class="form-select" id="takeLocationId" name="location_id" required>
                                <option value="">Pilih Lokasi</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="takeQuantity" class="form-label">Jumlah:</label>
                            <input type="number" class="form-control" id="takeQuantity" name="quantity" min="1" required>
                            <div class="form-text">Tersedia: <span id="availableStock">-</span></div>
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
        document.getElementById('storeCategory').value = category;
        document.getElementById('storeTypeId').value = typeId;
        document.getElementById('storeItemInfo').textContent = category + ' - ' + typeId;

        var modal = new bootstrap.Modal(document.getElementById('quickStoreModal'));
        modal.show();
    }

    function quickTake(category, typeId) {
        document.getElementById('takeCategory').value = category;
        document.getElementById('takeTypeId').value = typeId;
        document.getElementById('takeItemInfo').textContent = category + ' - ' + typeId;

        // Load available locations for this item
        fetch('<?= site_url('storage/get_stock'); ?>?category=' + category + '&type_id=' + typeId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const locationSelect = document.getElementById('takeLocationId');
                    locationSelect.innerHTML = '<option value="">Pilih Lokasi</option>';

                    data.stock_locations.forEach(location => {
                        const option = document.createElement('option');
                        option.value = location.location_id;
                        option.textContent = location.location_id + ' (Stok: ' + location.amount + ')';
                        locationSelect.appendChild(option);
                    });

                    document.getElementById('availableStock').textContent = data.total_stock;
                }
            });

        var modal = new bootstrap.Modal(document.getElementById('quickTakeModal'));
        modal.show();
    }

    function submitQuickStore() {
        const form = document.getElementById('quickStoreForm');
        const formData = new FormData(form);

        fetch('<?= site_url('storage/quick_action'); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            });
    }

    function submitQuickTake() {
        const form = document.getElementById('quickTakeForm');
        const formData = new FormData(form);

        fetch('<?= site_url('storage/quick_action'); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
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
                        document.getElementById('availableStock').textContent = data.item.amount;
                        document.getElementById('takeQuantity').max = data.item.amount;
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
                        alert('Item berhasil dihapus');
                        location.reload();
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