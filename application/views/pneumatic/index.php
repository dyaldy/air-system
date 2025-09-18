<?php if ($this->session->flashdata('action')) : ?>
    <!-- Flash Notification Alert -->
    <div class="alert alert-<?= $this->session->flashdata('action')[0]; ?> alert-dismissible fade show cust-notification" id="notification" role="alert">
        <?= $this->session->flashdata('action')[1]; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- Main Content Card -->
<div class="card mx-auto rounded-5 shadow border-0 table-responsive mb-5" style="margin-top: 5rem; max-width: 95%;">
    <!-- Card Header with Title and Search -->
    <div class="card-header bg-white border-bottom px-lg-5 px-4 py-4">
        <div class="row g-3 align-items-center">
            <!-- Page Title -->
            <div class="col-12 col-lg-6">
                <h3 class="m-0">Data Pneumatic</h3>
            </div>

            <!-- Search Form + Add Pneumatic -->
            <div class="col-12 col-lg-6">
                <div class="d-flex gap-2 align-items-center">
                    <!-- Search + Filters Form -->
                    <form action="" method="post" class="flex-grow-1 position-relative" id="search-form">
                        <div class="input-group">
                            <input type="text" class="form-control rounded-start-pill pe-5" placeholder="Cari berdasarkan ID, brand, type..." name="keyword" value="<?= $this->session->userdata('keyword') ?>" id="search-bar" onkeyup="displayClear()" autocomplete="off">
                            <input type="hidden" name="find" value="1">
                            <button class="btn btn-secondary rounded-end-pill px-4" type="submit">Cari</button>
                        </div>
                        <img src="<?= base_url('assets/img/delete.png'); ?>" alt="delete" class="action-button clear-button top-50 translate-middle-y" id="clear-button" onclick="clearKeyword()" style="right: 5.5rem;">

                        <div class="mt-2 d-flex gap-2">
                            <!-- Brand Filter -->
                            <select class="form-select form-select-sm" id="brand-filter" aria-label="Filter Brand">
                                <option value="">Semua Brand</option>
                                <?php foreach (($brand_options ?? []) as $brand) : ?>
                                    <?php $selected = (!empty($filter_keyword['brand']) && in_array($brand, (array)$filter_keyword['brand'])) ? 'selected' : ''; ?>
                                    <option value="<?= $brand; ?>" <?= $selected; ?>><?= $brand; ?></option>
                                <?php endforeach; ?>
                            </select>

                            <!-- Type Filter -->
                            <select class="form-select form-select-sm" id="type-filter" aria-label="Filter Type">
                                <option value="">Semua Type</option>
                                <?php foreach (($type_options ?? []) as $type) : ?>
                                    <?php $selected = (!empty($filter_keyword['type']) && in_array($type, (array)$filter_keyword['type'])) ? 'selected' : ''; ?>
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
                                <?php if (isset($sort_keyword) && strpos($sort_keyword, 'pneumatic_id') !== false) : ?>
                                    <?php if (strpos($sort_keyword, 'ASC') !== false) : ?>
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
                                <?php if (isset($sort_keyword) && strpos($sort_keyword, 'brand') !== false) : ?>
                                    <?php if (strpos($sort_keyword, 'ASC') !== false) : ?>
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
                                <?php if (isset($sort_keyword) && strpos($sort_keyword, 'type') !== false) : ?>
                                    <?php if (strpos($sort_keyword, 'ASC') !== false) : ?>
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
                                <?php if (isset($sort_keyword) && strpos($sort_keyword, 'bore') !== false) : ?>
                                    <?php if (strpos($sort_keyword, 'ASC') !== false) : ?>
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
                                <?php if (isset($sort_keyword) && strpos($sort_keyword, 'stroke') !== false) : ?>
                                    <?php if (strpos($sort_keyword, 'ASC') !== false) : ?>
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
                        <th scope="col" class="text-center">Edit</th>

                        <!-- Delete Column -->
                        <th scope="col" class="text-center pe-lg-5 pe-4">Delete</th>
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
                            <td class="text-center">
                                <a href="<?= site_url('pneumatic/edit/' . urlencode($pneumatic['pneumatic_id'])); ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit pneumatic">
                                    <img src="<?= base_url('assets/img/edit.png'); ?>" alt="edit" class="action-button">
                                </a>
                            </td>
                            <td class="text-center pe-lg-5 pe-4">
                                <a href="<?= site_url('pneumatic/delete/' . urlencode($pneumatic['pneumatic_id'])); ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus pneumatic ini?')" data-bs-toggle="tooltip" data-bs-placement="top" title="Hapus pneumatic">
                                    <img src="<?= base_url('assets/img/delete.png'); ?>" alt="delete" class="action-button">
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
                    <?php if (!empty($search_keyword) || !empty($filter_keyword) || !empty($sort_keyword)) : ?>
                        <form action="" method="post" class="d-inline">
                            <input type="hidden" name="clear" value="1">
                            <button type="submit" class="btn btn-outline-secondary rounded-start-pill">Reset Filter</button>
                        </form>
                    <?php endif; ?>

                    <!-- Download Button -->
                    <a href="<?= site_url('pneumatic/download'); ?>" class="btn btn-primary <?= (!empty($search_keyword) || !empty($filter_keyword) || !empty($sort_keyword)) ? '' : 'rounded-start-pill' ?>">Download</a>

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
                <form id="uploadForm" action="<?= site_url('pneumatic/upload'); ?>" method="POST" enctype="multipart/form-data">
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
    /**
     * Global Configuration Variables for existing JS files
     */
    window.notificationDuration = "10000";

    /**
     * Handle sorting functionality
     */
    function sortTable(sortKey) {
        const form = document.createElement('form');
        form.method = 'post';
        form.action = '';

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'sort';
        input.value = sortKey;

        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }

    /**
     * Clear search keyword functionality
     */
    function clearKeyword() {
        document.getElementById('search-bar').value = '';
        const form = document.createElement('form');
        form.method = 'post';
        form.action = '';

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'clear';
        input.value = '1';

        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }

    /**
     * Display clear button when search bar has content
     */
    function displayClear() {
        const searchBar = document.getElementById('search-bar');
        const clearButton = document.getElementById('clear-button');

        if (searchBar && clearButton) {
            clearButton.style.display = searchBar.value.length > 0 ? 'block' : 'none';
        }
    }

    /**
     * Handle upload form submission
     */
    (function() {
        const uploadBtn = document.getElementById('uploadBtn');
        const form = document.getElementById('uploadForm');
        const fileInput = document.getElementById('formFile');
        if (!uploadBtn || !form || !fileInput) return;
        uploadBtn.addEventListener('click', function(e) {
            if (fileInput.files.length === 0) {
                e.preventDefault();
                alert('Pilih file Excel terlebih dahulu!');
            }
        });
    })();

    // Initialize clear button display on page load
    document.addEventListener('DOMContentLoaded', function() {
        displayClear();
    });

    // Submit filter selections via POST (uses hidden form created on the fly)
    function applyFilters() {
        const brand = document.getElementById('brand-filter').value;
        const type = document.getElementById('type-filter').value;

        const form = document.createElement('form');
        form.method = 'post';
        form.action = '';

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'filter';
        // Build a JSON-encoded associative array for PHP to decode
        const filterObj = {};
        if (brand) filterObj.brand = [brand];
        if (type) filterObj.type = [type];
        input.value = JSON.stringify(filterObj);

        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }

    // Wire change events
    document.addEventListener('DOMContentLoaded', function() {
        const brandSelect = document.getElementById('brand-filter');
        const typeSelect = document.getElementById('type-filter');
        if (brandSelect) brandSelect.addEventListener('change', applyFilters);
        if (typeSelect) typeSelect.addEventListener('change', applyFilters);
    });
</script>