<?php if ($this->session->flashdata('action')) : ?>
    <!-- Flash Notification -->
    <div class="alert alert-<?= $this->session->flashdata('action')[0]; ?> alert-dismissible fade show cust-notification" id="notification" role="alert">
        <?= $this->session->flashdata('action')[1]; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- User Info Card -->
<div class="card mx-auto rounded-5 shadow border-0 mb-3" style="margin-top: 5rem; width: 95%;">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1">History Aktivitas User - Storage System</h5>
                <h6 class="text-muted mb-0">NIK: <?= $user['nik']; ?> | Nama: <?= $user['name']; ?> | Level: <?= $user['user_level']; ?></h6>
            </div>
            <a href="<?= site_url('user'); ?>" class="btn btn-secondary rounded-pill">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>
</div>

<!-- History Data Card -->
<div class="card mx-auto rounded-5 shadow border-0 table-responsive mb-5" style="max-width: 95%;">
    <!-- Card Header with Title and Search -->
    <div class="card-header bg-white border-bottom px-lg-5 px-4 py-4">
        <div class="row g-3 align-items-center">
            <!-- Page Title -->
            <div class="col-12 col-lg-6">
                <h3 class="m-0">History Aktivitas Storage</h3>
            </div>

            <!-- Search Form -->
            <div class="col-12 col-lg-6">
                <form action="" method="post" class="flex-grow-1 position-relative" id="search-form">
                    <div class="input-group">
                        <input type="text" class="form-control rounded-start-pill pe-5"
                            placeholder="Cari berdasarkan ID penyimpanan, kategori, lokasi, project, atau catatan..."
                            name="keyword"
                            value="<?= $this->session->userdata('keyword') ?>"
                            id="search-bar"
                            onkeyup="displayClear()"
                            autocomplete="off">
                        <input type="hidden" name="find" value="1">
                        <button class="btn btn-primary rounded-end-pill px-4" type="submit">Cari</button>
                    </div>
                    <img src="<?= base_url('assets/img/delete.png'); ?>"
                        alt="delete"
                        class="action-button clear-button top-50 translate-middle-y"
                        id="clear-button"
                        onclick="clearKeyword()"
                        style="right: 6.5rem;">
                </form>
            </div>
        </div>
    </div>

    <?php if (!empty($transactions)) : ?>
        <!-- Data Table Container -->
        <div class="card-body p-0 table-responsive">
            <table class="table table-borderless table-hover table-striped mb-0">
                <!-- Table Header -->
                <thead>
                    <tr>
                        <!-- Storing ID Column -->
                        <th scope="col" class="text-center ps-lg-5 ps-4">
                            <div class="d-flex align-items-center justify-content-center gap-1">
                                <span>ID Penyimpanan</span>
                            </div>
                        </th>

                        <!-- DateTime Column -->
                        <th scope="col" class="text-center">
                            <div class="d-flex align-items-center justify-content-center gap-1">
                                <span>Waktu</span>
                            </div>
                        </th>

                        <!-- Action Column with Filter -->
                        <th scope="col" class="text-center">
                            <div class="d-flex align-items-center justify-content-center gap-1">
                                <span>Aksi</span>
                                <svg id="action-trigger"
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 48.03 48.6"
                                    width="14.31px"
                                    height="14.48px"
                                    class="cursor-pointer"
                                    onclick="hidePop('action')">
                                    <g>
                                        <path d="M0,40.31c0,4.59,3.69,8.28,8.28,8.28h31.47c4.59,0,8.28-3.69,8.28-8.28V8.28C48.03,3.69,44.34,0,39.75,0H8.28C3.69,0,0,3.69,0,8.28v32.03Z" fill="#f8f9fa"></path>
                                        <path d="M24.02,15.3c1.98,0,3.58,1.6,3.58,3.58s-1.6,3.58-3.58,3.58-3.58-1.6-3.58-3.58,1.6-3.58,3.58-3.58Z" fill="#6c757d"></path>
                                        <path d="M24.02,20.44c1.98,0,3.58,1.6,3.58,3.58s-1.6,3.58-3.58,3.58-3.58-1.6-3.58-3.58,1.6-3.58,3.58-3.58Z" fill="#6c757d"></path>
                                        <path d="M24.02,25.58c1.98,0,3.58,1.6,3.58,3.58s-1.6,3.58-3.58,3.58-3.58-1.6-3.58-3.58,1.6-3.58,3.58-3.58Z" fill="#6c757d"></path>
                                    </g>
                                </svg>
                            </div>
                        </th>

                        <!-- Amount Column -->
                        <th scope="col" class="text-center">
                            <div class="d-flex align-items-center justify-content-center gap-1">
                                <span>Jumlah</span>
                            </div>
                        </th>

                        <!-- Location Column with Filter -->
                        <th scope="col" class="text-center">
                            <div class="d-flex align-items-center justify-content-center gap-1">
                                <span>Lokasi</span>
                                <svg id="location-trigger"
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 48.03 48.6"
                                    width="14.31px"
                                    height="14.48px"
                                    class="cursor-pointer"
                                    onclick="hidePop('location')">
                                    <g>
                                        <path d="M0,40.31c0,4.59,3.69,8.28,8.28,8.28h31.47c4.59,0,8.28-3.69,8.28-8.28V8.28C48.03,3.69,44.34,0,39.75,0H8.28C3.69,0,0,3.69,0,8.28v32.03Z" fill="#f8f9fa"></path>
                                        <path d="M24.02,15.3c1.98,0,3.58,1.6,3.58,3.58s-1.6,3.58-3.58,3.58-3.58-1.6-3.58-3.58,1.6-3.58,3.58-3.58Z" fill="#6c757d"></path>
                                        <path d="M24.02,20.44c1.98,0,3.58,1.6,3.58,3.58s-1.6,3.58-3.58,3.58-3.58-1.6-3.58-3.58,1.6-3.58,3.58-3.58Z" fill="#6c757d"></path>
                                        <path d="M24.02,25.58c1.98,0,3.58,1.6,3.58,3.58s-1.6,3.58-3.58,3.58-3.58-1.6-3.58-3.58,1.6-3.58,3.58-3.58Z" fill="#6c757d"></path>
                                    </g>
                                </svg>
                            </div>
                        </th>

                        <!-- Category Column with Filter -->
                        <th scope="col" class="text-center">
                            <div class="d-flex align-items-center justify-content-center gap-1">
                                <span>Kategori</span>
                                <svg id="category-trigger"
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 48.03 48.6"
                                    width="14.31px"
                                    height="14.48px"
                                    class="cursor-pointer"
                                    onclick="hidePop('category')">
                                    <g>
                                        <path d="M0,40.31c0,4.59,3.69,8.28,8.28,8.28h31.47c4.59,0,8.28-3.69,8.28-8.28V8.28C48.03,3.69,44.34,0,39.75,0H8.28C3.69,0,0,3.69,0,8.28v32.03Z" fill="#f8f9fa"></path>
                                        <path d="M24.02,15.3c1.98,0,3.58,1.6,3.58,3.58s-1.6,3.58-3.58,3.58-3.58-1.6-3.58-3.58,1.6-3.58,3.58-3.58Z" fill="#6c757d"></path>
                                        <path d="M24.02,20.44c1.98,0,3.58,1.6,3.58,3.58s-1.6,3.58-3.58,3.58-3.58-1.6-3.58-3.58,1.6-3.58,3.58-3.58Z" fill="#6c757d"></path>
                                        <path d="M24.02,25.58c1.98,0,3.58,1.6,3.58,3.58s-1.6,3.58-3.58,3.58-3.58-1.6-3.58-3.58,1.6-3.58,3.58-3.58Z" fill="#6c757d"></path>
                                    </g>
                                </svg>
                            </div>
                        </th>

                        <!-- Type ID Column -->
                        <th scope="col" class="text-center">
                            <div class="d-flex align-items-center justify-content-center gap-1">
                                <span>ID Tipe</span>
                            </div>
                        </th>

                        <!-- Project/Batch Column -->
                        <th scope="col" class="text-center">
                            <div class="d-flex align-items-center justify-content-center gap-1">
                                <span>Project/Batch</span>
                            </div>
                        </th>

                        <!-- Notes Column -->
                        <th scope="col" class="text-center pe-lg-5 pe-4">
                            <div class="d-flex align-items-center justify-content-center gap-1">
                                <span>Catatan</span>
                            </div>
                        </th>
                    </tr>
                </thead>

                <!-- Table Body -->
                <tbody class="table-group-divider">
                    <?php foreach ($transactions as $record) : ?>
                        <tr>
                            <th scope="row" class="text-center ps-lg-5 ps-4">
                                <code><?= $record['storing_id']; ?></code>
                            </th>
                            <td class="text-center">
                                <?= date('d M Y, H:i:s', strtotime($record['datetime'])); ?>
                            </td>
                            <td class="text-center">
                                <?php if ($record['action'] === 'take') : ?>
                                    <span class="badge rounded-pill text-bg-warning">AMBIL</span>
                                <?php elseif ($record['action'] === 'store') : ?>
                                    <span class="badge rounded-pill text-bg-success">SIMPAN</span>
                                <?php else : ?>
                                    <span class="badge rounded-pill text-bg-secondary"><?= ucfirst($record['action']); ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info"><?= isset($record['amount']) ? (int)$record['amount'] : 1; ?></span>
                            </td>
                            <td class="text-center">
                                <a href="<?= site_url('storage/location/' . urlencode($record['location_id'])); ?>"
                                    class="badge bg-secondary text-decoration-none"
                                    title="Lihat detail lokasi">
                                    <?= $record['location_id']; ?>
                                </a>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary"><?= $record['category']; ?></span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex align-items-center justify-content-center">
                                    <?= $record['type_id']; ?>
                                    <?php if (isset($record['comment']) && $record['comment'] === 'PROJECT'): ?>
                                        <i class="fas fa-project-diagram text-primary ms-2" title="Barang Project"></i>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="text-center">
                                <?php if (!empty($record['batch_id'])): ?>
                                    <div class="d-flex flex-column align-items-center">
                                        <small class="badge bg-success mb-1" title="Batch ID">
                                            <?= htmlspecialchars($record['batch_id']); ?>
                                        </small>
                                        <?php if (!empty($record['project_name'])): ?>
                                            <small class="text-primary fw-bold" title="Project: <?= htmlspecialchars($record['project_name']); ?>">
                                                <?= strlen($record['project_name']) > 15
                                                    ? substr(htmlspecialchars($record['project_name']), 0, 15) . '...'
                                                    : htmlspecialchars($record['project_name']); ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center pe-lg-5 pe-4">
                                <?php if ($record['note']): ?>
                                    <span class="text-muted" title="<?= htmlspecialchars($record['note']); ?>">
                                        <?= strlen($record['note']) > 20
                                            ? substr(htmlspecialchars($record['note']), 0, 20) . '...'
                                            : htmlspecialchars($record['note']); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
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
                    Menampilkan <strong><?= $display; ?></strong>
                </div>

                <!-- Pagination Links -->
                <?= $pagination_links; ?>

                <!-- Reset Filter Button -->
                <div class="btn-group">
                    <form action="" method="post">
                        <input type="hidden" name="reset" value="1">
                        <button type="submit" class="btn btn-outline-secondary rounded-pill"
                            data-bs-toggle="tooltip"
                            data-bs-placement="top"
                            title="Reset semua filter">Reset</button>
                    </form>
                </div>
            </div>
        </div>
    <?php else : ?>
        <!-- No Data Alert -->
        <div class="alert alert-warning m-4" role="alert">
            <h5 class="alert-heading">Tidak Ada Data</h5>
            <?php if ($hasFilters): ?>
                Tidak ada transaksi storage yang sesuai dengan kriteria pencarian atau filter. Coba sesuaikan kata kunci atau reset filter.
            <?php else: ?>
                Pengguna ini belum melakukan transaksi storage.
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Hidden Filter Forms -->
<div hidden>
    <!-- Action Filter Form -->
    <form action="" method="post" id="action-filter">
        <p class="text-muted small mb-2">Pilih aksi untuk filter:</p>
        <?php if (!empty($actions)): ?>
            <?php foreach ($actions as $action) : ?>
                <div class="form-check">
                    <input class="form-check-input"
                        id="check-action-<?= strtolower($action); ?>"
                        type="checkbox"
                        name="filter-action[]"
                        value="<?= $action; ?>"
                        <?= ($this->session->userdata('filter') && isset($this->session->userdata('filter')['action']) && in_array($action, $this->session->userdata('filter')['action'])) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="check-action-<?= strtolower($action); ?>">
                        <?= $action === 'take' ? 'AMBIL' : ($action === 'store' ? 'SIMPAN' : ucfirst($action)); ?>
                    </label>
                </div>
            <?php endforeach ?>
        <?php else: ?>
            <p class="text-muted small">Tidak ada aksi tersedia untuk filter.</p>
        <?php endif; ?>
        <input type="hidden" name="action" value="1">
        <div class="d-flex justify-content-between mt-3">
            <button type="button" class="btn btn-secondary btn-sm rounded-pill" onclick="resetFilter('action-filter')">Reset</button>
            <button type="submit" class="btn btn-primary btn-sm rounded-pill">Apply</button>
        </div>
    </form>

    <!-- Location Filter Form -->
    <form action="" method="post" id="location-filter">
        <p class="text-muted small mb-2">Pilih lokasi untuk filter:</p>
        <?php if (!empty($locations)): ?>
            <?php foreach ($locations as $location) : ?>
                <div class="form-check">
                    <input class="form-check-input"
                        id="check-location-<?= strtolower(str_replace('.', '-', $location)); ?>"
                        type="checkbox"
                        name="filter-location[]"
                        value="<?= $location; ?>"
                        <?= ($this->session->userdata('filter') && isset($this->session->userdata('filter')['location_id']) && in_array($location, $this->session->userdata('filter')['location_id'])) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="check-location-<?= strtolower(str_replace('.', '-', $location)); ?>">
                        Lokasi <?= $location; ?>
                    </label>
                </div>
            <?php endforeach ?>
        <?php else: ?>
            <p class="text-muted small">Tidak ada lokasi tersedia untuk filter.</p>
        <?php endif; ?>
        <input type="hidden" name="location" value="1">
        <div class="d-flex justify-content-between mt-3">
            <button type="button" class="btn btn-secondary btn-sm rounded-pill" onclick="resetFilter('location-filter')">Reset</button>
            <button type="submit" class="btn btn-primary btn-sm rounded-pill">Apply</button>
        </div>
    </form>

    <!-- Category Filter Form -->
    <form action="" method="post" id="category-filter">
        <p class="text-muted small mb-2">Pilih kategori untuk filter:</p>
        <?php if (!empty($categories)): ?>
            <?php foreach ($categories as $category) : ?>
                <div class="form-check">
                    <input class="form-check-input"
                        id="check-category-<?= strtolower($category); ?>"
                        type="checkbox"
                        name="filter-category[]"
                        value="<?= $category; ?>"
                        <?= ($this->session->userdata('filter') && isset($this->session->userdata('filter')['category']) && in_array($category, $this->session->userdata('filter')['category'])) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="check-category-<?= strtolower($category); ?>">
                        <?= ucfirst($category); ?>
                    </label>
                </div>
            <?php endforeach ?>
        <?php else: ?>
            <p class="text-muted small">Tidak ada kategori tersedia untuk filter.</p>
        <?php endif; ?>
        <input type="hidden" name="category" value="1">
        <div class="d-flex justify-content-between mt-3">
            <button type="button" class="btn btn-secondary btn-sm rounded-pill" onclick="resetFilter('category-filter')">Reset</button>
            <button type="submit" class="btn btn-primary btn-sm rounded-pill">Apply</button>
        </div>
    </form>
</div>

<script>
    // Copy notification logic from ASRS system
    function displayClear() {
        const searchInput = document.getElementById('search-bar');
        const clearButton = document.getElementById('clear-button');

        if (searchInput.value.trim() !== '') {
            clearButton.style.display = 'block';
        } else {
            clearButton.style.display = 'none';
        }
    }

    function clearKeyword() {
        document.getElementById('search-bar').value = '';
        document.getElementById('clear-button').style.display = 'none';
        document.getElementById('search-form').submit();
    }

    function resetFilter(formId) {
        const form = document.getElementById(formId);
        const checkboxes = form.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(checkbox => checkbox.checked = false);
    }

    function hidePop(filterId) {
        // Simple popover logic - you can enhance this with Bootstrap popovers
        const form = document.getElementById(filterId + '-filter');
        if (form.style.display === 'none' || form.style.display === '') {
            // Hide all other filters first
            ['action-filter', 'location-filter', 'category-filter'].forEach(id => {
                if (id !== filterId + '-filter') {
                    document.getElementById(id).style.display = 'none';
                }
            });
            form.style.display = 'block';
            form.style.position = 'absolute';
            form.style.backgroundColor = 'white';
            form.style.border = '1px solid #ddd';
            form.style.borderRadius = '5px';
            form.style.padding = '15px';
            form.style.boxShadow = '0 2px 10px rgba(0,0,0,0.1)';
            form.style.zIndex = '1000';
            form.style.minWidth = '200px';
        } else {
            form.style.display = 'none';
        }
    }

    // Hide filters when clicking outside
    document.addEventListener('click', function(event) {
        const filters = ['action-filter', 'location-filter', 'category-filter'];
        const triggers = ['action-trigger', 'location-trigger', 'category-trigger'];

        if (!triggers.some(id => document.getElementById(id).contains(event.target)) &&
            !filters.some(id => document.getElementById(id).contains(event.target))) {
            filters.forEach(id => document.getElementById(id).style.display = 'none');
        }
    });

    // Initialize clear button visibility
    document.addEventListener('DOMContentLoaded', displayClear);

    const popoverData = [{
            id: 'action',
            title: 'Filter by Action'
        },
        {
            id: 'location',
            title: 'Filter by Location'
        },
        {
            id: 'category',
            title: 'Filter by Category'
        }
    ];
</script>