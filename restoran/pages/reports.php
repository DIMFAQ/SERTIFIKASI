<?php
/**
 * Halaman Laporan Operasional Restoran
 */
$tgl_mulai   = !empty($_GET['tgl_mulai']) ? trim($_GET['tgl_mulai']) : date('Y-m-01');
$tgl_selesai = !empty($_GET['tgl_selesai']) ? trim($_GET['tgl_selesai']) : date('Y-m-d');

// Ambil data laporan menggunakan method class
$laporan_reservasi = $reservasiClass->ambilSemua($tgl_mulai, null);
$laporan_pesanan   = $pesananClass->ambilSemua($tgl_mulai, $tgl_selesai, null);

$total_jml_reservasi = count($laporan_reservasi);
$total_jml_pesanan   = count($laporan_pesanan);
$total_omset         = $pesananClass->hitungOmset($tgl_mulai, $tgl_selesai);
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-2 no-print">
    <div>
        <h1 class="h3 fw-bold mb-1">Laporan Operasional</h1>
        <p class="text-muted mb-0">Rekapitulasi data reservasi meja dan transaksi pesanan berdasarkan rentang tanggal.</p>
    </div>
    <button type="button" onclick="window.print()" class="btn btn-outline-dark">
        <i class="fa fa-print me-1"></i> Cetak Laporan
    </button>
</div>

<!-- Header Cetak Khusus Kertas Printer -->
<div class="d-none d-print-block mb-4 text-center">
    <h2 class="fw-bold mb-1">DAPOER DIMAS - LAPORAN OPERASIONAL</h2>
    <p class="text-muted mb-0">Periode: <?= date('d M Y', strtotime($tgl_mulai)) ?> s/d <?= date('d M Y', strtotime($tgl_selesai)) ?></p>
    <hr>
</div>

<!-- Filter Rentang Tanggal -->
<div class="card card-custom mb-4 no-print">
    <div class="card-body p-3">
        <form method="GET" action="index.php" class="row g-2 align-items-end">
            <input type="hidden" name="page" value="reports">
            <div class="col-sm-5 col-md-4">
                <label class="form-label small fw-semibold text-muted mb-1">Dari Tanggal</label>
                <input type="date" name="tgl_mulai" class="form-control form-control-sm" value="<?= htmlspecialchars($tgl_mulai) ?>" required>
            </div>
            <div class="col-sm-5 col-md-4">
                <label class="form-label small fw-semibold text-muted mb-1">Sampai Tanggal</label>
                <input type="date" name="tgl_selesai" class="form-control form-control-sm" value="<?= htmlspecialchars($tgl_selesai) ?>" required>
            </div>
            <div class="col-sm-2 col-md-4">
                <button type="submit" class="btn btn-sm btn-primary w-100">
                    <i class="fa fa-filter me-1"></i> Tampilkan Laporan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- 3 Kartu Ringkasan Laporan -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="kpi-card d-flex align-items-center justify-content-between">
            <div>
                <div class="text-muted small fw-medium text-uppercase">Jumlah Reservasi</div>
                <div class="h3 fw-bold mb-0 mt-1"><?= $total_jml_reservasi ?></div>
                <div class="small text-muted mt-1">Pada periode ini</div>
            </div>
            <div class="kpi-icon bg-info bg-opacity-10 text-info">
                <i class="fa fa-calendar-check"></i>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="kpi-card d-flex align-items-center justify-content-between">
            <div>
                <div class="text-muted small fw-medium text-uppercase">Jumlah Pesanan</div>
                <div class="h3 fw-bold mb-0 mt-1"><?= $total_jml_pesanan ?></div>
                <div class="small text-muted mt-1">Transaksi tercatat</div>
            </div>
            <div class="kpi-icon bg-warning bg-opacity-10 text-warning">
                <i class="fa fa-receipt"></i>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="kpi-card d-flex align-items-center justify-content-between">
            <div>
                <div class="text-muted small fw-medium text-uppercase">Total Nilai Pesanan</div>
                <div class="h3 fw-bold text-success mb-0 mt-1"><?= formatRupiah($total_omset) ?></div>
                <div class="small text-muted mt-1">Akumulasi pendapatan</div>
            </div>
            <div class="kpi-icon bg-success bg-opacity-10 text-success">
                <i class="fa fa-wallet"></i>
            </div>
        </div>
    </div>
</div>

<!-- Bagian 1: Laporan Reservasi -->
<div class="card card-custom mb-4">
    <div class="card-header card-header-custom">
        <i class="fa fa-calendar-alt me-2 text-primary"></i>Laporan Reservasi Meja (Periode: <?= htmlspecialchars($tgl_mulai) ?> s/d <?= htmlspecialchars($tgl_selesai) ?>)
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Jadwal</th>
                        <th>Nama Pelanggan</th>
                        <th>Nomor Meja</th>
                        <th>Tamu</th>
                        <th>Status</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($laporan_reservasi)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Tidak ada data reservasi pada periode ini.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($laporan_reservasi as $idx => $r): ?>
                            <tr>
                                <td><?= $idx + 1 ?></td>
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars($r['reservation_date']) ?></div>
                                    <div class="small text-muted"><?= substr($r['reservation_time'], 0, 5) ?> WIB</div>
                                </td>
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars($r['nama_pelanggan']) ?></div>
                                    <div class="small text-muted"><?= htmlspecialchars($r['telepon_pelanggan']) ?></div>
                                </td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($r['nomor_meja']) ?></span></td>
                                <td><?= $r['guest_count'] ?> orang</td>
                                <td><?= badgeStatus($r['status']) ?></td>
                                <td class="small text-muted"><?= !empty($r['notes']) ? htmlspecialchars($r['notes']) : '-' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Bagian 2: Laporan Pesanan -->
<div class="card card-custom mb-4">
    <div class="card-header card-header-custom">
        <i class="fa fa-receipt me-2 text-warning"></i>Laporan Transaksi Pesanan (Periode: <?= htmlspecialchars($tgl_mulai) ?> s/d <?= htmlspecialchars($tgl_selesai) ?>)
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom mb-0">
                <thead>
                    <tr>
                        <th style="width: 70px;">ID</th>
                        <th>Waktu Pesanan</th>
                        <th>Nama Pelanggan</th>
                        <th>Status</th>
                        <th class="text-end">Total Nilai Tagihan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($laporan_pesanan)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">Tidak ada data pesanan pada periode ini.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($laporan_pesanan as $o): ?>
                            <tr>
                                <td class="fw-bold text-muted">#<?= $o['id'] ?></td>
                                <td><?= date('d M Y H:i', strtotime($o['order_date'])) ?> WIB</td>
                                <td class="fw-semibold"><?= htmlspecialchars($o['nama_pelanggan']) ?></td>
                                <td><?= badgeStatus($o['status']) ?></td>
                                <td class="text-end fw-bold text-dark"><?= formatRupiah((float)$o['total_amount']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr class="table-light">
                        <th colspan="4" class="text-end fw-bold">TOTAL OMSET KESELURUHAN:</th>
                        <th class="text-end fw-bold fs-6 text-success"><?= formatRupiah($total_omset) ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
