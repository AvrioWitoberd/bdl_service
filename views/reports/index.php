<?php
// views/reports/index.php
$servicesByStatus = $servicesByStatus ?? [];
$revenueByTech = $revenueByTech ?? [];
$startDate = $startDate ?? '';
$endDate = $endDate ?? '';
$refreshMessage = $refreshMessage ?? '';
$msgClass = $msgClass ?? '';
$mvData = $mvData ?? [];
$viewData = $viewData ?? [];
$explainResults = $explainResults ?? ['with_index' => 'No Data'];

// Persiapan data untuk Chart.js
$statusLabels = json_encode(array_column($servicesByStatus, 'nama_status'));
$statusCounts = json_encode(array_column($servicesByStatus, 'jumlah_servis'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Analisis Sistem</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --success: #2ecc71;
            --info: #4895ef;
            --warning: #f1c40f;
            --danger: #e74c3c;
            --light: #f8f9fa;
            --dark: #2d3436;
        }

        body { background-color: #f0f2f5; font-family: 'Inter', 'Segoe UI', sans-serif; margin: 0; color: var(--dark); }
        .container { max-width: 1300px; margin: 0 auto; padding: 20px; }
        
        /* Header & Filter */
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .page-header h1 { font-size: 24px; margin: 0; display: flex; align-items: center; gap: 10px; }
        
        .filter-card { background: white; padding: 15px 20px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 25px; }
        .filter-form { display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap; }
        .form-group { display: flex; flex-direction: column; gap: 5px; }
        .form-group label { font-size: 12px; font-weight: 600; color: #666; }
        input[type="date"] { padding: 8px; border: 1px solid #ddd; border-radius: 6px; }
        
        .btn { padding: 10px 18px; border-radius: 8px; border: none; font-weight: 600; cursor: pointer; transition: 0.3s; text-decoration: none; font-size: 14px; }
        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { background: var(--secondary); }
        .btn-outline-success { border: 1.5px solid var(--success); color: var(--success); background: transparent; }
        .btn-outline-success:hover { background: var(--success); color: white; }

        /* Stats Grid */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 25px; }
        .stat-card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); border-bottom: 4px solid var(--primary); }
        .stat-card h3 { font-size: 13px; color: #7f8c8d; margin: 0; text-transform: uppercase; letter-spacing: 0.5px; }
        .stat-card .value { font-size: 28px; font-weight: 800; margin: 10px 0 0 0; color: var(--dark); }

        /* Layout Main */
        .main-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 25px; }
        .card { background: white; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); overflow: hidden; height: 100%; }
        .card-header { padding: 15px 20px; border-bottom: 1px solid #f0f0f0; display: flex; justify-content: space-between; align-items: center; background: #fff; }
        .card-header h2 { font-size: 16px; margin: 0; color: var(--dark); }
        .card-body { padding: 20px; }

        /* Tables */
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; font-size: 13px; color: #7f8c8d; padding: 12px 10px; border-bottom: 2px solid #f0f0f0; }
        td { padding: 12px 10px; font-size: 14px; border-bottom: 1px solid #f9f9f9; }
        .text-right { text-align: right; }
        .badge { padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: bold; background: #eee; }

        /* Analyze Section */
        .code-block { background: #1e1e1e; color: #d4d4d4; padding: 15px; border-radius: 8px; font-family: 'Consolas', monospace; font-size: 12px; overflow-x: auto; line-height: 1.5; }

        @media (max-width: 992px) { .main-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <div class="container">
        <div class="page-header">
            <h1>📊 Dashboard Analisis</h1>
            <div class="header-actions">
                <a href="?refresh_mv=1" class="btn btn-outline-success">🔄 Refresh Materialized View</a>
            </div>
        </div>

        <?php if ($refreshMessage): ?>
            <div style="padding: 15px; background: #d4edda; color: #155724; border-radius: 8px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
                <?= $refreshMessage ?>
            </div>
        <?php endif; ?>

        <div class="filter-card">
            <form method="GET" class="filter-form">
                <div class="form-group">
                    <label>DARI TANGGAL</label>
                    <input type="date" name="start_date" value="<?= htmlspecialchars($startDate) ?>">
                </div>
                <div class="form-group">
                    <label>SAMPAI TANGGAL</label>
                    <input type="date" name="end_date" value="<?= htmlspecialchars($endDate) ?>">
                </div>
                <button type="submit" class="btn btn-primary">Tampilkan Data</button>
            </form>
        </div>

        <div class="stats-grid">
            <?php foreach (array_slice($servicesByStatus, 0, 4) as $row): ?>
                <div class="stat-card">
                    <h3><?= htmlspecialchars($row['nama_status']) ?></h3>
                    <p class="value"><?= $row['jumlah_servis'] ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="main-grid">
            <div class="card">
                <div class="card-header">
                    <h2>📈 Distribusi Status Service</h2>
                </div>
                <div class="card-body">
                    <canvas id="statusChart" height="200"></canvas>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2>👨‍🔧 Performa & Pendapatan Teknisi</h2>
                    <a href="?export=revenue_by_tech" class="btn" style="font-size:12px; background:#f0f2f5;">📥 CSV</a>
                </div>
                <div class="card-body" style="max-height: 350px; overflow-y: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Teknisi</th>
                                <th class="text-right">Unit</th>
                                <th class="text-right">Total Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($revenueByTech as $row): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($row['nama_teknisi']) ?></strong></td>
                                    <td class="text-right"><?= $row['jumlah_pembayaran'] ?></td>
                                    <td class="text-right" style="color:var(--success); font-weight:bold;">
                                        Rp <?= number_format($row['total_pendapatan'], 0, ',', '.') ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card" style="margin-bottom: 25px;">
            <div class="card-header">
                <h2>💰 Rekapitulasi Pendapatan Bulanan (Materialized View)</h2>
            </div>
            <div class="card-body">
                <table>
                    <thead>
                        <tr>
                            <th>Tahun</th>
                            <th>Bulan (Angka)</th>
                            <th>Total Unit Selesai</th>
                            <th class="text-right">Total Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($mvData)): ?>
                            <tr><td colspan="4" style="text-align:center;">Data tidak ditemukan. Silakan klik Refresh.</td></tr>
                        <?php else: ?>
                            <?php foreach ($mvData as $row): ?>
                                <tr>
                                    <td><?= $row['year'] ?></td>
                                    <td><span class="badge"><?= $row['month'] ?></span></td>
                                    <td><?= $row['total_services'] ?> Unit</td>
                                    <td class="text-right" style="font-weight:bold;">Rp <?= number_format($row['total_revenue'], 0, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card" style="margin-bottom: 25px;">
            <div class="card-header">
                <h2>📋 Log Ringkasan Service (Complex View)</h2>
            </div>
            <div class="card-body">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Pelanggan / Perangkat</th>
                            <th>Status Terakhir</th>
                            <th>Teknisi Penanggung Jawab</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($viewData as $row): ?>
                            <tr>
                                <td>#<?= $row['id_service'] ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($row['nama_pelanggan']) ?></strong><br>
                                    <small style="color:#777"><?= htmlspecialchars($row['nama_perangkat']) ?> (<?= htmlspecialchars($row['merek']) ?>)</small>
                                </td>
                                <td><span class="badge"><?= htmlspecialchars($row['nama_status']) ?></span></td>
                                <td><?= htmlspecialchars($row['nama_teknisi'] ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header" style="background: #fff3cd;">
                <h2>⚡ Analisis Performa Query (EXPLAIN ANALYZE)</h2>
            </div>
            <div class="card-body">
                <p style="font-size: 13px; color: #856404; margin-bottom: 10px;">
                    Analisa internal database untuk mengukur kecepatan eksekusi query JOIN antar 5 tabel.
                </p>
                <div class="code-block"><?= htmlspecialchars($explainResults['with_index']) ?></div>
            </div>
        </div>
    </div>

    <script>
        // Chart Configuration
        const ctx = document.getElementById('statusChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= $statusLabels ?>,
                datasets: [{
                    label: 'Jumlah Unit',
                    data: <?= $statusCounts ?>,
                    backgroundColor: '#4361ee',
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true, grid: { display: false } },
                    x: { grid: { display: false } }
                }
            }
        });
    </script>
</body>
</html>