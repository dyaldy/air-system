    </main>

    <!-- Footer -->
    <footer class="fixed-bottom d-flex text-white px-2 py-1 justify-content-between" style="background-color: #004274; font-size: 10px;">
        <span>Air System V 1.0</span>
        <span>Operation Excellence 2025</span>
    </footer>

    <!-- Scripts: Bootstrap then app logic (ASRS canonical) -->
    <script>
        // Global vars used by ASRS scripts; set reasonable defaults for air-system
        window.notificationDuration = window.notificationDuration || 3000;
        window.popoverConfigs = window.popoverConfigs || [];
        window.inputConfigs = window.inputConfigs || [];
    </script>
    <!-- Load local Bootstrap bundle for offline use -->
    <script src="<?= base_url('assets/js/bootstrap.bundle.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/tooltip.js') ?>"></script>
    <script src="<?= base_url('assets/js/popoverlogic.js') ?>"></script>
    <script src="<?= base_url('assets/js/notificationlogic.js') ?>"></script>
    <script src="<?= base_url('assets/js/forminput.js') ?>"></script>
    <script src="<?= base_url('assets/js/resetfilter.js') ?>"></script>
    <script src="<?= base_url('assets/js/searchbar.js') ?>"></script>
    <script src="<?= base_url('assets/js/sortbutton.js') ?>"></script>
    <script src="<?= base_url('assets/js/uploadlogic.js') ?>"></script>

    </body>

    </html>