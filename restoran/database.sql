-- ==============================================================
-- DATABASE RESTORAN - SISTEM PEMESANAN RESTORAN
-- Skema Basis Data Relasional MySQL / MariaDB
-- Standar Kompetensi LSP Programmer (SKKNI J.620100)
-- ==============================================================

CREATE DATABASE IF NOT EXISTS db_restoran CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_restoran;

-- Nonaktifkan pemeriksaan foreign key sementara saat reset tabel
SET FOREIGN_KEY_CHECKS = 0;

-- Hapus tabel lama jika ada
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS restaurant_orders;
DROP TABLE IF EXISTS reservations;
DROP TABLE IF EXISTS menu_items;
DROP TABLE IF EXISTS restaurant_tables;
DROP TABLE IF EXISTS customers;
-- Hapus juga tabel dari skema lama jika ada
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS order_details;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS menus;
DROP TABLE IF EXISTS tables;

SET FOREIGN_KEY_CHECKS = 1;

-- 1. Tabel Pelanggan (customers)
CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Tabel Meja Restoran (restaurant_tables)
CREATE TABLE restaurant_tables (
    id INT AUTO_INCREMENT PRIMARY KEY,
    table_number VARCHAR(10) NOT NULL UNIQUE,
    capacity INT NOT NULL,
    status ENUM('Aktif', 'Nonaktif') NOT NULL DEFAULT 'Aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Tabel Menu Makanan & Minuman (menu_items)
CREATE TABLE menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    is_available TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Tabel Reservasi Meja (reservations)
CREATE TABLE reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    table_id INT NOT NULL,
    reservation_date DATE NOT NULL,
    reservation_time TIME NOT NULL,
    guest_count INT NOT NULL,
    status ENUM('Menunggu', 'Dikonfirmasi', 'Selesai', 'Dibatalkan') NOT NULL DEFAULT 'Menunggu',
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (table_id) REFERENCES restaurant_tables(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Tabel Pesanan (restaurant_orders)
CREATE TABLE restaurant_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    reservation_id INT NULL,
    order_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status ENUM('Diproses', 'Selesai', 'Dibatalkan') NOT NULL DEFAULT 'Diproses',
    total_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (reservation_id) REFERENCES reservations(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Tabel Detail Item Pesanan (order_items)
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    menu_item_id INT NOT NULL,
    menu_name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL,
    subtotal DECIMAL(12,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES restaurant_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==============================================================
-- DATA AWAL / SAMPLE SEED DATA
-- ==============================================================

-- Sample Meja
INSERT INTO restaurant_tables (id, table_number, capacity, status) VALUES
(1, 'T-01', 2, 'Aktif'),
(2, 'T-02', 4, 'Aktif'),
(3, 'T-03', 4, 'Aktif'),
(4, 'T-04', 6, 'Aktif'),
(5, 'T-05', 8, 'Aktif'),
(6, 'T-06', 2, 'Nonaktif');

-- Sample Pelanggan (Data realistis lokal)
INSERT INTO customers (id, name, phone, email) VALUES
(1, 'Dimas Faqih', '081278901234', 'dimasfaqih@gmail.com'),
(2, 'Favian Arya', '085712345678', 'favian.arya@gmail.com'),
(3, 'Radhitya Pratama', '087890123456', 'radhit.pratama@gmail.com'),
(4, 'Naufal Alif', '082198765432', 'naufal.alif@gmail.com'),
(5, 'Rafi Akbar', '081398765432', 'rafi.akbar@gmail.com');

-- Sample Menu
INSERT INTO menu_items (id, name, price, is_available) VALUES
(1, 'Nasi Goreng Spesial', 35000.00, 1),
(2, 'Ayam Bakar Madu', 42000.00, 1),
(3, 'Sate Ayam Madura (10 tusuk)', 38000.00, 1),
(4, 'Gado-Gado Betawi', 28000.00, 1),
(5, 'Sop Buntut Sapi', 65000.00, 1),
(6, 'Es Teh Manis', 8000.00, 1),
(7, 'Jus Alpukat', 18000.00, 1),
(8, 'Kopi Susu Gula Aren', 20000.00, 1),
(9, 'Pisang Goreng Keju', 18000.00, 0);

-- Sample Reservasi
INSERT INTO reservations (id, customer_id, table_id, reservation_date, reservation_time, guest_count, status, notes) VALUES
(1, 1, 2, CURDATE(), '12:30:00', 4, 'Dikonfirmasi', 'Dekat colokan buat laptopan'),
(2, 2, 4, CURDATE(), '19:00:00', 5, 'Menunggu', 'Bawa kue ultah buat makan bareng'),
(3, 3, 1, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '13:00:00', 2, 'Menunggu', 'Makan siang bareng teman kampus');

-- Sample Pesanan
INSERT INTO restaurant_orders (id, customer_id, reservation_id, order_date, status, total_amount) VALUES
(1, 1, 1, NOW(), 'Diproses', 140000.00),
(2, 4, NULL, NOW(), 'Selesai', 70000.00);

-- Sample Detail Pesanan
INSERT INTO order_items (id, order_id, menu_item_id, menu_name, price, quantity, subtotal) VALUES
(1, 1, 1, 'Nasi Goreng Spesial', 35000.00, 2, 70000.00),
(2, 1, 2, 'Ayam Bakar Madu', 42000.00, 1, 42000.00),
(3, 1, 6, 'Es Teh Manis', 8000.00, 1, 8000.00),
(4, 1, 8, 'Kopi Susu Gula Aren', 20000.00, 1, 20000.00),
(5, 2, 1, 'Nasi Goreng Spesial', 35000.00, 2, 70000.00);
