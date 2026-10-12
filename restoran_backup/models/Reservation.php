<?php
/**
 * Model Data Reservasi Meja (Reservation)
 * Standar Kompetensi: J.620100.018.02 (Pemrograman Berorientasi Objek)
 */

class Reservation
{
    private PDO $db;

    public function __construct(PDO $koneksi)
    {
        $this->db = $koneksi;
    }

    // Mengambil daftar reservasi dengan nama pelanggan dan meja
    public function ambilSemua(?string $tanggal = null, ?string $status = null): array
    {
        $sql = "SELECT r.*, c.name AS nama_pelanggan, c.phone AS telepon_pelanggan, 
                       t.table_number AS nomor_meja, t.capacity AS kapasitas_meja
                FROM reservations r
                JOIN customers c ON r.customer_id = c.id
                JOIN restaurant_tables t ON r.table_id = t.id
                WHERE 1=1";
        $params = [];

        if (!empty($tanggal)) {
            $sql .= " AND r.reservation_date = :tanggal";
            $params['tanggal'] = $tanggal;
        }

        if (!empty($status)) {
            $sql .= " AND r.status = :status";
            $params['status'] = $status;
        }

        $sql .= " ORDER BY r.reservation_date DESC, r.reservation_time DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Mengambil reservasi terbaru untuk widget dashboard
    public function ambilTerbaru(int $limit = 5): array
    {
        $sql = "SELECT r.*, c.name AS nama_pelanggan, c.phone AS telepon_pelanggan, 
                       t.table_number AS nomor_meja, t.capacity AS kapasitas_meja
                FROM reservations r
                JOIN customers c ON r.customer_id = c.id
                JOIN restaurant_tables t ON r.table_id = t.id
                ORDER BY r.id DESC LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Menambah reservasi baru dengan validasi kapasitas dan anti-bentrok
    public function tambah(array $data): array
    {
        $id_meja     = (int)$data['table_id'];
        $jumlah_tamu = (int)$data['guest_count'];
        $tanggal     = $data['reservation_date'];
        $waktu       = $data['reservation_time'];

        // Cek data meja
        $meja = $this->db->query("SELECT * FROM restaurant_tables WHERE id = {$id_meja}")->fetch();
        if (!$meja || $meja['status'] !== 'Aktif') {
            return ['sukses' => false, 'pesan' => 'Meja tidak ditemukan atau sedang nonaktif.'];
        }

        // Cek kapasitas tamu
        $cekKapasitas = validasiKapasitasTamu($jumlah_tamu, (int)$meja['capacity']);
        if (!$cekKapasitas['valid']) {
            return ['sukses' => false, 'pesan' => $cekKapasitas['pesan']];
        }

        // Cek jadwal bentrok
        if (cekReservasiBentrok($this->db, $id_meja, $tanggal, $waktu)) {
            return ['sukses' => false, 'pesan' => "Meja {$meja['table_number']} sudah memiliki reservasi aktif di jam tersebut."];
        }

        $sql = "INSERT INTO reservations (customer_id, table_id, reservation_date, reservation_time, guest_count, status, notes) 
                VALUES (:cust_id, :meja_id, :tgl, :jam, :tamu, :status, :catatan)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'cust_id' => $data['customer_id'],
            'meja_id' => $id_meja,
            'tgl'     => $tanggal,
            'jam'     => $waktu,
            'tamu'    => $jumlah_tamu,
            'status'  => $data['status'] ?? 'Menunggu',
            'catatan' => $data['notes'] ?? null
        ]);

        return ['sukses' => true, 'pesan' => 'Reservasi berhasil disimpan.'];
    }

    // Mengubah status reservasi (Menunggu, Dikonfirmasi, Selesai, Dibatalkan)
    public function ubahStatus(int $id, string $status): bool
    {
        $stmt = $this->db->prepare("UPDATE reservations SET status = :status WHERE id = :id");
        return $stmt->execute(['status' => $status, 'id' => $id]);
    }

    // Menghapus reservasi
    public function hapus(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM reservations WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    // Menghitung total reservasi untuk kartu KPI
    public function hitungTotal(): int
    {
        return (int)$this->db->query("SELECT COUNT(*) FROM reservations")->fetchColumn();
    }
}
