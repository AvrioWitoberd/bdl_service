<?php
// controllers/SparepartController.php

// === PERBAIKAN: Panggil database SEKALI saja ===
$pdo = require_once '../config/database.php';
require_once '../models/Sparepart.php';

$sparepartModel = new Sparepart($pdo);

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../views/auth/login.php");
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'create':
        $nama_sparepart = trim($_POST['nama_sparepart']);
        $stok = (int)$_POST['stok'];
        $harga = (float)$_POST['harga'];
        $merek = trim($_POST['merek']);

        if ($sparepartModel->create($nama_sparepart, $stok, $harga, $merek)) {
            header("Location: ../views/spareparts/list.php?msg=Spare part created successfully");
            exit;
        } else {
            $error_msg = 'Error creating spare part.';
            // Simpan input ke session agar tidak hilang
            $_SESSION['old_form'] = $_POST;
            header("Location: ../views/spareparts/create.php?error=" . urlencode($error_msg));
            exit;
        }
        break;

    case 'update':
        $id = (int)$_POST['id_sparepart'];
        $nama_sparepart = trim($_POST['nama_sparepart']);
        $stok = (int)$_POST['stok'];
        $harga = (float)$_POST['harga'];
        $merek = trim($_POST['merek']);

        if ($sparepartModel->update($id, $nama_sparepart, $stok, $harga, $merek)) {
            header("Location: ../views/spareparts/list.php?msg=Spare part updated successfully");
            exit;
        } else {
            // Redirect balik ke edit jika gagal
            header("Location: ../views/spareparts/edit.php?id=$id&msg=Error updating spare part");
            exit;
        }
        break;

    case 'delete':
        $id = (int)$_POST['id'];
        if ($sparepartModel->delete($id)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete spare part.']);
        }
        exit;

    default:
        header("Location: ../views/spareparts/list.php");
        exit;
}
?>