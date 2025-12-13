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
        // Railway Environment Variables
        $this->host     = getenv('MYSQLHOST')     ?: 'mysql.railway.internal';
        $this->port     = getenv('MYSQLPORT')     ?: '3306';
        $this->db_name  = getenv('MYSQLDATABASE') ?: 'railway';
        $this->username = getenv('MYSQLUSER')     ?: 'root';
        $this->password = getenv('MYSQLPASSWORD') ?: '';
    }
    
    public function getConnection() {
        $this->conn = null;
        
        try {
            // PERBAIKAN: Hapus spasi setelah "mysql:"
            $dsn = "mysql:host=" . $this->host . 
                   ";port=" . $this->port . 
                   ";dbname=" . $this->db_name . 
                   ";charset=utf8mb4";
            
            $this->conn = new PDO(
                $dsn,
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
            
            return $this->conn;
            
        } catch(PDOException $e) {
            // Debugging: tampilkan detail error
            echo "Connection Error: " . $e->getMessage() . "<br>";
            echo "DSN: mysql:host={$this->host};port={$this->port};dbname={$this->db_name}<br>";
            echo "User: {$this->username}<br>";
            die();
        }
    }
}
