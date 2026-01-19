<?php
require_once __DIR__ . '/../Database.php';

class Payroll {
    private $connection;
    private $table = 'PAYROLL';
    
    public function __construct() {
        $db = Database::getInstance();
        $this->connection = $db->getConnection();
    }
    
    // Get all payroll records
    public function getAllPayrolls() {
        $query = "SELECT p.*, e.first_name, e.last_name
                  FROM {$this->table} p
                  INNER JOIN EMPLOYEE e ON p.employee_id = e.employee_id
                  ORDER BY p.payment_date DESC";
        
        $stid = oci_parse($this->connection, $query);
        oci_execute($stid);
        
        $payrolls = [];
        while ($row = oci_fetch_assoc($stid)) {
            $payrolls[] = $row;
        }
        
        oci_free_statement($stid);
        return $payrolls;
    }
    
    // Get payroll by ID
    public function getPayrollById($id) {
        $query = "SELECT p.*, e.first_name, e.last_name
                  FROM {$this->table} p
                  INNER JOIN EMPLOYEE e ON p.employee_id = e.employee_id
                  WHERE p.payroll_id = :id";
        
        $stid = oci_parse($this->connection, $query);
        oci_bind_by_name($stid, ':id', $id);
        oci_execute($stid);
        
        $result = oci_fetch_assoc($stid);
        oci_free_statement($stid);
        
        return $result;
    }
    
    // Get payrolls by employee
    public function getPayrollsByEmployee($employee_id) {
        $query = "SELECT p.*, e.first_name, e.last_name
                  FROM {$this->table} p
                  INNER JOIN EMPLOYEE e ON p.employee_id = e.employee_id
                  WHERE p.employee_id = :emp_id
                  ORDER BY p.payment_date DESC";
        
        $stid = oci_parse($this->connection, $query);
        oci_bind_by_name($stid, ':emp_id', $employee_id);
        oci_execute($stid);
        
        $payrolls = [];
        while ($row = oci_fetch_assoc($stid)) {
            $payrolls[] = $row;
        }
        
        oci_free_statement($stid);
        return $payrolls;
    }
    
    // Get payrolls by date range
    public function getPayrollsByDateRange($start_date, $end_date) {
        $query = "SELECT p.*, e.first_name, e.last_name
                  FROM {$this->table} p
                  INNER JOIN EMPLOYEE e ON p.employee_id = e.employee_id
                  WHERE p.payment_date BETWEEN TO_DATE(:start_date, 'YYYY-MM-DD') 
                        AND TO_DATE(:end_date, 'YYYY-MM-DD')
                  ORDER BY p.payment_date DESC";
        
        $stid = oci_parse($this->connection, $query);
        oci_bind_by_name($stid, ':start_date', $start_date);
        oci_bind_by_name($stid, ':end_date', $end_date);
        oci_execute($stid);
        
        $payrolls = [];
        while ($row = oci_fetch_assoc($stid)) {
            $payrolls[] = $row;
        }
        
        oci_free_statement($stid);
        return $payrolls;
    }
    
    // Create payroll record
    public function createPayroll($data) {
        $query = "INSERT INTO {$this->table} 
                  (employee_id, pay_period_start, pay_period_end, payment_date, 
                   gross_pay, allowances, overtime_pay, deductions, tax_amount, 
                   net_pay, payment_method) 
                  VALUES (:emp_id, TO_DATE(:period_start, 'YYYY-MM-DD'), 
                          TO_DATE(:period_end, 'YYYY-MM-DD'), TO_DATE(:payment_date, 'YYYY-MM-DD'),
                          :gross_pay, :allowances, :overtime_pay, :deductions, 
                          :tax_amount, :net_pay, :payment_method)";
        
        $stid = oci_parse($this->connection, $query);
        
        oci_bind_by_name($stid, ':emp_id', $data['employee_id']);
        oci_bind_by_name($stid, ':period_start', $data['pay_period_start']);
        oci_bind_by_name($stid, ':period_end', $data['pay_period_end']);
        oci_bind_by_name($stid, ':payment_date', $data['payment_date']);
        oci_bind_by_name($stid, ':gross_pay', $data['gross_pay']);
        oci_bind_by_name($stid, ':allowances', $data['allowances']);
        oci_bind_by_name($stid, ':overtime_pay', $data['overtime_pay']);
        oci_bind_by_name($stid, ':deductions', $data['deductions']);
        oci_bind_by_name($stid, ':tax_amount', $data['tax_amount']);
        oci_bind_by_name($stid, ':net_pay', $data['net_pay']);
        oci_bind_by_name($stid, ':payment_method', $data['payment_method']);
        
        if (!oci_execute($stid, OCI_COMMIT_ON_SUCCESS)) {
            $e = oci_error($stid);
            oci_free_statement($stid);
            throw new Exception("Failed to create payroll: " . $e['message']);
        }
        
        oci_free_statement($stid);
        return true;
    }
    
    // Update payroll record
    public function updatePayroll($id, $data) {
        $query = "UPDATE {$this->table} 
                  SET pay_period_start = TO_DATE(:period_start, 'YYYY-MM-DD'),
                      pay_period_end = TO_DATE(:period_end, 'YYYY-MM-DD'),
                      payment_date = TO_DATE(:payment_date, 'YYYY-MM-DD'),
                      gross_pay = :gross_pay,
                      allowances = :allowances,
                      overtime_pay = :overtime_pay,
                      deductions = :deductions,
                      tax_amount = :tax_amount,
                      net_pay = :net_pay,
                      payment_method = :payment_method,
                      last_modified_date = SYSDATE
                  WHERE payroll_id = :id";
        
        $stid = oci_parse($this->connection, $query);
        
        oci_bind_by_name($stid, ':period_start', $data['pay_period_start']);
        oci_bind_by_name($stid, ':period_end', $data['pay_period_end']);
        oci_bind_by_name($stid, ':payment_date', $data['payment_date']);
        oci_bind_by_name($stid, ':gross_pay', $data['gross_pay']);
        oci_bind_by_name($stid, ':allowances', $data['allowances']);
        oci_bind_by_name($stid, ':overtime_pay', $data['overtime_pay']);
        oci_bind_by_name($stid, ':deductions', $data['deductions']);
        oci_bind_by_name($stid, ':tax_amount', $data['tax_amount']);
        oci_bind_by_name($stid, ':net_pay', $data['net_pay']);
        oci_bind_by_name($stid, ':payment_method', $data['payment_method']);
        oci_bind_by_name($stid, ':id', $id);
        
        if (!oci_execute($stid, OCI_COMMIT_ON_SUCCESS)) {
            $e = oci_error($stid);
            oci_free_statement($stid);
            throw new Exception("Failed to update payroll: " . $e['message']);
        }
        
        $rowsAffected = oci_num_rows($stid);
        oci_free_statement($stid);
        
        return $rowsAffected > 0;
    }
    
    // Delete payroll record
    public function deletePayroll($id) {
        $query = "DELETE FROM {$this->table} WHERE payroll_id = :id";
        
        $stid = oci_parse($this->connection, $query);
        oci_bind_by_name($stid, ':id', $id);
        
        $result = oci_execute($stid, OCI_COMMIT_ON_SUCCESS);
        
        if (!$result) {
            $e = oci_error($stid);
            oci_free_statement($stid);
            throw new Exception("Failed to delete payroll: " . $e['message']);
        }
        
        $rowsAffected = oci_num_rows($stid);
        oci_free_statement($stid);
        
        return $rowsAffected > 0;
    }
}
