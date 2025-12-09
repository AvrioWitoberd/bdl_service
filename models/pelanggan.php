<?php
// models/Pelanggan.php
class Pelanggan
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAll($limit = 10, $offset = 0, $search = '')
    {
        $whereClause = '';
        $params = [];
        if ($search) {
            $whereClause = " WHERE nama ILIKE :search OR no_hp ILIKE :search OR email ILIKE :search";
            $params = [':search' => "%$search%"];
        }
        $sql = "SELECT id_pelanggan, nama, no_hp, alamat, email, tanggal_daftar FROM pelanggan $whereClause ORDER BY nama LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countAll($search = '')
    {
        $whereClause = '';
        $params = [];
        if ($search) {
            $whereClause .= " WHERE nama ILIKE :search OR no_hp ILIKE :search OR email ILIKE :search";
            $params = [':search' => "%$search%"];
        }
        $sql = "SELECT COUNT(*) FROM pelanggan $whereClause";
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function getById($id)
    {
        $stmt = $this->pdo->prepare("SELECT id_pelanggan, nama, no_hp, alamat, email, tanggal_daftar FROM pelanggan WHERE id_pelanggan = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($nama, $no_hp, $alamat, $email, $password)
    {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("INSERT INTO pelanggan (nama, no_hp, alamat, email, password) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$nama, $no_hp, $alamat, $email, $hashedPassword]);
    }

    public function update($id, $nama, $no_hp, $alamat, $email)
    {
        $stmt = $this->pdo->prepare("UPDATE pelanggan SET nama=?, no_hp=?, alamat=?, email=? WHERE id_pelanggan=?");
        return $stmt->execute([$nama, $no_hp, $alamat, $email, $id]);
    }

    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM pelanggan WHERE id_pelanggan = ?");
        return $stmt->execute([$id]);
    }
}
?>