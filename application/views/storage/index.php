<div class="container-fluid pt-5 mt-3">
    <!-- Flash Messages -->
    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $this->session->flashdata('success'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $this->session->flashdata('error'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="text-primary">Overview Penyimpanan</h2>
            <p class="text-muted">Kelola inventaris Anda di semua lokasi penyimpanan</p>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex gap-2 flex-wrap">
                <a href="<?= site_url('storage/store'); ?>" class="btn btn-success">
                    <i class="fas fa-plus"></i> Simpan Barang
                </a>
                <a href="<?= site_url('storage/take'); ?>" class="btn btn-warning">
                    <i class="fas fa-minus"></i> Ambil Barang
                </a>
                <a href="<?= site_url('storage/search'); ?>" class="btn btn-info">
                    <i class="fas fa-search"></i> Cari Penyimpanan
                </a>
                <a href="<?= site_url('storage/reports'); ?>" class="btn btn-secondary">
                    <i class="fas fa-chart-bar"></i> Laporan
                </a>
            </div>
        </div>
    </div>

    <!-- Storage Locations Overview -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Lokasi Penyimpanan</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($locations)): ?>
                        <div class="row">
                            <?php foreach ($locations as $location): ?>
                                <div class="col-md-3 col-sm-6 mb-3">
                                    <div class="card border-primary">
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
                                                <strong><?= number_format($item['total_amount']); ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-info"><?= $item['location_count']; ?> lokasi</span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <button type="button" class="btn btn-outline-success" onclick="quickStore('<?= $item['category']; ?>', '<?= $item['type_id']; ?>')">
                                                        Simpan
                                                    </button>
                                                    <button type="button" class="btn btn-outline-warning" onclick="quickTake('<?= $item['category']; ?>', '<?= $item['type_id']; ?>')">
                                                        Ambil
                                                    </button>
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
            <div class="card">
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
                                            <td><?= htmlspecialchars($transaction['type_id']); ?></td>
                                            <td><?= htmlspecialchars($transaction['user_name'] ?? 'Tidak Diketahui'); ?></td>
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
</script>