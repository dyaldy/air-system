<?php if ($this->session->flashdata('action')) : ?>
    <!-- Flash Notification Alert (positioned below fixed header) -->
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
            <!-- Kembali Button above Page Title -->
            <div class="col-12 col-lg-6">
                <a href="<?= site_url('fitting/type'); ?>" class="btn btn-outline-secondary rounded-pill mb-2">
                    <i class="fas fa-arrow-left"></i> Kembali ke Pilih Type
                </a>
                <h3 class="m-0">Data Fitting</h3>
                <?php if ($hasFilters) : ?>
                    <small class="text-muted">Menampilkan hasil pencarian/filter</small>
                <?php endif; ?>
            </div>

            <!-- Search and Action Buttons -->
            <div class="col-12 col-lg-6">
                <div class="d-flex flex-column flex-lg-row gap-2 align-items-lg-end">
                    <!-- Search Form -->
                    <form method="post" action="" class="flex-fill">
                        <div class="input-group">
                            <input type="search" name="keyword" id="keyword" class="form-control" placeholder="Cari fitting..." value="<?= htmlspecialchars($searchKeyword ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            <button class="btn btn-outline-secondary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>

                    <!-- Filter and Action Buttons -->
                    <div class="d-flex gap-2">
                        <!-- Filter Button -->
                        <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-toggle="modal" data-bs-target="#filterModal">
                            <i class="fas fa-filter"></i> Filter
                        </button>

                        <!-- Download Button -->
                        <div class="dropdown">
                            <button class="btn btn-success rounded-pill dropdown-toggle px-4" type="button" id="downloadDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-download"></i>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="downloadDropdown">
                                <li><a class="dropdown-item" href="<?= site_url('fitting/download'); ?>">Download Data</a></li>
                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#uploadModal">Upload Data</a></li>
                                <li><a class="dropdown-item" href="<?= site_url('fitting/template'); ?>">Download Template</a></li>
                            </ul>
                        </div>

                        <!-- Clear Filter Button (only shown if there are active filters) -->
                        <?php if ($hasFilters) : ?>
                            <form method="post" action="" class="d-inline">
                                <input type="hidden" name="clear_all" value="1">
                                <button type="submit" class="btn btn-warning rounded-pill">
                                    <i class="fas fa-times"></i> Clear
                                </button>
                            </form>
                        <?php endif; ?>

                        <!-- Filter Dropdown -->
                        <form method="post" action="" class="d-inline">
                            <select class="form-select form-select-sm" id="type-filter" aria-label="Filter Type">
                                <option value="">Semua Type</option>
                                <?php foreach (($type_options ?? []) as $type) : ?>
                                    <?php $selected = (!empty($filterKeyword['type']) && in_array($type, (array)$filterKeyword['type'])) ? 'selected' : ''; ?>
                                    <option value="<?= $type; ?>" <?= $selected; ?>><?= $type; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </form>

                        <!-- Add Fitting Button -->
                        <a href="<?= site_url('fitting/add'); ?>" class="btn btn-primary rounded-pill px-4" type="button">Tambah</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (empty($fittings)) : ?>
        <!-- No Data Alert -->
        <div class="alert alert-warning m-4" role="alert">
            <h5 class="alert-heading">Tidak Ada Data</h5>
            <p>Fitting tidak ditemukan. Coba sesuaikan kata kunci pencarian atau filter Anda.</p>
        </div>
    <?php else : ?>
        <!-- Data Table Container -->
        <div class="card-body p-0 table-responsive">
            <table class="table table-borderless table-hover table-striped mb-0">
                <!-- Table Header -->
                <thead>
                    <tr>
                        <!-- Fitting ID Column -->
                        <th scope="col" class="text-center ps-lg-5 ps-4">
                            <div class="d-flex align-items-center justify-content-center gap-1">
                                <span>Fitting ID</span>
                                <?php if ($sortKeyword[0] === 'fitting_id') : ?>
                                    <?php if ($sortKeyword[1] === 'ASC') : ?>
                                        <img src="<?= base_url('assets/img/sort-up-active.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('fitting_id-DESC')" data-bs-toggle="tooltip" data-bs-placement="top" title="Urutkan berdasarkan Fitting ID (Descending)">
                                    <?php else : ?>
                                        <img src="<?= base_url('assets/img/sort-down-active.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('fitting_id-ASC')" data-bs-toggle="tooltip" data-bs-placement="top" title="Urutkan berdasarkan Fitting ID (Ascending)">
                                    <?php endif ?>
                                <?php else : ?>
                                    <img src="<?= base_url('assets/img/sort-default.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('fitting_id-ASC')" data-bs-toggle="tooltip" data-bs-placement="top" title="Urutkan berdasarkan Fitting ID (Ascending)">
                                <?php endif ?>
                            </div>
                        </th>

                        <!-- Type Column -->
                        <th scope="col" class="text-center">
                            <div class="d-flex align-items-center justify-content-center gap-1">
                                <span>Type</span>
                                <?php if ($sortKeyword[0] === 'type') : ?>
                                    <?php if ($sortKeyword[1] === 'ASC') : ?>
                                        <img src="<?= base_url('assets/img/sort-up-active.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('type-DESC')" data-bs-toggle="tooltip" data-bs-placement="top" title="Urutkan berdasarkan Type (Descending)">
                                    <?php else : ?>
                                        <img src="<?= base_url('assets/img/sort-down-active.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('type-ASC')" data-bs-toggle="tooltip" data-bs-placement="top" title="Urutkan berdasarkan Type (Ascending)">
                                    <?php endif ?>
                                <?php else : ?>
                                    <img src="<?= base_url('assets/img/sort-default.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('type-ASC')" data-bs-toggle="tooltip" data-bs-placement="top" title="Urutkan berdasarkan Type (Ascending)">
                                <?php endif ?>
                            </div>
                        </th>

                        <!-- D1 Column -->
                        <th scope="col" class="text-center">
                            <div class="d-flex align-items-center justify-content-center gap-1">
                                <span>D1</span>
                                <?php if ($sortKeyword[0] === 'D1') : ?>
                                    <?php if ($sortKeyword[1] === 'ASC') : ?>
                                        <img src="<?= base_url('assets/img/sort-up-active.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('D1-DESC')" data-bs-toggle="tooltip" data-bs-placement="top" title="Urutkan berdasarkan D1 (Descending)">
                                    <?php else : ?>
                                        <img src="<?= base_url('assets/img/sort-down-active.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('D1-ASC')" data-bs-toggle="tooltip" data-bs-placement="top" title="Urutkan berdasarkan D1 (Ascending)">
                                    <?php endif ?>
                                <?php else : ?>
                                    <img src="<?= base_url('assets/img/sort-default.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('D1-ASC')" data-bs-toggle="tooltip" data-bs-placement="top" title="Urutkan berdasarkan D1 (Ascending)">
                                <?php endif ?>
                            </div>
                        </th>

                        <!-- D2 Column -->
                        <th scope="col" class="text-center">
                            <div class="d-flex align-items-center justify-content-center gap-1">
                                <span>D2</span>
                                <?php if ($sortKeyword[0] === 'D2') : ?>
                                    <?php if ($sortKeyword[1] === 'ASC') : ?>
                                        <img src="<?= base_url('assets/img/sort-up-active.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('D2-DESC')" data-bs-toggle="tooltip" data-bs-placement="top" title="Urutkan berdasarkan D2 (Descending)">
                                    <?php else : ?>
                                        <img src="<?= base_url('assets/img/sort-down-active.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('D2-ASC')" data-bs-toggle="tooltip" data-bs-placement="top" title="Urutkan berdasarkan D2 (Ascending)">
                                    <?php endif ?>
                                <?php else : ?>
                                    <img src="<?= base_url('assets/img/sort-default.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('D2-ASC')" data-bs-toggle="tooltip" data-bs-placement="top" title="Urutkan berdasarkan D2 (Ascending)">
                                <?php endif ?>
                            </div>
                        </th>

                        <!-- D3 Column -->
                        <th scope="col" class="text-center">
                            <div class="d-flex align-items-center justify-content-center gap-1">
                                <span>D3</span>
                                <?php if ($sortKeyword[0] === 'D3') : ?>
                                    <?php if ($sortKeyword[1] === 'ASC') : ?>
                                        <img src="<?= base_url('assets/img/sort-up-active.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('D3-DESC')" data-bs-toggle="tooltip" data-bs-placement="top" title="Urutkan berdasarkan D3 (Descending)">
                                    <?php else : ?>
                                        <img src="<?= base_url('assets/img/sort-down-active.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('D3-ASC')" data-bs-toggle="tooltip" data-bs-placement="top" title="Urutkan berdasarkan D3 (Ascending)">
                                    <?php endif ?>
                                <?php else : ?>
                                    <img src="<?= base_url('assets/img/sort-default.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('D3-ASC')" data-bs-toggle="tooltip" data-bs-placement="top" title="Urutkan berdasarkan D3 (Ascending)">
                                <?php endif ?>
                            </div>
                        </th>

                        <!-- R(DRAT) Column -->
                        <th scope="col" class="text-center">
                            <div class="d-flex align-items-center justify-content-center gap-1">
                                <span>R(DRAT)</span>
                                <?php if ($sortKeyword[0] === 'R_DRAT') : ?>
                                    <?php if ($sortKeyword[1] === 'ASC') : ?>
                                        <img src="<?= base_url('assets/img/sort-up-active.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('R_DRAT-DESC')" data-bs-toggle="tooltip" data-bs-placement="top" title="Urutkan berdasarkan R(DRAT) (Descending)">
                                    <?php else : ?>
                                        <img src="<?= base_url('assets/img/sort-down-active.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('R_DRAT-ASC')" data-bs-toggle="tooltip" data-bs-placement="top" title="Urutkan berdasarkan R(DRAT) (Ascending)">
                                    <?php endif ?>
                                <?php else : ?>
                                    <img src="<?= base_url('assets/img/sort-default.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('R_DRAT-ASC')" data-bs-toggle="tooltip" data-bs-placement="top" title="Urutkan berdasarkan R(DRAT) (Ascending)">
                                <?php endif ?>
                            </div>
                        </th>

                        <!-- Edit Column -->
                        <th scope="col" class="text-center pe-lg-5 pe-4">Edit</th>
                    </tr>
                </thead>

                <!-- Table Body -->
                <tbody>
                    <?php foreach ($fittings as $fitting) : ?>
                        <tr>
                            <th scope="row" class="text-center ps-lg-5 ps-4"><?= $fitting['fitting_id']; ?></th>
                            <td class="text-center"><?= $fitting['type']; ?></td>
                            <td class="text-center"><?= $fitting['D1']; ?></td>
                            <td class="text-center"><?= $fitting['D2']; ?></td>
                            <td class="text-center"><?= $fitting['D3']; ?></td>
                            <td class="text-center"><?= $fitting['R_DRAT']; ?></td>
                            <td class="text-center pe-lg-5 pe-4">
                                <a href="<?= site_url('fitting/edit/' . urlencode($fitting['fitting_id'])); ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit fitting">
                                    <img src="<?= base_url('assets/img/edit.png'); ?>" alt="edit" class="action-button">
                                </a>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>

        <!-- Card Footer with Pagination and Controls -->
        <div class="card-footer bg-white border-0 px-lg-5 px-4 py-3">
            <div class="d-flex flex-column flex-lg-row align-items-center justify-content-between gap-3">
                <!-- Record Count Display -->
                <div class="text-muted">
                    Menampilkan <strong><?= count($fittings); ?> dari <?= $total_rows; ?></strong> fitting
                </div>

                <!-- Pagination Links -->
                <?= $pagination['links']; ?>

                <!-- Action Buttons -->
                <div class="d-flex gap-2">
                    <form method="post" action="">
                        <input type="hidden" name="clear_all" value="1">
                        <button type="submit" class="btn btn-outline-warning btn-sm rounded-pill">Reset Filter</button>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Filter Modal -->
<div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="filterModalLabel">Filter Fitting</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="filter-type" class="form-label">Type</label>
                        <select class="form-select" name="filter[type][]" id="filter-type" multiple>
                            <?php foreach ($type_options as $type) : ?>
                                <?php $selected = (!empty($filterKeyword['type']) && in_array($type, (array)$filterKeyword['type'])) ? 'selected' : ''; ?>
                                <option value="<?= htmlspecialchars($type, ENT_QUOTES, 'UTF-8'); ?>" <?= $selected; ?>><?= htmlspecialchars($type, ENT_QUOTES, 'UTF-8'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill">Terapkan Filter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadModalLabel">Upload Data Fitting</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <h6>Petunjuk Upload:</h6>
                    <ul class="mb-0">
                        <li>File harus berformat Excel (.xlsx atau .xls)</li>
                        <li>Gunakan template yang telah disediakan</li>
                        <li>Kolom yang harus diisi:
                            <ol>
                                <li>Type: Type fitting (maksimal 15 karakter)</li>
                                <li>D1: Dimensi D1 (angka positif)</li>
                                <li>D2: Dimensi D2 (angka positif)</li>
                                <li>D3: Dimensi D3 (angka positif)</li>
                                <li>R(DRAT): Ukuran drat (maksimal 20 karakter)</li>
                            </ol>
                        </li>
                        <li>Pastikan tidak ada kombinasi yang duplikat</li>
                    </ul>
                </div>
                <form id="uploadForm" action="" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="formFile" class="form-label">Pilih File Excel</label>
                        <input class="form-control" type="file" id="formFile" name="file" accept=".xlsx,.xls" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                <button type="submit" form="uploadForm" class="btn btn-success rounded-pill" id="uploadBtn">Upload Data</button>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        function applyFittingFilters() {
            const typeFilter = document.getElementById('type-filter');
            if (typeFilter && typeFilter.value) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '';

                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'filter[type][]';
                input.value = typeFilter.value;
                form.appendChild(input);

                document.body.appendChild(form);
                form.submit();
            }
        }

        // Type filter handler
        const typeFilter = document.getElementById('type-filter');
        if (typeFilter) {
            typeFilter.addEventListener('change', applyFittingFilters);
        }

        // Sort function
        window.sortTable = function(sortValue) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '';

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'sort';
            input.value = sortValue;
            form.appendChild(input);

            document.body.appendChild(form);
            form.submit();
        };

        // Enable tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    })();
</script>