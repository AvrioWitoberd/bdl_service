<?php
// controllers/CustomerController.php

// Panggil database SEKALI saja di atas
$pdo = require_once '../config/database.php';
require_once '../models/Pelanggan.php';

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

        $error_msg = '';

        // Validasi Password
        if ($password !== $confirm_password) {
            $error_msg = 'Password and confirmation do not match.';
        } 
        // Coba Create
        elseif ($pelangganModel->create($nama, $no_hp, $alamat, $email, $password)) {
            // Sukses
            header("Location: ../views/customers/list.php?msg=Customer created successfully");
            exit;
        } else {
            // Gagal Query
            $error_msg = 'Error creating customer.';
        }

        // === JIKA GAGAL: SIMPAN INPUT KE SESSION & REDIRECT ===
        if ($error_msg) {
            $_SESSION['old_form'] = $_POST; // Simpan inputan biar gak ilang
            header("Location: ../views/customers/create.php?error=" . urlencode($error_msg));
            exit;
        }
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
            // Untuk update, redirect balik ke edit.php juga lebih aman
            header("Location: ../views/customers/edit.php?id=$id&msg=Error updating customer");
            exit;
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