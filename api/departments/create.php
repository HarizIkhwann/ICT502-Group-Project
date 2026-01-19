<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../classes/controllers/DepartmentController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get raw POST data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    // Fallback to $_POST if JSON is empty
    if (empty($data)) {
        $data = $_POST;
    }
    
    // Debug: Log what we received
    error_log("Received data: " . print_r($data, true));
    
    $controller = new DepartmentController();
    $controller->create($data);
} else {
    Response::error('Method not allowed', 405);
}