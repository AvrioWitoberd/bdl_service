<?php
// views/services/create.php

// 1. Koneksi Database
$pdo = require_once '../../config/database.php';

require_once '../../models/Pelanggan.php';
require_once '../../models/Teknisi.php';
require_once '../../models/Perangkat.php';
require_once '../../models/Service.php';

session_start();

// Cek Sesi
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'pelanggan'])) {
    header("Location: list.php");
    exit;
}

$pelangganModel = new Pelanggan($pdo);
$teknisiModel = new Teknisi($pdo);
$perangkatModel = new Perangkat($pdo);
$serviceModel = new Service($pdo);

$role = $_SESSION['role'];
$userId = $_SESSION['user_id'];
$error = '';

// --- PROSES SIMPAN DATA (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Ambil Data Form Perangkat
    $nama_perangkat  = trim($_POST['nama_perangkat']);
    $jenis_perangkat = trim($_POST['jenis_perangkat']);
    $merek           = trim($_POST['merek']);
    $nomor_seri      = trim($_POST['nomor_seri']);
    $keluhan         = trim($_POST['keluhan']);

    // 2. Tentukan Siapa Pelanggannya
    if ($role === 'admin') {
        $id_pelanggan = (int) $_POST['pelanggan_id'];
        // Data Service Admin
        $id_teknisi    = !empty($_POST['id_teknisi']) ? (int) $_POST['id_teknisi'] : null;
        $biaya_service = (float) $_POST['biaya_service'];
        $id_admin      = $userId;
    } else {
        // Pelanggan Login
        $id_pelanggan  = $userId;
        // Default Service Pelanggan
        $id_teknisi    = null;
        $biaya_service = 0;
        $id_admin      = null;
    }

    // 3. Validasi
    if (empty($nama_perangkat) || empty($jenis_perangkat) || empty($keluhan)) {
        $error = 'Nama Perangkat, Jenis, dan Keluhan wajib diisi.';
    } else {
        try {
            // Mulai Transaksi
            $pdo->beginTransaction();

            // A. SIMPAN PERANGKAT DULU -> Dapet ID Baru
            $newDeviceId = $perangkatModel->create(
                $id_pelanggan,
                $nama_perangkat,
                $jenis_perangkat,
                $merek,
                $nomor_seri
            );

            // B. SIMPAN SERVICE (Pakai ID Perangkat yg baru)
            $newServiceId = $serviceModel->create(
                $newDeviceId,
                $id_teknisi,
                $id_admin,
                $id_pelanggan,
                $keluhan,
                $biaya_service
            );

            // Commit Transaksi
            $pdo->commit();

            header("Location: list.php?msg=Service created successfully");
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Gagal menyimpan data: ' . $e->getMessage();
        }
    }
}

// Persiapan Data untuk Admin (List Pelanggan & Teknisi)
$pelangganList = [];
$teknisiList = [];
if ($role === 'admin') {
    $pelangganList = $pelangganModel->getAll(1000, 0);
    $teknisiList = $teknisiModel->getAll(1000, 0, '', true);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Request Service Baru</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <style>
        .form-container { max-width: 700px; margin: 2rem auto; padding: 2rem; background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 1rem; }
        .row { display: flex; gap: 15px; }
        .col { flex: 1; }
        label { display: block; margin-bottom: 0.5rem; font-weight: bold; }
        input, select, textarea { width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        .btn-submit { background: #28a745; color: white; padding: 12px; border: none; border-radius: 4px; cursor: pointer; width: 100%; font-size: 16px; margin-top: 10px;}
        .section-title { border-bottom: 2px solid #eee; padding-bottom: 10px; margin: 20px 0 15px 0; color: #555; }
    </style>
</head>
<body>
    <?php include '../partials/header.php'; ?>
    
    <div class="container">
        <div class="form-container">
            <h2 style="text-align: center;">Formulir Service Masuk</h2>
            
            <?php if ($error): ?>
                <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px;">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                
                <?php if ($role === 'admin'): ?>
                    <div class="form-group">
                        <label>Pilih Pelanggan</label>
                        <select name="pelanggan_id" required>
                            <option value="">-- Pilih Pelanggan --</option>
                            <?php foreach ($pelangganList as $p): ?>
                                <option value="<?= $p['id_pelanggan'] ?>"><?= htmlspecialchars($p['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <h3 class="section-title">Data Perangkat</h3>
                
                <div class="row">
                    <div class="col">
                        <label>Nama Perangkat *</label>
                        <input type="text" name="nama_perangkat" placeholder="Cth: iPhone 11 Pro" required>
                    </div>
                    
                    <div class="col">
                        <label>Jenis Perangkat *</label>
                        <input type="text" name="jenis_perangkat" placeholder="Cth: Handphone / Laptop / Kulkas" required>
                    </div>
                </div>

                <div class="row" style="margin-top: 10px;">
                    <div class="col">
                        <label>Merek/Brand</label>
                        <input type="text" name="merek" placeholder="Cth: Apple / Asus">
                    </div>
                    <div class="col">
                        <label>Nomor Seri (SN)</label>
                        <input type="text" name="nomor_seri" placeholder="Cth: SN12345678">
                    </div>
                </div>

                <h3 class="section-title">Keluhan & Masalah</h3>
                <div class="form-group">
                    <label>Deskripsi Keluhan *</label>
                    <textarea name="keluhan" rows="4" placeholder="Jelaskan kerusakan yang dialami..." required></textarea>
                </div>

                <?php if ($role === 'admin'): ?>
                    <h3 class="section-title">Data Internal (Admin)</h3>
                    <div class="row">
                        <div class="col">
                            <label>Tunjuk Teknisi</label>
                            <select name="id_teknisi">
                                <option value="">-- Nanti Saja --</option>
                                <?php foreach ($teknisiList as $t): ?>
                                    <option value="<?= $t['id_teknisi'] ?>"><?= htmlspecialchars($t['nama_teknisi']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col">
                            <label>Biaya Awal (Rp)</label>
                            <input type="number" name="biaya_service" value="0">
                        </div>
                    </div>
                <?php endif; ?>

                <button type="submit" class="btn-submit">Kirim Permintaan Service</button>
                <a href="list.php" style="display:block; text-align:center; margin-top:15px; text-decoration:none; color:#666;">Batal</a>

            </form>
        </div>
    </div>
</body>
</html>