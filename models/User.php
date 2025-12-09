<?php
// models/User.php
class User {
    private $pdo;

    public function __construct($pdo) {
        // 🔥 SAFETY CHECK: pastikan $pdo valid sebelum dipakai
        if (!$pdo || !($pdo instanceof PDO)) {
            throw new Exception("❌ Invalid PDO object in User.php constructor. Check config/database.php return value.");
        }
        $this->pdo = $pdo;
    }

    // Authenticate Admin (Login via email - Sekarang pake email!)
    public function authenticateAdmin($email, $password) {
        // 🔁 Ganti query: cari berdasarkan email, bukan username
        // 🔥 Perhatikan: kolom password TANPA kutip karena di DB emang gak ada kutipnya
        $stmt = $this->pdo->prepare("SELECT id_admin as id, email as username, password as password_hash, nama_admin as name, 'admin' as role FROM admin WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // 🔥 Gunakan password_verify untuk cek password hash
        if ($user && password_verify($password, $user['password_hash'])) {
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];
            return true;
        }
        return false;
    }

    // Authenticate Pelanggan (Login via email) - Tetap sama
    public function authenticatePelanggan($email, $password) {
        // 🔥 Perhatikan: kolom password TANPA kutip
        $stmt = $this->pdo->prepare("SELECT id_pelanggan as id, email as username, password as password_hash, nama as name, 'pelanggan' as role FROM pelanggan WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // 🔥 Gunakan password_verify untuk cek password hash
        if ($user && password_verify($password, $user['password_hash'])) {
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];
            return true;
        }
        return false;
    }

    // Authenticate Teknisi (Login via email, hanya aktif) - Tetap sama
    public function authenticateTeknisi($email, $password) {
        // 🔥 Perhatikan: kolom password TANPA kutip
        $stmt = $this->pdo->prepare("SELECT id_teknisi as id, email as username, password as password_hash, nama_teknisi as name, 'teknisi' as role FROM teknisi WHERE email = ? AND status_aktif = TRUE");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // 🔥 Gunakan password_verify untuk cek password hash
        if ($user && password_verify($password, $user['password_hash'])) {
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];
            return true;
        }
        return false;
    }

    // Fungsi untuk cek apakah user sudah login
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
}
?>