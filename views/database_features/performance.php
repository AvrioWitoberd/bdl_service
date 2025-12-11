<?php
// views/database_features/performance.php

// Pastikan variabel tersedia
$mvData = $mvData ?? [];
$viewData = $viewData ?? [];
$explainResults = $explainResults ?? ['with_index' => 'No Data'];
$refreshMessage = $refreshMessage ?? '';
$msgClass = $msgClass ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Fitur & Performa Database</title>
    <link rel="stylesheet" href="../public/css/style.css">
    
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        
        .page-header { margin-bottom: 30px; }
        .page-header h1 { margin: 0; color: #333; font-size: 28px; }
        .page-header p { color: #666; margin-top: 5px; }

        /* Card Style */
        .card { background: white; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); padding: 25px; margin-bottom: 30px; border-top: 4px solid #007bff; }
        .card h2 { margin-top: 0; font-size: 20px; color: #444; border-bottom: 2px solid #f0f0f0; padding-bottom: 10px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }

        /* Table Styling */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { text-align: left; padding: 12px; background-color: #f8f9fa; color: #555; border-bottom: 2px solid #dee2e6; font-weight: 600; }
        td { padding: 12px; border-bottom: 1px solid #eee; color: #333; }
        tr:last-child td { border-bottom: none; }

        /* Button & Alert */
        .btn-refresh { background-color: #28a745; color: white; padding: 8px 15px; border-radius: 4px; text-decoration: none; font-size: 14px; font-weight: bold; transition: 0.3s; }
        .btn-refresh:hover { background-color: #218838; }
        
        .alert { padding: 15px; border-radius: 4px; margin-bottom: 20px; font-weight: 500; }
        .alert.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        /* Terminal Code Style */
        .code-block {
            background-color: #2d2d2d;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 6px;
            font-family: 'Consolas', 'Monaco', monospace;
            font-size: 13px;
            white-space: pre-wrap;
            overflow-x: auto;
            border-left: 5px solid #ff79c6;
        }
    </style>
</head>

<body>
    <?php include __DIR__ . '/../partials/header.php'; ?>
    
    <div class="container">
        
        <div class="page-header">
            <h1>🛠️ Fitur & Performa Database</h1>
            <p>Demo penggunaan Materialized View, Complex Query, dan Analisa Performa PostgreSQL.</p>
        </div>

        <?php if ($refreshMessage): ?>
            <div class="alert <?= $msgClass ?>">
                <?= $refreshMessage ?>
            </div>
        <?php endif; ?>

        <div class="card" style="border-top-color: #28a745;">
            <h2>
                📊 Materialized View: Pendapatan Bulanan
                <a href="?refresh_mv=1" class="btn-refresh">🔄 Refresh Data</a>
            </h2>
            <p style="font-size: 14px; color: #666; margin-bottom: 15px;">
                Data diambil dari <code>monthly_revenue_mv</code>. Data ini <strong>statis</strong> dan harus di-refresh manual untuk menghemat beban server.
            </p>
            
            <table>
                <thead>
                    <tr>
                        <th>Tahun</th>
                        <th>Bulan</th>
                        <th>Total Pendapatan</th>
                        <th>Jml Service Selesai</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($mvData)): ?>
                        <tr><td colspan="4" style="text-align:center; padding: 20px;">Belum ada data (Coba klik Refresh).</td></tr>
                    <?php else: ?>
                        <?php foreach ($mvData as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['year']) ?></td>
                                <td><?= htmlspecialchars($row['month']) ?></td>
                                
                                <td style="font-weight: bold; color: #28a745;">
                                    Rp <?= number_format($row['total_revenue'], 0, ',', '.') ?>
                                </td>
                                
                                <td><?= htmlspecialchars($row['total_services']) ?> Unit</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="card" style="border-top-color: #17a2b8;">
            <h2>📑 Complex View: Ringkasan Service Terbaru</h2>
            <p style="font-size: 14px; color: #666; margin-bottom: 15px;">
                Menggabungkan data dari tabel <code>service</code>, <code>pelanggan</code>, <code>perangkat</code>, <code>teknisi</code>, dan <code>status</code>.
            </p>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Pelanggan</th>
                        <th>Perangkat</th>
                        <th>Status</th>
                        <th>Teknisi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($viewData)): ?>
                        <tr><td colspan="5" style="text-align:center;">Tidak ada data service terbaru.</td></tr>
                    <?php else: ?>
                        <?php foreach ($viewData as $row): ?>
                            <tr>
                                <td>#<?= htmlspecialchars($row['id_service']) ?></td>
                                <td><?= htmlspecialchars($row['nama_pelanggan']) ?></td>
                                <td>
                                    <?= htmlspecialchars($row['nama_perangkat']) ?>
                                    <small style="color: #666;">(<?= htmlspecialchars($row['merek']) ?>)</small>
                                </td>
                                <td>
                                    <span style="padding: 3px 8px; background: #eee; border-radius: 4px; font-size: 12px; font-weight: bold;">
                                        <?= htmlspecialchars($row['nama_status']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($row['nama_teknisi'] ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="card" style="border-top-color: #ffc107;">
            <h2>🚀 Query Performance: EXPLAIN ANALYZE</h2>
            <p style="font-size: 14px; color: #666; margin-bottom: 15px;">
                Analisa query pencarian service yang sudah selesai. Perhatikan <strong>Execution Time</strong> dan penggunaan <strong>Index</strong>.
            </p>
            
            <div class="code-block">
<?= htmlspecialchars($explainResults['with_index']) ?>
            </div>
        </div>

    </div>

    <?php include __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>