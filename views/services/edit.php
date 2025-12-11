<?php
// views/services/edit.php

$pdo = require_once '../../config/database.php'; 
require_once '../../models/Service.php';
require_once '../../models/Teknisi.php';

session_start();

// Cek sesi
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$role = $_SESSION['role']; // Ambil role user
$userId = $_SESSION['user_id']; // ID User yang sedang login

// Cek Role: Hanya Admin & Teknisi boleh masuk
if ($role === 'pelanggan') {
    header("Location: list.php");
    exit;
}

$serviceModel = new Service($pdo);
$teknisiModel = new Teknisi($pdo);

// Ambil ID dari URL
$id_service = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ambil Data Service Lama
$service = $serviceModel->getById($id_service);

if (!$service) {
    header("Location: list.php?msg=Data not found");
    exit;
}

// Ambil daftar teknisi & status
$teknisiList = $teknisiModel->getAll(100, 0, '', true);
$stmtStatus = $pdo->query("SELECT * FROM status_perbaikan ORDER BY id_status ASC");
$statusList = $stmtStatus->fetchAll();

// --- PROSES UPDATE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_teknisi = !empty($_POST['id_teknisi']) ? $_POST['id_teknisi'] : null;
    $keluhan = $_POST['keluhan'];
    $biaya = $_POST['biaya_service'];
    $id_status = $_POST['id_status'];

    // === LOGIKA PENENTUAN ADMIN ===
    if ($role === 'admin') {
        // Jika yang ngedit ADMIN, otomatis catat "Saya yang bertanggung jawab sekarang"
        $id_admin_to_save = $userId;
    } else {
        // Jika yang ngedit TEKNISI, jangan ubah admin penanggung jawab (pakai data lama)
        $id_admin_to_save = $service['id_admin'];
    }

    // Panggil fungsi update dengan parameter baru ($id_admin_to_save)
    if ($serviceModel->update($id_service, $id_teknisi, $keluhan, $biaya, $id_status, $id_admin_to_save)) {
        header("Location: list.php?msg=Service updated successfully");
        exit;
    } else {
        $error = "Gagal mengupdate data service.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Service</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <style>
        .form-container { max-width: 600px; margin: 2rem auto; padding: 2rem; background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.5rem; font-weight: bold; }
        input[type="text"], input[type="number"], textarea, select { width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;}
        .btn-submit { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-cancel { background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; margin-left: 10px; }
        
        /* Style tambahan untuk input readonly agar terlihat jelas tidak bisa diedit */
        input[readonly], select[disabled], textarea[readonly] {
            background-color: #e9ecef;
            cursor: not-allowed;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <?php include '../partials/header.php'; ?>
    
    <div class="container">
        <div class="form-container">
            <h2>Edit Service #<?= $id_service ?></h2>
            
            <?php if(isset($error)): ?>
                <p style="color:red;"><?= $error ?></p>
            <?php endif; ?>

            <form method="POST">
                
                <div class="form-group">
                    <label>Pelanggan</label>
                    <input type="text" value="<?= htmlspecialchars($service['nama_pelanggan']) ?>" readonly>
                </div>

                <div class="form-group">
                    <label>Perangkat</label>
                    <input type="text" value="<?= htmlspecialchars($service['nama_perangkat'] ?? '') ?> - <?= htmlspecialchars($service['merek'] ?? '') ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="keluhan">Keluhan / Kerusakan</label>
                    <textarea name="keluhan" id="keluhan" rows="3" required><?= htmlspecialchars($service['keluhan']) ?></textarea>
                </div>

                <div class="form-group">
                    <label for="id_status">Status Pengerjaan</label>
                    <select name="id_status" id="id_status" required>
                        <?php foreach($statusList as $st): ?>
                            <option value="<?= $st['id_status'] ?>" <?= ($st['id_status'] == $service['id_status']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($st['nama_status']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="id_teknisi">Teknisi Penanggung Jawab</label>
                    
                    <?php if ($role === 'admin'): ?>
                        <select name="id_teknisi" id="id_teknisi">
                            <option value="">-- Belum Ditunjuk --</option>
                            <?php foreach($teknisiList as $t): ?>
                                <option value="<?= $t['id_teknisi'] ?>" <?= ($t['id_teknisi'] == $service['id_teknisi']) ? 'selected' : '' ?>>
                                    <?= $t['nama_teknisi'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                        <input type="text" value="<?= htmlspecialchars($service['nama_teknisi'] ?? 'Belum Ditunjuk') ?>" readonly>
                        <input type="hidden" name="id_teknisi" value="<?= $service['id_teknisi'] ?>">
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="biaya_service">Estimasi Biaya</label>
                    
                    <?php if ($role === 'admin'): ?>
                        <input type="number" name="biaya_service" id="biaya_service" value="<?= $service['biaya_service'] ?>">
                    <?php else: ?>
                        <input type="text" value="Rp <?= number_format($service['biaya_service'], 0, ',', '.') ?>" readonly>
                        <input type="hidden" name="biaya_service" value="<?= $service['biaya_service'] ?>">
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn-submit">Simpan Perubahan</button>
                <a href="list.php" class="btn-cancel">Batal</a>
            </form>
        </div>
    </div>
</body>
</html>