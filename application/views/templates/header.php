<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title; ?></title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/img/favicon.png'); ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.min.css'); ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css'); ?>">
    <!-- FontAwesome Local -->
    <link rel="stylesheet" href="<?= base_url('assets/fontawesome/all.min.css'); ?>">
</head>

<body style="background-color: #caeefb;">
    <nav class="navbar navbar-dark fixed-top" style="background-color: #004274 !important;">
        <div class="container-fluid">
            <div class="d-flex align-items-center gap-3">
                <!-- Offcanvas toggle always available in air-system (no user levels) -->
                <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasDarkNavbar" aria-controls="offcanvasDarkNavbar" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <!-- Apparel One Indonesia Logo -->
                <a href="<?= base_url(); ?>">
                    <img src="<?= base_url('assets/img/logo-aoi.png'); ?>" alt="logo-aoi" class="navbar-logo-aoi">
                </a>
            </div>

            <div class="d-flex align-items-center gap-3">
                <!-- User Name Display (guarded) -->
                <span class="text-white">Hi, <?= isset($this->session->userdata('user_data')['name']) ? explode(' ', $this->session->userdata('user_data')['name'])[0] : 'Guest'; ?></span>

                <!-- Divider -->
                <div class="vr" style="height: 24px; background-color: #ffffff;"></div>

                <!-- Logout Button -->
                <a href="<?= site_url('auth/logout'); ?>" class="text-white text-decoration-none">
                    Logout
                </a>
            </div>

            <div class="offcanvas offcanvas-start text-bg-dark" tabindex="-1" id="offcanvasDarkNavbar" aria-labelledby="offcanvasDarkNavbarLabel" style="background-color: #004274 !important;">
                <!-- Off Canvas Header -->
                <div class="offcanvas-header">
                    <!-- Apparel One Indonesia Logo -->
                    <a href="<?= base_url(); ?>">
                        <img src="<?= base_url('assets/img/logo-aoi.png'); ?>" alt="logo-aoi" class="navbar-logo-aoi">
                    </a>

                    <!-- Off Canvas Close Button -->
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>

                <!-- Off Canvas Body -->
                <div class="offcanvas-body">
                    <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
                        <!-- Link to Home -->
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url(); ?>">Beranda</a>
                        </li>

                        <!-- Link to User Controller -->
                        <li class="nav-item">
                            <a class="nav-link" href="<?= site_url('user'); ?>">Pengguna</a>
                        </li>

                        <!-- Link to Pneumatic Controller (goes to type selection) -->
                        <li class="nav-item">
                            <a class="nav-link" href="<?= site_url('pneumatic/type'); ?>">Pneumatic</a>
                        </li>

                        <!-- Link to Fitting Controller (goes to type selection) -->
                        <li class="nav-item">
                            <a class="nav-link" href="<?= site_url('fitting/type'); ?>">Fitting</a>
                        </li>

                        <!-- Link to Solenoid Controller (under development) -->
                        <li class="nav-item">
                            <a class="nav-link disabled" href="#" style="opacity: 0.6;">Solenoid (under development)</a>
                        </li>

                        <!-- Link to Manifold Controller (under development) -->
                        <li class="nav-item">
                            <a class="nav-link disabled" href="#" style="opacity: 0.6;">Manifold (under development)</a>
                        </li>

                        <!-- Link to Regulator Controller (under development) -->
                        <li class="nav-item">
                            <a class="nav-link disabled" href="#" style="opacity: 0.6;">Regulator (under development)</a>
                        </li>

                        <!-- Link to Storage Controller -->
                        <li class="nav-item">
                            <a class="nav-link" href="<?= site_url('storage'); ?>">Penyimpanan</a>
                        </li>

                        <!-- Link to Reports -->
                        <li class="nav-item">
                            <a class="nav-link" href="<?= site_url('storage/reports'); ?>">Laporan</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <main>