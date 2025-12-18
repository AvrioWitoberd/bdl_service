<?php
// models/Report.php

class Report
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // 1. Statistik Status Service (Laporan Standar)
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

        $sql = "SELECT sp.nama_status, COUNT(s.id_service) as jumlah_servis
                FROM service s
                JOIN status_perbaikan sp ON s.id_status = sp.id_status
                $whereClause
                GROUP BY sp.id_status, sp.nama_status
                ORDER BY jumlah_servis DESC";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    // 2. Performa Teknisi (Laporan Standar)
    public function getTechnicianPerformance($startDate = null, $endDate = null)
    {
        // Default tanggal jika kosong (biar SP tidak error)
        if (!$startDate) $startDate = '2000-01-01'; 
        if (!$endDate) $endDate = date('Y-12-31');

        // Panggil Function/Stored Procedure di PostgreSQL
        $sql = "SELECT * FROM sp_get_performa_teknisi(:start_date, :end_date)";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':start_date', $startDate);
            $stmt->bindValue(':end_date', $endDate);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    // [TAMBAH INI] 3. Simple View (Antrian Pending)
    public function getPendingServicesSimple()
    {
        try {
            // Ambil dari Simple View yang kita buat
            $stmt = $this->pdo->prepare("SELECT * FROM v_service_pending LIMIT 5");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    // 3. Materialized View (Fitur Lanjut)
    public function getMonthlyRevenueMV()
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM mv_pendapatan_bulanan ORDER BY year DESC, month DESC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function refreshMonthlyRevenueMV()
    {
        try {
            $this->pdo->exec("REFRESH MATERIALIZED VIEW mv_pendapatan_bulanan");
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    // 4. View Complex (Fitur Lanjut)
    public function getServiceSummaryViewData($limit = 5)
    {
        try {
            $sql = "SELECT * FROM v_service_complex LIMIT :limit";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    // 5. Explain Analyze (Fitur Lanjut)
    public function getExplainAnalyzeResults()
    {
        $query = "SELECT * FROM service s 
                  JOIN status_perbaikan sp ON s.id_status = sp.id_status 
                  WHERE s.tanggal_masuk > '2024-01-01'";

        try {
            $explain = $this->pdo->prepare("EXPLAIN (ANALYZE, BUFFERS) $query");
            $explain->execute();
            $result = $explain->fetchAll(PDO::FETCH_COLUMN, 0);
            return ['with_index' => implode("\n", $result)];
        } catch (PDOException $e) {
            return ['with_index' => "Error EXPLAIN: " . $e->getMessage()];
        }
    }
}
?>