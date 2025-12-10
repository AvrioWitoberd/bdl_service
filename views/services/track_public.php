<?php
// views/services/track_public.php

// Pastikan path benar
require_once __DIR__ . '/../../config/database.php';

$message = '';
$service = null;
$error = false;

// Pastikan $pdo tersedia
if (!isset($pdo) || $pdo === null) {
    die("❌ ERROR: Koneksi database tidak tersedia. Pastikan database.php membuat variabel \$pdo.");
}

if (isset($_GET['id_service'])) {
    $id_service = (int)$_GET['id_service'];

    try {
        // Query untuk mengambil detail service
        $stmt = $pdo->prepare("
            SELECT 
                s.id_service, 
                s.keluhan, 
                s.tanggal_masuk, 
                s.tanggal_selesai, 
                s.biaya_akhir,
                sp.nama_status, 
                p.nama as nama_pelanggan, 
                d.jenis_perangkat, 
                d.merek, 
                d.model,
                d.kondisi_masuk,
                t.nama_teknisi as teknisi_penangan
            FROM service s
            JOIN status_perbaikan sp ON s.id_status = sp.id_status
            JOIN perangkat d ON s.id_perangkat = d.id_perangkat
            JOIN pelanggan p ON d.id_pelanggan = p.id_pelanggan
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
    <title>Track Service - Service Elektronik ABC</title>
    
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

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
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
        label { font-weight: 600; color: #475569; }

        .input-wrapper { display: flex; gap: 10px; }

        input[type="number"] {
            flex-grow: 1;
            padding: 12px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 1rem;
        }

        input[type="number"]:focus {
            border-color: #23a6d5;
            box-shadow: 0 0 0 3px rgba(35, 166, 213, 0.1);
            outline: none;
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
        }

        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 1rem 1.5rem; border-bottom: 1px solid #f1f5f9; }

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
            animation: shake 0.5s ease;
        }
    </style>
</head>
<body>
<div class="container">

    <h2>🔍 Track Status Service</h2>
    <p class="subtitle">Masukkan ID Service Anda untuk memantau perkembangan perbaikan.</p>

    <form method="GET">
        <div class="form-group">
            <label for="id_service">Nomor ID Service</label>
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
            <h3>📋 Detail Service #<?= htmlspecialchars($service['id_service']) ?></h3>
            <table>
                <tr>
                    <th>Nama Pelanggan</th>
                    <td><?= htmlspecialchars($service['nama_pelanggan']) ?></td>
                </tr>

                <tr>
                    <th>Perangkat</th>
                    <td>
                        <strong><?= htmlspecialchars($service['jenis_perangkat']) ?></strong>
                        <?= htmlspecialchars($service['merek']) ?> - <?= htmlspecialchars($service['model']) ?>
                    </td>
                </tr>

                <tr>
                    <th>Kondisi Masuk</th>
                    <td><?= htmlspecialchars($service['kondisi_masuk']) ?></td>
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
                            if (str_contains($status_nama, "selesai") || str_contains($status_nama, "completed")) $badge = "status-completed";
                            elseif (str_contains($status_nama, "pending") || str_contains($status_nama, "menunggu")) $badge = "status-pending";
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
                    <td><?= htmlspecialchars($service['tanggal_masuk']) ?></td>
                </tr>

                <tr>
                    <th>Tanggal Selesai</th>
                    <td><?= htmlspecialchars($service['tanggal_selesai'] ?? '-') ?></td>
                </tr>

                <tr>
                    <th>Estimasi Biaya</th>
                    <td><strong>Rp <?= number_format($service['biaya_akhir'] ?? 0, 0, ',', '.') ?></strong></td>
                </tr>
            </table>
        </div>
    <?php endif; ?>

    <div style="text-align: center; margin-top: 20px;">
        <a href="../../public/index.php">← Kembali ke Beranda</a>
    </div>

</div>
</body>
</html>
