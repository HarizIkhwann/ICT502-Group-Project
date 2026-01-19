<?php
require_once __DIR__ . '/../Database.php';

class Department {
    private $db;
    private $connection;

    private $table = 'DEPARTMENT';

    public $department_id;
    public $department_name;
    public $location;
    public $budget;
    public $manager_id;
    public $created_date;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->connection = $this->db->getConnection();
    }

    // Fetch all departments
    public function getAllDepartments() {
        $query = "SELECT d.department_id, d.department_name, d.location, 
                         d.budget, d.manager_id, d.created_date,
                         e.first_name, e.last_name
                  FROM {$this->table} d
                  LEFT JOIN EMPLOYEE e ON d.manager_id = e.employee_id
                  ORDER BY d.department_id";
        $stid = oci_parse($this->connection, $query);

        if (!oci_execute($stid)) {
            $e = oci_error($stid);
            throw new Exception("Failed to fetch departments: " . $e['message']);
        }

        $departments = [];
        while ($row = oci_fetch_assoc($stid)) {
            // Build manager name if exists
            if ($row['FIRST_NAME'] && $row['LAST_NAME']) {
                $row['MANAGER_NAME'] = $row['FIRST_NAME'] . ' ' . $row['LAST_NAME'];
            } else {
                $row['MANAGER_NAME'] = null;
            }
            $departments[] = $row;
        }

        oci_free_statement($stid);
        return $departments;
    }

    // Fetch a single department by ID
    public function getDepartmentById($id) {
        $query = "SELECT d.department_id, d.department_name, d.location, 
                         d.budget, d.manager_id, d.created_date,
                         e.first_name, e.last_name
                  FROM {$this->table} d
                  LEFT JOIN EMPLOYEE e ON d.manager_id = e.employee_id
                  WHERE d.department_id = :id";
        $stid = oci_parse($this->connection, $query);
        oci_bind_by_name($stid, ':id', $id);

        if (!oci_execute($stid)) {
            $e = oci_error($stid);
            throw new Exception("Failed to fetch department: " . $e['message']);
        }

        $department = oci_fetch_assoc($stid);
        
        if ($department && $department['FIRST_NAME'] && $department['LAST_NAME']) {
            $department['MANAGER_NAME'] = $department['FIRST_NAME'] . ' ' . $department['LAST_NAME'];
        } else if ($department) {
            $department['MANAGER_NAME'] = null;
        }
        
        oci_free_statement($stid);
        return $department ? $department : null;
    }

    // Create a new department
    public function createDepartment($data) {
        $query = "INSERT INTO {$this->table} 
                  (department_name, location, budget, manager_id, created_date) 
                  VALUES (:name, :location, :budget, :manager_id, SYSDATE)";
        
        $stid = oci_parse($this->connection, $query);
        
        oci_bind_by_name($stid, ':name', $data['department_name']);
        oci_bind_by_name($stid, ':location', $data['location']);
        oci_bind_by_name($stid, ':budget', $data['budget']);
        oci_bind_by_name($stid, ':manager_id', $data['manager_id']);
        
        if (!oci_execute($stid, OCI_COMMIT_ON_SUCCESS)) {
            $e = oci_error($stid);
            oci_free_statement($stid);
            throw new Exception("Failed to create department: " . $e['message']);
        }
        
        oci_free_statement($stid);
        return true;
    }

    // Update an existing department
    public function updateDepartment($id, $data) {
        $query = "UPDATE {$this->table} 
                  SET department_name = :department_name, location = :location, budget = :budget, manager_id = :manager_id
                  WHERE department_id = :id";

        $stid = oci_parse($this->connection, $query);

        oci_bind_by_name($stid, ':department_name', $data['department_name']);
        oci_bind_by_name($stid, ':location', $data['location']);
        oci_bind_by_name($stid, ':budget', $data['budget']);
        oci_bind_by_name($stid, ':manager_id', $data['manager_id']);
        oci_bind_by_name($stid, ':id', $id);

        if (!oci_execute($stid, OCI_COMMIT_ON_SUCCESS)) {
            $e = oci_error($stid);
            throw new Exception("Failed to update department: " . $e['message']);
        }

        $rowsAffected = oci_num_rows($stid);
        oci_free_statement($stid);
        return $rowsAffected > 0;
    }

    // Delete a department
    public function deleteDepartment($id) {
        $query = "DELETE FROM {$this->table} WHERE department_id = :id";
        $stid = oci_parse($this->connection, $query);
        oci_bind_by_name($stid, ':id', $id);

        if (!oci_execute($stid, OCI_COMMIT_ON_SUCCESS)) {
            $e = oci_error($stid);
            throw new Exception("Failed to delete department: " . $e['message']);
        }

        $rowsAffected = oci_num_rows($stid);
        oci_free_statement($stid);
        return $rowsAffected > 0;
    }
}