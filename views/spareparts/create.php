<?php
// views/spareparts/create.php

// --- 1. SETUP & LOGIKA UTAMA ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek Auth (Hanya Admin)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

// Load Config (jika belum ada)
if (!isset($pdo)) {
    $pdo = require_once __DIR__ . '/../../config/database.php';
}

// Ambil error & data lama dari session (Flash Data)
$message = $_GET['error'] ?? '';
$old = $_SESSION['old_form'] ?? [];

// Hapus data lama dari session agar form bersih saat refresh
if (isset($_SESSION['old_form'])) {
    unset($_SESSION['old_form']);
}

// --- 2. LOAD HEADER ---
require_once __DIR__ . '/../partials/header.php';
?>

<style>
    .card-custom {
        border: none;
        border-radius: 1rem;
        box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.08);
        background: #fff;
    }

    .form-section-title {
        font-size: 0.85rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
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

    .form-control:focus {
        background-color: #fff;
        box-shadow: none;
        border-color: #86b7fe;
    }

    .input-group:focus-within .input-group-text {
        background-color: #fff;
        border-color: #86b7fe;
        color: var(--accent-blue);
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4 animate__animated animate__fadeIn">
    <div>
        <h4 class="m-0 fw-bold text-dark">Tambah Stok Barang</h4>
        <span class="text-muted small">Input data spare part baru ke dalam inventaris.</span>
    </div>
    <a href="list.php" class="btn btn-outline-secondary btn-sm shadow-sm rounded-pill px-3">
        <i class="fas fa-arrow-left me-1"></i> Kembali
    </a>
</div>

<?php if ($message): ?>
    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 animate__animated animate__shakeX" role="alert">
        <div class="d-flex align-items-center">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <div><?= htmlspecialchars($message) ?></div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card-custom animate__animated animate__fadeInUp">
    <div class="card-body p-4 p-md-5">
        <form method="POST" action="../../controllers/SparepartController.php">
            <input type="hidden" name="action" value="create">

            <div class="row g-5">
                <div class="col-lg-6">
                    <div class="form-section-title">
                        <i class="fas fa-microchip me-2"></i>Identitas Produk
                    </div>

                    <div class="mb-4">
                        <label for="nama_sparepart" class="form-label fw-bold small text-secondary">Nama Spare Part <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-box"></i></span>
                            <input type="text" class="form-control" id="nama_sparepart" name="nama_sparepart"
                                value="<?php echo htmlspecialchars($old['nama_sparepart'] ?? ''); ?>"
                                placeholder="Contoh: LCD Samsung A50 Original" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="merek" class="form-label fw-bold small text-secondary">Merek / Brand</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-tag"></i></span>
                            <input type="text" class="form-control" id="merek" name="merek"
                                value="<?php echo htmlspecialchars($old['merek'] ?? ''); ?>"
                                placeholder="Contoh: Samsung, Asus, KW Super">
                        </div>
                        <div class="form-text small ms-1">Kosongkan jika tidak ada merek spesifik.</div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="form-section-title">
                        <i class="fas fa-coins me-2"></i>Inventaris & Harga
                    </div>

                    <div class="mb-4">
                        <label for="stok" class="form-label fw-bold small text-secondary">Jumlah Stok Awal <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-cubes"></i></span>
                            <input type="number" class="form-control" id="stok" name="stok"
                                value="<?php echo htmlspecialchars($old['stok'] ?? ''); ?>" 
                                min="0" placeholder="0" required>
                            <span class="input-group-text bg-white text-muted small border-start-0">Unit</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="harga" class="form-label fw-bold small text-secondary">Harga Jual Satuan <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text fw-bold text-success">Rp</span>
                            <input type="number" class="form-control" id="harga" name="harga"
                                value="<?php echo htmlspecialchars($old['harga'] ?? ''); ?>" 
                                step="0.01" min="0" placeholder="0" required>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="my-4 opacity-10">

            <div class="d-flex justify-content-end gap-2">
                <a href="list.php" class="btn btn-light border px-4 rounded-pill">Batal</a>
                <button type="submit" class="btn btn-outline-primary rounded-pill px-4 shadow-sm" style="color: var(--accent-blue); border-color: var(--accent-blue);">
                    <i class="fas fa-save me-2"></i> Simpan Spare Part
                </button>
            </div>
        </form>
    </div>
</div>

<?php
// --- 5. LOAD FOOTER ---
require_once __DIR__ . '/../partials/footer.php';
?>