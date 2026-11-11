<?php
// config/database.php
// FUNGSI: Untuk koneksi ke MySQL database

class Database {
    private $host = "localhost";
    private $db_name = "bimbel_aiueo";
    private $username = "root";
    private $password = "";
    public $conn = null;

    public function getConnection() {
        if ($this->conn !== null) {
            return $this->conn;
        }

        // Coba koneksi ke database bimbel_aiueo atau bimbel_billa jika sudah dibuat
        $databasesToTry = [$this->db_name, "bimbel_billa"];
        
        foreach ($databasesToTry as $targetDb) {
            try {
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ];
                if (defined('PDO::MYSQL_ATTR_MULTI_STATEMENTS')) {
                    $options[PDO::MYSQL_ATTR_MULTI_STATEMENTS] = true;
                }

                $pdo = new PDO(
                    "mysql:host=" . $this->host . ";dbname=" . $targetDb . ";charset=utf8mb4",
                    $this->username,
                    $this->password,
                    $options
                );
                
                $this->db_name = $targetDb;
                $this->conn = $pdo;

                // Pastikan tabel database sudah ada
                $this->ensureTablesExist($this->conn);

                return $this->conn;
            } catch (PDOException $e) {
                // Database belum ada, lanjut ke opsi berikutnya atau buat otomatis
                continue;
            }
        }

        // Jika kedua database belum ada, buat database secara otomatis
        try {
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ];
            $serverPdo = new PDO(
                "mysql:host=" . $this->host . ";charset=utf8mb4",
                $this->username,
                $this->password,
                $options
            );

            // Buat database bimbel_aiueo
            $serverPdo->exec("CREATE DATABASE IF NOT EXISTS `{$this->db_name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");

            // Hubungkan ke database baru
            $connOptions = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ];
            if (defined('PDO::MYSQL_ATTR_MULTI_STATEMENTS')) {
                $connOptions[PDO::MYSQL_ATTR_MULTI_STATEMENTS] = true;
            }

            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password,
                $connOptions
            );

            // Import struktur tabel dan data default dari bimbel_aiueo.sql
            $this->ensureTablesExist($this->conn);

            return $this->conn;
        } catch (PDOException $exception) {
            error_log("Database connection error: " . $exception->getMessage());
            return null;
        }
    }

    private function ensureTablesExist($pdo) {
        try {
            $check = $pdo->query("SHOW TABLES LIKE 'users'")->fetch();
            if (!$check) {
                $sqlFile = __DIR__ . '/../bimbel_aiueo.sql';
                if (file_exists($sqlFile)) {
                    $sql = file_get_contents($sqlFile);
                    if (!empty($sql)) {
                        $pdo->exec($sql);
                    }
                }
            }
        } catch (PDOException $e) {
            error_log("Auto import tables error: " . $e->getMessage());
        }
    }
}

// Fungsi helper untuk koneksi
function getDB() {
    $database = new Database();
    return $database->getConnection();
}
?>