<?php
// controllers/DatabaseFeaturesController.php
require_once '../config/database.php';
require_once '../models/DatabaseFeatures.php';

$pdo = require_once '../config/database.php';
$dbFeaturesModel = new DatabaseFeatures($pdo);

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../views/auth/login.php");
    exit;
}

$refreshMessage = '';
if (isset($_GET['refresh_mv'])) {
    if ($dbFeaturesModel->refreshMonthlyRevenueMV()) {
        $refreshMessage = 'Materialized View Refreshed Successfully!';
    } else {
        $refreshMessage = 'Error refreshing Materialized View.';
    }
}

$mvData = $dbFeaturesModel->getMonthlyRevenueMV();
$viewData = $dbFeaturesModel->getServiceSummaryViewData();
$explainResults = $dbFeaturesModel->getExplainAnalyzeResults();

include '../views/database_features/performance.php'; // Load the view
?>