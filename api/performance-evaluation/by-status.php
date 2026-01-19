<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../classes/controllers/PerformanceEvaluationController.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $status = $_GET['status'] ?? null;
    
    $controller = new PerformanceEvaluationController();
    $controller->getByStatus($status);
} else {
    Response::error('Method not allowed', 405);
}
