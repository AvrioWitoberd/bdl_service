<?php
// views/customers/create.php

// --- 1. SETUP & LOGIKA PHP ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek Auth (Hanya Admin yang boleh tambah)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

// Database & Model
$pdo = require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Pelanggan.php';
// Tidak perlu inisialisasi $pelangganModel kalau hanya tampilkan form, 
// tapi biarkan jika nanti butuh logic tambahan.

// Ambil Data Flash Session (Error / Old Input)
$message = $_SESSION['error_message'] ?? '';
$old = $_SESSION['old_form'] ?? [];

// Bersihkan session setelah diambil
unset($_SESSION['old_form']);
unset($_SESSION['error_message']);

// --- 2. LOAD HEADER ---
require_once __DIR__ . '/../partials/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 animate__animated animate__fadeIn">
    <div>
        <h4 class="m-0 fw-bold text-dark">Tambah Pelanggan Baru</h4>
        <span class="text-muted small">Silakan isi formulir di bawah ini.</span>
    </div>
    <a href="list.php" class="btn btn-outline-secondary btn-sm shadow-sm rounded-pill px-3">
        <i class="fas fa-arrow-left me-1"></i> Kembali
    </a>
</div>

<?php if ($message): ?>
    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 animate__animated animate__shakeX" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i> <?= htmlspecialchars($message) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm animate__animated animate__fadeInUp" style="border-radius: 1rem;">
    <div class="card-body p-4">
        <form method="POST" action="../../controllers/CustomerController.php" class="needs-validation">
            <input type="hidden" name="action" value="create">

            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="mb-3 pb-2 border-bottom text-uppercase fw-bold text-muted small">
                        <i class="fas fa-id-card me-2"></i>Informasi Pribadi
                    </div>

                    <div class="mb-3">
                        <label for="nama" class="form-label fw-bold small">Nama Lengkap</label>
                        <div class="input-group shadow-sm">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-user text-muted"></i></span>
                            <input type="text" class="form-control border-start-0 ps-0" id="nama" name="nama"
                                value="<?= htmlspecialchars($old['nama'] ?? '') ?>"
                                placeholder="Contoh: Budi Santoso" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="no_hp" class="form-label fw-bold small">Nomor HP</label>
                        <div class="input-group shadow-sm">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-phone text-muted"></i></span>
                            <input type="text" class="form-control border-start-0 ps-0" id="no_hp" name="no_hp"
                                value="<?= htmlspecialchars($old['no_hp'] ?? '') ?>"
                                placeholder="08xxxxxxxxxx" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="alamat" class="form-label fw-bold small">Alamat Lengkap</label>
                        <div class="input-group shadow-sm">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-map-marker-alt text-muted"></i></span>
                            <textarea class="form-control border-start-0 ps-0" id="alamat" name="alamat" rows="4"
                                placeholder="Jalan, RT/RW, Kelurahan..."
                                required><?= htmlspecialchars($old['alamat'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="mb-3 pb-2 border-bottom text-uppercase fw-bold text-muted small">
                        <i class="fas fa-lock me-2"></i>Informasi Akun
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label fw-bold small">Alamat Email</label>
                        <div class="input-group shadow-sm">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-envelope text-muted"></i></span>
                            <input type="email" class="form-control border-start-0 ps-0" id="email" name="email"
                                value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                                placeholder="nama@email.com" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label fw-bold small">Password</label>
                            <div class="input-group shadow-sm">
                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-key text-muted"></i></span>
                                <input type="password" class="form-control border-start-0 ps-0" id="password" name="password" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="confirm_password" class="form-label fw-bold small">Konfirmasi Password</label>
                            <div class="input-group shadow-sm">
                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-check-circle text-muted"></i></span>
                                <input type="password" class="form-control border-start-0 ps-0" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-light border text-muted small mt-2">
                        <i class="fas fa-info-circle me-1 text-primary"></i> Pastikan email aktif untuk keperluan notifikasi service.
                    </div>
                </div>
            </div>

            <hr class="my-4 opacity-10">

            <div class="d-flex justify-content-end gap-2">
                <a href="list.php" class="btn btn-light border px-4 rounded-pill">Batal</a>
                <button type="submit" class="btn btn-primary px-4 rounded-pill shadow-sm" style="background-color: var(--sidebar-active); border: none;">
                    <i class="fas fa-save me-2"></i> Simpan Data Pelanggan
                </button>
            </div>

        </form>
    </div>
</div>

<?php
// --- 4. LOAD FOOTER ---
require_once __DIR__ . '/../partials/footer.php';
?>