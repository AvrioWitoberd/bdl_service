<?php
// views/reports/index.php
include __DIR__ . '/../partials/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 animate__animated animate__fadeIn">
    <div>
        <h4 class="m-0 fw-bold text-dark"><i class="fas fa-chart-line me-2 text-primary"></i>Executive Dashboard</h4>
        <p class="text-muted small m-0">Ringkasan performa operasional & audit data sistem.</p>
    </div>
    
    <a href="?refresh_mv=true" class="btn btn-primary btn-sm fw-bold shadow-sm rounded-pill px-3">
        <i class="fas fa-sync-alt me-2"></i> Sinkronisasi Data Keuangan
    </a>
</div>

<?php if (!empty($refreshMessage)): ?>
    <div class="alert alert-<?= $msgClass === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show shadow-sm" role="alert">
        <i class="fas <?= $msgClass === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?> me-2"></i>
        <?= htmlspecialchars($refreshMessage) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm mb-4 animate__animated animate__fadeInUp">
    <div class="card-body p-3 rounded-3 border bg-white">
        <form action="" method="GET" class="row g-2 align-items-center">
            <div class="col-md-auto">
                <span class="fw-bold text-dark small text-uppercase"><i class="fas fa-calendar-alt me-2 text-primary"></i> Periode Laporan:</span>
            </div>
            <div class="col-md-3">
                <input type="date" name="start_date" class="form-control form-control-sm bg-light" value="<?= htmlspecialchars($startDate ?? '') ?>">
            </div>
            <div class="col-md-auto text-muted"><i class="fas fa-arrow-right small"></i></div>
            <div class="col-md-3">
                <input type="date" name="end_date" class="form-control form-control-sm bg-light" value="<?= htmlspecialchars($endDate ?? '') ?>">
            </div>
            <div class="col-md-auto ms-auto">
                <button type="submit" class="btn btn-dark btn-sm px-4 rounded-pill fw-bold">Terapkan Filter</button>
                <a href="ReportController.php" class="btn btn-light text-secondary btn-sm px-3 rounded-pill border">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100 animate__animated animate__fadeInLeft">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="m-0 fw-bold text-dark"><i class="fas fa-clipboard-list me-2 text-primary"></i>Volume Pengerjaan</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="bg-light text-secondary small text-uppercase">
                            <tr>
                                <th class="ps-4">Status Service</th>
                                <th class="text-end pe-4">Jumlah Unit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($servicesByStatus)): ?>
                                <?php foreach ($servicesByStatus as $stat): ?>
                                <tr>
                                    <td class="ps-4 fw-medium text-dark"><?= htmlspecialchars($stat['nama_status']) ?></td>
                                    <td class="text-end pe-4">
                                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3"><?= number_format($stat['jumlah_servis']) ?> Unit</span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="2" class="text-center py-4 text-muted">Tidak ada data.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100 animate__animated animate__fadeInRight">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="m-0 fw-bold text-dark"><i class="fas fa-user-tie me-2 text-success"></i>Produktivitas Teknisi</h6>
                <span class="badge bg-light text-secondary border" style="font-size: 0.65rem;">AUTO-CALCULATED</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="bg-light text-secondary small text-uppercase">
                            <tr>
                                <th class="ps-4">Nama Teknisi</th>
                                <th class="text-center">Selesai</th>
                                <th class="text-end pe-4">Estimasi Omzet</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($revenueByTech)): ?>
                                <?php foreach ($revenueByTech as $tech): ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-dark"><?= htmlspecialchars($tech['nama_teknisi']) ?></td>
                                    <td class="text-center text-muted small"><?= number_format($tech['jumlah_pembayaran']) ?> unit</td>
                                    <td class="text-end pe-4 fw-bold text-success">
                                        Rp <?= number_format($tech['total_pendapatan'], 0, ',', '.') ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="3" class="text-center py-4 text-muted">Data performa kosong.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center mb-0" role="alert">
            <div class="me-3">
                <div class="bg-warning text-white rounded-circle d-flex justify-content-center align-items-center" style="width: 45px; height: 45px;">
                    <i class="fas fa-history"></i>
                </div>
            </div>
            <div class="flex-grow-1">
                <h6 class="alert-heading fw-bold mb-1">Monitoring Antrian (Pending Queue)</h6>
                <p class="mb-0 small text-dark opacity-75">
                    Saat ini terdapat <strong><?= isset($pendingServices) ? count($pendingServices) : 0 ?></strong> service yang belum selesai dan membutuhkan tindakan segera.
                </p>
            </div>
            <div class="ms-3 d-none d-md-block">
                <div class="d-flex gap-2">
                    <?php if(!empty($pendingServices)): ?>
                        <?php foreach($pendingServices as $p): ?>
                            <span class="badge bg-white text-warning border border-warning">#<?= $p['id_service'] ?></span>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-5 animate__animated animate__fadeInUp">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 fw-bold text-dark"><i class="fas fa-list-alt me-2 text-primary"></i>Rincian Riwayat Transaksi</h6>
        <span class="badge bg-secondary rounded-pill"><?= isset($detailedData) ? count($detailedData) : 0 ?> Data</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0 align-middle">
                <thead class="bg-light text-secondary small text-uppercase">
                    <tr>
                        <th class="ps-3">ID</th>
                        <th>Tanggal</th>
                        <th>Pelanggan</th>
                        <th>Perangkat</th>
                        <th>Teknisi</th>
                        <th class="text-center">Status</th>
                        <th class="text-end pe-3">Biaya</th>
                    </tr>
                </thead>
                <tbody class="small">
                    <?php if (!empty($detailedData)): ?>
                        <?php foreach ($detailedData as $row): ?>
                        <tr>
                            <td class="ps-3 fw-bold text-primary">#<?= $row['id_service'] ?></td>
                            <td><?= date('d/m/Y', strtotime($row['tanggal_masuk'])) ?></td>
                            <td class="fw-medium"><?= htmlspecialchars($row['nama_pelanggan'] ?? '-') ?></td>
                            <td class="text-muted"><?= htmlspecialchars($row['nama_perangkat'] ?? '-') ?></td>
                            <td>
                                <?php if ($row['nama_teknisi']): ?>
                                    <span class="text-dark"><?= htmlspecialchars($row['nama_teknisi']) ?></span>
                                <?php else: ?>
                                    <span class="text-muted fst-italic">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php 
                                    $status = strtolower($row['nama_status'] ?? '');
                                    $badgeClass = 'bg-secondary';
                                    if (strpos($status, 'selesai') !== false || strpos($status, 'lunas') !== false) $badgeClass = 'bg-success';
                                    elseif (strpos($status, 'proses') !== false) $badgeClass = 'bg-warning text-dark';
                                    elseif (strpos($status, 'batal') !== false) $badgeClass = 'bg-danger';
                                    elseif (strpos($status, 'baru') !== false) $badgeClass = 'bg-info text-dark';
                                ?>
                                <span class="badge <?= $badgeClass ?> rounded-pill px-2">
                                    <?= htmlspecialchars($row['nama_status'] ?? 'N/A') ?>
                                </span>
                            </td>
                            <td class="text-end pe-3 fw-bold">
                                Rp <?= number_format((float)filter_var($row['biaya_service'] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION), 0, ',', '.') ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">Tidak ada data transaksi pada periode ini.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<hr class="my-5 border-secondary opacity-10">

<h5 class="fw-bold mb-4 text-dark"><i class="fas fa-server me-2 text-secondary"></i>System Analytics & Audit Logs</h5>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-warning bg-opacity-10 py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 fw-bold text-warning-emphasis small text-uppercase"><i class="fas fa-archive me-2"></i>Arsip Keuangan Bulanan</h6>
                <span class="badge bg-warning text-dark rounded-pill" style="font-size: 0.65rem;">CACHED DATA</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-striped mb-0 small">
                        <thead>
                            <tr>
                                <th class="ps-3">Tahun</th>
                                <th>Bulan</th>
                                <th class="text-end pe-3">Pendapatan Bersih</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($mvData)): ?>
                                <?php foreach ($mvData as $row): ?>
                                <tr>
                                    <td class="ps-3 fw-bold"><?= $row['year'] ?></td>
                                    <td><?= date('F', mktime(0, 0, 0, $row['month'], 10)) ?></td>
                                    <td class="text-end pe-3 text-dark">Rp <?= number_format($row['total_revenue'], 0, ',', '.') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="3" class="text-center text-muted py-3">Cache kosong. Harap sinkronisasi.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-info bg-opacity-10 py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 fw-bold text-info-emphasis small text-uppercase"><i class="fas fa-network-wired me-2"></i>Live Data Stream (Sample)</h6>
                <span class="badge bg-info text-dark rounded-pill" style="font-size: 0.65rem;">REALTIME AGGREGATION</span>
            </div>
            <div class="card-body p-3">
                <p class="small text-muted mb-2">Sampling 5 data terakhir yang digabungkan dari berbagai master data secara realtime.</p>
                <div class="table-responsive border rounded bg-white">
                    <table class="table table-sm table-hover mb-0" style="font-size: 0.8rem;">
                        <thead class="bg-light">
                            <tr>
                                <th>Ref ID</th>
                                <th>Pelanggan</th>
                                <th>Perangkat</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($viewData)): ?>
                                <?php foreach ($viewData as $v): ?>
                                <tr>
                                    <td class="fw-bold text-primary">#<?= $v['id_service'] ?></td>
                                    <td><?= $v['nama_pelanggan'] ?? $v['nama'] ?? 'Unknown' ?></td>
                                    <td><?= $v['nama_perangkat'] ?? $v['merek'] ?? 'Unknown' ?></td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <?= $v['nama_status'] ?? 'N/A' ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center py-3 text-danger">Tidak ada sample data.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-dark text-white py-3 border-bottom border-secondary">
                <div class="d-flex justify-content-between align-items-center">
                     <h6 class="m-0 fw-bold small text-uppercase"><i class="fas fa-terminal me-2"></i>Database Query Diagnostics</h6>
                     <small class="text-success"><i class="fas fa-circle me-1" style="font-size: 8px;"></i>System Online</small>
                </div>
            </div>
            <div class="card-body bg-dark p-0">
                <div class="p-3 font-monospace small" style="background-color: #0d1117; color: #58a6ff; max-height: 200px; overflow-y: auto; font-size: 0.75rem;">
                    <div class="mb-2 text-secondary border-bottom border-secondary pb-1">system@audit:~$ run_performance_check --target=service_logs</div>
                    <?= htmlspecialchars($explainResults['with_index'] ?? 'No execution plan available.') ?>
                </div>
            </div>
            <div class="card-footer bg-light small text-muted">
                Analisis otomatis Execution Plan untuk memantau efisiensi index database.
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>