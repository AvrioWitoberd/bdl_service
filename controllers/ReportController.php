<?php
// controllers/ReportController.php

// Load DB dan Model
$pdo = require_once '../config/database.php';
require_once '../models/Report.php';
require_once '../models/DatabaseFeatures.php'; // Tambahkan model DB Features

$reportModel = new Report($pdo);
$dbFeaturesModel = new DatabaseFeatures($pdo); // Inisialisasi model DB Features

session_start();

// Proteksi Halaman Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../views/auth/login.php");
    exit;
}

// Handle Refresh Materialized View (tambahkan ini)
$refreshMessage = '';
$msgClass = '';
if (isset($_GET['refresh_mv'])) {
    if ($dbFeaturesModel->refreshMonthlyRevenueMV()) {
        $refreshMessage = '✅ Materialized View Berhasil Di-refresh!';
        $msgClass = 'success';
    } else {
        $refreshMessage = '❌ Gagal me-refresh Materialized View.';
        $msgClass = 'error';
    }
}

// Mengambil Parameter Filter Tanggal (untuk laporan lama)
$startDate = $_GET['start_date'] ?? null;
$endDate = $_GET['end_date'] ?? null;
$endDateForQuery = $endDate ? $endDate . ' 23:59:59' : null;

// 1. Mengambil Laporan Statistik Status Service (lama)
$servicesByStatus = $reportModel->getServicesByStatusReport($startDate, $endDateForQuery);

// 2. Mengambil Laporan Performa Teknisi (lama)
$revenueByTech = $reportModel->getTechnicianPerformance($startDate, $endDateForQuery);

// 3. Mengambil Data dari Materialized View (baru)
$mvData = $dbFeaturesModel->getMonthlyRevenueMV();

// 4. Mengambil Data dari Complex View (baru)
$viewData = $dbFeaturesModel->getServiceSummaryViewData(5); // Ambil 5 terbaru

// 5. Mengambil Data EXPLAIN ANALYZE (baru)
$explainResults = $dbFeaturesModel->getExplainAnalyzeResults();

// --- LOGIKA EXPORT (lama) ---
if (isset($_GET['export'])) {
    if ($_GET['export'] === 'services_by_status') {
        $reportModel->exportToCSV($servicesByStatus, 'statistik_status_service.csv');
    } elseif ($_GET['export'] === 'revenue_by_tech') {
        $reportModel->exportToCSV($revenueByTech, 'performa_teknisi_report.csv');
    }
    // Bisa tambah export buat MV atau View nanti
}

// Kirim semua data ke view
$refreshMessage = $refreshMessage;
$msgClass = $msgClass;
$mvData = $mvData;
$viewData = $viewData;
$explainResults = $explainResults;

// Load View
include '../views/reports/index.php';