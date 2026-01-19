<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../classes/controllers/PayrollController.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $controller = new PayrollController();
    $controller->index();
} else {
    Response::error('Method not allowed', 405);
}
