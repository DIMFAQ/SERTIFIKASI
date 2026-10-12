<?php
/**
 * Halaman Dashboard Utama
 */
$total_pelanggan = $pelangganClass->hitungTotal();
$total_meja      = (int)$db->query("SELECT COUNT(*) FROM restaurant_tables")->fetchColumn();
$meja_aktif      = (int)$db->query("SELECT COUNT(*) FROM restaurant_tables WHERE status = 'Aktif'")->fetchColumn();
$total_reservasi = $reservasiClass->hitungTotal();
$total_pesanan   = $pesananClass->hitungTotal();

$reservasi_terbaru = $reservasiClass->ambilTerbaru(5);
$pesanan_terbaru   = $pesananClass->ambilTerbaru(5);
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-2">
    <div>
        <h1 class="h3 fw-bold mb-1">Dashboard Dapoer Dimas</h1>
        <p class="text-muted mb-0">Ringkasan operasional data pelanggan, ketersediaan meja, reservasi, dan pesanan.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="index.php?page=reservations" class="btn btn-outline-primary">
            <i class="fa fa-calendar-plus me-1"></i> Reservasi Baru
        </a>
        <a href="index.php?page=orders" class="btn btn-warning-custom">
            <i class="fa fa-plus-circle me-1"></i> Buat Pesanan
        </a>
    </div>
</div>

<!-- 4 Kartu KPI Ringkasan -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="kpi-card d-flex align-items-center justify-content-between">
            <div>
                <div class="text-muted small fw-medium text-uppercase">Total Pelanggan</div>
                <div class="h3 fw-bold mb-0 mt-1"><?= $total_pelanggan ?></div>
                <div class="small text-muted mt-1"><i class="fa fa-user-check me-1"></i>Pelanggan aktif</div>
            </div>
            <div class="kpi-icon bg-primary bg-opacity-10 text-primary">
                <i class="fa fa-users"></i>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="kpi-card d-flex align-items-center justify-content-between">
            <div>
                <div class="text-muted small fw-medium text-uppercase">Total Meja</div>
                <div class="h3 fw-bold mb-0 mt-1"><?= $total_meja ?></div>
                <div class="small text-success mt-1"><i class="fa fa-check me-1"></i><?= $meja_aktif ?> Meja Siap Pakai</div>
            </div>
            <div class="kpi-icon bg-success bg-opacity-10 text-success">
                <i class="fa fa-chair"></i>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="kpi-card d-flex align-items-center justify-content-between">
            <div>
                <div class="text-muted small fw-medium text-uppercase">Total Reservasi</div>
                <div class="h3 fw-bold mb-0 mt-1"><?= $total_reservasi ?></div>
                <div class="small text-info mt-1"><i class="fa fa-calendar-alt me-1"></i>Jadwal booking</div>
            </div>
            <div class="kpi-icon bg-info bg-opacity-10 text-info">
                <i class="fa fa-calendar-check"></i>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="kpi-card d-flex align-items-center justify-content-between">
            <div>
                <div class="text-muted small fw-medium text-uppercase">Total Pesanan</div>
                <div class="h3 fw-bold mb-0 mt-1"><?= $total_pesanan ?></div>
                <div class="small text-warning mt-1"><i class="fa fa-receipt me-1"></i>Transaksi tercatat</div>
            </div>
            <div class="kpi-icon bg-warning bg-opacity-10 text-warning">
                <i class="fa fa-utensils"></i>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Tabel Reservasi Terbaru -->
    <div class="col-lg-6">
        <div class="card card-custom h-100">
            <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                <span><i class="fa fa-calendar-check me-2 text-primary"></i>Reservasi Terbaru</span>
                <a href="index.php?page=reservations" class="small text-decoration-none fw-semibold">Lihat Semua &rarr;</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($reservasi_terbaru)): ?>
                    <div class="p-4 text-center text-muted">Belum ada data reservasi.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-custom mb-0">
                            <thead>
                                <tr>
                                    <th>Pelanggan</th>
                                    <th>Meja</th>
                                    <th>Jadwal</th>
                                    <th>Tamu</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reservasi_terbaru as $r): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?= avatarInisial($r['nama_pelanggan']) ?>
                                                <div>
                                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($r['nama_pelanggan']) ?></div>
                                                    <div class="text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars($r['telepon_pelanggan']) ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td><span class="badge badge-soft-secondary font-monospace"><?= htmlspecialchars($r['nomor_meja']) ?></span></td>
                                        <td>
                                            <div><?= htmlspecialchars($r['reservation_date']) ?></div>
                                            <div class="text-muted" style="font-size: 0.75rem;"><?= substr($r['reservation_time'], 0, 5) ?> WIB</div>
                                        </td>
                                        <td><span class="badge bg-light text-dark border"><?= $r['guest_count'] ?> Kursi</span></td>
                                        <td><?= badgeStatus($r['status']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Tabel Pesanan Terbaru -->
    <div class="col-lg-6">
        <div class="card card-custom h-100">
            <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                <span><i class="fa fa-receipt me-2 text-warning"></i>Pesanan Terbaru</span>
                <a href="index.php?page=orders" class="small text-decoration-none fw-semibold">Lihat Semua &rarr;</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($pesanan_terbaru)): ?>
                    <div class="p-4 text-center text-muted">Belum ada data transaksi pesanan.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-custom mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Pelanggan</th>
                                    <th>Waktu</th>
                                    <th>Total Tagihan</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pesanan_terbaru as $o): ?>
                                    <tr>
                                        <td class="fw-bold text-muted">#<?= $o['id'] ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?= avatarInisial($o['nama_pelanggan']) ?>
                                                <span class="fw-semibold text-dark"><?= htmlspecialchars($o['nama_pelanggan']) ?></span>
                                            </div>
                                        </td>
                                        <td class="small text-muted"><?= date('d M Y H:i', strtotime($o['order_date'])) ?></td>
                                        <td class="fw-bold text-dark"><?= formatRupiah((float)$o['total_amount']) ?></td>
                                        <td><?= badgeStatus($o['status']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
