<?php
class Database {
    private $host = "localhost";
    private $db_name = "futsal_rental";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        try {
            if (!$this->conn) {
                $this->conn = new PDO(
                    "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                    $this->username,
                    $this->password,
                    array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
                );
                $this->conn->exec("SET NAMES utf8");
            }
            return $this->conn;
        } catch(PDOException $e) {
            error_log("Connection error: " . $e->getMessage());
            return null;
        }
    }

    public function createDatabase() {
        try {
            // Connect without database selected
            $conn = new PDO(
                "mysql:host=" . $this->host,
                $this->username,
                $this->password,
                array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
            );

            // Create database if not exists
            $sql = "CREATE DATABASE IF NOT EXISTS " . $this->db_name;
            $conn->exec($sql);
            
            // Select the database
            $conn->exec("USE " . $this->db_name);
            
            // Import schema
            $sql = file_get_contents(__DIR__ . '/../database.sql');
            $conn->exec($sql);
            
            return true;
        } catch(PDOException $e) {
            error_log("Database creation error: " . $e->getMessage());
            return false;
        }
    }
}

// Initialize database if needed
if (php_sapi_name() === 'cli-server') {
    $database = new Database();
    if (!$database->getConnection()) {
        $database->createDatabase();
    }
}
?>
