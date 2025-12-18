<?php
// views/customers/list.php

// --- 1. SETUP & LOGIKA PHP ---
// Pastikan session dimulai (jika belum ada di header, tapi untuk aman start aja cek status)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek Auth Spesifik Halaman Ini
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'teknisi')) {
    // Sesuaikan path redirect ke login jika perlu
    header("Location: ../../auth/login.php"); 
    exit;
}

// Panggil Model & Database
$pdo = require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Pelanggan.php';

$pelangganModel = new Pelanggan($pdo);
$role = $_SESSION['role'];

// --- LOGIKA DELETE (Tetap Sama) ---
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    if ($role === 'admin') {
        $idDelete = (int) $_GET['id'];
        if ($pelangganModel->delete($idDelete)) {
            header("Location: list.php?msg=Pelanggan berhasil dihapus");
            exit;
        } else {
            $msg = "Gagal menghapus pelanggan. Data sedang digunakan.";
        }
    } else {
        $msg = "Anda tidak memiliki akses untuk menghapus.";
    }
}

// --- PAGINATION & SEARCH (Tetap Sama) ---
$limit = 10;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$search = $_GET['search'] ?? '';

if (!isset($msg)) { $msg = $_GET['msg'] ?? ''; }

$pelanggan = $pelangganModel->getAll($limit, $offset, $search);
$totalPelanggan = $pelangganModel->countAll($search);
$totalPages = ($totalPelanggan > 0) ? ceil($totalPelanggan / $limit) : 1;

// --- 2. LOAD HEADER ---
// Path mundur satu folder ke 'views', lalu masuk 'partials'
require_once __DIR__ . '/../partials/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 animate__animated animate__fadeIn">
    <div>
        <h4 class="m-0 fw-bold text-dark">Data Pelanggan</h4>
        <span class="text-muted small">Kelola data pelanggan service center</span>
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
                <?php if ($role === 'admin'): ?>
                    <a href="create.php" class="btn btn-primary rounded-pill px-4 shadow-sm" style="background-color: var(--sidebar-active); border: none;">
                        <i class="fas fa-plus me-2"></i>Tambah Pelanggan
                    </a>
                <?php endif; ?>
            </div>
            <div class="col-md-5">
                <form method="GET" class="d-flex">
                    <div class="input-group shadow-sm rounded-pill overflow-hidden">
                        <input type="text" name="search" class="form-control border-0 ps-4 bg-light"
                            placeholder="Cari nama, hp, atau email..."
                            value="<?= htmlspecialchars($search) ?>">
                        <button type="submit" class="btn btn-primary px-4" style="background-color: var(--sidebar-active); border: none;">
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
                        <th>Nama Lengkap</th>
                        <th>Kontak</th>
                        <th>Alamat</th>
                        <th>Tanggal Daftar</th>
                        <?php if ($role === 'admin'): ?>
                            <th class="text-end pe-4">Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pelanggan)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-folder-open fa-3x mb-3 opacity-25"></i>
                                <p class="mb-0 fw-medium">Data pelanggan tidak ditemukan.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($pelanggan as $p): ?>
                            <tr>
                                <td class="ps-4 fw-bold text-muted">#<?= $p['id_pelanggan'] ?></td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($p['nama']) ?></div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="text-dark">
                                            <i class="fas fa-phone-alt fa-fw me-1 text-success small"></i>
                                            <?= htmlspecialchars($p['no_hp'] ?? '-') ?>
                                        </span>
                                        <small class="text-muted">
                                            <i class="fas fa-envelope fa-fw me-1 text-primary small"></i>
                                            <?= htmlspecialchars($p['email'] ?? '-') ?>
                                        </small>
                                    </div>
                                </td>
                                <td class="text-muted small" style="max-width: 250px;">
                                    <?= htmlspecialchars($p['alamat'] ?? '-') ?>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <i class="far fa-calendar-alt me-1"></i>
                                        <?= date('d/m/Y', strtotime($p['tanggal_daftar'])) ?>
                                    </span>
                                </td>

                                <?php if ($role === 'admin'): ?>
                                    <td class="text-end pe-4">
                                        <a href="edit.php?id=<?= $p['id_pelanggan'] ?>"
                                            class="btn btn-sm btn-outline-primary rounded-circle me-1" title="Edit">
                                            <i class="fas fa-pencil-alt"></i>
                                        </a>
                                        <a href="list.php?action=delete&id=<?= $p['id_pelanggan'] ?>"
                                            class="btn btn-sm btn-outline-danger rounded-circle" title="Hapus"
                                            onclick="return confirm('Yakin ingin menghapus data pelanggan ini?')">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </td>
                                <?php endif; ?>
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
// --- 4. LOAD FOOTER ---
require_once __DIR__ . '/../partials/footer.php';
?>