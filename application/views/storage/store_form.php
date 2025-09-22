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
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="text-primary">Store Items</h2>
                    <p class="text-muted">Add items to storage locations</p>
                </div>
                <a href="<?= site_url('storage'); ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Storage
                </a>
            </div>
        </div>
    </div>

    <!-- Store Form -->
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Store Items Form</h5>
                </div>
                <div class="card-body">
                    <?= form_open('storage/store', ['class' => 'needs-validation', 'novalidate' => '']); ?>

                    <!-- Location ID -->
                    <div class="mb-3">
                        <label for="location_id" class="form-label">Location ID <span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?= form_error('location_id') ? 'is-invalid' : ''; ?>"
                            id="location_id" name="location_id" value="<?= set_value('location_id'); ?>"
                            maxlength="3" placeholder="Enter 3-character location ID (e.g., A01)" required>
                        <div class="form-text">Enter a 3-character location identifier</div>
                        <?php if (form_error('location_id')): ?>
                            <div class="invalid-feedback"><?= form_error('location_id'); ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Category -->
                    <div class="mb-3">
                        <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                        <select class="form-select <?= form_error('category') ? 'is-invalid' : ''; ?>"
                            id="category" name="category" required onchange="updateTypeOptions()">
                            <option value="">Select Category</option>
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
                        <label for="type_id" class="form-label">Type ID <span class="text-danger">*</span></label>
                        <select class="form-select <?= form_error('type_id') ? 'is-invalid' : ''; ?>"
                            id="type_id" name="type_id" required>
                            <option value="">Select Type ID</option>
                            <!-- Options will be populated based on category selection -->
                        </select>
                        <div class="form-text">Select the specific type/model of the item</div>
                        <?php if (form_error('type_id')): ?>
                            <div class="invalid-feedback"><?= form_error('type_id'); ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Quantity -->
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                        <input type="number" class="form-control <?= form_error('quantity') ? 'is-invalid' : ''; ?>"
                            id="quantity" name="quantity" value="<?= set_value('quantity'); ?>"
                            min="1" step="1" placeholder="Enter quantity" required>
                        <div class="form-text">Enter the number of items to store</div>
                        <?php if (form_error('quantity')): ?>
                            <div class="invalid-feedback"><?= form_error('quantity'); ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Note -->
                    <div class="mb-3">
                        <label for="note" class="form-label">Note (Optional)</label>
                        <textarea class="form-control <?= form_error('note') ? 'is-invalid' : ''; ?>"
                            id="note" name="note" rows="3" maxlength="255"
                            placeholder="Add any additional notes about this storage operation"><?= set_value('note'); ?></textarea>
                        <div class="form-text">Optional notes about the storage operation</div>
                        <?php if (form_error('note')): ?>
                            <div class="invalid-feedback"><?= form_error('note'); ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Submit Button -->
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="button" class="btn btn-secondary" onclick="window.history.back()">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Store Items
                        </button>
                    </div>

                    <?= form_close(); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Existing Storage Preview -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Preview: Check Current Stock</h6>
                </div>
                <div class="card-body">
                    <div id="stockPreview" class="text-muted">
                        Select category and type to view current stock levels
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Pneumatic items data from PHP
    const pneumaticItems = <?= json_encode($pneumatic_items ?? []); ?>;

    function updateTypeOptions() {
        const category = document.getElementById('category').value;
        const typeSelect = document.getElementById('type_id');

        // Clear existing options
        typeSelect.innerHTML = '<option value="">Select Type ID</option>';

        if (category === 'pneumatic') {
            // Populate with pneumatic items
            pneumaticItems.forEach(item => {
                const option = document.createElement('option');
                option.value = item.pneumatic_id;
                option.textContent = `${item.pneumatic_id} (${item.brand} ${item.type} - ${item.bore}x${item.stroke})`;
                if (option.value === '<?= set_value('type_id'); ?>') {
                    option.selected = true;
                }
                typeSelect.appendChild(option);
            });
        } else if (category) {
            // For other categories, allow manual input by changing to text input
            // Or you could implement similar lookups for other categories
            const option = document.createElement('option');
            option.value = 'manual';
            option.textContent = 'Enter manually below';
            typeSelect.appendChild(option);

            // Add input field for manual entry
            if (!document.getElementById('manual_type_id')) {
                const manualInput = document.createElement('input');
                manualInput.type = 'text';
                manualInput.className = 'form-control mt-2';
                manualInput.id = 'manual_type_id';
                manualInput.placeholder = 'Enter type ID manually';
                manualInput.maxLength = 30;

                manualInput.addEventListener('input', function() {
                    typeSelect.value = this.value;
                });

                typeSelect.parentNode.appendChild(manualInput);
            }
        }

        // Update stock preview
        updateStockPreview();
    }

    function updateStockPreview() {
        const category = document.getElementById('category').value;
        const typeId = document.getElementById('type_id').value;

        if (category && typeId) {
            fetch(`<?= site_url('storage/get_stock'); ?>?category=${category}&type_id=${typeId}`)
                .then(response => response.json())
                .then(data => {
                    const previewDiv = document.getElementById('stockPreview');

                    if (data.success && data.stock_locations.length > 0) {
                        let html = '<h6>Current Stock:</h6>';
                        html += '<div class="row">';

                        data.stock_locations.forEach(location => {
                            html += `
                            <div class="col-md-3 col-sm-6 mb-2">
                                <div class="card border-info">
                                    <div class="card-body p-2 text-center">
                                        <strong>${location.location_id}</strong><br>
                                        <span class="text-info">${location.amount} items</span>
                                    </div>
                                </div>
                            </div>
                        `;
                        });

                        html += '</div>';
                        html += `<div class="mt-2"><strong>Total Stock: ${data.total_stock} items</strong></div>`;

                        previewDiv.innerHTML = html;
                    } else {
                        previewDiv.innerHTML = '<div class="alert alert-info">No existing stock found for this item.</div>';
                    }
                })
                .catch(error => {
                    console.error('Error fetching stock:', error);
                    document.getElementById('stockPreview').innerHTML = '<div class="alert alert-warning">Error loading stock information.</div>';
                });
        } else {
            document.getElementById('stockPreview').innerHTML = '<div class="text-muted">Select category and type to view current stock levels</div>';
        }
    }

    // Event listeners
    document.getElementById('type_id').addEventListener('change', updateStockPreview);

    // Initialize with selected values if any
    document.addEventListener('DOMContentLoaded', function() {
        const category = document.getElementById('category').value;
        if (category) {
            updateTypeOptions();
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