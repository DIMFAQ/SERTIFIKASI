<?php
/**
 * Sistem Pemesanan Restoran - Sertifikasi LSP Programmer
 * Pintu Masuk Utama Aplikasi (Front Controller & Router)
 * 
 * Standar Kompetensi SKKNI:
 * - J.620100.016.01: Guidelines & Best Practices
 * - J.620100.017.02: Pemrograman Terstruktur
 * - J.620100.018.02: Pemrograman Berorientasi Objek (OOP)
 * - J.620100.021.02: Menerapkan Akses Basis Data
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Muat Konfigurasi Database & Fungsi Bantuan
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/helpers/functions.php';

// 2. Muat Model Data (Pengganti nama 'classes')
require_once __DIR__ . '/models/Customer.php';
require_once __DIR__ . '/models/Reservation.php';
require_once __DIR__ . '/models/Order.php';

// Inisialisasi Objek Model untuk digunakan di halaman
$pelangganClass = new Customer($db);
$reservasiClass = new Reservation($db);
$pesananClass   = new Order($db);

// 3. Tentukan Halaman yang Dituju (Routing)
$halaman = $_GET['page'] ?? 'dashboard';

// Daftar halaman yang diperbolehkan
$halaman_valid = [
    'dashboard'    => 'pages/dashboard.php',
    'customers'    => 'pages/customers.php',
    'tables'       => 'pages/tables.php',
    'menu'         => 'pages/menu.php',
    'reservations' => 'pages/reservations.php',
    'orders'       => 'pages/orders.php',
    'reports'      => 'pages/reports.php',
];

$file_halaman = $halaman_valid[$halaman] ?? 'pages/dashboard.php';

// 4. Render Antarmuka Pengguna (Header, Navbar, Halaman, Footer)
require_once __DIR__ . '/templates/header.php';
require_once __DIR__ . '/templates/navbar.php';
require_once __DIR__ . '/' . $file_halaman;
require_once __DIR__ . '/templates/footer.php';
