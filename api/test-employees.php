<?php
require_once __DIR__ . '/../config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    $query = "SELECT employee_id, first_name, last_name FROM EMPLOYEE ORDER BY employee_id FETCH FIRST 5 ROWS ONLY";
    $stid = oci_parse($conn, $query);
    
    if (!oci_execute($stid)) {
        $e = oci_error($stid);
        echo json_encode(['error' => $e['message']]);
        exit;
    }
    
    $employees = [];
    while ($row = oci_fetch_assoc($stid)) {
        $employees[] = $row;
    }
    
    oci_free_statement($stid);
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'employees' => $employees,
        'count' => count($employees)
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
