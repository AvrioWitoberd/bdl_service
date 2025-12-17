<?php
// views/customers/create.php

// === PERBAIKAN PATH & DOBEL INCLUDE (LOGIKA ASLI DIPERTAHANKAN) ===
if (!isset($pdo)) {
    $pdo = require_once __DIR__ . '/../../config/database.php';
}
require_once __DIR__ . '/../../models/Pelanggan.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$pelangganModel = new Pelanggan($pdo);

// === AMBIL DATA DARI SESSION ===
$message = $_SESSION['error_message'] ?? '';
$old = $_SESSION['old_form'] ?? [];

// Bersihkan session
if (isset($_SESSION['old_form']))
    unset($_SESSION['old_form']);
if (isset($_SESSION['error_message']))
    unset($_SESSION['error_message']);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Pelanggan - Service ABC</title>

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

        /* FORM STYLES */
        .card-custom {
            border: none;
            border-radius: 1rem;
            box-shadow: var(--soft-shadow);
            background: #fff;
            overflow: hidden;
        }

        .form-section-title {
            font-size: 0.9rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #adb5bd;
            margin-bottom: 1rem;
            border-bottom: 1px solid #f1f1f1;
            padding-bottom: 0.5rem;
        }

        .input-group-text {
            background-color: #f8f9fa;
            border-right: none;
            color: #6c757d;
        }

        .form-control {
            border-left: none;
            background-color: #f8f9fa;
        }

        .form-control:focus {
            background-color: #fff;
            box-shadow: none;
            border-color: #dee2e6;
        }

        .input-group:focus-within {
            box-shadow: 0 0 0 0.25rem rgba(93, 135, 255, 0.25);
            border-radius: 0.375rem;
        }

        .input-group:focus-within .input-group-text,
        .input-group:focus-within .form-control {
            border-color: #86b7fe;
            background-color: #fff;
        }

        .btn-primary-custom {
            background-color: var(--accent-blue);
            border-color: var(--accent-blue);
            color: white;
            padding: 10px 25px;
            font-weight: 600;
        }

        .btn-primary-custom:hover {
            background-color: #4a72e8;
            border-color: #4a72e8;
            box-shadow: 0 4px 10px rgba(93, 135, 255, 0.3);
        }

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
                            <?= htmlspecialchars($_SESSION['nama_user'] ?? 'Admin') ?></h6>
                        <div class="rounded-circle shadow-sm d-flex align-items-center justify-content-center"
                            style="width: 40px; height: 40px; background: linear-gradient(45deg, var(--accent-blue), #a2c2ff); color: white;">
                            <i class="fas fa-user-shield"></i>
                        </div>
                    </div>
                </div>
            </nav>

            <div class="d-flex justify-content-between align-items-center mb-4 animate__animated animate__fadeIn">
                <div>
                    <h4 class="m-0 fw-bold text-dark">Tambah Pelanggan Baru</h4>
                    <span class="text-muted small">Silakan isi formulir di bawah ini untuk mendaftarkan
                        pelanggan.</span>
                </div>
                <a href="list.php" class="btn btn-outline-secondary btn-sm shadow-sm">
                    <i class="fas fa-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 animate__animated animate__shakeX"
                    role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i> <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card-custom animate__animated animate__fadeInUp">
                <div class="card-body p-4">
                    <form method="POST" action="../../controllers/CustomerController.php" class="needs-validation">
                        <input type="hidden" name="action" value="create">

                        <div class="row g-4">
                            <div class="col-lg-6">
                                <div class="form-section-title"><i class="fas fa-id-card me-2"></i>Informasi Pribadi
                                </div>

                                <div class="mb-3">
                                    <label for="nama" class="form-label fw-bold small">Nama Lengkap</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                        <input type="text" class="form-control" id="nama" name="nama"
                                            value="<?php echo htmlspecialchars($old['nama'] ?? ''); ?>"
                                            placeholder="Contoh: Budi Santoso" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="no_hp" class="form-label fw-bold small">Nomor HP</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                        <input type="text" class="form-control" id="no_hp" name="no_hp"
                                            value="<?php echo htmlspecialchars($old['no_hp'] ?? ''); ?>"
                                            placeholder="08xxxxxxxxxx" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="alamat" class="form-label fw-bold small">Alamat Lengkap</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                        <textarea class="form-control" id="alamat" name="alamat" rows="4"
                                            placeholder="Jalan, RT/RW, Kelurahan..."
                                            required><?php echo htmlspecialchars($old['alamat'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-section-title"><i class="fas fa-lock me-2"></i>Informasi Akun</div>

                                <div class="mb-3">
                                    <label for="email" class="form-label fw-bold small">Alamat Email</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                        <input type="email" class="form-control" id="email" name="email"
                                            value="<?php echo htmlspecialchars($old['email'] ?? ''); ?>"
                                            placeholder="nama@email.com" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="password" class="form-label fw-bold small">Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-key"></i></span>
                                            <input type="password" class="form-control" id="password" name="password"
                                                required>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="confirm_password" class="form-label fw-bold small">Konfirmasi
                                            Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-check-circle"></i></span>
                                            <input type="password" class="form-control" id="confirm_password"
                                                name="confirm_password" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="alert alert-light border border-light-subtle text-muted small mt-2">
                                    <i class="fas fa-info-circle me-1"></i> Pastikan email aktif untuk keperluan
                                    notifikasi service.
                                </div>
                            </div>
                        </div>

                        <hr class="my-4 opacity-10">

                        <div class="d-flex justify-content-end gap-2">
                            <a href="list.php" class="btn btn-light border">Batal</a>
                            <button type="submit" class="btn btn-primary-custom rounded-pill">
                                <i class="fas fa-save me-2"></i> Simpan Data Pelanggan
                            </button>
                        </div>

                    </form>
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