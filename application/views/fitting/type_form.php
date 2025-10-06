<!-- Card -->
<div class="card mx-auto rounded-5 shadow border-0 mb-5 w-75" style="margin-top: 5rem;">
    <!-- Card Header -->
    <div class="card-header bg-transparent border-0 px-4 pt-3 pb-1">
        <!-- Container -->
        <div class="d-flex align-items-center justify-content-between">
            <!-- Title -->
            <h4 class="m-0"><?= htmlspecialchars($title ?? 'Type Fitting', ENT_QUOTES, 'UTF-8') ?></h4>

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

        <?= form_open_multipart($form_action, ['id' => 'typeForm']); ?>
        <!-- Type Field -->
        <div class="mb-3">
            <label for="type" class="form-label">Type Fitting <span class="text-danger">*</span></label>
            <input type="text"
                class="form-control <?= form_error('type') ? 'is-invalid' : ''; ?>"
                name="type"
                id="type"
                value="<?= set_value('type', $type['type'] ?? ''); ?>"
                placeholder="Masukkan nama type fitting (contoh: ELBOW, TEE, COUPLING)"
                maxlength="30"
                required>
            <?php if (form_error('type')): ?>
                <div class="invalid-feedback">
                    <?= form_error('type'); ?>
                </div>
            <?php endif; ?>
            <div class="form-text">Maksimal 30 karakter. Hanya boleh huruf besar, angka, dan underscore. Contoh: ELBOW, TEE, COUPLING</div>
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
        <?php else: ?>
            <!-- Placeholder Image Preview -->
            <div class="mb-3">
                <label class="form-label">Preview Gambar</label>
                <div class="current-image-preview">
                    <img src="<?= base_url('assets/img/placeholder-image.svg') ?>"
                        alt="Placeholder Image"
                        class="img-thumbnail"
                        style="max-width: 200px; max-height: 200px;">
                </div>
                <div class="form-text">Gambar placeholder akan diganti saat Anda mengunggah gambar.</div>
            </div>
        <?php endif; ?>

        <!-- Submit Button -->
        <div class="d-grid">
            <button type="submit" class="btn btn-primary">
                <?= empty($type) ? 'Tambah Type' : 'Update Type' ?>
            </button>
        </div>
        <?= form_close(); ?>

        <!-- Subtype Management Section - Only show for edit mode -->
        <?php if (!empty($type) && isset($type['id'])): ?>
            <hr class="my-4">

            <div class="d-flex align-items-center justify-content-between mb-3">
                <h5 class="mb-0">Kelola Subtype untuk <?= htmlspecialchars($type['type'], ENT_QUOTES, 'UTF-8') ?></h5>
                <button type="button" class="btn btn-success btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#addSubtypeModal">
                    <i class="fas fa-plus"></i> Tambah Subtype
                </button>
            </div>

            <!-- Subtypes List -->
            <div id="subtypesList">
                <?php if (!empty($subtypes)): ?>
                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3">
                        <?php foreach ($subtypes as $subtype): ?>
                            <div class="col" data-subtype-id="<?= $subtype['id'] ?>">
                                <div class="card border-left-primary shadow-sm">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="card-title text-primary mb-1"><?= htmlspecialchars($subtype['subtype'], ENT_QUOTES, 'UTF-8') ?></h6>
                                                <?php if (!empty($subtype['description'])): ?>
                                                    <p class="card-text text-muted small mb-0"><?= htmlspecialchars($subtype['description'], ENT_QUOTES, 'UTF-8') ?></p>
                                                <?php endif; ?>
                                            </div>
                                            <div class="dropdown">
                                                <button class="btn btn-link btn-sm text-muted" type="button" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li>
                                                        <a class="dropdown-item edit-subtype" href="#"
                                                            data-id="<?= $subtype['id'] ?>"
                                                            data-subtype="<?= htmlspecialchars($subtype['subtype'], ENT_QUOTES, 'UTF-8') ?>"
                                                            data-description="<?= htmlspecialchars($subtype['description'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                                            <i class="fas fa-edit"></i> Edit
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item text-danger delete-subtype" href="#"
                                                            data-id="<?= $subtype['id'] ?>"
                                                            data-subtype="<?= htmlspecialchars($subtype['subtype'], ENT_QUOTES, 'UTF-8') ?>">
                                                            <i class="fas fa-trash"></i> Hapus
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle"></i>
                        Belum ada subtype untuk type ini. Klik "Tambah Subtype" untuk menambahkan subtype pertama.
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Subtype Modal -->
<?php if (!empty($type) && isset($type['id'])): ?>
    <div class="modal fade" id="addSubtypeModal" tabindex="-1" aria-labelledby="addSubtypeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addSubtypeModalLabel">Tambah Subtype untuk <?= htmlspecialchars($type['type'], ENT_QUOTES, 'UTF-8') ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addSubtypeForm">
                    <div class="modal-body">
                        <input type="hidden" name="parent_type" value="<?= htmlspecialchars($type['type'], ENT_QUOTES, 'UTF-8') ?>">

                        <div class="mb-3">
                            <label for="addSubtype" class="form-label">Subtype <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="addSubtype" name="subtype" required
                                placeholder="Contoh: MALE_THREAD, FEMALE_THREAD">
                            <div class="form-text">Hanya huruf besar, angka, dan underscore (_) yang diizinkan.</div>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label for="addDescription" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="addDescription" name="description" rows="2"
                                placeholder="Deskripsi opsional untuk subtype ini"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Tambah Subtype</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Subtype Modal -->
    <div class="modal fade" id="editSubtypeModal" tabindex="-1" aria-labelledby="editSubtypeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editSubtypeModalLabel">Edit Subtype</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editSubtypeForm">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="editSubtypeId">
                        <input type="hidden" name="parent_type" value="<?= htmlspecialchars($type['type'], ENT_QUOTES, 'UTF-8') ?>">

                        <div class="mb-3">
                            <label for="editSubtype" class="form-label">Subtype <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editSubtype" name="subtype" required>
                            <div class="form-text">Hanya huruf besar, angka, dan underscore (_) yang diizinkan.</div>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label for="editDescription" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="editDescription" name="description" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Update Subtype</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>

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
    });

    // Subtype Management JavaScript
    <?php if (!empty($type) && isset($type['id'])): ?>

        // Format validation for subtype inputs
        function setupSubtypeValidation(inputId) {
            const input = document.getElementById(inputId);
            if (!input) return;

            input.addEventListener('keydown', function(e) {
                const allowedKeys = [
                    'Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown',
                    'Home', 'End', 'Tab', 'Escape', 'Enter'
                ];

                if (allowedKeys.includes(e.key)) return;
                if (e.ctrlKey && ['a', 'c', 'v', 'x', 'z'].includes(e.key.toLowerCase())) return;

                const char = e.key.toUpperCase();
                if (!/^[A-Z0-9_]$/.test(char)) {
                    e.preventDefault();
                    showInputError(input, 'Hanya huruf besar, angka, dan underscore (_) yang diizinkan!');
                }
            });

            input.addEventListener('input', function(e) {
                let value = e.target.value.toUpperCase();
                const filteredValue = value.replace(/[^A-Z0-9_]/g, '');

                if (value !== filteredValue) {
                    showInputError(input, 'Hanya huruf besar, angka, dan underscore (_) yang diizinkan!');
                }

                e.target.value = filteredValue;
                clearInputError(input);
            });
        }

        function showInputError(input, message) {
            clearInputError(input);
            input.classList.add('is-invalid');
            const feedback = input.parentNode.querySelector('.invalid-feedback');
            if (feedback) feedback.textContent = message;
        }

        function clearInputError(input) {
            input.classList.remove('is-invalid');
            const feedback = input.parentNode.querySelector('.invalid-feedback');
            if (feedback) feedback.textContent = '';
        }

        function showAlert(type, message) {
            const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;

            // Insert alert at the top of subtypes section
            const subtypesSection = document.getElementById('subtypesList');
            subtypesSection.insertAdjacentHTML('beforebegin', alertHtml);

            // Auto dismiss after 5 seconds
            setTimeout(() => {
                const alert = subtypesSection.previousElementSibling;
                if (alert && alert.classList.contains('alert')) {
                    alert.remove();
                }
            }, 5000);
        }

        // Setup validation for subtype inputs
        setupSubtypeValidation('addSubtype');
        setupSubtypeValidation('editSubtype');

        // Add Subtype Form Handler
        document.getElementById('addSubtypeForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menambahkan...';

            fetch('<?= site_url('fitting_type/addSubtype') ?>', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        showAlert('success', data.message);
                        const modal = bootstrap.Modal.getInstance(document.getElementById('addSubtypeModal'));
                        if (modal) modal.hide();
                        this.reset();
                        // Reload subtypes
                        loadSubtypes();
                    } else {
                        showAlert('danger', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('danger', 'Terjadi kesalahan saat menambahkan subtype');
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Tambah Subtype';
                });
        });

        // Edit Subtype Handlers
        document.addEventListener('click', function(e) {
            if (e.target.closest('.edit-subtype')) {
                e.preventDefault();
                const link = e.target.closest('.edit-subtype');

                document.getElementById('editSubtypeId').value = link.dataset.id;
                document.getElementById('editSubtype').value = link.dataset.subtype;
                document.getElementById('editDescription').value = link.dataset.description;

                new bootstrap.Modal(document.getElementById('editSubtypeModal')).show();
            }

            if (e.target.closest('.delete-subtype')) {
                e.preventDefault();
                const link = e.target.closest('.delete-subtype');

                if (confirm(`Apakah Anda yakin ingin menghapus subtype "${link.dataset.subtype}"?`)) {
                    deleteSubtype(link.dataset.id);
                }
            }
        });

        // Edit Subtype Form Handler
        document.getElementById('editSubtypeForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengupdate...';

            fetch('<?= site_url('fitting_type/updateSubtype') ?>', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        showAlert('success', data.message);
                        const modal = bootstrap.Modal.getInstance(document.getElementById('editSubtypeModal'));
                        if (modal) modal.hide();
                        loadSubtypes();
                    } else {
                        showAlert('danger', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('danger', 'Terjadi kesalahan saat mengupdate subtype');
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Update Subtype';
                });
        });

        // Delete Subtype Function
        function deleteSubtype(id) {
            const formData = new FormData();
            formData.append('id', id);

            fetch('<?= site_url('fitting_type/deleteSubtype') ?>', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        showAlert('success', data.message);
                        loadSubtypes();
                    } else {
                        showAlert('danger', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('danger', 'Terjadi kesalahan saat menghapus subtype');
                });
        }

        // Load Subtypes Function
        function loadSubtypes() {
            const formData = new FormData();
            formData.append('type', '<?= htmlspecialchars($type['type'], ENT_QUOTES, 'UTF-8') ?>');

            fetch('<?= site_url('fitting_type/getSubtypes') ?>', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(subtypes => {
                    // Ensure subtypes is an array
                    if (!Array.isArray(subtypes)) {
                        console.error('Expected array but got:', subtypes);
                        throw new Error('Invalid response format');
                    }
                    const container = document.getElementById('subtypesList');

                    if (subtypes.length === 0) {
                        container.innerHTML = `
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle"></i>
                        Belum ada subtype untuk type ini. Klik "Tambah Subtype" untuk menambahkan subtype pertama.
                    </div>
                `;
                        return;
                    }

                    let html = '<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3">';

                    subtypes.forEach(subtype => {
                        html += `
                    <div class="col" data-subtype-id="${subtype.id}">
                        <div class="card border-left-primary shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="card-title text-primary mb-1">${subtype.subtype}</h6>
                                        ${subtype.description ? `<p class="card-text text-muted small mb-0">${subtype.description}</p>` : ''}
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-link btn-sm text-muted" type="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item edit-subtype" href="#" 
                                                   data-id="${subtype.id}"
                                                   data-subtype="${subtype.subtype}"
                                                   data-description="${subtype.description || ''}">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item text-danger delete-subtype" href="#" 
                                                   data-id="${subtype.id}"
                                                   data-subtype="${subtype.subtype}">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                    });

                    html += '</div>';
                    container.innerHTML = html;
                })
                .catch(error => {
                    console.error('Error loading subtypes:', error);
                    showAlert('danger', 'Gagal memuat data subtype: ' + error.message);
                });
        }

    <?php endif; ?>
</script>

<style>
    /* Subtype card styling */
    .border-left-primary {
        border-left: 0.375rem solid #0d6efd !important;
    }

    .card.border-left-primary {
        border-left-width: 4px;
        border-left-color: #0d6efd;
    }

    .card.border-left-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 0.125rem 0.5rem rgba(0, 0, 0, 0.15) !important;
        transition: all 0.2s ease-in-out;
    }

    /* Modal styling improvements */
    .modal-header {
        border-bottom: 1px solid #dee2e6;
    }

    .modal-footer {
        border-top: 1px solid #dee2e6;
    }

    /* Alert positioning */
    .alert {
        margin-bottom: 1rem;
    }
</style>