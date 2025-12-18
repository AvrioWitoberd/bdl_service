<?php
// views/partials/header.php

// 1. Cek Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. CONFIG BASE URL (Sesuaikan dengan nama folder project Anda)
$base_url = '/bdl_service'; 

// 3. Ambil Data User
$namaUser = $_SESSION['username'] ?? '';
$roleUser = $_SESSION['role'] ?? 'guest';

// 4. Helper Menu Aktif
$uri = $_SERVER['REQUEST_URI'];
function isActive($path) {
    global $uri;
    // Cek apakah URL mengandung kata kunci tertentu
    return strpos($uri, $path) !== false ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Center App</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --sidebar-width: 250px;
            --sidebar-bg: #2c3e50;
            --sidebar-text: #b0b8c1;
            --sidebar-active: #5D87FF;
            --body-bg: #f5f7fa;
            --topbar-height: 70px;
        }

        body { font-family: 'Inter', sans-serif; background-color: var(--body-bg); overflow-x: hidden; }
        
        /* Layout Wrapper */
        #wrapper { display: flex; width: 100%; min-height: 100vh; transition: all 0.3s ease; }
        
        /* Sidebar Styling */
        #sidebar { min-width: var(--sidebar-width); max-width: var(--sidebar-width); background-color: var(--sidebar-bg); color: #fff; transition: all 0.3s ease; z-index: 1000; }
        #sidebar .sidebar-brand { height: var(--topbar-height); display: flex; align-items: center; padding: 0 1.5rem; font-weight: 700; font-size: 1.25rem; color: #fff; background: rgba(0,0,0,0.1); border-bottom: 1px solid rgba(255,255,255,0.05); }
        #sidebar ul.components { padding: 1rem 0; list-style: none; padding-left: 0; }
        #sidebar ul li a { padding: 12px 25px; display: flex; align-items: center; font-size: 0.95rem; color: var(--sidebar-text); text-decoration: none; transition: 0.2s; border-left: 4px solid transparent; }
        #sidebar ul li a:hover { color: #fff; background: rgba(255,255,255,0.05); }
        #sidebar ul li a.active { background: rgba(0, 0, 0, 0.2); color: #fff; border-left-color: var(--sidebar-active); }
        #sidebar ul li a i { margin-right: 15px; width: 20px; text-align: center; }
        
        /* Content Styling */
        #content { flex: 1; display: flex; flex-direction: column; width: 100%; transition: all 0.3s ease; }
        .top-navbar { background: #fff; height: var(--topbar-height); display: flex; align-items: center; padding: 0 2rem; box-shadow: 0 2px 15px rgba(0,0,0,0.04); margin-bottom: 2rem; }
        
        /* Toggled State */
        #wrapper.toggled #sidebar { margin-left: calc(-1 * var(--sidebar-width)); }
        
        @media (max-width: 768px) {
            #wrapper #sidebar { margin-left: calc(-1 * var(--sidebar-width)); position: fixed; height: 100%; }
            #wrapper.toggled #sidebar { margin-left: 0; }
        }
    </style>
</head>
<body>

<div id="wrapper">
    <nav id="sidebar">
        <div class="sidebar-brand">
            <i class="fas fa-robot me-2"></i> SERVICE GACOR
        </div>

        <ul class="components">
            <?php if ($roleUser === 'admin'): ?>
                <li>
                    <a href="<?= $base_url ?>/views/dashboard.php" class="<?= isActive('dashboard.php') ?>">
                        <i class="fas fa-th-large"></i> Dashboard
                    </a>
                </li>
                <li class="my-2 border-top border-secondary opacity-25"></li>
            <?php endif; ?>

            <?php if ($roleUser === 'admin'): ?>
                <div class="small text-uppercase px-4 py-2 opacity-50" style="font-size: 0.7rem;">Data Master</div>
                <li><a href="<?= $base_url ?>/views/customers/list.php" class="<?= isActive('customers') ?>"><i class="fas fa-users"></i> Pelanggan</a></li>
                <li><a href="<?= $base_url ?>/views/technicians/list.php" class="<?= isActive('technicians') ?>"><i class="fas fa-user-cog"></i> Teknisi</a></li>
                <li><a href="<?= $base_url ?>/views/spareparts/list.php" class="<?= isActive('spareparts') ?>"><i class="fas fa-boxes-stacked"></i> Spare Part</a></li>
                <li class="my-2 border-top border-secondary opacity-25"></li>
            <?php endif; ?>

            <div class="small text-uppercase px-4 py-2 opacity-50" style="font-size: 0.7rem;">
                <?= ($roleUser === 'pelanggan') ? 'Menu Utama' : 'Transaksi' ?>
            </div>
            <li>
                <a href="<?= $base_url ?>/views/services/list.php" class="<?= isActive('services') ?>">
                    <i class="fas fa-clipboard-list"></i> 
                    <?= ($roleUser === 'pelanggan') ? 'Status Service Saya' : 'Data Service' ?>
                </a>
            </li>

            <?php if ($roleUser === 'admin'): ?>
                <li class="my-2 border-top border-secondary opacity-25"></li>
                <div class="small text-uppercase px-4 py-2 opacity-50" style="font-size: 0.7rem;">Analisa</div>
                <li>
                    <a href="<?= $base_url ?>/controllers/ReportController.php" class="<?= isActive('ReportController') ?>">
                        <i class="fas fa-chart-pie"></i> Laporan
                    </a>
                </li>
            <?php endif; ?>

            <li class="my-2 border-top border-secondary opacity-25"></li>

            <li><a href="<?= $base_url ?>/index.php" class="<?= isActive('index.php') ?>"><i class="fas fa-home"></i> Halaman Depan</a></li>

            <li class="mt-4 pt-2">
                <a href="<?= $base_url ?>/controllers/AuthController.php?action=logout" class="text-danger">
                    <i class="fas fa-power-off"></i> Logout
                </a>
            </li>
        </ul>
    </nav>

    <div id="content">
        <nav class="top-navbar">
            <button type="button" id="sidebarCollapse" class="btn btn-light shadow-sm text-primary rounded-3">
                <i class="fas fa-bars"></i>
            </button>

            <div class="ms-auto d-flex align-items-center">
                <div class="text-end me-3 d-none d-sm-block">
                    <span class="d-block text-muted small">User</span>
                    <span class="fw-bold text-dark">
                        <?= htmlspecialchars($namaUser) ?> 
                        <span class="badge bg-secondary ms-1" style="font-size: 0.6rem;"><?= ucfirst($roleUser) ?></span>
                    </span>
                </div>
                
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-decoration-none" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; cursor: pointer;">
                            <i class="fas fa-user"></i>
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownUser1">
                        <li><span class="dropdown-header text-muted">Halo, <?= htmlspecialchars($namaUser) ?></span></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="<?= $base_url ?>/controllers/AuthController.php?action=logout">
                                <i class="fas fa-sign-out-alt me-2"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="container-fluid px-4">