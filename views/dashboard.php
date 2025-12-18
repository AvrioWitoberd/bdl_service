<?php
// views/dashboard.php

// --- 1. SETUP & LOGIKA PHP ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo = require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: /bdl_service/index.php"); 
    exit;
}

// ... (QUERY STATISTIK SAMA SEPERTI SEBELUMNYA, TIDAK BERUBAH) ...
// Service Pending
$stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM service WHERE id_status NOT IN (SELECT id_status FROM status_perbaikan WHERE nama_status IN ('Selesai Diperbaiki', 'Siap Diambil', 'Diambil Pelanggan'))");
$stmt->execute();
$pending_count = $stmt->fetch()['total'] ?? 0;

// Service Selesai
$stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM service WHERE id_status IN (SELECT id_status FROM status_perbaikan WHERE nama_status IN ('Selesai Diperbaiki', 'Siap Diambil', 'Diambil Pelanggan'))");
$stmt->execute();
$completed_count = $stmt->fetch()['total'] ?? 0;

// Total Pelanggan
$stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM pelanggan");
$stmt->execute();
$customer_count = $stmt->fetch()['total'] ?? 0;

// Teknisi Aktif
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

// --- 2. LOAD HEADER ---
include __DIR__ . '/partials/header.php'; 
?>

<style>
    .stat-card {
        border: none; border-radius: 1rem; background: #fff;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03); transition: all 0.3s; height: 100%;
    }
    .stat-card:hover { transform: translateY(-5px); box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.08); }
    .border-accent-start { border-left-width: 4px !important; }
    .stat-value { font-size: 1.75rem; font-weight: 700; color: #343a40; }
    .stat-icon-bg {
        width: 55px; height: 55px; display: flex; align-items: center; justify-content: center;
        border-radius: 50%; font-size: 1.5rem;
    }
    .badge-status { padding: 0.4em 0.8em; font-size: 0.75rem; font-weight: 600; border-radius: 50rem; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4 animate__animated animate__fadeIn">
    <div>
        <h4 class="m-0 fw-bold text-dark">Ringkasan Dashboard</h4>
        <p class="text-muted small m-0">Selamat datang kembali, Admin!</p>
    </div>
    <span class="badge bg-light text-secondary border px-3 py-2">
        <i class="far fa-calendar-alt me-1"></i> <?= date('d F Y') ?>
    </span>
</div>

<div class="row g-3 g-xl-4 mb-5 animate__animated animate__fadeInUp">
    <div class="col-md-6 col-xl-3">
        <div class="stat-card p-3 d-flex align-items-center border-accent-start border-warning">
            <div class="flex-grow-1">
                <div class="text-muted small fw-bold text-uppercase">Pending</div>
                <div class="stat-value"><?= number_format($pending_count) ?></div>
            </div>
            <div class="stat-icon-bg text-warning bg-warning bg-opacity-10">
                <i class="fas fa-hourglass-half"></i>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-xl-3">
        <div class="stat-card p-3 d-flex align-items-center border-accent-start border-success">
            <div class="flex-grow-1">
                <div class="text-muted small fw-bold text-uppercase">Selesai</div>
                <div class="stat-value"><?= number_format($completed_count) ?></div>
            </div>
            <div class="stat-icon-bg text-success bg-success bg-opacity-10">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-xl-3">
        <div class="stat-card p-3 d-flex align-items-center border-accent-start border-info">
            <div class="flex-grow-1">
                <div class="text-muted small fw-bold text-uppercase">Pelanggan</div>
                <div class="stat-value"><?= number_format($customer_count) ?></div>
            </div>
            <div class="stat-icon-bg text-info bg-info bg-opacity-10">
                <i class="fas fa-users"></i>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-xl-3">
        <div class="stat-card p-3 d-flex align-items-center border-accent-start border-primary">
            <div class="flex-grow-1">
                <div class="text-muted small fw-bold text-uppercase">Teknisi</div>
                <div class="stat-value"><?= number_format($technician_count) ?></div>
            </div>
            <div class="stat-icon-bg text-primary bg-primary bg-opacity-10">
                <i class="fas fa-user-shield"></i>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm animate__animated animate__fadeInUp animate__delay-1s" style="border-radius: 1rem;">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="m-0 fw-bold">
                <i class="fas fa-history me-2 text-primary"></i>5 Transaksi Terakhir
            </h5>
            <a href="/bdl_service/views/services/list.php" class="btn btn-sm btn-light text-primary fw-bold px-3 rounded-pill">
                Lihat Semua <i class="fas fa-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-3 py-3">ID</th>
                        <th>Pelanggan</th>
                        <th>Perangkat</th>
                        <th>Keluhan</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($recent_services) > 0): ?>
                        <?php foreach ($recent_services as $service): ?>
                            <tr>
                                <td class="ps-3 fw-bold text-primary">#<?= $service['id_service'] ?></td>
                                <td class="fw-semibold"><?= htmlspecialchars($service['nama_pelanggan']) ?></td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-medium small"><?= htmlspecialchars($service['nama_perangkat']) ?></span>
                                        <small class="text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars($service['merek']) ?></small>
                                    </div>
                                </td>
                                <td class="text-muted small" style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
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
                                    } elseif (strpos($st, 'proses') !== false || strpos($st, 'diagnosa') !== false) {
                                        $bgClass = 'bg-info bg-opacity-75 text-dark';
                                        $icon = 'fa-cog fa-spin small';
                                    } elseif (strpos($st, 'batal') !== false) {
                                        $bgClass = 'bg-danger bg-opacity-75 text-white';
                                        $icon = 'fa-times';
                                    }
                                    ?>
                                    <span class="badge badge-status <?= $bgClass ?>">
                                        <i class="fas <?= $icon ?> me-1 opacity-75"></i> <?= htmlspecialchars($service['nama_status']) ?>
                                    </span>
                                </td>
                                <td class="text-muted small"><?= date('d M Y', strtotime($service['tanggal_masuk'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fa-3x mb-3 opacity-25"></i><br>Belum ada data service.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>