<?php
/**
 * Model Data Pesanan & Transaksi (Order)
 * Standar Kompetensi: J.620100.018.02 (OOP) & J.620100.021.02 (Database Transaction)
 */

class Order
{
    private PDO $db;

    public function __construct(PDO $koneksi)
    {
        $this->db = $koneksi;
    }

    // Mengambil semua riwayat pesanan
    public function ambilSemua(?string $mulai = null, ?string $selesai = null, ?string $status = null): array
    {
        $sql = "SELECT o.*, c.name AS nama_pelanggan, c.phone AS telepon_pelanggan 
                FROM restaurant_orders o
                JOIN customers c ON o.customer_id = c.id
                WHERE 1=1";
        $params = [];

        if (!empty($mulai)) {
            $sql .= " AND DATE(o.order_date) >= :mulai";
            $params['mulai'] = $mulai;
        }
        if (!empty($selesai)) {
            $sql .= " AND DATE(o.order_date) <= :selesai";
            $params['selesai'] = $selesai;
        }
        if (!empty($status)) {
            $sql .= " AND o.status = :status";
            $params['status'] = $status;
        }

        $sql .= " ORDER BY o.order_date DESC, o.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Mengambil pesanan terbaru untuk dashboard
    public function ambilTerbaru(int $limit = 5): array
    {
        $sql = "SELECT o.*, c.name AS nama_pelanggan 
                FROM restaurant_orders o
                JOIN customers c ON o.customer_id = c.id
                ORDER BY o.id DESC LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Mengambil rincian detail 1 pesanan beserta item makanannya
    public function ambilDetail(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT o.*, c.name AS nama_pelanggan, c.phone AS telepon_pelanggan 
                                    FROM restaurant_orders o 
                                    JOIN customers c ON o.customer_id = c.id 
                                    WHERE o.id = :id");
        $stmt->execute(['id' => $id]);
        $pesanan = $stmt->fetch();
        if (!$pesanan) return null;

        // Ambil item makanan
        $stmtItems = $this->db->prepare("SELECT * FROM order_items WHERE order_id = :id");
        $stmtItems->execute(['id' => $id]);
        $pesanan['items'] = $stmtItems->fetchAll();

        return $pesanan;
    }

    // Membuat pesanan baru dengan Transaksi Database (ACID)
    public function buatPesanan(int $id_pelanggan, ?int $id_reservasi, array $daftar_item): array
    {
        // 1. Filter item yang jumlahnya > 0
        $item_valid = [];
        $total_bayar = 0.0;

        foreach ($daftar_item as $item) {
            $id_menu = (int)($item['menu_item_id'] ?? 0);
            $qty     = (int)($item['quantity'] ?? 0);

            if ($id_menu > 0 && $qty > 0) {
                // Ambil harga saat ini dari tabel menu_items (mengunci harga saat dipesan)
                $menu = $this->db->query("SELECT name, price FROM menu_items WHERE id = {$id_menu}")->fetch();
                if ($menu) {
                    $harga = (float)$menu['price'];
                    $subtotal = hitungSubtotal($harga, $qty);
                    $total_bayar += $subtotal;

                    $item_valid[] = [
                        'menu_item_id' => $id_menu,
                        'menu_name'    => $menu['name'],
                        'price'        => $harga,
                        'quantity'     => $qty,
                        'subtotal'     => $subtotal
                    ];
                }
            }
        }

        if (empty($item_valid)) {
            return ['sukses' => false, 'pesan' => 'Pilih minimal 1 menu makanan/minuman dengan jumlah > 0.'];
        }

        // 2. Jalankan Transaksi Database PDO (ACID)
        try {
            $this->db->beginTransaction();

            // Simpan ke tabel restaurant_orders
            $stmtOrder = $this->db->prepare("INSERT INTO restaurant_orders (customer_id, reservation_id, order_date, status, total_amount) 
                                             VALUES (:cust_id, :res_id, NOW(), 'Diproses', :total)");
            $stmtOrder->execute([
                'cust_id' => $id_pelanggan,
                'res_id'  => $id_reservasi ?: null,
                'total'   => $total_bayar
            ]);
            $order_id = (int)$this->db->lastInsertId();

            // Simpan detail item ke order_items
            $stmtItem = $this->db->prepare("INSERT INTO order_items (order_id, menu_item_id, menu_name, price, quantity, subtotal) 
                                            VALUES (:order_id, :menu_id, :nama, :harga, :qty, :subtotal)");
            foreach ($item_valid as $iv) {
                $stmtItem->execute([
                    'order_id' => $order_id,
                    'menu_id'  => $iv['menu_item_id'],
                    'nama'     => $iv['menu_name'],
                    'harga'    => $iv['price'],
                    'qty'      => $iv['quantity'],
                    'subtotal' => $iv['subtotal']
                ]);
            }

            // Komit transaksi jika semua sukses
            $this->db->commit();
            return ['sukses' => true, 'pesan' => 'Pesanan berhasil dibuat.', 'id' => $order_id];
        } catch (PDOException $e) {
            $this->db->rollBack();
            return ['sukses' => false, 'pesan' => 'Gagal menyimpan pesanan: ' . $e->getMessage()];
        }
    }

    // Mengubah status pesanan (Diproses, Selesai, Dibatalkan)
    public function ubahStatus(int $id, string $status): bool
    {
        $stmt = $this->db->prepare("UPDATE restaurant_orders SET status = :status WHERE id = :id");
        return $stmt->execute(['status' => $status, 'id' => $id]);
    }

    // Menghapus pesanan
    public function hapus(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM restaurant_orders WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    // Menghitung total pesanan untuk kartu KPI
    public function hitungTotal(): int
    {
        return (int)$this->db->query("SELECT COUNT(*) FROM restaurant_orders")->fetchColumn();
    }

    // Menghitung total omset pendapatan
    public function hitungOmset(?string $mulai = null, ?string $selesai = null): float
    {
        $sql = "SELECT COALESCE(SUM(total_amount), 0) FROM restaurant_orders WHERE status != 'Dibatalkan'";
        $params = [];
        if (!empty($mulai)) {
            $sql .= " AND DATE(order_date) >= :mulai";
            $params['mulai'] = $mulai;
        }
        if (!empty($selesai)) {
            $sql .= " AND DATE(order_date) <= :selesai";
            $params['selesai'] = $selesai;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (float)$stmt->fetchColumn();
    }
}
