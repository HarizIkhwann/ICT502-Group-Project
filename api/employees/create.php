<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../classes/controllers/EmployeeController.php';
require_once __DIR__ . '/../../classes/Response.php';

// Log the request method for debugging
error_log("Request method: " . $_SERVER['REQUEST_METHOD']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (empty($data)) {
        $data = $_POST;
    }
    
    $controller = new EmployeeController();
    $controller->create($data);
} else {
    Response::error('Method not allowed: ' . $_SERVER['REQUEST_METHOD'], 405);
}