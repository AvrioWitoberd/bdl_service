<?php
// views/reports/index.php

// Pastikan variabel report tersedia (jika file ini diakses langsung tanpa controller, cegah error)
$servicesByStatus = $servicesByStatus ?? [];
$revenueByTech = $revenueByTech ?? [];
$startDate = $startDate ?? '';
$endDate = $endDate ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan & Statistik</title>
    <link rel="stylesheet" href="../public/css/style.css"> 
    
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .page-header h1 { margin: 0; color: #333; font-size: 28px; }

        /* Card Style */
        .card { background: white; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); padding: 20px; margin-bottom: 30px; }
        .card h2 { margin-top: 0; font-size: 18px; color: #555; border-bottom: 2px solid #f0f0f0; padding-bottom: 10px; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; }

        /* Filter Form */
        .filter-form { display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap; }
        .form-group { display: flex; flex-direction: column; }
        .form-group label { font-size: 13px; font-weight: bold; color: #666; margin-bottom: 5px; }
        .form-group input { padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; }
        .btn-filter { background-color: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-weight: 600; }
        .btn-filter:hover { background-color: #0056b3; }

        /* Table Styling */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { text-align: left; padding: 12px; background-color: #f8f9fa; color: #444; border-bottom: 2px solid #dee2e6; }
        td { padding: 12px; border-bottom: 1px solid #eee; color: #333; }
        tr:last-child td { border-bottom: none; }

        /* Export Button */
        .btn-export { 
            background-color: #28a745; color: white; text-decoration: none; 
            padding: 6px 12px; border-radius: 4px; font-size: 13px; font-weight: bold; 
        }
        .btn-export:hover { background-color: #218838; }

        /* Grid Layout untuk 2 Tabel */
        .report-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        @media (max-width: 768px) { .report-grid { grid-template-columns: 1fr; } }
    </style>
</head>

<body>
    <?php include __DIR__ . '/../partials/header.php'; ?>
    
    <div class="container">
        
        <div class="page-header">
            <h1>Laporan & Statistik</h1>
        </div>

        <div class="card">
            <form method="GET" action="" class="filter-form">
                <div class="form-group">
                    <label for="start_date">Dari Tanggal:</label>
                    <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($startDate); ?>">
                </div>
                
                <div class="form-group">
                    <label for="end_date">Sampai Tanggal:</label>
                    <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($endDate); ?>">
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn-filter">Tampilkan Data</button>
                </div>
            </form>
        </div>

        <div class="report-grid">
            
            <div class="card">
                <h2>
                    Statistik Status Service
                    <a href="?export=services_by_status&start_date=<?= urlencode($startDate) ?>&end_date=<?= urlencode($endDate) ?>" class="btn-export">
                        📥 Export CSV
                    </a>
                </h2>
                
                <table>
                    <thead>
                        <tr>
                            <th>Status Pengerjaan</th>
                            <th style="text-align: right;">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($servicesByStatus)): ?>
                            <tr><td colspan="2" style="text-align:center; padding: 20px;">Belum ada data pada periode ini.</td></tr>
                        <?php else: ?>
                            <?php foreach ($servicesByStatus as $row): ?>
                                <tr>
                                    <td>
                                        <span style="font-weight: 500;"><?php echo htmlspecialchars($row['nama_status']); ?></span>
                                    </td>
                                    <td style="text-align: right; font-weight: bold;">
                                        <?php echo $row['jumlah_servis']; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="card">
                <h2>
                    Pendapatan Teknisi
                    <a href="?export=revenue_by_tech&start_date=<?= urlencode($startDate) ?>&end_date=<?= urlencode($endDate) ?>" class="btn-export">
                        📥 Export CSV
                    </a>
                </h2>
                
                <table>
                    <thead>
                        <tr>
                            <th>Nama Teknisi</th>
                            <th style="text-align: right;">Total Pendapatan</th>
                            <th style="text-align: center;">Jml Service</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($revenueByTech)): ?>
                            <tr><td colspan="3" style="text-align:center; padding: 20px;">Belum ada data pendapatan.</td></tr>
                        <?php else: ?>
                            <?php foreach ($revenueByTech as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['nama_teknisi']); ?></td>
                                    
                                    <td style="text-align: right; color: #28a745; font-weight: bold;">
                                        Rp <?php echo number_format($row['total_pendapatan'], 0, ',', '.'); ?>
                                    </td>
                                    
                                    <td style="text-align: center;">
                                        <?php echo $row['jumlah_pembayaran']; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div> </div>

    <?php include __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>