<?php
require_once __DIR__ . '/../Database.php';

class Position {
    private $db;
    private $connection;
    
    private $table = 'POSITION';
    
    public $position_id;
    public $position_title;
    public $job_description;
    public $min_salary;
    public $max_salary;
    public $created_date;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->connection = $this->db->getConnection();
    }
    
    // Get all positions
    public function getAllPositions() {
        $query = "SELECT * FROM {$this->table} ORDER BY position_id";
        $stid = oci_parse($this->connection, $query);
        
        if (!oci_execute($stid)) {
            $e = oci_error($stid);
            throw new Exception("Query failed: " . $e['message']);
        }
        
        $positions = [];
        while ($row = oci_fetch_assoc($stid)) {
            $positions[] = $row;
        }
        
        oci_free_statement($stid);
        return $positions;
    }
    
    // Get single position by ID
    public function getPositionById($id) {
        $query = "SELECT * FROM {$this->table} WHERE position_id = :id";
        $stid = oci_parse($this->connection, $query);
        oci_bind_by_name($stid, ':id', $id);
        
        if (!oci_execute($stid)) {
            $e = oci_error($stid);
            throw new Exception("Query failed: " . $e['message']);
        }
        
        $position = oci_fetch_assoc($stid);
        oci_free_statement($stid);
        
        return $position ? $position : null;
    }
    
    // Create new position
    public function createPosition($data) {
        $query = "INSERT INTO {$this->table} 
                  (position_title, job_description, min_salary, max_salary) 
                  VALUES (:title, :description, :min_salary, :max_salary)";
        
        $stid = oci_parse($this->connection, $query);
        
        oci_bind_by_name($stid, ':title', $data['position_title']);
        oci_bind_by_name($stid, ':description', $data['job_description']);
        oci_bind_by_name($stid, ':min_salary', $data['min_salary']);
        oci_bind_by_name($stid, ':max_salary', $data['max_salary']);
        
        if (!oci_execute($stid, OCI_COMMIT_ON_SUCCESS)) {
            $e = oci_error($stid);
            oci_free_statement($stid);
            throw new Exception("Failed to create position: " . $e['message']);
        }
        
        oci_free_statement($stid);
        return true;
    }
    
    // Update position
    public function updatePosition($id, $data) {
        $query = "UPDATE {$this->table} 
                  SET position_title = :title, 
                      job_description = :description, 
                      min_salary = :min_salary, 
                      max_salary = :max_salary
                  WHERE position_id = :id";
        
        $stid = oci_parse($this->connection, $query);
        
        oci_bind_by_name($stid, ':title', $data['position_title']);
        oci_bind_by_name($stid, ':description', $data['job_description']);
        oci_bind_by_name($stid, ':min_salary', $data['min_salary']);
        oci_bind_by_name($stid, ':max_salary', $data['max_salary']);
        oci_bind_by_name($stid, ':id', $id);
        
        if (!oci_execute($stid, OCI_COMMIT_ON_SUCCESS)) {
            $e = oci_error($stid);
            oci_free_statement($stid);
            throw new Exception("Failed to update position: " . $e['message']);
        }
        
        $rowsAffected = oci_num_rows($stid);
        oci_free_statement($stid);
        
        return $rowsAffected > 0;
    }
    
    // Delete position
    public function deletePosition($id) {
        $query = "DELETE FROM {$this->table} WHERE position_id = :id";
        $stid = oci_parse($this->connection, $query);
        oci_bind_by_name($stid, ':id', $id);
        
        if (!oci_execute($stid, OCI_COMMIT_ON_SUCCESS)) {
            $e = oci_error($stid);
            oci_free_statement($stid);
            throw new Exception("Failed to delete position: " . $e['message']);
        }
        
        $rowsAffected = oci_num_rows($stid);
        oci_free_statement($stid);
        
        return $rowsAffected > 0;
    }
}