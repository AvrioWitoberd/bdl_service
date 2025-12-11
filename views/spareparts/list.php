<?php
// views/spareparts/list.php

// 1. Panggil database SEKALI saja & simpan ke $pdo
$pdo = require_once '../../config/database.php';
require_once '../../models/Sparepart.php';

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$sparepartModel = new Sparepart($pdo);
$role = $_SESSION['role'];

// --- LOGIKA DELETE (Langsung di sini agar tidak tergantung JS eksternal) ---
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $idDelete = (int)$_GET['id'];
    if ($sparepartModel->delete($idDelete)) {
        header("Location: list.php?msg=Spare part deleted successfully");
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

$spareparts = $sparepartModel->getAll($limit, $offset, $search);
$totalSpareparts = $sparepartModel->countAll($search);
$totalPages = ($totalSpareparts > 0) ? ceil($totalSpareparts / $limit) : 1;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Spare Part</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <style>
        /* Styling agar seragam dengan halaman lain */
        body { background-color: #f4f6f9; font-family: sans-serif; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .content-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .btn-add { background-color: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; }
        .search-container { display: flex; gap: 5px; }
        .input-search { padding: 8px; border: 1px solid #ddd; border-radius: 4px; width: 300px; }
        .btn-search { background-color: #28a745; color: white; border: none; padding: 8px; border-radius: 4px; cursor: pointer; }
        table { width: 100%; border-collapse: collapse; background: white; box-shadow: 0 0 5px rgba(0,0,0,0.1); }
        th, td { padding: 12px; border-bottom: 1px solid #dee2e6; text-align: left; }
        th { background-color: #f8f9fa; }
        .action-link { margin-right: 10px; text-decoration: none; font-weight: bold; }
        .text-blue { color: #007bff; }
        .text-red { color: #dc3545; }
        .pagination { margin-top: 15px; }
        .page-item { padding: 8px 12px; border: 1px solid #ddd; margin-right: 5px; text-decoration: none; background: white; }
        .page-item.active { background: #007bff; color: white; border-color: #007bff; }
    </style>
</head>
<body>
    <?php include '../../views/partials/header.php'; ?>
    
    <div class="container">
        <h1>Data Spare Part</h1>
        
        <?php if (isset($_GET['msg'])): ?>
            <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 20px;">
                <?php echo htmlspecialchars($_GET['msg']); ?>
            </div>
        <?php endif; ?>

        <div class="content-header">
            <a href="create.php" class="btn-add">+ Tambah Spare Part</a>
            
            <form method="GET" class="search-container">
                <input type="text" name="search" class="input-search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Cari spare part...">
                <button type="submit" class="btn-search">Search</button>
            </form>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama</th>
                    <th>Stok</th>
                    <th>Harga</th>
                    <th>Merek</th>
                    <th>Update Terakhir</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($spareparts)): ?>
                    <tr><td colspan="7" style="text-align:center;">Data tidak ditemukan</td></tr>
                <?php else: ?>
                    <?php foreach ($spareparts as $s): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($s['id_sparepart']); ?></td>
                        <td><?php echo htmlspecialchars($s['nama_sparepart']); ?></td>
                        <td><?php echo $s['stok']; ?></td>
                        
                        <td>Rp <?php echo number_format($s['harga'], 0, ',', '.'); ?></td>
                        
                        <td><?php echo htmlspecialchars($s['merek']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($s['tanggal_update'] ?? 'now')); ?></td>
                        <td>
                            <a href="edit.php?id=<?php echo $s['id_sparepart']; ?>" class="action-link text-blue">Edit</a>
                            <a href="list.php?action=delete&id=<?php echo $s['id_sparepart']; ?>" class="action-link text-red" onclick="return confirm('Yakin ingin menghapus data ini?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>" class="page-item <?php if ($i == $page) echo 'active'; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
    </div>
    
    <?php include '../../views/partials/footer.php'; ?>
</body>
</html>