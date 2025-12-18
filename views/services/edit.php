<?php
// views/services/edit.php

// --- 1. LOGIKA PHP & DATA ---
session_start();

// Koneksi & Model
// Sesuaikan path ini jika lokasi config/models berbeda
require_once '../../config/database.php';
require_once '../../models/Service.php';
require_once '../../models/Teknisi.php';

// Cek Login (Backup jika di header belum tereksekusi)
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login.php");
    exit;
}

$role = $_SESSION['role']; 
$userId = $_SESSION['user_id']; 

// Inisialisasi Model
$serviceModel = new Service($pdo);
$teknisiModel = new Teknisi($pdo);

// Ambil ID dari Parameter URL
$id_service = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$service = $serviceModel->getById($id_service);

// Jika Data Tidak Ditemukan
if (!$service) {
    // Redirect kembali ke list
    echo "<script>alert('Data service tidak ditemukan!'); window.location.href='list.php';</script>";
    exit;
}

// Ambil Data Referensi untuk Dropdown
$teknisiList = $teknisiModel->getAll(100, 0, '', true);
$stmtStatus = $pdo->query("SELECT * FROM status_perbaikan ORDER BY id_status ASC");
$statusList = $stmtStatus->fetchAll();

$error = '';

// --- 2. PROSES UPDATE SAAT TOMBOL SIMPAN DITEKAN ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_teknisi = !empty($_POST['id_teknisi']) ? $_POST['id_teknisi'] : null;
    $keluhan    = $_POST['keluhan'];
    $biaya      = $_POST['biaya_service'];
    $id_status  = $_POST['id_status'];

    // Mapping ID Admin (Khusus Admin yang login)
    if ($role === 'admin') {
        $mapAdmin = [ 1 => 8001, 2 => 8002 ]; 
        if (isset($mapAdmin[$userId])) {
            $id_admin_to_save = $mapAdmin[$userId];
        } else {
            $stmtDefault = $pdo->query("SELECT id_admin FROM admin ORDER BY id_admin ASC LIMIT 1");
            $id_admin_to_save = $stmtDefault->fetchColumn();
        }
    } else {
        // Teknisi tidak mengubah ID Admin pencatat
        $id_admin_to_save = $service['id_admin'];
    }

    // Eksekusi Update
    if ($serviceModel->update($id_service, $id_teknisi, $keluhan, $biaya, $id_status, $id_admin_to_save)) {
        // Redirect Sukses
        echo "<script>
            alert('Data berhasil diperbarui!');
            window.location.href = 'list.php';
        </script>";
        exit;
    } else {
        $error = "Gagal mengupdate database. Silakan coba lagi.";
    }
}

// --- 3. MULAI TAMPILAN (VIEW) ---

// Panggil Header (Sidebar & Navbar ada di sini)
require_once '../partials/header.php'; 
?>

<div class="d-flex justify-content-between align-items-center mb-4 mt-4">
    <div>
        <h4 class="mb-0 fw-bold text-dark">Edit Data Service</h4>
        <small class="text-muted">ID: #<?= $id_service ?> | <?= htmlspecialchars($service['nama_pelanggan']) ?></small>
    </div>
    <a href="list.php" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i> Kembali
    </a>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i> <?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-4 bg-white mb-5">
    <div class="card-body p-4">
        <form method="POST">
            <div class="row g-4">
                
                <div class="col-lg-5 border-end">
                    <h6 class="text-uppercase text-muted fw-bold mb-3 small">Data Masuk</h6>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Perangkat</label>
                        <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($service['nama_perangkat']) ?> - <?= htmlspecialchars($service['merek']) ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Tanggal Masuk</label>
                        <input type="text" class="form-control bg-light" value="<?= date('d M Y, H:i', strtotime($service['tanggal_masuk'])) ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Keluhan Awal</label>
                        <div class="p-3 bg-light rounded text-muted small fst-italic">
                            "<?= htmlspecialchars($service['keluhan']) ?>"
                        </div>
                    </div>
                </div>

                <div class="col-lg-7 ps-lg-4">
                    <h6 class="text-uppercase text-primary fw-bold mb-3 small">Update Pengerjaan</h6>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Detail Kerusakan / Catatan Teknisi</label>
                        <textarea name="keluhan" class="form-control" rows="3" required><?= htmlspecialchars($service['keluhan']) ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Status Status</label>
                            <select name="id_status" class="form-select border-primary" required>
                                <?php foreach($statusList as $st): ?>
                                    <option value="<?= $st['id_status'] ?>" <?= ($st['id_status'] == $service['id_status']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($st['nama_status']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Teknisi</label>
                            <?php if ($role === 'admin'): ?>
                                <select name="id_teknisi" class="form-select">
                                    <option value="">-- Pilih Teknisi --</option>
                                    <?php foreach($teknisiList as $t): ?>
                                        <option value="<?= $t['id_teknisi'] ?>" <?= ($t['id_teknisi'] == $service['id_teknisi']) ? 'selected' : '' ?>>
                                            <?= $t['nama_teknisi'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            <?php else: ?>
                                <input type="hidden" name="id_teknisi" value="<?= $service['id_teknisi'] ?>">
                                <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($service['nama_teknisi'] ?? '-') ?>" readonly>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Biaya Service (Rp)</label>
                        <?php if ($role === 'admin'): ?>
                            <input type="number" name="biaya_service" class="form-control" value="<?= $service['biaya_service'] ?>">
                        <?php else: ?>
                            <input type="hidden" name="biaya_service" value="<?= $service['biaya_service'] ?>">
                            <input type="text" class="form-control bg-light" value="<?= number_format($service['biaya_service'], 0, ',', '.') ?>" readonly>
                            <div class="form-text text-danger small">*Hanya admin yang dapat mengubah biaya.</div>
                        <?php endif; ?>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="list.php" class="btn btn-light border">Batal</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save me-1"></i> Simpan Perubahan
                        </button>
                    </div>

                </div>
            </div>
        </form>
    </div>
</div>

<?php
// Panggil Footer (Tutup container, load script JS)
require_once '../partials/footer.php'; 
?>