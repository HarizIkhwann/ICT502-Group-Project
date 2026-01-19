<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../classes/controllers/LeaveController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    // Debug logging
    error_log("Leave Create API - Raw input: " . $input);
    error_log("Leave Create API - Decoded data: " . print_r($data, true));
    
    if (empty($data)) {
        $data = $_POST;
        error_log("Leave Create API - Using POST data: " . print_r($data, true));
    }
    
    $controller = new LeaveController();
    $controller->create($data);
} else {
    Response::error('Method not allowed', 405);
}