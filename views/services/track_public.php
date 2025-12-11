<?php
// views/services/track_public.php

// Pastikan path benar & load DB sekali saja
if (!isset($pdo)) {
    $pdo = require_once __DIR__ . '/../../config/database.php';
}

$message = '';
$service = null;
$error = false;

if (isset($_GET['id_service'])) {
    $id_service = (int)$_GET['id_service'];

    try {
        // PERBAIKAN SQL:
        // 1. Hapus d.model
        // 2. Hapus d.kondisi_masuk (karena di form create tadi kita tidak input ini)
        // 3. Tambahkan d.nama_perangkat
        $stmt = $pdo->prepare("
            SELECT 
                s.id_service, 
                s.keluhan, 
                s.tanggal_masuk, 
                s.tanggal_selesai, 
                s.biaya_service,
                sp.nama_status, 
                p.nama as nama_pelanggan, 
                d.nama_perangkat,     -- Ganti model jadi nama_perangkat
                d.jenis_perangkat, 
                d.merek, 
                t.nama_teknisi as teknisi_penangan
            FROM service s
            JOIN status_perbaikan sp ON s.id_status = sp.id_status
            JOIN perangkat d ON s.id_perangkat = d.id_perangkat
            JOIN pelanggan p ON s.id_pelanggan = p.id_pelanggan
            LEFT JOIN teknisi t ON s.id_teknisi = t.id_teknisi
            WHERE s.id_service = ?
        ");
        $stmt->execute([$id_service]);
        $service = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$service) {
            $message = "ID Service tidak ditemukan.";
            $error = true;
        }

    } catch (PDOException $e) {
        $message = "Terjadi kesalahan database: " . $e->getMessage();
        $error = true;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Service Public</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../public/css/style.css">
    
    <style>
        * { box-sizing: border-box; }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        @keyframes slideInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 2rem 1rem;
            background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5ab);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #333;
        }

        .container {
            width: 100%;
            max-width: 800px;
            background-color: rgba(255, 255, 255, 0.95);
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            backdrop-filter: blur(10px);
            animation: slideInUp 0.8s cubic-bezier(0.165, 0.84, 0.44, 1);
        }

        h2 {
            color: #1e3c72;
            text-align: center;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        p.subtitle {
            text-align: center;
            color: #64748b;
            margin-bottom: 2rem;
            font-size: 0.95rem;
        }

        .form-group { margin-bottom: 1.5rem; }
        .input-wrapper { display: flex; gap: 10px; }

        input[type="number"] {
            flex-grow: 1;
            padding: 12px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 1rem;
        }

        button {
            background: linear-gradient(to right, #23a6d5, #23d5ab);
            color: white;
            padding: 0 2rem;
            border-radius: 10px;
            font-weight: 600;
            border: none;
            cursor: pointer;
        }

        .service-details {
            margin-top: 2.5rem;
            background: #fff;
            border-radius: 15px;
            animation: fadeIn 0.6s ease;
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }

        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 1rem 1.5rem; border-bottom: 1px solid #f1f5f9; text-align: left;}

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .status-completed { background: #dcfce7; color: #166534; }
        .status-pending   { background: #fef9c3; color: #854d0e; }
        .status-active    { background: #dbeafe; color: #1e40af; }

        .message-box {
            margin-top: 1.5rem;
            padding: 1rem;
            border-radius: 10px;
            text-align: center;
            font-weight: 500;
        }

        .error {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
    </style>
</head>
<body>

<div class="container">

    <h2>🔍 Track Status Service</h2>
    <p class="subtitle">Masukkan ID Service Anda untuk memantau perkembangan perbaikan.</p>

    <form method="GET">
        <div class="form-group">
            <div class="input-wrapper">
                <input type="number" name="id_service" id="id_service"
                       placeholder="Contoh: 1024"
                       value="<?= htmlspecialchars($_GET['id_service'] ?? '') ?>"
                       required>
                <button type="submit">Cek Status</button>
            </div>
        </div>
    </form>

    <?php if ($message): ?>
        <div class="message-box error">
            ⚠️ <?= htmlspecialchars($message) ?>
        </div>

    <?php elseif ($service): ?>
        <div class="service-details">
            <h3 style="padding: 1.5rem 1.5rem 0; margin:0;">📋 Detail Service #<?= htmlspecialchars($service['id_service']) ?></h3>
            <table>
                <tr>
                    <th>Nama Pelanggan</th>
                    <td><?= htmlspecialchars($service['nama_pelanggan']) ?></td>
                </tr>

                <tr>
                    <th>Perangkat</th>
                    <td>
                        <strong><?= htmlspecialchars($service['nama_perangkat']) ?></strong><br>
                        <span style="color: #666; font-size: 0.9em;">
                            <?= htmlspecialchars($service['jenis_perangkat']) ?> - <?= htmlspecialchars($service['merek']) ?>
                        </span>
                    </td>
                </tr>

                <tr>
                    <th>Keluhan</th>
                    <td><?= htmlspecialchars($service['keluhan']) ?></td>
                </tr>

                <tr>
                    <th>Status</th>
                    <td>
                        <?php 
                            $status_nama = strtolower($service['nama_status']);
                            $badge = "status-active";
                            if (strpos($status_nama, "selesai") !== false || strpos($status_nama, "completed") !== false) $badge = "status-completed";
                            elseif (strpos($status_nama, "pending") !== false || strpos($status_nama, "menunggu") !== false) $badge = "status-pending";
                        ?>
                        <span class="status-badge <?= $badge ?>">
                            <?= htmlspecialchars($service['nama_status']) ?>
                        </span>
                    </td>
                </tr>

                <tr>
                    <th>Teknisi</th>
                    <td><?= htmlspecialchars($service['teknisi_penangan'] ?? 'Sedang dijadwalkan') ?></td>
                </tr>

                <tr>
                    <th>Tanggal Masuk</th>
                    <td><?= date('d/m/Y', strtotime($service['tanggal_masuk'])) ?></td>
                </tr>

                <tr>
                    <th>Tanggal Selesai</th>
                    <td><?= $service['tanggal_selesai'] ? date('d/m/Y', strtotime($service['tanggal_selesai'])) : '-' ?></td>
                </tr>

                <tr>
                    <th>Estimasi Biaya</th>
                    <td style="font-size: 1.1em; color: #28a745; font-weight: bold;">
                        Rp <?= number_format($service['biaya_service'] ?? 0, 0, ',', '.') ?>
                    </td>
                </tr>
            </table>
        </div>
    <?php endif; ?>

    <div style="text-align: center; margin-top: 20px;">
        <a href="../../index.php" style="text-decoration: none; color: #333;">← Kembali ke Beranda</a>
    </div>

</div>
</body>
</html>