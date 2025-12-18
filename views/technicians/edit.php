<?php
// views/technicians/edit.php

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
$pdo = require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Teknisi.php';

$teknisiModel = new Teknisi($pdo);
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$teknisi = $teknisiModel->getById($id);

if (!$teknisi) {
    // Jika ID tidak ditemukan, kembalikan ke list dengan pesan error
    header("Location: list.php?msg=Technician not found");
    exit;
}

$message = isset($_GET['msg']) ? $_GET['msg'] : '';

// --- 2. LOAD HEADER ---
require_once __DIR__ . '/../partials/header.php';
?>

<style>
    /* Card Styling */
    .card-custom {
        border: none;
        border-radius: 1rem;
        box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.08);
        background: #fff;
        overflow: hidden;
    }
    
    /* Garis Aksen di atas Card */
    .card-header-accent {
        background: linear-gradient(90deg, #5D87FF, #86b7fe);
        height: 6px;
        width: 100%;
    }

    /* Form Elements */
    .form-label {
        font-weight: 600;
        font-size: 0.85rem;
        color: #555;
        margin-bottom: 0.5rem;
    }
    .form-control {
        padding: 0.7rem 1rem;
        border-color: #dfe5ef;
        background-color: #fcfdfe;
    }
    .form-control:focus {
        border-color: #5D87FF;
        box-shadow: 0 0 0 3px rgba(93, 135, 255, 0.1);
        background-color: #fff;
    }

    /* Status Switch */
    .status-card {
        background: #f8f9fa;
        border-radius: 0.75rem;
        padding: 1rem;
        border: 1px solid #eee;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .form-check-input:checked {
        background-color: #198754; /* Hijau untuk status aktif */
        border-color: #198754;
    }

    /* PERBAIKAN TOMBOL UPDATE (Warna Hardcode agar muncul) */
    .btn-update {
        background-color: #5D87FF; 
        color: white;
        border: none;
        padding: 10px 30px;
        border-radius: 50rem; /* Pill shape */
        font-weight: 600;
        transition: all 0.2s;
    }
    .btn-update:hover {
        background-color: #4a72e8;
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(93, 135, 255, 0.3);
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4 animate__animated animate__fadeIn">
    <div>
        <h4 class="m-0 fw-bold text-dark">Edit Data Teknisi</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small text-muted">
                <li class="breadcrumb-item"><a href="list.php" class="text-decoration-none text-muted">Teknisi</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit #<?= $teknisi['id_teknisi'] ?></li>
            </ol>
        </nav>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-warning alert-dismissible fade show shadow-sm border-0 animate__animated animate__shakeX" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i> <?= htmlspecialchars($message) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="row justify-content-center">
    <div class="col-lg-8 animate__animated animate__fadeInUp">
        <div class="card-custom">
            <div class="card-header-accent"></div>
            
            <div class="card-body p-4">
                <form method="POST" action="../../controllers/TechnicianController.php">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id_teknisi" value="<?php echo $teknisi['id_teknisi']; ?>">

                    <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                        <h5 class="fw-bold m-0 text-secondary"><i class="fas fa-user-edit me-2"></i>Formulir Perubahan</h5>
                        
                        <?php if ($teknisi['status_aktif']): ?>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill">
                                <i class="fas fa-check-circle me-1"></i> Saat ini Aktif
                            </span>
                        <?php else: ?>
                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-2 rounded-pill">
                                <i class="fas fa-times-circle me-1"></i> Saat ini Non-Aktif
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="nama_teknisi" class="form-label">Nama Lengkap Teknisi</label>
                        <input type="text" class="form-control" id="nama_teknisi" name="nama_teknisi" 
                               value="<?php echo htmlspecialchars($teknisi['nama_teknisi']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="keahlian" class="form-label">Keahlian (Spesialisasi)</label>
                        <textarea class="form-control" id="keahlian" name="keahlian" rows="3"><?php echo htmlspecialchars($teknisi['keahlian']); ?></textarea>
                        <div class="form-text small text-muted">Deskripsikan perangkat elektronik yang dapat ditangani.</div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="no_hp" class="form-label">Nomor HP / WA</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-phone small"></i></span>
                                <input type="text" class="form-control border-start-0 ps-0" id="no_hp" name="no_hp" 
                                       value="<?php echo htmlspecialchars($teknisi['no_hp']); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email (Akun Login)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-envelope small"></i></span>
                                <input type="email" class="form-control border-start-0 ps-0" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($teknisi['email']); ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="status-card mb-4">
                        <div>
                            <span class="d-block fw-bold small text-uppercase text-secondary">Status Akun</span>
                            <span class="small text-muted">Aktifkan agar teknisi dapat menerima order.</span>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" style="width: 3em; height: 1.5em; cursor: pointer;" 
                                   type="checkbox" id="status_aktif" name="status_aktif" value="1" 
                                   <?php if ($teknisi['status_aktif']) echo 'checked'; ?>>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end align-items-center gap-2 mt-4">
                        <a href="list.php" class="btn btn-light border rounded-pill px-4 fw-bold text-muted">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-update shadow-sm">
                            <i class="fas fa-save me-2"></i> Update Data
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<?php
// --- 5. LOAD FOOTER ---
require_once __DIR__ . '/../partials/footer.php';
?>