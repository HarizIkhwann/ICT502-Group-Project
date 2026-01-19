<?php
require_once __DIR__ . '/../config/database.php';

class Database {
    private static $instance = null;
    private $connection;

    public function __construct() {
        $this->connection = oci_pconnect(DB_USERNAME, DB_PASSWORD, DB_CONNECTION_STRING);
        if (!$this->connection) {
            $e = oci_error();
            throw new Exception("Failed to connect to the database." . $e['message']);
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }
    public function closeConnection() {
        if ($this->connection) {
            oci_close($this->connection);
            $this->connection = null;
        }
    }
    
    //Prevent cloning
    private function __clone() {}
}