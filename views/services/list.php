<?php
// views/services/list.php

// 1. Setup Database & Model
if (!isset($pdo)) {
    $pdo = require_once __DIR__ . '/../../config/database.php';
}
require_once __DIR__ . '/../../models/Service.php';

// 2. Cek Session (kalau di header sudah ada, ini buat jaga-jaga aja)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 3. Cek Login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$serviceModel = new Service($pdo);
$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

// --- LOGIKA DELETE (Hanya Admin) ---
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    if ($role === 'admin') {
        $idDelete = (int) $_GET['id'];
        if ($serviceModel->delete($idDelete)) {
            echo "<script>alert('Service berhasil dihapus!'); window.location='list.php';</script>";
            exit;
        } else {
            $msg = "Gagal menghapus data.";
        }
    }
}

// --- SETUP PAGINATION & SEARCH ---
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';

// --- CONFIG FILTER BERDASARKAN ROLE ---
$filterStatus = '';
$filterTeknisi = '';
$filterPelanggan = '';

if ($role === 'teknisi') {
    $filterTeknisi = $user_id; // Teknisi hanya lihat job dia
} elseif ($role === 'pelanggan') {
    $filterPelanggan = $user_id; // Pelanggan HANYA lihat punya dia sendiri
}

// Ambil data
$services = $serviceModel->getServices($limit, $offset, $search, $filterStatus, $filterTeknisi, $filterPelanggan);
$totalServices = $serviceModel->countServices($search, $filterStatus, $filterTeknisi, $filterPelanggan);
$totalPages = ($totalServices > 0) ? ceil($totalServices / $limit) : 1;

// --- MULAI TAMPILAN ---
// Panggil Header yang sudah kita benerin tadi
include __DIR__ . '/../partials/header.php';
?>

<style>
    /* Badges */
    .badge-status { padding: 6px 12px; border-radius: 30px; font-weight: 600; font-size: 0.75rem; letter-spacing: 0.3px; }
    .bg-soft-success { background-color: #e6fffa; color: #00b894; border: 1px solid #b3f5e6; }
    .bg-soft-warning { background-color: #fff8e1; color: #f1c40f; border: 1px solid #ffeeba; }
    .bg-soft-info { background-color: #e3f2fd; color: #0984e3; border: 1px solid #bbdefb; }
    .bg-soft-danger { background-color: #ffebee; color: #d63031; border: 1px solid #ffcdd2; }
    
    /* Action Buttons */
    .btn-action { width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; transition: all 0.2s; border: none; text-decoration: none;}
    .btn-action-edit { background-color: #f0f7ff; color: #5D87FF; }
    .btn-action-edit:hover { background-color: #5D87FF; color: white; }
    .btn-action-delete { background-color: #fff5f5; color: #ff6b6b; }
    .btn-action-delete:hover { background-color: #ff6b6b; color: white; }
    
    .card-custom { border: none; border-radius: 1rem; box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.08); background: #fff; overflow: hidden; }
    .avatar-circle { width: 35px; height: 35px; background-color: #e6f2ff; color: #5D87FF; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 10px; }
</style>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 animate__animated animate__fadeIn">
    <div class="mb-3 mb-md-0">
        <h4 class="m-0 fw-bold text-dark">Daftar Service <?= ($role === 'pelanggan') ? 'Saya' : '' ?></h4>
        <span class="text-muted small">Kelola dan pantau status perbaikan perangkat.</span>
    </div>
    
    <?php if ($role !== 'teknisi'): ?>
        <a href="create.php" class="btn btn-primary shadow-sm rounded-pill px-4">
            <i class="fas fa-plus me-2"></i> Buat Service Baru
        </a>
    <?php endif; ?>
</div>

<?php if ($msg): ?>
    <div class="alert alert-info alert-dismissible fade show shadow-sm border-0 animate__animated animate__fadeIn" role="alert">
        <i class="fas fa-info-circle me-2"></i> <?= htmlspecialchars($msg) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card-custom animate__animated animate__fadeInUp">
    
    <div class="card-header bg-white border-bottom-0 pt-4 px-4 pb-0">
        <form method="GET" class="row g-3 align-items-center justify-content-end">
            <div class="col-md-6 col-lg-4">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted ps-3"><i class="fas fa-search"></i></span>
                    <input type="text" name="search" class="form-control bg-light border-start-0 ps-0" 
                           placeholder="Cari ID, atau Perangkat..." 
                           value="<?= htmlspecialchars($search) ?>">
                    <button type="submit" class="btn btn-primary px-3">Cari</button>
                </div>
            </div>
        </form>
    </div>

    <div class="card-body p-0 mt-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3 text-secondary text-uppercase small fw-bold">ID</th>
                        <?php if ($role !== 'pelanggan'): ?>
                            <th class="py-3 text-secondary text-uppercase small fw-bold">Pelanggan</th>
                        <?php endif; ?>
                        <th class="py-3 text-secondary text-uppercase small fw-bold">Perangkat</th>
                        <th class="py-3 text-secondary text-uppercase small fw-bold">Biaya</th>
                        <th class="py-3 text-secondary text-uppercase small fw-bold">Status</th>
                        <?php if ($role === 'admin'): ?><th class="py-3 text-secondary text-uppercase small fw-bold">Teknisi</th><?php endif; ?>
                        <th class="py-3 text-secondary text-uppercase small fw-bold">Tgl Masuk</th>
                        <?php if ($role !== 'pelanggan'): ?>
                            <th class="text-end pe-4 py-3 text-secondary text-uppercase small fw-bold">Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($services)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <div class="mb-3"><i class="fas fa-box-open fa-3x opacity-25"></i></div>
                                <h6 class="fw-bold">Tidak ada data ditemukan</h6>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($services as $s): ?>
                            <tr>
                                <td class="ps-4 fw-bold text-primary">#<?= $s['id_service'] ?></td>
                                
                                <?php if ($role !== 'pelanggan'): ?>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle"><i class="fas fa-user"></i></div>
                                            <span class="fw-bold text-dark"><?= htmlspecialchars($s['nama_pelanggan']) ?></span>
                                        </div>
                                    </td>
                                <?php endif; ?>

                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold text-dark"><?= htmlspecialchars($s['nama_perangkat'] ?? 'Device') ?></span>
                                        <small class="text-muted">
                                            <span class="badge bg-light text-secondary border me-1"><?= htmlspecialchars($s['merek'] ?? '-') ?></span>
                                            <?= htmlspecialchars(substr($s['keluhan'], 0, 30)) ?><?= strlen($s['keluhan']) > 30 ? '...' : '' ?>
                                        </small>
                                    </div>
                                </td>

                                <td><span class="fw-bold text-success font-monospace">Rp <?= number_format($s['biaya_service'], 0, ',', '.') ?></span></td>

                                <td>
                                    <?php
                                    $st = strtolower($s['nama_status']);
                                    $bgClass = 'bg-soft-warning';
                                    if (strpos($st, 'selesai') !== false || strpos($st, 'ambil') !== false) $bgClass = 'bg-soft-success';
                                    elseif (strpos($st, 'batal') !== false) $bgClass = 'bg-soft-danger';
                                    elseif (strpos($st, 'proses') !== false || strpos($st, 'diagnosa') !== false) $bgClass = 'bg-soft-info';
                                    ?>
                                    <span class="badge badge-status <?= $bgClass ?>"><?= htmlspecialchars($s['nama_status']) ?></span>
                                </td>

                                <?php if ($role === 'admin'): ?>
                                    <td>
                                        <?php if ($s['nama_teknisi']): ?>
                                            <small class="text-muted"><i class="fas fa-wrench me-1"></i><?= htmlspecialchars($s['nama_teknisi']) ?></small>
                                        <?php else: ?>
                                            <span class="badge bg-light text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>

                                <td><small class="text-muted"><?= date('d M Y', strtotime($s['tanggal_masuk'])) ?></small></td>

                                <?php if ($role !== 'pelanggan'): ?>
                                    <td class="text-end pe-4">
                                        <div class="d-inline-flex gap-1">
                                            <a href="edit.php?id=<?= $s['id_service'] ?>" class="btn-action btn-action-edit" title="Edit"><i class="fas fa-pencil-alt"></i></a>
                                            <?php if ($role === 'admin'): ?>
                                                <a href="list.php?action=delete&id=<?= $s['id_service'] ?>" class="btn-action btn-action-delete" onclick="return confirm('Hapus data #<?= $s['id_service'] ?>?')" title="Hapus"><i class="fas fa-trash-alt"></i></a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-footer bg-white border-top-0 py-3">
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-end mb-0">
                <?php if ($page > 1): ?>
                    <li class="page-item"><a class="page-link border-0" href="?page=<?= $page - 1 ?>&search=<?= $search ?>">&laquo;</a></li>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                        <a class="page-link border-0 rounded-circle mx-1" href="?page=<?= $i ?>&search=<?= $search ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <li class="page-item"><a class="page-link border-0" href="?page=<?= $page + 1 ?>&search=<?= $search ?>">&raquo;</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</div>

<?php
// Panggil Footer (Menutup div container dan div content)
include __DIR__ . '/../partials/footer.php';
?>