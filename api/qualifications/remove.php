<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../classes/controllers/QualificationController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $employee_id = $_GET['employee_id'] ?? null;
    $qualification_id = $_GET['qualification_id'] ?? null;
    
    if (!$employee_id || !$qualification_id) {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        $employee_id = $employee_id ?? $data['employee_id'] ?? null;
        $qualification_id = $qualification_id ?? $data['qualification_id'] ?? null;
    }
    
    $controller = new QualificationController();
    $controller->remove($employee_id, $qualification_id);
} else {
    Response::error('Method not allowed', 405);
}