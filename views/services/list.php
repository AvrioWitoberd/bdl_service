<?php
// views/services/list.php
require_once '../../config/database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$pdo = require_once '../../config/database.php';
$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

$services = [];

if ($role === 'pelanggan') {
    // Ambil service berdasarkan perangkat pelanggan
    $stmt = $pdo->prepare("
        SELECT s.id_service, s.keluhan, s.tanggal_masuk, s.tanggal_selesai, s.biaya_akhir, sp.nama_status, d.jenis_perangkat, d.merek, d.model
        FROM service s
        JOIN perangkat d ON s.id_perangkat = d.id_perangkat
        JOIN status_perbaikan sp ON s.id_status = sp.id_status
        WHERE d.id_pelanggan = ?
        ORDER BY s.tanggal_masuk DESC
    ");
    $stmt->execute([$user_id]);
    $services = $stmt->fetchAll();
} elseif ($role === 'teknisi') {
    // Ambil service yang ditugaskan ke teknisi ini
    $stmt = $pdo->prepare("
        SELECT s.id_service, s.keluhan, s.tanggal_masuk, s.tanggal_selesai, s.biaya_akhir, sp.nama_status, p.nama as nama_pelanggan, d.jenis_perangkat, d.merek, d.model
        FROM service s
        JOIN perangkat d ON s.id_perangkat = d.id_perangkat
        JOIN pelanggan p ON d.id_pelanggan = p.id_pelanggan
        JOIN status_perbaikan sp ON s.id_status = sp.id_status
        WHERE s.id_teknisi = ?
        ORDER BY s.tanggal_masuk DESC
    ");
    $stmt->execute([$user_id]);
    $services = $stmt->fetchAll();
} else {
    // Jika admin, redirect ke halaman khusus atau tampilkan error
    header("Location: ../dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Service Saya</title>
    <!-- 🔁 Ganti path CSS jadi sesuai struktur lo -->
    <link rel="stylesheet" href="../../public/css/style.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f9f9f9; }
        .container { max-width: 1200px; margin: 0 auto; padding: 2rem; }
        table { width: 100%; border-collapse: collapse; background-color: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        th, td { padding: 0.75rem; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
        .status-active { color: #28a745; font-weight: bold; }
        .status-pending { color: #ffc107; font-weight: bold; }
        .status-completed { color: #17a2b8; font-weight: bold; }
    </style>
</head>
<body>
    <!-- 🔁 Ganti path include header jadi sesuai struktur lo -->
    <?php include '../views/partials/header.php'; ?>
    <div class="container">
        <h1>Daftar Service Saya</h1>

        <?php if (empty($services)): ?>
            <p>Belum ada service yang terdaftar.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID Service</th>
                        <th>Perangkat</th>
                        <th>Keluhan</th>
                        <th>Status</th>
                        <th>Tanggal Masuk</th>
                        <th>Tanggal Selesai</th>
                        <th>Biaya Akhir</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($services as $service): ?>
                    <tr>
                        <td><?= htmlspecialchars($service['id_service']) ?></td>
                        <td><?= htmlspecialchars($service['jenis_perangkat']) ?> <?= htmlspecialchars($service['merek']) ?> <?= htmlspecialchars($service['model']) ?></td>
                        <td><?= htmlspecialchars($service['keluhan']) ?></td>
                        <td class="
                            <?php 
                                $status_class = '';
                                $status_nama = strtolower($service['nama_status']);
                                if (strpos($status_nama, 'selesai') !== false || strpos($status_nama, 'completed') !== false) {
                                    $status_class = 'status-completed';
                                } elseif (strpos($status_nama, 'pending') !== false || strpos($status_nama, 'menunggu') !== false) {
                                    $status_class = 'status-pending';
                                } else {
                                    $status_class = 'status-active';
                                }
                                echo $status_class;
                            ?>
                        "><?= htmlspecialchars($service['nama_status']) ?></td>
                        <td><?= htmlspecialchars($service['tanggal_masuk']) ?></td>
                        <td><?= htmlspecialchars($service['tanggal_selesai'] ?? 'Belum selesai') ?></td>
                        <td>Rp <?= number_format($service['biaya_akhir'] ?? 0, 0, ',', '.') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>