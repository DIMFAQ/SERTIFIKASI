<?php
/**
 * Konfigurasi Koneksi Database (PDO MySQL)
 * Standar Kompetensi: J.620100.021.02
 */

function koneksiDatabase(): PDO
{
    static $koneksi = null;

    if ($koneksi === null) {
        $host     = '127.0.0.1';
        $database = 'db_restoran';
        $username = 'root';
        $password = '';

        try {
            $dsn = "mysql:host={$host};dbname={$database};charset=utf8mb4";
            $koneksi = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            die("Koneksi database gagal: " . $e->getMessage());
        }
    }

    return $koneksi;
}

// Inisialisasi variabel koneksi global untuk dipakai di aplikasi
$db = koneksiDatabase();
