<?php
set_time_limit(30); // 30 second timeout
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../classes/controllers/EmployeeController.php';
require_once __DIR__ . '/../../classes/Response.php';

if ($_SERVER['REQUEST_METHOD'] === 'DELETE' || $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        $id = $data['employee_id'] ?? $_POST['employee_id'] ?? null;
    }
    
    $controller = new EmployeeController();
    $controller->destroy($id);
} else {
    Response::error('Method not allowed', 405);
}