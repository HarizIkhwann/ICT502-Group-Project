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
                  ce.hourly_rate, ce.contract_start_date, ce.contract_end_date,
                  pe.annual_salary, pe.vacation_days,
                  s.first_name || ' ' || s.last_name AS supervisor_name
                  FROM {$this->table} e
                  LEFT JOIN DEPARTMENT d ON e.department_id = d.department_id
                  LEFT JOIN POSITION p ON e.position_id = p.position_id
                  LEFT JOIN CONTRACT_EMPLOYEE ce ON e.employee_id = ce.employee_id
                  LEFT JOIN PERMANENT_EMPLOYEE pe ON e.employee_id = pe.employee_id
                  LEFT JOIN {$this->table} s ON e.supervisor_id = s.employee_id
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
                  ce.hourly_rate, ce.contract_start_date, ce.contract_end_date,
                  pe.annual_salary, pe.vacation_days,
                  s.first_name || ' ' || s.last_name AS supervisor_name
                  FROM {$this->table} e
                  LEFT JOIN DEPARTMENT d ON e.department_id = d.department_id
                  LEFT JOIN POSITION p ON e.position_id = p.position_id
                  LEFT JOIN CONTRACT_EMPLOYEE ce ON e.employee_id = ce.employee_id
                  LEFT JOIN PERMANENT_EMPLOYEE pe ON e.employee_id = pe.employee_id
                  LEFT JOIN {$this->table} s ON e.supervisor_id = s.employee_id
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
                  ce.hourly_rate, ce.contract_start_date, ce.contract_end_date,
                  pe.annual_salary, pe.vacation_days,
                  s.first_name || ' ' || s.last_name AS supervisor_name
                  FROM {$this->table} e
                  LEFT JOIN POSITION p ON e.position_id = p.position_id
                  LEFT JOIN CONTRACT_EMPLOYEE ce ON e.employee_id = ce.employee_id
                  LEFT JOIN PERMANENT_EMPLOYEE pe ON e.employee_id = pe.employee_id
                  LEFT JOIN {$this->table} s ON e.supervisor_id = s.employee_id
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
                  (first_name, last_name, email, phone_number, date_of_birth, address, hire_date, 
                   position_id, department_id, supervisor_id, employment_type) 
                  VALUES (:first_name, :last_name, :email, :phone, TO_DATE(:dob, 'YYYY-MM-DD'), :address, SYSDATE, 
                          :position_id, :dept_id, :supervisor_id, :employment_type)
                  RETURNING employee_id INTO :new_id";
        
        $stid = oci_parse($this->connection, $query);
        $new_id = null;
        
        oci_bind_by_name($stid, ':first_name', $data['first_name']);
        oci_bind_by_name($stid, ':last_name', $data['last_name']);
        oci_bind_by_name($stid, ':email', $data['email']);
        oci_bind_by_name($stid, ':phone', $data['phone_number']);
        oci_bind_by_name($stid, ':dob', $data['date_of_birth']);
        oci_bind_by_name($stid, ':address', $data['address']);
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
            $subQuery = "INSERT INTO CONTRACT_EMPLOYEE (employee_id, hourly_rate, contract_start_date, contract_end_date) 
                         VALUES (:emp_id, :rate, TO_DATE(:start_date, 'YYYY-MM-DD'), TO_DATE(:end_date, 'YYYY-MM-DD'))";
            $subStid = oci_parse($this->connection, $subQuery);
            
            $rate = $data['hourly_rate'] ?? 0;
            $start = $data['contract_start_date'] ?? date('Y-m-d');
            $end = $data['contract_end_date'] ?? date('Y-m-d', strtotime('+1 year'));
            
            oci_bind_by_name($subStid, ':emp_id', $new_id);
            oci_bind_by_name($subStid, ':rate', $rate);
            oci_bind_by_name($subStid, ':start_date', $start);
            oci_bind_by_name($subStid, ':end_date', $end);
            
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
        // Update main EMPLOYEE table
        $query = "UPDATE {$this->table} 
                  SET first_name = :first_name, 
                      last_name = :last_name, 
                      email = :email, 
                      phone_number = :phone,
                      date_of_birth = TO_DATE(:dob, 'YYYY-MM-DD'),
                      address = :address,
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
        oci_bind_by_name($stid, ':dob', $data['date_of_birth']);
        oci_bind_by_name($stid, ':address', $data['address']);
        oci_bind_by_name($stid, ':position_id', $data['position_id']);
        oci_bind_by_name($stid, ':dept_id', $data['department_id']);
        oci_bind_by_name($stid, ':supervisor_id', $data['supervisor_id']);
        oci_bind_by_name($stid, ':employment_type', $data['employment_type']);
        oci_bind_by_name($stid, ':id', $id);
        
        if (!oci_execute($stid)) {
            $e = oci_error($stid);
            oci_free_statement($stid);
            throw new Exception("Failed to update employee: " . $e['message']);
        }
        
        $rowsAffected = oci_num_rows($stid);
        oci_free_statement($stid);
        
        // Handle employment type subtype tables
        if ($data['employment_type'] === 'CONTRACT') {
            // Delete from PERMANENT_EMPLOYEE if exists
            $deletePerm = "DELETE FROM PERMANENT_EMPLOYEE WHERE employee_id = :id";
            $permStmt = oci_parse($this->connection, $deletePerm);
            oci_bind_by_name($permStmt, ':id', $id);
            oci_execute($permStmt);
            oci_free_statement($permStmt);
            
            // Insert or update CONTRACT_EMPLOYEE
            if (!empty($data['contract_start_date']) && 
                !empty($data['contract_end_date']) &&
                !empty($data['hourly_rate'])) {
                
                $contractQuery = "MERGE INTO CONTRACT_EMPLOYEE ce
                                USING (SELECT :emp_id AS employee_id FROM DUAL) src
                                ON (ce.employee_id = src.employee_id)
                                WHEN MATCHED THEN
                                    UPDATE SET 
                                        hourly_rate = :hourly_rate,
                                        contract_start_date = TO_DATE(:start_date, 'YYYY-MM-DD'),
                                        contract_end_date = TO_DATE(:end_date, 'YYYY-MM-DD')
                                WHEN NOT MATCHED THEN
                                    INSERT (employee_id, hourly_rate, contract_start_date, contract_end_date)
                                    VALUES (:emp_id2, :hourly_rate2, TO_DATE(:start_date2, 'YYYY-MM-DD'), TO_DATE(:end_date2, 'YYYY-MM-DD'))";
                
                $contractStmt = oci_parse($this->connection, $contractQuery);
                
                oci_bind_by_name($contractStmt, ':emp_id', $id);
                oci_bind_by_name($contractStmt, ':hourly_rate', $data['hourly_rate']);
                oci_bind_by_name($contractStmt, ':start_date', $data['contract_start_date']);
                oci_bind_by_name($contractStmt, ':end_date', $data['contract_end_date']);
                oci_bind_by_name($contractStmt, ':emp_id2', $id);
                oci_bind_by_name($contractStmt, ':hourly_rate2', $data['hourly_rate']);
                oci_bind_by_name($contractStmt, ':start_date2', $data['contract_start_date']);
                oci_bind_by_name($contractStmt, ':end_date2', $data['contract_end_date']);
                
                if (!oci_execute($contractStmt)) {
                    $e = oci_error($contractStmt);
                    oci_free_statement($contractStmt);
                    throw new Exception("Failed to update contract employee: " . $e['message']);
                }
                
                oci_free_statement($contractStmt);
            }
            
        } else if ($data['employment_type'] === 'PERMANENT') {
            // Delete from CONTRACT_EMPLOYEE if exists
            $deleteContract = "DELETE FROM CONTRACT_EMPLOYEE WHERE employee_id = :id";
            $contractStmt = oci_parse($this->connection, $deleteContract);
            oci_bind_by_name($contractStmt, ':id', $id);
            oci_execute($contractStmt);
            oci_free_statement($contractStmt);
            
            // Insert or update PERMANENT_EMPLOYEE
            $salary = $data['annual_salary'] ?? 0;
            $vacation = $data['vacation_days'] ?? 14;
            
            $permQuery = "MERGE INTO PERMANENT_EMPLOYEE pe
                         USING (SELECT :emp_id AS employee_id FROM DUAL) src
                         ON (pe.employee_id = src.employee_id)
                         WHEN MATCHED THEN
                             UPDATE SET 
                                 annual_salary = :salary,
                                 vacation_days = :vacation
                         WHEN NOT MATCHED THEN
                             INSERT (employee_id, annual_salary, vacation_days)
                             VALUES (:emp_id2, :salary2, :vacation2)";
            
            $permStmt = oci_parse($this->connection, $permQuery);
            
            oci_bind_by_name($permStmt, ':emp_id', $id);
            oci_bind_by_name($permStmt, ':salary', $salary);
            oci_bind_by_name($permStmt, ':vacation', $vacation);
            oci_bind_by_name($permStmt, ':emp_id2', $id);
            oci_bind_by_name($permStmt, ':salary2', $salary);
            oci_bind_by_name($permStmt, ':vacation2', $vacation);
            
            if (!oci_execute($permStmt)) {
                $e = oci_error($permStmt);
                oci_free_statement($permStmt);
                throw new Exception("Failed to update permanent employee: " . $e['message']);
            }
            
            oci_free_statement($permStmt);
        }
        
        // Commit all changes
        oci_commit($this->connection);
        
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