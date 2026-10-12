<?php
/**
 * Halaman Manajemen Reservasi Meja
 * Menggunakan class Reservation dari classes/Reservation.php
 */

// 1. Tangani Aksi Form POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';

    if ($aksi === 'tambah') {
        $tipe_pelanggan = $_POST['tipe_pelanggan'] ?? 'baru';
        if ($tipe_pelanggan === 'terdaftar') {
            $customer_id = (int)($_POST['id_pelanggan'] ?? 0);
            if ($customer_id <= 0) {
                setNotifikasi('danger', 'Silakan pilih pelanggan terdaftar.');
                header('Location: index.php?page=reservations');
                exit;
            }
        } else {
            // Tamu Baru Cepat
            $nama_tamu = bersihkanInput($_POST['nama_tamu'] ?? '');
            if (empty($nama_tamu)) {
                $nama_tamu = 'Tamu Reservasi #' . rand(100, 999);
            }
            $telepon_tamu = bersihkanInput($_POST['telepon_tamu'] ?? '');
            if (empty($telepon_tamu)) {
                $telepon_tamu = '-';
            }

            $id_baru = $pelangganClass->tambah($nama_tamu, $telepon_tamu, null);
            if (!$id_baru) {
                setNotifikasi('danger', 'Gagal mendaftarkan data tamu reservasi.');
                header('Location: index.php?page=reservations');
                exit;
            }
            $customer_id = (int)$id_baru;
        }

        $hasil = $reservasiClass->tambah([
            'customer_id'      => $customer_id,
            'table_id'         => (int)($_POST['id_meja'] ?? 0),
            'reservation_date' => $_POST['tanggal'] ?? '',
            'reservation_time' => $_POST['waktu'] ?? '',
            'guest_count'      => (int)($_POST['tamu'] ?? 0),
            'status'           => $_POST['status'] ?? 'Menunggu',
            'notes'            => bersihkanInput($_POST['catatan'] ?? '')
        ]);

        if ($hasil['sukses']) {
            setNotifikasi('success', $hasil['pesan']);
        } else {
            setNotifikasi('danger', $hasil['pesan']);
        }
        header('Location: index.php?page=reservations');
        exit;
    }

    if ($aksi === 'ubah_status') {
        $id     = (int)($_POST['id'] ?? 0);
        $status = bersihkanInput($_POST['status'] ?? 'Menunggu');
        if ($reservasiClass->ubahStatus($id, $status)) {
            setNotifikasi('success', "Status reservasi berhasil diubah menjadi {$status}.");
        } else {
            setNotifikasi('danger', "Gagal memperbarui status reservasi.");
        }
        header('Location: index.php?page=reservations');
        exit;
    }

    if ($aksi === 'hapus') {
        $id = (int)($_POST['id'] ?? 0);
        if ($reservasiClass->hapus($id)) {
            setNotifikasi('success', 'Reservasi berhasil dihapus/dibatalkan.');
        } else {
            setNotifikasi('danger', 'Gagal menghapus reservasi.');
        }
        header('Location: index.php?page=reservations');
        exit;
    }
}

// 2. Filter & Ambil Data
$filter_tgl    = !empty($_GET['tanggal']) ? trim($_GET['tanggal']) : null;
$filter_status = !empty($_GET['status']) ? trim($_GET['status']) : null;

$daftar_reservasi = $reservasiClass->ambilSemua($filter_tgl, $filter_status);
$daftar_pelanggan = $pelangganClass->ambilSemua();
$daftar_meja_aktif= $db->query("SELECT * FROM restaurant_tables WHERE status = 'Aktif' ORDER BY table_number ASC")->fetchAll();
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-2">
    <div>
        <h1 class="h3 fw-bold mb-1">Reservasi Meja</h1>
        <p class="text-muted mb-0">Kelola pemesanan meja tamu dengan validasi kapasitas dan pencegahan bentrok jadwal.</p>
    </div>
    <button type="button" class="btn btn-warning-custom" data-bs-toggle="modal" data-bs-target="#modalTambahReservasi">
        <i class="fa fa-calendar-plus me-1"></i> Buat Reservasi Baru
    </button>
</div>

<!-- Filter Pencarian -->
<div class="card card-custom mb-4">
    <div class="card-body p-3">
        <form method="GET" action="index.php" class="row g-2 align-items-end">
            <input type="hidden" name="page" value="reservations">
            <div class="col-sm-5 col-md-4">
                <label class="form-label small fw-semibold text-muted mb-1">Filter Tanggal Reservasi</label>
                <input type="date" name="tanggal" class="form-control form-control-sm" value="<?= htmlspecialchars($filter_tgl ?? '') ?>">
            </div>
            <div class="col-sm-4 col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">Filter Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    <option value="Menunggu" <?= $filter_status === 'Menunggu' ? 'selected' : '' ?>>Menunggu</option>
                    <option value="Dikonfirmasi" <?= $filter_status === 'Dikonfirmasi' ? 'selected' : '' ?>>Dikonfirmasi</option>
                    <option value="Selesai" <?= $filter_status === 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                    <option value="Dibatalkan" <?= $filter_status === 'Dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
                </select>
            </div>
            <div class="col-sm-3 col-md-2">
                <button type="submit" class="btn btn-sm btn-primary w-100">
                    <i class="fa fa-filter me-1"></i> Terapkan
                </button>
            </div>
            <?php if (!empty($filter_tgl) || !empty($filter_status)): ?>
                <div class="col-sm-12 col-md-2">
                    <a href="index.php?page=reservations" class="btn btn-sm btn-outline-secondary w-100">
                        <i class="fa fa-sync me-1"></i> Reset
                    </a>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="card card-custom">
    <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
        <span><i class="fa fa-calendar-check me-2 text-primary"></i>Daftar Reservasi (<?= count($daftar_reservasi) ?> Data)</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Pelanggan</th>
                        <th>Meja</th>
                        <th>Jadwal</th>
                        <th>Jumlah Tamu</th>
                        <th>Status</th>
                        <th>Catatan</th>
                        <th class="text-end" style="width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($daftar_reservasi)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">Tidak ditemukan data reservasi yang sesuai.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($daftar_reservasi as $no => $r): ?>
                            <tr>
                                <td><?= $no + 1 ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?= avatarInisial($r['nama_pelanggan']) ?>
                                        <div>
                                            <div class="fw-semibold text-dark"><?= htmlspecialchars($r['nama_pelanggan']) ?></div>
                                            <div class="small text-muted"><i class="fa fa-phone me-1"></i><?= htmlspecialchars($r['telepon_pelanggan']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-soft-secondary font-monospace"><?= htmlspecialchars($r['nomor_meja']) ?></span>
                                    <div class="small text-muted" style="font-size: 0.75rem;">Kapasitas <?= $r['kapasitas_meja'] ?> Kursi</div>
                                </td>
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars($r['reservation_date']) ?></div>
                                    <div class="small text-primary fw-medium"><?= substr($r['reservation_time'], 0, 5) ?> WIB</div>
                                </td>
                                <td>
                                    <i class="fa fa-users text-muted me-1"></i><?= (int)$r['guest_count'] ?> orang
                                </td>
                                <td><?= badgeStatus($r['status']) ?></td>
                                <td class="small text-muted" style="max-width: 180px;">
                                    <?= !empty($r['notes']) ? htmlspecialchars($r['notes']) : '-' ?>
                                </td>
                                <td class="text-end">
                                    <!-- Form Ubah Status Cepat -->
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                                            Status
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <form method="POST" action="index.php?page=reservations">
                                                    <input type="hidden" name="aksi" value="ubah_status">
                                                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                                    <input type="hidden" name="status" value="Dikonfirmasi">
                                                    <button type="submit" class="dropdown-item">Set Dikonfirmasi</button>
                                                </form>
                                            </li>
                                            <li>
                                                <form method="POST" action="index.php?page=reservations">
                                                    <input type="hidden" name="aksi" value="ubah_status">
                                                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                                    <input type="hidden" name="status" value="Selesai">
                                                    <button type="submit" class="dropdown-item">Set Selesai</button>
                                                </form>
                                            </li>
                                            <li>
                                                <form method="POST" action="index.php?page=reservations">
                                                    <input type="hidden" name="aksi" value="ubah_status">
                                                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                                    <input type="hidden" name="status" value="Dibatalkan">
                                                    <button type="submit" class="dropdown-item text-danger">Batalkan</button>
                                                </form>
                                            </li>
                                        </ul>

                                        <form method="POST" action="index.php?page=reservations" class="d-inline ms-1" onsubmit="return confirm('Hapus reservasi ini?');">
                                            <input type="hidden" name="aksi" value="hapus">
                                            <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                            <button type="submit" class="btn btn-outline-danger" title="Hapus">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Reservasi (Modern Style) -->
<div class="modal fade" id="modalTambahReservasi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <form method="POST" action="index.php?page=reservations">
                <input type="hidden" name="aksi" value="tambah">
                <input type="hidden" name="tipe_pelanggan" id="resInputTipe" value="baru">
                
                <div class="modal-header bg-dark text-white py-3 px-4" style="border-top-left-radius: 16px; border-top-right-radius: 16px;">
                    <div>
                        <h5 class="modal-title fw-bold mb-0">
                            <i class="fa fa-calendar-plus text-warning me-2"></i>Buat Reservasi Baru
                        </h5>
                        <p class="text-white-50 small mb-0">Atur pemesanan tempat meja tamu dengan mudah.</p>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body p-4">
                    <!-- Segmented Control Pelanggan -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-uppercase text-muted mb-2">Metode Pemesan:</label>
                        <div class="segmented-nav">
                            <button type="button" class="segmented-btn active" id="resBtnBaru" onclick="setResTipe('baru')">
                                <i class="fa fa-bolt text-warning me-1"></i> Tamu Baru (Input Cepat)
                            </button>
                            <button type="button" class="segmented-btn" id="resBtnTerdaftar" onclick="setResTipe('terdaftar')">
                                <i class="fa fa-user-check text-primary me-1"></i> Pelanggan Terdaftar
                            </button>
                        </div>
                    </div>

                    <!-- Tamu Baru Cepat -->
                    <div id="resPanelBaru" class="p-3 mb-3 bg-light rounded-3 border">
                        <div class="mb-2">
                            <label class="form-label small fw-semibold text-dark mb-1">Nama Pemesan <span class="text-danger">*</span></label>
                            <input type="text" name="nama_tamu" id="resNamaTamu" class="form-control form-control-sm" 
                                   placeholder="Contoh: Dimas, Kak Favian, Rombongan..." value="Dimas Faqih" required>
                        </div>
                        <div>
                            <label class="form-label small fw-semibold text-dark mb-1">No. WhatsApp / HP</label>
                            <input type="text" name="telepon_tamu" class="form-control form-control-sm" placeholder="Contoh: 081278901234">
                        </div>
                    </div>

                    <!-- Pelanggan Terdaftar -->
                    <div id="resPanelTerdaftar" class="p-3 mb-3 bg-light rounded-3 border d-none">
                        <label class="form-label small fw-semibold text-dark mb-1">Pilih Pelanggan <span class="text-danger">*</span></label>
                        <select name="id_pelanggan" id="resSelectPelanggan" class="form-select form-select-sm">
                            <option value="">-- Pilih Pelanggan --</option>
                            <?php foreach ($daftar_pelanggan as $c): ?>
                                <option value="<?= $c['id'] ?>">
                                    <?= htmlspecialchars($c['name']) ?> (<?= htmlspecialchars($c['phone']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-uppercase text-muted mb-1">Pilih Meja Restoran <span class="text-danger">*</span></label>
                        <select name="id_meja" class="form-select" required>
                            <option value="">-- Pilih Meja Aktif --</option>
                            <?php foreach ($daftar_meja_aktif as $tb): ?>
                                <option value="<?= $tb['id'] ?>">
                                    Meja <?= htmlspecialchars($tb['table_number']) ?> &bull; Kapasitas <?= $tb['capacity'] ?> orang
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-uppercase text-muted mb-1">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-uppercase text-muted mb-1">Jam Datang <span class="text-danger">*</span></label>
                            <input type="time" name="waktu" class="form-control" value="12:00" required>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-uppercase text-muted mb-1">Jumlah Tamu <span class="text-danger">*</span></label>
                            <input type="number" name="tamu" class="form-control" min="1" max="50" value="2" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-uppercase text-muted mb-1">Status Awal</label>
                            <select name="status" class="form-select" required>
                                <option value="Menunggu" selected>Menunggu</option>
                                <option value="Dikonfirmasi">Dikonfirmasi</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-uppercase text-muted mb-1">Catatan Khusus <span class="text-muted text-lowercase fw-normal">(Opsional)</span></label>
                        <textarea name="catatan" class="form-control" rows="2" placeholder="Contoh: Dekat colokan, bawa kue ultah..."></textarea>
                    </div>
                </div>

                <div class="modal-footer bg-light px-4 py-3" style="border-bottom-left-radius: 16px; border-bottom-right-radius: 16px;">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning-custom px-4">
                        <i class="fa fa-calendar-check me-1"></i> Simpan Reservasi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function setResTipe(tipe) {
    document.getElementById('resInputTipe').value = tipe;
    const btnBaru = document.getElementById('resBtnBaru');
    const btnTerdaftar = document.getElementById('resBtnTerdaftar');
    const panelBaru = document.getElementById('resPanelBaru');
    const panelTerdaftar = document.getElementById('resPanelTerdaftar');
    const selectPelanggan = document.getElementById('resSelectPelanggan');
    const inputNama = document.getElementById('resNamaTamu');

    if (tipe === 'baru') {
        btnBaru.classList.add('active');
        btnTerdaftar.classList.remove('active');
        panelBaru.classList.remove('d-none');
        panelTerdaftar.classList.add('d-none');
        selectPelanggan.removeAttribute('required');
        inputNama.setAttribute('required', 'required');
    } else {
        btnTerdaftar.classList.add('active');
        btnBaru.classList.remove('active');
        panelTerdaftar.classList.remove('d-none');
        panelBaru.classList.add('d-none');
        inputNama.removeAttribute('required');
        selectPelanggan.setAttribute('required', 'required');
    }
}
</script>
