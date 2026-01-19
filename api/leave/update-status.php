<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../classes/controllers/LeaveController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (empty($data)) {
        $data = $_POST;
    }
    
    $id = $data['leave_id'] ?? $_GET['id'] ?? null;
    $status = $data['status'] ?? null;
    $approved_by = $data['approved_by'] ?? null;
    
    $controller = new LeaveController();
    $controller->updateStatus($id, $status, $approved_by);
} else {
    Response::error('Method not allowed', 405);
}