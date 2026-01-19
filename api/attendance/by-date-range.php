<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../classes/controllers/AttendanceController.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $start_date = $_GET['start_date'] ?? null;
    $end_date = $_GET['end_date'] ?? null;
    
    $controller = new AttendanceController();
    $controller->getByDateRange($start_date, $end_date);
} else {
    Response::error('Method not allowed', 405);
}