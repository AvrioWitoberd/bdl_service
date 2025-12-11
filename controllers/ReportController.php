<?php
// controllers/ReportController.php

// PERBAIKAN: Load DB sekali saja
$pdo = require_once '../config/database.php';
require_once '../models/Report.php';

$reportModel = new Report($pdo);

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../views/auth/login.php");
    exit;
}

// ... Sisa kode sama ...
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';

$servicesByStatus = $reportModel->getServicesByStatusReport($startDate, $endDate);
$revenueByTech = $reportModel->getRevenueByTechnicianReport($startDate, $endDate);

// Handle Export
if (isset($_GET['export']) && $_GET['export'] === 'services_by_status') {
    $reportModel->exportToCSV($servicesByStatus, 'services_by_status_report.csv');
}
if (isset($_GET['export']) && $_GET['export'] === 'revenue_by_tech') {
    $reportModel->exportToCSV($revenueByTech, 'revenue_by_technician_report.csv');
}

include '../views/reports/index.php';
?>