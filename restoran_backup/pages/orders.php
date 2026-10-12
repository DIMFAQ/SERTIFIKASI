<?php
/**
 * Halaman Manajemen Pesanan Restoran
 * Menggunakan class Order dari classes/Order.php
 */

// 1. Tangani Aksi Form POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';

    if ($aksi === 'buat_pesanan') {
        $tipe_pelanggan = $_POST['tipe_pelanggan'] ?? 'baru';
        $id_reservasi   = !empty($_POST['id_reservasi']) ? (int)$_POST['id_reservasi'] : null;
        $items          = $_POST['items'] ?? [];

        if ($tipe_pelanggan === 'terdaftar') {
            $id_pelanggan = (int)($_POST['id_pelanggan'] ?? 0);
            if ($id_pelanggan <= 0) {
                setNotifikasi('danger', 'Silakan pilih pelanggan terdaftar yang memesan.');
                header('Location: index.php?page=orders');
                exit;
            }
        } else {
            // Tamu Langsung / Walk-in / Input Cepat Tanpa Ribet
            $nama_tamu = bersihkanInput($_POST['nama_tamu'] ?? '');
            if (empty($nama_tamu)) {
                $nama_tamu = 'Tamu Walk-in #' . rand(100, 999);
            }
            $telepon_tamu = bersihkanInput($_POST['telepon_tamu'] ?? '');
            if (empty($telepon_tamu)) {
                $telepon_tamu = '-';
            }

            // Daftarkan pelanggan baru secara otomatis dan ambil ID-nya
            $id_baru = $pelangganClass->tambah($nama_tamu, $telepon_tamu, null);
            if (!$id_baru) {
                setNotifikasi('danger', 'Gagal mendaftarkan data tamu.');
                header('Location: index.php?page=orders');
                exit;
            }
            $id_pelanggan = (int)$id_baru;
        }

        $hasil = $pesananClass->buatPesanan($id_pelanggan, $id_reservasi, $items);
        if ($hasil['sukses']) {
            setNotifikasi('success', $hasil['pesan']);
            header('Location: index.php?page=orders&view_id=' . $hasil['id']);
        } else {
            setNotifikasi('danger', $hasil['pesan']);
            header('Location: index.php?page=orders');
        }
        exit;
    }

    if ($aksi === 'ubah_status') {
        $id     = (int)($_POST['id'] ?? 0);
        $status = bersihkanInput($_POST['status'] ?? 'Diproses');
        if ($pesananClass->ubahStatus($id, $status)) {
            setNotifikasi('success', "Status pesanan #{$id} diubah menjadi {$status}.");
        } else {
            setNotifikasi('danger', "Gagal memperbarui status pesanan.");
        }
        header('Location: index.php?page=orders&view_id=' . $id);
        exit;
    }

    if ($aksi === 'hapus') {
        $id = (int)($_POST['id'] ?? 0);
        if ($pesananClass->hapus($id)) {
            setNotifikasi('success', "Pesanan #{$id} berhasil dihapus.");
        } else {
            setNotifikasi('danger', "Gagal menghapus pesanan.");
        }
        header('Location: index.php?page=orders');
        exit;
    }
}

// 2. Ambil Data
$filter_status  = !empty($_GET['status']) ? trim($_GET['status']) : null;
$daftar_pesanan = $pesananClass->ambilSemua(null, null, $filter_status);
$daftar_pelanggan = $pelangganClass->ambilSemua();
$daftar_reservasi = $reservasiClass->ambilSemua();
$daftar_menu_tersedia = $db->query("SELECT * FROM menu_items WHERE is_available = 1 ORDER BY name ASC")->fetchAll();

$view_id = !empty($_GET['view_id']) ? (int)$_GET['view_id'] : null;
$detail_pesanan = $view_id ? $pesananClass->ambilDetail($view_id) : null;
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-2 no-print">
    <div>
        <h1 class="h3 fw-bold mb-1">Pesanan Menu</h1>
        <p class="text-muted mb-0">Kelola pemesanan hidangan pelanggan dengan subtotal dan total otomatis.</p>
    </div>
    <button type="button" class="btn btn-warning-custom" data-bs-toggle="modal" data-bs-target="#modalBuatPesanan">
        <i class="fa fa-cart-plus me-1"></i> Buat Pesanan Baru
    </button>
</div>

<!-- Kotak Detail / Struk Nota Pesanan jika dipilih -->
<?php if ($detail_pesanan): ?>
    <div class="row justify-content-center mb-4">
        <div class="col-lg-8 col-xl-7">
            <div class="card card-custom nota-struk-box shadow-sm">
                <!-- Kop Resmi Restoran (Tampil di Layar & Saat Cetak) -->
                <div class="text-center pb-3 border-bottom mb-3">
                    <div class="d-flex align-items-center justify-content-center gap-2 mb-1">
                        <span class="badge bg-warning text-dark px-2 py-1"><i class="fa fa-utensils"></i></span>
                        <h3 class="fw-bold mb-0 text-dark" style="letter-spacing: -0.02em;">DAPOER DIMAS</h3>
                    </div>
                    <p class="text-muted small mb-0">Aneka Masakan & Kuliner Nusantara</p>
                    <p class="text-muted small mb-0">Jl. Pramuka No. 12 • Telp/WA: 0812-7890-1234</p>
                    <div class="d-inline-block border border-dark px-3 py-1 mt-2 fw-bold small text-uppercase" style="letter-spacing: 0.05em;">
                        STRUK BUKTI PEMESANAN #<?= $detail_pesanan['id'] ?>
                    </div>
                </div>

                <!-- Informasi Pelanggan & Transaksi -->
                <div class="row g-2 mb-3 small">
                    <div class="col-6">
                        <div><span class="text-muted">No. Nota :</span> <strong class="text-dark">#<?= $detail_pesanan['id'] ?></strong></div>
                        <div><span class="text-muted">Pelanggan:</span> <strong class="text-dark"><?= htmlspecialchars($detail_pesanan['nama_pelanggan']) ?></strong></div>
                        <div><span class="text-muted">Telepon  :</span> <?= htmlspecialchars($detail_pesanan['telepon_pelanggan']) ?></div>
                    </div>
                    <div class="col-6 text-end">
                        <div><span class="text-muted">Waktu   :</span> <?= date('d M Y H:i', strtotime($detail_pesanan['order_date'])) ?> WIB</div>
                        <div><span class="text-muted">Layanan :</span> <?= !empty($detail_pesanan['reservation_id']) ? 'Reservasi Meja #' . $detail_pesanan['reservation_id'] : 'Langsung (Dine-in / Walk-in)' ?></div>
                        <div><span class="text-muted">Status  :</span> <span class="fw-bold"><?= strtoupper($detail_pesanan['status']) ?></span></div>
                    </div>
                </div>

                <!-- Tabel Rincian Menu -->
                <div class="table-responsive mb-3">
                    <table class="table table-sm table-bordered mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 35px;" class="text-center">No</th>
                                <th>Menu Pesanan</th>
                                <th class="text-end" style="width: 110px;">Harga</th>
                                <th class="text-center" style="width: 55px;">Qty</th>
                                <th class="text-end" style="width: 120px;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($detail_pesanan['items'] as $idx => $item): ?>
                                <tr>
                                    <td class="text-center small"><?= $idx + 1 ?></td>
                                    <td class="fw-semibold text-dark"><?= htmlspecialchars($item['menu_name']) ?></td>
                                    <td class="text-end small"><?= formatRupiah((float)$item['price']) ?></td>
                                    <td class="text-center fw-bold"><?= (int)$item['quantity'] ?></td>
                                    <td class="text-end fw-bold text-dark"><?= formatRupiah((float)$item['subtotal']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-light">
                                <th colspan="4" class="text-end fw-bold">TOTAL PEMESANAN:</th>
                                <th class="text-end fs-6 text-dark fw-bold"><?= formatRupiah((float)$detail_pesanan['total_amount']) ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Catatan Penutup Struk -->
                <div class="text-center pt-2 border-top small text-muted">
                    <p class="mb-0 fw-semibold text-dark">Terima Kasih Atas Kunjungan Anda!</p>
                    <p class="mb-0 text-secondary" style="font-size: 0.8rem;">Struk ini merupakan bukti pembayaran resmi yang sah.</p>
                </div>

                <!-- Tombol Aksi di Layar (Otomatis Disembunyikan Saat Cetak) -->
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mt-4 pt-3 border-top no-print">
                    <form method="POST" action="index.php?page=orders" class="d-flex align-items-center gap-2">
                        <input type="hidden" name="aksi" value="ubah_status">
                        <input type="hidden" name="id" value="<?= $detail_pesanan['id'] ?>">
                        <label class="small fw-semibold text-muted text-nowrap">Ubah Status:</label>
                        <select name="status" class="form-select form-select-sm" style="width: 140px;" onchange="this.form.submit()">
                            <option value="Diproses" <?= $detail_pesanan['status'] === 'Diproses' ? 'selected' : '' ?>>Diproses</option>
                            <option value="Selesai" <?= $detail_pesanan['status'] === 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                            <option value="Dibatalkan" <?= $detail_pesanan['status'] === 'Dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
                        </select>
                    </form>
                    <div class="d-flex gap-2">
                        <a href="index.php?page=orders" class="btn btn-outline-secondary btn-sm">
                            <i class="fa fa-times me-1"></i> Tutup Rincian
                        </a>
                        <button onclick="window.print()" class="btn btn-dark btn-sm">
                            <i class="fa fa-print me-1"></i> Cetak Bukti Nota
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Filter Status -->
<div class="card card-custom mb-4 <?= $detail_pesanan ? 'no-print' : '' ?>">
    <div class="card-body p-3">
        <form method="GET" action="index.php" class="row g-2 align-items-end">
            <input type="hidden" name="page" value="orders">
            <div class="col-sm-6 col-md-4">
                <label class="form-label small fw-semibold text-muted mb-1">Filter Berdasarkan Status</label>
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Semua Status Pesanan</option>
                    <option value="Diproses" <?= $filter_status === 'Diproses' ? 'selected' : '' ?>>Diproses</option>
                    <option value="Selesai" <?= $filter_status === 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                    <option value="Dibatalkan" <?= $filter_status === 'Dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
                </select>
            </div>
            <?php if (!empty($filter_status)): ?>
                <div class="col-sm-6 col-md-2">
                    <a href="index.php?page=orders" class="btn btn-sm btn-outline-secondary w-100">
                        <i class="fa fa-sync me-1"></i> Reset
                    </a>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="card card-custom <?= $detail_pesanan ? 'no-print' : '' ?>">
    <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
        <span><i class="fa fa-receipt me-2 text-primary"></i>Daftar Riwayat Pesanan (<?= count($daftar_pesanan) ?> Transaksi)</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom mb-0">
                <thead>
                    <tr>
                        <th style="width: 70px;">ID</th>
                        <th>Pelanggan</th>
                        <th>Waktu Pesanan</th>
                        <th>Reservasi Terkait</th>
                        <th>Total Tagihan</th>
                        <th>Status</th>
                        <th class="text-end" style="width: 150px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($daftar_pesanan)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Belum ada riwayat transaksi pesanan.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($daftar_pesanan as $o): ?>
                            <tr>
                                <td class="fw-bold text-muted">#<?= $o['id'] ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?= avatarInisial($o['nama_pelanggan']) ?>
                                        <div>
                                            <div class="fw-semibold text-dark"><?= htmlspecialchars($o['nama_pelanggan']) ?></div>
                                            <div class="text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars($o['telepon_pelanggan']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><?= date('d M Y H:i', strtotime($o['order_date'])) ?> WIB</td>
                                <td>
                                    <?php if (!empty($o['reservation_id'])): ?>
                                        <span class="badge bg-info text-dark">
                                            <i class="fa fa-calendar-check me-1"></i>Res #<?= $o['reservation_id'] ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted small fst-italic">Langsung / Walk-in</span>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-bold text-dark fs-6"><?= formatRupiah((float)$o['total_amount']) ?></td>
                                <td><?= badgeStatus($o['status']) ?></td>
                                <td class="text-end">
                                    <a href="index.php?page=orders&view_id=<?= $o['id'] ?>" class="btn btn-sm btn-outline-primary me-1" title="Lihat Detail">
                                        <i class="fa fa-eye"></i> Detail
                                    </a>
                                    <form method="POST" action="index.php?page=orders" class="d-inline" onsubmit="return confirm('Hapus pesanan #<?= $o['id'] ?>?');">
                                        <input type="hidden" name="aksi" value="hapus">
                                        <input type="hidden" name="id" value="<?= $o['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Buat Pesanan Baru (Modern POS Style) -->
<div class="modal fade" id="modalBuatPesanan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <form method="POST" action="index.php?page=orders">
                <input type="hidden" name="aksi" value="buat_pesanan">
                <input type="hidden" name="tipe_pelanggan" id="inputTipePelanggan" value="baru">
                
                <div class="modal-header bg-dark text-white py-3 px-4" style="border-top-left-radius: 16px; border-top-right-radius: 16px;">
                    <div>
                        <h5 class="modal-title fw-bold mb-0">
                            <i class="fa fa-cash-register text-warning me-2"></i>Kasir & Buat Pesanan Baru
                        </h5>
                        <p class="text-white-50 small mb-0">Input cepat pesanan tamu tanpa perlu daftar pelanggan manual.</p>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body p-4">
                    <!-- Segmented Control Pemilihan Pelanggan -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-uppercase text-muted mb-2">Metode Pelanggan:</label>
                        <div class="segmented-nav">
                            <button type="button" class="segmented-btn active" id="btnTipeBaru" onclick="setTipePelanggan('baru')">
                                <i class="fa fa-bolt text-warning me-1"></i> Tamu Langsung / Walk-In (Instan)
                            </button>
                            <button type="button" class="segmented-btn" id="btnTipeTerdaftar" onclick="setTipePelanggan('terdaftar')">
                                <i class="fa fa-user-check text-primary me-1"></i> Pelanggan Terdaftar
                            </button>
                        </div>
                    </div>

                    <!-- Pilihan 1: Tamu Langsung / Walk-in -->
                    <div id="panelTamuBaru" class="p-3 mb-3 bg-light rounded-3 border">
                        <div class="row g-2">
                            <div class="col-md-7">
                                <label class="form-label small fw-semibold text-dark mb-1">
                                    Nama Pemesan / Meja <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="nama_tamu" id="inputNamaTamu" class="form-control form-control-sm" 
                                       placeholder="Ketik nama atau nomor meja..." value="Tamu Meja 01">
                                <div class="mt-2 d-flex flex-wrap gap-1">
                                    <span class="quick-chip" onclick="pilihChip('Tamu Meja 01')">Meja 01</span>
                                    <span class="quick-chip" onclick="pilihChip('Tamu Meja 02')">Meja 02</span>
                                    <span class="quick-chip" onclick="pilihChip('Tamu Meja 03')">Meja 03</span>
                                    <span class="quick-chip" onclick="pilihChip('Take Away / Bungkus')">Bungkus / Take Away</span>
                                    <span class="quick-chip" onclick="pilihChip('Dimas')">Dimas</span>
                                    <span class="quick-chip" onclick="pilihChip('Favian')">Favian</span>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label small fw-semibold text-dark mb-1">
                                    No. WhatsApp / HP <span class="text-muted">(Opsional)</span>
                                </label>
                                <input type="text" name="telepon_tamu" class="form-control form-control-sm" placeholder="Contoh: 0812...">
                                <div class="small text-muted mt-1" style="font-size: 0.75rem;">Otomatis tersimpan ke buku pelanggan.</div>
                            </div>
                        </div>
                    </div>

                    <!-- Pilihan 2: Pelanggan Terdaftar -->
                    <div id="panelPelangganTerdaftar" class="p-3 mb-3 bg-light rounded-3 border d-none">
                        <label class="form-label small fw-semibold text-dark mb-1">
                            Pilih Pelanggan <span class="text-danger">*</span>
                        </label>
                        <select name="id_pelanggan" id="selectPelanggan" class="form-select form-select-sm">
                            <option value="">-- Pilih dari Daftar Pelanggan --</option>
                            <?php foreach ($daftar_pelanggan as $c): ?>
                                <option value="<?= $c['id'] ?>">
                                    <?= htmlspecialchars($c['name']) ?> (<?= htmlspecialchars($c['phone']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Hubungkan Reservasi (Opsional) -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-uppercase text-muted mb-1">
                            Hubungkan Reservasi Meja <span class="text-muted text-lowercase fw-normal">(Opsional)</span>
                        </label>
                        <select name="id_reservasi" class="form-select form-select-sm">
                            <option value="">-- Tanpa Reservasi (Langsung / Walk-in) --</option>
                            <?php foreach ($daftar_reservasi as $res): ?>
                                <option value="<?= $res['id'] ?>">
                                    Reservasi #<?= $res['id'] ?> &bull; <?= htmlspecialchars($res['nama_pelanggan']) ?> (<?= $res['reservation_date'] ?> - <?= substr($res['reservation_time'], 0, 5) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Panel Pemilihan Menu Modern POS -->
                    <div class="border-top pt-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label small fw-bold text-uppercase text-muted mb-0">
                                <i class="fa fa-utensils me-1 text-warning"></i>Pilih Hidangan Menu:
                            </label>
                            <span class="badge bg-light text-muted border small" id="badgeItemCount">0 Menu Dipilih</span>
                        </div>

                        <!-- Live Search Menu -->
                        <div class="position-relative mb-3">
                            <input type="text" id="cariMenu" onkeyup="filterMenu()" 
                                   class="form-control form-control-sm ps-4" 
                                   placeholder="Cari menu makanan atau minuman...">
                            <i class="fa fa-search text-muted position-absolute" style="left: 12px; top: 9px; font-size: 0.8rem;"></i>
                        </div>

                        <!-- Grid Menu Modern -->
                        <div class="pos-menu-scroll" style="max-height: 290px; overflow-y: auto; padding-right: 4px;">
                            <div class="row g-2" id="kontainerMenu">
                                <?php foreach ($daftar_menu_tersedia as $m): ?>
                                    <div class="col-md-6 menu-card-wrapper" data-nama="<?= strtolower(htmlspecialchars($m['name'])) ?>">
                                        <div class="pos-menu-item d-flex justify-content-between align-items-center h-100" id="card_menu_<?= $m['id'] ?>">
                                            <div class="me-2 flex-grow-1">
                                                <div class="fw-bold text-dark small mb-0"><?= htmlspecialchars($m['name']) ?></div>
                                                <div class="text-warning-emphasis small fw-bold"><?= formatRupiah((float)$m['price']) ?></div>
                                                <div class="text-muted small item-subtotal-text" id="subtotal_<?= $m['id'] ?>" style="font-size: 0.75rem;">
                                                    Subtotal: Rp 0
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center gap-1">
                                                <button type="button" class="qty-control-btn" onclick="ubahQty(<?= $m['id'] ?>, -1)">&minus;</button>
                                                <input type="number" 
                                                       name="items[<?= $m['id'] ?>][quantity]" 
                                                       id="qty_<?= $m['id'] ?>"
                                                       class="form-control form-control-sm text-center fw-bold input-qty" 
                                                       style="width: 44px; padding: 4px;"
                                                       min="0" 
                                                       value="0"
                                                       data-id="<?= $m['id'] ?>"
                                                       data-harga="<?= $m['price'] ?>"
                                                       onchange="hitungOtomatis()" 
                                                       onkeyup="hitungOtomatis()">
                                                <button type="button" class="qty-control-btn btn-plus" onclick="ubahQty(<?= $m['id'] ?>, 1)">&plus;</button>
                                                <input type="hidden" name="items[<?= $m['id'] ?>][menu_item_id]" value="<?= $m['id'] ?>">
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Sticky Ringkasan Total Tagihan -->
                        <div class="card bg-white border border-2 border-warning-subtle shadow-sm p-3 mt-3 rounded-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="small text-muted text-uppercase fw-semibold">Estimasi Total Tagihan</div>
                                    <div class="small text-muted" id="totalPorsiLabel">0 porsi dipesan</div>
                                </div>
                                <div class="text-end">
                                    <div class="fs-3 fw-bold text-dark" id="totalSemua">Rp 0</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light px-4 py-3" style="border-bottom-left-radius: 16px; border-bottom-right-radius: 16px;">
                    <button type="button" class="btn btn-outline-secondary px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning-custom px-4 py-2">
                        <i class="fa fa-check-circle me-1"></i> Simpan & Proses Pesanan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function setTipePelanggan(tipe) {
    document.getElementById('inputTipePelanggan').value = tipe;
    const btnBaru = document.getElementById('btnTipeBaru');
    const btnTerdaftar = document.getElementById('btnTipeTerdaftar');
    const panelBaru = document.getElementById('panelTamuBaru');
    const panelTerdaftar = document.getElementById('panelPelangganTerdaftar');
    const selectPelanggan = document.getElementById('selectPelanggan');
    const inputNama = document.getElementById('inputNamaTamu');

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

function pilihChip(teks) {
    document.getElementById('inputNamaTamu').value = teks;
}

function ubahQty(id, delta) {
    const el = document.getElementById('qty_' + id);
    if (!el) return;
    let val = parseInt(el.value) || 0;
    val = Math.max(0, val + delta);
    el.value = val;
    hitungOtomatis();
}

function filterMenu() {
    const q = document.getElementById('cariMenu').value.toLowerCase();
    document.querySelectorAll('.menu-card-wrapper').forEach(wrapper => {
        const nama = wrapper.dataset.nama || '';
        if (nama.includes(q)) {
            wrapper.style.display = '';
        } else {
            wrapper.style.display = 'none';
        }
    });
}

function hitungOtomatis() {
    let total = 0;
    let totalPorsi = 0;
    let menuDipilih = 0;

    document.querySelectorAll('.input-qty').forEach(input => {
        const id = input.dataset.id;
        const qty = parseInt(input.value) || 0;
        const harga = parseFloat(input.dataset.harga) || 0;
        const subtotal = qty * harga;
        total += subtotal;
        totalPorsi += qty;

        const card = document.getElementById('card_menu_' + id);
        if (card) {
            if (qty > 0) {
                card.classList.add('active-item');
                menuDipilih++;
            } else {
                card.classList.remove('active-item');
            }
        }

        const elSubtotal = document.getElementById('subtotal_' + id);
        if (elSubtotal) {
            elSubtotal.textContent = 'Subtotal: Rp ' + subtotal.toLocaleString('id-ID');
        }
    });

    document.getElementById('totalSemua').textContent = 'Rp ' + total.toLocaleString('id-ID');
    document.getElementById('totalPorsiLabel').textContent = totalPorsi + ' porsi dipesan';
    document.getElementById('badgeItemCount').textContent = menuDipilih + ' Menu Dipilih';
}
</script>
