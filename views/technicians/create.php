<?php
// views/technicians/create.php

// --- 1. LOGIKA PHP ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek Auth
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

// Load Config & Model
if (!isset($pdo)) {
    $pdo = require_once __DIR__ . '/../../config/database.php';
}
require_once __DIR__ . '/../../models/Teknisi.php';

// Ambil Error & Data Lama (jika ada validasi gagal sebelumnya)
$message = $_GET['error'] ?? '';
$old = $_SESSION['old_form'] ?? [];

// Bersihkan session lama
if (isset($_SESSION['old_form'])) unset($_SESSION['old_form']);

// Logic checkbox active
$isActive = true;
if (!empty($old)) {
    $isActive = isset($old['status_aktif']);
}

// --- 2. LOAD HEADER ---
require_once __DIR__ . '/../partials/header.php';
?>

<style>
    /* Styling Card Form */
    .card-custom {
        border: none;
        border-radius: 1rem;
        box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.08);
        background: #fff;
    }
    
    /* Judul Bagian Form */
    .form-section-title {
        font-size: 0.9rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #adb5bd;
        margin-bottom: 1rem;
        border-bottom: 1px solid #f1f1f1;
        padding-bottom: 0.5rem;
    }

    /* Input Group Styling */
    .input-group-text {
        background-color: #f8f9fa;
        border-right: none;
        color: #6c757d;
    }
    .form-control {
        border-left: none;
        background-color: #f8f9fa;
    }
    .form-control:focus {
        background-color: #fff;
        box-shadow: none;
        border-color: #dee2e6;
    }
    .input-group:focus-within {
        box-shadow: 0 0 0 0.25rem rgba(93, 135, 255, 0.25);
        border-radius: 0.375rem;
    }
    .input-group:focus-within .input-group-text,
    .input-group:focus-within .form-control {
        border-color: #86b7fe;
        background-color: #fff;
    }

    /* PERBAIKAN TOMBOL (Agar Konsisten & Tidak Hilang) */
    .btn-primary-custom {
        background-color: #5D87FF; /* Warna Biru Utama */
        border-color: #5D87FF;
        color: white;
        padding: 10px 25px;
        font-weight: 600;
    }
    .btn-primary-custom:hover {
        background-color: #4a72e8;
        border-color: #4a72e8;
        color: white;
        box-shadow: 0 4px 10px rgba(93, 135, 255, 0.3);
    }
    
    /* Switch Checkbox */
    .form-check-input:checked {
        background-color: #5D87FF;
        border-color: #5D87FF;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4 animate__animated animate__fadeIn">
    <div>
        <h4 class="m-0 fw-bold text-dark">Tambah Teknisi Baru</h4>
        <span class="text-muted small">Daftarkan teknisi baru dan atur keahliannya.</span>
    </div>
    <a href="list.php" class="btn btn-outline-secondary btn-sm shadow-sm rounded-pill px-3">
        <i class="fas fa-arrow-left me-1"></i> Kembali
    </a>
</div>

<?php if ($message): ?>
    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 animate__animated animate__shakeX" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i> <?= htmlspecialchars($message) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card-custom animate__animated animate__fadeInUp">
    <div class="card-body p-4">
        <form method="POST" action="../../controllers/TechnicianController.php">
            <input type="hidden" name="action" value="create">

            <div class="row g-5">
                <div class="col-lg-6">
                    <div class="form-section-title"><i class="fas fa-user-tag me-2"></i>Profil Profesional</div>

                    <div class="mb-3">
                        <label for="nama_teknisi" class="form-label fw-bold small">Nama Teknisi</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control" id="nama_teknisi" name="nama_teknisi"
                                value="<?php echo htmlspecialchars($old['nama_teknisi'] ?? ''); ?>"
                                placeholder="Contoh: Ahmad Savarudin" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="keahlian" class="form-label fw-bold small">Keahlian (Spesialisasi)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-tools"></i></span>
                            <textarea class="form-control" id="keahlian" name="keahlian" rows="3"
                                placeholder="Contoh: Reparasi AC, Kulkas, TV LED..."><?php echo htmlspecialchars($old['keahlian'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-text small">Pisahkan dengan koma jika lebih dari satu.</div>
                    </div>

                    <div class="mb-3 pt-2">
                        <label class="form-label fw-bold small d-block">Status Akun</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="status_aktif" name="status_aktif" value="1" 
                                <?php if ($isActive) echo 'checked'; ?>>
                            <label class="form-check-label" for="status_aktif">Aktif (Dapat menerima tugas)</label>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="form-section-title"><i class="fas fa-address-book me-2"></i>Kontak & Akun</div>

                    <div class="mb-3">
                        <label for="no_hp" class="form-label fw-bold small">Nomor HP / WhatsApp</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                            <input type="text" class="form-control" id="no_hp" name="no_hp"
                                value="<?php echo htmlspecialchars($old['no_hp'] ?? ''); ?>"
                                placeholder="08xxxxxxxxxx">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label fw-bold small">Email (Username Login)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email"
                                value="<?php echo htmlspecialchars($old['email'] ?? ''); ?>"
                                placeholder="email@teknisi.com" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label fw-bold small">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-key"></i></span>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="confirm_password" class="form-label fw-bold small">Konfirmasi Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-check-circle"></i></span>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="my-4 opacity-10">

            <div class="d-flex justify-content-end gap-2">
                <a href="list.php" class="btn btn-light border rounded-pill px-4">Batal</a>
                <button type="submit" class="btn btn-primary-custom rounded-pill">
                    <i class="fas fa-save me-2"></i> Simpan Data Teknisi
                </button>
            </div>
        </form>
    </div>
</div>

<?php
// --- 5. LOAD FOOTER ---
require_once __DIR__ . '/../partials/footer.php';
?>