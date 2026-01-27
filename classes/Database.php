<?php

class Database
{
    private static $instance = null;
    private $conn;

    private $username = "HR";
    private $password = "ORACLE";
    private $connection_string = "localhost:1521/FREEPDB1";


    // Prevent direct creation
    private function __construct()
    {
       $this->conn = oci_connect($this->username, $this->password, $this->connection_string);


        if (!$this->conn) {
            $e = oci_error();
            throw new Exception("Oracle Connection Error: " . $e['message']);
        }
    }

    // Singleton accessor (what your Attendance model calls)
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    // Returns the active Oracle connection resource
    public function getConnection()
    {
        return $this->conn;
    }
}
