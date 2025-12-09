<?php
// controllers/SparepartController.php
require_once '../config/database.php';
require_once '../models/Sparepart.php';

$pdo = require_once '../config/database.php';
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
            $message = 'Error creating spare part.';
            include '../views/spareparts/create.php';
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
            $message = 'Error updating spare part.';
            $sparepart = $sparepartModel->getById($id);
            include '../views/spareparts/edit.php';
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