<?php
require_once __DIR__ . '/../Database.php';

class Qualification {
    private $db;
    private $connection;
    
    private $table = 'QUALIFICATION';
    
    public $qualification_id;
    public $name;
    public $description;
    public $created_date;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->connection = $this->db->getConnection();
    }
    
    // Get all qualifications
    public function getAllQualifications() {
        $query = "SELECT * FROM {$this->table} ORDER BY qualification_id";
        $stid = oci_parse($this->connection, $query);
        
        if (!oci_execute($stid)) {
            $e = oci_error($stid);
            throw new Exception("Query failed: " . $e['message']);
        }
        
        $qualifications = [];
        while ($row = oci_fetch_assoc($stid)) {
            $qualifications[] = $row;
        }
        
        oci_free_statement($stid);
        return $qualifications;
    }
    
    // Get single qualification by ID
    public function getQualificationById($id) {
        $query = "SELECT * FROM {$this->table} WHERE qualification_id = :id";
        $stid = oci_parse($this->connection, $query);
        oci_bind_by_name($stid, ':id', $id);
        
        if (!oci_execute($stid)) {
            $e = oci_error($stid);
            throw new Exception("Query failed: " . $e['message']);
        }
        
        $qualification = oci_fetch_assoc($stid);
        oci_free_statement($stid);
        
        return $qualification ? $qualification : null;
    }
    
    // Get qualifications by employee
    public function getQualificationsByEmployee($employee_id) {
        $query = "SELECT q.*, eq.date_obtained, eq.institution_name 
                  FROM {$this->table} q
                  INNER JOIN EMPLOYEE_QUALIFICATION eq ON q.qualification_id = eq.qualification_id
                  WHERE eq.employee_id = :emp_id
                  ORDER BY eq.date_obtained DESC";
        
        $stid = oci_parse($this->connection, $query);
        oci_bind_by_name($stid, ':emp_id', $employee_id);
        
        if (!oci_execute($stid)) {
            $e = oci_error($stid);
            throw new Exception("Query failed: " . $e['message']);
        }
        
        $qualifications = [];
        while ($row = oci_fetch_assoc($stid)) {
            $qualifications[] = $row;
        }
        
        oci_free_statement($stid);
        return $qualifications;
    }
    
    // Create new qualification
    public function createQualification($data) {
        $query = "INSERT INTO {$this->table} 
                  (name, description) 
                  VALUES (:name, :description)";
        
        $stid = oci_parse($this->connection, $query);
        
        oci_bind_by_name($stid, ':name', $data['name']);
        oci_bind_by_name($stid, ':description', $data['description']);
        
        if (!oci_execute($stid, OCI_COMMIT_ON_SUCCESS)) {
            $e = oci_error($stid);
            oci_free_statement($stid);
            throw new Exception("Failed to create qualification: " . $e['message']);
        }
        
        oci_free_statement($stid);
        return true;
    }
    
    // Assign qualification to employee
    public function assignToEmployee($employee_id, $qualification_id, $data) {
        $query = "INSERT INTO EMPLOYEE_QUALIFICATION 
                  (employee_id, qualification_id, date_obtained, institution_name) 
                  VALUES (:emp_id, :qual_id, TO_DATE(:date_obtained, 'YYYY-MM-DD'), :institution)";
        
        $stid = oci_parse($this->connection, $query);
        
        oci_bind_by_name($stid, ':emp_id', $employee_id);
        oci_bind_by_name($stid, ':qual_id', $qualification_id);
        oci_bind_by_name($stid, ':date_obtained', $data['date_obtained']);
        oci_bind_by_name($stid, ':institution', $data['institution_name']);
        
        if (!oci_execute($stid, OCI_COMMIT_ON_SUCCESS)) {
            $e = oci_error($stid);
            oci_free_statement($stid);
            throw new Exception("Failed to assign qualification: " . $e['message']);
        }
        
        oci_free_statement($stid);
        return true;
    }
    
    // Remove qualification from employee
    public function removeFromEmployee($employee_id, $qualification_id) {
        $query = "DELETE FROM EMPLOYEE_QUALIFICATION 
                  WHERE employee_id = :emp_id AND qualification_id = :qual_id";
        
        $stid = oci_parse($this->connection, $query);
        oci_bind_by_name($stid, ':emp_id', $employee_id);
        oci_bind_by_name($stid, ':qual_id', $qualification_id);
        
        if (!oci_execute($stid, OCI_COMMIT_ON_SUCCESS)) {
            $e = oci_error($stid);
            oci_free_statement($stid);
            throw new Exception("Failed to remove qualification: " . $e['message']);
        }
        
        $rowsAffected = oci_num_rows($stid);
        oci_free_statement($stid);
        
        return $rowsAffected > 0;
    }
    
    // Update qualification
    public function updateQualification($id, $data) {
        $query = "UPDATE {$this->table} 
                  SET name = :name, 
                      description = :description
                  WHERE qualification_id = :id";
        
        $stid = oci_parse($this->connection, $query);
        
        oci_bind_by_name($stid, ':name', $data['name']);
        oci_bind_by_name($stid, ':description', $data['description']);
        oci_bind_by_name($stid, ':id', $id);
        
        if (!oci_execute($stid, OCI_COMMIT_ON_SUCCESS)) {
            $e = oci_error($stid);
            oci_free_statement($stid);
            throw new Exception("Failed to update qualification: " . $e['message']);
        }
        
        $rowsAffected = oci_num_rows($stid);
        oci_free_statement($stid);
        
        return $rowsAffected > 0;
    }
    
    // Delete qualification
    public function deleteQualification($id) {
        // First delete from junction table
        $query1 = "DELETE FROM EMPLOYEE_QUALIFICATION WHERE qualification_id = :id";
        $stid1 = oci_parse($this->connection, $query1);
        oci_bind_by_name($stid1, ':id', $id);
        
        if (!oci_execute($stid1)) {
            $e = oci_error($stid1);
            oci_free_statement($stid1);
            throw new Exception("Failed to delete qualification references: " . $e['message']);
        }
        oci_free_statement($stid1);
        
        // Then delete from main table
        $query2 = "DELETE FROM {$this->table} WHERE qualification_id = :id";
        $stid2 = oci_parse($this->connection, $query2);
        oci_bind_by_name($stid2, ':id', $id);
        
        if (!oci_execute($stid2, OCI_COMMIT_ON_SUCCESS)) {
            $e = oci_error($stid2);
            oci_free_statement($stid2);
            throw new Exception("Failed to delete qualification: " . $e['message']);
        }
        
        $rowsAffected = oci_num_rows($stid2);
        oci_free_statement($stid2);
        
        return $rowsAffected > 0;
    }
}