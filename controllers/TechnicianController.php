<?php
// controllers/TechnicianController.php

$pdo = require_once '../config/database.php';
require_once '../models/Teknisi.php';

$teknisiModel = new Teknisi($pdo);

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../views/auth/login.php");
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'create':
        $nama_teknisi = trim($_POST['nama_teknisi']);
        $keahlian     = trim($_POST['keahlian']);
        $no_hp        = trim($_POST['no_hp']);
        $email        = trim($_POST['email']);
        $password     = $_POST['password'];
        $confirm_pass = $_POST['confirm_password'];
        $status_aktif = isset($_POST['status_aktif']) ? 1 : 0;

        $error_msg = '';

        // Validasi
        if ($password !== $confirm_pass) {
            $error_msg = 'Password and confirmation do not match.';
        } elseif ($teknisiModel->create($nama_teknisi, $keahlian, $no_hp, $email, $password, $status_aktif)) {
            // Sukses
            header("Location: ../views/technicians/list.php?msg=Technician created successfully");
            exit;
        } else {
            // Gagal Query
            $error_msg = 'Error creating technician.';
        }
        
        // === JIKA GAGAL: SIMPAN INPUT KE SESSION & REDIRECT ===
        if ($error_msg) {
            $_SESSION['old_form'] = $_POST; // Simpan inputan biar gak ilang
            header("Location: ../views/technicians/create.php?error=" . urlencode($error_msg));
            exit;
        }
        break;

    case 'update':
        $id           = (int)$_POST['id_teknisi'];
        $nama_teknisi = trim($_POST['nama_teknisi']);
        $keahlian     = trim($_POST['keahlian']);
        $no_hp        = trim($_POST['no_hp']);
        $email        = trim($_POST['email']);
        $status_aktif = isset($_POST['status_aktif']) ? 1 : 0;

        if ($teknisiModel->update($id, $nama_teknisi, $keahlian, $no_hp, $email, $status_aktif)) {
            header("Location: ../views/technicians/list.php?msg=Technician updated successfully");
            exit;
        } else {
            // Logic Redirect untuk Update juga bisa diterapkan disini jika mau
            $message = 'Error updating technician.';
            $teknisi = $teknisiModel->getById($id);
            include '../views/technicians/edit.php';
        }
        break;

    case 'delete':
        $id = (int)$_POST['id'];
        if ($teknisiModel->delete($id)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete technician.']);
        }
        exit;

    default:
        header("Location: ../views/technicians/list.php");
        exit;
}
?>