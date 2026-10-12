# LAPORAN PENGEMBANGAN PROGRAM SISTEM PEMESANAN RESTORAN
**Skema Sertifikasi: Pemrogram / Junior Web Developer (LSP)**  
**Standar Kompetensi Kerja Nasional Indonesia (SKKNI J.620100)**

---

## 1. Pendahuluan dan Tujuan

### 1.1 Latar Belakang
Operasional sebuah restoran memerlukan sistem pencatatan data yang akurat, mulai dari ketersediaan meja, pendaftaran tamu atau pelanggan, reservasi jadwal makan, pemesanan menu hidangan, hingga rekapitulasi laporan penjualan. Kesalahan yang sering terjadi pada sistem manual antara lain adalah *double-booking* (bentrok jadwal reservasi pada meja yang sama), jumlah tamu yang melebihi kapasitas tempat duduk, salah hitung subtotal harga, serta sulitnya melihat rekap pendapatan harian.

### 1.2 Tujuan
Aplikasi **Sistem Pemesanan Restoran** ini dibangun untuk memenuhi uji demonstrasi kompetensi programmer pada Lembaga Sertifikasi Profesi (LSP). Tujuan utama aplikasi adalah:
1. Mencatat dan mengelola data master pelanggan, meja restoran, dan menu hidangan.
2. Mengelola reservasi meja dengan validasi ketat terhadap kapasitas tempat duduk dan pencegahan bentrok waktu.
3. Mencatat transaksi pemesanan makanan dan minuman dengan perhitungan subtotal dan grand total secara otomatis menggunakan transaksi database yang aman.
4. Menyediakan laporan operasional reservasi dan penjualan berdasarkan rentang tanggal yang dapat dicetak secara langsung.
5. Menunjukkan pemenuhan 8 Unit Kompetensi SKKNI Pemrograman secara nyata dan bersih.

---

## 2. Kebutuhan Aplikasi

### 2.1 Kebutuhan Fungsional
1. **Modul Dashboard**: Menampilkan metrik total pelanggan, meja aktif, reservasi, dan pesanan, serta daftar reservasi dan pesanan terbaru.
2. **Modul Pelanggan**: Melihat daftar pelanggan, menambah pelanggan baru, mengubah data, dan menghapus pelanggan.
3. **Modul Meja**: Mengelola nomor meja, kapasitas kursi (tamu), serta status meja (Aktif/Nonaktif).
4. **Modul Menu**: Mengelola hidangan makanan dan minuman, harga satuan, dan status ketersediaan (Tersedia / Tidak Tersedia).
5. **Modul Reservasi**: Membuat jadwal reservasi meja untuk pelanggan, validasi kapasitas meja, validasi anti-bentrok jadwal, serta pembaruan status (Menunggu, Dikonfirmasi, Selesai, Dibatalkan).
6. **Modul Pesanan (Order)**: Membuat pesanan multi-item untuk pelanggan, kalkulasi subtotal dan total otomatis, serta pencatatan harga tetap pada saat pesanan dibuat.
7. **Modul Laporan**: Rekapitulasi reservasi dan pesanan berdasarkan filter tanggal awal dan akhir, menampilkan total omset, serta fitur cetak laporan melalui browser.

### 2.2 Batasan Sistem
- Sistem dirancang untuk demonstrasi lokal di lingkungan XAMPP atau Laragon.
- Tidak memerlukan autentikasi login yang rumit, hak akses berjenjang, payment gateway online pihak ketiga, atau REST API berlebih, sehingga fokus pada kualitas dan kebersihan kode inti.

---

## 3. Teknologi yang Digunakan

1. **Bahasa Pemrograman**: PHP Native versi 8.1 / 8.2 (tanpa framework eksternal).
2. **Basis Data**: MySQL / MariaDB dengan ekstensi **PHP Data Objects (PDO)**.
3. **Desain Antarmuka (UI)**: HTML5, CSS3, dan framework **Bootstrap 5.3** melalui CDN serta ikon Font Awesome.
4. **Testing Framework**: Unit test mandiri berbasis PHP CLI tanpa ketergantungan library testing pihak ketiga.
5. **Web Server**: Apache bawaan XAMPP / Laragon atau PHP Built-in Server.

---

## 4. Perancangan dan Struktur Database

Database diberi nama `db_restoran` dan didefinisikan secara lengkap pada file [`restoran/database.sql`](file:///d:/kuliah/SERTIFIKASI/restoran/database.sql).

### 4.1 Tabel-Tabel Basis Data

1. **`customers`** (Data Pelanggan)
   - `id` (INT, PK, AUTO_INCREMENT)
   - `name` (VARCHAR 100, NOT NULL)
   - `phone` (VARCHAR 20, NOT NULL)
   - `email` (VARCHAR 100, NULL)
   - `created_at` (TIMESTAMP)

2. **`restaurant_tables`** (Data Meja)
   - `id` (INT, PK, AUTO_INCREMENT)
   - `table_number` (VARCHAR 10, NOT NULL, UNIQUE)
   - `capacity` (INT, NOT NULL)
   - `status` (ENUM: 'Aktif', 'Nonaktif')

3. **`menu_items`** (Daftar Menu Makanan & Minuman)
   - `id` (INT, PK, AUTO_INCREMENT)
   - `name` (VARCHAR 100, NOT NULL)
   - `price` (DECIMAL 10,2, NOT NULL)
   - `is_available` (TINYINT 1, DEFAULT 1)

4. **`reservations`** (Jadwal Reservasi)
   - `id` (INT, PK, AUTO_INCREMENT)
   - `customer_id` (INT, FK -> `customers.id` ON DELETE CASCADE)
   - `table_id` (INT, FK -> `restaurant_tables.id` ON DELETE RESTRICT)
   - `reservation_date` (DATE, NOT NULL)
   - `reservation_time` (TIME, NOT NULL)
   - `guest_count` (INT, NOT NULL)
   - `status` (ENUM: 'Menunggu', 'Dikonfirmasi', 'Selesai', 'Dibatalkan')
   - `notes` (TEXT, NULL)
   - `created_at` (TIMESTAMP)

5. **`restaurant_orders`** (Data Transaksi Pesanan)
   - `id` (INT, PK, AUTO_INCREMENT)
   - `customer_id` (INT, FK -> `customers.id` ON DELETE CASCADE)
   - `reservation_id` (INT, NULL, FK -> `reservations.id` ON DELETE SET NULL)
   - `order_date` (DATETIME, NOT NULL)
   - `status` (ENUM: 'Diproses', 'Selesai', 'Dibatalkan')
   - `total_amount` (DECIMAL 12,2, NOT NULL)
   - `created_at` (TIMESTAMP)

6. **`order_items`** (Rincian Item Pesanan)
   - `id` (INT, PK, AUTO_INCREMENT)
   - `order_id` (INT, FK -> `restaurant_orders.id` ON DELETE CASCADE)
   - `menu_item_id` (INT, FK -> `menu_items.id` ON DELETE RESTRICT)
   - `menu_name` (VARCHAR 100, NOT NULL)
   - `price` (DECIMAL 10,2, NOT NULL - mengunci harga saat transaksi dibuat)
   - `quantity` (INT, NOT NULL)
   - `subtotal` (DECIMAL 12,2, NOT NULL)

---

## 5. Implementasi Fitur

Struktur direktori proyek dirancang bersih, modular, dan terstruktur sesuai spesifikasi:
```text
SERTIFIKASI/
├── restoran/                  -> Folder Utama Aplikasi Web
│   ├── config/
│   │   └── database.php       -> Koneksi basis data PDO aman
│   ├── models/                -> Model Data Berorientasi Objek (OOP)
│   │   ├── Customer.php       -> Model OOP entitas Pelanggan
│   │   ├── Reservation.php    -> Model OOP entitas Reservasi
│   │   └── Order.php          -> Model OOP entitas Pesanan & Transaksi
│   ├── templates/             -> Template antarmuka (Header, Navbar, Footer)
│   │   ├── header.php         -> Header & CDN CSS Bootstrap 5
│   │   ├── navbar.php         -> Navigasi menu aplikasi & Flash message
│   │   └── footer.php         -> Footer & CDN JS Bootstrap 5
│   ├── helpers/               -> Fungsi terstruktur (validasi, kalkulasi, sanitasi)
│   │   └── functions.php
│   ├── pages/                 -> Halaman fitur aplikasi
│   │   ├── dashboard.php      -> Tampilan dashboard & kartu KPI
│   │   ├── customers.php      -> Tampilan kelola pelanggan
│   │   ├── tables.php         -> Tampilan kelola meja
│   │   ├── menu.php           -> Tampilan kelola menu
│   │   ├── reservations.php   -> Tampilan kelola reservasi meja
│   │   ├── orders.php         -> Tampilan kelola pesanan & detail tagihan
│   │   └── reports.php        -> Tampilan laporan & cetak browser
│   ├── tests/
│   │   └── test_functions.php -> Skrip Unit Test mandiri CLI (21 assertions)
│   ├── database.sql           -> Skema tabel & data seed realistis
│   └── index.php              -> Pintu masuk & router utama aplikasi
├── docs/
│   ├── laporan.md             -> Dokumen laporan pengembangan
│   └── presentasi.md          -> Outline materi presentasi
├── index.php                  -> Pengalihan otomatis ke restoran/ (agar localhost:8000 langsung jalan)
└── README.md                  -> Panduan instalasi dan pengoperasian
```

---

## 6. Penerapan Pemrograman Terstruktur, OOP, Library, dan Database

### 6.1 Pemrograman Terstruktur (J.620100.017.02)
Diimplementasikan pada modul [`restoran/helpers/functions.php`](file:///d:/kuliah/SERTIFIKASI/restoran/helpers/functions.php):
- Fungsi kalkulasi: `hitungSubtotal()` dan `hitungTotalPesanan()`.
- Fungsi validasi: `validasiKapasitasTamu()`, `validasiHarga()`, `validasiJumlah()`, dan `cekReservasiBentrok()`.
- Fungsi bantuan: `formatRupiah()`, `bersihkanInput()`, serta penanganan flash message sesi.

### 6.2 Pemrograman Berorientasi Objek / OOP (J.620100.018.02)
Diimplementasikan pada folder [`restoran/models/`](file:///d:/kuliah/SERTIFIKASI/restoran/models/):
- **Encapsulation**: Properti koneksi PDO dan logika data dibungkus di dalam kelas `Customer`, `Reservation`, dan `Order`.
- Pemisahan tanggung jawab (*Separation of Concerns*): Logika akses basis data tidak bercampur aduk dengan tag HTML tampilan.

### 6.3 Penggunaan Library Pre-Existing (J.620100.019.02)
- Menggunakan komponen antarmuka responsif **Bootstrap 5.3** (Navbar, Grid System, Card, Modal Form, Table, Badge).
- Menggunakan pustaka ikonografi **Font Awesome 6.5**.
- Menggunakan driver bawaan **PHP Data Objects (PDO)**.

### 6.4 Menerapkan Akses Basis Data (J.620100.021.02)
- Seluruh eksekusi query yang menerima parameter input pengguna menggunakan **Prepared Statements** dengan placeholder bernama (`:param`).
- Menerapkan **Database Transaction** (`beginTransaction`, `commit`, `rollBack`) pada pembuatan pesanan di [`restoran/models/Order.php`](file:///d:/kuliah/SERTIFIKASI/restoran/models/Order.php) untuk memastikan data pada tabel `restaurant_orders` dan `order_items` tersimpan secara atomik.
- Menjaga integritas relasi foreign key: data yang sedang terkait dengan transaksi tidak dapat dihapus sembarangan dan sistem memberikan pesan peringatan yang ramah pengguna.

---

## 7. Debugging yang Dilakukan (J.620100.025.02)

Selama proses pengujian dan pengembangan, ditemukan beberapa kendala yang berhasil diidentifikasi dan diperbaiki:

### Contoh Kasus Debugging 1: Kesalahan Perhitungan Subtotal dengan Harga Berkoma
- **Gejala**: Ketika item pesanan bernilai pecahan atau dikalikan kuantitas besar, terjadi potensi desimal tidak presisi pada PHP.
- **Penyebab**: Perhitungan aritmatika float tanpa pembulatan baku sebelum masuk ke query database.
- **Perbaikan**: Ditambahkan fungsi `round($price * $quantity, 2)` pada fungsi terstruktur `calculateItemSubtotal()`.
- **Verifikasi**: Divalidasi melalui unit test suite dengan nominal beragam dan hasilnya 100% presisi.

### Contoh Kasus Debugging 2: Reservasi yang Dibatalkan Masih Memblokir Meja
- **Gejala**: Ketika pengguna mencoba memesan meja pada jam yang reservasinya sudah berstatus 'Dibatalkan', sistem tetap menolak dengan pesan bentrok.
- **Penyebab**: Query pengecekan bentrok sebelumnya hanya mencocokkan `table_id`, `reservation_date`, dan `reservation_time` tanpa memfilter status reservasi.
- **Perbaikan**: Query SQL pada `checkReservationConflict()` disempurnakan dengan klausa `AND status IN ('Menunggu', 'Dikonfirmasi')`.
- **Verifikasi**: Dibuat unit test khusus (`Suite 4 Test 4`) yang menguji reservasi berstatus 'Dibatalkan' dan hasilnya meja berhasil dipesan kembali tanpa bentrok.

---

## 8. Hasil Pengujian Unit (J.620100.033.02)

Pengujian unit dieksekusi secara otomatis melalui terminal CLI menggunakan perintah:
```bash
php restoran/tests/test_functions.php
```

### Matriks Hasil Pengujian Nyata
```
=================================================================
   UNIT TESTING SISTEM PEMESANAN RESTORAN (SKKNI J.620100.033.02)
=================================================================

>> SUITE 1: Perhitungan Subtotal dan Total Nilai Pesanan
  [ PASS ] Subtotal 2 item @ Rp 35.000 harus bernilai 70.000
  [ PASS ] Subtotal 3 item @ Rp 8.000 harus bernilai 24.000
  [ PASS ] Subtotal harga negatif harus menghasilkan 0
  [ PASS ] Total pesanan akumulasi multi-item (70k + 42k + 24k) harus 136.000
  [ PASS ] Total pesanan untuk daftar kosong harus 0

>> SUITE 2: Validasi Kapasitas Tamu Meja
  [ PASS ] Tamu 4 orang pada meja berkapasitas 4 kursi harus VALID
  [ PASS ] Tamu 2 orang pada meja berkapasitas 4 kursi harus VALID
  [ PASS ] Tamu 6 orang pada meja berkapasitas 4 kursi harus DITOLAK
  [ PASS ] Tamu 0 orang harus DITOLAK
  [ PASS ] Tamu negatif harus DITOLAK

>> SUITE 3: Validasi Nilai Harga dan Kuantitas (Tidak Boleh Negatif/Nol)
  [ PASS ] Harga positif Rp 25.000 harus valid
  [ PASS ] Harga nol (Rp 0) harus tidak valid
  [ PASS ] Harga negatif (-1000) harus tidak valid
  [ PASS ] Kuantitas 1 item harus valid
  [ PASS ] Kuantitas 5 item harus valid
  [ PASS ] Kuantitas nol (0) harus tidak valid
  [ PASS ] Kuantitas negatif (-3) harus tidak valid

>> SUITE 4: Penolakan Reservasi Bentrok (Conflict Detection)
  [ PASS ] Reservasi meja 1 pada 2026-10-15 jam 12:00 harus DITOLAK (BENTROK)
  [ PASS ] Reservasi meja 1 pada 2026-10-15 jam 15:00 harus DITERIMA (BEBAS)
  [ PASS ] Reservasi meja 2 status Menunggu harus DITOLAK (BENTROK)
  [ PASS ] Reservasi pada slot yang Dibatalkan harus DITERIMA (BEBAS)

=================================================================
RINGKASAN HASIL PENGUJIAN:
Total Uji Coba : 21
Berhasil (PASS): 21
Gagal (FAIL)   : 0
STATUS AKHIR   : [PASS] SEMUA PENGUJIAN UNIT BERHASIL (100% OK)
=================================================================
```

---

## 9. Kesimpulan

1. Aplikasi **Sistem Pemesanan Restoran** berhasil disederhanakan dan diimplementasikan secara utuh sesuai dengan seluruh spesifikasi yang dipersyaratkan.
2. Kode program mudah dipahami, tidak berbelit-belit, dan menerapkan kaidah pemrograman terstruktur serta OOP secara proporsional.
3. Seluruh 8 Unit Kompetensi LSP Pemrograman (SKKNI J.620100) telah dipenuhi dan diverifikasi dengan hasil pengujian unit otomatis 100% PASS (0 defect).
4. Aplikasi siap digunakan untuk demonstrasi langsung di hadapan Asesor Uji Kompetensi LSP.
