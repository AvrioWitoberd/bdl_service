<?php
// views/technicians/list.php

$pdo = require_once '../../config/database.php';
require_once '../../models/Teknisi.php';

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$teknisiModel = new Teknisi($pdo);

// --- LOGIKA DELETE ---
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $idDelete = (int)$_GET['id'];
    if ($teknisiModel->delete($idDelete)) {
        header("Location: list.php?msg=Technician deleted successfully");
        exit;
    } else {
        $msg = "Gagal menghapus data.";
    }
}

// --- PAGINATION & SEARCH ---
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$search = $_GET['search'] ?? '';
$activeOnly = isset($_GET['active_only']); 
$msg = $_GET['msg'] ?? '';

$teknisi = $teknisiModel->getAll($limit, $offset, $search, $activeOnly);
$totalTeknisi = $teknisiModel->countAll($search, $activeOnly);
$totalPages = ($totalTeknisi > 0) ? ceil($totalTeknisi / $limit) : 1;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Teknisi</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <style>
        body { background-color: #f4f6f9; font-family: sans-serif; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        
        .content-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        
        .btn-add { background-color: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; font-weight: bold;}
        .btn-add:hover { background-color: #218838; }

        .search-container { display: flex; gap: 10px; align-items: center;}
        .input-search { padding: 8px; border: 1px solid #ddd; border-radius: 4px; width: 250px; }
        .btn-search { background-color: #007bff; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; }
        
        table { width: 100%; border-collapse: collapse; background: white; box-shadow: 0 0 5px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden; }
        th, td { padding: 15px; border-bottom: 1px solid #dee2e6; text-align: left; vertical-align: middle; }
        th { background-color: #f8f9fa; color: #495057; font-weight: 600; }
        tr:hover { background-color: #f1f1f1; }
        
        .action-link { margin-right: 10px; text-decoration: none; font-weight: bold; font-size: 14px; }
        .text-blue { color: #007bff; }
        .text-red { color: #dc3545; }
        
        /* Badge Status */
        .badge { padding: 5px 10px; border-radius: 50px; font-size: 12px; font-weight: bold; color: white; }
        .bg-success { background-color: #28a745; }
        .bg-danger { background-color: #dc3545; }
        
        .pagination { margin-top: 20px; display: flex; gap: 5px; }
        .page-item { padding: 8px 12px; border: 1px solid #ddd; text-decoration: none; background: white; color: #007bff; border-radius: 4px;}
        .page-item.active { background: #007bff; color: white; border-color: #007bff; }
    </style>
</head>
<body>
    <?php include '../partials/header.php'; ?>
    
    <div class="container">
        <h1>Data Teknisi</h1>
        
        <?php if($msg): ?>
            <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 20px;">
                <?= htmlspecialchars($msg) ?>
            </div>
        <?php endif; ?>

        <div class="content-header">
            <a href="create.php" class="btn-add">+ Tambah Teknisi</a>

            <form method="GET" class="search-container">
                <label style="font-size: 14px; cursor: pointer;">
                    <input type="checkbox" name="active_only" onchange="this.form.submit()" <?php if($activeOnly) echo 'checked'; ?>> 
                    Hanya Aktif
                </label>
                <input type="text" name="search" class="input-search" placeholder="Cari teknisi/keahlian..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn-search">Search</button>
            </form>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Teknisi</th>
                    <th>Keahlian</th>
                    <th>Kontak</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($teknisi)): ?>
                    <tr><td colspan="6" style="text-align: center; padding: 20px;">Data tidak ditemukan.</td></tr>
                <?php else: ?>
                    <?php foreach ($teknisi as $t): ?>
                    <tr>
                        <td><?= $t['id_teknisi'] ?></td>
                        <td style="font-weight: 500;"><?= htmlspecialchars($t['nama_teknisi']) ?></td>
                        <td><?= htmlspecialchars($t['keahlian']) ?></td>
                        <td>
                            <div><?= htmlspecialchars($t['no_hp']) ?></div>
                            <div style="font-size: 12px; color: #666;"><?= htmlspecialchars($t['email']) ?></div>
                        </td>
                        <td>
                            <?php if ($t['status_aktif']): ?>
                                <span class="badge bg-success">Aktif</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Non-Aktif</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="edit.php?id=<?= $t['id_teknisi'] ?>" class="action-link text-blue">Edit</a>
                            <a href="list.php?action=delete&id=<?= $t['id_teknisi'] ?>" class="action-link text-red" onclick="return confirm('Yakin ingin menghapus data teknisi ini?')">Del</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="pagination">
            <?php for($i = 1; $i <= $totalPages; $i++): ?>
                <?php 
                    // Membawa parameter search & active_only ke link pagination
                    $link = "?page=$i&search=" . urlencode($search);
                    if ($activeOnly) $link .= "&active_only=on";
                ?>
                <a href="<?= $link ?>" class="page-item <?= ($i == $page) ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    </div>
    
    <?php include '../partials/footer.php'; ?>
</body>
</html>