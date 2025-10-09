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
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3 class="text-dark m-0">Lokasi Penyimpanan: <?= htmlspecialchars($location_id); ?></h3>
                <p class="text-muted mb-0">Barang yang disimpan di lokasi ini</p>
            </div>
            <div class="d-flex gap-2">
                <a href="<?= site_url('storage/store'); ?>" class="btn btn-success">
                    <i class="fas fa-plus"></i> Simpan Barang
                </a>
                <a href="<?= site_url('storage/take'); ?>" class="btn btn-warning">
                    <i class="fas fa-minus"></i> Ambil Barang
                </a>
                <a href="<?= site_url('storage'); ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali ke Overview
                </a>
            </div>
        </div>
    </div>

    <!-- Card Body with Main Content -->
    <div class="card-body px-lg-5 px-4 py-4">

        <!-- Location Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Total Barang</h6>
                                <h3><?= count($storage_items); ?></h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-boxes fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-success text-white border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Total Jumlah</h6>
                                <h3><?= array_sum(array_column($storage_items, 'amount')); ?></h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-cubes fa-2x"></i>
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
                                <h6 class="card-title">Kategori</h6>
                                <h3><?= count(array_unique(array_column($storage_items, 'category'))); ?></h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-tags fa-2x"></i>
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
                                <h6 class="card-title">Terakhir Diperbarui</h6>
                                <small>
                                    <?php
                                    $latest = '';
                                    foreach ($storage_items as $item) {
                                        if ($item['updated_at'] > $latest) {
                                            $latest = $item['updated_at'];
                                        }
                                    }
                                    echo $latest ? date('M d, Y', strtotime($latest)) : 'Tidak ada data';
                                    ?>
                                </small>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-clock fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Storage Items Table -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card rounded-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Barang di Penyimpanan</h5>
                        <div class="btn-group">
                            <a href="<?= site_url('storage/export_location_excel/' . $location_id); ?>" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-download"></i> Ekspor Excel
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($storage_items)): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover" id="storageTable">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Kategori</th>
                                            <th>ID Tipe</th>
                                            <th>Jumlah</th>
                                            <th>Status</th>
                                            <th>Dibuat</th>
                                            <th>Terakhir Diperbarui</th>
                                            <th>Editor</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($storage_items as $item): ?>
                                            <?php
                                            $isProjectItem = strpos($item['type_id'], '_PROJECT') !== false;
                                            $displayTypeId = $isProjectItem ? str_replace('_PROJECT', '', $item['type_id']) : $item['type_id'];
                                            ?>
                                            <tr>
                                                <td>
                                                    <span class="badge bg-primary"><?= htmlspecialchars($item['category']); ?></span>
                                                </td>
                                                <td>
                                                    <strong><?= htmlspecialchars($displayTypeId); ?></strong>
                                                </td>
                                                <td>
                                                    <span class="badge bg-success fs-6"><?= number_format($item['amount'] ?? 0); ?></span>
                                                </td>
                                                <td>
                                                    <?php if ($isProjectItem): ?>
                                                        <span class="badge bg-warning text-dark">
                                                            <i class="fas fa-project-diagram"></i> Project
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Regular</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <small><?= date('M d, Y H:i', strtotime($item['created_at'])); ?></small>
                                                </td>
                                                <td>
                                                    <small><?= date('M d, Y H:i', strtotime($item['updated_at'])); ?></small>
                                                </td>
                                                <td>
                                                    <small><?= htmlspecialchars($item['editor_name'] ?? 'Tidak Diketahui'); ?></small>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <button type="button" class="btn btn-outline-success"
                                                            onclick="quickStoreItem('<?= $item['category']; ?>', '<?= $item['type_id']; ?>')">
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-warning"
                                                            onclick="quickTakeItem('<?= $item['category']; ?>', '<?= $item['type_id']; ?>', <?= $item['amount']; ?>)">
                                                            <i class="fas fa-minus"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-info"
                                                            onclick="viewItemDetails('<?= $item['category']; ?>', '<?= $item['type_id']; ?>')">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <?php if ($item['amount'] > 0): ?>
                                                            <button type="button" class="btn btn-outline-danger disabled-delete-btn"
                                                                title="Tidak dapat dihapus - masih ada <?= $item['amount']; ?> item di lokasi ini. Silakan kosongkan stok terlebih dahulu."
                                                                style="opacity: 0.6; cursor: not-allowed;">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        <?php else: ?>
                                                            <button type="button" class="btn btn-outline-danger"
                                                                onclick="deleteItem('<?= $item['category']; ?>', '<?= $item['type_id']; ?>')">
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
                            <div class="alert alert-info text-center">
                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                <h5>Tidak ada barang disimpan di lokasi ini</h5>
                                <p>Mulai dengan <a href="<?= site_url('storage/store'); ?>">menyimpan beberapa barang</a> di lokasi ini.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Project Batches for this Location -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card rounded-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Project Batches</h5>
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="refreshBatches()">
                                <i class="fas fa-sync"></i> Refresh
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($project_batches)): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover" id="batchesTable">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Batch ID</th>
                                            <th>Project Name</th>
                                            <th>Category</th>
                                            <th>Type ID</th>
                                            <th>Initial Qty</th>
                                            <th>Remaining</th>
                                            <th>Created By</th>
                                            <th>Created At</th>
                                            <th>Notes</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($project_batches as $batch): ?>
                                            <tr>
                                                <td>
                                                    <code class="small"><?= htmlspecialchars($batch['batch_id']); ?></code>
                                                </td>
                                                <td>
                                                    <strong><?= htmlspecialchars($batch['project_name']); ?></strong>
                                                </td>
                                                <td>
                                                    <span class="badge bg-primary"><?= htmlspecialchars($batch['category']); ?></span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary"><?= htmlspecialchars($batch['type_id']); ?></span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info"><?= number_format($batch['batch_quantity']); ?></span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-success"><?= number_format($batch['remaining_quantity']); ?></span>
                                                </td>
                                                <td>
                                                    <small><?= htmlspecialchars($batch['created_by_name'] ?? 'System'); ?></small>
                                                </td>
                                                <td>
                                                    <small><?= date('M d, Y H:i', strtotime($batch['created_at'])); ?></small>
                                                </td>
                                                <td>
                                                    <?php if ($batch['project_notes']): ?>
                                                        <button type="button" class="btn btn-sm btn-outline-info"
                                                            onclick="showBatchNotes('<?= htmlspecialchars($batch['batch_id']); ?>', '<?= htmlspecialchars(addslashes($batch['project_notes'])); ?>')">
                                                            <i class="fas fa-sticky-note"></i> View
                                                        </button>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($batch['remaining_quantity'] > 0): ?>
                                                        <button type="button" class="btn btn-sm btn-outline-danger disabled-delete-btn"
                                                            title="Tidak dapat dihapus - masih ada <?= $batch['remaining_quantity']; ?> item tersisa dalam batch ini. Silakan habiskan batch terlebih dahulu."
                                                            style="opacity: 0.6; cursor: not-allowed;">
                                                            <i class="fas fa-trash"></i> Hapus
                                                        </button>
                                                    <?php else: ?>
                                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                                            onclick="deleteBatch('<?= htmlspecialchars($batch['batch_id']); ?>')">
                                                            <i class="fas fa-trash"></i> Hapus
                                                        </button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info text-center">
                                <i class="fas fa-boxes fa-3x mb-3"></i>
                                <h5>Tidak ada project batches di lokasi ini</h5>
                                <p>Project batches akan muncul di sini ketika Anda menyimpan barang project.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Transactions for this Location -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Transaksi Terbaru</h5>
                        <a href="<?= site_url('storage/reports?location=' . $location_id); ?>" class="btn btn-sm btn-outline-primary">
                            Lihat Semua Transaksi
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($location_transactions)): ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Tanggal/Waktu</th>
                                            <th>Aksi</th>
                                            <th>Kategori</th>
                                            <th>ID Tipe</th>
                                            <th>Batch/Project</th>
                                            <th>Pengguna</th>
                                            <th>Catatan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($location_transactions as $transaction): ?>
                                            <tr>
                                                <td><?= date('M d, Y H:i', strtotime($transaction['datetime'])); ?></td>
                                                <td>
                                                    <?php if ($transaction['action'] == 'store'): ?>
                                                        <span class="badge bg-success">Simpan</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning">Ambil</span>
                                                    <?php endif; ?>
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
                            <div class="alert alert-info">
                                Belum ada transaksi tercatat untuk lokasi ini.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Action Modal -->
    <div class="modal fade" id="quickActionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="quickActionTitle">Aksi Cepat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="quickActionForm">
                        <input type="hidden" id="actionType" name="action">
                        <input type="hidden" id="actionCategory" name="category">
                        <input type="hidden" id="actionTypeId" name="type_id">
                        <input type="hidden" name="location_id" value="<?= $location_id; ?>">

                        <div class="mb-3">
                            <label class="form-label">Barang:</label>
                            <div id="actionItemInfo" class="form-control-plaintext"></div>
                        </div>

                        <!-- Type Image Display -->
                        <div class="mb-3" id="quickActionTypeImageContainer" style="display: none;">
                            <label class="form-label">Gambar Tipe</label>
                            <div class="card" style="max-width: 250px; margin: 0 auto;">
                                <img id="quickActionTypeImage" src="" alt="Type Image" class="card-img-top" style="object-fit: contain; max-height: 200px;" onerror="this.src='<?= base_url('assets/img/placeholder-image.svg'); ?>'">
                                <div class="card-body py-2 text-center">
                                    <small class="text-muted" id="quickActionTypeImageLabel"></small>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3" id="availableStockDiv" style="display: none;">
                            <label class="form-label">Stok Tersedia:</label>
                            <div id="actionAvailableStock" class="form-control-plaintext text-primary"></div>
                        </div>

                        <!-- Batch Selection for Project Items -->
                        <div class="mb-3" id="batchSelectionDiv" style="display: none;">
                            <label for="actionBatchId" class="form-label">Pilih Batch Project <span class="text-danger">*</span></label>
                            <select class="form-select" id="actionBatchId" name="batch_id">
                                <option value="">Pilih batch yang akan diambil</option>
                            </select>
                            <div class="form-text">Pilih batch project spesifik untuk diambil</div>
                        </div>

                        <div class="mb-3">
                            <label for="actionQuantity" class="form-label">Jumlah:</label>
                            <input type="number" class="form-control" id="actionQuantity" name="quantity" min="1" required>
                        </div>

                        <div class="mb-3">
                            <label for="actionNote" class="form-label">Catatan (Opsional):</label>
                            <textarea class="form-control" id="actionNote" name="note" rows="2"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn" id="quickActionSubmit">Aksi</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Batch Notes Modal -->
    <div class="modal fade" id="batchNotesModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Batch Notes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <strong>Batch ID:</strong>
                        <code id="batchNotesId"></code>
                    </div>
                    <div>
                        <strong>Project Notes:</strong>
                        <div class="mt-2 p-3 bg-light rounded" id="batchNotesContent" style="white-space: pre-wrap; word-wrap: break-word;"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="deleteConfirmMessage"></p>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Perhatian:</strong> Tindakan ini tidak dapat dibatalkan.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn" onclick="executeDelete()">Hapus</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Item Details Modal -->
    <div class="modal fade" id="itemDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="itemDetailsContent">
                    <!-- Content will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function quickStoreItem(category, typeId) {
        // Reset modal state
        resetQuickActionModal();

        document.getElementById('actionType').value = 'store';
        document.getElementById('actionCategory').value = category;
        document.getElementById('actionTypeId').value = typeId;
        document.getElementById('actionItemInfo').textContent = category + ' - ' + typeId;
        document.getElementById('quickActionTitle').textContent = 'Simpan Cepat';
        document.getElementById('quickActionSubmit').textContent = 'Simpan Barang';
        document.getElementById('quickActionSubmit').className = 'btn btn-success';
        document.getElementById('availableStockDiv').style.display = 'none';

        // Batch selection is already hidden by resetQuickActionModal()

        // Show type image
        displayQuickActionTypeImage(category, typeId);

        var modal = new bootstrap.Modal(document.getElementById('quickActionModal'));
        modal.show();
    }

    function quickTakeItem(category, typeId, availableStock) {
        // Reset modal state
        resetQuickActionModal();

        document.getElementById('actionType').value = 'take';
        document.getElementById('actionCategory').value = category;
        document.getElementById('actionTypeId').value = typeId;
        document.getElementById('actionItemInfo').textContent = category + ' - ' + typeId;
        document.getElementById('quickActionTitle').textContent = 'Ambil Cepat';
        document.getElementById('quickActionSubmit').textContent = 'Ambil Barang';
        document.getElementById('quickActionSubmit').className = 'btn btn-warning';
        document.getElementById('availableStockDiv').style.display = 'block';
        document.getElementById('actionAvailableStock').textContent = availableStock + ' items';
        document.getElementById('actionQuantity').max = availableStock;

        // Check if this is a project item
        const isProjectItem = typeId.includes('_PROJECT');
        const batchSelectionDiv = document.getElementById('batchSelectionDiv');
        const batchSelect = document.getElementById('actionBatchId');

        if (isProjectItem) {
            // Show batch selection and load available batches
            batchSelectionDiv.style.display = 'block';
            batchSelect.required = true;

            // Load batches for this project item
            loadProjectBatches(category, typeId);
        } else {
            // Hide batch selection for regular items
            batchSelectionDiv.style.display = 'none';
            batchSelect.required = false;
        }

        // Show type image
        displayQuickActionTypeImage(category, typeId);

        var modal = new bootstrap.Modal(document.getElementById('quickActionModal'));
        modal.show();
    }

    function loadProjectBatches(category, typeId) {
        const batchSelect = document.getElementById('actionBatchId');

        // Show loading state
        batchSelect.innerHTML = '<option value="">Memuat batch...</option>';
        batchSelect.disabled = true;

        // Fetch project batches for this location and item
        fetch(`<?= site_url('storage/get_item_details'); ?>?location_id=<?= $location_id; ?>&category=${category}&type_id=${typeId}`)
            .then(response => response.json())
            .then(data => {
                batchSelect.disabled = false;

                if (data.success && data.project_batches && data.project_batches.length > 0) {
                    // Clear loading state and populate with batches
                    batchSelect.innerHTML = '<option value="">Pilih batch yang akan diambil</option>';

                    data.project_batches.forEach(batch => {
                        const option = document.createElement('option');
                        option.value = batch.batch_id;
                        option.textContent = `${batch.project_name || 'Unnamed Project'} (Sisa: ${batch.remaining_quantity}) - ${batch.batch_id}`;
                        option.dataset.remainingQuantity = batch.remaining_quantity;
                        batchSelect.appendChild(option);
                    });

                    // Update quantity max based on batch selection
                    batchSelect.addEventListener('change', function() {
                        const selectedOption = this.selectedOptions[0];
                        const quantityInput = document.getElementById('actionQuantity');

                        if (selectedOption && selectedOption.dataset.remainingQuantity) {
                            const maxQuantity = parseInt(selectedOption.dataset.remainingQuantity);
                            quantityInput.max = maxQuantity;
                            quantityInput.placeholder = `Maksimal: ${maxQuantity}`;

                            // Update available stock display
                            const availableStockDiv = document.getElementById('actionAvailableStock');
                            availableStockDiv.textContent = maxQuantity + ' items (dari batch ini)';
                        }
                    });
                } else {
                    // No batches available
                    batchSelect.innerHTML = '<option value="">Tidak ada batch tersedia</option>';
                    document.getElementById('quickActionSubmit').disabled = true;
                    AirSystemUtils.showErrorMessage('Tidak ada batch project tersedia untuk item ini');
                }
            })
            .catch(error => {
                console.error('Error loading batches:', error);
                batchSelect.disabled = false;
                batchSelect.innerHTML = '<option value="">Error memuat batch</option>';
                AirSystemUtils.showErrorMessage('Gagal memuat data batch');
            });
    }

    function submitQuickAction() {
        const form = document.getElementById('quickActionForm');
        if (!form) {
            alert('Error: Form not found');
            return;
        }

        const formData = new FormData(form);

        // Basic validation
        const actionType = document.getElementById('actionType').value;
        const typeId = document.getElementById('actionTypeId').value;
        const quantity = document.getElementById('actionQuantity').value;

        if (!actionType || !typeId || !quantity || quantity <= 0) {
            alert('Mohon lengkapi semua field yang diperlukan');
            return;
        }

        const isProjectItem = typeId.includes('_PROJECT');

        if (actionType === 'take' && isProjectItem) {
            const batchId = document.getElementById('actionBatchId').value;
            if (!batchId) {
                alert('Pilih batch project terlebih dahulu');
                return;
            }
        }

        // Disable submit button to prevent double submission
        const submitBtn = document.getElementById('quickActionSubmit');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Memproses...';

        fetch('<?= site_url('storage/quick_action'); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Close modal first
                    const modal = bootstrap.Modal.getInstance(document.getElementById('quickActionModal'));
                    if (modal) {
                        modal.hide();
                    }

                    // Reload page to show flash message
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                alert('Terjadi kesalahan saat memproses permintaan: ' + error.message);
            })
            .finally(() => {
                // Re-enable submit button
                submitBtn.disabled = false;
                if (actionType === 'take') {
                    submitBtn.textContent = 'Ambil Barang';
                } else {
                    submitBtn.textContent = 'Simpan Barang';
                }
            });
    }

    function resetQuickActionModal() {
        // Reset form
        document.getElementById('quickActionForm').reset();

        // Reset batch selection
        const batchSelectionDiv = document.getElementById('batchSelectionDiv');
        const batchSelect = document.getElementById('actionBatchId');

        batchSelectionDiv.style.display = 'none';
        batchSelect.required = false;
        batchSelect.innerHTML = '<option value="">Pilih batch yang akan diambil</option>';
        batchSelect.disabled = false;

        // Reset submit button
        document.getElementById('quickActionSubmit').disabled = false;

        // Reset quantity input
        const quantityInput = document.getElementById('actionQuantity');
        quantityInput.max = '';
        quantityInput.placeholder = '';
    }

    function viewItemDetails(category, typeId) {
        // Load item details
        fetch(`<?= site_url('storage/get_item_details'); ?>?location_id=<?= $location_id; ?>&category=${category}&type_id=${typeId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const item = data.item;
                    let html = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Basic Information</h6>
                            <table class="table table-sm">
                                <tr><td><strong>Location:</strong></td><td><?= $location_id; ?></td></tr>
                                <tr><td><strong>Category:</strong></td><td>${item.category}</td></tr>
                                <tr><td><strong>Type ID:</strong></td><td>${item.type_id}</td></tr>
                                <tr><td><strong>Quantity:</strong></td><td>${item.amount}</td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Timestamps</h6>
                            <table class="table table-sm">
                                <tr><td><strong>Created:</strong></td><td>${new Date(item.created_at).toLocaleString()}</td></tr>
                                <tr><td><strong>Last Updated:</strong></td><td>${new Date(item.updated_at).toLocaleString()}</td></tr>
                                <tr><td><strong>Editor:</strong></td><td>${item.editor}</td></tr>
                            </table>
                        </div>
                    </div>
                `;

                    if (item.storage_data) {
                        try {
                            const storageData = JSON.parse(item.storage_data);
                            html += `
                            <div class="row mt-3">
                                <div class="col-12">
                                    <h6>Additional Data</h6>
                                    <pre class="bg-light p-3">${JSON.stringify(storageData, null, 2)}</pre>
                                </div>
                            </div>
                        `;
                        } catch (e) {
                            html += `
                            <div class="row mt-3">
                                <div class="col-12">
                                    <h6>Additional Data</h6>
                                    <div class="alert alert-warning">Invalid JSON data</div>
                                </div>
                            </div>
                        `;
                        }
                    }

                    document.getElementById('itemDetailsContent').innerHTML = html;
                } else {
                    document.getElementById('itemDetailsContent').innerHTML = '<div class="alert alert-danger">Error loading item details</div>';
                }
            });

        var modal = new bootstrap.Modal(document.getElementById('itemDetailsModal'));
        modal.show();
    }

    function showBatchNotes(batchId, notes) {
        document.getElementById('batchNotesId').textContent = batchId;
        document.getElementById('batchNotesContent').textContent = notes;

        var modal = new bootstrap.Modal(document.getElementById('batchNotesModal'));
        modal.show();
    }

    function refreshBatches() {
        location.reload();
    }

    function deleteItem(category, typeId) {
        document.getElementById('deleteConfirmMessage').textContent = `Apakah Anda yakin ingin menghapus item "${category} - ${typeId}" dari lokasi ini? Semua data terkait akan dihapus secara permanen.`;
        document.getElementById('confirmDeleteBtn').setAttribute('data-type', 'item');
        document.getElementById('confirmDeleteBtn').setAttribute('data-category', category);
        document.getElementById('confirmDeleteBtn').setAttribute('data-type-id', typeId);
        document.getElementById('confirmDeleteBtn').setAttribute('data-location-id', '<?= $location_id; ?>');

        var modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
        modal.show();
    }

    function deleteBatch(batchId) {
        document.getElementById('deleteConfirmMessage').textContent = `Apakah Anda yakin ingin menghapus batch "${batchId}"? Batch ini akan dihapus secara permanen.`;
        document.getElementById('confirmDeleteBtn').setAttribute('data-type', 'batch');
        document.getElementById('confirmDeleteBtn').setAttribute('data-batch-id', batchId);

        var modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
        modal.show();
    }

    function executeDelete() {
        const deleteBtn = document.getElementById('confirmDeleteBtn');
        const deleteType = deleteBtn.getAttribute('data-type');

        let url, data;

        if (deleteType === 'item') {
            url = '<?= site_url('storage/delete_item'); ?>';
            data = {
                category: deleteBtn.getAttribute('data-category'),
                type_id: deleteBtn.getAttribute('data-type-id'),
                location_id: deleteBtn.getAttribute('data-location-id')
            };
        } else if (deleteType === 'batch') {
            url = '<?= site_url('storage/delete_batch'); ?>';
            data = {
                batch_id: deleteBtn.getAttribute('data-batch-id')
            };
        }

        fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menghapus data.');
            });

        // Close the modal
        var modal = bootstrap.Modal.getInstance(document.getElementById('deleteConfirmModal'));
        modal.hide();
    }

    function displayQuickActionTypeImage(category, typeId) {
        const typeImageContainer = document.getElementById('quickActionTypeImageContainer');
        const typeImage = document.getElementById('quickActionTypeImage');
        const typeImageLabel = document.getElementById('quickActionTypeImageLabel');

        // Find the storage item to get type image
        const storageData = <?= json_encode($storage_items ?? []); ?>;
        const baseTypeId = typeId.replace('_PROJECT', '');

        const item = storageData.find(item =>
            item.category === category &&
            (item.type_id === baseTypeId || item.type_id === typeId || item.type_id === baseTypeId + '_PROJECT')
        );

        if (item && item.type_image) {
            let imageUrl = null;
            if (category === 'pneumatic') {
                imageUrl = '<?= base_url('assets/img/pneumatic_types/'); ?>' + item.type_image;
            } else if (category === 'fitting') {
                imageUrl = '<?= base_url('assets/img/fitting_types/'); ?>' + item.type_image;
            }

            if (imageUrl) {
                typeImage.src = imageUrl;
                typeImageLabel.textContent = baseTypeId;
                typeImageContainer.style.display = 'block';
                return;
            }
        }

        // Show placeholder if no image
        typeImage.src = '<?= base_url('assets/img/placeholder-image.svg'); ?>';
        typeImageLabel.textContent = baseTypeId + ' (No image available)';
        typeImageContainer.style.display = 'block';
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

        // Add event listener for quick action submit button
        var quickActionSubmitBtn = document.getElementById('quickActionSubmit');
        if (quickActionSubmitBtn) {
            quickActionSubmitBtn.addEventListener('click', function(e) {
                e.preventDefault();
                submitQuickAction();
            });
        }
    });
</script>