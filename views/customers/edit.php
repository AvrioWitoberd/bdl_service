<?php
// views/customers/edit.php

// --- 1. LOGIKA PHP ASLI (DIPERTAHANKAN & DIRAPIKAN PATH-NYA) ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek Auth
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

// Database & Model
$pdo = require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Pelanggan.php';

$pelangganModel = new Pelanggan($pdo);
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$pelanggan = $pelangganModel->getById($id);

if (!$pelanggan) {
    // Bisa dialihkan atau tampilkan error die
    die("Customer not found.");
}

$message = isset($_GET['msg']) ? $_GET['msg'] : '';

// --- 2. LOAD HEADER ---
require_once __DIR__ . '/../partials/header.php';
?>

<style>
    /* Profile Card Specific Styles */
    .profile-header {
        background: linear-gradient(45deg, var(--sidebar-bg-start), var(--accent-blue));
        padding: 2rem;
        text-align: center;
        color: white;
        border-radius: 1rem 1rem 0 0;
    }
    .profile-avatar {
        width: 100px;
        height: 100px;
        background: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto -50px;
        border: 4px solid rgba(255, 255, 255, 0.3);
        font-size: 2.5rem;
        color: var(--accent-blue);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        position: relative;
        z-index: 10;
    }
    .profile-body {
        padding-top: 60px;
        text-align: center;
    }
    /* Form Floating Customization */
    .form-floating > .form-control:focus ~ label,
    .form-floating > .form-control:not(:placeholder-shown) ~ label {
        color: var(--accent-blue);
    }
    .form-floating > .form-control:focus {
        border-color: var(--accent-blue);
        box-shadow: 0 0 0 0.25rem rgba(93, 135, 255, 0.25);
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4 animate__animated animate__fadeIn">
    <div>
        <h4 class="m-0 fw-bold text-dark">Edit Data Pelanggan</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small text-muted">
                <li class="breadcrumb-item"><a href="list.php" class="text-decoration-none">Pelanggan</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit #<?= $pelanggan['id_pelanggan'] ?></li>
            </ol>
        </nav>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 animate__animated animate__shakeX" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i> <?= htmlspecialchars($message) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="row g-4">

    <div class="col-lg-4 animate__animated animate__fadeInLeft">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 1rem;">
            <div class="profile-header">
                <h5 class="mb-0">Profil Pelanggan</h5>
            </div>
            <div class="profile-avatar">
                <i class="fas fa-user"></i>
            </div>
            <div class="card-body profile-body pb-4">
                <h5 class="fw-bold mb-1"><?= htmlspecialchars($pelanggan['nama']) ?></h5>
                <p class="text-muted small mb-3">ID: <?= $pelanggan['id_pelanggan'] ?></p>

                <hr class="w-50 mx-auto opacity-25">

                <div class="d-flex justify-content-center gap-2 mt-3">
                    <span class="badge bg-light text-dark border">
                        <i class="fas fa-history me-1"></i> Terdaftar
                    </span>
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                        Active
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8 animate__animated animate__fadeInRight">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 1rem;">
            <div class="card-body p-4">
                <h6 class="fw-bold text-uppercase text-muted mb-4 border-bottom pb-2">
                    <i class="fas fa-pen-square me-2"></i> Form Perubahan Data
                </h6>

                <form method="POST" action="../../controllers/CustomerController.php">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id_pelanggan" value="<?= $pelanggan['id_pelanggan']; ?>">

                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="nama" name="nama"
                            placeholder="Nama Lengkap"
                            value="<?= htmlspecialchars($pelanggan['nama']); ?>" required>
                        <label for="nama">Nama Lengkap</label>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="no_hp" name="no_hp"
                                    placeholder="08xxx"
                                    value="<?= htmlspecialchars($pelanggan['no_hp'] ?? ''); ?>" required>
                                <label for="no_hp">Nomor HP</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="email" class="form-control" id="email" name="email"
                                    placeholder="name@example.com"
                                    value="<?= htmlspecialchars($pelanggan['email'] ?? ''); ?>" required>
                                <label for="email">Alamat Email</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-floating mb-4">
                        <textarea class="form-control" placeholder="Alamat lengkap" id="alamat"
                            name="alamat" style="height: 100px" required><?= htmlspecialchars($pelanggan['alamat'] ?? ''); ?></textarea>
                        <label for="alamat">Alamat Domisili</label>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-4 pt-2 border-top">
                        <a href="list.php" class="text-decoration-none text-muted fw-bold small">
                            <i class="fas fa-arrow-left me-1"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm" style="background-color: var(--sidebar-active); border: none;">
                            <i class="fas fa-save me-2"></i> Update Customer
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