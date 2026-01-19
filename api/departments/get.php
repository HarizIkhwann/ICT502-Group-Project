<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../classes/controllers/DepartmentController.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = $_GET['id'] ?? null;
    
    $controller = new DepartmentController();
    $controller->show($id);
} else {
    Response::error('Method not allowed', 405);
}