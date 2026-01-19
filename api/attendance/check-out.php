<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../classes/controllers/AttendanceController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (empty($data)) {
        $data = $_POST;
    }
    
    $id = $data['attendance_id'] ?? $_GET['id'] ?? null;
    $check_out_time = $data['check_out_time'] ?? null;
    
    $controller = new AttendanceController();
    $controller->checkOut($id, $check_out_time);
} else {
    Response::error('Method not allowed', 405);
}