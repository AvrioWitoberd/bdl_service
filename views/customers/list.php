<?php
// views/customers/list.php

// 1. Panggil database SEKALI saja
$pdo = require_once '../../config/database.php';
require_once '../../models/Pelanggan.php';

session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'teknisi')) {
    header("Location: ../auth/login.php");
    exit;
}

$pelangganModel = new Pelanggan($pdo);
$role = $_SESSION['role'];

// --- LOGIKA DELETE (AJAX/Direct Handle) ---
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    if ($role === 'admin') {
        $idDelete = (int) $_GET['id'];
        if ($pelangganModel->delete($idDelete)) {
            header("Location: list.php?msg=Pelanggan berhasil dihapus");
            exit;
        } else {
            $msg = "Gagal menghapus pelanggan.";
        }
    }
}

// --- PAGINATION & SEARCH ---
$limit = 10;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$search = $_GET['search'] ?? '';
$msg = $_GET['msg'] ?? '';

$pelanggan = $pelangganModel->getAll($limit, $offset, $search);
$totalPelanggan = $pelangganModel->countAll($search);
$totalPages = ($totalPelanggan > 0) ? ceil($totalPelanggan / $limit) : 1;
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pelanggan - Service ABC</title>

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
            --text-muted: #6c757d;
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
        #wrapper {
            display: flex;
            width: 100%;
            align-items: stretch;
        }

        #sidebar {
            min-width: var(--sidebar-width);
            max-width: var(--sidebar-width);
            min-height: 100vh;
            background: linear-gradient(180deg, var(--sidebar-bg-start) 0%, var(--sidebar-bg-end) 100%);
            color: #fff;
            transition: all 0.3s;
            position: fixed;
            z-index: 1050;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }

        #sidebar.toggled {
            margin-left: calc(-1 * var(--sidebar-width));
        }

        .sidebar-brand {
            padding: 1.5rem;
            text-align: center;
            font-weight: 700;
            font-size: 1.2rem;
            background: rgba(0, 0, 0, 0.15);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        #sidebar ul.components {
            padding: 1.5rem 0;
            list-style: none;
            padding-left: 0;
        }

        #sidebar ul li a {
            padding: 14px 25px;
            display: flex;
            align-items: center;
            color: rgba(255, 255, 255, 0.75);
            text-decoration: none;
            transition: all 0.2s;
            border-left: 4px solid transparent;
        }

        #sidebar ul li a:hover,
        #sidebar ul li a.active {
            color: #fff;
            background: rgba(255, 255, 255, 0.1);
            border-left-color: var(--accent-blue);
        }

        #sidebar ul li a i {
            margin-right: 16px;
            font-size: 1.1rem;
            width: 24px;
            text-align: center;
        }

        /* CONTENT */
        #content {
            width: 100%;
            margin-left: var(--sidebar-width);
            padding: 2rem;
            min-height: 100vh;
            transition: all 0.3s;
        }

        #content.toggled {
            margin-left: 0;
        }

        .top-navbar {
            background: #fff;
            box-shadow: var(--soft-shadow);
            border-radius: 0.75rem;
            padding: 0.8rem 1.5rem;
        }

        /* TABEL & CARD CUSTOM */
        .card-custom {
            border: none;
            border-radius: 1rem;
            box-shadow: var(--soft-shadow);
            background: #fff;
            overflow: hidden;
        }

        .table thead th {
            background-color: #f8f9fa;
            color: var(--text-muted);
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

        /* Buttons & Inputs */
        .btn-primary-custom {
            background-color: var(--accent-blue);
            border-color: var(--accent-blue);
            color: white;
        }

        .btn-primary-custom:hover {
            background-color: #4a72e8;
            border-color: #4a72e8;
        }

        .search-input {
            border-radius: 50rem 0 0 50rem;
            border-right: none;
        }

        .search-btn {
            border-radius: 0 50rem 50rem 0;
        }

        /* Responsive */
        @media (max-width: 991.98px) {
            #sidebar {
                margin-left: calc(-1 * var(--sidebar-width));
            }

            #sidebar.toggled {
                margin-left: 0;
            }

            #content {
                margin-left: 0;
                padding: 1.5rem;
            }

            #sidebarCollapse span {
                display: none;
            }
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

                <li><a href="list.php" class="active"><i class="fas fa-users"></i> Pelanggan</a></li>

                <li><a href="../technicians/list.php"><i class="fas fa-user-cog"></i> Teknisi</a></li>
                <li><a href="../spareparts/list.php"><i class="fas fa-boxes-stacked"></i> Spare Part</a></li>
                <li><a href="../services/list.php"><i class="fas fa-clipboard-list"></i> Data Service</a></li>
                <li><a href="../reports/index.php"><i class="fas fa-chart-line"></i> Laporan</a></li>

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
                    <button type="button" id="sidebarCollapse" class="btn btn-light text-primary shadow-sm border-0"
                        style="background: #eef2ff;">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="ms-auto d-flex align-items-center">
                        <span class="d-none d-sm-block small text-muted me-2">Login sebagai:</span>
                        <h6 class="m-0 fw-bold me-3" style="color: var(--accent-blue);">
                            <?= htmlspecialchars($_SESSION['nama_user'] ?? 'User') ?></h6>
                        <div class="rounded-circle shadow-sm d-flex align-items-center justify-content-center"
                            style="width: 40px; height: 40px; background: linear-gradient(45deg, var(--accent-blue), #a2c2ff); color: white;">
                            <i class="fas fa-user"></i>
                        </div>
                    </div>
                </div>
            </nav>

            <div class="d-flex justify-content-between align-items-center mb-4 animate__animated animate__fadeIn">
                <div>
                    <h4 class="m-0 fw-bold text-dark">Data Pelanggan</h4>
                    <span class="text-muted small">Kelola data pelanggan service center</span>
                </div>
            </div>

            <?php if ($msg): ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 animate__animated animate__fadeInUp"
                    role="alert">
                    <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($msg) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card-custom animate__animated animate__fadeInUp animate__delay-1s">
                <div class="card-body p-4">

                    <div class="row g-3 mb-4 align-items-center justify-content-between">
                        <div class="col-md-auto">
                            <?php if ($role === 'admin'): ?>
                                <a href="create.php" class="btn btn-primary-custom rounded-pill px-4 shadow-sm">
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
                                                    <span class="text-dark"><i
                                                            class="fas fa-phone-alt fa-fw me-1 text-success small"></i>
                                                        <?= htmlspecialchars($p['no_hp'] ?? '-') ?></span>
                                                    <small class="text-muted"><i
                                                            class="fas fa-envelope fa-fw me-1 text-primary small"></i>
                                                        <?= htmlspecialchars($p['email'] ?? '-') ?></small>
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
                                                href="?page=<?= $i ?>&search=<?= $search ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>
                                </ul>
                            </nav>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle Sidebar Script (Sama seperti Dashboard)
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

        // Auto-hide sidebar on mobile
        if (window.innerWidth < 992) {
            sidebar.classList.add('toggled');
            content.classList.add('toggled');
        }
    </script>
</body>

</html>