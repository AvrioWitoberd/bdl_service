<?php
// models/DatabaseFeatures.php
class DatabaseFeatures
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // 1. Ambil Data dari Materialized View
    public function getMonthlyRevenueMV()
    {
        try {
            // Pastikan kolom ini ada di MV kamu: year, month, total_revenue, total_services
            $stmt = $this->pdo->prepare("SELECT * FROM monthly_revenue_mv ORDER BY year DESC, month DESC");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            // Jika MV belum ada atau nama kolom beda
            return []; 
        }
    }

    // 2. Fungsi Refresh Materialized View
    public function refreshMonthlyRevenueMV()
    {
        try {
            // Perintah Refresh MV PostgreSQL
            $this->pdo->exec("REFRESH MATERIALIZED VIEW monthly_revenue_mv;");
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    // 3. Complex Query (Simulasi View tanpa create view di DB)
    public function getServiceSummaryViewData($limit = 10)
    {
        $sql = "SELECT s.id_service, p.nama as nama_pelanggan, 
                       d.nama_perangkat, d.merek, d.jenis_perangkat,
                       sp.nama_status, t.nama_teknisi
                FROM service s
                JOIN perangkat d ON s.id_perangkat = d.id_perangkat
                JOIN pelanggan p ON s.id_pelanggan = p.id_pelanggan
                LEFT JOIN teknisi t ON s.id_teknisi = t.id_teknisi
                JOIN status_perbaikan sp ON s.id_status = sp.id_status
                ORDER BY s.tanggal_masuk DESC
                LIMIT :limit";
                
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // 4. Explain Analyze (Demo Performa)
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
            return ['with_index' => "Gagal menjalankan EXPLAIN: " . $e->getMessage()];
        }
    }
}
?>