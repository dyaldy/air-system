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
                <h3 class="text-dark m-0">Ambil Barang</h3>
                <p class="text-muted mb-0">Hapus barang dari lokasi penyimpanan</p>
            </div>
            <a href="<?= site_url('storage'); ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali ke Penyimpanan
            </a>
        </div>
    </div>

    <!-- Card Body with Main Content -->
    <div class="card-body px-lg-5 px-4 py-4">

        <!-- Take Form -->
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h5 class="mb-4">Form Ambil Barang</h5>
                <?= form_open('storage/take', ['class' => 'needs-validation', 'novalidate' => '']); ?>

                <!-- Category -->
                <div class="mb-3">
                    <label for="category" class="form-label">Kategori <span class="text-danger">*</span></label>
                    <select class="form-select <?= form_error('category') ? 'is-invalid' : ''; ?>"
                        id="category" name="category" required onchange="updateAvailableItems()">
                        <option value="">Pilih Kategori</option>
                        <option value="pneumatic" <?= set_select('category', 'pneumatic'); ?>>Pneumatic</option>
                        <option value="valve" <?= set_select('category', 'valve'); ?>>Valve</option>
                        <option value="fitting" <?= set_select('category', 'fitting'); ?>>Fitting</option>
                        <option value="sensor" <?= set_select('category', 'sensor'); ?>>Sensor</option>
                        <option value="other" <?= set_select('category', 'other'); ?>>Other</option>
                    </select>
                    <?php if (form_error('category')): ?>
                        <div class="invalid-feedback"><?= form_error('category'); ?></div>
                    <?php endif; ?>
                </div>

                <!-- Type ID -->
                <div class="mb-3">
                    <label for="type_id" class="form-label">ID Tipe <span class="text-danger">*</span></label>
                    <select class="form-select <?= form_error('type_id') ? 'is-invalid' : ''; ?>"
                        id="type_id" name="type_id" required onchange="updateLocationOptions()">
                        <option value="">Pilih ID Tipe</option>
                        <!-- Options will be populated based on category selection -->
                    </select>
                    <div class="form-text">Pilih tipe/model spesifik dari barang</div>
                    <?php if (form_error('type_id')): ?>
                        <div class="invalid-feedback"><?= form_error('type_id'); ?></div>
                    <?php endif; ?>
                </div>

                <!-- Location ID -->
                <div class="mb-3">
                    <label for="location_id" class="form-label">ID Lokasi <span class="text-danger">*</span></label>
                    <select class="form-select <?= form_error('location_id') ? 'is-invalid' : ''; ?>"
                        id="location_id" name="location_id" required onchange="updateAvailableStock()">
                        <option value="">Pilih Lokasi</option>
                        <!-- Options will be populated based on type selection -->
                    </select>
                    <div class="form-text">Pilih lokasi untuk mengambil barang</div>
                    <?php if (form_error('location_id')): ?>
                        <div class="invalid-feedback"><?= form_error('location_id'); ?></div>
                    <?php endif; ?>
                </div>

                <!-- Available Stock Display -->
                <div class="mb-3">
                    <div class="card bg-light">
                        <div class="card-body py-2">
                            <small class="text-muted">Stok Tersedia: </small>
                            <strong id="availableStock" class="text-primary">-</strong>
                            <div id="projectIndicator" class="mt-1" style="display: none;">
                                <small class="badge bg-warning text-dark">
                                    <i class="fas fa-project-diagram"></i> Barang untuk Project
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quantity -->
                <div class="mb-3">
                    <label for="quantity" class="form-label">Jumlah <span class="text-danger">*</span></label>
                    <input type="number" class="form-control <?= form_error('quantity') ? 'is-invalid' : ''; ?>"
                        id="quantity" name="quantity" value="<?= set_value('quantity'); ?>"
                        min="1" step="1" placeholder="Masukkan jumlah yang akan diambil" required>
                    <div class="form-text">Masukkan jumlah barang yang akan diambil</div>
                    <?php if (form_error('quantity')): ?>
                        <div class="invalid-feedback"><?= form_error('quantity'); ?></div>
                    <?php endif; ?>
                </div>

                <!-- Note -->
                <div class="mb-3">
                    <label for="note" class="form-label">Catatan (Opsional)</label>
                    <textarea class="form-control <?= form_error('note') ? 'is-invalid' : ''; ?>"
                        id="note" name="note" rows="3" maxlength="255"
                        placeholder="Tambahkan catatan tambahan tentang operasi pengambilan ini"><?= set_value('note'); ?></textarea>
                    <div class="form-text">Catatan opsional tentang operasi pengambilan</div>
                    <?php if (form_error('note')): ?>
                        <div class="invalid-feedback"><?= form_error('note'); ?></div>
                    <?php endif; ?>
                </div>

                <!-- Submit Button -->
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="button" class="btn btn-secondary" onclick="window.history.back()">Batal</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-minus"></i> Ambil Barang
                    </button>
                </div>

                <?= form_close(); ?>
            </div>
        </div>

        <!-- Available Items Preview -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Barang Tersedia di Penyimpanan</h6>
                    </div>
                    <div class="card-body">
                        <div id="availableItemsPreview">
                            <div class="text-muted">Pilih kategori untuk melihat barang yang tersedia</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Storage items data from PHP
    const storageItems = <?= json_encode($storage_items ?? []); ?>;

    function updateAvailableItems() {
        const category = document.getElementById('category').value;
        const typeSelect = document.getElementById('type_id');
        const locationSelect = document.getElementById('location_id');

        // Clear existing options
        typeSelect.innerHTML = '<option value="">Pilih ID Tipe</option>';
        locationSelect.innerHTML = '<option value="">Pilih Lokasi</option>';

        // Clear available stock
        document.getElementById('availableStock').textContent = '-';

        if (category) {
            // Filter items by category
            const categoryItems = storageItems.filter(item => item.category === category);

            // Get unique base type_ids (remove _PROJECT suffix)
            const uniqueTypes = [...new Set(categoryItems.map(item =>
                item.type_id.replace('_PROJECT', '')
            ))];

            uniqueTypes.forEach(baseTypeId => {
                const option = document.createElement('option');
                option.value = baseTypeId;
                option.textContent = baseTypeId;
                if (option.value === '<?= set_value('type_id'); ?>') {
                    option.selected = true;
                }
                typeSelect.appendChild(option);
            });

            updateAvailableItemsPreview();
        }

        // Clear preview if no category selected
        if (!category) {
            document.getElementById('availableItemsPreview').innerHTML = '<div class="text-muted">Pilih kategori untuk melihat barang yang tersedia</div>';
        }
    }

    function updateLocationOptions() {
        const category = document.getElementById('category').value;
        const typeId = document.getElementById('type_id').value;
        const locationSelect = document.getElementById('location_id');

        // Clear existing options
        locationSelect.innerHTML = '<option value="">Pilih Lokasi</option>';

        // Clear available stock
        document.getElementById('availableStock').textContent = '-';

        if (category && typeId) {
            // Filter items by category and type_id (including project versions)
            const filteredItems = storageItems.filter(item =>
                item.category === category &&
                (item.type_id === typeId || item.type_id === typeId + '_PROJECT') &&
                parseInt(item.amount) > 0
            );

            filteredItems.forEach(item => {
                const isProjectItem = item.type_id.endsWith('_PROJECT');
                const displayTypeId = isProjectItem ? item.type_id.slice(0, -8) : item.type_id; // Remove _PROJECT suffix

                const option = document.createElement('option');
                // Use a compound value to distinguish project vs non-project items
                option.value = isProjectItem ? `${item.location_id}_project` : item.location_id;
                option.textContent = `${item.location_id} (Stok: ${item.amount})${isProjectItem ? ' - Project' : ''}`;
                if (option.value === '<?= set_value('location_id'); ?>') {
                    option.selected = true;
                }
                locationSelect.appendChild(option);
            });
        }
    }

    function updateAvailableStock() {
        const category = document.getElementById('category').value;
        const typeId = document.getElementById('type_id').value;
        const locationValue = document.getElementById('location_id').value;

        if (category && typeId && locationValue) {
            // Parse the location value to get location_id and project status
            const isProject = locationValue.endsWith('_project');
            const locationId = isProject ? locationValue.slice(0, -8) : locationValue; // Remove '_project' suffix

            // Find the specific item
            const item = storageItems.find(item => {
                if (item.category !== category || item.location_id !== locationId) {
                    return false;
                }

                // Check project status by type_id suffix
                const itemIsProject = item.type_id.endsWith('_PROJECT');
                return itemIsProject === isProject;
            });

            if (item) {
                const stock = parseInt(item.amount);
                document.getElementById('availableStock').textContent = stock + ' barang';

                // Check if item is for project
                const projectIndicator = document.getElementById('projectIndicator');
                projectIndicator.style.display = isProject ? 'block' : 'none';

                // Update quantity input constraints
                const quantityInput = document.getElementById('quantity');
                quantityInput.max = stock;

                // Show warning if stock is low
                const stockDisplay = document.getElementById('availableStock');
                if (stock <= 5) {
                    stockDisplay.className = 'text-warning';
                } else if (stock <= 2) {
                    stockDisplay.className = 'text-danger';
                } else {
                    stockDisplay.className = 'text-primary';
                }
            }
        } else {
            document.getElementById('availableStock').textContent = '-';
            document.getElementById('projectIndicator').style.display = 'none';
        }
    }

    function updateAvailableItemsPreview() {
        const category = document.getElementById('category').value;

        if (category) {
            const categoryItems = storageItems.filter(item =>
                item.category === category && parseInt(item.amount) > 0
            );

            const previewDiv = document.getElementById('availableItemsPreview');

            if (categoryItems.length > 0) {
                // Group items by base type_id (remove _PROJECT suffix)
                const groupedItems = {};
                categoryItems.forEach(item => {
                    const baseTypeId = item.type_id.replace('_PROJECT', '');
                    if (!groupedItems[baseTypeId]) {
                        groupedItems[baseTypeId] = [];
                    }
                    groupedItems[baseTypeId].push(item);
                });

                let html = '<div class="row">';

                Object.keys(groupedItems).forEach(baseTypeId => {
                    const items = groupedItems[baseTypeId];
                    const totalStock = items.reduce((sum, item) => sum + parseInt(item.amount), 0);

                    html += `
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="card border-primary h-100">
                            <div class="card-body">
                                <h6 class="card-title">${baseTypeId}</h6>
                                <p class="card-text">
                                    <strong>Total Stok: ${totalStock}</strong><br>
                                    <small class="text-muted">Tersedia di ${items.length} lokasi</small>
                                </p>
                                <div class="locations">
                `;

                    items.forEach(item => {
                        const isProjectItem = item.type_id.endsWith('_PROJECT');
                        const projectBadge = isProjectItem ? ' <small class="badge bg-warning text-dark">Project</small>' : '';
                        html += `<span class="badge bg-info me-1">${item.location_id}: ${item.amount}${projectBadge}</span>`;
                    });

                    html += `
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                });

                html += '</div>';
                previewDiv.innerHTML = html;
            } else {
                previewDiv.innerHTML = '<div class="alert alert-info">Tidak ada barang tersedia dalam kategori ini.</div>';
            }
        }
    }

    // Event listeners
    document.getElementById('category').addEventListener('change', updateAvailableItems);
    document.getElementById('type_id').addEventListener('change', updateLocationOptions);
    document.getElementById('location_id').addEventListener('change', updateAvailableStock);

    // Validate quantity against available stock
    document.getElementById('quantity').addEventListener('input', function() {
        const maxStock = parseInt(this.max);
        const enteredQuantity = parseInt(this.value);

        if (maxStock && enteredQuantity > maxStock) {
            this.setCustomValidity(`Stok maksimum yang tersedia adalah ${maxStock}`);
        } else {
            this.setCustomValidity('');
        }
    });

    // Initialize with selected values if any
    document.addEventListener('DOMContentLoaded', function() {
        const category = document.getElementById('category').value;
        if (category) {
            updateAvailableItems();

            const typeId = document.getElementById('type_id').value;
            if (typeId) {
                updateLocationOptions();

                const locationId = document.getElementById('location_id').value;
                if (locationId) {
                    updateAvailableStock();
                }
            }
        }
    });

    // Bootstrap form validation
    (function() {
        'use strict';
        window.addEventListener('load', function() {
            var forms = document.getElementsByClassName('needs-validation');
            var validation = Array.prototype.filter.call(forms, function(form) {
                form.addEventListener('submit', function(event) {
                    if (form.checkValidity() === false) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        }, false);
    })();
</script>