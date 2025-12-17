<?php
// models/Report.php
class Report
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // --- FUNGSI LAPORAN LAMA (Statistik & Performa Teknisi) ---

    // 1. Laporan Statistik Status Service (Tabel Kiri)
    public function getServicesByStatusReport($startDate = null, $endDate = null)
    {
        $whereClause = " WHERE 1=1 ";
        $params = [];
        if ($startDate) {
            $whereClause .= " AND s.tanggal_masuk >= :start_date ";
            $params[':start_date'] = $startDate;
        }
        if ($endDate) {
            $whereClause .= " AND s.tanggal_masuk <= :end_date ";
            $params[':end_date'] = $endDate;
        }

        // Query menggunakan REPLACE pada ID untuk mengantisipasi format koma (6,009 -> 6009)
        $sql = "SELECT sp.nama_status, COUNT(s.id_service) as jumlah_servis
                FROM service s
                JOIN status_perbaikan sp ON REPLACE(s.id_status, ',', '') = REPLACE(sp.id_status, ',', '')
                $whereClause
                GROUP BY sp.id_status, sp.nama_status
                ORDER BY jumlah_servis DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 2. Laporan Performa & Pendapatan Teknisi (Tabel Kanan)
    public function getTechnicianPerformance($startDate = null, $endDate = null)
    {
        $params = [];
        $dateFilter = "";

        if ($startDate && $endDate) {
            $dateFilter = " AND s.tanggal_masuk BETWEEN :start_date AND :end_date ";
            $params[':start_date'] = $startDate;
            $params[':end_date'] = $endDate;
        }

        // Menggunakan LEFT JOIN agar Teknisi yang belum ada kerjaan tetap muncul (0)
        // Menggunakan REPLACE pada ID agar join 3,001 ke 3,001 berhasil meski ada koma
        $sql = "SELECT 
                    t.nama_teknisi,
                    COUNT(s.id_service) as jumlah_pembayaran,
                    SUM(CASE 
                        WHEN REPLACE(s.id_status, ',', '') IN (6006, 6009) 
                        THEN CAST(REPLACE(s.biaya_service, ',', '') AS UNSIGNED) 
                        ELSE 0 
                    END) as total_pendapatan
                FROM teknisi t
                LEFT JOIN service s ON REPLACE(t.id_teknisi, ',', '') = REPLACE(s.id_teknisi, ',', '') $dateFilter
                GROUP BY t.id_teknisi, t.nama_teknisi
                ORDER BY total_pendapatan DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // --- FUNGSI LAPORAN BARU (Dari Fitur DB) ---

    // 3. Ambil data dari Materialized View
    public function getMonthlyRevenueMV()
    {
        try {
            // SELECT langsung dari Materialized View
            $stmt = $this->pdo->prepare("SELECT * FROM mv_pendapatan_bulanan ORDER BY year DESC, month DESC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC); // Pastikan fetch as array
        } catch (PDOException $e) {
            error_log("DB Error fetching MV data: " . $e->getMessage());
            return [];
        }
    }

    // 4. Ambil data dari Complex View
    public function getServiceSummaryViewData($limit = 5)
    {
        // Ganti nama 'v_service_complex' sesuai nama VIEW yang kamu buat di database
        $sql = "SELECT * FROM v_service_complex LIMIT :limit";
                
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Pastikan fetch as array
    }

    // 5. Ambil hasil EXPLAIN ANALYZE
    public function getExplainAnalyzeResults()
    {
        // Query demo: Mencari service yang sudah selesai di tahun ini
        $query = "SELECT * FROM service s 
                  JOIN status_perbaikan sp ON s.id_status = sp.id_status 
                  WHERE sp.nama_status = 'Selesai Diperbaiki' 
                  AND s.tanggal_masuk > '2024-01-01'";

        try {
            $explain = $this->pdo->prepare("EXPLAIN (ANALYZE, BUFFERS) $query");
            $explain->execute();
            $result = $explain->fetchAll(PDO::FETCH_COLUMN, 0);
            return ['with_index' => implode("\n", $result)];
        } catch (PDOException $e) {
            error_log("DB Error running EXPLAIN: " . $e->getMessage());
            return ['with_index' => "Gagal menjalankan EXPLAIN: " . $e->getMessage()];
        }
    }

    // --- FUNGSI TAMBAHAN ---
    
    // Refresh Materialized View
    public function refreshMonthlyRevenueMV()
    {
        try {
            $this->pdo->exec("REFRESH MATERIALIZED VIEW mv_pendapatan_bulanan");
            return true;
        } catch (PDOException $e) {
            error_log("DB Error refreshing MV: " . $e->getMessage());
            return false;
        }
    }

    // Export ke CSV (lama, bisa dipake buat semua data)
    public function exportToCSV($data, $filename)
    {
        if (empty($data)) {
            return;
        }

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '";');

        $output = fopen('php://output', 'w');
        // Header kolom dari array pertama
        if (is_array($data[0])) {
            fputcsv($output, array_keys($data[0]));
        }
        // Isi data
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        fclose($output);
        exit;
    }
}
?>