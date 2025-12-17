<?php
// views/services/create.php

// ==========================================
// 1. BAGIAN LOGIKA (TIDAK DIUBAH SAMA SEKALI)
// ==========================================

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
    $nama_perangkat = trim($_POST['nama_perangkat']);
    $jenis_perangkat = trim($_POST['jenis_perangkat']);
    $merek = trim($_POST['merek']);
    $nomor_seri = trim($_POST['nomor_seri']);
    $keluhan = trim($_POST['keluhan']);

    // 2. Tentukan Siapa Pelanggannya
    if ($role === 'admin') {
        $id_pelanggan = (int) $_POST['pelanggan_id'];
        // Data Service Admin
        $id_teknisi = !empty($_POST['id_teknisi']) ? (int) $_POST['id_teknisi'] : null;
        $biaya_service = (float) $_POST['biaya_service'];
        $id_admin = $userId;
    } else {
        // Pelanggan Login
        $id_pelanggan = $userId;
        // Default Service Pelanggan
        $id_teknisi = null;
        $biaya_service = 0;
        $id_admin = null;
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Service Baru</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* --- RESET & BASIC STYLE --- */
        :root {
            --primary: #435ebe;
            --secondary: #6c757d;
            --success: #198754; 
            --bg-light: #f2f7ff;
            --white: #ffffff;
            --text-dark: #25396f;
            --text-muted: #7c8db5;
            --border-color: #dce7f1;
        }

        body {
            font-family: 'Nunito', sans-serif;
            background-color: var(--bg-light);
            margin: 0;
            padding: 0;
            color: var(--text-dark);
        }

        /* --- LAYOUT WRAPPER (Simulasi Dashboard) --- */
        .wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Dummy (Hanya Visual agar mirip Refrensi) */
        .sidebar {
            width: 260px;
            background: #fff;
            padding: 20px;
            display: none; /* Hidden di mobile, bisa diubah */
        }
        @media(min-width: 768px) { .sidebar { display: block; } }

        .main-content {
            flex: 1;
            padding: 2rem;
        }

        /* --- HEADER SECTION --- */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        .page-title h3 { margin: 0; font-size: 1.5rem; font-weight: 700; }
        .page-title p { margin: 5px 0 0; color: var(--text-muted); font-size: 0.9rem; }
        .btn-back {
            text-decoration: none;
            color: var(--secondary);
            background: #fff;
            padding: 8px 16px;
            border-radius: 5px;
            border: 1px solid var(--border-color);
            font-size: 0.9rem;
            transition: 0.3s;
        }
        .btn-back:hover { background: #e9ecef; }

        /* --- CARD STYLE --- */
        .card {
            background: var(--white);
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.03);
            border: none;
            margin-bottom: 2rem;
            overflow: hidden;
        }
        
        .card-body {
            padding: 2rem;
        }

        /* --- SECTION DIVIDER --- */
        .section-label {
            display: flex;
            align-items: center;
            color: var(--text-muted);
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 1.5rem;
            margin-top: 0.5rem;
        }
        .section-label i { margin-right: 8px; font-size: 1rem; }
        
        /* Garis pemisah halus antar section */
        .divider { 
            height: 1px; 
            background: var(--border-color); 
            margin: 2rem 0; 
            opacity: 0.6;
        }

        /* --- FORM GRID SYSTEM --- */
        .form-row {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        @media(min-width: 768px) {
            .form-row { grid-template-columns: 1fr 1fr; }
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 0.95rem;
            color: #4a5568;
        }
        
        /* Tanda Bintang Merah */
        label span.required { color: #dc3545; }

        /* --- INPUT STYLING (THEME UTAMA) --- */
        .input-group {
            position: relative;
            display: flex;
            align-items: center;
        }
        
        .input-icon {
            position: absolute;
            left: 15px;
            color: #a0aec0;
            z-index: 2;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px 12px 45px; /* Padding kiri besar untuk icon */
            font-size: 0.95rem;
            font-family: inherit;
            color: #495057;
            background-color: #fff;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            transition: border-color 0.2s, box-shadow 0.2s;
            box-sizing: border-box;
        }

        .form-control:focus {
            border-color: var(--primary);
            outline: 0;
            box-shadow: 0 0 0 3px rgba(67, 94, 190, 0.1);
        }

        textarea.form-control {
            padding: 15px;
            min-height: 100px;
            resize: vertical;
        }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 16px 12px;
            padding-right: 40px;
        }

        /* --- ALERT ERROR --- */
        .alert-error {
            background: #ffebe9;
            border: 1px solid #ffc1bc;
            color: #cc3636;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        .alert-error i { margin-right: 10px; }

        /* --- FOOTER ACTIONS --- */
        .form-actions {
            background: #f9fafb;
            padding: 1.5rem 2rem;
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 24px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            border: none;
            font-size: 0.95rem;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
            box-shadow: 0 3px 6px rgba(67, 94, 190, 0.2);
        }
        .btn-primary:hover { background-color: #354a96; transform: translateY(-1px); }

        .btn-secondary {
            background-color: #fff;
            color: var(--secondary);
            border: 1px solid var(--border-color);
        }
        .btn-secondary:hover { background-color: #f1f3f5; }

        /* Layout Admin / Internal khusus */
        .admin-panel {
            background-color: #f8f9fa; 
            padding: 20px; 
            border-radius: 8px; 
            border: 1px dashed #ced4da;
        }

    </style>
</head>
<body>

    <?php // include '../partials/header.php'; ?> 

    <div class="wrapper">
        
        <nav class="sidebar">
            <div style="padding-bottom: 20px; border-bottom: 1px solid #eee; margin-bottom: 20px;">
                <h3 style="color: var(--primary); margin:0;"><i class="fas fa-tools"></i> ServiceApp</h3>
            </div>
            <div style="color: #444; padding: 10px; font-weight:600;"><i class="fas fa-home" style="width:25px"></i> Dashboard</div>
            <div style="color: var(--primary); background: #eef2ff; padding: 10px; border-radius: 5px; font-weight:600;"><i class="fas fa-plus-circle" style="width:25px"></i> Service Baru</div>
            <div style="color: #444; padding: 10px; font-weight:600;"><i class="fas fa-users" style="width:25px"></i> Pelanggan</div>
        </nav>

        <div class="main-content">
            
            <div class="page-header">
                <div class="page-title">
                    <h3>Service Masuk</h3>
                    <p>Input data perangkat dan keluhan pelanggan</p>
                </div>
                <a href="list.php" class="btn-back"><i class="fas fa-arrow-left"></i> Kembali</a>
            </div>

            <form method="POST">
                <div class="card">
                    <div class="card-body">

                        <?php if ($error): ?>
                                <div class="alert-error">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <span><?= $error ?></span>
                                </div>
                        <?php endif; ?>

                        <?php if ($role === 'admin'): ?>
                                <div class="section-label"><i class="fas fa-user-tag"></i> Informasi Pelanggan</div>
                                <div class="form-group">
                                    <label>Pilih Pelanggan <span class="required">*</span></label>
                                    <div class="input-group">
                                        <i class="fas fa-user input-icon"></i>
                                        <select name="pelanggan_id" class="form-control" required>
                                            <option value="">-- Cari Nama Pelanggan --</option>
                                            <?php foreach ($pelangganList as $p): ?>
                                                    <option value="<?= $p['id_pelanggan'] ?>"><?= htmlspecialchars($p['nama']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="divider"></div>
                        <?php endif; ?>

                        <div class="section-label"><i class="fas fa-laptop-medical"></i> Data Perangkat & Fisik</div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Nama Perangkat <span class="required">*</span></label>
                                <div class="input-group">
                                    <i class="fas fa-mobile-alt input-icon"></i>
                                    <input type="text" class="form-control" name="nama_perangkat" placeholder="Contoh: iPhone 11 Pro, Laptop Acer Nitro 5" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Jenis Perangkat <span class="required">*</span></label>
                                <div class="input-group">
                                    <i class="fas fa-shapes input-icon"></i>
                                    <input type="text" class="form-control" name="jenis_perangkat" placeholder="Contoh: Handphone, Laptop, TV" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Merek / Brand</label>
                                <div class="input-group">
                                    <i class="fas fa-tag input-icon"></i>
                                    <input type="text" class="form-control" name="merek" placeholder="Contoh: Apple, Samsung, ASUS">
                                </div>
                                <small style="color: #999; font-size: 0.8rem; margin-top: 4px; display:block;">Kosongkan jika tidak ada merek spesifik.</small>
                            </div>
                            <div class="form-group">
                                <label>Nomor Seri (SN)</label>
                                <div class="input-group">
                                    <i class="fas fa-barcode input-icon"></i>
                                    <input type="text" class="form-control" name="nomor_seri" placeholder="Contoh: SN12345678XX">
                                </div>
                            </div>
                        </div>

                        <div class="divider"></div>

                        <div class="section-label"><i class="fas fa-tools"></i> Keluhan & Masalah</div>
                        <div class="form-group">
                            <label>Deskripsi Kerusakan <span class="required">*</span></label>
                            <textarea class="form-control" name="keluhan" rows="4" placeholder="Jelaskan secara detail kerusakan yang dialami perangkat..." required></textarea>
                        </div>

                        <?php if ($role === 'admin'): ?>
                                <div class="divider"></div>
                                <div class="admin-panel">
                                    <div class="section-label" style="color: #495057;"><i class="fas fa-user-shield"></i> Data Internal (Admin)</div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label>Tunjuk Teknisi</label>
                                            <div class="input-group">
                                                <i class="fas fa-user-cog input-icon"></i>
                                                <select name="id_teknisi" class="form-control">
                                                    <option value="">-- Nanti Saja --</option>
                                                    <?php foreach ($teknisiList as $t): ?>
                                                            <option value="<?= $t['id_teknisi'] ?>"><?= htmlspecialchars($t['nama_teknisi']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label>Biaya Awal / DP (Rp)</label>
                                            <div class="input-group">
                                                <i class="fas fa-wallet input-icon"></i>
                                                <input type="number" class="form-control" name="biaya_service" value="0">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        <?php endif; ?>

                    </div> <div class="form-actions">
                        <a href="list.php" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane" style="margin-right: 8px;"></i> Kirim Permintaan Service
                        </button>
                    </div>

                </div> </form>
        </div> </div> </body>
</html>