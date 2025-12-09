<?php
// views/dashboard.php
require_once '../config/database.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$pdo = require_once '../config/database.php';

// Ambil statistik
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM service WHERE id_status != (SELECT id_status FROM status_perbaikan WHERE nama_status = 'Selesai')");
$stmt->execute();
$pending_count = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM service WHERE id_status = (SELECT id_status FROM status_perbaikan WHERE nama_status = 'Selesai')");
$stmt->execute();
$completed_count = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM pelanggan");
$stmt->execute();
$customer_count = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM teknisi WHERE status_aktif = TRUE");
$stmt->execute();
$technician_count = $stmt->fetch()['total'];

// Ambil 5 service terbaru
$stmt = $pdo->prepare("
    SELECT s.id_service, s.keluhan, s.tanggal_masuk, sp.nama_status, p.nama as nama_pelanggan, d.jenis_perangkat, d.merek, d.model
    FROM service s
    JOIN perangkat d ON s.id_perangkat = d.id_perangkat
    JOIN pelanggan p ON d.id_pelanggan = p.id_pelanggan
    JOIN status_perbaikan sp ON s.id_status = sp.id_status
    ORDER BY s.tanggal_masuk DESC
    LIMIT 5
");
$stmt->execute();
$recent_services = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - Service Elektronik ABC</title>
    <!-- 🔁 Ganti path CSS jadi sesuai struktur lo -->
    <link rel="stylesheet" href="../../public/css/style.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f9f9f9; }
        .container { max-width: 1200px; margin: 0 auto; padding: 2rem; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
        .stat-box { background-color: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); text-align: center; }
        .stat-box h3 { margin: 0; color: #007bff; }
        .stat-box p { font-size: 2rem; margin: 0.5rem 0; }
        .recent-services { background-color: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .recent-services h3 { color: #007bff; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { padding: 0.75rem; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <!-- 🔁 Ganti path include header jadi sesuai struktur lo -->
    <?php include '../views/partials/header.php'; ?>
    <div class="container">
        <h1>Dashboard Admin</h1>

        <div class="stats">
            <div class="stat-box">
                <h3>Service Pending</h3>
                <p><?= $pending_count ?></p>
            </div>
            <div class="stat-box">
                <h3>Service Selesai</h3>
                <p><?= $completed_count ?></p>
            </div>
            <div class="stat-box">
                <h3>Total Pelanggan</h3>
                <p><?= $customer_count ?></p>
            </div>
            <div class="stat-box">
                <h3>Teknisi Aktif</h3>
                <p><?= $technician_count ?></p>
            </div>
        </div>

        <div class="recent-services">
            <h3>5 Service Terbaru</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Pelanggan</th>
                        <th>Perangkat</th>
                        <th>Keluhan</th>
                        <th>Status</th>
                        <th>Tanggal Masuk</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_services as $service): ?>
                    <tr>
                        <td><?= htmlspecialchars($service['id_service']) ?></td>
                        <td><?= htmlspecialchars($service['nama_pelanggan']) ?></td>
                        <td><?= htmlspecialchars($service['jenis_perangkat']) ?> <?= htmlspecialchars($service['merek']) ?> <?= htmlspecialchars($service['model']) ?></td>
                        <td><?= htmlspecialchars($service['keluhan']) ?></td>
                        <td><?= htmlspecialchars($service['nama_status']) ?></td>
                        <td><?= htmlspecialchars($service['tanggal_masuk']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>