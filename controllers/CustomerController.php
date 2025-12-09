<?php
// controllers/CustomerController.php
require_once '../config/database.php';
require_once '../models/Pelanggan.php';

$pdo = require_once '../config/database.php';
$pelangganModel = new Pelanggan($pdo);

session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'teknisi')) {
    header("Location: ../views/auth/login.php");
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'create':
        $nama = trim($_POST['nama']);
        $no_hp = trim($_POST['no_hp']);
        $alamat = trim($_POST['alamat']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        $message = '';
        if ($password !== $confirm_password) {
            $message = 'Password and confirmation do not match.';
        } elseif ($pelangganModel->create($nama, $no_hp, $alamat, $email, $password)) {
            header("Location: ../views/customers/list.php?msg=Customer created successfully");
            exit;
        } else {
            $message = 'Error creating customer.';
        }
        include '../views/customers/create.php';
        break;

    case 'update':
        $id = (int)$_POST['id_pelanggan'];
        $nama = trim($_POST['nama']);
        $no_hp = trim($_POST['no_hp']);
        $alamat = trim($_POST['alamat']);
        $email = trim($_POST['email']);

        if ($pelangganModel->update($id, $nama, $no_hp, $alamat, $email)) {
            header("Location: ../views/customers/list.php?msg=Customer updated successfully");
            exit;
        } else {
            $message = 'Error updating customer.';
            $pelanggan = $pelangganModel->getById($id);
            include '../views/customers/edit.php';
        }
        break;

    case 'delete':
        $id = (int)$_POST['id'];
        if ($pelangganModel->delete($id)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete customer.']);
        }
        exit;

    default:
        header("Location: ../views/customers/list.php");
        exit;
}
?>