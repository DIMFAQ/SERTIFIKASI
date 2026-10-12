<?php
/**
 * RESTORAN - AUTOMATED UNIT TEST SUITE
 * Standar Kompetensi: J.620100.033.02 (Melaksanakan Pengujian Unit Program)
 * 
 * Eksekusi dari terminal:
 *   php tests/test_functions.php
 */

require_once __DIR__ . '/../helpers/functions.php';

$totalTests = 0;
$totalPassed = 0;
$totalFailed = 0;
$errors = [];

function assertTest(string $testName, bool $condition, string $detail = ''): void
{
    global $totalTests, $totalPassed, $totalFailed, $errors;
    $totalTests++;

    if ($condition) {
        $totalPassed++;
        echo "  [ PASS ] {$testName}\n";
    } else {
        $totalFailed++;
        $errors[] = "{$testName}: {$detail}";
        echo "  [ FAIL ] {$testName} -> {$detail}\n";
    }
}

echo "=================================================================\n";
echo "   UNIT TESTING SISTEM PEMESANAN RESTORAN (SKKNI J.620100.033.02)\n";
echo "=================================================================\n\n";

// -----------------------------------------------------------------
// 1. Pengujian Perhitungan Subtotal dan Total Pesanan
// -----------------------------------------------------------------
echo ">> SUITE 1: Perhitungan Subtotal dan Total Nilai Pesanan\n";

$subtotal1 = hitungSubtotal(35000.0, 2);
assertTest("Subtotal 2 item @ Rp 35.000 harus bernilai 70.000", $subtotal1 === 70000.0, "Hasil: {$subtotal1}");

$subtotal2 = hitungSubtotal(8000.0, 3);
assertTest("Subtotal 3 item @ Rp 8.000 harus bernilai 24.000", $subtotal2 === 24000.0, "Hasil: {$subtotal2}");

$subtotalNegative = hitungSubtotal(-10000.0, 2);
assertTest("Subtotal harga negatif harus menghasilkan 0", $subtotalNegative === 0.0, "Hasil: {$subtotalNegative}");

$sampleItems = [
    ['price' => 35000.0, 'quantity' => 2], // 70.000
    ['price' => 42000.0, 'quantity' => 1], // 42.000
    ['price' => 8000.0,  'quantity' => 3], // 24.000
];
$totalOrder = hitungTotalPesanan($sampleItems);
assertTest("Total pesanan akumulasi multi-item (70k + 42k + 24k) harus 136.000", $totalOrder === 136000.0, "Hasil: {$totalOrder}");

$emptyTotal = hitungTotalPesanan([]);
assertTest("Total pesanan untuk daftar kosong harus 0", $emptyTotal === 0.0, "Hasil: {$emptyTotal}");

echo "\n";

// -----------------------------------------------------------------
// 2. Pengujian Validasi Jumlah Tamu terhadap Kapasitas Meja
// -----------------------------------------------------------------
echo ">> SUITE 2: Validasi Kapasitas Tamu Meja\n";

$capWithin = validasiKapasitasTamu(4, 4);
assertTest("Tamu 4 orang pada meja berkapasitas 4 kursi harus VALID", $capWithin['valid'] === true, $capWithin['pesan']);

$capBelow = validasiKapasitasTamu(2, 4);
assertTest("Tamu 2 orang pada meja berkapasitas 4 kursi harus VALID", $capBelow['valid'] === true, $capBelow['pesan']);

$capExceed = validasiKapasitasTamu(6, 4);
assertTest("Tamu 6 orang pada meja berkapasitas 4 kursi harus DITOLAK", $capExceed['valid'] === false, "Diterima padahal melebihi");

$capZero = validasiKapasitasTamu(0, 4);
assertTest("Tamu 0 orang harus DITOLAK", $capZero['valid'] === false, "Tamu 0 diizinkan");

$capNegative = validasiKapasitasTamu(-2, 4);
assertTest("Tamu negatif harus DITOLAK", $capNegative['valid'] === false, "Tamu negatif diizinkan");

echo "\n";

// -----------------------------------------------------------------
// 3. Pengujian Validasi Nilai Harga dan Kuantitas
// -----------------------------------------------------------------
echo ">> SUITE 3: Validasi Nilai Harga dan Kuantitas (Tidak Boleh Negatif/Nol)\n";

assertTest("Harga positif Rp 25.000 harus valid", validasiHarga(25000.0) === true);
assertTest("Harga nol (Rp 0) harus tidak valid", validasiHarga(0.0) === false);
assertTest("Harga negatif (-1000) harus tidak valid", validasiHarga(-1000.0) === false);

assertTest("Kuantitas 1 item harus valid", validasiJumlah(1) === true);
assertTest("Kuantitas 5 item harus valid", validasiJumlah(5) === true);
assertTest("Kuantitas nol (0) harus tidak valid", validasiJumlah(0) === false);
assertTest("Kuantitas negatif (-3) harus tidak valid", validasiJumlah(-3) === false);

echo "\n";

// -----------------------------------------------------------------
// 4. Pengujian Penolakan Reservasi Bentrok pada Meja, Tanggal, & Waktu Sama
// -----------------------------------------------------------------
echo ">> SUITE 4: Penolakan Reservasi Bentrok (Conflict Detection)\n";

// Mock SQLite in-memory untuk pengujian logika mandiri
$testPdo = new PDO('sqlite::memory:');
$testPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$testPdo->exec("CREATE TABLE reservations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    table_id INTEGER NOT NULL,
    reservation_date TEXT NOT NULL,
    reservation_time TEXT NOT NULL,
    status TEXT NOT NULL
)");

// Masukkan data jadwal reservasi awal
$testPdo->exec("INSERT INTO reservations (table_id, reservation_date, reservation_time, status) 
                VALUES (1, '2026-10-15', '12:00:00', 'Dikonfirmasi')");
$testPdo->exec("INSERT INTO reservations (table_id, reservation_date, reservation_time, status) 
                VALUES (2, '2026-10-15', '19:00:00', 'Menunggu')");
$testPdo->exec("INSERT INTO reservations (table_id, reservation_date, reservation_time, status) 
                VALUES (1, '2026-10-15', '18:00:00', 'Dibatalkan')");

// Test 1: Meja 1, tanggal dan jam sama persis (12:00:00) -> DITOLAK (BENTROK)
$conflict1 = cekReservasiBentrok($testPdo, 1, '2026-10-15', '12:00:00');
assertTest("Reservasi meja 1 pada 2026-10-15 jam 12:00 harus DITOLAK (BENTROK)", $conflict1 === true);

// Test 2: Meja 1, tanggal sama tapi jam berbeda (15:00:00) -> DITERIMA (BEBAS)
$conflict2 = cekReservasiBentrok($testPdo, 1, '2026-10-15', '15:00:00');
assertTest("Reservasi meja 1 pada 2026-10-15 jam 15:00 harus DITERIMA (BEBAS)", $conflict2 === false);

// Test 3: Meja 2, tanggal sama, jam sama (19:00:00) -> DITOLAK (BENTROK)
$conflict3 = cekReservasiBentrok($testPdo, 2, '2026-10-15', '19:00:00');
assertTest("Reservasi meja 2 status Menunggu harus DITOLAK (BENTROK)", $conflict3 === true);

// Test 4: Meja 1, pada slot yang sudah Dibatalkan (18:00:00) -> DITERIMA (BEBAS)
$conflict4 = cekReservasiBentrok($testPdo, 1, '2026-10-15', '18:00:00');
assertTest("Reservasi pada slot yang Dibatalkan harus DITERIMA (BEBAS)", $conflict4 === false);

echo "\n";
echo "=================================================================\n";
echo "RINGKASAN HASIL PENGUJIAN:\n";
echo "Total Uji Coba : {$totalTests}\n";
echo "Berhasil (PASS): {$totalPassed}\n";
echo "Gagal (FAIL)   : {$totalFailed}\n";

if ($totalFailed === 0) {
    echo "STATUS AKHIR   : [PASS] SEMUA PENGUJIAN UNIT BERHASIL (100% OK)\n";
    echo "=================================================================\n";
    exit(0);
} else {
    echo "STATUS AKHIR   : [FAIL] DITEMUKAN PENGUJIAN YANG GAGAL!\n";
    foreach ($errors as $err) {
        echo " - {$err}\n";
    }
    echo "=================================================================\n";
    exit(1);
}
