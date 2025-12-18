<?php
// views/spareparts/edit.php

// === 1. LOGIKA PHP ASLI ===
if (!isset($pdo)) {
    $pdo = require_once __DIR__ . '/../../config/database.php';
}
require_once __DIR__ . '/../../models/Sparepart.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$sparepartModel = new Sparepart($pdo);
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$sparepart = $sparepartModel->getById($id);

if (!$sparepart) {
    die("Spare part not found.");
}

$message = $_GET['msg'] ?? '';

// === 2. LOAD HEADER ===
require_once __DIR__ . '/../partials/header.php';
?>

<style>
    .card-custom {
        border: none;
        border-radius: 1rem;
        box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.08);
        background: #fff;
        overflow: hidden;
    }

    /* KEMBALI KE BIRU (Blue Accent) */
    .card-header-accent {
        background: linear-gradient(90deg, #5D87FF, #86b7fe);
        height: 6px;
        width: 100%;
    }

    .form-section-title {
        font-size: 0.85rem;
        font-weight: 700;
        text-transform: uppercase;
        color: #adb5bd;
        margin-bottom: 1.5rem;
        border-bottom: 1px solid #f1f1f1;
        padding-bottom: 0.5rem;
    }

    .input-group-text {
        background-color: #f8f9fa;
        border-right: none;
        color: #6c757d;
        border-color: #dee2e6;
    }

    .form-control {
        border-left: none;
        background-color: #f8f9fa;
        border-color: #dee2e6;
        padding: 0.7rem 1rem;
    }

    /* Fokus kembali ke Biru */
    .form-control:focus {
        background-color: #fff;
        box-shadow: none;
        border-color: #5D87FF; 
    }

    .input-group:focus-within .input-group-text {
        background-color: #fff;
        border-color: #5D87FF;
        color: #5D87FF;
    }

    /* Tombol Update Biru */
    .btn-update {
        background-color: #5D87FF;
        border-color: #5D87FF;
        color: white;
        padding: 10px 25px;
        font-weight: 600;
        transition: all 0.2s;
    }

    .btn-update:hover {
        background-color: #4a72e8;
        border-color: #4a72e8;
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(93, 135, 255, 0.3);
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4 animate__animated animate__fadeIn">
    <div>
        <h4 class="m-0 fw-bold text-dark">Edit Data Spare Part</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small text-muted mt-1">
                <li class="breadcrumb-item"><a href="list.php" class="text-decoration-none">Spare Part</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit #<?= $sparepart['id_sparepart'] ?></li>
            </ol>
        </nav>
    </div>
    <a href="list.php" class="btn btn-outline-secondary btn-sm shadow-sm rounded-pill px-3">
        <i class="fas fa-arrow-left me-1"></i> Kembali
    </a>
</div>

<?php if ($message): ?>
    <div class="alert alert-warning alert-dismissible fade show shadow-sm border-0 animate__animated animate__shakeX" role="alert">
        <div class="d-flex align-items-center">
            <i class="fas fa-info-circle me-2"></i>
            <div><?= htmlspecialchars($message) ?></div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card-custom animate__animated animate__fadeInUp">
    <div class="card-header-accent"></div>
    <div class="card-body p-4 p-md-5">
        
        <form method="POST" action="../../controllers/SparepartController.php">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id_sparepart" value="<?php echo $sparepart['id_sparepart']; ?>">
            
            <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                <h5 class="fw-bold m-0 text-secondary"><i class="fas fa-edit me-2 text-primary"></i>Formulir Perubahan</h5>
                <span class="badge bg-light text-secondary border">ID: <?= $sparepart['id_sparepart'] ?></span>
            </div>

            <div class="row g-5">
                <div class="col-lg-6">
                    <div class="form-section-title"><i class="fas fa-box-open me-2"></i>Identitas Produk</div>

                    <div class="mb-4">
                        <label for="nama_sparepart" class="form-label fw-bold small text-secondary">Nama Spare Part</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-microchip"></i></span>
                            <input type="text" class="form-control" id="nama_sparepart" name="nama_sparepart" 
                                   value="<?php echo htmlspecialchars($sparepart['nama_sparepart']); ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="merek" class="form-label fw-bold small text-secondary">Merek / Brand</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-tag"></i></span>
                            <input type="text" class="form-control" id="merek" name="merek" 
                                   value="<?php echo htmlspecialchars($sparepart['merek']); ?>">
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="form-section-title"><i class="fas fa-warehouse me-2"></i>Inventaris & Harga</div>

                    <div class="mb-4">
                        <label for="stok" class="form-label fw-bold small text-secondary">Jumlah Stok</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-cubes"></i></span>
                            <input type="number" class="form-control" id="stok" name="stok" 
                                   value="<?php echo $sparepart['stok']; ?>" min="0" required>
                            <span class="input-group-text bg-white border-start-0 text-muted small">Unit</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="harga" class="form-label fw-bold small text-secondary">Harga Jual Satuan</label>
                        <div class="input-group">
                            <span class="input-group-text fw-bold text-success">Rp</span>
                            <input type="number" class="form-control" id="harga" name="harga" 
                                   value="<?php echo $sparepart['harga']; ?>" step="0.01" min="0" required>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="my-4 opacity-10">

            <div class="d-flex justify-content-end gap-2">
                <a href="list.php" class="btn btn-light border px-4 rounded-pill">Batal</a>
                <button type="submit" class="btn btn-update rounded-pill px-4 shadow-sm">
                    <i class="fas fa-save me-2"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<?php
// === 5. LOAD FOOTER ===
require_once __DIR__ . '/../partials/footer.php';
?>