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
                    <a href="<?= site_url('pneumatic/type'); ?>" class="btn btn-secondary rounded-pill px-4">Kembali ke Pilih Type</a>
                </div>
                <h3 class="m-0">Data Pneumatic</h3>
            </div>

            <!-- Search Form + Add Pneumatic -->
            <div class="col-12 col-lg-6">
                <div class="d-flex gap-2 align-items-center">
                    <!-- Search + Filters Form -->
                    <form action="" method="post" class="flex-grow-1" id="search-form">
                        <div class="input-group">
                            <!-- make the input area a positioned container so absolute children are anchored inside it -->
                            <div class="position-relative flex-grow-1">
                                <input type="text" class="form-control rounded-start-pill pe-5" placeholder="Cari berdasarkan ID, brand, type..." name="keyword" value="<?= $searchKeyword ?>" id="search-bar" onkeyup="displayClear()" autocomplete="off">
                                <img src="<?= base_url('assets/img/delete.png'); ?>" alt="delete" class="action-button clear-button" id="clear-button" onclick="clearKeyword()">
                            </div>

                            <input type="hidden" name="find" value="1">
                            <button class="btn btn-secondary rounded-end-pill px-4" type="submit">Cari</button>
                        </div>

                        <div class="mt-2 d-flex gap-2">
                            <!-- Brand Filter -->
                            <select class="form-select form-select-sm" id="brand-filter" aria-label="Filter Brand">
                                <option value="">Semua Brand</option>
                                <?php foreach (($brand_options ?? []) as $brand) : ?>
                                    <?php $selected = (!empty($filterKeyword['brand']) && in_array($brand, (array)$filterKeyword['brand'])) ? 'selected' : ''; ?>
                                    <option value="<?= $brand; ?>" <?= $selected; ?>><?= $brand; ?></option>
                                <?php endforeach; ?>
                            </select>

                            <!-- Type Filter -->
                            <select class="form-select form-select-sm" id="type-filter" aria-label="Filter Type">
                                <option value="">Semua Type</option>
                                <?php foreach (($type_options ?? []) as $type) : ?>
                                    <?php $selected = (!empty($filterKeyword['type']) && in_array($type, (array)$filterKeyword['type'])) ? 'selected' : ''; ?>
                                    <option value="<?= $type; ?>" <?= $selected; ?>><?= $type; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>

                    <!-- Add Pneumatic Button -->
                    <a href="<?= site_url('pneumatic/add'); ?>" class="btn btn-primary rounded-pill px-4" type="button">Tambah</a>
                </div>
            </div>
        </div>
    </div>

    <?php if (empty($pneumatics)) : ?>
        <!-- No Data Alert -->
        <div class="alert alert-warning m-4" role="alert">
            <h5 class="alert-heading">Tidak Ada Data</h5>
            <p>Pneumatic tidak ditemukan. Coba sesuaikan kata kunci pencarian atau filter Anda.</p>
        </div>
    <?php else : ?>
        <!-- Data Table Container -->
        <div class="card-body p-0 table-responsive">
            <table class="table table-borderless table-hover table-striped mb-0">
                <!-- Table Header -->
                <thead>
                    <tr>
                        <!-- Pneumatic ID Column -->
                        <th scope="col" class="text-center ps-lg-5 ps-4">
                            <div class="d-flex align-items-center justify-content-center gap-1">
                                <span>Pneumatic ID</span>
                                <?php if ($sortKeyword[0] === 'pneumatic_id') : ?>
                                    <?php if ($sortKeyword[1] === 'ASC') : ?>
                                        <img src="<?= base_url('assets/img/sort-asc.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('pneumatic_id-DESC')" data-bs-toggle="tooltip" data-bs-placement="top" title="Urutkan berdasarkan ID (Descending)">
                                    <?php else : ?>
                                        <img src="<?= base_url('assets/img/sort-desc.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('')" data-bs-toggle="tooltip" data-bs-placement="top" title="Reset urutan ID">
                                    <?php endif ?>
                                <?php else : ?>
                                    <img src="<?= base_url('assets/img/sort-default.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('pneumatic_id-ASC')" data-bs-toggle="tooltip" data-bs-placement="top" title="Urutkan berdasarkan ID (Ascending)">
                                <?php endif ?>
                            </div>
                        </th>

                        <!-- Brand Column -->
                        <th scope="col" class="text-center">
                            <div class="d-flex align-items-center justify-content-center gap-1">
                                <span>Brand</span>
                                <?php if ($sortKeyword[0] === 'brand') : ?>
                                    <?php if ($sortKeyword[1] === 'ASC') : ?>
                                        <img src="<?= base_url('assets/img/sort-asc.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('brand-DESC')" data-bs-toggle="tooltip" data-bs-placement="top" title="Urutkan berdasarkan Brand (Descending)">
                                    <?php else : ?>
                                        <img src="<?= base_url('assets/img/sort-desc.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('')" data-bs-toggle="tooltip" data-bs-placement="top" title="Reset urutan Brand">
                                    <?php endif ?>
                                <?php else : ?>
                                    <img src="<?= base_url('assets/img/sort-default.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('brand-ASC')" data-bs-toggle="tooltip" data-bs-placement="top" title="Urutkan berdasarkan Brand (Ascending)">
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

                        <!-- Bore Column -->
                        <th scope="col" class="text-center">
                            <div class="d-flex align-items-center justify-content-center gap-1">
                                <span>Bore</span>
                                <?php if ($sortKeyword[0] === 'bore') : ?>
                                    <?php if ($sortKeyword[1] === 'ASC') : ?>
                                        <img src="<?= base_url('assets/img/sort-asc.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('bore-DESC')" data-bs-toggle="tooltip" data-bs-placement="top" title="Urutkan berdasarkan Bore (Descending)">
                                    <?php else : ?>
                                        <img src="<?= base_url('assets/img/sort-desc.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('')" data-bs-toggle="tooltip" data-bs-placement="top" title="Reset urutan Bore">
                                    <?php endif ?>
                                <?php else : ?>
                                    <img src="<?= base_url('assets/img/sort-default.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('bore-ASC')" data-bs-toggle="tooltip" data-bs-placement="top" title="Urutkan berdasarkan Bore (Ascending)">
                                <?php endif ?>
                            </div>
                        </th>

                        <!-- Stroke Column -->
                        <th scope="col" class="text-center">
                            <div class="d-flex align-items-center justify-content-center gap-1">
                                <span>Stroke</span>
                                <?php if ($sortKeyword[0] === 'stroke') : ?>
                                    <?php if ($sortKeyword[1] === 'ASC') : ?>
                                        <img src="<?= base_url('assets/img/sort-asc.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('stroke-DESC')" data-bs-toggle="tooltip" data-bs-placement="top" title="Urutkan berdasarkan Stroke (Descending)">
                                    <?php else : ?>
                                        <img src="<?= base_url('assets/img/sort-desc.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('')" data-bs-toggle="tooltip" data-bs-placement="top" title="Reset urutan Stroke">
                                    <?php endif ?>
                                <?php else : ?>
                                    <img src="<?= base_url('assets/img/sort-default.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('stroke-ASC')" data-bs-toggle="tooltip" data-bs-placement="top" title="Urutkan berdasarkan Stroke (Ascending)">
                                <?php endif ?>
                            </div>
                        </th>

                        <!-- Edit Column -->
                        <th scope="col" class="text-center pe-lg-5 pe-4">Edit</th>
                    </tr>
                </thead>

                <!-- Table Body -->
                <tbody>
                    <?php foreach ($pneumatics as $pneumatic) : ?>
                        <tr>
                            <th scope="row" class="text-center ps-lg-5 ps-4"><?= $pneumatic['pneumatic_id']; ?></th>
                            <td class="text-center"><?= $pneumatic['brand']; ?></td>
                            <td class="text-center"><?= $pneumatic['type']; ?></td>
                            <td class="text-center"><?= $pneumatic['bore']; ?></td>
                            <td class="text-center"><?= $pneumatic['stroke']; ?></td>
                            <td class="text-center pe-lg-5 pe-4">
                                <a href="<?= site_url('pneumatic/edit/' . urlencode($pneumatic['pneumatic_id'])); ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit pneumatic">
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
                    Menampilkan <strong><?= count($pneumatics); ?> dari <?= $total_rows; ?></strong> pneumatic
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
                    <a href="<?= site_url('storage/store'); ?>" class="btn btn-success <?= $hasFilters ? '' : 'rounded-start-pill' ?>" title="Store Pneumatic Items">Store</a>
                    <a href="<?= site_url('storage/take'); ?>" class="btn btn-warning" title="Take Pneumatic Items">Take</a>

                    <!-- Download Button -->
                    <a href="<?= site_url('pneumatic/download'); ?>" class="btn btn-primary">Download</a>

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
                <h5 class="modal-title" id="uploadModalLabel">Upload Data Pneumatic</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <strong>Ketentuan Upload:</strong>
                    <ul class="mb-0 mt-2">
                        <li>WAJIB menggunakan template yang sudah disediakan.</li>
                        <li>Download template <a href="<?= site_url('pneumatic/template'); ?>">disini</a>.</li>
                        <li>Ketentuan pengisian tabel:
                            <ol>
                                <li>Pneumatic ID akan dibuat otomatis berdasarkan format: pnm-brand-type-bore-stroke.</li>
                                <li>Brand maksimal 15 karakter, akan otomatis diformat menjadi huruf kecil.</li>
                                <li>Type maksimal 5 karakter, akan otomatis diformat menjadi huruf kecil.</li>
                                <li>Bore dan Stroke harus berupa angka positif.</li>
                                <li>Kombinasi brand, type, bore, dan stroke harus unik.</li>
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
        function applyPneumaticFilters() {
            const brand = document.getElementById('brand-filter')?.value || '';
            const type = document.getElementById('type-filter')?.value || '';

            const filterObj = {};
            if (brand) filterObj.brand = [brand];
            if (type) filterObj.type = [type];

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

        const brandEl = document.getElementById('brand-filter');
        const typeEl = document.getElementById('type-filter');

        if (brandEl) brandEl.addEventListener('change', applyPneumaticFilters);
        if (typeEl) typeEl.addEventListener('change', applyPneumaticFilters);
    })();
</script>