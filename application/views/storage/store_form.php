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
                <h3 class="text-dark m-0">Simpan Barang</h3>
                <p class="text-muted mb-0">Tambah barang ke lokasi penyimpanan</p>
            </div>
            <a href="<?= site_url('storage'); ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali ke Penyimpanan
            </a>
        </div>
    </div>

    <!-- Card Body with Main Content -->
    <div class="card-body px-lg-5 px-4 py-4">

        <!-- Store Form -->
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h5 class="mb-4">Form Simpan Barang</h5>
                <?= form_open('storage/store', ['class' => 'needs-validation', 'novalidate' => '']); ?>

                <!-- Location ID -->
                <div class="mb-3">
                    <label for="location_id" class="form-label">ID Lokasi <span class="text-danger">*</span></label>
                    <input type="text" class="form-control <?= form_error('location_id') ? 'is-invalid' : ''; ?>"
                        id="location_id" name="location_id" value="<?= set_value('location_id'); ?>"
                        maxlength="3" placeholder="Masukkan ID lokasi 3 karakter (mis: A01)" required>
                    <div class="form-text">Masukkan pengidentifikasi lokasi 3 karakter</div>
                    <?php if (form_error('location_id')): ?>
                        <div class="invalid-feedback"><?= form_error('location_id'); ?></div>
                    <?php endif; ?>
                </div>

                <!-- Category -->
                <div class="mb-3">
                    <label for="category" class="form-label">Kategori <span class="text-danger">*</span></label>
                    <select class="form-select <?= form_error('category') ? 'is-invalid' : ''; ?>"
                        id="category" name="category" required onchange="updateTypeOptions()">
                        <option value="">Pilih Kategori</option>
                        <option value="pneumatic" <?= set_select('category', 'pneumatic'); ?>>Pneumatic</option>
                        <option value="fitting" <?= set_select('category', 'fitting'); ?>>Fitting</option>
                        <option value="solenoid" <?= set_select('category', 'solenoid'); ?>>Solenoid</option>
                        <option value="manifold" <?= set_select('category', 'manifold'); ?>>Manifold</option>
                        <option value="regulator" <?= set_select('category', 'regulator'); ?>>Regulator</option>
                    </select>
                    <?php if (form_error('category')): ?>
                        <div class="invalid-feedback"><?= form_error('category'); ?></div>
                    <?php endif; ?>
                </div>

                <!-- Type ID -->
                <div class="mb-3">
                    <label for="type_id" class="form-label">ID Tipe <span class="text-danger">*</span></label>
                    <select class="form-select <?= form_error('type_id') ? 'is-invalid' : ''; ?>"
                        id="type_id" name="type_id" required onchange="updateTypeImage()">
                        <option value="">Pilih ID Tipe</option>
                        <!-- Options will be populated based on category selection -->
                    </select>
                    <div class="form-text">Pilih tipe/model spesifik dari barang</div>
                    <?php if (form_error('type_id')): ?>
                        <div class="invalid-feedback"><?= form_error('type_id'); ?></div>
                    <?php endif; ?>
                </div>

                <!-- Type Image Display -->
                <div class="mb-3" id="typeImageContainer" style="display: none;">
                    <label class="form-label">Gambar Tipe</label>
                    <div class="card" style="max-width: 300px;">
                        <img id="typeImage" src="" alt="Type Image" class="card-img-top" style="object-fit: contain; max-height: 250px;" onerror="this.src='<?= base_url('assets/img/placeholder-image.svg'); ?>'">
                        <div class="card-body py-2 text-center">
                            <small class="text-muted" id="typeImageLabel"></small>
                        </div>
                    </div>
                </div>

                <!-- Quantity -->
                <div class="mb-3">
                    <label for="quantity" class="form-label">Jumlah <span class="text-danger">*</span></label>
                    <input type="number" class="form-control <?= form_error('quantity') ? 'is-invalid' : ''; ?>"
                        id="quantity" name="quantity" value="<?= set_value('quantity'); ?>"
                        min="1" step="1" placeholder="Masukkan jumlah" required>
                    <div class="form-text">Masukkan jumlah barang yang akan disimpan</div>
                    <?php if (form_error('quantity')): ?>
                        <div class="invalid-feedback"><?= form_error('quantity'); ?></div>
                    <?php endif; ?>
                </div>

                <!-- Note -->
                <div class="mb-3">
                    <label for="note" class="form-label">Catatan (Opsional)</label>
                    <textarea class="form-control <?= form_error('note') ? 'is-invalid' : ''; ?>"
                        id="note" name="note" rows="3" maxlength="255"
                        placeholder="Tambahkan catatan tambahan tentang operasi penyimpanan ini"><?= set_value('note'); ?></textarea>
                    <div class="form-text">Catatan opsional tentang operasi penyimpanan</div>
                    <?php if (form_error('note')): ?>
                        <div class="invalid-feedback"><?= form_error('note'); ?></div>
                    <?php endif; ?>
                </div>

                <!-- Project Item Checkbox -->
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_project_item" name="is_project_item" value="1" <?= set_checkbox('is_project_item', '1'); ?> onchange="toggleProjectFields()">
                        <label class="form-check-label" for="is_project_item">
                            Barang untuk Project
                        </label>
                        <div class="form-text">Centang jika barang ini disimpan untuk keperluan project tertentu</div>
                    </div>
                </div>

                <!-- Project Name (only shown when project item is checked) -->
                <div class="mb-3" id="projectNameField" style="display: none;">
                    <label for="project_name" class="form-label">Nama Project <span class="text-danger">*</span></label>
                    <input type="text" class="form-control <?= form_error('project_name') ? 'is-invalid' : ''; ?>"
                        id="project_name" name="project_name" value="<?= set_value('project_name'); ?>"
                        maxlength="100" placeholder="Masukkan nama project">
                    <div class="form-text">Nama project untuk identifikasi batch barang ini</div>
                    <?php if (form_error('project_name')): ?>
                        <div class="invalid-feedback"><?= form_error('project_name'); ?></div>
                    <?php endif; ?>
                </div>

                <!-- Submit Button -->
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="button" class="btn btn-secondary" onclick="window.history.back()">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Simpan Barang
                    </button>
                </div>

                <?= form_close(); ?>
            </div>
        </div>

        <!-- Existing Storage Preview -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Pratinjau: Periksa Stok Saat Ini</h6>
                    </div>
                    <div class="card-body">
                        <div id="stockPreview" class="text-muted">
                            Pilih kategori dan tipe untuk melihat tingkat stok saat ini
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Pneumatic items data from PHP
        const pneumaticItems = <?= json_encode($pneumatic_items ?? []); ?>;
        // Fitting items data from PHP
        const fittingItems = <?= json_encode($fitting_items ?? []); ?>;
        // Solenoid items data from PHP
        const solenoidItems = <?= json_encode($solenoid_items ?? []); ?>;
        // Manifold items data from PHP
        const manifoldItems = <?= json_encode($manifold_items ?? []); ?>;
        // Regulator items data from PHP
        const regulatorItems = <?= json_encode($regulator_items ?? []); ?>;

        function updateTypeOptions() {
            const category = document.getElementById('category').value;
            const typeSelect = document.getElementById('type_id');

            // Clear existing options
            typeSelect.innerHTML = '<option value="">Pilih ID Tipe</option>';

            // Hide type image when category changes
            document.getElementById('typeImageContainer').style.display = 'none';

            // Remove any existing manual input field
            const existingManualInput = document.getElementById('manual_type_id');
            if (existingManualInput) {
                existingManualInput.remove();
            }

            if (category === 'pneumatic') {
                // Populate with pneumatic items
                pneumaticItems.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.pneumatic_id;
                    option.textContent = `${item.pneumatic_id} (${item.type} - ${item.bore}x${item.stroke})`;
                    if (option.value === '<?= set_value('type_id'); ?>') {
                        option.selected = true;
                    }
                    typeSelect.appendChild(option);
                });
            } else if (category === 'fitting') {
                // Populate with fitting items
                fittingItems.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.fitting_id;

                    // Build dimension display string
                    let dimensions = [];
                    if (item.D1) dimensions.push(`D1:${item.D1}`);
                    if (item.D2) dimensions.push(`D2:${item.D2}`);
                    if (item.D3) dimensions.push(`D3:${item.D3}`);
                    if (item.R_DRAT) dimensions.push(`DRAT:${item.R_DRAT}`);

                    const dimensionString = dimensions.length > 0 ? dimensions.join(' ') : 'No dimensions';
                    option.textContent = `${item.fitting_id} (${item.type} ${item.subtype || ''} - ${dimensionString})`;

                    if (option.value === '<?= set_value('type_id'); ?>') {
                        option.selected = true;
                    }
                    typeSelect.appendChild(option);
                });
            } else if (category === 'solenoid') {
                // Populate with solenoid items
                solenoidItems.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.solenoid_id;
                    option.textContent = `${item.solenoid_id} (${item.type} - ${item.subtype})`;
                    if (option.value === '<?= set_value('type_id'); ?>') {
                        option.selected = true;
                    }
                    typeSelect.appendChild(option);
                });
            } else if (category === 'manifold') {
                // Populate with manifold items
                manifoldItems.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.manifold_id;
                    option.textContent = `${item.manifold_id} (Block: ${item.block})`;
                    if (option.value === '<?= set_value('type_id'); ?>') {
                        option.selected = true;
                    }
                    typeSelect.appendChild(option);
                });
            } else if (category === 'regulator') {
                // Populate with regulator items
                regulatorItems.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.regulator_id;
                    option.textContent = `${item.regulator_id} (Type: ${item.type})`;
                    if (option.value === '<?= set_value('type_id'); ?>') {
                        option.selected = true;
                    }
                    typeSelect.appendChild(option);
                });
            } else if (category) {
                // For other categories under development, allow manual input
                const option = document.createElement('option');
                option.value = 'manual';
                option.textContent = 'Masukkan secara manual di bawah';
                typeSelect.appendChild(option);

                // Add input field for manual entry
                const manualInput = document.createElement('input');
                manualInput.type = 'text';
                manualInput.className = 'form-control mt-2';
                manualInput.id = 'manual_type_id';
                manualInput.placeholder = 'Masukkan ID tipe secara manual';
                manualInput.maxLength = 30;

                manualInput.addEventListener('input', function() {
                    typeSelect.value = this.value;
                });

                typeSelect.parentNode.appendChild(manualInput);
            } else if (category) {
                // For other categories, allow manual input by changing to text input
                const option = document.createElement('option');
                option.value = 'manual';
                option.textContent = 'Masukkan secara manual di bawah';
                typeSelect.appendChild(option);

                // Add input field for manual entry
                const manualInput = document.createElement('input');
                manualInput.type = 'text';
                manualInput.className = 'form-control mt-2';
                manualInput.id = 'manual_type_id';
                manualInput.placeholder = 'Masukkan ID tipe secara manual';
                manualInput.maxLength = 30;

                manualInput.addEventListener('input', function() {
                    typeSelect.value = this.value;
                });

                typeSelect.parentNode.appendChild(manualInput);
            }

            // Update stock preview
            updateStockPreview();
        }

        function updateTypeImage() {
            const category = document.getElementById('category').value;
            const typeId = document.getElementById('type_id').value;
            const typeImageContainer = document.getElementById('typeImageContainer');
            const typeImage = document.getElementById('typeImage');
            const typeImageLabel = document.getElementById('typeImageLabel');

            if (category && typeId && typeId !== 'manual') {
                let imageUrl = null;
                let typeName = typeId;

                if (category === 'pneumatic') {
                    const item = pneumaticItems.find(p => p.pneumatic_id === typeId);
                    if (item && item.type_image) {
                        imageUrl = '<?= base_url('assets/img/pneumatic_types/'); ?>' + item.type_image;
                        typeName = item.type || typeId;
                    }
                } else if (category === 'fitting') {
                    const item = fittingItems.find(f => f.fitting_id === typeId);
                    if (item && item.type_image) {
                        imageUrl = '<?= base_url('assets/img/fitting_types/'); ?>' + item.type_image;
                        typeName = item.type || typeId;
                    }
                } else if (category === 'solenoid') {
                    const item = solenoidItems.find(s => s.solenoid_id === typeId);
                    if (item && item.type_image) {
                        imageUrl = '<?= base_url('assets/img/solenoid_types/'); ?>' + item.type_image;
                        typeName = item.type || typeId;
                    }
                } else if (category === 'manifold' || category === 'regulator') {
                    // Manifold and Regulator don't have images, don't show image container
                    typeImageContainer.style.display = 'none';
                    return;
                }

                if (imageUrl) {
                    typeImage.src = imageUrl;
                    typeImageLabel.textContent = typeName;
                    typeImageContainer.style.display = 'block';
                } else {
                    // Show placeholder if no image
                    typeImage.src = '<?= base_url('assets/img/placeholder-image.svg'); ?>';
                    typeImageLabel.textContent = typeName + ' (No image available)';
                    typeImageContainer.style.display = 'block';
                }
            } else {
                typeImageContainer.style.display = 'none';
            }

            // Also update stock preview
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
                            let html = '<h6>Stok Saat Ini:</h6>';
                            html += '<div class="row">';

                            data.stock_locations.forEach(location => {
                                html += `
                            <div class="col-md-3 col-sm-6 mb-2">
                                <div class="card border-info">
                                    <div class="card-body p-2 text-center">
                                        <strong>${location.location_id}</strong><br>
                                        <span class="text-info">${location.amount} barang</span>
                                    </div>
                                </div>
                            </div>
                        `;
                            });

                            html += '</div>';
                            html += `<div class="mt-2"><strong>Total Stok: ${data.total_stock} barang</strong></div>`;

                            previewDiv.innerHTML = html;
                        } else {
                            previewDiv.innerHTML = '<div class="alert alert-info">Tidak ada stok yang ditemukan untuk barang ini.</div>';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching stock:', error);
                        document.getElementById('stockPreview').innerHTML = '<div class="alert alert-warning">Error memuat informasi stok.</div>';
                    });
            } else {
                document.getElementById('stockPreview').innerHTML = '<div class="text-muted">Pilih kategori dan tipe untuk melihat tingkat stok saat ini</div>';
            }
        }

        // Toggle project fields visibility
        function toggleProjectFields() {
            const isChecked = document.getElementById('is_project_item').checked;
            const projectNameField = document.getElementById('projectNameField');
            const projectNameInput = document.getElementById('project_name');

            if (isChecked) {
                projectNameField.style.display = 'block';
                projectNameInput.required = true;
            } else {
                projectNameField.style.display = 'none';
                projectNameInput.required = false;
                projectNameInput.value = '';
            }
        }

        // Event listeners
        document.getElementById('type_id').addEventListener('change', function() {
            updateTypeImage();
            updateStockPreview();
        });

        // Initialize with selected values if any
        document.addEventListener('DOMContentLoaded', function() {
            const category = document.getElementById('category').value;
            if (category) {
                updateTypeOptions();
            }

            // Initialize project fields state
            toggleProjectFields();
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