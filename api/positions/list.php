<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../classes/controllers/PositionController.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $controller = new PositionController();
    $controller->index();
} else {
    Response::error('Method not allowed', 405);
}