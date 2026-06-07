<?php
date_default_timezone_set('Asia/Manila');

// Include your existing model
require_once dirname(__DIR__) . "/models/centers.model.php";

// The Connection class is already defined in centers.model.php
// This file just ensures it's available
if (!class_exists('Connection')) {
    class Connection {
        private $host = "localhost";
        private $username = "root";
        private $password = "";
        private $database = "evacfinder";
        
        public function connect() {
            try {
                $conn = new PDO(
                    "mysql:host=" . $this->host . ";dbname=" . $this->database,
                    $this->username,
                    $this->password
                );
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $conn->exec("SET time_zone = '+08:00'");
                return $conn;
            } catch(PDOException $e) {
                die("Connection failed: " . $e->getMessage());
            }
        }
    }
}
?>