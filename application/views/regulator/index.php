<?php if ($this->session->flashdata('action')) : ?>
    <!-- Flash Notification Alert (ASRS-style positioned so it appears below fixed header) -->
    <div class="cust-notification m-3">
        <div class="alert alert-<?= $this->session->flashdata('action')[0]; ?> alert-dismissible fade show" id="notification" role="alert">
            <?= $this->session->flashdata('action')[1]; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
<?php endif; ?>

<!-- Main Content Card -->
<div class="card mx-auto rounded-5 shadow border-0 table-responsive mb-5" style="margin-top: 5rem; max-width: 95%;">
    <!-- Card Header with Title and Search -->
    <div class="card-header bg-white border-bottom px-lg-5 px-4 py-4">
        <div class="row g-3 align-items-center">
            <!-- Page Title -->
            <div class="col-12 col-lg-6">
                <h3 class="m-0">Data Regulator</h3>
            </div>

            <!-- Search Form + Add Regulator -->
            <div class="col-12 col-lg-6">
                <div class="d-flex gap-2 align-items-center">
                    <!-- Search + Filters Form -->
                    <form action="<?= site_url('regulator/index'); ?>" method="get" class="flex-grow-1" id="search-form">
                        <div class="input-group">
                            <!-- make the input area a positioned container so absolute children are anchored inside it -->
                            <div class="position-relative flex-grow-1">
                                <input type="text" class="form-control rounded-start-pill pe-5" placeholder="Cari berdasarkan ID, type..." name="search" value="<?= $search ?? ''; ?>" id="search-bar" autocomplete="off">
                                <img src="<?= base_url('assets/img/delete.png'); ?>" alt="delete" class="action-button clear-button" id="clear-button" onclick="clearKeyword()">
                            </div>

                            <button class="btn btn-secondary rounded-end-pill px-4" type="submit">Cari</button>
                        </div>

                        <div class="mt-2 d-flex gap-2">
                            <!-- Type Filter -->
                            <select class="form-select form-select-sm" name="type" id="type-filter" aria-label="Filter Type">
                                <option value="">Semua Type</option>
                                <?php
                                $types = array_unique(array_column($regulators, 'type'));
                                foreach ($types as $type) :
                                ?>
                                    <option value="<?= $type; ?>" <?= ($filter_type ?? '') === $type ? 'selected' : ''; ?>><?= $type; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>

                    <!-- Add Regulator Button -->
                    <a href="<?= site_url('regulator/add'); ?>" class="btn btn-primary rounded-pill px-4" type="button">Tambah</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Body -->
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>
                        <a href="?sort_by=regulator_id&sort_order=<?= ($sort_by === 'regulator_id' && $sort_order === 'ASC') ? 'DESC' : 'ASC'; ?><?= $search ? '&search=' . urlencode($search) : ''; ?><?= $filter_type ? '&type=' . urlencode($filter_type) : ''; ?>" class="text-decoration-none text-dark">
                            Regulator ID <?= $sort_by === 'regulator_id' ? ($sort_order === 'ASC' ? '▲' : '▼') : ''; ?>
                        </a>
                    </th>
                    <th>
                        <a href="?sort_by=type&sort_order=<?= ($sort_by === 'type' && $sort_order === 'ASC') ? 'DESC' : 'ASC'; ?><?= $search ? '&search=' . urlencode($search) : ''; ?><?= $filter_type ? '&type=' . urlencode($filter_type) : ''; ?>" class="text-decoration-none text-dark">
                            Type <?= $sort_by === 'type' ? ($sort_order === 'ASC' ? '▲' : '▼') : ''; ?>
                        </a>
                    </th>
                    <th>
                        <a href="?sort_by=min_stock&sort_order=<?= ($sort_by === 'min_stock' && $sort_order === 'ASC') ? 'DESC' : 'ASC'; ?><?= $search ? '&search=' . urlencode($search) : ''; ?><?= $filter_type ? '&type=' . urlencode($filter_type) : ''; ?>" class="text-decoration-none text-dark">
                            Min Stock <?= $sort_by === 'min_stock' ? ($sort_order === 'ASC' ? '▲' : '▼') : ''; ?>
                        </a>
                    </th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($regulators)) : ?>
                    <tr>
                        <td colspan="4" class="text-center py-4">Tidak ada data regulator</td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($regulators as $regulator) : ?>
                        <tr>
                            <td><?= $regulator['regulator_id']; ?></td>
                            <td><?= $regulator['type']; ?></td>
                            <td><?= $regulator['min_stock']; ?></td>
                            <td>
                                <a href="<?= site_url('regulator/edit/' . $regulator['regulator_id']); ?>" class="btn btn-sm btn-warning">Edit</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Card Footer with Pagination -->
    <div class="card-footer bg-white border-top px-lg-5 px-4 py-3">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                Showing <?= !empty($regulators) ? (($current_page - 1) * $per_page + 1) : 0; ?>
                to <?= min($current_page * $per_page, $total_rows); ?>
                of <?= $total_rows; ?> entries
            </div>
            <div>
                <?= $pagination; ?>
            </div>
        </div>

        <!-- Download & Upload Buttons -->
        <div class="mt-3 d-flex gap-2">
            <a href="<?= site_url('regulator/download'); ?>" class="btn btn-success btn-sm">
                <i class="fas fa-download"></i> Download CSV
            </a>
            <a href="<?= site_url('regulator/downloadPDF'); ?>" class="btn btn-danger btn-sm">
                <i class="fas fa-file-pdf"></i> Download PDF
            </a>
            <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#uploadModal">
                <i class="fas fa-upload"></i> Upload CSV
            </button>
            <a href="<?= site_url('regulator/download_template'); ?>" class="btn btn-secondary btn-sm">
                <i class="fas fa-download"></i> Download Template
            </a>
        </div>
    </div>
</div>

<!-- Upload CSV Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadModalLabel">Upload CSV Regulator</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="uploadForm" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="csv_file" class="form-label">Pilih File CSV</label>
                        <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv" required>
                    </div>
                    <div class="alert alert-info">
                        <small>
                            <strong>Format CSV:</strong><br>
                            - Column 1: Type<br>
                            - Column 2: Min Stock (optional, default 5)<br>
                            <br>
                            Download template untuk contoh format yang benar.
                        </small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="uploadCSV()">Upload</button>
            </div>
        </div>
    </div>
</div>

<script>
    function clearKeyword() {
        document.getElementById('search-bar').value = '';
        document.getElementById('search-form').submit();
    }

    function uploadCSV() {
        const form = document.getElementById('uploadForm');
        const formData = new FormData(form);

        fetch('<?= site_url('regulator/upload'); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat upload file.');
            });
    }
</script>