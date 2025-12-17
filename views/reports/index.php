<?php
// views/reports/index.php
// Ambil semua variabel dari controller
$servicesByStatus = $servicesByStatus ?? [];
$revenueByTech = $revenueByTech ?? [];
$startDate = $startDate ?? '';
$endDate = $endDate ?? '';
$refreshMessage = $refreshMessage ?? '';
$msgClass = $msgClass ?? '';
$mvData = $mvData ?? [];
$viewData = $viewData ?? [];
$explainResults = $explainResults ?? ['with_index' => 'No Data'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan & Statistik</title>
    <link rel="stylesheet" href="../public/css/style.css"> 
    
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Arial, sans-serif; margin: 0; }
        .container { max-width: 1200px; margin: 20px auto; padding: 20px; background: #fff; border-radius: 8px; }
        .page-header h1 { color: #333; font-size: 24px; border-left: 5px solid #007bff; padding-left: 15px; }
        
        .card { background: #fff; border: 1px solid #e0e0e0; border-radius: 8px; padding: 20px; margin-bottom: 20px; }
        .filter-form { display: flex; gap: 15px; align-items: flex-end; margin-bottom: 10px; }
        .form-group { display: flex; flex-direction: column; gap: 5px; }
        .btn-filter { background: #007bff; color: #fff; border: none; padding: 8px 20px; border-radius: 4px; cursor: pointer; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background: #f8f9fa; padding: 12px; border-bottom: 2px solid #dee2e6; text-align: left; }
        td { padding: 12px; border-bottom: 1px solid #eee; }
        
        .btn-export { background: #28a745; color: #fff; padding: 5px 10px; border-radius: 4px; font-size: 12px; text-decoration: none; float: right; }
        .btn-refresh { background-color: #28a745; color: white; padding: 8px 15px; border-radius: 4px; text-decoration: none; font-size: 14px; font-weight: bold; transition: 0.3s; }
        .btn-refresh:hover { background-color: #218838; }
        
        .report-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        @media (max-width: 768px) { .report-grid { grid-template-columns: 1fr; } }

        /* Styling untuk alert */
        .alert { padding: 15px; border-radius: 4px; margin-bottom: 20px; font-weight: 500; }
        .alert.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        /* Styling untuk code block */
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
            <h1>📊 Laporan & Analisis Sistem</h1>
        </div>

        <!-- Alert untuk refresh MV -->
        <?php if ($refreshMessage): ?>
            <div class="alert <?= $msgClass ?>">
                <?= $refreshMessage ?>
            </div>
        <?php endif; ?>

        <!-- Form Filter Tanggal (lama) -->
        <div class="card">
            <form method="GET" action="" class="filter-form">
                <div class="form-group">
                    <label>Dari Tanggal:</label>
                    <input type="date" name="start_date" value="<?= htmlspecialchars($startDate) ?>">
                </div>
                <div class="form-group">
                    <label>Sampai Tanggal:</label>
                    <input type="date" name="end_date" value="<?= htmlspecialchars($endDate) ?>">
                </div>
                <button type="submit" class="btn-filter">Tampilkan Data</button>
            </form>
        </div>

        <!-- Grid untuk Laporan Statistik & Performa (lama) -->
        <div class="report-grid">
            <div class="card">
                <h2>📋 Statistik Status Service
                    <a href="?export=services_by_status&start_date=<?= $startDate ?>&end_date=<?= $endDate ?>" class="btn-export">📥 Export</a>
                </h2>
                <table>
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th style="text-align: right;">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($servicesByStatus)): ?>
                            <tr><td colspan="2" style="text-align:center;">Tidak ada data.</td></tr>
                        <?php else: ?>
                            <?php foreach ($servicesByStatus as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['nama_status']) ?></td>
                                    <td style="text-align: right;"><b><?= $row['jumlah_servis'] ?></b></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="card">
                <h2>👨‍🔧 Performa Teknisi
                    <a href="?export=revenue_by_tech&start_date=<?= $startDate ?>&end_date=<?= $endDate ?>" class="btn-export">📥 Export</a>
                </h2>
                <table>
                    <thead>
                        <tr>
                            <th>Nama Teknisi</th>
                            <th style="text-align: center;">Total Unit</th>
                            <th style="text-align: right;">Estimasi Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($revenueByTech)): ?>
                            <tr><td colspan="3" style="text-align:center;">Data teknisi tidak ditemukan.</td></tr>
                        <?php else: ?>
                            <?php foreach ($revenueByTech as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['nama_status'] ?? $row['nama_teknisi']) ?></td>
                                    <td style="text-align: center;"><?= $row['jumlah_pembayaran'] ?></td>
                                    <td style="text-align: right; color: #28a745; font-weight: bold;">
                                        Rp <?= number_format($row['total_pendapatan'], 0, ',', '.') ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div> 

        <!-- Materialized View Card (baru) -->
        <div class="card" style="border-top-color: #28a745;">
            <h2>
                📈 Pendapatan Bulanan (Dari Materialized View)
                <a href="?refresh_mv=1&start_date=<?= $startDate ?>&end_date=<?= $endDate ?>" class="btn-refresh">🔄 Refresh Data MV</a>
            </h2>
            <p style="font-size: 14px; color: #666; margin-bottom: 15px;">
                Data ini diambil dari <code>mv_pendapatan_bulanan</code> untuk efisiensi query. Klik "Refresh" untuk sinkronisasi.
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
                        <tr><td colspan="4" style="text-align:center; padding: 20px;">Belum ada data MV (Coba klik Refresh).</td></tr>
                    <?php else: ?>
                        <?php foreach ($mvData as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['year']) ?></td>
                                <td><?= htmlspecialchars($row['month']) ?></td>
                                <td style="font-weight: bold; color: #28a745;">Rp <?= number_format($row['total_revenue'], 0, ',', '.') ?></td>
                                <td><?= htmlspecialchars($row['total_services']) ?> Unit</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Complex View Card (baru) -->
        <div class="card" style="border-top-color: #17a2b8;">
            <h2>📋 Ringkasan Service Terbaru (Dari Complex View)</h2>
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
                                <td><?= htmlspecialchars($row['nama_perangkat']) ?> (<?= htmlspecialchars($row['merek']) ?>)</td>
                                <td><span style="padding: 3px 8px; background: #eee; border-radius: 4px; font-size: 12px; font-weight: bold;"><?= htmlspecialchars($row['nama_status']) ?></span></td>
                                <td><?= htmlspecialchars($row['nama_teknisi'] ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- EXPLAIN ANALYZE Card (baru) -->
        <div class="card" style="border-top-color: #ffc107;">
            <h2>⚡ Analisis Performa Query (EXPLAIN ANALYZE)</h2>
            <p style="font-size: 14px; color: #666; margin-bottom: 15px;">
                Analisa query pencarian service yang sudah selesai. Perhatikan <strong>Execution Time</strong> dan penggunaan <strong>Index</strong>.
            </p>
            
            <div class="code-block">
<?= htmlspecialchars($explainResults['with_index']) ?>
            </div>
        </div>

    </div>
</body>
</html>