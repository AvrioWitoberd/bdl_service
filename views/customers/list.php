<?php
// views/customers/list.php

// 1. Panggil database SEKALI saja
$pdo = require_once '../../config/database.php';
require_once '../../models/Pelanggan.php';

session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'teknisi')) {
    header("Location: ../auth/login.php");
    exit;
}

$pelangganModel = new Pelanggan($pdo);
$role = $_SESSION['role'];

// --- LOGIKA DELETE (AJAX/Direct Handle) ---
// Jika mau simpel tanpa JS, kita handle direct delete di sini juga bisa
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    if ($role === 'admin') {
        $idDelete = (int)$_GET['id'];
        if ($pelangganModel->delete($idDelete)) {
            header("Location: list.php?msg=Customer deleted successfully");
            exit;
        } else {
            $msg = "Gagal menghapus pelanggan.";
        }
    }
}

// --- PAGINATION & SEARCH ---
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$search = $_GET['search'] ?? '';
$msg = $_GET['msg'] ?? '';

$pelanggan = $pelangganModel->getAll($limit, $offset, $search);
$totalPelanggan = $pelangganModel->countAll($search);
$totalPages = ($totalPelanggan > 0) ? ceil($totalPelanggan / $limit) : 1;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Pelanggan</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <style>
        body { background-color: #f4f6f9; font-family: sans-serif; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        
        .content-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        
        .btn-add { background-color: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; font-weight: bold;}
        .btn-add:hover { background-color: #218838; }

        .search-container { display: flex; gap: 5px; }
        .input-search { padding: 8px; border: 1px solid #ddd; border-radius: 4px; width: 250px; }
        .btn-search { background-color: #007bff; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; }
        
        table { width: 100%; border-collapse: collapse; background: white; box-shadow: 0 0 5px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden; }
        th, td { padding: 15px; border-bottom: 1px solid #dee2e6; text-align: left; vertical-align: middle; }
        th { background-color: #f8f9fa; color: #495057; font-weight: 600; }
        tr:hover { background-color: #f1f1f1; }
        
        .action-link { margin-right: 10px; text-decoration: none; font-weight: bold; font-size: 14px; }
        .text-blue { color: #007bff; }
        .text-red { color: #dc3545; }
        
        .pagination { margin-top: 20px; display: flex; gap: 5px; }
        .page-item { padding: 8px 12px; border: 1px solid #ddd; text-decoration: none; background: white; color: #007bff; border-radius: 4px;}
        .page-item.active { background: #007bff; color: white; border-color: #007bff; }
    </style>
</head>
<body>
    <?php include '../partials/header.php'; ?>
    
    <div class="container">
        <h1>Data Pelanggan</h1>
        
        <?php if($msg): ?>
            <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 20px;">
                <?= htmlspecialchars($msg) ?>
            </div>
        <?php endif; ?>

        <div class="content-header">
            <?php if ($role === 'admin'): ?>
                <a href="create.php" class="btn-add">+ Tambah Pelanggan</a>
            <?php else: ?>
                <div></div>
            <?php endif; ?>

            <form method="GET" class="search-container">
                <input type="text" name="search" class="input-search" placeholder="Cari nama/hp/email..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn-search">Search</button>
            </form>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Lengkap</th>
                    <th>Kontak</th>
                    <th>Alamat</th>
                    <th>Tanggal Daftar</th>
                    <?php if($role === 'admin'): ?>
                        <th>Aksi</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pelanggan)): ?>
                    <tr><td colspan="6" style="text-align: center; padding: 20px;">Data tidak ditemukan.</td></tr>
                <?php else: ?>
                    <?php foreach ($pelanggan as $p): ?>
                    <tr>
                        <td><?= $p['id_pelanggan'] ?></td>
                        <td style="font-weight: 500;"><?= htmlspecialchars($p['nama']) ?></td>
                        <td>
                            <div><?= htmlspecialchars($p['no_hp']) ?></div>
                            <div style="font-size: 12px; color: #666;"><?= htmlspecialchars($p['email']) ?></div>
                        </td>
                        <td><?= htmlspecialchars($p['alamat']) ?></td>
                        <td><?= date('d/m/Y', strtotime($p['tanggal_daftar'])) ?></td>
                        
                        <?php if($role === 'admin'): ?>
                            <td>
                                <a href="edit.php?id=<?= $p['id_pelanggan'] ?>" class="action-link text-blue">Edit</a>
                                <a href="list.php?action=delete&id=<?= $p['id_pelanggan'] ?>" class="action-link text-red" onclick="return confirm('Yakin ingin menghapus data pelanggan ini?')">Del</a>
                            </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="pagination">
            <?php for($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?= $i ?>&search=<?= $search ?>" class="page-item <?= ($i == $page) ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    </div>
    
    <?php include '../partials/footer.php'; ?> </body>
</html>