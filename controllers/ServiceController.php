<?php
// controllers/ServiceController.php
require_once '../config/database.php';
require_once '../models/Service.php';
require_once '../models/Pembayaran.php'; // Jika perlu mengakses pembayaran

$pdo = require_once '../config/database.php';
$serviceModel = new Service($pdo);
$pembayaranModel = new Pembayaran($pdo);

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../views/auth/login.php");
    exit;
}

// Update Status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $serviceId = (int) $_POST['id_service'];
    $statusId = (int) $_POST['id_status'];
    $keterangan = trim($_POST['keterangan']);
    $catatan_internal = trim($_POST['catatan_internal']);

    // Hanya admin atau teknisi yang ditugaskan yang bisa mengupdate status
    if ($_SESSION['role'] === 'admin' || ($_SESSION['role'] === 'teknisi' && $_SESSION['user_id'] == $currentService['id_teknisi'])) {
        if ($serviceModel->updateStatus($serviceId, $statusId, $keterangan, $catatan_internal)) {
            header("Location: ../views/services/track.php?msg=Status updated successfully");
            exit;
        } else {
            header("Location: ../views/services/track.php?msg=Error updating status");
            exit;
        }
    } else {
        header("Location: ../views/services/track.php?msg=Access denied");
        exit;
    }
}

// Complete Service (Transaction)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'complete_service') {
    $serviceId = (int) $_POST['id_service'];
    $finalCost = (float) $_POST['final_cost'];
    $paymentMethod = trim($_POST['payment_method']);
    $discount = (float) $_POST['discount'];

    if ($_SESSION['role'] === 'admin') { // Biasanya hanya admin yang menyelesaikan dan mencatat pembayaran
        if ($serviceModel->completeService($serviceId, $finalCost, $paymentMethod, $discount)) {
            header("Location: ../views/services/track.php?msg=Service completed and payment recorded successfully");
            exit;
        } else {
            header("Location: ../views/services/track.php?msg=Error completing service or recording payment");
            exit;
        }
    } else {
        header("Location: ../views/services/track.php?msg=Access denied");
        exit;
    }
}

// Contoh untuk mengambil data service via AJAX (misalnya untuk edit)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_service') {
    $serviceId = (int) $_GET['id'];
    $service = $serviceModel->getById($serviceId);
    header('Content-Type: application/json');
    echo json_encode($service);
    exit;
}

?>