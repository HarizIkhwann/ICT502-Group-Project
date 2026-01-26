<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, PUT');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../classes/controllers/EmployeeController.php';
require_once __DIR__ . '/../../classes/Response.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT') {
    $input = file_get_contents('php://input');
    error_log("Update request data: " . $input);
    $data = json_decode($input, true);
    
    if (empty($data)) {
        $data = $_POST;
    }
    
    $id = $data['employee_id'] ?? $_GET['id'] ?? null;
    error_log("Update employee ID: " . $id);
    error_log("Update data: " . json_encode($data));
    
    $controller = new EmployeeController();
    $controller->update($id, $data);
} else {
    Response::error('Method not allowed', 405);
}