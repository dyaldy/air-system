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
                    <h2 class="text-primary">Take Items</h2>
                    <p class="text-muted">Remove items from storage locations</p>
                </div>
                <a href="<?= site_url('storage'); ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Storage
                </a>
            </div>
        </div>
    </div>

    <!-- Take Form -->
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Take Items Form</h5>
                </div>
                <div class="card-body">
                    <?= form_open('storage/take', ['class' => 'needs-validation', 'novalidate' => '']); ?>
                        
                        <!-- Category -->
                        <div class="mb-3">
                            <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                            <select class="form-select <?= form_error('category') ? 'is-invalid' : ''; ?>" 
                                    id="category" name="category" required onchange="updateAvailableItems()">
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
                                    id="type_id" name="type_id" required onchange="updateLocationOptions()">
                                <option value="">Select Type ID</option>
                                <!-- Options will be populated based on category selection -->
                            </select>
                            <div class="form-text">Select the specific type/model of the item</div>
                            <?php if (form_error('type_id')): ?>
                                <div class="invalid-feedback"><?= form_error('type_id'); ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Location ID -->
                        <div class="mb-3">
                            <label for="location_id" class="form-label">Location ID <span class="text-danger">*</span></label>
                            <select class="form-select <?= form_error('location_id') ? 'is-invalid' : ''; ?>" 
                                    id="location_id" name="location_id" required onchange="updateAvailableStock()">
                                <option value="">Select Location</option>
                                <!-- Options will be populated based on type selection -->
                            </select>
                            <div class="form-text">Select the location to take items from</div>
                            <?php if (form_error('location_id')): ?>
                                <div class="invalid-feedback"><?= form_error('location_id'); ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Available Stock Display -->
                        <div class="mb-3">
                            <div class="card bg-light">
                                <div class="card-body py-2">
                                    <small class="text-muted">Available Stock: </small>
                                    <strong id="availableStock" class="text-primary">-</strong>
                                </div>
                            </div>
                        </div>

                        <!-- Quantity -->
                        <div class="mb-3">
                            <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control <?= form_error('quantity') ? 'is-invalid' : ''; ?>" 
                                   id="quantity" name="quantity" value="<?= set_value('quantity'); ?>" 
                                   min="1" step="1" placeholder="Enter quantity to take" required>
                            <div class="form-text">Enter the number of items to take</div>
                            <?php if (form_error('quantity')): ?>
                                <div class="invalid-feedback"><?= form_error('quantity'); ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Note -->
                        <div class="mb-3">
                            <label for="note" class="form-label">Note (Optional)</label>
                            <textarea class="form-control <?= form_error('note') ? 'is-invalid' : ''; ?>" 
                                      id="note" name="note" rows="3" maxlength="255" 
                                      placeholder="Add any additional notes about this taking operation"><?= set_value('note'); ?></textarea>
                            <div class="form-text">Optional notes about the taking operation</div>
                            <?php if (form_error('note')): ?>
                                <div class="invalid-feedback"><?= form_error('note'); ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="button" class="btn btn-secondary" onclick="window.history.back()">Cancel</button>
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-minus"></i> Take Items
                            </button>
                        </div>

                    <?= form_close(); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Available Items Preview -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Available Items in Storage</h6>
                </div>
                <div class="card-body">
                    <div id="availableItemsPreview">
                        <div class="text-muted">Select category to view available items</div>
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
    typeSelect.innerHTML = '<option value="">Select Type ID</option>';
    locationSelect.innerHTML = '<option value="">Select Location</option>';
    
    // Clear available stock
    document.getElementById('availableStock').textContent = '-';
    
    if (category) {
        // Filter items by category
        const categoryItems = storageItems.filter(item => item.category === category);
        
        // Get unique type_ids
        const uniqueTypes = [...new Set(categoryItems.map(item => item.type_id))];
        
        uniqueTypes.forEach(typeId => {
            const option = document.createElement('option');
            option.value = typeId;
            option.textContent = typeId;
            if (option.value === '<?= set_value('type_id'); ?>') {
                option.selected = true;
            }
            typeSelect.appendChild(option);
        });
        
        updateAvailableItemsPreview();
    }
    
    // Clear preview if no category selected
    if (!category) {
        document.getElementById('availableItemsPreview').innerHTML = '<div class="text-muted">Select category to view available items</div>';
    }
}

function updateLocationOptions() {
    const category = document.getElementById('category').value;
    const typeId = document.getElementById('type_id').value;
    const locationSelect = document.getElementById('location_id');
    
    // Clear existing options
    locationSelect.innerHTML = '<option value="">Select Location</option>';
    
    // Clear available stock
    document.getElementById('availableStock').textContent = '-';
    
    if (category && typeId) {
        // Filter items by category and type_id
        const filteredItems = storageItems.filter(item => 
            item.category === category && 
            item.type_id === typeId && 
            parseInt(item.amount) > 0
        );
        
        filteredItems.forEach(item => {
            const option = document.createElement('option');
            option.value = item.location_id;
            option.textContent = `${item.location_id} (Stock: ${item.amount})`;
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
    const locationId = document.getElementById('location_id').value;
    
    if (category && typeId && locationId) {
        // Find the specific item
        const item = storageItems.find(item => 
            item.category === category && 
            item.type_id === typeId && 
            item.location_id === locationId
        );
        
        if (item) {
            const stock = parseInt(item.amount);
            document.getElementById('availableStock').textContent = stock + ' items';
            
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
            // Group items by type_id
            const groupedItems = {};
            categoryItems.forEach(item => {
                if (!groupedItems[item.type_id]) {
                    groupedItems[item.type_id] = [];
                }
                groupedItems[item.type_id].push(item);
            });
            
            let html = '<div class="row">';
            
            Object.keys(groupedItems).forEach(typeId => {
                const items = groupedItems[typeId];
                const totalStock = items.reduce((sum, item) => sum + parseInt(item.amount), 0);
                
                html += `
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="card border-primary h-100">
                            <div class="card-body">
                                <h6 class="card-title">${typeId}</h6>
                                <p class="card-text">
                                    <strong>Total Stock: ${totalStock}</strong><br>
                                    <small class="text-muted">Available in ${items.length} location(s)</small>
                                </p>
                                <div class="locations">
                `;
                
                items.forEach(item => {
                    html += `<span class="badge bg-info me-1">${item.location_id}: ${item.amount}</span>`;
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
            previewDiv.innerHTML = '<div class="alert alert-info">No items available in this category.</div>';
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
        this.setCustomValidity(`Maximum available stock is ${maxStock}`);
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