<?php
// views/spareparts/list.php

// --- 1. SETUP & LOGIKA UTAMA ---
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
require_once __DIR__ . '/../../models/Sparepart.php';

$sparepartModel = new Sparepart($pdo);
$role = $_SESSION['role'];

// --- LOGIKA DELETE ---
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $idDelete = (int) $_GET['id'];
    if ($sparepartModel->delete($idDelete)) {
        header("Location: list.php?msg=Spare part berhasil dihapus");
        exit;
    } else {
        $msg = "Gagal menghapus data. Data mungkin sedang digunakan di transaksi service.";
    }
}

// --- PAGINATION & SEARCH ---
$limit = 10;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$search = $_GET['search'] ?? '';
$msg = $_GET['msg'] ?? '';

$spareparts = $sparepartModel->getAll($limit, $offset, $search);
$totalSpareparts = $sparepartModel->countAll($search);
$totalPages = ($totalSpareparts > 0) ? ceil($totalSpareparts / $limit) : 1;

// --- 2. LOAD HEADER ---
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
    
    .table thead th {
        background-color: #f8f9fa;
        color: #6c757d;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        padding: 1rem;
        border-bottom: none;
    }
    
    .table tbody td {
        padding: 1rem;
        vertical-align: middle;
        border-bottom: 1px solid #f1f1f1;
    }

    /* Badge Stok */
    .badge-stock {
        background-color: rgba(93, 135, 255, 0.1);
        color: #5D87FF;
        border: 1px solid rgba(93, 135, 255, 0.2);
    }
    
    /* Tombol Biru Custom */
    .btn-primary-custom {
        background-color: #5D87FF;
        border-color: #5D87FF;
        color: white;
        font-weight: 500;
        transition: all 0.2s;
    }
    .btn-primary-custom:hover {
        background-color: #4a72e8;
        border-color: #4a72e8;
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 4px 6px rgba(93, 135, 255, 0.2);
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4 animate__animated animate__fadeIn">
    <div>
        <h4 class="m-0 fw-bold text-dark">Data Spare Part</h4>
        <span class="text-muted small">Kelola stok dan harga suku cadang elektronik</span>
    </div>
</div>

<?php if ($msg): ?>
    <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 animate__animated animate__fadeInUp" role="alert">
        <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($msg) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card-custom animate__animated animate__fadeInUp animate__delay-1s">
    <div class="card-body p-4">

        <div class="row g-3 mb-4 align-items-center justify-content-between">
            <div class="col-md-auto">
                <a href="create.php" class="btn btn-primary-custom rounded-pill px-4 shadow-sm">
                    <i class="fas fa-plus me-2"></i>Tambah Spare Part
                </a>
            </div>

            <div class="col-md-5">
                <form method="GET" class="d-flex">
                    <div class="input-group shadow-sm rounded-pill overflow-hidden">
                        <input type="text" name="search" class="form-control border-0 ps-4 bg-light"
                            placeholder="Cari nama spare part..." value="<?= htmlspecialchars($search) ?>">
                        <button type="submit" class="btn btn-primary-custom px-4">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="table-responsive rounded-3">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>Nama Spare Part</th>
                        <th class="text-center">Stok</th>
                        <th>Harga Satuan</th>
                        <th>Merek</th>
                        <th>Update Terakhir</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($spareparts)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fas fa-box-open fa-3x mb-3 opacity-25"></i>
                                <p class="mb-0 fw-medium">Data spare part tidak ditemukan.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($spareparts as $s): ?>
                            <tr>
                                <td class="ps-4 fw-bold text-muted">#<?= $s['id_sparepart'] ?></td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($s['nama_sparepart']) ?></div>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-stock rounded-pill px-3 py-2">
                                        <?= $s['stok'] ?> Unit
                                    </span>
                                </td>
                                <td class="fw-bold text-success">
                                    Rp <?= number_format($s['harga'], 0, ',', '.') ?>
                                </td>
                                <td>
                                    <span class="text-secondary small text-uppercase fw-bold">
                                        <?= htmlspecialchars($s['merek'] ?: '-') ?>
                                    </span>
                                </td>
                                <td class="text-muted small">
                                    <i class="far fa-clock me-1"></i>
                                    <?= date('d/m/Y', strtotime($s['tanggal_update'] ?? 'now')) ?>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="edit.php?id=<?= $s['id_sparepart'] ?>"
                                        class="btn btn-sm btn-outline-primary rounded-circle me-1" 
                                        data-bs-toggle="tooltip" title="Edit Data">
                                        <i class="fas fa-pencil-alt"></i>
                                    </a>
                                    <a href="list.php?action=delete&id=<?= $s['id_sparepart'] ?>"
                                        class="btn btn-sm btn-outline-danger rounded-circle" 
                                        title="Hapus Data"
                                        onclick="return confirm('Yakin ingin menghapus data spare part ini?')">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
            <div class="d-flex justify-content-end mt-4">
                <nav aria-label="Page navigation">
                    <ul class="pagination pagination-sm mb-0">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                <a class="page-link shadow-none rounded-pill mx-1 <?= ($i == $page) ? 'bg-primary border-primary text-white' : 'text-primary' ?>"
                                    href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php
// --- 5. LOAD FOOTER ---
require_once __DIR__ . '/../partials/footer.php';
?>