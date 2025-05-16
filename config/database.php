<?php
class Database {
    private $host = "localhost";
    private $db_name = "contact_form_db";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            // First connect without database name to create it if it doesn't exist
            $this->conn = new PDO(
                "mysql:host=" . $this->host,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Create database if it doesn't exist
            $this->conn->exec("CREATE DATABASE IF NOT EXISTS " . $this->db_name);
            
            // Select the database
            $this->conn->exec("USE " . $this->db_name);

            // Create messages table if it doesn't exist
            $this->conn->exec("CREATE TABLE IF NOT EXISTS messages (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(100) NOT NULL,
                subject VARCHAR(200) NOT NULL,
                message TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");

        } catch(PDOException $e) {
            echo "Connection Error: " . $e->getMessage();
        }

        return $this->conn;
    }
} 