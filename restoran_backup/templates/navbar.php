<?php
/**
 * Template Navigasi Menu & Notifikasi Alert
 */
$halaman_aktif = $_GET['page'] ?? 'dashboard';
?>
<nav class="navbar navbar-expand-lg navbar-custom sticky-top no-print py-2">
    <div class="container-fluid px-lg-4">
        <a class="navbar-brand d-flex align-items-center" href="index.php?page=dashboard">
            <span class="badge bg-warning text-dark me-2 px-2 py-1"><i class="fa fa-utensils"></i></span>
            Dapoer Dimas
        </a>
        <button class="navbar-toggler text-white border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navContent" aria-controls="navContent" aria-expanded="false" aria-label="Toggle navigation">
            <i class="fa fa-bars fs-5 text-white"></i>
        </button>

        <div class="collapse navbar-collapse" id="navContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-3 gap-1">
                <li class="nav-item">
                    <a class="nav-link <?= ($halaman_aktif === 'dashboard') ? 'active' : '' ?>" href="index.php?page=dashboard">
                        <i class="fa fa-chart-pie me-1"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($halaman_aktif === 'customers') ? 'active' : '' ?>" href="index.php?page=customers">
                        <i class="fa fa-users me-1"></i> Pelanggan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($halaman_aktif === 'tables') ? 'active' : '' ?>" href="index.php?page=tables">
                        <i class="fa fa-chair me-1"></i> Meja
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($halaman_aktif === 'menu') ? 'active' : '' ?>" href="index.php?page=menu">
                        <i class="fa fa-book-open me-1"></i> Menu
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($halaman_aktif === 'reservations') ? 'active' : '' ?>" href="index.php?page=reservations">
                        <i class="fa fa-calendar-check me-1"></i> Reservasi
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($halaman_aktif === 'orders') ? 'active' : '' ?>" href="index.php?page=orders">
                        <i class="fa fa-receipt me-1"></i> Pesanan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($halaman_aktif === 'reports') ? 'active' : '' ?>" href="index.php?page=reports">
                        <i class="fa fa-file-alt me-1"></i> Laporan
                    </a>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-secondary bg-opacity-50 text-light py-2 px-3 border border-secondary border-opacity-25">
                    <i class="fa fa-check-circle text-success me-1"></i> Demo LSP Programmer
                </span>
            </div>
        </div>
    </div>
</nav>

<main class="flex-grow-1 py-4">
    <div class="container-fluid px-lg-4">
        <?php 
        $notif = ambilNotifikasi(); 
        if ($notif): 
        ?>
            <div class="alert alert-<?= htmlspecialchars($notif['tipe']) ?> alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
                <i class="fa <?= $notif['tipe'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle' ?> me-2 fs-5"></i>
                <div><?= htmlspecialchars($notif['pesan']) ?></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
