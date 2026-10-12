<?php
/**
 * Halaman Manajemen Data Menu Hidangan
 */

// 1. Tangani Aksi Form POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';

    if ($aksi === 'tambah') {
        $nama        = bersihkanInput($_POST['nama'] ?? '');
        $harga       = (float)($_POST['harga'] ?? 0);
        $ketersediaan= (int)($_POST['ketersediaan'] ?? 1);

        if (empty($nama) || !validasiHarga($harga)) {
            setNotifikasi('danger', 'Nama menu dan harga valid (lebih dari 0) wajib diisi.');
        } else {
            $stmt = $db->prepare("INSERT INTO menu_items (name, price, is_available) VALUES (:nama, :harga, :st)");
            $stmt->execute(['nama' => $nama, 'harga' => $harga, 'st' => $ketersediaan]);
            setNotifikasi('success', "Menu '{$nama}' berhasil ditambahkan.");
        }
        header('Location: index.php?page=menu');
        exit;
    }

    if ($aksi === 'ubah') {
        $id          = (int)($_POST['id'] ?? 0);
        $nama        = bersihkanInput($_POST['nama'] ?? '');
        $harga       = (float)($_POST['harga'] ?? 0);
        $ketersediaan= (int)($_POST['ketersediaan'] ?? 1);

        if (empty($nama) || !validasiHarga($harga)) {
            setNotifikasi('danger', 'Nama menu dan harga valid wajib diisi.');
        } else {
            $stmt = $db->prepare("UPDATE menu_items SET name = :nama, price = :harga, is_available = :st WHERE id = :id");
            $stmt->execute(['id' => $id, 'nama' => $nama, 'harga' => $harga, 'st' => $ketersediaan]);
            setNotifikasi('success', "Menu '{$nama}' berhasil diperbarui.");
        }
        header('Location: index.php?page=menu');
        exit;
    }

    if ($aksi === 'hapus') {
        $id = (int)($_POST['id'] ?? 0);
        try {
            $stmt = $db->prepare("DELETE FROM menu_items WHERE id = :id");
            $stmt->execute(['id' => $id]);
            setNotifikasi('success', 'Menu berhasil dihapus.');
        } catch (PDOException $e) {
            setNotifikasi('danger', 'Menu tidak dapat dihapus karena masih tercatat di riwayat transaksi.');
        }
        header('Location: index.php?page=menu');
        exit;
    }
}

// 2. Ambil Daftar Seluruh Menu
$daftar_menu = $db->query("SELECT * FROM menu_items ORDER BY id DESC")->fetchAll();
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-2">
    <div>
        <h1 class="h3 fw-bold mb-1">Data Menu</h1>
        <p class="text-muted mb-0">Kelola daftar hidangan makanan, minuman, harga, dan ketersediaan stok.</p>
    </div>
    <button type="button" class="btn btn-warning-custom" data-bs-toggle="modal" data-bs-target="#modalTambahMenu">
        <i class="fa fa-plus-circle me-1"></i> Tambah Menu Baru
    </button>
</div>

<div class="card card-custom">
    <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
        <span><i class="fa fa-utensils me-2 text-primary"></i>Daftar Menu Hidangan (<?= count($daftar_menu) ?> Item)</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Nama Menu</th>
                        <th>Harga Satuan</th>
                        <th>Status Ketersediaan</th>
                        <th class="text-end" style="width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($daftar_menu)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">Belum ada item menu yang ditambahkan.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($daftar_menu as $no => $m): ?>
                            <tr>
                                <td><?= $no + 1 ?></td>
                                <td class="fw-semibold text-dark"><?= htmlspecialchars($m['name']) ?></td>
                                <td class="fw-bold text-success"><?= formatRupiah((float)$m['price']) ?></td>
                                <td><?= badgeStatus($m['is_available'] ? 'Tersedia' : 'Dibatalkan') ?></td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-outline-primary me-1" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalEditMenu<?= $m['id'] ?>"
                                            title="Ubah Data">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <form method="POST" action="index.php?page=menu" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus menu <?= htmlspecialchars(addslashes($m['name'])) ?>?');">
                                        <input type="hidden" name="aksi" value="hapus">
                                        <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus Data">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>

                            <!-- Modal Edit Menu -->
                            <div class="modal fade" id="modalEditMenu<?= $m['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST" action="index.php?page=menu">
                                            <input type="hidden" name="aksi" value="ubah">
                                            <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                            <div class="modal-header">
                                                <h5 class="modal-title"><i class="fa fa-edit me-2 text-primary"></i>Ubah Data Menu</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Nama Menu <span class="text-danger">*</span></label>
                                                    <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($m['name']) ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Harga Satuan (Rp) <span class="text-danger">*</span></label>
                                                    <input type="number" name="harga" class="form-control" min="100" step="500" value="<?= (float)$m['price'] ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Status Ketersediaan</label>
                                                    <select name="ketersediaan" class="form-select" required>
                                                        <option value="1" <?= $m['is_available'] ? 'selected' : '' ?>>Tersedia (Bisa dipesan)</option>
                                                        <option value="0" <?= !$m['is_available'] ? 'selected' : '' ?>>Habis / Tidak Tersedia</option>
                                                    </select>
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

<!-- Modal Tambah Menu -->
<div class="modal fade" id="modalTambahMenu" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="index.php?page=menu">
                <input type="hidden" name="aksi" value="tambah">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa fa-plus-circle me-2 text-warning"></i>Tambah Menu Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Menu <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control" placeholder="Contoh: Sate Sapi Maranggi" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Harga Satuan (Rp) <span class="text-danger">*</span></label>
                        <input type="number" name="harga" class="form-control" min="100" step="500" placeholder="Contoh: 45000" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Status Ketersediaan</label>
                        <select name="ketersediaan" class="form-select" required>
                            <option value="1" selected>Tersedia</option>
                            <option value="0">Habis / Tidak Tersedia</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning-custom">Simpan Menu</button>
                </div>
            </form>
        </div>
    </div>
</div>
