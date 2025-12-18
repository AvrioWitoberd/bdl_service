<?php
// views/technicians/list.php

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

// --- LOGIKA DELETE ---
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $idDelete = (int) $_GET['id'];
    if ($teknisiModel->delete($idDelete)) {
        header("Location: list.php?msg=Technician deleted successfully");
        exit;
    } else {
        $msg = "Gagal menghapus data.";
    }
}

// --- PAGINATION & SEARCH ---
$limit = 10;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$search = $_GET['search'] ?? '';
$activeOnly = isset($_GET['active_only']);
$msg = $_GET['msg'] ?? '';

$teknisi = $teknisiModel->getAll($limit, $offset, $search, $activeOnly);
$totalTeknisi = $teknisiModel->countAll($search, $activeOnly);
$totalPages = ($totalTeknisi > 0) ? ceil($totalTeknisi / $limit) : 1;

// --- 2. LOAD HEADER ---
require_once __DIR__ . '/../partials/header.php';
?>

<style>
    /* Table Styling Overrides */
    .table thead th {
        background-color: #f8f9fa;
        color: #6c757d; /* text-muted manual */
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

    /* PERBAIKAN TOMBOL DI SINI: Menggunakan kode warna HEX langsung */
    .btn-primary-custom {
        background-color: #5D87FF; /* Warna Biru Utama */
        border-color: #5D87FF;
        color: white;
    }
    
    .btn-primary-custom:hover {
        background-color: #4a72e8; /* Warna saat cursor menempel (hover) */
        border-color: #4a72e8;
        color: white;
    }

    /* Switch Checkbox Custom */
    .form-check-input:checked {
        background-color: #5D87FF;
        border-color: #5D87FF;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4 animate__animated animate__fadeIn">
    <div>
        <h4 class="m-0 fw-bold text-dark">Data Teknisi</h4>
        <span class="text-muted small">Kelola data teknisi dan keahlian mereka</span>
    </div>
</div>

<?php if ($msg): ?>
    <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 animate__animated animate__fadeInUp" role="alert">
        <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($msg) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm animate__animated animate__fadeInUp animate__delay-1s" style="border-radius: 1rem;">
    <div class="card-body p-4">

        <div class="row g-3 mb-4 align-items-center justify-content-between">
            <div class="col-md-auto">
                <a href="create.php" class="btn btn-primary-custom rounded-pill px-4 shadow-sm">
                    <i class="fas fa-plus me-2"></i>Tambah Teknisi
                </a>
            </div>

            <div class="col-md-7 col-lg-6">
                <form method="GET" class="d-flex align-items-center gap-2 justify-content-md-end">
                    <div class="form-check form-switch me-2" title="Tampilkan hanya teknisi aktif">
                        <input class="form-check-input" type="checkbox" role="switch" id="activeFilter"
                            name="active_only" onchange="this.form.submit()" 
                            <?= $activeOnly ? 'checked' : ''; ?>>
                        <label class="form-check-label small fw-bold text-muted" for="activeFilter">Hanya Aktif</label>
                    </div>

                    <div class="input-group shadow-sm rounded-pill overflow-hidden" style="max-width: 350px;">
                        <input type="text" name="search" class="form-control border-0 ps-4 bg-light"
                            placeholder="Cari teknisi/keahlian..." value="<?= htmlspecialchars($search) ?>">
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
                        <th>Nama Teknisi</th>
                        <th>Keahlian</th>
                        <th>Kontak</th>
                        <th class="text-center">Status</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($teknisi)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-user-slash fa-3x mb-3 opacity-25"></i>
                                <p class="mb-0 fw-medium">Data teknisi tidak ditemukan.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($teknisi as $t): ?>
                            <tr>
                                <td class="ps-4 fw-bold text-muted">#<?= $t['id_teknisi'] ?></td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($t['nama_teknisi']) ?></div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-secondary border">
                                        <i class="fas fa-tools me-1 small"></i>
                                        <?= htmlspecialchars($t['keahlian']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="text-dark small">
                                            <i class="fas fa-phone fa-fw me-1 text-success"></i>
                                            <?= htmlspecialchars($t['no_hp']) ?>
                                        </span>
                                        <small class="text-muted">
                                            <i class="fas fa-envelope fa-fw me-1 text-primary"></i>
                                            <?= htmlspecialchars($t['email']) ?>
                                        </small>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <?php if ($t['status_aktif']): ?>
                                        <span class="badge rounded-pill bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3">
                                            <i class="fas fa-check-circle me-1"></i> Aktif
                                        </span>
                                    <?php else: ?>
                                        <span class="badge rounded-pill bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3">
                                            <i class="fas fa-times-circle me-1"></i> Non-Aktif
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="edit.php?id=<?= $t['id_teknisi'] ?>"
                                        class="btn btn-sm btn-outline-primary rounded-circle me-1" title="Edit">
                                        <i class="fas fa-pencil-alt"></i>
                                    </a>
                                    <a href="list.php?action=delete&id=<?= $t['id_teknisi'] ?>"
                                        class="btn btn-sm btn-outline-danger rounded-circle" title="Hapus"
                                        onclick="return confirm('Yakin ingin menghapus data teknisi ini?')">
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
                            <?php
                            $link = "?page=$i&search=" . urlencode($search);
                            if ($activeOnly) $link .= "&active_only=on";
                            ?>
                            <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                <a class="page-link shadow-none rounded-pill mx-1 <?= ($i == $page) ? 'bg-primary border-primary text-white' : 'text-primary' ?>"
                                    href="<?= $link ?>"><?= $i ?></a>
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