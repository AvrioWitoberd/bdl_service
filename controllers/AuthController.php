<?php
require_once '../models/User.php';

session_start();

$pdo = require_once '../config/database.php';
if (!$pdo) {
    die("Database connection failed.");
}

$user = new User($pdo);

// =====================
// LOGIN TANPA PILIH ROLE
// =====================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'login') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        header("Location: ../views/auth/login.php?error=empty");
        exit;
    }

    $success = false;

    // Cek admin dulu
    if ($user->authenticateAdmin($email, $password)) {
        $success = true;
    }

    // Kalau bukan admin, cek teknisi
    if (!$success && $user->authenticateTeknisi($email, $password)) {
        $success = true;
    }

    // Kalau bukan teknisi, cek pelanggan
    if (!$success && $user->authenticatePelanggan($email, $password)) {
        $success = true;
    }

    if ($success) {
        $role = $_SESSION['role'] ?? '';

        if ($role === 'admin') {
            header("Location: ../views/dashboard.php");
        } else {
            header("Location: ../views/services/list.php");
        }
        exit;
    }

    header("Location: ../views/auth/login.php?error=1");
    exit;
}

// =====================
// LOGOUT
// =====================
if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['action'] ?? '') === 'logout') {
    session_unset();
    session_destroy();
    header("Location: ../views/auth/login.php");
    exit;
}
