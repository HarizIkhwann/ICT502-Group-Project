<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../classes/controllers/LeaveController.php';

if ($_SERVER['REQUEST_METHOD'] === 'DELETE' || $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        $id = $data['leave_id'] ?? $_POST['leave_id'] ?? null;
    }
    
    $controller = new LeaveController();
    $controller->destroy($id);
} else {
    Response::error('Method not allowed', 405);
}