<div class="container-fluid pt-5 mt-3">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="text-primary">Search Storage</h2>
                    <p class="text-muted">Find items across all storage locations</p>
                </div>
                <a href="<?= site_url('storage'); ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Storage
                </a>
            </div>
        </div>
    </div>

    <!-- Search Form -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Search Filters</h6>
                </div>
                <div class="card-body">
                    <?= form_open('storage/search', ['method' => 'GET', 'class' => 'row g-3']); ?>
                    <div class="col-md-4">
                        <label for="q" class="form-label">Search Term</label>
                        <input type="text" class="form-control" id="q" name="q"
                            value="<?= htmlspecialchars($search_term ?? ''); ?>"
                            placeholder="Search by type ID, category, or location...">
                        <div class="form-text">Search in type ID, category, or location</div>
                    </div>

                    <div class="col-md-3">
                        <label for="location" class="form-label">Location</label>
                        <select class="form-select" id="location" name="location">
                            <option value="">All Locations</option>
                            <?php foreach ($locations as $location): ?>
                                <option value="<?= $location['location_id']; ?>"
                                    <?= $selected_location == $location['location_id'] ? 'selected' : ''; ?>>
                                    <?= $location['location_id']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="category" class="form-label">Category</label>
                        <select class="form-select" id="category" name="category">
                            <option value="">All Categories</option>
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
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </div>

                    <div class="col-12">
                        <a href="<?= site_url('storage/search'); ?>" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-times"></i> Clear Search
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
                        Search Results
                        <?php if (!empty($search_term) || !empty($selected_location) || !empty($selected_category)): ?>
                            <small class="text-muted">
                                <?php
                                $filters = [];
                                if ($search_term) $filters[] = "Term: \"$search_term\"";
                                if ($selected_location) $filters[] = "Location: $selected_location";
                                if ($selected_category) $filters[] = "Category: $selected_category";
                                echo '(' . implode(', ', $filters) . ')';
                                ?>
                            </small>
                        <?php endif; ?>
                </div>
                <?php if (!empty($search_results)): ?>
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="exportSearchResults()">
                            <i class="fas fa-download"></i> Export
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                            <i class="fas fa-print"></i> Print
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
                                            <h6>Items Found</h6>
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
                                            <h6>Total Quantity</h6>
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
                                            <h6>Locations</h6>
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
                                            <h6>Categories</h6>
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
                                    <th>Location</th>
                                    <th>Category</th>
                                    <th>Type ID</th>
                                    <th>Quantity</th>
                                    <th>Last Updated</th>
                                    <th>Editor</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($search_results as $item): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($item['location_id']); ?></strong>
                                            <br>
                                            <a href="<?= site_url('storage/location/' . $item['location_id']); ?>" class="text-decoration-none small">
                                                View Location
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
                                            <small><?= htmlspecialchars($item['editor_name'] ?? 'Unknown'); ?></small>
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
                                    <h6 class="mb-0">By Category</h6>
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
                                                <small><?= $data['count']; ?> items, <?= number_format($data['quantity']); ?> total</small>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">By Location</h6>
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
                                                <small><?= $data['count']; ?> items, <?= number_format($data['quantity']); ?> total</small>
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
                        <h5>No items found</h5>
                        <p>No items match your search criteria. Try adjusting your search terms or filters.</p>
                    </div>
                <?php else: ?>
                    <div class="alert alert-light text-center">
                        <i class="fas fa-search fa-3x mb-3 text-muted"></i>
                        <h5>Search Storage Items</h5>
                        <p>Use the search form above to find items in storage. You can search by type ID, category, or location.</p>
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
                <h5 class="modal-title" id="quickActionTitle">Quick Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="quickActionForm">
                    <input type="hidden" id="actionType" name="action">
                    <input type="hidden" id="actionLocationId" name="location_id">
                    <input type="hidden" id="actionCategory" name="category">
                    <input type="hidden" id="actionTypeId" name="type_id">

                    <div class="mb-3">
                        <label class="form-label">Item:</label>
                        <div id="actionItemInfo" class="form-control-plaintext"></div>
                    </div>

                    <div class="mb-3" id="availableStockDiv" style="display: none;">
                        <label class="form-label">Available Stock:</label>
                        <div id="actionAvailableStock" class="form-control-plaintext text-primary"></div>
                    </div>

                    <div class="mb-3">
                        <label for="actionQuantity" class="form-label">Quantity:</label>
                        <input type="number" class="form-control" id="actionQuantity" name="quantity" min="1" required>
                    </div>

                    <div class="mb-3">
                        <label for="actionNote" class="form-label">Note (Optional):</label>
                        <textarea class="form-control" id="actionNote" name="note" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn" id="quickActionSubmit" onclick="submitQuickAction()">Action</button>
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
        document.getElementById('quickActionTitle').textContent = 'Quick Store';
        document.getElementById('quickActionSubmit').textContent = 'Store Items';
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
        document.getElementById('quickActionTitle').textContent = 'Quick Take';
        document.getElementById('quickActionSubmit').textContent = 'Take Items';
        document.getElementById('quickActionSubmit').className = 'btn btn-warning';
        document.getElementById('availableStockDiv').style.display = 'block';
        document.getElementById('actionAvailableStock').textContent = availableStock + ' items';
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

    function exportSearchResults() {
        const table = document.getElementById('searchResultsTable');
        if (!table) {
            alert('No data to export');
            return;
        }

        let csv = [];

        // Headers
        const headers = [];
        table.querySelectorAll('thead th').forEach(th => {
            if (th.textContent.trim() !== 'Actions') {
                headers.push(th.textContent.trim());
            }
        });
        csv.push(headers.join(','));

        // Data rows
        table.querySelectorAll('tbody tr').forEach(tr => {
            const row = [];
            tr.querySelectorAll('td').forEach((td, index) => {
                if (index < headers.length) { // Skip actions column
                    row.push('"' + td.textContent.trim().replace(/"/g, '""') + '"');
                }
            });
            csv.push(row.join(','));
        });

        // Download
        const csvContent = csv.join('\n');
        const blob = new Blob([csvContent], {
            type: 'text/csv'
        });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'storage_search_results_' + new Date().toISOString().split('T')[0] + '.csv';
        a.click();
        window.URL.revokeObjectURL(url);
    }

    // Auto-focus search input
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('q');
        if (searchInput && !searchInput.value) {
            searchInput.focus();
        }
    });
</script>