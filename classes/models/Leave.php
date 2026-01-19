<?php
require_once __DIR__ . '/../Database.php';

class Leave {
    private $db;
    private $connection;
    
    private $table = 'LEAVE';
    
    public $leave_id;
    public $employee_id;
    public $leave_type_id;
    public $start_date;
    public $end_date;
    public $total_days;
    public $reason;
    public $status;
    public $approved_by;
    public $approval_date;
    public $created_date;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->connection = $this->db->getConnection();
    }
    
    // Get all leave records
    public function getAllLeaves() {
        $query = "SELECT l.*, e.first_name, e.last_name, lt.type_name, lt.description as type_description
                  FROM {$this->table} l
                  INNER JOIN EMPLOYEE e ON l.employee_id = e.employee_id
                  INNER JOIN LEAVE_TYPE lt ON l.leave_type_id = lt.leave_type_id
                  ORDER BY l.created_date DESC";
        
        $stid = oci_parse($this->connection, $query);
        
        if (!oci_execute($stid)) {
            $e = oci_error($stid);
            throw new Exception("Query failed: " . $e['message']);
        }
        
        $leaves = [];
        while ($row = oci_fetch_assoc($stid)) {
            $leaves[] = $row;
        }
        
        oci_free_statement($stid);
        return $leaves;
    }
    
    // Get leave by ID
    public function getLeaveById($id) {
        $query = "SELECT l.*, e.first_name, e.last_name, lt.type_name, lt.description as type_description
                  FROM {$this->table} l
                  INNER JOIN EMPLOYEE e ON l.employee_id = e.employee_id
                  INNER JOIN LEAVE_TYPE lt ON l.leave_type_id = lt.leave_type_id
                  WHERE l.leave_id = :id";
        
        $stid = oci_parse($this->connection, $query);
        oci_bind_by_name($stid, ':id', $id);
        
        if (!oci_execute($stid)) {
            $e = oci_error($stid);
            throw new Exception("Query failed: " . $e['message']);
        }
        
        $leave = oci_fetch_assoc($stid);
        oci_free_statement($stid);
        
        return $leave ? $leave : null;
    }
    
    // Get leaves by employee
    public function getLeavesByEmployee($employee_id) {
        $query = "SELECT l.*, lt.type_name, lt.description as type_description
                  FROM {$this->table} l
                  INNER JOIN LEAVE_TYPE lt ON l.leave_type_id = lt.leave_type_id
                  WHERE l.employee_id = :emp_id
                  ORDER BY l.start_date DESC";
        
        $stid = oci_parse($this->connection, $query);
        oci_bind_by_name($stid, ':emp_id', $employee_id);
        
        if (!oci_execute($stid)) {
            $e = oci_error($stid);
            throw new Exception("Query failed: " . $e['message']);
        }
        
        $leaves = [];
        while ($row = oci_fetch_assoc($stid)) {
            $leaves[] = $row;
        }
        
        oci_free_statement($stid);
        return $leaves;
    }
    
    // Get leaves by status
    public function getLeavesByStatus($status) {
        $query = "SELECT l.*, e.first_name, e.last_name, lt.type_name
                  FROM {$this->table} l
                  INNER JOIN EMPLOYEE e ON l.employee_id = e.employee_id
                  INNER JOIN LEAVE_TYPE lt ON l.leave_type_id = lt.leave_type_id
                  WHERE l.leave_status = :leave_status
                  ORDER BY l.created_date DESC";
        
        $stid = oci_parse($this->connection, $query);
        oci_bind_by_name($stid, ':leave_status', $status);
        
        if (!oci_execute($stid)) {
            $e = oci_error($stid);
            throw new Exception("Query failed: " . $e['message']);
        }
        
        $leaves = [];
        while ($row = oci_fetch_assoc($stid)) {
            $leaves[] = $row;
        }
        
        oci_free_statement($stid);
        return $leaves;
    }
    
    // Create leave request
    public function createLeave($data) {
        $query = "INSERT INTO {$this->table} 
                  (employee_id, leave_type_id, start_date, end_date, reason, leave_status) 
                  VALUES (:emp_id, :leave_type_id, TO_DATE(:start_date, 'YYYY-MM-DD'), 
                          TO_DATE(:end_date, 'YYYY-MM-DD'), :reason, :leave_status)";
        
        $stid = oci_parse($this->connection, $query);
        
        oci_bind_by_name($stid, ':emp_id', $data['employee_id']);
        oci_bind_by_name($stid, ':leave_type_id', $data['leave_type_id']);
        oci_bind_by_name($stid, ':start_date', $data['start_date']);
        oci_bind_by_name($stid, ':end_date', $data['end_date']);
        oci_bind_by_name($stid, ':reason', $data['reason']);
        oci_bind_by_name($stid, ':leave_status', $data['status']);
        
        if (!oci_execute($stid, OCI_COMMIT_ON_SUCCESS)) {
            $e = oci_error($stid);
            oci_free_statement($stid);
            throw new Exception("Failed to create leave request: " . $e['message']);
        }
        
        oci_free_statement($stid);
        return true;
    }
    
    // Approve/Reject leave
    public function updateLeaveStatus($id, $status, $approved_by) {
        $query = "UPDATE {$this->table} 
                  SET leave_status = :leave_status, 
                      approved_by = :approved_by, 
                      approval_date = SYSDATE
                  WHERE leave_id = :id";
        
        $stid = oci_parse($this->connection, $query);
        
        oci_bind_by_name($stid, ':leave_status', $status);
        oci_bind_by_name($stid, ':approved_by', $approved_by);
        oci_bind_by_name($stid, ':id', $id);
        
        if (!oci_execute($stid, OCI_COMMIT_ON_SUCCESS)) {
            $e = oci_error($stid);
            oci_free_statement($stid);
            throw new Exception("Failed to update leave status: " . $e['message']);
        }
        
        $rowsAffected = oci_num_rows($stid);
        oci_free_statement($stid);
        
        return $rowsAffected > 0;
    }
    
    // Update leave
    public function updateLeave($id, $data) {
        $query = "UPDATE {$this->table} 
                  SET leave_type_id = :leave_type_id,
                      start_date = TO_DATE(:start_date, 'YYYY-MM-DD'),
                      end_date = TO_DATE(:end_date, 'YYYY-MM-DD'),
                      reason = :reason
                  WHERE leave_id = :id";
        
        $stid = oci_parse($this->connection, $query);
        
        oci_bind_by_name($stid, ':leave_type_id', $data['leave_type_id']);
        oci_bind_by_name($stid, ':start_date', $data['start_date']);
        oci_bind_by_name($stid, ':end_date', $data['end_date']);
        oci_bind_by_name($stid, ':reason', $data['reason']);
        oci_bind_by_name($stid, ':id', $id);
        
        if (!oci_execute($stid, OCI_COMMIT_ON_SUCCESS)) {
            $e = oci_error($stid);
            oci_free_statement($stid);
            throw new Exception("Failed to update leave: " . $e['message']);
        }
        
        $rowsAffected = oci_num_rows($stid);
        oci_free_statement($stid);
        
        return $rowsAffected > 0;
    }
    
    // Delete leave
    public function deleteLeave($id) {
        $query = "DELETE FROM {$this->table} WHERE leave_id = :id";
        $stid = oci_parse($this->connection, $query);
        oci_bind_by_name($stid, ':id', $id);
        
        if (!oci_execute($stid, OCI_COMMIT_ON_SUCCESS)) {
            $e = oci_error($stid);
            oci_free_statement($stid);
            throw new Exception("Failed to delete leave: " . $e['message']);
        }
        
        $rowsAffected = oci_num_rows($stid);
        oci_free_statement($stid);
        
        return $rowsAffected > 0;
    }
    
    // Get all leave types
    public function getAllLeaveTypes() {
        $query = "SELECT * FROM LEAVE_TYPE ORDER BY leave_type_id";
        $stid = oci_parse($this->connection, $query);
        
        if (!oci_execute($stid)) {
            $e = oci_error($stid);
            throw new Exception("Query failed: " . $e['message']);
        }
        
        $types = [];
        while ($row = oci_fetch_assoc($stid)) {
            $types[] = $row;
        }
        
        oci_free_statement($stid);
        return $types;
    }
}