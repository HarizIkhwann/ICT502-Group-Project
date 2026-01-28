<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/Response.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (empty($data)) {
        $data = $_POST;
    }
    
    try {
        // Validate required fields
        $required = ['employee_id', 'attendance_date', 'check_in_time'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                Response::error("Field '{$field}' is required", 400);
            }
        }
        
        // Get database connection
        $db = Database::getInstance();
        $connection = $db->getConnection();
        
        // Prepare insert query with attendance_date from user input
        $query = "INSERT INTO ATTENDANCE 
                  (employee_id, attendance_date, check_in_time, check_out_time, remarks) 
                  VALUES (:emp_id, TO_DATE(:att_date, 'YYYY-MM-DD'), 
                          TO_TIMESTAMP(:check_in, 'HH24:MI:SS'), 
                          " . (isset($data['check_out_time']) && !empty($data['check_out_time']) 
                              ? "TO_TIMESTAMP(:check_out, 'HH24:MI:SS')" 
                              : "NULL") . ", 
                          :remarks)";
        
        $stid = oci_parse($connection, $query);
        
        oci_bind_by_name($stid, ':emp_id', $data['employee_id']);
        oci_bind_by_name($stid, ':att_date', $data['attendance_date']);
        oci_bind_by_name($stid, ':check_in', $data['check_in_time']);
        
        if (isset($data['check_out_time']) && !empty($data['check_out_time'])) {
            oci_bind_by_name($stid, ':check_out', $data['check_out_time']);
        }
        
        $remarks = $data['remarks'] ?? null;
        oci_bind_by_name($stid, ':remarks', $remarks);
        
        if (!oci_execute($stid, OCI_COMMIT_ON_SUCCESS)) {
            $e = oci_error($stid);
            oci_free_statement($stid);
            throw new Exception("Failed to create attendance: " . $e['message']);
        }
        
        oci_free_statement($stid);
        Response::success(null, 'Attendance record created successfully');
        
    } catch (Exception $e) {
        Response::serverError($e->getMessage());
    }
} else {
    Response::error('Method not allowed', 405);
}
