<?php
/**
 * Halaman Manajemen Data Pelanggan
 * Menggunakan class Customer dari classes/Customer.php
 */

// 1. Tangani Aksi Form POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';

    if ($aksi === 'tambah') {
        $nama    = bersihkanInput($_POST['nama'] ?? '');
        $telepon = bersihkanInput($_POST['telepon'] ?? '');
        $email   = bersihkanInput($_POST['email'] ?? '');

        if (empty($nama) || empty($telepon)) {
            setNotifikasi('danger', 'Nama dan nomor telepon wajib diisi.');
        } else {
            if ($pelangganClass->tambah($nama, $telepon, $email)) {
                setNotifikasi('success', "Pelanggan '{$nama}' berhasil ditambahkan.");
            } else {
                setNotifikasi('danger', 'Gagal menambahkan pelanggan.');
            }
        }
        header('Location: index.php?page=customers');
        exit;
    }

    if ($aksi === 'ubah') {
        $id      = (int)($_POST['id'] ?? 0);
        $nama    = bersihkanInput($_POST['nama'] ?? '');
        $telepon = bersihkanInput($_POST['telepon'] ?? '');
        $email   = bersihkanInput($_POST['email'] ?? '');

        if (empty($nama) || empty($telepon)) {
            setNotifikasi('danger', 'Nama dan nomor telepon wajib diisi.');
        } else {
            if ($pelangganClass->ubah($id, $nama, $telepon, $email)) {
                setNotifikasi('success', "Data pelanggan '{$nama}' berhasil diperbarui.");
            } else {
                setNotifikasi('danger', 'Gagal memperbarui data pelanggan.');
            }
        }
        header('Location: index.php?page=customers');
        exit;
    }

    if ($aksi === 'hapus') {
        $id = (int)($_POST['id'] ?? 0);
        if ($pelangganClass->hapus($id)) {
            setNotifikasi('success', 'Pelanggan berhasil dihapus.');
        } else {
            setNotifikasi('danger', 'Pelanggan tidak bisa dihapus karena masih memiliki riwayat reservasi atau pesanan.');
        }
        header('Location: index.php?page=customers');
        exit;
    }
}

// 2. Ambil Daftar Pelanggan
$daftar_pelanggan = $pelangganClass->ambilSemua();
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-2">
    <div>
        <h1 class="h3 fw-bold mb-1">Data Pelanggan</h1>
        <p class="text-muted mb-0">Kelola informasi nama, kontak telepon, dan email pelanggan.</p>
    </div>
    <button type="button" class="btn btn-warning-custom" data-bs-toggle="modal" data-bs-target="#modalTambah">
        <i class="fa fa-user-plus me-1"></i> Tambah Pelanggan
    </button>
</div>

<div class="card card-custom">
    <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
        <span><i class="fa fa-users me-2 text-primary"></i>Daftar Pelanggan (<?= count($daftar_pelanggan) ?> Orang)</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Nama Pelanggan</th>
                        <th>Nomor Telepon</th>
                        <th>Email</th>
                        <th>Terdaftar Sejak</th>
                        <th class="text-end" style="width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($daftar_pelanggan)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Belum ada data pelanggan yang tersimpan.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($daftar_pelanggan as $no => $p): ?>
                            <tr>
                                <td><?= $no + 1 ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?= avatarInisial($p['name']) ?>
                                        <span class="fw-semibold text-dark"><?= htmlspecialchars($p['name']) ?></span>
                                    </div>
                                </td>
                                <td>
                                    <i class="fa fa-phone text-muted me-1 small"></i>
                                    <?= htmlspecialchars($p['phone']) ?>
                                </td>
                                <td>
                                    <?= !empty($p['email']) ? htmlspecialchars($p['email']) : '<span class="text-muted fst-italic">-</span>' ?>
                                </td>
                                <td class="small text-muted"><?= date('d M Y', strtotime($p['created_at'])) ?></td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-outline-primary me-1" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalEdit<?= $p['id'] ?>"
                                            title="Ubah Data">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <form method="POST" action="index.php?page=customers" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pelanggan <?= htmlspecialchars(addslashes($p['name'])) ?>?');">
                                        <input type="hidden" name="aksi" value="hapus">
                                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus Data">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>

                            <!-- Modal Edit Pelanggan -->
                            <div class="modal fade" id="modalEdit<?= $p['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST" action="index.php?page=customers">
                                            <input type="hidden" name="aksi" value="ubah">
                                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                            <div class="modal-header">
                                                <h5 class="modal-title"><i class="fa fa-edit me-2 text-primary"></i>Ubah Data Pelanggan</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                                                    <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($p['name']) ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Nomor Telepon <span class="text-danger">*</span></label>
                                                    <input type="text" name="telepon" class="form-control" value="<?= htmlspecialchars($p['phone']) ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Email <span class="text-muted">(Boleh kosong)</span></label>
                                                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($p['email'] ?? '') ?>">
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Pelanggan -->
<div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="index.php?page=customers">
                <input type="hidden" name="aksi" value="tambah">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa fa-user-plus me-2 text-warning"></i>Tambah Pelanggan Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control" placeholder="Contoh: Budi Santoso" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nomor Telepon <span class="text-danger">*</span></label>
                        <input type="text" name="telepon" class="form-control" placeholder="Contoh: 081234567890" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email <span class="text-muted">(Boleh kosong)</span></label>
                        <input type="email" name="email" class="form-control" placeholder="Contoh: budi@gmail.com">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning-custom">Simpan Pelanggan</button>
                </div>
            </form>
        </div>
    </div>
</div>
