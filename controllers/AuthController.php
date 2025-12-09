<?php
require_once '../models/User.php';

$pdo = require_once '../config/database.php';
if (!$pdo) {
    die("Database connection failed.");
}

$user = new User($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'login') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $login_as = $_POST['login_as'];

    $success = false;
    if ($login_as === 'admin') {
        $success = $user->authenticateAdmin($username, $password);
    } elseif ($login_as === 'pelanggan') {
        $success = $user->authenticatePelanggan($username, $password);
    } elseif ($login_as === 'teknisi') {
        $success = $user->authenticateTeknisi($username, $password);
    }

    if ($success) {
        $role = $_SESSION['role'];
        if ($role === 'admin') {
            $redirect = '../views/dashboard.php';
        } elseif ($role === 'pelanggan') {
            $redirect = '../views/services/list.php';
        } elseif ($role === 'teknisi') {
            $redirect = '../views/services/list.php';
        } else {
            $redirect = '../public/index.php';
        }

        header("Location: $redirect");
        exit;
    } else {
        header("Location: ../views/auth/login.php?error=1&login_as=" . urlencode($login_as));
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['action'] === 'logout') {
    session_start();
    session_unset();
    session_destroy();
    header("Location: ../views/auth/login.php");
    exit;
}
