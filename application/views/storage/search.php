<div class="container-fluid pt-5 mt-3">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="text-primary">Cari Penyimpanan</h2>
                    <p class="text-muted">Temukan barang di semua lokasi penyimpanan</p>
                </div>
                <a href="<?= site_url('storage'); ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali ke Penyimpanan
                </a>
            </div>
        </div>
    </div>

    <!-- Search Form -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Filter Pencarian</h6>
                </div>
                <div class="card-body">
                    <?= form_open('storage/search', ['method' => 'GET', 'class' => 'row g-3']); ?>
                    <div class="col-md-4">
                        <label for="q" class="form-label">Kata Kunci Pencarian</label>
                        <input type="text" class="form-control" id="q" name="q"
                            value="<?= htmlspecialchars($search_term ?? ''); ?>"
                            placeholder="Cari berdasarkan ID tipe, kategori, atau lokasi...">
                        <div class="form-text">Cari di ID tipe, kategori, atau lokasi</div>
                    </div>

                    <div class="col-md-3">
                        <label for="location" class="form-label">Lokasi</label>
                        <select class="form-select" id="location" name="location">
                            <option value="">Semua Lokasi</option>
                            <?php foreach ($locations as $location): ?>
                                <option value="<?= $location['location_id']; ?>"
                                    <?= $selected_location == $location['location_id'] ? 'selected' : ''; ?>>
                                    <?= $location['location_id']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="category" class="form-label">Kategori</label>
                        <select class="form-select" id="category" name="category">
                            <option value="">Semua Kategori</option>
                            <option value="pneumatic" <?= $selected_category == 'pneumatic' ? 'selected' : ''; ?>>Pneumatic</option>
                            <option value="valve" <?= $selected_category == 'valve' ? 'selected' : ''; ?>>Valve</option>
                            <option value="fitting" <?= $selected_category == 'fitting' ? 'selected' : ''; ?>>Fitting</option>
                            <option value="sensor" <?= $selected_category == 'sensor' ? 'selected' : ''; ?>>Sensor</option>
                            <option value="other" <?= $selected_category == 'other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Cari
                            </button>
                        </div>
                    </div>

                    <div class="col-12">
                        <a href="<?= site_url('storage/search'); ?>" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-times"></i> Hapus Pencarian
                        </a>
                    </div>
                    <?= form_close(); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Results -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        Hasil Pencarian
                        <?php if (!empty($search_term) || !empty($selected_location) || !empty($selected_category)): ?>
                            <small class="text-muted">
                                <?php
                                $filters = [];
                                if ($search_term) $filters[] = "Kata Kunci: \"$search_term\"";
                                if ($selected_location) $filters[] = "Lokasi: $selected_location";
                                if ($selected_category) $filters[] = "Kategori: $selected_category";
                                echo '(' . implode(', ', $filters) . ')';
                                ?>
                            </small>
                        <?php endif; ?>
                </div>
                <?php if (!empty($search_results)): ?>
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="exportToExcel()">
                            <i class="fas fa-download"></i> Ekspor Excel
                        </button>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (!empty($search_results)): ?>
                    <!-- Summary Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body py-3">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6>Barang Ditemukan</h6>
                                            <h4><?= count($search_results); ?></h4>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-boxes fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body py-3">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6>Total Jumlah</h6>
                                            <h4><?= array_sum(array_column($search_results, 'amount')); ?></h4>
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
                                <div class="card-body py-3">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6>Lokasi</h6>
                                            <h4><?= count(array_unique(array_column($search_results, 'location_id'))); ?></h4>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-map-marker-alt fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body py-3">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6>Kategori</h6>
                                            <h4><?= count(array_unique(array_column($search_results, 'category'))); ?></h4>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-tags fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Results Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="searchResultsTable">
                            <thead class="table-dark">
                                <tr>
                                    <th>Lokasi</th>
                                    <th>Kategori</th>
                                    <th>ID Tipe</th>
                                    <th>Jumlah</th>
                                    <th>Terakhir Diperbarui</th>
                                    <th>Editor</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($search_results as $item): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($item['location_id']); ?></strong>
                                            <br>
                                            <a href="<?= site_url('storage/location/' . $item['location_id']); ?>" class="text-decoration-none small">
                                                Lihat Lokasi
                                            </a>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary"><?= htmlspecialchars($item['category']); ?></span>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($item['type_id']); ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-success fs-6"><?= number_format($item['amount']); ?></span>
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
                                                    onclick="quickStore('<?= $item['location_id']; ?>', '<?= $item['category']; ?>', '<?= $item['type_id']; ?>')">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-warning"
                                                    onclick="quickTake('<?= $item['location_id']; ?>', '<?= $item['category']; ?>', '<?= $item['type_id']; ?>', <?= $item['amount']; ?>)">
                                                    <i class="fas fa-minus"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-info"
                                                    onclick="viewDetails('<?= $item['location_id']; ?>', '<?= $item['category']; ?>', '<?= $item['type_id']; ?>')">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Groupby Analysis -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Berdasarkan Kategori</h6>
                                </div>
                                <div class="card-body">
                                    <?php
                                    $categories = [];
                                    foreach ($search_results as $item) {
                                        if (!isset($categories[$item['category']])) {
                                            $categories[$item['category']] = ['count' => 0, 'quantity' => 0];
                                        }
                                        $categories[$item['category']]['count']++;
                                        $categories[$item['category']]['quantity'] += $item['amount'];
                                    }
                                    ?>
                                    <?php foreach ($categories as $category => $data): ?>
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div>
                                                <span class="badge bg-primary"><?= htmlspecialchars($category); ?></span>
                                            </div>
                                            <div>
                                                <small><?= $data['count']; ?> barang, <?= number_format($data['quantity']); ?> total</small>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Berdasarkan Lokasi</h6>
                                </div>
                                <div class="card-body">
                                    <?php
                                    $locations_group = [];
                                    foreach ($search_results as $item) {
                                        if (!isset($locations_group[$item['location_id']])) {
                                            $locations_group[$item['location_id']] = ['count' => 0, 'quantity' => 0];
                                        }
                                        $locations_group[$item['location_id']]['count']++;
                                        $locations_group[$item['location_id']]['quantity'] += $item['amount'];
                                    }
                                    ?>
                                    <?php foreach ($locations_group as $location_id => $data): ?>
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div>
                                                <strong><?= htmlspecialchars($location_id); ?></strong>
                                            </div>
                                            <div>
                                                <small><?= $data['count']; ?> barang, <?= number_format($data['quantity']); ?> total</small>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php elseif (!empty($search_term) || !empty($selected_location) || !empty($selected_category)): ?>
                    <div class="alert alert-info text-center">
                        <i class="fas fa-search fa-3x mb-3"></i>
                        <h5>Tidak ada barang ditemukan</h5>
                        <p>Tidak ada barang yang cocok dengan kriteria pencarian Anda. Coba sesuaikan kata kunci atau filter pencarian.</p>
                    </div>
                <?php else: ?>
                    <div class="alert alert-light text-center">
                        <i class="fas fa-search fa-3x mb-3 text-muted"></i>
                        <h5>Cari Barang Penyimpanan</h5>
                        <p>Gunakan form pencarian di atas untuk menemukan barang di penyimpanan. Anda dapat mencari berdasarkan ID tipe, kategori, atau lokasi.</p>
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
                    <input type="hidden" id="actionLocationId" name="location_id">
                    <input type="hidden" id="actionCategory" name="category">
                    <input type="hidden" id="actionTypeId" name="type_id">

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

<script>
    function quickStore(locationId, category, typeId) {
        document.getElementById('actionType').value = 'store';
        document.getElementById('actionLocationId').value = locationId;
        document.getElementById('actionCategory').value = category;
        document.getElementById('actionTypeId').value = typeId;
        document.getElementById('actionItemInfo').textContent = `${locationId} - ${category} - ${typeId}`;
        document.getElementById('quickActionTitle').textContent = 'Simpan Cepat';
        document.getElementById('quickActionSubmit').textContent = 'Simpan Barang';
        document.getElementById('quickActionSubmit').className = 'btn btn-success';
        document.getElementById('availableStockDiv').style.display = 'none';

        var modal = new bootstrap.Modal(document.getElementById('quickActionModal'));
        modal.show();
    }

    function quickTake(locationId, category, typeId, availableStock) {
        document.getElementById('actionType').value = 'take';
        document.getElementById('actionLocationId').value = locationId;
        document.getElementById('actionCategory').value = category;
        document.getElementById('actionTypeId').value = typeId;
        document.getElementById('actionItemInfo').textContent = `${locationId} - ${category} - ${typeId}`;
        document.getElementById('quickActionTitle').textContent = 'Ambil Cepat';
        document.getElementById('quickActionSubmit').textContent = 'Ambil Barang';
        document.getElementById('quickActionSubmit').className = 'btn btn-warning';
        document.getElementById('availableStockDiv').style.display = 'block';
        document.getElementById('actionAvailableStock').textContent = availableStock + ' barang';
        document.getElementById('actionQuantity').max = availableStock;

        var modal = new bootstrap.Modal(document.getElementById('quickActionModal'));
        modal.show();
    }

    function viewDetails(locationId, category, typeId) {
        window.location.href = '<?= site_url('storage/location/'); ?>' + locationId;
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

    function exportToExcel() {
        // Get current search parameters
        const searchTerm = document.querySelector('input[name="search_term"]')?.value || '';
        const locationId = document.querySelector('select[name="location_id"]')?.value || '';
        const category = document.querySelector('select[name="category"]')?.value || '';

        // Build URL with parameters
        let url = '<?= site_url("storage/export_search_excel"); ?>';
        let params = [];

        if (searchTerm) params.push('search=' + encodeURIComponent(searchTerm));
        if (locationId) params.push('location=' + encodeURIComponent(locationId));
        if (category) params.push('category=' + encodeURIComponent(category));

        if (params.length > 0) {
            url += '?' + params.join('&');
        }

        // Open in new window to trigger download
        window.open(url, '_blank');
    }

    // Auto-focus search input
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('q');
        if (searchInput && !searchInput.value) {
            searchInput.focus();
        }
    });
</script>