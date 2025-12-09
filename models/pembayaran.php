<?php
// models/Pembayaran.php
class Pembayaran
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function getByServiceId($serviceId)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM pembayaran WHERE id_service = ? ORDER BY tanggal_bayar DESC");
        $stmt->execute([$serviceId]);
        return $stmt->fetch(); // Ambil pembayaran terbaru untuk service tertentu
    }

    // Tambahkan metode lain jika diperlukan, misalnya getAllForReport
}
?>