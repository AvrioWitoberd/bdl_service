<?php
// controllers/ReportController.php

// 1. Hubungkan Database & Model
require_once '../config/database.php';
require_once '../models/Report.php';

// 2. Start Session
session_start();

// 3. Cek Login Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../views/auth/login.php");
    exit;
}

// 4. Inisialisasi Model
$reportModel = new Report($pdo);

// 5. Logic Refresh MV
$refreshMessage = '';
$msgClass = '';

if (isset($_GET['refresh_mv'])) {
    if ($reportModel->refreshMonthlyRevenueMV()) {
        $refreshMessage = '✅ Materialized View Berhasil Di-refresh!';
        $msgClass = 'success';
    } else {
        $refreshMessage = '❌ Gagal refresh MV (Pastikan MV sudah dibuat di Database).';
        $msgClass = 'error';
    }
}

// 6. Filter Tanggal
$startDate = $_GET['start_date'] ?? null;
$endDate = $_GET['end_date'] ?? null;
$endDateForQuery = $endDate ? $endDate . ' 23:59:59' : null;

// 7. AMBIL DATA DARI MODEL
// Bagian Laporan Standar
$servicesByStatus = $reportModel->getServicesByStatusReport($startDate, $endDateForQuery);
$revenueByTech = $reportModel->getTechnicianPerformance($startDate, $endDateForQuery);

// Bagian Fitur Lanjutan
$mvData = $reportModel->getMonthlyRevenueMV();
$viewData = $reportModel->getServiceSummaryViewData(5); 
$pendingServices = $reportModel->getPendingServicesSimple();
$explainResults = $reportModel->getExplainAnalyzeResults();

// 8. TAMPILKAN KE VIEW
include '../views/reports/index.php';
?>