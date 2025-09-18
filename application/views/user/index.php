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
                <h3 class="m-0">Data Pengguna</h3>
            </div>

            <!-- Search Form + Add User -->
            <div class="col-12 col-lg-6">
                <div class="d-flex gap-2 align-items-center">
                    <!-- Search Form -->
                    <form action="" method="post" class="flex-grow-1 position-relative" id="search-form">
                        <div class="input-group">
                            <input type="text" class="form-control rounded-start-pill pe-5" placeholder="Cari berdasarkan NIK atau nama..." name="keyword" value="<?= $this->session->userdata('keyword') ?>" id="search-bar" onkeyup="displayClear()" autocomplete="off">
                            <input type="hidden" name="find" value="1">
                            <button class="btn btn-secondary rounded-end-pill px-4" type="submit">Cari</button>
                        </div>
                        <img src="<?= base_url('assets/img/delete.png'); ?>" alt="delete" class="action-button clear-button top-50 translate-middle-y" id="clear-button" onclick="clearKeyword()" style="right: 5.5rem;">
                    </form>

                    <!-- Add User Button -->
                    <a href="<?= site_url('user/add'); ?>" class="btn btn-primary rounded-pill px-4" type="button">Tambah</a>
                </div>
            </div>
        </div>
    </div>

    <?php if (empty($users)) : ?>
        <!-- No Data Alert -->
        <div class="alert alert-warning m-4" role="alert">
            <h5 class="alert-heading">Tidak Ada Data</h5>
            <p>Pengguna tidak ditemukan. Coba sesuaikan kata kunci pencarian atau filter Anda.</p>
        </div>
    <?php else : ?>
        <!-- Data Table Container -->
        <div class="card-body p-0 table-responsive">
            <table class="table table-borderless table-hover table-striped mb-0">
                <!-- Table Header -->
                <thead>
                    <tr>
                        <!-- NIK Column -->
                        <th scope="col" class="text-center ps-lg-5 ps-4">
                            <div class="d-flex align-items-center justify-content-center gap-1">
                                <span>NIK</span>
                                <?php if ($sortKeyword[0] === 'nik') : ?>
                                    <img src="<?= base_url($sortKeyword[1] === 'ASC' ? 'assets/img/sort-asc.png' : 'assets/img/sort-desc.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('<?= $sortKeyword[1] === 'ASC' ? 'nik-DESC' : '' ?>')" data-bs-toggle="tooltip" data-bs-placement="top" title="Urutkan berdasarkan NIK">
                                <?php else : ?>
                                    <img src="<?= base_url('assets/img/sort-default.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('nik-ASC')" data-bs-toggle="tooltip" data-bs-placement="top" title="Urutkan berdasarkan NIK">
                                <?php endif ?>
                            </div>
                        </th>

                        <!-- Name Column -->
                        <th scope="col" class="text-center">
                            <div class="d-flex align-items-center justify-content-center gap-1">
                                <span>Nama</span>
                                <?php if ($sortKeyword[0] === 'name') : ?>
                                    <img src="<?= base_url($sortKeyword[1] === 'ASC' ? 'assets/img/sort-asc.png' : 'assets/img/sort-desc.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('<?= $sortKeyword[1] === 'ASC' ? 'name-DESC' : '' ?>')" data-bs-toggle="tooltip" data-bs-placement="top" title="Urutkan berdasarkan Nama">
                                <?php else : ?>
                                    <img src="<?= base_url('assets/img/sort-default.png'); ?>" alt="sort" class="cursor-pointer" width="10px" onclick="sortTable('name-ASC')" data-bs-toggle="tooltip" data-bs-placement="top" title="Urutkan berdasarkan Nama">
                                <?php endif ?>
                            </div>
                        </th>

                        <!-- (user_level column removed for Air System) -->

                        <!-- Factory Column with Filter -->
                        <th scope="col" class="text-center">
                            <div class="d-flex align-items-center justify-content-center gap-1">
                                <span>Factory</span>
                                <svg id="factory-trigger" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48.03 48.6" width="14.31px" height="14.48px" class="cursor-pointer" onclick="hidePop('factory')">
                                    <path d="M47.38,3.78,29.59,21.85A2.24,2.24,0,0,0,29,23.41v23a2.22,2.22,0,0,1-3.61,1.73l-5.56-4.44A2.21,2.21,0,0,1,19,41.93V23.41a2.24,2.24,0,0,0-.64-1.56L.64,3.77A2.22,2.22,0,0,1,2.23,0H45.8A2.22,2.22,0,0,1,47.38,3.78Z" fill="<?= $filterKeyword && isset($filterKeyword['factory']) ? '#000000' : '#b3b3b3' ?>" />
                                </svg>
                            </div>
                        </th>

                        <!-- Edit Column -->
                        <th scope="col" class="text-center">Edit</th>

                        <!-- History Column -->
                        <th scope="col" class="text-center pe-lg-5 pe-4">History</th>
                    </tr>
                </thead>

                <!-- Table Body -->
                <tbody>
                    <?php foreach ($users as $user) : ?>
                        <tr>
                            <th scope="row" class="text-center ps-lg-5 ps-4"><?= $user['nik']; ?></th>
                            <td class="text-center"><?= $user['name']; ?></td>
                            <!-- user_level column removed -->
                            <td class="text-center">
                                <span class="badge bg-info text-dark"><?= $user['factory']; ?></span>
                            </td>
                            <td class="text-center">
                                <a href="<?= site_url('user/edit/') . urlencode(base64_encode($user['nik'])); ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit pengguna">
                                    <img src="<?= base_url('assets/img/edit.png'); ?>" alt="edit" class="action-button">
                                </a>
                            </td>
                            <td class="text-center pe-lg-5 pe-4">
                                <a href="<?= site_url('report/user_history/') . urlencode(base64_encode($user['nik'])); ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="Lihat history pengguna">
                                    <img src="<?= base_url('assets/img/clock-history.svg'); ?>" alt="history" class="action-button">
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
                    Menampilkan <strong><?= $display; ?></strong>
                </div>

                <!-- Pagination Links -->
                <?= $this->pagination->create_links(); ?>

                <!-- Action Buttons -->
                <div class="btn-group">
                    <!-- Reset All Filters -->
                    <form action="" method="post">
                        <input type="hidden" name="reset" value="1">
                        <button type="submit" class="btn btn-outline-secondary rounded-start-pill">Reset Filter</button>
                    </form>

                    <!-- Download Button -->
                    <a href="<?= site_url('user/download'); ?>" class="btn btn-primary">Download</a>

                    <!-- Upload Modal Trigger -->
                    <button type="button" class="btn btn-primary rounded-end-pill" data-bs-toggle="modal" data-bs-target="#uploadModal">
                        Upload
                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Hidden Filter Forms -->

<div hidden>
    <!-- Factory Filter Form -->
    <form action="" method="post" id="factory-filter" style="min-width: 150px;">
        <p class="text-muted small mb-2">Pilih factory untuk filter:</p>
        <?php foreach ($factories as $factory) : ?>
            <div class="form-check">
                <input class="form-check-input" id="check-factory-<?= strtolower(str_replace(' ', '-', $factory)); ?>" type="checkbox" name="filter-factory[]" value="<?= $factory; ?>" <?= ($filterKeyword && isset($filterKeyword['factory']) && in_array($factory, $filterKeyword['factory'])) ? 'checked' : '' ?>>
                <label class="form-check-label" for="check-factory-<?= strtolower(str_replace(' ', '-', $factory)); ?>">
                    <?= $factory; ?>
                </label>
            </div>
        <?php endforeach ?>
        <input type="hidden" name="factory" value="1">
        <div class="d-flex justify-content-between mt-3">
            <button type="button" class="btn btn-secondary btn-sm rounded-pill" onclick="resetFilter('factory-filter')">Reset</button>
            <button type="submit" class="btn btn-primary btn-sm rounded-pill">Apply Filter</button>
        </div>
    </form>
</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadModalLabel">Upload Data Pengguna</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <strong>Ketentuan Upload:</strong>
                    <ul class="mb-0 mt-2">
                        <li>WAJIB menggunakan template yang sudah disediakan.</li>
                        <li>Download template <a href="<?= site_url('user/template'); ?>">disini</a>.</li>
                        <li>Ketentuan pengisian tabel:
                            <ol>
                                <li>NIK wajib menggunakan angka dan berjumlah 9 digit.</li>
                                <li>Level Pengguna diisi dengan "OPERATOR" atau "SUPER USER".</li>
                                <li>Factory diisi dengan "AOI 1", "AOI 2", "AOI 3", atau "AOI 5".</li>
                            </ol>
                        </li>
                    </ul>
                </div>
                <form id="uploadForm" action="" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="formFile" class="form-label">Pilih File Excel</label>
                        <input class="form-control" type="file" id="formFile" name="file" accept=".xlsx,.xls">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success rounded-pill" id="uploadBtn">Upload Data</button>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript Files -->
<script src="<?= base_url('assets/js/searchbar.js'); ?>"></script>
<script src="<?= base_url('assets/js/sortbutton.js'); ?>"></script>
<script src="<?= base_url('assets/js/resetfilter.js'); ?>"></script>
<script src="<?= base_url('assets/js/tooltip.js'); ?>"></script>

<script>
    /**
     * Global Configuration Variables
     * These variables configure the behavior of various JavaScript components
     */

    // Set notification duration to 10 seconds (10000ms)
    window.notificationDuration = "10000";

    /**
     * Configuration array for initializing Bootstrap popovers.
     * Each entry includes the element ID prefix and the popover title.
     * 
     * @type {Array<{id: string, title: string}>}
     */
    window.popoverConfigs = [{
        id: 'factory',
        title: 'Filter Factory'
    }];
</script>

<!-- Additional JavaScript Components -->
<script src="<?= base_url('assets/js/notificationlogic.js'); ?>"></script>
<script src="<?= base_url('assets/js/popoverlogic.js'); ?>"></script>
<script src="<?= base_url('assets/js/uploadlogic.js'); ?>"></script>