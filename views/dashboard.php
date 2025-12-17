<?php
// views/dashboard.php
session_start();

// 1. Konfigurasi Database
// Pastikan path ini sesuai dengan struktur foldermu
$pdo = require_once __DIR__ . '/../config/database.php';

if (!$pdo) {
    die("Koneksi database gagal.");
}

// 2. Cek Akses (Hanya Admin)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // Jika bukan admin, tendang ke login atau halaman depan
    header("Location: /bdl_service/auth/login.php");
    exit;
}

/* ================================
   QUERY DATA STATISTIK
   ================================ */

// Service Pending
$stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM service WHERE id_status NOT IN (SELECT id_status FROM status_perbaikan WHERE nama_status IN ('Selesai Diperbaiki', 'Siap Diambil', 'Diambil Pelanggan'))");
$stmt->execute();
$pending_count = $stmt->fetch()['total'] ?? 0;

// Service Selesai
$stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM service WHERE id_status IN (SELECT id_status FROM status_perbaikan WHERE nama_status IN ('Selesai Diperbaiki', 'Siap Diambil', 'Diambil Pelanggan'))");
$stmt->execute();
$completed_count = $stmt->fetch()['total'] ?? 0;

// Total pelanggan
$stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM pelanggan");
$stmt->execute();
$customer_count = $stmt->fetch()['total'] ?? 0;

// Teknisi aktif
$stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM teknisi WHERE status_aktif = 'true' OR status_aktif = true");
$stmt->execute();
$technician_count = $stmt->fetch()['total'] ?? 0;

// 5 Service Terbaru
$stmt = $pdo->prepare("
    SELECT s.id_service, s.keluhan, s.tanggal_masuk, sp.nama_status, p.nama AS nama_pelanggan, d.jenis_perangkat, d.merek, d.nama_perangkat
    FROM service s
    JOIN perangkat d ON s.id_perangkat = d.id_perangkat
    JOIN pelanggan p ON s.id_pelanggan = p.id_pelanggan
    JOIN status_perbaikan sp ON s.id_status = sp.id_status
    ORDER BY s.tanggal_masuk DESC LIMIT 5
");
$stmt->execute();
$recent_services = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Service Center</title>

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

        /* SIDEBAR */
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

        /* CARDS & TABLES */
        .stat-card {
            border: none;
            border-radius: 1rem;
            background: #fff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
            height: 100%;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--soft-shadow);
        }

        .border-accent-start {
            border-left-width: 4px !important;
        }

        .stat-value {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-dark);
        }

        .stat-icon-bg {
            width: 55px;
            height: 55px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-size: 1.5rem;
        }

        .table-container {
            background: #fff;
            border-radius: 1rem;
            box-shadow: var(--soft-shadow);
            padding: 1.5rem;
        }

        .badge-status {
            padding: 0.4em 0.8em;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 50rem;
        }

        /* RESPONSIVE */
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
                <li>
                    <a href="/bdl_service/views/dashboard.php" class="active">
                        <i class="fas fa-th-large"></i> Dashboard
                    </a>
                </li>

                <li>
                    <a href="/bdl_service/index.php">
                        <i class="fas fa-home"></i> Halaman Depan
                    </a>
                </li>

                <li class="my-1 border-top border-secondary opacity-25"></li>

                <li>
                    <a href="/bdl_service/views/customers/list.php">
                        <i class="fas fa-users"></i> Pelanggan
                    </a>
                </li>
                <li>
                    <a href="/bdl_service/views/technicians/list.php">
                        <i class="fas fa-user-cog"></i> Teknisi
                    </a>
                </li>
                <li>
                    <a href="/bdl_service/views/spareparts/list.php">
                        <i class="fas fa-boxes-stacked"></i> Spare Part
                    </a>
                </li>
                <li>
                    <a href="/bdl_service/views/services/list.php">
                        <i class="fas fa-clipboard-list"></i> Data Service
                    </a>
                </li>
                <li>
                    <a href="/bdl_service/views/reports/index.php">
                        <i class="fas fa-chart-line"></i> Laporan
                    </a>
                </li>

                <li style="margin-top: auto; padding-top: 2rem;">
                    <a href="/bdl_service/auth/logout.php" style="color: #ffb3b3;">
                        <i
                            class="fas fa-power-off animate__animated animate__pulse animate__infinite animate__slower"></i>
                        Logout
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
                        <span class="ms-2 fw-semibold d-none d-md-inline" style="color: var(--accent-blue);">Menu</span>
                    </button>

                    <div class="ms-auto d-flex align-items-center">
                        <div class="text-end me-3 d-none d-sm-block">
                            <span class="d-block small text-muted">Login sebagai:</span>
                            <h6 class="m-0 fw-bold" style="color: var(--accent-blue);">
                                <?= htmlspecialchars($_SESSION['nama_user'] ?? 'Administrator') ?>
                            </h6>
                        </div>
                        <div class="rounded-circle shadow-sm d-flex align-items-center justify-content-center"
                            style="width: 42px; height: 42px; background: linear-gradient(45deg, var(--accent-blue), #a2c2ff); color: white;">
                            <i class="fas fa-user-tie fs-5"></i>
                        </div>
                    </div>
                </div>
            </nav>

            <div
                class="d-flex justify-content-between align-items-center mb-4 animate__animated animate__fadeIn animate__delay-1s">
                <h4 class="m-0 fw-bold text-dark">Ringkasan Dashboard</h4>
                <span class="text-muted small"><i class="far fa-calendar-alt me-1"></i> <?= date('d F Y') ?></span>
            </div>

            <div class="row g-3 g-xl-4 mb-5 animate__animated animate__fadeInUp">
                <div class="col-md-6 col-xl-3">
                    <div class="stat-card p-3 d-flex align-items-center border-accent-start border-warning">
                        <div class="flex-grow-1">
                            <div class="stat-label">Menunggu Diproses</div>
                            <div class="stat-value"><?= number_format($pending_count) ?></div>
                        </div>
                        <div class="stat-icon-bg" style="color: #ffc107; background: rgba(255, 193, 7, 0.15);">
                            <i class="fas fa-hourglass-half"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="stat-card p-3 d-flex align-items-center border-accent-start border-success">
                        <div class="flex-grow-1">
                            <div class="stat-label">Service Selesai</div>
                            <div class="stat-value"><?= number_format($completed_count) ?></div>
                        </div>
                        <div class="stat-icon-bg" style="color: #198754; background: rgba(25, 135, 84, 0.15);">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="stat-card p-3 d-flex align-items-center border-accent-start border-info">
                        <div class="flex-grow-1">
                            <div class="stat-label">Total Pelanggan</div>
                            <div class="stat-value"><?= number_format($customer_count) ?></div>
                        </div>
                        <div class="stat-icon-bg" style="color: #0dcaf0; background: rgba(13, 202, 240, 0.15);">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="stat-card p-3 d-flex align-items-center border-accent-start"
                        style="border-color: var(--accent-blue) !important;">
                        <div class="flex-grow-1">
                            <div class="stat-label">Teknisi Aktif</div>
                            <div class="stat-value"><?= number_format($technician_count) ?></div>
                        </div>
                        <div class="stat-icon-bg"
                            style="color: var(--accent-blue); background: rgba(93, 135, 255, 0.15);">
                            <i class="fas fa-user-shield"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-container animate__animated animate__fadeInUp animate__delay-1s">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="m-0 fw-bold" style="color: var(--sidebar-bg-start);">
                        <i class="fas fa-history me-2" style="color: var(--accent-blue);"></i>5 Transaksi Terakhir
                    </h5>
                    <a href="/bdl_service/views/services/list.php" class="btn btn-sm rounded-pill px-3 fw-semibold"
                        style="background-color: #eef2ff; color: var(--accent-blue);">
                        Lihat Semua <i class="fas fa-arrow-right ms-1 small"></i>
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-striped border-light align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3">ID Service</th>
                                <th>Pelanggan</th>
                                <th>Perangkat</th>
                                <th>Keluhan</th>
                                <th>Status Terbaru</th>
                                <th>Tanggal Masuk</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($recent_services) > 0): ?>
                                <?php foreach ($recent_services as $service): ?>
                                    <tr>
                                        <td class="ps-3 fw-bold" style="color: var(--accent-blue);">
                                            #<?= $service['id_service'] ?></td>
                                        <td class="fw-semibold"><?= htmlspecialchars($service['nama_pelanggan']) ?></td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span
                                                    class="fw-medium"><?= htmlspecialchars($service['nama_perangkat']) ?></span>
                                                <small class="text-muted"
                                                    style="font-size: 0.8rem;"><?= htmlspecialchars($service['merek']) ?></small>
                                            </div>
                                        </td>
                                        <td class="text-muted"
                                            style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            <?= htmlspecialchars($service['keluhan']) ?>
                                        </td>
                                        <td>
                                            <?php
                                            $st = strtolower($service['nama_status']);
                                            $bgClass = 'bg-warning text-dark bg-opacity-75';
                                            $icon = 'fa-clock';

                                            if (strpos($st, 'selesai') !== false || strpos($st, 'ambil') !== false || strpos($st, 'siap') !== false) {
                                                $bgClass = 'bg-success bg-opacity-75 text-white';
                                                $icon = 'fa-check';
                                            } elseif (strpos($st, 'proses') !== false || strpos($st, 'diagnosa') !== false || strpos($st, 'uji') !== false) {
                                                $bgClass = 'bg-info bg-opacity-75 text-dark';
                                                $icon = 'fa-cog fa-spin small';
                                            } elseif (strpos($st, 'batal') !== false) {
                                                $bgClass = 'bg-danger bg-opacity-75 text-white';
                                                $icon = 'fa-times';
                                            }
                                            ?>
                                            <span class="badge badge-status <?= $bgClass ?>">
                                                <i class="fas <?= $icon ?> me-1 opacity-75"></i>
                                                <?= htmlspecialchars($service['nama_status']) ?>
                                            </span>
                                        </td>
                                        <td class="text-muted small fw-medium">
                                            <?= date('d M Y, H:i', strtotime($service['tanggal_masuk'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted fw-medium">
                                        <i class="fas fa-inbox fa-3x mb-3 opacity-25"></i>
                                        <br>Belum ada data service terbaru.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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