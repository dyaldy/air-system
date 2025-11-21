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
                <h3 class="m-0">Data Manifold</h3>
            </div>

            <!-- Search Form + Add Manifold -->
            <div class="col-12 col-lg-6">
                <div class="d-flex gap-2 align-items-center">
                    <!-- Search + Filters Form -->
                    <form action="" method="post" class="flex-grow-1" id="search-form">
                        <div class="input-group">
                            <!-- make the input area a positioned container so absolute children are anchored inside it -->
                            <div class="position-relative flex-grow-1">
                                <input type="text" class="form-control rounded-start-pill pe-5" placeholder="Cari berdasarkan ID, block..." name="keyword" value="<?= $searchKeyword ?>" id="search-bar" onkeyup="displayClear()" autocomplete="off">
                                <img src="<?= base_url('assets/img/delete.png'); ?>" alt="delete" class="action-button clear-button" id="clear-button" onclick="clearKeyword()">
                            </div>

                            <input type="hidden" name="find" value="1">
                            <button class="btn btn-secondary rounded-end-pill px-4" type="submit">Cari</button>
                        </div>

                        <div class="mt-2 d-flex gap-2">
                            <!-- Block Filter -->
                            <select class="form-select form-select-sm" id="block-filter" aria-label="Filter Block">
                                <option value="">Semua Block</option>
                                <?php foreach (($block_options ?? []) as $block) : ?>
                                    <?php $selected = (!empty($filterKeyword['block']) && in_array($block, (array)$filterKeyword['block'])) ? 'selected' : ''; ?>
                                    <option value="<?= $block; ?>" <?= $selected; ?>><?= $block; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>

                    <!-- Add Manifold Button -->
                    <a href="<?= site_url('manifold/add'); ?>" class="btn btn-primary rounded-pill px-4" type="button">Tambah</a>
                </div>
            </div>
        </div>
    </div>

    <?php if (empty($manifolds)) : ?>
        <!-- No Data Alert -->
        <div class="alert alert-warning m-4" role="alert">
            <h5 class="alert-heading">Tidak Ada Data</h5>
            <p>Manifold tidak ditemukan. Coba sesuaikan kata kunci pencarian atau filter Anda.</p>
        </div>
    <?php else : ?>
        <!-- Data Table Container -->
        <div class="card-body p-0 table-responsive">
            <table class="table table-borderless table-hover table-striped mb-0">
                <!-- Table Header -->
                <thead>
                    <tr>
                        <!-- Manifold ID Column -->
                        <th scope="col" class="text-center ps-lg-5 ps-4">
                            <div class="d-flex align-items-center justify-content-center gap-1">
                                <span>Manifold ID</span>
                                <?php if ($sortKeyword[0] === 'manifold_id') : ?>
                                    <?php if ($sortKeyword[1] === 'ASC') : ?>
                                        <img src="<?= base_url('assets/img/sort-asc.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('manifold_id-DESC')" data-bs-toggle="tooltip" data-bs-placement="top" title="Urutkan berdasarkan ID (Descending)">
                                    <?php else : ?>
                                        <img src="<?= base_url('assets/img/sort-desc.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('')" data-bs-toggle="tooltip" data-bs-placement="top" title="Reset urutan ID">
                                    <?php endif ?>
                                <?php else : ?>
                                    <img src="<?= base_url('assets/img/sort-default.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('manifold_id-ASC')" data-bs-toggle="tooltip" data-bs-placement="top" title="Urutkan berdasarkan ID (Ascending)">
                                <?php endif ?>
                            </div>
                        </th>

                        <!-- Block Column -->
                        <th scope="col" class="text-center">
                            <div class="d-flex align-items-center justify-content-center gap-1">
                                <span>Block</span>
                                <?php if ($sortKeyword[0] === 'block') : ?>
                                    <?php if ($sortKeyword[1] === 'ASC') : ?>
                                        <img src="<?= base_url('assets/img/sort-asc.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('block-DESC')" data-bs-toggle="tooltip" data-bs-placement="top" title="Urutkan berdasarkan Block (Descending)">
                                    <?php else : ?>
                                        <img src="<?= base_url('assets/img/sort-desc.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('')" data-bs-toggle="tooltip" data-bs-placement="top" title="Reset urutan Block">
                                    <?php endif ?>
                                <?php else : ?>
                                    <img src="<?= base_url('assets/img/sort-default.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('block-ASC')" data-bs-toggle="tooltip" data-bs-placement="top" title="Urutkan berdasarkan Block (Ascending)">
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
                    <?php foreach ($manifolds as $manifold) : ?>
                        <tr>
                            <th scope="row" class="text-center ps-lg-5 ps-4"><?= $manifold['manifold_id']; ?></th>
                            <td class="text-center"><?= $manifold['block']; ?></td>
                            <td class="text-center"><?= $manifold['min_stock'] ? $manifold['min_stock'] : '-'; ?></td>
                            <td class="text-center pe-lg-5 pe-4">
                                <a href="<?= site_url('manifold/edit/' . urlencode($manifold['manifold_id'])); ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit manifold">
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
                    Menampilkan <strong><?= count($manifolds); ?> dari <?= $total_rows; ?></strong> manifold
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
                    <a href="<?= site_url('storage/store'); ?>" class="btn btn-success <?= $hasFilters ? '' : 'rounded-start-pill' ?>" title="Store Manifold Items">Store</a>
                    <a href="<?= site_url('storage/take'); ?>" class="btn btn-warning" title="Take Manifold Items">Take</a>

                    <!-- Download Buttons -->
                    <div class="btn-group" role="group">
                        <a href="<?= site_url('manifold/download'); ?>" class="btn btn-primary" title="Download CSV">Download CSV</a>
                        <a href="<?= site_url('manifold/downloadPDF'); ?>" class="btn btn-danger" title="Download PDF">Download PDF</a>
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
                <h5 class="modal-title" id="uploadModalLabel">Upload Data Manifold</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <strong>Ketentuan Upload:</strong>
                    <ul class="mb-0 mt-2">
                        <li>WAJIB menggunakan template yang sudah disediakan.</li>
                        <li>Download template <a href="<?= site_url('manifold/template'); ?>">disini</a>.</li>
                        <li>Ketentuan pengisian tabel:
                            <ol>
                                <li>Manifold ID akan dibuat otomatis berdasarkan format: mnf-block.</li>
                                <li>Block harus berupa angka positif.</li>
                                <li>Block harus unik.</li>
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
        function applyManifoldFilters() {
            const block = document.getElementById('block-filter')?.value || '';

            const filterObj = {};
            if (block) filterObj.block = [block];

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

        const blockEl = document.getElementById('block-filter');

        if (blockEl) blockEl.addEventListener('change', applyManifoldFilters);
    })();
</script>