# OUTLINE SLIDE PRESENTASI UJI KOMPETENSI LSP
**Skema Sertifikasi**: Pemrogram / Junior Web Developer  
**Judul Aplikasi**: Sistem Pemesanan Restoran Berbasis Web (PHP Native & MySQL)

---

### SLIDE 1: JUDUL & IDENTITAS PESERTA
- **Judul Proyek**: Sistem Pemesanan Restoran — Dapoer Dimas
- **Tujuan**: Uji Demonstrasi Praktik Sertifikasi Profesi Pemrograman (SKKNI J.620100)
- **Nama Peserta**: Dimas Faqih
- **Institusi**: Program Studi Informatika / Ilmu Komputer
- **Lembaga Uji**: Lembaga Sertifikasi Profesi (LSP)

---

### SLIDE 2: LATAR BELAKANG & TUJUAN
- **Permasalahan Operasional**:
  - Reservasi manual berisiko bentrok jadwal (*double-booking*).
  - Jumlah tamu melebihi kapasitas tempat duduk meja.
  - Kesalahan kalkulasi tagihan dan data harga yang berubah-ubah.
- **Tujuan Solusi**:
  - Mengotomatisasi validasi ketersediaan meja dan kapasitas tamu.
  - Mengunci harga menu saat pesanan dibuat menggunakan transaksi basis data ACID.
  - Menyediakan laporan rekapitulasi penjualan harian yang siap cetak.

---

### SLIDE 3: FITUR UTAMA SISTEM
1. **Dashboard Eksekutif**: Ringkasan metrik pelanggan, meja aktif, reservasi, dan pesanan terbaru.
2. **Kelola Master Pelanggan**: Pencatatan data nama, nomor telepon, dan email.
3. **Kelola Meja**: Nomor meja, kapasitas kursi, dan status (Aktif/Nonaktif).
4. **Kelola Menu Hidangan**: Daftar makanan/minuman, harga satuan, dan status ketersediaan stok.
5. **Reservasi Meja Cerdas**: Pemilihan meja dengan validasi kapasitas dan anti-bentrok waktu.
6. **Transaksi Pesanan Multi-Item**: Pemilihan banyak menu, subtotal otomatis, dan grand total.
7. **Laporan & Cetak**: Filter rentang tanggal dan cetak laporan langsung via browser.

---

### SLIDE 4: ARSITEKTUR TEKNOLOGI & BASIS DATA
- **Tech Stack**:
  - **Backend**: PHP 8 Native (PDO MySQL, Prepared Statements).
  - **Database**: MySQL/MariaDB (`db_restoran`).
  - **Frontend**: Bootstrap 5.3 CDN, Font Awesome, HTML5, CSS3.
- **Relasi Database (6 Tabel)**:
  - `customers` &bull; `restaurant_tables` &bull; `menu_items`
  - `reservations` &bull; `restaurant_orders` &bull; `order_items`
  - Dilengkapi Foreign Keys, Indexing, dan aturan integritas relasi.

---

### SLIDE 5: PENERAPAN PEMROGRAMAN TERSTRUKTUR & OOP
- **Pemrograman Terstruktur (`restoran/helpers/functions.php`)**:
  - `calculateItemSubtotal()`: Perhitungan presisi perkalian harga dan kuantitas.
  - `validateGuestCount()`: Validasi logis jumlah tamu vs kapasitas kursi.
  - `checkReservationConflict()`: Algoritma pendeteksi bentrok jadwal meja.
- **Pemrograman Berorientasi Objek / OOP (`restoran/models/`)**:
  - Kelas `Customer`, `Reservation`, dan `Order`.
  - Enkapsulasi data dan pemisahan logika database dari antarmuka tampilan.
  - Penggunaan PDO Transaction (`beginTransaction`, `commit`, `rollBack`) pada kelas `Order`.

---

### SLIDE 6: DEMO ALUR APLIKASI
1. **Langkah 1**: Pendaftaran Meja baru dan penentuan kapasitas kursi.
2. **Langkah 2**: Pendaftaran Pelanggan baru.
3. **Langkah 3**: Pembuatan Reservasi Meja (Sistem memvalidasi kapasitas dan jadwal bebas).
4. **Langkah 4**: Pembuatan Pesanan Menu (Pilih hidangan, hitung subtotal & total otomatis).
5. **Langkah 5**: Filter Laporan dan Cetak Laporan Operasional.

---

### SLIDE 7: PENGUJIAN UNIT (UNIT TESTING) & DEBUGGING
- **Metode Pengujian**: CLI Automated Test Runner (`php tests/test_functions.php`).
- **Cakupan Pengujian (21 Test Assertions)**:
  - Perhitungan subtotal dan total akumulasi.
  - Validasi kapasitas tamu (dalam batas, melebihi batas, nilai nol/negatif).
  - Validasi harga dan kuantitas non-negatif.
  - Penolakan reservasi bentrok jadwal dan penerimaan slot bebas.
- **Hasil Pengujian Aktual**: **21/21 PASSED (100% Lulus, 0 Defect)**.
- **Debugging**: Penanganan desimal float dan penyempurnaan query anti-bentrok untuk status dibatalkan.

---

### SLIDE 8: KESIMPULAN
- Aplikasi Sistem Pemesanan Restoran dibangun sederhana, bersih, dan sesuai spesifikasi PRD.
- Seluruh 8 Unit Kompetensi LSP Programmer (SKKNI J.620100) terpenuhi secara tuntas.
- Siap digunakan dan didemonstrasikan di hadapan Asesor Penguji.
