<?php
require_once __DIR__ . '/../Database.php';

class Employee {
    private $db;
    private $connection;
    
    private $table = 'EMPLOYEE';
    
    public $employee_id;
    public $first_name;
    public $last_name;
    public $email;
    public $phone_number;
    public $hire_date;
    public $position_id;  // Changed from job_id
    public $salary;
    public $department_id;
    public $supervisor_id;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->connection = $this->db->getConnection();
    }
    
    // Get all employees with department and job info
    public function getAllEmployees() {
        $query = "SELECT e.*, d.department_name, p.position_title,
                  ce.contract_start_date, ce.contract_end_date
                  FROM {$this->table} e
                  LEFT JOIN DEPARTMENT d ON e.department_id = d.department_id
                  LEFT JOIN POSITION p ON e.position_id = p.position_id
                  LEFT JOIN CONTRACT_EMPLOYEE ce ON e.employee_id = ce.employee_id
                  ORDER BY e.employee_id";
        
        $stid = oci_parse($this->connection, $query);
        
        if (!oci_execute($stid)) {
            $e = oci_error($stid);
            throw new Exception("Query failed: " . $e['message']);
        }
        
        $employees = [];
        while ($row = oci_fetch_assoc($stid)) {
            $employees[] = $row;
        }
        
        oci_free_statement($stid);
        return $employees;
    }
    
    // Get single employee by ID
    public function getEmployeeById($id) {
        $query = "SELECT e.*, d.department_name, p.position_title,
                  ce.contract_start_date, ce.contract_end_date
                  FROM {$this->table} e
                  LEFT JOIN DEPARTMENT d ON e.department_id = d.department_id
                  LEFT JOIN POSITION p ON e.position_id = p.position_id
                  LEFT JOIN CONTRACT_EMPLOYEE ce ON e.employee_id = ce.employee_id
                  WHERE e.employee_id = :id";
        
        $stid = oci_parse($this->connection, $query);
        oci_bind_by_name($stid, ':id', $id);
        
        if (!oci_execute($stid)) {
            $e = oci_error($stid);
            throw new Exception("Query failed: " . $e['message']);
        }
        
        $employee = oci_fetch_assoc($stid);
        oci_free_statement($stid);
        
        return $employee ? $employee : null;
    }
    
    // Get employees by department
    public function getEmployeesByDepartment($department_id) {
        $query = "SELECT e.*, p.position_title,
                  ce.contract_start_date, ce.contract_end_date
                  FROM {$this->table} e
                  LEFT JOIN POSITION p ON e.position_id = p.position_id
                  LEFT JOIN CONTRACT_EMPLOYEE ce ON e.employee_id = ce.employee_id
                  WHERE e.department_id = :dept_id
                  ORDER BY e.employee_id";
        
        $stid = oci_parse($this->connection, $query);
        oci_bind_by_name($stid, ':dept_id', $department_id);
        
        if (!oci_execute($stid)) {
            $e = oci_error($stid);
            throw new Exception("Query failed: " . $e['message']);
        }
        
        $employees = [];
        while ($row = oci_fetch_assoc($stid)) {
            $employees[] = $row;
        }
        
        oci_free_statement($stid);
        return $employees;
    }
    
    // Create new employee
    public function createEmployee($data) {
        // Insert into main EMPLOYEE table with RETURNING clause to get new ID
        $query = "INSERT INTO {$this->table} 
                  (first_name, last_name, email, phone_number, hire_date, 
                   position_id, department_id, supervisor_id, employment_type) 
                  VALUES (:first_name, :last_name, :email, :phone, SYSDATE, 
                          :position_id, :dept_id, :supervisor_id, :employment_type)
                  RETURNING employee_id INTO :new_id";
        
        $stid = oci_parse($this->connection, $query);
        $new_id = null;
        
        oci_bind_by_name($stid, ':first_name', $data['first_name']);
        oci_bind_by_name($stid, ':last_name', $data['last_name']);
        oci_bind_by_name($stid, ':email', $data['email']);
        oci_bind_by_name($stid, ':phone', $data['phone_number']);
        oci_bind_by_name($stid, ':position_id', $data['position_id']);
        oci_bind_by_name($stid, ':dept_id', $data['department_id']);
        oci_bind_by_name($stid, ':supervisor_id', $data['supervisor_id']);
        oci_bind_by_name($stid, ':employment_type', $data['employment_type']);
        oci_bind_by_name($stid, ':new_id', $new_id, 32);
        
        if (!oci_execute($stid)) {
            $e = oci_error($stid);
            oci_free_statement($stid);
            throw new Exception("Failed to create employee: " . $e['message']);
        }
        oci_free_statement($stid);
        
        // Insert into subtype table based on employment_type
        if ($data['employment_type'] === 'PERMANENT') {
            $subQuery = "INSERT INTO PERMANENT_EMPLOYEE (employee_id, annual_salary, vacation_days) 
                         VALUES (:emp_id, :salary, :vacation)";
            $subStid = oci_parse($this->connection, $subQuery);
            
            $salary = $data['annual_salary'] ?? 0;
            $vacation = $data['vacation_days'] ?? 14;
            
            oci_bind_by_name($subStid, ':emp_id', $new_id);
            oci_bind_by_name($subStid, ':salary', $salary);
            oci_bind_by_name($subStid, ':vacation', $vacation);
            
            if (!oci_execute($subStid, OCI_COMMIT_ON_SUCCESS)) {
                $e = oci_error($subStid);
                oci_free_statement($subStid);
                throw new Exception("Failed to create permanent employee record: " . $e['message']);
            }
            oci_free_statement($subStid);
            
        } else if ($data['employment_type'] === 'CONTRACT') {
            $subQuery = "INSERT INTO CONTRACT_EMPLOYEE (employee_id, hourly_rate, contract_start_date, contract_end_date, agency_name) 
                         VALUES (:emp_id, :rate, :start_date, :end_date, :agency)";
            $subStid = oci_parse($this->connection, $subQuery);
            
            $rate = $data['hourly_rate'] ?? 0;
            $start = $data['contract_start_date'] ?? date('Y-m-d');
            $end = $data['contract_end_date'] ?? date('Y-m-d', strtotime('+1 year'));
            $agency = $data['agency_name'] ?? 'N/A';
            
            oci_bind_by_name($subStid, ':emp_id', $new_id);
            oci_bind_by_name($subStid, ':rate', $rate);
            oci_bind_by_name($subStid, ':start_date', $start);
            oci_bind_by_name($subStid, ':end_date', $end);
            oci_bind_by_name($subStid, ':agency', $agency);
            
            if (!oci_execute($subStid, OCI_COMMIT_ON_SUCCESS)) {
                $e = oci_error($subStid);
                oci_free_statement($subStid);
                throw new Exception("Failed to create contract employee record: " . $e['message']);
            }
            oci_free_statement($subStid);
        } else {
            oci_commit($this->connection);
        }
        
        return $new_id;
    }
    
    // Update employee
    public function updateEmployee($id, $data) {
        $query = "UPDATE {$this->table} 
                  SET first_name = :first_name, 
                      last_name = :last_name, 
                      email = :email, 
                      phone_number = :phone,
                      position_id = :position_id, 
                      department_id = :dept_id, 
                      supervisor_id = :supervisor_id,
                      employment_type = :employment_type
                  WHERE employee_id = :id";
        
        $stid = oci_parse($this->connection, $query);
        
        oci_bind_by_name($stid, ':first_name', $data['first_name']);
        oci_bind_by_name($stid, ':last_name', $data['last_name']);
        oci_bind_by_name($stid, ':email', $data['email']);
        oci_bind_by_name($stid, ':phone', $data['phone_number']);
        oci_bind_by_name($stid, ':position_id', $data['position_id']);
        oci_bind_by_name($stid, ':dept_id', $data['department_id']);
        oci_bind_by_name($stid, ':supervisor_id', $data['supervisor_id']);
        oci_bind_by_name($stid, ':employment_type', $data['employment_type']);
        oci_bind_by_name($stid, ':id', $id);
        
        if (!oci_execute($stid, OCI_COMMIT_ON_SUCCESS)) {
            $e = oci_error($stid);
            oci_free_statement($stid);
            throw new Exception("Failed to update employee: " . $e['message']);
        }
        
        $rowsAffected = oci_num_rows($stid);
        oci_free_statement($stid);
        
        return $rowsAffected > 0;
    }
    
    // Delete employee
    public function deleteEmployee($id) {
        // First, delete from subtype tables (PERMANENT_EMPLOYEE and CONTRACT_EMPLOYEE)
        // This ensures clean deletion even if CASCADE is not properly configured
        
        $deletePermanent = "DELETE FROM PERMANENT_EMPLOYEE WHERE employee_id = :id";
        $stid1 = oci_parse($this->connection, $deletePermanent);
        oci_bind_by_name($stid1, ':id', $id);
        oci_execute($stid1); // Ignore errors if record doesn't exist
        oci_free_statement($stid1);
        
        $deleteContract = "DELETE FROM CONTRACT_EMPLOYEE WHERE employee_id = :id";
        $stid2 = oci_parse($this->connection, $deleteContract);
        oci_bind_by_name($stid2, ':id', $id);
        oci_execute($stid2); // Ignore errors if record doesn't exist
        oci_free_statement($stid2);
        
        // Now delete from main EMPLOYEE table
        $query = "DELETE FROM {$this->table} WHERE employee_id = :id";
        $stid = oci_parse($this->connection, $query);
        oci_bind_by_name($stid, ':id', $id);
        
        $success = oci_execute($stid);
        
        if (!$success) {
            $e = oci_error($stid);
            oci_free_statement($stid);
            oci_rollback($this->connection);
            throw new Exception("Failed to delete employee: " . $e['message']);
        }
        
        // Check rows affected before freeing
        $rowsAffected = oci_num_rows($stid);
        oci_free_statement($stid);
        
        // Commit all deletions
        oci_commit($this->connection);
        
        return $rowsAffected > 0;
    }
}