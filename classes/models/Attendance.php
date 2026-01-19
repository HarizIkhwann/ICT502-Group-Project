<?php
require_once __DIR__ . '/../Database.php';

class Attendance {
    private $db;
    private $connection;
    
    private $table = 'ATTENDANCE';
    
    public $attendance_id;
    public $employee_id;
    public $attendance_date;
    public $check_in_time;
    public $check_out_time;
    public $status;
    public $remarks;
    public $created_date;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->connection = $this->db->getConnection();
    }
    
    // Get all attendance records
    public function getAllAttendance() {
        $query = "SELECT a.*, e.first_name, e.last_name 
                  FROM {$this->table} a
                  INNER JOIN EMPLOYEE e ON a.employee_id = e.employee_id
                  ORDER BY a.attendance_date DESC, a.attendance_id DESC";
        
        $stid = oci_parse($this->connection, $query);
        
        if (!oci_execute($stid)) {
            $e = oci_error($stid);
            throw new Exception("Query failed: " . $e['message']);
        }
        
        $records = [];
        while ($row = oci_fetch_assoc($stid)) {
            $records[] = $row;
        }
        
        oci_free_statement($stid);
        return $records;
    }
    
    // Get attendance by ID
    public function getAttendanceById($id) {
        $query = "SELECT a.*, e.first_name, e.last_name 
                  FROM {$this->table} a
                  INNER JOIN EMPLOYEE e ON a.employee_id = e.employee_id
                  WHERE a.attendance_id = :id";
        
        $stid = oci_parse($this->connection, $query);
        oci_bind_by_name($stid, ':id', $id);
        
        if (!oci_execute($stid)) {
            $e = oci_error($stid);
            throw new Exception("Query failed: " . $e['message']);
        }
        
        $record = oci_fetch_assoc($stid);
        oci_free_statement($stid);
        
        return $record ? $record : null;
    }
    
    // Get attendance by employee
    public function getAttendanceByEmployee($employee_id) {
        $query = "SELECT * FROM {$this->table} 
                  WHERE employee_id = :emp_id 
                  ORDER BY attendance_date DESC";
        
        $stid = oci_parse($this->connection, $query);
        oci_bind_by_name($stid, ':emp_id', $employee_id);
        
        if (!oci_execute($stid)) {
            $e = oci_error($stid);
            throw new Exception("Query failed: " . $e['message']);
        }
        
        $records = [];
        while ($row = oci_fetch_assoc($stid)) {
            $records[] = $row;
        }
        
        oci_free_statement($stid);
        return $records;
    }
    
    // Get attendance by date range
    public function getAttendanceByDateRange($start_date, $end_date) {
        $query = "SELECT a.*, e.first_name, e.last_name 
                  FROM {$this->table} a
                  INNER JOIN EMPLOYEE e ON a.employee_id = e.employee_id
                  WHERE a.attendance_date BETWEEN TO_DATE(:start_date, 'YYYY-MM-DD') 
                  AND TO_DATE(:end_date, 'YYYY-MM-DD')
                  ORDER BY a.attendance_date DESC, e.first_name";
        
        $stid = oci_parse($this->connection, $query);
        oci_bind_by_name($stid, ':start_date', $start_date);
        oci_bind_by_name($stid, ':end_date', $end_date);
        
        if (!oci_execute($stid)) {
            $e = oci_error($stid);
            throw new Exception("Query failed: " . $e['message']);
        }
        
        $records = [];
        while ($row = oci_fetch_assoc($stid)) {
            $records[] = $row;
        }
        
        oci_free_statement($stid);
        return $records;
    }
    
    // Check in
    public function checkIn($data) {
        $query = "INSERT INTO {$this->table} 
                  (employee_id, attendance_date, check_in_time, remarks) 
                  VALUES (:emp_id, TRUNC(SYSDATE), 
                          TO_TIMESTAMP(:check_in, 'HH24:MI:SS'), :remarks)";
        
        $stid = oci_parse($this->connection, $query);
        
        oci_bind_by_name($stid, ':emp_id', $data['employee_id']);
        oci_bind_by_name($stid, ':check_in', $data['check_in_time']);
        oci_bind_by_name($stid, ':remarks', $data['remarks']);
        
        if (!oci_execute($stid, OCI_COMMIT_ON_SUCCESS)) {
            $e = oci_error($stid);
            oci_free_statement($stid);
            throw new Exception("Failed to check in: " . $e['message']);
        }
        
        oci_free_statement($stid);
        return true;
    }
    
    // Check out
    public function checkOut($id, $check_out_time) {
        $query = "UPDATE {$this->table} 
                  SET check_out_time = TO_TIMESTAMP(:check_out, 'HH24:MI:SS')
                  WHERE attendance_id = :id";
        
        $stid = oci_parse($this->connection, $query);
        
        oci_bind_by_name($stid, ':check_out', $check_out_time);
        oci_bind_by_name($stid, ':id', $id);
        
        if (!oci_execute($stid, OCI_COMMIT_ON_SUCCESS)) {
            $e = oci_error($stid);
            oci_free_statement($stid);
            throw new Exception("Failed to check out: " . $e['message']);
        }
        
        $rowsAffected = oci_num_rows($stid);
        oci_free_statement($stid);
        
        return $rowsAffected > 0;
    }
    
    // Update attendance
    public function updateAttendance($id, $data) {
        $query = "UPDATE {$this->table} 
                  SET attendance_date = TO_DATE(:att_date, 'YYYY-MM-DD'),
                      check_in_time = TO_TIMESTAMP(:check_in, 'HH24:MI:SS'),
                      check_out_time = TO_TIMESTAMP(:check_out, 'HH24:MI:SS'),
                      remarks = :remarks
                  WHERE attendance_id = :id";
        
        $stid = oci_parse($this->connection, $query);
        
        oci_bind_by_name($stid, ':att_date', $data['attendance_date']);
        oci_bind_by_name($stid, ':check_in', $data['check_in_time']);
        oci_bind_by_name($stid, ':check_out', $data['check_out_time']);
        oci_bind_by_name($stid, ':remarks', $data['remarks']);
        oci_bind_by_name($stid, ':id', $id);
        
        if (!oci_execute($stid, OCI_COMMIT_ON_SUCCESS)) {
            $e = oci_error($stid);
            oci_free_statement($stid);
            throw new Exception("Failed to update attendance: " . $e['message']);
        }
        
        $rowsAffected = oci_num_rows($stid);
        oci_free_statement($stid);
        
        return $rowsAffected > 0;
    }
    
    // Delete attendance
    public function deleteAttendance($id) {
        $query = "DELETE FROM {$this->table} WHERE attendance_id = :id";
        $stid = oci_parse($this->connection, $query);
        oci_bind_by_name($stid, ':id', $id);
        
        if (!oci_execute($stid, OCI_COMMIT_ON_SUCCESS)) {
            $e = oci_error($stid);
            oci_free_statement($stid);
            throw new Exception("Failed to delete attendance: " . $e['message']);
        }
        
        $rowsAffected = oci_num_rows($stid);
        oci_free_statement($stid);
        
        return $rowsAffected > 0;
    }
}