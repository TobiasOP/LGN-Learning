<?php
// config/database.php - Railway Compatible

class Database {
    private $host;
    private $port;
    private $db_name;
    private $username;
    private $password;
    public $conn;

    public function __construct() {
        // Railway Environment Variables (auto-set by Railway)
        // Local fallback untuk development
        $this->host     = getenv('MYSQLHOST')     ?: 'railway';
        $this->port     = getenv('MYSQLPORT')     ?: '3306';
        $this->db_name  = getenv('MYSQLDATABASE') ?: 'railway';
        // $this->username = getenv('MYSQLUSER')     ?: 'root';
        $this->password = getenv('MYSQLPASSWORD') ?: '';
    }

    public function getConnection() {
        $this->conn = null;
        
        try {
            $dsn = "mysql: host=" . $this->host . 
                   ";port=" . $this->port . 
                   ";dbname=" . $this->db_name . 
                   ";charset=utf8mb4";
            
            $this->conn = new PDO(
                $dsn,
                $this->username,
                $this->password,
                [
                    PDO:: ATTR_ERRMODE => PDO:: ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO:: FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch(PDOException $e) {
            die("Connection Error: " . $e->getMessage());
        }

        return $this->conn;
    }
}


