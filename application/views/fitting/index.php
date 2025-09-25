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
            <!-- Kembali Button above Page Title -->
            <div class="col-12 col-lg-6">
                <div class="mb-2">
                    <a href="<?= site_url('fitting/type'); ?>" class="btn btn-secondary rounded-pill px-4">Kembali ke Pilih Type</a>
                </div>
                <h3 class="m-0">Data Fitting</h3>
            </div>

            <!-- Search Form + Add Fitting -->
            <div class="col-12 col-lg-6">
                <div class="d-flex gap-2 align-items-center">
                    <!-- Search + Filters Form -->
                    <form action="" method="post" class="flex-grow-1" id="search-form">
                        <div class="input-group">
                            <!-- make the input area a positioned container so absolute children are anchored inside it -->
                            <div class="position-relative flex-grow-1">
                                <input type="text" class="form-control rounded-start-pill pe-5" placeholder="Cari berdasarkan ID, type, D1, D2, D3, drat..." name="keyword" value="<?= $searchKeyword ?>" id="search-bar" onkeyup="displayClear()" autocomplete="off">
                                <img src="<?= base_url('assets/img/delete.png'); ?>" alt="delete" class="action-button clear-button" id="clear-button" onclick="clearKeyword()">
                            </div>

                            <input type="hidden" name="find" value="1">
                            <button class="btn btn-secondary rounded-end-pill px-4" type="submit">Cari</button>
                        </div>

                        <div class="mt-2 d-flex gap-2">
                            <!-- Type Filter -->
                            <select class="form-select form-select-sm" id="type-filter" aria-label="Filter Type">
                                <option value="">Semua Type</option>
                                <?php foreach (($type_options ?? []) as $type) : ?>
                                    <?php $selected = (!empty($filterKeyword['type']) && in_array($type, (array)$filterKeyword['type'])) ? 'selected' : ''; ?>
                                    <option value="<?= $type; ?>" <?= $selected; ?>><?= $type; ?></option>
                                <?php endforeach; ?>
                            </select>

                            <!-- Subtype Filter -->
                            <select class="form-select form-select-sm" id="subtype-filter" aria-label="Filter Subtype">
                                <option value="">Semua Subtype</option>
                                <?php foreach (($subtype_options ?? []) as $subtype) : ?>
                                    <?php $selected = (!empty($filterKeyword['subtype']) && in_array($subtype, (array)$filterKeyword['subtype'])) ? 'selected' : ''; ?>
                                    <option value="<?= $subtype; ?>" <?= $selected; ?>><?= $subtype; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>

                    <!-- Add Fitting Button -->
                    <a href="<?= site_url('fitting/add'); ?>" class="btn btn-primary rounded-pill px-4" type="button">Tambah</a>
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
                                        <img src="<?= base_url('assets/img/sort-asc.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('fitting_id-DESC')" data-bs-toggle="tooltip" data-bs-placement="top" title="Urutkan berdasarkan ID (Descending)">
                                    <?php else : ?>
                                        <img src="<?= base_url('assets/img/sort-desc.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('')" data-bs-toggle="tooltip" data-bs-placement="top" title="Reset urutan ID">
                                    <?php endif ?>
                                <?php else : ?>
                                    <img src="<?= base_url('assets/img/sort-default.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('fitting_id-ASC')" data-bs-toggle="tooltip" data-bs-placement="top" title="Urutkan berdasarkan ID (Ascending)">
                                <?php endif ?>
                            </div>
                        </th>

                        <!-- Type Column -->
                        <th scope="col" class="text-center">
                            <div class="d-flex align-items-center justify-content-center gap-1">
                                <span>Type</span>
                                <?php if ($sortKeyword[0] === 'type') : ?>
                                    <?php if ($sortKeyword[1] === 'ASC') : ?>
                                        <img src="<?= base_url('assets/img/sort-asc.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('type-DESC')" data-bs-toggle="tooltip" data-bs-placement="top" title="Urutkan berdasarkan Type (Descending)">
                                    <?php else : ?>
                                        <img src="<?= base_url('assets/img/sort-desc.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('')" data-bs-toggle="tooltip" data-bs-placement="top" title="Reset urutan Type">
                                    <?php endif ?>
                                <?php else : ?>
                                    <img src="<?= base_url('assets/img/sort-default.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('type-ASC')" data-bs-toggle="tooltip" data-bs-placement="top" title="Urutkan berdasarkan Type (Ascending)">
                                <?php endif ?>
                            </div>
                        </th>

                        <!-- Subtype Column -->
                        <th scope="col" class="text-center">
                            <div class="d-flex align-items-center justify-content-center gap-1">
                                <span>Subtype</span>
                                <?php if ($sortKeyword[0] === 'subtype') : ?>
                                    <?php if ($sortKeyword[1] === 'ASC') : ?>
                                        <img src="<?= base_url('assets/img/sort-asc.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('subtype-DESC')" data-bs-toggle="tooltip" data-bs-placement="top" title="Urutkan berdasarkan Subtype (Descending)">
                                    <?php else : ?>
                                        <img src="<?= base_url('assets/img/sort-desc.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('')" data-bs-toggle="tooltip" data-bs-placement="top" title="Reset urutan Subtype">
                                    <?php endif ?>
                                <?php else : ?>
                                    <img src="<?= base_url('assets/img/sort-default.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('subtype-ASC')" data-bs-toggle="tooltip" data-bs-placement="top" title="Urutkan berdasarkan Subtype (Ascending)">
                                <?php endif ?>
                            </div>
                        </th>

                        <!-- D1 Column -->
                        <th scope="col" class="text-center">
                            <div class="d-flex align-items-center justify-content-center gap-1">
                                <span>D1</span>
                                <?php if ($sortKeyword[0] === 'D1') : ?>
                                    <?php if ($sortKeyword[1] === 'ASC') : ?>
                                        <img src="<?= base_url('assets/img/sort-asc.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('D1-DESC')" data-bs-toggle="tooltip" data-bs-placement="top" title="Urutkan berdasarkan D1 (Descending)">
                                    <?php else : ?>
                                        <img src="<?= base_url('assets/img/sort-desc.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('')" data-bs-toggle="tooltip" data-bs-placement="top" title="Reset urutan D1">
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
                                        <img src="<?= base_url('assets/img/sort-asc.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('D2-DESC')" data-bs-toggle="tooltip" data-bs-placement="top" title="Urutkan berdasarkan D2 (Descending)">
                                    <?php else : ?>
                                        <img src="<?= base_url('assets/img/sort-desc.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('')" data-bs-toggle="tooltip" data-bs-placement="top" title="Reset urutan D2">
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
                                        <img src="<?= base_url('assets/img/sort-asc.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('D3-DESC')" data-bs-toggle="tooltip" data-bs-placement="top" title="Urutkan berdasarkan D3 (Descending)">
                                    <?php else : ?>
                                        <img src="<?= base_url('assets/img/sort-desc.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('')" data-bs-toggle="tooltip" data-bs-placement="top" title="Reset urutan D3">
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
                                        <img src="<?= base_url('assets/img/sort-asc.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('R_DRAT-DESC')" data-bs-toggle="tooltip" data-bs-placement="top" title="Urutkan berdasarkan R(DRAT) (Descending)">
                                    <?php else : ?>
                                        <img src="<?= base_url('assets/img/sort-desc.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('')" data-bs-toggle="tooltip" data-bs-placement="top" title="Reset urutan R(DRAT)">
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
                            <td class="text-center"><?= $fitting['subtype'] ?? 'N/A'; ?></td>
                            <td class="text-center"><?= !empty($fitting['D1']) ? $fitting['D1'] : '-'; ?></td>
                            <td class="text-center"><?= !empty($fitting['D2']) ? $fitting['D2'] : '-'; ?></td>
                            <td class="text-center"><?= !empty($fitting['D3']) ? $fitting['D3'] : '-'; ?></td>
                            <td class="text-center"><?= !empty($fitting['R_DRAT']) ? $fitting['R_DRAT'] : '-'; ?></td>
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
                <div class="btn-group">
                    <!-- Reset All Filters -->
                    <?php if ($hasFilters) : ?>
                        <form action="" method="post" class="d-inline">
                            <input type="hidden" name="reset" value="1">
                            <button type="submit" class="btn btn-outline-secondary rounded-start-pill">Reset Filter</button>
                        </form>
                    <?php endif; ?>

                    <!-- Download Button -->
                    <a href="<?= site_url('fitting/download'); ?>" class="btn btn-primary <?= $hasFilters ? '' : 'rounded-start-pill' ?>">Download</a>

                    <!-- Upload Modal Trigger -->
                    <button type="button" class="btn btn-primary rounded-end-pill" data-bs-toggle="modal" data-bs-target="#uploadModal">
                        Upload
                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?>
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
                    <strong>Ketentuan Upload:</strong>
                    <ul class="mb-0 mt-2">
                        <li>WAJIB menggunakan template yang sudah disediakan.</li>
                        <li>Download template <a href="<?= site_url('fitting/template'); ?>">disini</a>.</li>
                        <li>Ketentuan pengisian tabel:
                            <ol>
                                <li>Fitting ID akan dibuat otomatis berdasarkan field yang diisi.</li>
                                <li>Type wajib diisi, maksimal 30 karakter, akan otomatis diformat menjadi huruf besar.</li>
                                <li>Minimal satu field dimensi (D1, D2, D3, atau R(DRAT)) harus diisi.</li>
                                <li>D1, D2, D3 jika diisi harus berupa angka positif.</li>
                                <li>R(DRAT) jika diisi maksimal 20 karakter.</li>
                                <li>Kombinasi field yang diisi harus menghasilkan ID yang unik.</li>
                            </ol>
                        </li>
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
            const type = document.getElementById('type-filter')?.value || '';
            const subtype = document.getElementById('subtype-filter')?.value || '';

            const filterObj = {};
            if (type) filterObj.type = [type];
            if (subtype) filterObj.subtype = [subtype];

            // Build transient POST form
            const form = document.createElement('form');
            form.method = 'POST';
            form.style.display = 'none';

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'filter';
            input.value = JSON.stringify(filterObj);
            form.appendChild(input);

            document.body.appendChild(form);
            form.submit();
        }

        const typeEl = document.getElementById('type-filter');
        const subtypeEl = document.getElementById('subtype-filter');

        if (typeEl) typeEl.addEventListener('change', applyFittingFilters);
        if (subtypeEl) subtypeEl.addEventListener('change', applyFittingFilters);
    })();
</script>