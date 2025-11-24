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
                <h3 class="m-0">Data Hose</h3>
            </div>

            <!-- Search Form + Add Hose -->
            <div class="col-12 col-lg-6">
                <div class="d-flex gap-2 align-items-center">
                    <!-- Search + Filters Form -->
                    <form action="" method="post" class="flex-grow-1" id="search-form">
                        <div class="input-group">
                            <!-- make the input area a positioned container so absolute children are anchored inside it -->
                            <div class="position-relative flex-grow-1">
                                <input type="text" class="form-control rounded-start-pill pe-5" placeholder="Cari berdasarkan ID, diameter..." name="keyword" value="<?= $searchKeyword ?>" id="search-bar" onkeyup="displayClear()" autocomplete="off">
                                <img src="<?= base_url('assets/img/delete.png'); ?>" alt="delete" class="action-button clear-button" id="clear-button" onclick="clearKeyword()">
                            </div>

                            <input type="hidden" name="find" value="1">
                            <button class="btn btn-secondary rounded-end-pill px-4" type="submit">Cari</button>
                        </div>

                        <div class="mt-2 d-flex gap-2">
                            <!-- Diameter Filter -->
                            <select class="form-select form-select-sm" id="diameter-filter" aria-label="Filter Diameter">
                                <option value="">Semua Diameter</option>
                                <?php foreach (($diameter_options ?? []) as $diameter) : ?>
                                    <?php $selected = (!empty($filterKeyword['diameter']) && in_array($diameter, (array)$filterKeyword['diameter'])) ? 'selected' : ''; ?>
                                    <option value="<?= $diameter; ?>" <?= $selected; ?>><?= $diameter; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>

                    <!-- Add Hose Button -->
                    <a href="<?= site_url('hose/add'); ?>" class="btn btn-primary rounded-pill px-4" type="button">Tambah</a>
                </div>
            </div>
        </div>
    </div>

    <?php if (empty($hoses)) : ?>
        <!-- No Data Alert -->
        <div class="alert alert-warning m-4" role="alert">
            <h5 class="alert-heading">Tidak Ada Data</h5>
            <p>Hose tidak ditemukan. Coba sesuaikan kata kunci pencarian atau filter Anda.</p>
        </div>
    <?php else : ?>
        <!-- Data Table Container -->
        <div class="card-body p-0 table-responsive">
            <table class="table table-borderless table-hover table-striped mb-0">
                <!-- Table Header -->
                <thead>
                    <tr>
                        <!-- Hose ID Column -->
                        <th scope="col" class="text-center ps-lg-5 ps-4">
                            <div class="d-flex align-items-center justify-content-center gap-1">
                                <span>Hose ID</span>
                                <?php if ($sortKeyword[0] === 'hose_id') : ?>
                                    <?php if ($sortKeyword[1] === 'ASC') : ?>
                                        <img src="<?= base_url('assets/img/sort-asc.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('hose_id-DESC')" data-bs-toggle="tooltip" data-bs-placement="top" title="Urutkan berdasarkan ID (Descending)">
                                    <?php else : ?>
                                        <img src="<?= base_url('assets/img/sort-desc.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('')" data-bs-toggle="tooltip" data-bs-placement="top" title="Reset urutan ID">
                                    <?php endif ?>
                                <?php else : ?>
                                    <img src="<?= base_url('assets/img/sort-default.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('hose_id-ASC')" data-bs-toggle="tooltip" data-bs-placement="top" title="Urutkan berdasarkan ID (Ascending)">
                                <?php endif ?>
                            </div>
                        </th>

                        <!-- Diameter Column -->
                        <th scope="col" class="text-center">
                            <div class="d-flex align-items-center justify-content-center gap-1">
                                <span>Diameter</span>
                                <?php if ($sortKeyword[0] === 'diameter') : ?>
                                    <?php if ($sortKeyword[1] === 'ASC') : ?>
                                        <img src="<?= base_url('assets/img/sort-asc.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('diameter-DESC')" data-bs-toggle="tooltip" data-bs-placement="top" title="Urutkan berdasarkan Diameter (Descending)">
                                    <?php else : ?>
                                        <img src="<?= base_url('assets/img/sort-desc.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('')" data-bs-toggle="tooltip" data-bs-placement="top" title="Reset urutan Diameter">
                                    <?php endif ?>
                                <?php else : ?>
                                    <img src="<?= base_url('assets/img/sort-default.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('diameter-ASC')" data-bs-toggle="tooltip" data-bs-placement="top" title="Urutkan berdasarkan Diameter (Ascending)">
                                <?php endif ?>
                            </div>
                        </th>

                        <!-- Min Stock Column -->
                        <th scope="col" class="text-center">
                            <div class="d-flex align-items-center justify-content-center gap-1">
                                <span>Min Stock</span>
                                <?php if ($sortKeyword[0] === 'min_stock') : ?>
                                    <?php if ($sortKeyword[1] === 'ASC') : ?>
                                        <img src="<?= base_url('assets/img/sort-asc.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('min_stock-DESC')" data-bs-toggle="tooltip" data-bs-placement="top" title="Urutkan berdasarkan Min Stock (Descending)">
                                    <?php else : ?>
                                        <img src="<?= base_url('assets/img/sort-desc.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('')" data-bs-toggle="tooltip" data-bs-placement="top" title="Reset urutan Min Stock">
                                    <?php endif ?>
                                <?php else : ?>
                                    <img src="<?= base_url('assets/img/sort-default.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('min_stock-ASC')" data-bs-toggle="tooltip" data-bs-placement="top" title="Urutkan berdasarkan Min Stock (Ascending)">
                                <?php endif ?>
                            </div>
                        </th>

                        <!-- Edit Column -->
                        <th scope="col" class="text-center pe-lg-5 pe-4">Edit</th>
                    </tr>
                </thead>

                <!-- Table Body -->
                <tbody>
                    <?php foreach ($hoses as $hose) : ?>
                        <tr>
                            <th scope="row" class="text-center ps-lg-5 ps-4"><?= $hose['hose_id']; ?></th>
                            <td class="text-center"><?= $hose['diameter']; ?></td>
                            <td class="text-center"><?= $hose['min_stock'] ? $hose['min_stock'] : '-'; ?></td>
                            <td class="text-center pe-lg-5 pe-4">
                                <a href="<?= site_url('hose/edit/' . urlencode($hose['hose_id'])); ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit hose">
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
                    Menampilkan <strong><?= count($hoses); ?> dari <?= $total_rows; ?></strong> hose
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

                    <!-- Storage Links -->
                    <a href="<?= site_url('storage/store'); ?>" class="btn btn-success <?= $hasFilters ? '' : 'rounded-start-pill' ?>" title="Store Hose Items">Store</a>
                    <a href="<?= site_url('storage/take'); ?>" class="btn btn-warning" title="Take Hose Items">Take</a>

                    <!-- Download Buttons -->
                    <div class="btn-group" role="group">
                        <a href="<?= site_url('hose/download'); ?>" class="btn btn-primary" title="Download CSV">Download CSV</a>
                        <a href="<?= site_url('hose/downloadPDF'); ?>" class="btn btn-danger" title="Download PDF">Download PDF</a>
                    </div>

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
                <h5 class="modal-title" id="uploadModalLabel">Upload Data Hose</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <strong>Ketentuan Upload:</strong>
                    <ul class="mb-0 mt-2">
                        <li>WAJIB menggunakan template yang sudah disediakan.</li>
                        <li>Download template <a href="<?= site_url('hose/download_template'); ?>">disini</a>.</li>
                        <li>Ketentuan pengisian tabel:
                            <ol>
                                <li>Hose ID akan dibuat otomatis berdasarkan format: hose-diameter.</li>
                                <li>Diameter maksimal 20 karakter.</li>
                                <li>Diameter harus unik.</li>
                            </ol>
                        </li>
                    </ul>
                </div>
                <form id="uploadForm" action="" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="formFile" class="form-label">Pilih File CSV</label>
                        <input class="form-control" type="file" id="formFile" name="file" accept=".csv" required>
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
        function applyHoseFilters() {
            const diameter = document.getElementById('diameter-filter')?.value || '';

            const filterObj = {};
            if (diameter) filterObj.diameter = [diameter];

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

        const diameterEl = document.getElementById('diameter-filter');

        if (diameterEl) diameterEl.addEventListener('change', applyHoseFilters);
    })();
</script>