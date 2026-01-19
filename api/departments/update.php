<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, PUT');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../classes/controllers/DepartmentController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // If JSON parsing fails, try regular POST
    if (empty($data)) {
        $data = $_POST;
    }
    
    $id = $data['department_id'] ?? $_GET['id'] ?? null;
    
    $controller = new DepartmentController();
    $controller->update($id, $data);
} else {
    Response::error('Method not allowed', 405);
}