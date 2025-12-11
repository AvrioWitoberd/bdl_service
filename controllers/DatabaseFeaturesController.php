<?php
// controllers/DatabaseFeaturesController.php

// 1. Load Database SEKALI saja
$pdo = require_once '../config/database.php';
require_once '../models/DatabaseFeatures.php';

$dbFeaturesModel = new DatabaseFeatures($pdo);

session_start();
// Hanya Admin yang boleh akses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../views/auth/login.php");
    exit;
}

$refreshMessage = '';
$msgClass = '';

// Handle Refresh Action
if (isset($_GET['refresh_mv'])) {
    if ($dbFeaturesModel->refreshMonthlyRevenueMV()) {
        $refreshMessage = '✅ Materialized View Berhasil Di-refresh!';
        $msgClass = 'success';
    } else {
        $refreshMessage = '❌ Gagal me-refresh Materialized View.';
        $msgClass = 'error';
    }
}

// Ambil Data untuk View
$mvData = $dbFeaturesModel->getMonthlyRevenueMV();
$viewData = $dbFeaturesModel->getServiceSummaryViewData();
$explainResults = $dbFeaturesModel->getExplainAnalyzeResults();

include '../views/database_features/performance.php'; 
?>