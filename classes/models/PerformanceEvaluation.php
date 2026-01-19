<?php
require_once __DIR__ . '/../Database.php';

class PerformanceEvaluation {
    private $connection;
    private $table = 'PERFORMANCE_EVALUATION';
    
    public function __construct() {
        $db = Database::getInstance();
        $this->connection = $db->getConnection();
    }
    
    // Get all evaluations
    public function getAllEvaluations() {
        $query = "SELECT pe.evaluation_id, pe.employee_id, pe.evaluator_id, 
                         pe.evaluation_date, pe.score, pe.status,
                         DBMS_LOB.SUBSTR(pe.strengths, 4000, 1) as strengths,
                         DBMS_LOB.SUBSTR(pe.weaknesses, 4000, 1) as weaknesses,
                         pe.goals, pe.created_date, pe.last_modified_date,
                         e1.first_name, e1.last_name,
                         e2.first_name as evaluator_first_name, 
                         e2.last_name as evaluator_last_name
                  FROM {$this->table} pe
                  INNER JOIN EMPLOYEE e1 ON pe.employee_id = e1.employee_id
                  INNER JOIN EMPLOYEE e2 ON pe.evaluator_id = e2.employee_id
                  ORDER BY pe.evaluation_date DESC";
        
        $stid = oci_parse($this->connection, $query);
        
        if (!oci_execute($stid)) {
            $e = oci_error($stid);
            oci_free_statement($stid);
            throw new Exception("Failed to get evaluations: " . $e['message']);
        }
        
        $evaluations = [];
        while ($row = oci_fetch_assoc($stid)) {
            $evaluations[] = $row;
        }
        
        oci_free_statement($stid);
        return $evaluations;
    }
    
    // Get evaluation by ID
    public function getEvaluationById($id) {
        $query = "SELECT pe.evaluation_id, pe.employee_id, pe.evaluator_id, 
                         pe.evaluation_date, pe.score, pe.status,
                         DBMS_LOB.SUBSTR(pe.strengths, 4000, 1) as strengths,
                         DBMS_LOB.SUBSTR(pe.weaknesses, 4000, 1) as weaknesses,
                         pe.goals, pe.created_date, pe.last_modified_date,
                         e1.first_name, e1.last_name,
                         e2.first_name as evaluator_first_name, 
                         e2.last_name as evaluator_last_name
                  FROM {$this->table} pe
                  INNER JOIN EMPLOYEE e1 ON pe.employee_id = e1.employee_id
                  INNER JOIN EMPLOYEE e2 ON pe.evaluator_id = e2.employee_id
                  WHERE pe.evaluation_id = :id";
        
        $stid = oci_parse($this->connection, $query);
        oci_bind_by_name($stid, ':id', $id);
        
        if (!oci_execute($stid)) {
            $e = oci_error($stid);
            oci_free_statement($stid);
            throw new Exception("Failed to get evaluation: " . $e['message']);
        }
        
        $result = oci_fetch_assoc($stid);
        oci_free_statement($stid);
        
        return $result;
    }
    
    // Get evaluations by employee
    public function getEvaluationsByEmployee($employee_id) {
        $query = "SELECT pe.evaluation_id, pe.employee_id, pe.evaluator_id, 
                         pe.evaluation_date, pe.score, pe.status,
                         DBMS_LOB.SUBSTR(pe.strengths, 4000, 1) as strengths,
                         DBMS_LOB.SUBSTR(pe.weaknesses, 4000, 1) as weaknesses,
                         pe.goals, pe.created_date, pe.last_modified_date,
                         e1.first_name, e1.last_name,
                         e2.first_name as evaluator_first_name, 
                         e2.last_name as evaluator_last_name
                  FROM {$this->table} pe
                  INNER JOIN EMPLOYEE e1 ON pe.employee_id = e1.employee_id
                  INNER JOIN EMPLOYEE e2 ON pe.evaluator_id = e2.employee_id
                  WHERE pe.employee_id = :emp_id
                  ORDER BY pe.evaluation_date DESC";
        
        $stid = oci_parse($this->connection, $query);
        oci_bind_by_name($stid, ':emp_id', $employee_id);
        
        if (!oci_execute($stid)) {
            $e = oci_error($stid);
            oci_free_statement($stid);
            throw new Exception("Failed to get evaluations: " . $e['message']);
        }
        
        $evaluations = [];
        while ($row = oci_fetch_assoc($stid)) {
            $evaluations[] = $row;
        }
        
        oci_free_statement($stid);
        return $evaluations;
    }
    
    // Get evaluations by status
    public function getEvaluationsByStatus($status) {
        $query = "SELECT pe.evaluation_id, pe.employee_id, pe.evaluator_id, 
                         pe.evaluation_date, pe.score, pe.status,
                         DBMS_LOB.SUBSTR(pe.strengths, 4000, 1) as strengths,
                         DBMS_LOB.SUBSTR(pe.weaknesses, 4000, 1) as weaknesses,
                         pe.goals, pe.created_date, pe.last_modified_date,
                         e1.first_name, e1.last_name,
                         e2.first_name as evaluator_first_name, 
                         e2.last_name as evaluator_last_name
                  FROM {$this->table} pe
                  INNER JOIN EMPLOYEE e1 ON pe.employee_id = e1.employee_id
                  INNER JOIN EMPLOYEE e2 ON pe.evaluator_id = e2.employee_id
                  WHERE pe.status = :status
                  ORDER BY pe.evaluation_date DESC";
        
        $stid = oci_parse($this->connection, $query);
        oci_bind_by_name($stid, ':status', $status);
        
        if (!oci_execute($stid)) {
            $e = oci_error($stid);
            oci_free_statement($stid);
            throw new Exception("Failed to get evaluations: " . $e['message']);
        }
        
        $evaluations = [];
        while ($row = oci_fetch_assoc($stid)) {
            $evaluations[] = $row;
        }
        
        oci_free_statement($stid);
        return $evaluations;
    }
    
    // Create evaluation
    public function createEvaluation($data) {
        $query = "INSERT INTO {$this->table} 
                  (employee_id, evaluator_id, evaluation_date, score, status, 
                   strengths, weaknesses, goals) 
                  VALUES (:emp_id, :evaluator_id, TO_DATE(:eval_date, 'YYYY-MM-DD'), 
                          :score, :status, :strengths, :weaknesses, :goals)";
        
        $stid = oci_parse($this->connection, $query);
        
        oci_bind_by_name($stid, ':emp_id', $data['employee_id']);
        oci_bind_by_name($stid, ':evaluator_id', $data['evaluator_id']);
        oci_bind_by_name($stid, ':eval_date', $data['evaluation_date']);
        oci_bind_by_name($stid, ':score', $data['score']);
        oci_bind_by_name($stid, ':status', $data['status']);
        oci_bind_by_name($stid, ':strengths', $data['strengths']);
        oci_bind_by_name($stid, ':weaknesses', $data['weaknesses']);
        oci_bind_by_name($stid, ':goals', $data['goals']);
        
        if (!oci_execute($stid, OCI_COMMIT_ON_SUCCESS)) {
            $e = oci_error($stid);
            oci_free_statement($stid);
            throw new Exception("Failed to create evaluation: " . $e['message']);
        }
        
        oci_free_statement($stid);
        return true;
    }
    
    // Update evaluation status
    public function updateEvaluationStatus($id, $status) {
        $query = "UPDATE {$this->table} 
                  SET status = :status,
                      last_modified_date = SYSDATE
                  WHERE evaluation_id = :id";
        
        $stid = oci_parse($this->connection, $query);
        
        oci_bind_by_name($stid, ':status', $status);
        oci_bind_by_name($stid, ':id', $id);
        
        if (!oci_execute($stid, OCI_COMMIT_ON_SUCCESS)) {
            $e = oci_error($stid);
            oci_free_statement($stid);
            throw new Exception("Failed to update evaluation status: " . $e['message']);
        }
        
        $rowsAffected = oci_num_rows($stid);
        oci_free_statement($stid);
        
        return $rowsAffected > 0;
    }
    
    // Update evaluation
    public function updateEvaluation($id, $data) {
        $query = "UPDATE {$this->table} 
                  SET evaluation_date = TO_DATE(:eval_date, 'YYYY-MM-DD'),
                      score = :score,
                      status = :status,
                      strengths = :strengths,
                      weaknesses = :weaknesses,
                      goals = :goals,
                      last_modified_date = SYSDATE
                  WHERE evaluation_id = :id";
        
        $stid = oci_parse($this->connection, $query);
        
        oci_bind_by_name($stid, ':eval_date', $data['evaluation_date']);
        oci_bind_by_name($stid, ':score', $data['score']);
        oci_bind_by_name($stid, ':status', $data['status']);
        oci_bind_by_name($stid, ':strengths', $data['strengths']);
        oci_bind_by_name($stid, ':weaknesses', $data['weaknesses']);
        oci_bind_by_name($stid, ':goals', $data['goals']);
        oci_bind_by_name($stid, ':id', $id);
        
        if (!oci_execute($stid, OCI_COMMIT_ON_SUCCESS)) {
            $e = oci_error($stid);
            oci_free_statement($stid);
            throw new Exception("Failed to update evaluation: " . $e['message']);
        }
        
        $rowsAffected = oci_num_rows($stid);
        oci_free_statement($stid);
        
        return $rowsAffected > 0;
    }
    
    // Delete evaluation
    public function deleteEvaluation($id) {
        $query = "DELETE FROM {$this->table} WHERE evaluation_id = :id";
        
        $stid = oci_parse($this->connection, $query);
        oci_bind_by_name($stid, ':id', $id);
        
        $result = oci_execute($stid, OCI_COMMIT_ON_SUCCESS);
        
        if (!$result) {
            $e = oci_error($stid);
            oci_free_statement($stid);
            throw new Exception("Failed to delete evaluation: " . $e['message']);
        }
        
        $rowsAffected = oci_num_rows($stid);
        oci_free_statement($stid);
        
        return $rowsAffected > 0;
    }
}
