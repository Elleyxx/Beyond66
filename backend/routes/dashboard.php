<?php

require_once __DIR__ . '/../controllers/DashboardController.php';

$controller = new DashboardController();

if (($uri === '/api/dashboard' || $uri === '/api/dashboard/profile') && $method === 'GET') {
    $controller->profile();
    exit;
}

Response::json(['success' => false, 'message' => 'Dashboard route not found'], 404);
