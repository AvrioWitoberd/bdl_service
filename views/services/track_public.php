<?php
// views/services/track_public.php
require_once '../../config/database.php';

$message = '';
$service = null;
$error = false;

if (isset($_GET['id_service'])) {
    $id_service = (int)$_GET['id_service'];

    // Query untuk mengambil detail service (Logika Tetap)
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
    $service = $stmt->fetch();

    if (!$service) {
        $message = "ID Service tidak ditemukan.";
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
        /* --- ANIMASI & RESET --- */
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

        /* --- BODY STYLE --- */
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 2rem 1rem;
            /* Gradient Background Konsisten */
            background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5ab);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center; /* Center content vertically */
            color: #333;
        }

        /* --- CONTAINER UTAMA --- */
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

        /* --- TYPOGRAPHY --- */
        h2 {
            color: #1e3c72;
            text-align: center;
            margin-top: 0;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        p.subtitle {
            text-align: center;
            color: #64748b;
            margin-bottom: 2rem;
            font-size: 0.95rem;
        }

        /* --- FORM PENCARIAN --- */
        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #475569;
            font-size: 0.9rem;
        }

        .input-wrapper {
            display: flex;
            gap: 10px;
        }

        input[type="number"] {
            flex-grow: 1;
            padding: 12px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8fafc;
        }

        input[type="number"]:focus {
            border-color: #23a6d5;
            background: #fff;
            outline: none;
            box-shadow: 0 0 0 3px rgba(35, 166, 213, 0.1);
        }

        button {
            background: linear-gradient(to right, #23a6d5, #23d5ab);
            color: white;
            padding: 0 2rem;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            font-family: 'Poppins', sans-serif;
            white-space: nowrap;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(35, 213, 171, 0.4);
        }

        /* --- HASIL PENCARIAN (TABLE) --- */
        .service-details {
            margin-top: 2.5rem;
            background: #fff;
            border-radius: 15px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            animation: fadeIn 0.6s ease;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .service-details h3 {
            background: #f1f5f9;
            margin: 0;
            padding: 1rem 1.5rem;
            font-size: 1.1rem;
            color: #334155;
            border-bottom: 1px solid #e2e8f0;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 1rem 1.5rem;
            text-align: left;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.95rem;
        }

        th {
            background-color: #f8fafc;
            color: #64748b;
            font-weight: 600;
            width: 40%;
        }

        td {
            color: #334155;
            font-weight: 500;
        }

        tr:last-child th, tr:last-child td {
            border-bottom: none;
        }

        /* --- STATUS BADGES --- */
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-completed {
            background-color: #dcfce7;
            color: #166534; /* Hijau Tua */
        }

        .status-pending {
            background-color: #fef9c3;
            color: #854d0e; /* Kuning/Coklat */
        }

        .status-active {
            background-color: #dbeafe;
            color: #1e40af; /* Biru */
        }

        /* --- PESAN & ERROR --- */
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

        /* --- LINKS --- */
        .back-link {
            display: inline-block;
            margin-top: 2rem;
            color: #64748b;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }

        .back-link:hover {
            color: #1e3c72;
            text-decoration: underline;
        }

        /* --- RESPONSIVE --- */
        @media (max-width: 600px) {
            .input-wrapper {
                flex-direction: column;
            }
            
            button {
                padding: 12px;
                width: 100%;
            }

            th, td {
                display: block;
                width: 100%;
            }
            
            th {
                padding-bottom: 0.2rem;
                background: transparent;
                color: #94a3b8;
                font-size: 0.85rem;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            
            td {
                padding-top: 0;
                padding-bottom: 1.2rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>🔍 Track Status Service</h2>
        <p class="subtitle">Masukkan ID Service Anda untuk memantau perkembangan perbaikan secara Real-Time.</p>

        <form method="GET">
            <div class="form-group">
                <label for="id_service">Nomor ID Service</label>
                <div class="input-wrapper">
                    <input type="number" name="id_service" id="id_service" placeholder="Contoh: 1024" value="<?= htmlspecialchars($_GET['id_service'] ?? '') ?>" required>
                    <button type="submit">Cek Status</button>
                </div>
            </div>
        </form>

        <?php if ($message): ?>
            <div class="message-box <?= $error ? 'error' : 'info' ?>">
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
                        <th>Kondisi Awal</th>
                        <td><?= htmlspecialchars($service['kondisi_masuk']) ?></td>
                    </tr>
                    <tr>
                        <th>Keluhan Utama</th>
                        <td><?= htmlspecialchars($service['keluhan']) ?></td>
                    </tr>
                    <tr>
                        <th>Status Terkini</th>
                        <td>
                            <span class="status-badge 
                                <?php 
                                    $status_nama = strtolower($service['nama_status']);
                                    if (strpos($status_nama, 'selesai') !== false || strpos($status_nama, 'completed') !== false) {
                                        echo 'status-completed';
                                    } elseif (strpos($status_nama, 'pending') !== false || strpos($status_nama, 'menunggu') !== false) {
                                        echo 'status-pending';
                                    } else {
                                        echo 'status-active';
                                    }
                                ?>">
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
                        <td style="font-weight: bold; color: #1e3c72;">Rp <?= number_format($service['biaya_akhir'] ?? 0, 0, ',', '.') ?></td>
                    </tr>
                </table>
            </div>
        <?php endif; ?>

        <div style="text-align: center;">
            <a href="../../public/index.php" class="back-link">← Kembali ke Beranda Utama</a>
        </div>
    </div>
</body>
</html>