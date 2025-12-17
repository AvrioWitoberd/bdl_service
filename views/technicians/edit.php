<?php
// views/technicians/edit.php

// === LOGIKA PHP ASLI (TIDAK DIUBAH) ===
$pdo = require_once '../../config/database.php';
require_once '../../models/Teknisi.php';

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$teknisiModel = new Teknisi($pdo);
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$teknisi = $teknisiModel->getById($id);

if (!$teknisi) {
    die("Technician not found.");
}

$message = isset($_GET['msg']) ? $_GET['msg'] : '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Teknisi - Service ABC</title>

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

        /* EDIT SPECIFIC STYLES */
        .card-custom { border: none; border-radius: 1rem; box-shadow: var(--soft-shadow); background: #fff; overflow: hidden; }
        .card-header-accent { background: linear-gradient(90deg, var(--accent-blue), #86b7fe); height: 6px; width: 100%; }
        
        .form-label { font-weight: 600; font-size: 0.85rem; color: #555; margin-bottom: 0.5rem; }
        .form-control { padding: 0.7rem 1rem; border-color: #dfe5ef; background-color: #fcfdfe; }
        .form-control:focus { border-color: var(--accent-blue); box-shadow: 0 0 0 3px rgba(93, 135, 255, 0.1); background-color: #fff; }
        
        /* Status Toggle Style */
        .status-card { background: #f8f9fa; border-radius: 0.75rem; padding: 1rem; border: 1px solid #eee; display: flex; align-items: center; justify-content: space-between; }
        .form-check-input:checked { background-color: #198754; border-color: #198754; }

        .btn-update { background-color: var(--accent-blue); color: white; border: none; padding: 10px 30px; border-radius: 8px; font-weight: 600; transition: all 0.2s; }
        .btn-update:hover { background-color: #4a72e8; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(93, 135, 255, 0.3); }

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
                
                <li><a href="../customers/list.php"><i class="fas fa-users"></i> Pelanggan</a></li>
                <li><a href="list.php" class="active"><i class="fas fa-user-cog"></i> Teknisi</a></li>
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
                    <button type="button" id="sidebarCollapse" class="btn btn-light text-primary shadow-sm border-0" style="background: #eef2ff;">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="ms-auto d-flex align-items-center">
                        <span class="d-none d-sm-block small text-muted me-2">Login sebagai:</span>
                        <h6 class="m-0 fw-bold me-3" style="color: var(--accent-blue);"><?= htmlspecialchars($_SESSION['nama_user'] ?? 'Admin') ?></h6>
                        <div class="rounded-circle shadow-sm d-flex align-items-center justify-content-center"
                            style="width: 40px; height: 40px; background: linear-gradient(45deg, var(--accent-blue), #a2c2ff); color: white;">
                            <i class="fas fa-user-shield"></i>
                        </div>
                    </div>
                </div>
            </nav>

            <div class="d-flex justify-content-between align-items-center mb-4 animate__animated animate__fadeIn">
                <div>
                    <h4 class="m-0 fw-bold text-dark">Edit Data Teknisi</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 small text-muted">
                            <li class="breadcrumb-item"><a href="list.php" class="text-decoration-none">Teknisi</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Edit #<?= $teknisi['id_teknisi'] ?></li>
                        </ol>
                    </nav>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-warning alert-dismissible fade show shadow-sm border-0 animate__animated animate__shakeX" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i> <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row justify-content-center">
                <div class="col-lg-8 animate__animated animate__fadeInUp">
                    <div class="card-custom">
                        <div class="card-header-accent"></div>
                        <div class="card-body p-4">
                            
                            <form method="POST" action="../../controllers/TechnicianController.php">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="id_teknisi" value="<?php echo $teknisi['id_teknisi']; ?>">

                                <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                                    <h5 class="fw-bold m-0"><i class="fas fa-user-edit me-2 text-primary"></i>Formulir Perubahan</h5>
                                    
                                    <?php if ($teknisi['status_aktif']): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill">
                                                <i class="fas fa-check-circle me-1"></i> Saat ini Aktif
                                            </span>
                                    <?php else: ?>
                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-2 rounded-pill">
                                                <i class="fas fa-times-circle me-1"></i> Saat ini Non-Aktif
                                            </span>
                                    <?php endif; ?>
                                </div>

                                <div class="mb-3">
                                    <label for="nama_teknisi" class="form-label">Nama Lengkap Teknisi</label>
                                    <input type="text" class="form-control" id="nama_teknisi" name="nama_teknisi" 
                                           value="<?php echo htmlspecialchars($teknisi['nama_teknisi']); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="keahlian" class="form-label">Keahlian (Spesialisasi)</label>
                                    <textarea class="form-control" id="keahlian" name="keahlian" rows="3"><?php echo htmlspecialchars($teknisi['keahlian']); ?></textarea>
                                    <div class="form-text small text-muted">Deskripsikan perangkat elektronik yang dapat ditangani.</div>
                                </div>

                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label for="no_hp" class="form-label">Nomor HP / WA</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-phone small"></i></span>
                                            <input type="text" class="form-control border-start-0 ps-0" id="no_hp" name="no_hp" 
                                                   value="<?php echo htmlspecialchars($teknisi['no_hp']); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="email" class="form-label">Email (Akun Login)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-envelope small"></i></span>
                                            <input type="email" class="form-control border-start-0 ps-0" id="email" name="email" 
                                                   value="<?php echo htmlspecialchars($teknisi['email']); ?>" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="status-card mb-4">
                                    <div>
                                        <span class="d-block fw-bold small text-uppercase text-secondary">Status Akun</span>
                                        <span class="small text-muted">Aktifkan agar teknisi dapat menerima order.</span>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" style="width: 3em; height: 1.5em; cursor: pointer;" 
                                               type="checkbox" id="status_aktif" name="status_aktif" value="1" 
                                               <?php if ($teknisi['status_aktif'])
                                                   echo 'checked'; ?>>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-4">
                                    <a href="list.php" class="btn btn-light text-muted fw-bold">
                                        <i class="fas fa-arrow-left me-1"></i> Kembali
                                    </a>
                                    <button type="submit" class="btn btn-update shadow-sm">
                                        <i class="fas fa-save me-2"></i> Update Data Teknisi
                                    </button>
                                </div>

                            </form>
                        </div>
                    </div>
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