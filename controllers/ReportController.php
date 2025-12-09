<?php
// controllers/ReportController.php
require_once '../config/database.php';
require_once '../models/Report.php';

$pdo = require_once '../config/database.php';
$reportModel = new Report($pdo);

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../views/auth/login.php");
    exit;
}

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

include '../views/reports/index.php'; // Load the view
?>