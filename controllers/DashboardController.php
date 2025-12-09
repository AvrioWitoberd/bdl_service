<?php
// controllers/DashboardController.php
require_once '../config/database.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../views/auth/login.php");
    exit;
}

$pdo = require_once '../config/database.php';

// Contoh statistik
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM service WHERE id_status != (SELECT id_status FROM status_perbaikan WHERE nama_status = 'completed')");
$stmt->execute();
$pending_count = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT SUM(total_bayar) as revenue FROM pembayaran WHERE status_bayar = 'paid' AND tanggal_bayar >= NOW() - INTERVAL '30 days'");
$stmt->execute();
$revenue_30_days = $stmt->fetch()['revenue'] ?? 0;

include '../views/dashboard.php'; // Load the view
?>