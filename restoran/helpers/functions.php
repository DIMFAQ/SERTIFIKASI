<?php
/**
 * Modul Fungsi Bantuan (Helpers)
 * Standar Kompetensi: J.620100.017.02 & J.620100.016.01
 */

/**
 * 1. Menghitung subtotal per item menu (harga x jumlah)
 */
function hitungSubtotal(float $harga, int $jumlah): float
{
    if ($harga < 0 || $jumlah < 0) {
        return 0.0;
    }
    return round($harga * $jumlah, 2);
}

/**
 * 2. Menghitung total nilai pesanan dari semua item
 */
function hitungTotalPesanan(array $daftar_item): float
{
    $total = 0.0;
    foreach ($daftar_item as $item) {
        $harga = (float)($item['price'] ?? $item['harga'] ?? 0);
        $qty   = (int)($item['quantity'] ?? $item['jumlah'] ?? 0);
        if ($harga > 0 && $qty > 0) {
            $total += hitungSubtotal($harga, $qty);
        }
    }
    return round($total, 2);
}

/**
 * 3. Validasi jumlah tamu tidak boleh melebihi kapasitas meja
 */
function validasiKapasitasTamu(int $jumlah_tamu, int $kapasitas_meja): array
{
    if ($jumlah_tamu <= 0) {
        return ['valid' => false, 'pesan' => 'Jumlah tamu minimal 1 orang.'];
    }
    if ($jumlah_tamu > $kapasitas_meja) {
        return ['valid' => false, 'pesan' => "Tamu ({$jumlah_tamu} orang) melebihi kapasitas meja ({$kapasitas_meja} kursi)."];
    }
    return ['valid' => true, 'pesan' => 'Kapasitas meja mencukupi.'];
}

/**
 * 4. Validasi nilai harga dan kuantitas positif
 */
function validasiHarga(float $harga): bool
{
    return $harga > 0;
}

function validasiJumlah(int $jumlah): bool
{
    return $jumlah > 0;
}

/**
 * 5. Pengecekan reservasi bentrok pada meja, tanggal, dan jam yang sama
 */
function cekReservasiBentrok(PDO $db, int $id_meja, string $tanggal, string $waktu, ?int $kecualikan_id = null): bool
{
    $sql = "SELECT COUNT(*) FROM reservations 
            WHERE table_id = :id_meja 
              AND reservation_date = :tanggal 
              AND reservation_time = :waktu 
              AND status IN ('Menunggu', 'Dikonfirmasi')";

    $params = [
        'id_meja' => $id_meja,
        'tanggal' => $tanggal,
        'waktu'   => $waktu
    ];

    if ($kecualikan_id !== null) {
        $sql .= " AND id != :kecualikan_id";
        $params['kecualikan_id'] = $kecualikan_id;
    }

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return (int)$stmt->fetchColumn() > 0;
}

/**
 * 6. Format tampilan uang Rupiah (contoh: Rp 35.000)
 */
function formatRupiah(float $angka): string
{
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

/**
 * 7. Sanitasi input form untuk mencegah XSS
 */
function bersihkanInput(string $data): string
{
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * 8. Flash message (notifikasi pesan sukses / gagal)
 */
function setNotifikasi(string $tipe, string $pesan): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['notifikasi'] = ['tipe' => $tipe, 'pesan' => $pesan];
}

function ambilNotifikasi(): ?array
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (isset($_SESSION['notifikasi'])) {
        $notif = $_SESSION['notifikasi'];
        unset($_SESSION['notifikasi']);
        return $notif;
    }
    return null;
}

function badgeStatus(string $status): string
{
    $warna = match ($status) {
        'Menunggu', 'Diproses' => 'badge-soft-warning',
        'Dikonfirmasi', 'Aktif' => 'badge-soft-primary',
        'Selesai', 'Tersedia'   => 'badge-soft-success',
        'Dibatalkan', 'Nonaktif' => 'badge-soft-danger',
        default => 'badge-soft-secondary'
    };
    return '<span class="badge ' . $warna . ' px-2 py-1 rounded-pill fw-semibold"><i class="fa fa-circle me-1" style="font-size: 0.45em; vertical-align: middle;"></i>' . htmlspecialchars($status) . '</span>';
}

/**
 * 10. Avatar inisial nama pelanggan untuk UI modern
 */
function avatarInisial(string $nama): string
{
    $parts = explode(' ', trim($nama));
    $i1 = substr($parts[0] ?? '', 0, 1);
    $i2 = isset($parts[1]) ? substr($parts[1], 0, 1) : '';
    $inisial = strtoupper($i1 . $i2);
    if (empty($inisial)) $inisial = 'P';
    
    // Warna acak konsisten berdasarkan panjang nama
    $bgColors = ['#e0e7ff', '#fef3c7', '#dcfce7', '#fee2e2', '#f3e8ff'];
    $textColors = ['#4338ca', '#b45309', '#15803d', '#b91c1c', '#7e22ce'];
    $idx = strlen($nama) % count($bgColors);

    return '<div class="avatar-circle me-2" style="background-color: ' . $bgColors[$idx] . '; color: ' . $textColors[$idx] . ';">' . htmlspecialchars($inisial) . '</div>';
}

// Alias nama fungsi bahasa Inggris untuk unit testing
function calculateItemSubtotal(float $p, int $q): float { return hitungSubtotal($p, $q); }
function calculateOrderTotal(array $items): float { return hitungTotalPesanan($items); }
function validateGuestCount(int $g, int $c): array { 
    $r = validasiKapasitasTamu($g, $c); 
    return ['valid' => $r['valid'], 'message' => $r['pesan']]; 
}
function validatePrice(float $p): bool { return validasiHarga($p); }
function validateQuantity(int $q): bool { return validasiJumlah($q); }
function checkReservationConflict(PDO $db, int $t, string $d, string $ti, ?int $ex = null): bool {
    return cekReservasiBentrok($db, $t, $d, $ti, $ex);
}
function sanitize(string $s): string { return bersihkanInput($s); }
function setFlash(string $t, string $m): void { setNotifikasi($t, $m); }
function getFlash(): ?array { 
    $n = ambilNotifikasi(); 
    return $n ? ['type' => $n['tipe'], 'message' => $n['pesan']] : null; 
}
function getReservationBadge(string $s): string { return badgeStatus($s); }
function getOrderBadge(string $s): string { return badgeStatus($s); }
