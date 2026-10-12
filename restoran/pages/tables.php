<?php
/**
 * Halaman Manajemen Data Meja Restoran
 */

// 1. Tangani Aksi Form POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';

    if ($aksi === 'tambah') {
        $nomor_meja = bersihkanInput($_POST['nomor_meja'] ?? '');
        $kapasitas  = (int)($_POST['kapasitas'] ?? 0);
        $status     = bersihkanInput($_POST['status'] ?? 'Aktif');

        if (empty($nomor_meja) || $kapasitas <= 0) {
            setNotifikasi('danger', 'Nomor meja dan kapasitas minimal 1 kursi wajib diisi.');
        } else {
            try {
                $stmt = $db->prepare("INSERT INTO restaurant_tables (table_number, capacity, status) VALUES (:no, :kap, :st)");
                $stmt->execute(['no' => $nomor_meja, 'kap' => $kapasitas, 'st' => $status]);
                setNotifikasi('success', "Meja {$nomor_meja} berhasil ditambahkan.");
            } catch (PDOException $e) {
                setNotifikasi('danger', "Gagal menambahkan meja. Pastikan nomor meja belum dipakai.");
            }
        }
        header('Location: index.php?page=tables');
        exit;
    }

    if ($aksi === 'ubah') {
        $id         = (int)($_POST['id'] ?? 0);
        $nomor_meja = bersihkanInput($_POST['nomor_meja'] ?? '');
        $kapasitas  = (int)($_POST['kapasitas'] ?? 0);
        $status     = bersihkanInput($_POST['status'] ?? 'Aktif');

        if (empty($nomor_meja) || $kapasitas <= 0) {
            setNotifikasi('danger', 'Nomor meja dan kapasitas harus valid.');
        } else {
            $stmt = $db->prepare("UPDATE restaurant_tables SET table_number = :no, capacity = :kap, status = :st WHERE id = :id");
            $stmt->execute(['id' => $id, 'no' => $nomor_meja, 'kap' => $kapasitas, 'st' => $status]);
            setNotifikasi('success', "Data Meja {$nomor_meja} berhasil diperbarui.");
        }
        header('Location: index.php?page=tables');
        exit;
    }

    if ($aksi === 'hapus') {
        $id = (int)($_POST['id'] ?? 0);
        try {
            $stmt = $db->prepare("DELETE FROM restaurant_tables WHERE id = :id");
            $stmt->execute(['id' => $id]);
            setNotifikasi('success', 'Meja berhasil dihapus.');
        } catch (PDOException $e) {
            setNotifikasi('danger', 'Meja tidak dapat dihapus karena masih terkait data reservasi.');
        }
        header('Location: index.php?page=tables');
        exit;
    }
}

// 2. Ambil Daftar Seluruh Meja
$daftar_meja = $db->query("SELECT * FROM restaurant_tables ORDER BY table_number ASC")->fetchAll();
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-2">
    <div>
        <h1 class="h3 fw-bold mb-1">Data Meja</h1>
        <p class="text-muted mb-0">Kelola nomor meja, kapasitas kursi, dan status ketersediaan meja.</p>
    </div>
    <button type="button" class="btn btn-warning-custom" data-bs-toggle="modal" data-bs-target="#modalTambahMeja">
        <i class="fa fa-plus-circle me-1"></i> Tambah Meja
    </button>
</div>

<div class="card card-custom">
    <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
        <span><i class="fa fa-chair me-2 text-primary"></i>Daftar Meja Restoran (<?= count($daftar_meja) ?> Meja)</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Nomor Meja</th>
                        <th>Kapasitas Kursi</th>
                        <th>Status</th>
                        <th class="text-end" style="width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($daftar_meja)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">Belum ada data meja yang ditambahkan.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($daftar_meja as $no => $m): ?>
                            <tr>
                                <td><?= $no + 1 ?></td>
                                <td class="fw-bold fs-6 text-dark"><?= htmlspecialchars($m['table_number']) ?></td>
                                <td>
                                    <i class="fa fa-user me-1 text-muted small"></i>
                                    <?= (int)$m['capacity'] ?> Orang
                                </td>
                                <td><?= badgeStatus($m['status']) ?></td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-outline-primary me-1" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalEditMeja<?= $m['id'] ?>"
                                            title="Ubah Data">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <form method="POST" action="index.php?page=tables" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus meja <?= htmlspecialchars(addslashes($m['table_number'])) ?>?');">
                                        <input type="hidden" name="aksi" value="hapus">
                                        <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus Data">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>

                            <!-- Modal Edit Meja -->
                            <div class="modal fade" id="modalEditMeja<?= $m['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST" action="index.php?page=tables">
                                            <input type="hidden" name="aksi" value="ubah">
                                            <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                            <div class="modal-header">
                                                <h5 class="modal-title"><i class="fa fa-edit me-2 text-primary"></i>Ubah Data Meja</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Nomor Meja <span class="text-danger">*</span></label>
                                                    <input type="text" name="nomor_meja" class="form-control" value="<?= htmlspecialchars($m['table_number']) ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Kapasitas Kursi <span class="text-danger">*</span></label>
                                                    <input type="number" name="kapasitas" class="form-control" min="1" max="50" value="<?= (int)$m['capacity'] ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Status Meja <span class="text-danger">*</span></label>
                                                    <select name="status" class="form-select" required>
                                                        <option value="Aktif" <?= $m['status'] === 'Aktif' ? 'selected' : '' ?>>Aktif (Dapat dipesan)</option>
                                                        <option value="Nonaktif" <?= $m['status'] === 'Nonaktif' ? 'selected' : '' ?>>Nonaktif (Tidak dapat dipesan)</option>
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

<!-- Modal Tambah Meja -->
<div class="modal fade" id="modalTambahMeja" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="index.php?page=tables">
                <input type="hidden" name="aksi" value="tambah">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa fa-chair me-2 text-warning"></i>Tambah Meja Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nomor Meja <span class="text-danger">*</span></label>
                        <input type="text" name="nomor_meja" class="form-control" placeholder="Contoh: T-07" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Kapasitas Kursi <span class="text-danger">*</span></label>
                        <input type="number" name="kapasitas" class="form-control" min="1" max="50" value="4" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Status Meja</label>
                        <select name="status" class="form-select" required>
                            <option value="Aktif" selected>Aktif (Dapat dipesan)</option>
                            <option value="Nonaktif">Nonaktif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning-custom">Simpan Meja</button>
                </div>
            </form>
        </div>
    </div>
</div>
