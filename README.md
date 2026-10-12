# Sistem Pemesanan Restoran — Dapoer Dimas
**Tugas Praktik Demonstrasi Sertifikasi LSP Programmer (SKKNI J.620100)**

Aplikasi web sederhana untuk mencatat data pelanggan, meja, reservasi, pesanan menu, dan melihat laporan operasional restoran. Dibangun menggunakan **PHP Native 8**, **MySQL/MariaDB (PDO)**, dan **Bootstrap 5 CDN**.

---

## 📋 Struktur Direktori Proyek

```text
SERTIFIKASI/
├── restoran/                  # Folder Aplikasi Web Utama
│   ├── config/
│   │   └── database.php       # Konfigurasi koneksi PDO MySQL
│   ├── models/                # Model Data OOP (Customer, Reservation, Order)
│   │   ├── Customer.php
│   │   ├── Reservation.php
│   │   └── Order.php
│   ├── templates/             # Template Antarmuka (Header, Navbar, Footer)
│   │   ├── header.php
│   │   ├── navbar.php
│   │   └── footer.php
│   ├── helpers/               # Fungsi Terstruktur (Validasi, Perhitungan, Sanitasi)
│   │   └── functions.php
│   ├── pages/                 # Halaman Fitur Aplikasi
│   │   ├── dashboard.php
│   │   ├── customers.php
│   │   ├── tables.php
│   │   ├── menu.php
│   │   ├── reservations.php
│   │   ├── orders.php
│   │   └── reports.php
│   ├── tests/
│   │   └── test_functions.php # Unit Testing CLI Mandiri (21 Assertions)
│   ├── database.sql           # Skrip Basis Data Relasional & Sample Data
│   └── index.php              # Routing & Pintu Masuk Utama Aplikasi
├── docs/
│   ├── laporan.md             # Dokumen Laporan Pengembangan Lengkap
│   └── presentasi.md          # Outline Slide Presentasi Uji Kompetensi
├── index.php                  # Pengalihan Otomatis ke restoran/ (agar localhost:8000 langsung jalan)
└── README.md                  # Petunjuk Penggunaan & Instalasi
```

---

## 💻 Kebutuhan Perangkat Lunak (Software Requirements)

1. **PHP**: Versi 8.1 atau lebih baru (dengan ekstensi `pdo_mysql` aktif).
2. **Basis Data**: MySQL / MariaDB (melalui paket XAMPP atau Laragon).
3. **Web Browser**: Google Chrome, Mozilla Firefox, atau Microsoft Edge.

---

## 🚀 Panduan Menjalankan Aplikasi

### 1. Membuat dan Mengimpor Basis Data
1. Nyalakan modul **Apache** dan **MySQL** pada XAMPP Control Panel atau Laragon.
2. Buka **phpMyAdmin** (`http://localhost/phpmyadmin`) pada browser.
3. Buat database baru dengan nama: **`db_restoran`**.
4. Klik tab **Import**, pilih file **`restoran/database.sql`**, lalu klik **Import / Kirim**.

*(Alternatif melalui terminal MySQL:)*
```bash
mysql -u root -e "CREATE DATABASE IF NOT EXISTS db_restoran;"
mysql -u root db_restoran < restoran/database.sql
```

### 2. Pengaturan Koneksi Database
Jika menggunakan pengaturan standar XAMPP (user: `root`, tanpa password), Anda tidak perlu mengubah apapun. Jika memiliki kata sandi database, sesuaikan pada berkas [`restoran/config/database.php`](file:///d:/kuliah/SERTIFIKASI/restoran/config/database.php):
```php
$host = '127.0.0.1';
$db   = 'db_restoran';
$user = 'root';
$pass = ''; // isi jika MySQL Anda menggunakan password
```

### 3. Menjalankan Aplikasi Web

**Opsi A — Menggunakan PHP Built-in Server (Sangat Praktis):**
Buka terminal / PowerShell di direktori proyek ini, lalu langsung jalankan:
```bash
php -S localhost:8000
```
Buka peramban (browser) dan akses URL: **`http://localhost:8000`** *(otomatis diarahkan ke halaman utama aplikasi restoran)*.

*(Atau jika ingin menjalankan dari dalam folder aplikasi:)*
```bash
cd restoran
php -S localhost:8000
```

**Opsi B — Menggunakan Folder htdocs XAMPP:**
Salin folder `restoran/` ke dalam direktori `C:\xampp\htdocs\restoran\`.
Lalu akses melalui browser di URL: **`http://localhost/restoran`**

---

## 🧪 Menjalankan Unit Testing (J.620100.033.02)

Pengujian unit dapat dijalankan kapan saja langsung dari terminal tanpa perlu membuka browser:

```bash
php restoran/tests/test_functions.php
```

**Cakupan Pengujian Unit (21 Test Assertions):**
- Perhitungan subtotal per item dan total akumulasi pesanan.
- Validasi jumlah tamu terhadap kapasitas meja (dalam batas, melebihi kapasitas, nol/negatif).
- Validasi harga dan kuantitas agar tidak boleh nol atau bernilai negatif.
- Penolakan reservasi bentrok pada meja, tanggal, dan waktu yang sama.
- Penerimaan reservasi pada meja bebas atau slot waktu berbeda.

**Hasil Aktual Pengujian:**
```text
Total Uji Coba : 21
Berhasil (PASS): 21
Gagal (FAIL)   : 0
STATUS AKHIR   : [PASS] SEMUA PENGUJIAN UNIT BERHASIL (100% OK)
```

---

## 🎯 Pemetaan 8 Unit Kompetensi LSP Programmer (SKKNI J.620100)

| No | Kode Unit | Judul Unit Kompetensi | Implementasi di Aplikasi |
|:--:|:---------:|:----------------------|:-------------------------|
| 1 | **J.620100.016.01** | Menulis Kode dengan Guidelines & Best Practices | Penamaan konsisten PSR, sanitasi XSS `htmlspecialchars()`, Prepared Statements PDO. |
| 2 | **J.620100.017.02** | Mengimplementasikan Pemrograman Terstruktur | Modul [`restoran/helpers/functions.php`](file:///d:/kuliah/SERTIFIKASI/restoran/helpers/functions.php) untuk validasi kapasitas, kalkulasi tagihan, dan anti-bentrok. |
| 3 | **J.620100.018.02** | Mengimplementasikan OOP | Kelas entitas berorientasi objek di folder [`restoran/models/`](file:///d:/kuliah/SERTIFIKASI/restoran/models/) (`Customer`, `Reservation`, `Order`). |
| 4 | **J.620100.019.02** | Menggunakan Library Pre-Existing | Tampilan responsif Bootstrap 5.3 CDN, Font Awesome Icons, dan PDO. |
| 5 | **J.620100.021.02** | Menerapkan Akses Basis Data | MySQL relasional (6 tabel, Foreign Keys, CASCADE/RESTRICT), Prepared Statements, dan Transaksi Database ACID. |
| 6 | **J.620100.023.02** | Membuat Dokumen Kode Program | `README.md`, komentar kode program, [`docs/laporan.md`](file:///d:/kuliah/SERTIFIKASI/docs/laporan.md), dan [`docs/presentasi.md`](file:///d:/kuliah/SERTIFIKASI/docs/presentasi.md). |
| 7 | **J.620100.025.02** | Melakukan Debugging | Penanganan presisi desimal float dan query anti-bentrok reservasi dibatalkan (tercatat di Bab 7 Laporan). |
| 8 | **J.620100.033.02** | Melaksanakan Pengujian Unit Program | Skrip pengujian CLI mandiri [`restoran/tests/test_functions.php`](file:///d:/kuliah/SERTIFIKASI/restoran/tests/test_functions.php) dengan hasil PASS/FAIL otomatis. |
