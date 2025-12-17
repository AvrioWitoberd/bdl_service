<?php
// views/services/list.php

// 1. Panggil database SEKALI saja & simpan ke $pdo
$pdo = require_once '../../config/database.php';
require_once '../../models/Service.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$serviceModel = new Service($pdo);
$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

// --- LOGIKA DELETE (Hanya Admin) ---
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    if ($role === 'admin') { 
        $idDelete = (int)$_GET['id'];
        if ($serviceModel->delete($idDelete)) {
            header("Location: list.php?msg=Service deleted successfully");
            exit;
        } else {
            $msg = "Gagal menghapus data.";
        }
    } else {
        $msg = "Akses ditolak.";
    }
}

// --- SETUP PAGINATION & SEARCH ---
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';

// --- CONFIG FILTER BERDASARKAN ROLE ---
$filterStatus = ''; 
$filterTeknisi = '';
$filterPelanggan = '';

if ($role === 'teknisi') {
    $filterTeknisi = $user_id; // Teknisi hanya lihat job dia
} 
elseif ($role === 'pelanggan') {
    $filterPelanggan = $user_id; // Pelanggan HANYA lihat punya dia sendiri
}

// Ambil data
$services = $serviceModel->getServices($limit, $offset, $search, $filterStatus, $filterTeknisi, $filterPelanggan);
$totalServices = $serviceModel->countServices($search, $filterStatus, $filterTeknisi, $filterPelanggan);
$totalPages = ($totalServices > 0) ? ceil($totalServices / $limit) : 1;

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Service</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <style>
        body { background-color: #f4f6f9; font-family: sans-serif; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .content-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .btn-add { background-color: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; }
        .search-container { display: flex; gap: 5px; }
        .input-search { padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        .btn-search { background-color: #28a745; color: white; border: none; padding: 8px; border-radius: 4px; cursor: pointer; }
        table { width: 100%; border-collapse: collapse; background: white; box-shadow: 0 0 5px rgba(0,0,0,0.1); }
        th, td { padding: 12px; border-bottom: 1px solid #dee2e6; text-align: left; }
        th { background-color: #f8f9fa; }
        .badge { padding: 5px 10px; border-radius: 50px; font-size: 12px; color: white; background: #6c757d; }
        .bg-success { background: #28a745; }
        .bg-warning { background: #ffc107; color: #212529; }
        .bg-info { background: #17a2b8; }
        .bg-danger { background: #dc3545; } /* MERAH untuk Dibatalkan */
        .action-link { margin-right: 10px; text-decoration: none; font-weight: bold; }
        .text-blue { color: #007bff; }
        .text-red { color: #dc3545; }
        .pagination { margin-top: 15px; }
        .page-item { padding: 8px 12px; border: 1px solid #ddd; margin-right: 5px; text-decoration: none; background: white; }
        .page-item.active { background: #007bff; color: white; border-color: #007bff; }
    </style>
</head>
<body>
    <?php include '../partials/header.php'; ?>
    
    <div class="container">
        <h1>Daftar Service <?= ($role === 'pelanggan') ? 'Saya' : '' ?></h1>
        
        <?php if($msg): ?>
            <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 20px;">
                <?= htmlspecialchars($msg) ?>
            </div>
        <?php endif; ?>

        <div class="content-header">
            <?php if ($role !== 'teknisi'): ?>
                <a href="create.php" class="btn-add">+ Request Service Baru</a>
            <?php else: ?>
                <div></div> 
            <?php endif; ?>

            <form method="GET" class="search-container">
                <input type="text" name="search" class="input-search" placeholder="Cari..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn-search">Search</button>
            </form>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    
                    <?php if($role !== 'pelanggan'): ?>
                        <th>Pelanggan</th>
                    <?php endif; ?>
                    
                    <th>Perangkat</th>
                    <th>Keluhan</th>
                    
                    <th>Biaya</th>
                    
                    <th>Status</th>
                    
                    <?php if($role === 'admin'): ?><th>Teknisi</th><?php endif; ?>
                    
                    <th>Tanggal</th>
                    
                    <?php if($role !== 'pelanggan'): ?>
                        <th>Aksi</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($services)): ?>
                    <tr><td colspan="9" style="text-align: center;">Tidak ada data service.</td></tr>
                <?php else: ?>
                    <?php foreach ($services as $s): ?>
                    <tr>
                        <td><?= $s['id_service'] ?></td>
                        
                        <?php if($role !== 'pelanggan'): ?>
                            <td><?= htmlspecialchars($s['nama_pelanggan']) ?></td>
                        <?php endif; ?>

                        <td>
                            <strong><?= htmlspecialchars($s['nama_perangkat'] ?? 'Device') ?></strong><br>
                            <small><?= htmlspecialchars($s['merek'] ?? '-') ?></small>
                        </td>
                        <td><?= htmlspecialchars($s['keluhan']) ?></td>
                        
                        <td style="font-weight: bold; color: #555;">
                            Rp <?= number_format($s['biaya_service'], 0, ',', '.') ?>
                        </td>

                        <td>
                            <?php 
                                $st = strtolower($s['nama_status']);
                                $bg = 'bg-warning'; // Default untuk status "menunggu" dan "hold"
                                
                                // Logika Status Selesai / Siap
                                if (strpos($st, 'selesai') !== false || strpos($st, 'ambil') !== false || strpos($st, 'siap') !== false) {
                                    $bg = 'bg-success'; // Hijau
                                } 
                                // Logika Status Dalam Proses / Pengecekan
                                elseif (strpos($st, 'proses') !== false || strpos($st, 'diagnosa') !== false || strpos($st, 'diterima') !== false || strpos($st, 'uji coba') !== false) {
                                    $bg = 'bg-info'; // Biru Muda
                                }
                                // Logika Status Dibatalkan
                                elseif (strpos($st, 'batal') !== false) {
                                    $bg = 'bg-danger'; // Merah
                                }
                                // Sisanya (Menunggu Konfirmasi, Menunggu Sparepart) akan default ke 'bg-warning'
                            ?>
                            <span class="badge <?= $bg ?>"><?= htmlspecialchars($s['nama_status']) ?></span>
                        </td>
                        
                        <?php if($role === 'admin'): ?>
                            <td><?= htmlspecialchars($s['nama_teknisi'] ?? '-') ?></td>
                        <?php endif; ?>

                        <td><?= date('d/m/Y', strtotime($s['tanggal_masuk'])) ?></td>
                        
                        <?php if($role !== 'pelanggan'): ?>
                            <td>
                                <a href="edit.php?id=<?= $s['id_service'] ?>" class="action-link text-blue">Edit</a>
                                
                                <?php if($role === 'admin'): ?>
                                    <a href="list.php?action=delete&id=<?= $s['id_service'] ?>" class="action-link text-red" onclick="return confirm('Hapus data?')">Del</a>
                                <?php endif; ?>
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
</body>
</html>