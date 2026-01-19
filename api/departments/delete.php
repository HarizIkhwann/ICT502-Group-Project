<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../classes/controllers/DepartmentController.php';

if ($_SERVER['REQUEST_METHOD'] === 'DELETE' || $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_GET['id'] ?? null;
    
    // Try to get from request body if not in query string
    if (!$id) {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['department_id'] ?? $_POST['department_id'] ?? null;
    }
    
    $controller = new DepartmentController();
    $controller->destroy($id);
} else {
    Response::error('Method not allowed', 405);
}