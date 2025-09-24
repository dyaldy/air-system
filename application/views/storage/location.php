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

                        <div class="mb-3" id="availableStockDiv" style="display: none;">
                            <label class="form-label">Stok Tersedia:</label>
                            <div id="actionAvailableStock" class="form-control-plaintext text-primary"></div>
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
                    <button type="button" class="btn" id="quickActionSubmit" onclick="submitQuickAction()">Aksi</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Item Details Modal -->
    <div class="modal fade" id="itemDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Barang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="itemDetailsContent">
                    <!-- Content will be loaded dynamically -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function quickStoreItem(category, typeId) {
        document.getElementById('actionType').value = 'store';
        document.getElementById('actionCategory').value = category;
        document.getElementById('actionTypeId').value = typeId;
        document.getElementById('actionItemInfo').textContent = category + ' - ' + typeId;
        document.getElementById('quickActionTitle').textContent = 'Simpan Cepat';
        document.getElementById('quickActionSubmit').textContent = 'Simpan Barang';
        document.getElementById('quickActionSubmit').className = 'btn btn-success';
        document.getElementById('availableStockDiv').style.display = 'none';

        var modal = new bootstrap.Modal(document.getElementById('quickActionModal'));
        modal.show();
    }

    function quickTakeItem(category, typeId, availableStock) {
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

        var modal = new bootstrap.Modal(document.getElementById('quickActionModal'));
        modal.show();
    }

    function submitQuickAction() {
        const form = document.getElementById('quickActionForm');
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
</script>