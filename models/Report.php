<?php
// models/Report.php
class Report
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

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
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getRevenueByTechnicianReport($startDate = null, $endDate = null)
    {
        $whereClause = " WHERE p.status_bayar = 'paid' ";
        $params = [];
        if ($startDate) {
            $whereClause .= " AND s.tanggal_masuk >= :start_date ";
            $params[':start_date'] = $startDate;
        }
        if ($endDate) {
            $whereClause .= " AND s.tanggal_masuk <= :end_date ";
            $params[':end_date'] = $endDate;
        }
        $sql = "SELECT t.nama_teknisi, SUM(p.total_bayar) as total_pendapatan, COUNT(p.id_pembayaran) as jumlah_pembayaran
                FROM pembayaran p
                JOIN service s ON p.id_service = s.id_service
                JOIN teknisi t ON s.id_teknisi = t.id_teknisi
                $whereClause
                GROUP BY t.id_teknisi, t.nama_teknisi
                ORDER BY total_pendapatan DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function exportToCSV($data, $filename)
    {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '";');

        if (count($data) > 0) {
            $output = fopen('php://output', 'w');
            fputcsv($output, array_keys($data[0]));
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
            fclose($output);
            exit;
        }
    }
}
?>