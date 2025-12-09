<?php
// models/DatabaseFeatures.php
class DatabaseFeatures
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Example Complex View Query (assuming view exists)
    public function getServiceSummaryViewData($limit = 20)
    {
        // Contoh view: CREATE VIEW service_summary_view AS SELECT ... FROM service JOIN ...
        $sql = "SELECT s.id_service, s.tanggal_masuk, s.tanggal_selesai, s.keluhan, s.biaya_service, sp.nama_status, d.jenis_perangkat, d.merek, p.nama as nama_pelanggan, t.nama_teknisi
                FROM service s
                JOIN perangkat d ON s.id_perangkat = d.id_perangkat
                JOIN pelanggan p ON d.id_pelanggan = p.id_pelanggan
                LEFT JOIN teknisi t ON s.id_teknisi = t.id_teknisi
                JOIN status_perbaikan sp ON s.id_status = sp.id_status
                ORDER BY s.tanggal_masuk DESC
                LIMIT :limit";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Example Materialized View Query and Refresh
    public function getMonthlyRevenueMV()
    {
        // Contoh MV: CREATE MATERIALIZED VIEW monthly_revenue_mv AS SELECT ...
        $stmt = $this->pdo->prepare("SELECT year, month, total_revenue, total_services FROM monthly_revenue_mv ORDER BY year DESC, month DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function refreshMonthlyRevenueMV()
    {
        try {
            $this->pdo->exec("REFRESH MATERIALIZED VIEW monthly_revenue_mv;");
            return true;
        } catch (PDOException $e) {
            error_log("Error refreshing MV: " . $e->getMessage());
            return false;
        }
    }

    public function getExplainAnalyzeResults()
    {
        $queryWithIndex = "SELECT * FROM service s JOIN status_perbaikan sp ON s.id_status = sp.id_status WHERE sp.nama_status = 'completed' AND s.tanggal_masuk > '2024-09-01'";

        $explainWith = $this->pdo->prepare("EXPLAIN (ANALYZE, BUFFERS) $queryWithIndex");
        $explainWith->execute();
        $resultWith = $explainWith->fetchAll(PDO::FETCH_COLUMN, 0);

        return [
            'with_index' => implode("\n", $resultWith)
        ];
    }
}
?>