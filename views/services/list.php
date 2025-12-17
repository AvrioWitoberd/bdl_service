<?php
// views/services/list.php

// 1. Panggil database SEKALI saja & simpan ke $pdo
if (!isset($pdo)) {
    // Sesuaikan path jika perlu, asumsi struktur folder standar
    $pdo = require_once __DIR__ . '/../../config/database.php';
}
require_once __DIR__ . '/../../models/Service.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
            header("Location: list.php?msg=Service deleted successfully");
            exit;
        } else {
            $msg = "Gagal menghapus data.";
        }
    } else {
        $msg = "Akses ditolak.";
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
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Service - Service ABC</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --sidebar-bg-start: #2c3e50;
            --sidebar-bg-end: #34495e;
            --accent-blue: #5D87FF;
            --body-bg: #f5f7fa;
            --text-dark: #343a40;
            --soft-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.08);
            --sidebar-width: 260px;
        }

        body {
            background-color: var(--body-bg);
            font-family: 'Inter', sans-serif;
            color: var(--text-dark);
            overflow-x: hidden;
        }

        /* SIDEBAR & LAYOUT */
        #wrapper { display: flex; width: 100%; align-items: stretch; }
        #sidebar {
            min-width: var(--sidebar-width); max-width: var(--sidebar-width); min-height: 100vh;
            background: linear-gradient(180deg, var(--sidebar-bg-start) 0%, var(--sidebar-bg-end) 100%);
            color: #fff; transition: all 0.3s; position: fixed; z-index: 1050;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }
        #sidebar.toggled { margin-left: calc(-1 * var(--sidebar-width)); }
        .sidebar-brand { padding: 1.5rem; text-align: center; font-weight: 700; font-size: 1.2rem; background: rgba(0,0,0,0.15); border-bottom: 1px solid rgba(255,255,255,0.05); }
        #sidebar ul.components { padding: 1.5rem 0; list-style: none; padding-left: 0; }
        #sidebar ul li a { padding: 14px 25px; display: flex; align-items: center; color: rgba(255,255,255,0.75); text-decoration: none; transition: all 0.2s; border-left: 4px solid transparent; }
        #sidebar ul li a:hover, #sidebar ul li a.active { color: #fff; background: rgba(255,255,255,0.1); border-left-color: var(--accent-blue); }
        #sidebar ul li a i { margin-right: 16px; font-size: 1.1rem; width: 24px; text-align: center; }

        /* CONTENT */
        #content { width: 100%; margin-left: var(--sidebar-width); padding: 2rem; min-height: 100vh; transition: all 0.3s; }
        #content.toggled { margin-left: 0; }
        .top-navbar { background: #fff; box-shadow: var(--soft-shadow); border-radius: 0.75rem; padding: 0.8rem 1.5rem; }

        /* TABLE & CARD STYLES */
        .card-custom { border: none; border-radius: 1rem; box-shadow: var(--soft-shadow); background: #fff; overflow: hidden; }
        .table { margin-bottom: 0; }
        .table thead th { background-color: #f8f9fa; border-bottom: 2px solid #e9ecef; font-weight: 600; text-transform: uppercase; font-size: 0.8rem; color: #6c757d; padding: 1rem; }
        .table tbody td { vertical-align: middle; padding: 1rem; border-bottom: 1px solid #f1f1f1; font-size: 0.9rem; }
        .table-hover tbody tr:hover { background-color: #f8f9ff; }
        
        .avatar-circle { width: 35px; height: 35px; background-color: #e6f2ff; color: var(--accent-blue); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.9rem; margin-right: 10px; }
        
        /* Badges */
        .badge-status { padding: 6px 12px; border-radius: 30px; font-weight: 600; font-size: 0.75rem; letter-spacing: 0.3px; }
        .bg-soft-success { background-color: #e6fffa; color: #00b894; border: 1px solid #b3f5e6; }
        .bg-soft-warning { background-color: #fff8e1; color: #f1c40f; border: 1px solid #ffeeba; }
        .bg-soft-info { background-color: #e3f2fd; color: #0984e3; border: 1px solid #bbdefb; }
        .bg-soft-danger { background-color: #ffebee; color: #d63031; border: 1px solid #ffcdd2; }

        /* Action Buttons */
        .btn-action { width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; transition: all 0.2s; border: none; }
        .btn-action-edit { background-color: #f0f7ff; color: var(--accent-blue); }
        .btn-action-edit:hover { background-color: var(--accent-blue); color: white; }
        .btn-action-delete { background-color: #fff5f5; color: #ff6b6b; }
        .btn-action-delete:hover { background-color: #ff6b6b; color: white; }

        /* Pagination */
        .pagination .page-item .page-link { border: none; border-radius: 8px; margin: 0 3px; color: #6c757d; }
        .pagination .page-item.active .page-link { background-color: var(--accent-blue); color: white; box-shadow: 0 4px 10px rgba(93, 135, 255, 0.3); }

        @media (max-width: 991.98px) {
            #sidebar { margin-left: calc(-1 * var(--sidebar-width)); }
            #sidebar.toggled { margin-left: 0; }
            #content { margin-left: 0; padding: 1.5rem; }
            #sidebarCollapse span { display: none; }
        }
    </style>
</head>

<body>
    <div id="wrapper">
        
        <nav id="sidebar" class="animate__animated animate__slideInLeft">
            <div class="sidebar-brand">
                <i class="fas fa-robot me-2"></i> SERVICE ABC
            </div>
            <ul class="components">
                <li><a href="../dashboard.php"><i class="fas fa-th-large"></i> Dashboard</a></li>
                <li><a href="../../index.php"><i class="fas fa-home"></i> Halaman Depan</a></li>
                <li class="my-1 border-top border-secondary opacity-25"></li>
                
                <?php if ($role === 'admin'): ?>
                        <li><a href="../customers/list.php"><i class="fas fa-users"></i> Pelanggan</a></li>
                        <li><a href="../technicians/list.php"><i class="fas fa-user-cog"></i> Teknisi</a></li>
                        <li><a href="../spareparts/list.php"><i class="fas fa-boxes-stacked"></i> Spare Part</a></li>
                <?php endif; ?>
                
                <li><a href="list.php" class="active"><i class="fas fa-clipboard-list"></i> Data Service</a></li>
                
                <?php if ($role === 'admin'): ?>
                        <li><a href="../reports/index.php"><i class="fas fa-chart-line"></i> Laporan</a></li>
                <?php endif; ?>
                
                <li style="margin-top: auto; padding-top: 2rem;">
                    <a href="../../auth/logout.php" style="color: #ffb3b3;">
                        <i class="fas fa-power-off"></i> Logout
                    </a>
                </li>
            </ul>
        </nav>

        <div id="content">

            <nav class="navbar navbar-expand top-navbar mb-4 animate__animated animate__fadeInDown">
                <div class="container-fluid px-0">
                    <button type="button" id="sidebarCollapse" class="btn btn-light text-primary shadow-sm border-0" style="background: #eef2ff;">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="ms-auto d-flex align-items-center">
                        <span class="d-none d-sm-block small text-muted me-2">Login sebagai:</span>
                        <h6 class="m-0 fw-bold me-3" style="color: var(--accent-blue); text-transform: capitalize;"><?= $role ?></h6>
                        <div class="rounded-circle shadow-sm d-flex align-items-center justify-content-center"
                            style="width: 40px; height: 40px; background: linear-gradient(45deg, var(--accent-blue), #a2c2ff); color: white;">
                            <i class="fas fa-user"></i>
                        </div>
                    </div>
                </div>
            </nav>

            <?php if ($msg): ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 animate__animated animate__fadeIn" role="alert">
                    <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($msg) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

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

            <div class="card-custom animate__animated animate__fadeInUp">
                
                <div class="card-header bg-white border-bottom-0 pt-4 px-4 pb-0">
                    <form method="GET" class="row g-3 align-items-center">
                        <div class="col-md-6 col-lg-4 ms-auto">
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted ps-3"><i class="fas fa-search"></i></span>
                                <input type="text" name="search" class="form-control bg-light border-start-0 ps-0" 
                                       placeholder="Cari ID, Pelanggan, atau Perangkat..." 
                                       value="<?= htmlspecialchars($search) ?>">
                                <button type="submit" class="btn btn-primary px-3">Cari</button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th class="ps-4">ID</th>
                                    <?php if ($role !== 'pelanggan'): ?>
                                            <th>Pelanggan</th>
                                    <?php endif; ?>
                                    <th>Perangkat & Keluhan</th>
                                    <th>Estimasi Biaya</th>
                                    <th>Status</th>
                                    <?php if ($role === 'admin'): ?><th>Teknisi</th><?php endif; ?>
                                    <th>Tanggal Masuk</th>
                                    <?php if ($role !== 'pelanggan'): ?>
                                            <th class="text-end pe-4">Aksi</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($services)): ?>
                                        <tr>
                                            <td colspan="8" class="text-center py-5 text-muted">
                                                <div class="mb-3"><i class="fas fa-box-open fa-3x opacity-25"></i></div>
                                                <h6 class="fw-bold">Tidak ada data ditemukan</h6>
                                                <small>Silakan coba kata kunci pencarian lain atau buat data baru.</small>
                                            </td>
                                        </tr>
                                <?php else: ?>
                                        <?php foreach ($services as $s): ?>
                                            <tr>
                                                <td class="ps-4 fw-bold text-primary">#<?= $s['id_service'] ?></td>
                                        
                                                <?php if ($role !== 'pelanggan'): ?>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <div class="avatar-circle">
                                                                    <i class="fas fa-user"></i>
                                                                </div>
                                                                <div>
                                                                    <div class="fw-bold text-dark"><?= htmlspecialchars($s['nama_pelanggan']) ?></div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                <?php endif; ?>

                                                <td>
                                                    <div class="d-flex flex-column">
                                                        <span class="fw-bold text-dark"><?= htmlspecialchars($s['nama_perangkat'] ?? 'Device') ?></span>
                                                        <small class="text-muted">
                                                            <span class="badge bg-light text-secondary border me-1"><?= htmlspecialchars($s['merek'] ?? '-') ?></span>
                                                            <?= htmlspecialchars(substr($s['keluhan'], 0, 30)) ?>        <?= strlen($s['keluhan']) > 30 ? '...' : '' ?>
                                                        </small>
                                                    </div>
                                                </td>

                                                <td>
                                                    <span class="fw-bold text-success font-monospace">
                                                        Rp <?= number_format($s['biaya_service'], 0, ',', '.') ?>
                                                    </span>
                                                </td>

                                                <td>
                                                    <?php
                                                    $st = strtolower($s['nama_status']);
                                                    $bgClass = 'bg-soft-warning'; // Default
                                            
                                                    if (strpos($st, 'selesai') !== false || strpos($st, 'ambil') !== false || strpos($st, 'siap') !== false) {
                                                        $bgClass = 'bg-soft-success';
                                                    } elseif (strpos($st, 'proses') !== false || strpos($st, 'diagnosa') !== false || strpos($st, 'diterima') !== false || strpos($st, 'uji') !== false) {
                                                        $bgClass = 'bg-soft-info';
                                                    } elseif (strpos($st, 'batal') !== false || strpos($st, 'gagal') !== false) {
                                                        $bgClass = 'bg-soft-danger';
                                                    }
                                                    ?>
                                                    <span class="badge badge-status <?= $bgClass ?>">
                                                        <?= htmlspecialchars($s['nama_status']) ?>
                                                    </span>
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

                                                <td>
                                                    <small class="text-muted">
                                                        <i class="far fa-calendar-alt me-1"></i>
                                                        <?= date('d M Y', strtotime($s['tanggal_masuk'])) ?>
                                                    </small>
                                                </td>

                                                <?php if ($role !== 'pelanggan'): ?>
                                                        <td class="text-end pe-4">
                                                            <div class="d-inline-flex gap-1">
                                                                <a href="edit.php?id=<?= $s['id_service'] ?>" class="btn-action btn-action-edit" title="Edit / Update">
                                                                    <i class="fas fa-pencil-alt"></i>
                                                                </a>
                                                    
                                                                <?php if ($role === 'admin'): ?>
                                                                        <a href="list.php?action=delete&id=<?= $s['id_service'] ?>" 
                                                                           class="btn-action btn-action-delete" 
                                                                           onclick="return confirm('Apakah Anda yakin ingin menghapus data service #<?= $s['id_service'] ?>?')" 
                                                                           title="Hapus">
                                                                            <i class="fas fa-trash-alt"></i>
                                                                        </a>
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
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= $search ?>" aria-label="Previous">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?>&search=<?= $search ?>"><?= $i ?></a>
                                    </li>
                            <?php endfor; ?>

                            <?php if ($page < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= $search ?>" aria-label="Next">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle Sidebar Script
        const sidebar = document.getElementById('sidebar');
        const content = document.getElementById('content');
        const sidebarCollapseBtn = document.getElementById('sidebarCollapse');

        sidebarCollapseBtn.addEventListener('click', function () {
            sidebar.classList.toggle('toggled');
            content.classList.toggle('toggled');
            const icon = this.querySelector('i');
            if (sidebar.classList.contains('toggled')) {
                if (window.innerWidth < 992) { icon.classList.replace('fa-bars', 'fa-arrow-right'); }
                else { icon.classList.replace('fa-arrow-left', 'fa-bars'); }
            } else {
                if (window.innerWidth < 992) { icon.classList.replace('fa-arrow-right', 'fa-bars'); }
                else { icon.classList.replace('fa-bars', 'fa-arrow-left'); }
            }
        });

        if (window.innerWidth < 992) {
            sidebar.classList.add('toggled');
            content.classList.add('toggled');
        }
    </script>
</body>
</html>