<!-- Card -->
<div class="card mx-auto rounded-5 shadow border-0 mb-5 w-75" style="margin-top: 5rem;">
    <!-- Card Header -->
    <div class="card-header bg-transparent border-0 px-4 pt-3 pb-1">
        <!-- Container -->
        <div class="d-flex align-items-center justify-content-between">
            <!-- Title -->
            <h4 class="m-0"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h4>

            <!-- Back Button-->
            <a href="<?= site_url('fitting_type'); ?>" class="btn btn-secondary rounded-pill">Kembali</a>
        </div>
    </div>

    <!-- Card Body -->
    <div class="card-body p-4 pt-3">
        <?php if ($this->session->flashdata('action')) : ?>
            <div class="alert alert-<?= $this->session->flashdata('action')[0]; ?> alert-dismissible fade show" role="alert">
                <?= $this->session->flashdata('action')[1]; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form action="<?= htmlspecialchars($form_action, ENT_QUOTES, 'UTF-8') ?>" method="post" enctype="multipart/form-data">
            <!-- Type Field -->
            <div class="mb-3">
                <label for="type" class="form-label">Type Fitting <span class="text-danger">*</span></label>
                <input type="text"
                    class="form-control <?= form_error('type') ? 'is-invalid' : ''; ?>"
                    name="type"
                    id="type"
                    value="<?= set_value('type', $type['type'] ?? ''); ?>"
                    placeholder="Masukkan nama type fitting (contoh: ELBOW_90, TEE, REDUCER)"
                    maxlength="30"
                    required>
                <?php if (form_error('type')): ?>
                    <div class="invalid-feedback">
                        <?= form_error('type'); ?>
                    </div>
                <?php endif; ?>
                <div class="form-text">Maksimal 30 karakter. Hanya boleh huruf besar, angka, dan underscore. Contoh: ELBOW_90, TEE, BALL_VALVE</div>
            </div>

            <!-- Image Field -->
            <div class="mb-3">
                <label for="image" class="form-label">Gambar Type</label>
                <input type="file"
                    class="form-control"
                    name="image"
                    id="image"
                    accept=".jpg,.jpeg,.png">
                <div class="form-text">
                    Format yang didukung: JPG, JPEG, PNG. Maksimal 2MB.
                    <?php if (!empty($type['image'])): ?>
                        <br>File saat ini: <?= htmlspecialchars($type['image'], ENT_QUOTES, 'UTF-8') ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Current Image Preview (for edit mode) -->
            <?php if (!empty($type['image'])): ?>
                <div class="mb-3">
                    <label class="form-label">Gambar Saat Ini</label>
                    <div class="current-image-preview">
                        <img src="<?= base_url('assets/img/fitting_types/' . htmlspecialchars($type['image'], ENT_QUOTES, 'UTF-8')) ?>"
                            alt="<?= htmlspecialchars($type['type'] ?? 'Type Image', ENT_QUOTES, 'UTF-8') ?>"
                            class="img-thumbnail"
                            style="max-width: 200px; max-height: 200px;">
                    </div>
                    <div class="form-text">Unggah gambar baru untuk mengganti gambar ini.</div>
                </div>
            <?php endif; ?>

            <!-- Subtype Management -->
            <hr class="my-4">
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Kelola Subtypes</h5>
                    <button type="button" class="btn btn-success btn-sm" id="addSubtypeBtn">
                        <i class="fas fa-plus"></i> Tambah Subtype
                    </button>
                </div>

                <!-- Add Subtype Form -->
                <div id="addSubtypeForm" class="card border-success mb-3" style="display: none;">
                    <div class="card-body">
                        <h6 class="card-title">Tambah Subtype Baru</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <input type="text" class="form-control" id="newSubtype" placeholder="Nama Subtype (contoh: MALE_THREAD)" maxlength="50">
                                <small class="form-text text-muted">Hanya huruf besar, angka, dan underscore</small>
                            </div>
                            <div class="col-md-6">
                                <input type="text" class="form-control" id="newSubtypeDescription" placeholder="Deskripsi (opsional)">
                            </div>
                            <div class="col-md-2">
                                <div class="btn-group w-100">
                                    <button type="button" class="btn btn-success btn-sm" id="saveSubtypeBtn">
                                        <i class="fas fa-save"></i>
                                    </button>
                                    <button type="button" class="btn btn-secondary btn-sm" id="cancelSubtypeBtn">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Subtypes List -->
                <div id="subtypesList">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Subtype</th>
                                    <th>Deskripsi</th>
                                    <th width="120">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="subtypesTableBody">
                                <!-- Will be loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="d-grid">
                <button type="submit" class="btn btn-primary">
                    <?= empty($type) ? 'Tambah Type' : 'Update Type' ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Preview uploaded image
    document.getElementById('image').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                // Remove existing preview if any
                const existingPreview = document.getElementById('image-preview');
                if (existingPreview) {
                    existingPreview.remove();
                }

                // Create new preview
                const preview = document.createElement('div');
                preview.id = 'image-preview';
                preview.className = 'mb-3';
                preview.innerHTML = `
                    <label class="form-label">Preview Gambar Baru</label>
                    <div>
                        <img src="${e.target.result}" alt="Preview" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                    </div>
                `;

                // Insert after the file input
                document.getElementById('image').parentNode.insertAdjacentElement('afterend', preview);
            };
            reader.readAsDataURL(file);
        }
    });

    // Subtype management
    let currentType = '<?= htmlspecialchars($type['type'] ?? '', ENT_QUOTES, 'UTF-8') ?>';

    // Function to get current type from input
    function getCurrentType() {
        return document.getElementById('type').value.trim().toUpperCase();
    }

    // Load subtypes when page loads (only if type exists)
    document.addEventListener('DOMContentLoaded', function() {
        if (currentType) {
            loadSubtypes();
        } else {
            // For add mode, show empty table
            displaySubtypes([]);
        }
    });

    // Update currentType when type input changes
    document.getElementById('type').addEventListener('input', function() {
        const newType = getCurrentType();
        if (newType !== currentType) {
            currentType = newType;
            if (currentType) {
                loadSubtypes();
            } else {
                displaySubtypes([]);
            }
        }
    });

    // Show/hide add subtype form
    document.getElementById('addSubtypeBtn').addEventListener('click', function() {
        const form = document.getElementById('addSubtypeForm');
        form.style.display = form.style.display === 'none' ? 'block' : 'none';
        if (form.style.display === 'block') {
            document.getElementById('newSubtype').focus();
        }
    });

    // Cancel add subtype
    document.getElementById('cancelSubtypeBtn').addEventListener('click', function() {
        document.getElementById('addSubtypeForm').style.display = 'none';
        document.getElementById('newSubtype').value = '';
        document.getElementById('newSubtypeDescription').value = '';
    });

    // Save new subtype
    document.getElementById('saveSubtypeBtn').addEventListener('click', function() {
        const subtype = document.getElementById('newSubtype').value.trim();
        const description = document.getElementById('newSubtypeDescription').value.trim();

        if (!subtype) {
            alert('Nama subtype harus diisi');
            return;
        }

        if (!currentType) {
            alert('Nama type harus diisi terlebih dahulu');
            document.getElementById('type').focus();
            return;
        }

        saveSubtype(subtype, description);
    });

    // Enter key support for add form
    document.getElementById('newSubtype').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            document.getElementById('saveSubtypeBtn').click();
        }
    });

    document.getElementById('newSubtypeDescription').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            document.getElementById('saveSubtypeBtn').click();
        }
    });

    // Load subtypes function
    function loadSubtypes() {
        if (!currentType) return;

        fetch('<?= site_url('fitting_type/getSubtypes') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'type=' + encodeURIComponent(currentType)
            })
            .then(response => response.json())
            .then(data => {
                displaySubtypes(data);
            })
            .catch(error => {
                console.error('Error loading subtypes:', error);
            });
    } // Display subtypes in table
    function displaySubtypes(subtypes) {
        const tbody = document.getElementById('subtypesTableBody');

        if (!subtypes || subtypes.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">Belum ada subtype</td></tr>';
            return;
        }

        let html = '';
        subtypes.forEach((subtype, index) => {
            // Determine delete button HTML based on usage
            let deleteButtonHtml;
            if (subtype.is_in_use) {
                deleteButtonHtml = `
                        <span class="btn btn-outline-secondary btn-sm" 
                            data-bs-toggle="tooltip" 
                            data-bs-placement="top" 
                            title="Tidak dapat dihapus karena sedang digunakan oleh fitting"
                            style="cursor: not-allowed;">
                            <i class="fas fa-trash"></i>
                        </span>
                    `;
            } else {
                deleteButtonHtml = `
                        <button type="button" class="btn btn-outline-danger btn-sm delete-btn" onclick="deleteSubtype(${subtype.id}, '${subtype.subtype}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    `;
            }

            html += `
                <tr id="subtypeRow${subtype.id}">
                    <td>${index + 1}</td>
                    <td>
                        <span class="subtype-display">${subtype.subtype}</span>
                        <input type="text" class="form-control subtype-edit" value="${subtype.subtype}" style="display: none;" maxlength="50">
                    </td>
                    <td>
                        <span class="description-display">${subtype.description || '-'}</span>
                        <input type="text" class="form-control description-edit" value="${subtype.description || ''}" style="display: none;">
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm action-buttons">
                            <button type="button" class="btn btn-outline-primary btn-sm edit-btn" onclick="editSubtype(${subtype.id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            ${deleteButtonHtml}
                        </div>
                        <div class="btn-group btn-group-sm edit-buttons" style="display: none;">
                            <button type="button" class="btn btn-success btn-sm save-btn" onclick="saveEditSubtype(${subtype.id})">
                                <i class="fas fa-save"></i>
                            </button>
                            <button type="button" class="btn btn-secondary btn-sm cancel-btn" onclick="cancelEditSubtype(${subtype.id})">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });
        tbody.innerHTML = html;

        // Initialize tooltips for the newly created elements
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    // Save new subtype
    function saveSubtype(subtype, description) {
        fetch('<?= site_url('fitting_type/addSubtype') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `parent_type=${encodeURIComponent(currentType)}&subtype=${encodeURIComponent(subtype)}&description=${encodeURIComponent(description)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Reset form
                    document.getElementById('newSubtype').value = '';
                    document.getElementById('newSubtypeDescription').value = '';
                    document.getElementById('addSubtypeForm').style.display = 'none';

                    // Reload subtypes
                    loadSubtypes();

                    // Show success message
                    showMessage('success', data.message);
                } else {
                    showMessage('error', data.message);
                }
            })
            .catch(error => {
                console.error('Error saving subtype:', error);
                showMessage('error', 'Terjadi kesalahan saat menyimpan subtype');
            });
    }

    // Edit subtype
    function editSubtype(id) {
        const row = document.getElementById(`subtypeRow${id}`);
        const subtypeDisplay = row.querySelector('.subtype-display');
        const subtypeEdit = row.querySelector('.subtype-edit');
        const descriptionDisplay = row.querySelector('.description-display');
        const descriptionEdit = row.querySelector('.description-edit');
        const actionButtons = row.querySelector('.action-buttons');
        const editButtons = row.querySelector('.edit-buttons');

        // Store original values
        subtypeEdit.setAttribute('data-original', subtypeDisplay.textContent);
        descriptionEdit.setAttribute('data-original', descriptionDisplay.textContent === '-' ? '' : descriptionDisplay.textContent);

        // Show edit inputs
        subtypeDisplay.style.display = 'none';
        subtypeEdit.style.display = 'block';
        descriptionDisplay.style.display = 'none';
        descriptionEdit.style.display = 'block';
        actionButtons.style.display = 'none';
        editButtons.style.display = 'block';

        subtypeEdit.focus();
    }

    // Cancel edit subtype
    function cancelEditSubtype(id) {
        const row = document.getElementById(`subtypeRow${id}`);
        const subtypeDisplay = row.querySelector('.subtype-display');
        const subtypeEdit = row.querySelector('.subtype-edit');
        const descriptionDisplay = row.querySelector('.description-display');
        const descriptionEdit = row.querySelector('.description-edit');
        const actionButtons = row.querySelector('.action-buttons');
        const editButtons = row.querySelector('.edit-buttons');

        // Restore original values
        subtypeEdit.value = subtypeEdit.getAttribute('data-original');
        descriptionEdit.value = descriptionEdit.getAttribute('data-original');

        // Show display elements
        subtypeDisplay.style.display = 'block';
        subtypeEdit.style.display = 'none';
        descriptionDisplay.style.display = 'block';
        descriptionEdit.style.display = 'none';
        actionButtons.style.display = 'block';
        editButtons.style.display = 'none';
    }

    // Save edit subtype
    function saveEditSubtype(id) {
        const row = document.getElementById(`subtypeRow${id}`);
        const subtypeEdit = row.querySelector('.subtype-edit');
        const descriptionEdit = row.querySelector('.description-edit');

        const subtype = subtypeEdit.value.trim();
        const description = descriptionEdit.value.trim();

        if (!subtype) {
            alert('Nama subtype harus diisi');
            return;
        }

        fetch('<?= site_url('fitting_type/updateSubtype') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `id=${id}&parent_type=${encodeURIComponent(currentType)}&subtype=${encodeURIComponent(subtype)}&description=${encodeURIComponent(description)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Reload subtypes to show updated data
                    loadSubtypes();
                    showMessage('success', data.message);
                } else {
                    showMessage('error', data.message);
                }
            })
            .catch(error => {
                console.error('Error updating subtype:', error);
                showMessage('error', 'Terjadi kesalahan saat memperbarui subtype');
            });
    }

    // Delete subtype
    function deleteSubtype(id, subtypeName) {
        if (!confirm(`Apakah Anda yakin ingin menghapus subtype "${subtypeName}"?\n\nPerhatian: Subtype yang sedang digunakan oleh fitting tidak dapat dihapus.`)) {
            return;
        }

        fetch('<?= site_url('fitting_type/deleteSubtype') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Reload subtypes
                    loadSubtypes();
                    showMessage('success', data.message);
                } else {
                    showMessage('error', data.message);
                }
            })
            .catch(error => {
                console.error('Error deleting subtype:', error);
                showMessage('error', 'Terjadi kesalahan saat menghapus subtype');
            });
    }

    // Show message function
    function showMessage(type, message) {
        // Remove existing alerts
        const existingAlert = document.querySelector('.alert');
        if (existingAlert) {
            existingAlert.remove();
        }

        // Create new alert
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const alert = document.createElement('div');
        alert.className = `alert ${alertClass} alert-dismissible fade show`;
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;

        // Insert at the beginning of card body
        const cardBody = document.querySelector('.card-body');
        cardBody.insertBefore(alert, cardBody.firstChild);

        // Auto dismiss after 5 seconds
        setTimeout(() => {
            if (alert && alert.parentNode) {
                alert.remove();
            }
        }, 5000);
    }
    });

    // Enforce uppercase and underscore format for type field
    document.addEventListener('DOMContentLoaded', function() {
        const typeInput = document.getElementById('type');
        if (typeInput) {
            let warningTimeout;

            // Block disallowed characters on keydown (before they appear)
            typeInput.addEventListener('keydown', function(e) {
                // Allow control keys (backspace, delete, arrow keys, etc.)
                const allowedKeys = [
                    'Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown',
                    'Home', 'End', 'Tab', 'Escape', 'Enter'
                ];

                if (allowedKeys.includes(e.key)) {
                    return; // Allow control keys
                }

                // Allow Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X, Ctrl+Z
                if (e.ctrlKey && ['a', 'c', 'v', 'x', 'z'].includes(e.key.toLowerCase())) {
                    return; // Allow copy/paste/select all
                }

                // Check if the key is an allowed character (letters, numbers, underscore)
                const char = e.key.toUpperCase();
                if (!/^[A-Z0-9_]$/.test(char)) {
                    e.preventDefault(); // Block the key
                    showWarning('Hanya huruf besar, angka, dan underscore (_) yang diizinkan!');
                    return;
                }
            });

            // Convert to uppercase on input (for pasted content or other inputs)
            typeInput.addEventListener('input', function(e) {
                let value = e.target.value.toUpperCase();
                // Only allow letters, numbers, and underscores
                const filteredValue = value.replace(/[^A-Z0-9_]/g, '');

                if (value !== filteredValue) {
                    showWarning('Hanya huruf besar, angka, dan underscore (_) yang diizinkan!');
                }

                e.target.value = filteredValue;
            });

            // Validate format on blur and hide warning
            typeInput.addEventListener('blur', function(e) {
                const value = e.target.value;
                hideWarning();
                if (value && !value.match(/^[A-Z0-9_]+$/)) {
                    e.target.setCustomValidity('Type harus menggunakan huruf besar, angka, dan underscore saja');
                } else {
                    e.target.setCustomValidity('');
                }
            });

            function showWarning(message) {
                // Remove existing warning
                hideWarning();

                // Create warning element
                const warning = document.createElement('div');
                warning.className = 'text-danger';
                warning.style.cssText = 'font-size: 0.875rem; margin-top: 0.25rem; font-weight: 500;';
                warning.textContent = message;
                warning.setAttribute('data-warning', 'character-warning');

                // Insert after input
                typeInput.parentNode.appendChild(warning);

                // Add red border to input
                typeInput.style.borderColor = '#dc3545';
                typeInput.style.boxShadow = '0 0 0 0.2rem rgba(220, 53, 69, 0.25)';

                // Auto-hide after 3 seconds
                clearTimeout(warningTimeout);
                warningTimeout = setTimeout(() => {
                    hideWarning();
                }, 3000);
            }

            function hideWarning() {
                const warning = typeInput.parentNode.querySelector('[data-warning="character-warning"]');
                if (warning) {
                    warning.remove();
                }
                // Reset border color
                typeInput.style.borderColor = '';
                typeInput.style.boxShadow = '';
            }
        }

        // Enforce uppercase and underscore format for subtype fields
        function enforceSubtypeFormat(input) {
            let subtypeWarningTimeout;

            // Block disallowed characters on keydown for subtypes
            input.addEventListener('keydown', function(e) {
                // Allow control keys (backspace, delete, arrow keys, etc.)
                const allowedKeys = [
                    'Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown',
                    'Home', 'End', 'Tab', 'Escape', 'Enter'
                ];

                if (allowedKeys.includes(e.key)) {
                    return; // Allow control keys
                }

                // Allow Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X, Ctrl+Z
                if (e.ctrlKey && ['a', 'c', 'v', 'x', 'z'].includes(e.key.toLowerCase())) {
                    return; // Allow copy/paste/select all
                }

                // Check if the key is an allowed character (letters, numbers, underscore)
                const char = e.key.toUpperCase();
                if (!/^[A-Z0-9_]$/.test(char)) {
                    e.preventDefault(); // Block the key
                    showSubtypeWarning(input, 'Hanya huruf besar, angka, dan underscore (_) yang diizinkan!');
                    return;
                }
            });

            input.addEventListener('input', function(e) {
                let value = e.target.value.toUpperCase();
                // Only allow letters, numbers, and underscores
                const filteredValue = value.replace(/[^A-Z0-9_]/g, '');

                if (value !== filteredValue) {
                    showSubtypeWarning(input, 'Hanya huruf besar, angka, dan underscore (_) yang diizinkan!');
                }

                e.target.value = filteredValue;
            });

            input.addEventListener('blur', function(e) {
                const value = e.target.value;
                hideSubtypeWarning(input);
                if (value && !value.match(/^[A-Z0-9_]+$/)) {
                    e.target.setCustomValidity('Subtype harus menggunakan huruf besar, angka, dan underscore saja');
                } else {
                    e.target.setCustomValidity('');
                }
            });

            function showSubtypeWarning(input, message) {
                // Remove existing warning
                hideSubtypeWarning(input);

                // Create warning element
                const warning = document.createElement('div');
                warning.className = 'text-danger';
                warning.style.cssText = 'font-size: 0.875rem; margin-top: 0.25rem; font-weight: 500;';
                warning.textContent = message;
                warning.setAttribute('data-warning', 'subtype-character-warning');

                // Insert after input
                input.parentNode.appendChild(warning);

                // Add red border to input
                input.style.borderColor = '#dc3545';
                input.style.boxShadow = '0 0 0 0.2rem rgba(220, 53, 69, 0.25)';

                // Auto-hide after 3 seconds
                clearTimeout(subtypeWarningTimeout);
                subtypeWarningTimeout = setTimeout(() => {
                    hideSubtypeWarning(input);
                }, 3000);
            }

            function hideSubtypeWarning(input) {
                const warning = input.parentNode.querySelector('[data-warning="subtype-character-warning"]');
                if (warning) {
                    warning.remove();
                }
                // Reset border color
                input.style.borderColor = '';
                input.style.boxShadow = '';
            }
        }

        // Apply format enforcement to new subtype input
        const newSubtypeInput = document.getElementById('newSubtype');
        if (newSubtypeInput) {
            enforceSubtypeFormat(newSubtypeInput);
        }

        // Apply format enforcement to existing subtype edit inputs (will be created dynamically)
        document.addEventListener('keydown', function(e) {
            if (e.target.classList.contains('subtype-edit')) {
                // Allow control keys (backspace, delete, arrow keys, etc.)
                const allowedKeys = [
                    'Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown',
                    'Home', 'End', 'Tab', 'Escape', 'Enter'
                ];

                if (allowedKeys.includes(e.key)) {
                    return; // Allow control keys
                }

                // Allow Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X, Ctrl+Z
                if (e.ctrlKey && ['a', 'c', 'v', 'x', 'z'].includes(e.key.toLowerCase())) {
                    return; // Allow copy/paste/select all
                }

                // Check if the key is an allowed character (letters, numbers, underscore)
                const char = e.key.toUpperCase();
                if (!/^[A-Z0-9_]$/.test(char)) {
                    e.preventDefault(); // Block the key
                    showDynamicSubtypeWarning(e.target, 'Hanya huruf besar, angka, dan underscore (_) yang diizinkan!');
                    return;
                }
            }
        });

        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('subtype-edit')) {
                let value = e.target.value.toUpperCase();
                const filteredValue = value.replace(/[^A-Z0-9_]/g, '');

                if (value !== filteredValue) {
                    showDynamicSubtypeWarning(e.target, 'Hanya huruf besar, angka, dan underscore (_) yang diizinkan!');
                }

                e.target.value = filteredValue;
            }
        });

        document.addEventListener('blur', function(e) {
            if (e.target.classList.contains('subtype-edit')) {
                const value = e.target.value;
                hideDynamicSubtypeWarning(e.target);
                if (value && !value.match(/^[A-Z0-9_]+$/)) {
                    e.target.setCustomValidity('Subtype harus menggunakan huruf besar, angka, dan underscore saja');
                } else {
                    e.target.setCustomValidity('');
                }
            }
        });

        // Show dynamic subtype warning (for table edit inputs)
        function showDynamicSubtypeWarning(input, message) {
            // Remove existing warning
            hideDynamicSubtypeWarning(input);

            // Find the table cell that contains this input
            const cell = input.closest('td');
            if (!cell) return;

            // Create warning element
            const warning = document.createElement('div');
            warning.className = 'text-danger';
            warning.style.cssText = 'font-size: 0.75rem; margin-top: 0.25rem; font-weight: 500;';
            warning.textContent = message;
            warning.setAttribute('data-warning', 'dynamic-subtype-warning');

            // Insert after input in the cell
            cell.appendChild(warning);

            // Add red border to input
            input.style.borderColor = '#dc3545';
            input.style.boxShadow = '0 0 0 0.2rem rgba(220, 53, 69, 0.25)';

            // Auto-hide after 3 seconds
            setTimeout(() => {
                hideDynamicSubtypeWarning(input);
            }, 3000);
        }

        // Hide dynamic subtype warning
        function hideDynamicSubtypeWarning(input) {
            const cell = input.closest('td');
            if (cell) {
                const warning = cell.querySelector('[data-warning="dynamic-subtype-warning"]');
                if (warning) {
                    warning.remove();
                }
            }
            // Reset border color
            input.style.borderColor = '';
            input.style.boxShadow = '';
        }
    });
</script>