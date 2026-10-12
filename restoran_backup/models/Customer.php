<?php
/**
 * Model Data Pelanggan (Customer)
 * Standar Kompetensi: J.620100.018.02 (Pemrograman Berorientasi Objek)
 */

class Customer
{
    private PDO $db;

    public function __construct(PDO $koneksi)
    {
        $this->db = $koneksi;
    }

    // Mengambil semua daftar pelanggan
    public function ambilSemua(): array
    {
        $query = $this->db->query("SELECT * FROM customers ORDER BY id DESC");
        return $query->fetchAll();
    }

    // Mengambil 1 data pelanggan berdasarkan ID
    public function ambilBerdasarkanId(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM customers WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    // Menambah pelanggan baru (mengembalikan ID yang baru dibuat)
    public function tambah(string $nama, string $telepon, ?string $email = null): int|bool
    {
        $sql = "INSERT INTO customers (name, phone, email) VALUES (:nama, :telepon, :email)";
        $stmt = $this->db->prepare($sql);
        $sukses = $stmt->execute([
            'nama'    => trim($nama),
            'telepon' => trim($telepon),
            'email'   => !empty($email) ? trim($email) : null
        ]);
        return $sukses ? (int)$this->db->lastInsertId() : false;
    }

    // Mengubah data pelanggan
    public function ubah(int $id, string $nama, string $telepon, ?string $email = null): bool
    {
        $sql = "UPDATE customers SET name = :nama, phone = :telepon, email = :email WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id'      => $id,
            'nama'    => trim($nama),
            'telepon' => trim($telepon),
            'email'   => !empty($email) ? trim($email) : null
        ]);
    }

    // Menghapus data pelanggan
    public function hapus(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM customers WHERE id = :id");
            return $stmt->execute(['id' => $id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Menghitung total pelanggan untuk kartu KPI
    public function hitungTotal(): int
    {
        return (int)$this->db->query("SELECT COUNT(*) FROM customers")->fetchColumn();
    }
}
