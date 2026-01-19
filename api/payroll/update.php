<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../classes/controllers/PayrollController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (empty($data)) {
        $data = $_POST;
    }
    
    $id = $data['payroll_id'] ?? null;
    unset($data['payroll_id']);
    
    $controller = new PayrollController();
    $controller->update($id, $data);
} else {
    Response::error('Method not allowed', 405);
}
