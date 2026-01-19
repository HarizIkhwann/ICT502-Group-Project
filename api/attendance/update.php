<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, PUT');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../classes/controllers/AttendanceController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (empty($data)) {
        $data = $_POST;
    }
    
    $id = $data['attendance_id'] ?? $_GET['id'] ?? null;
    
    $controller = new AttendanceController();
    $controller->update($id, $data);
} else {
    Response::error('Method not allowed', 405);
}