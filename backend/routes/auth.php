<?php

require_once __DIR__ . '/../controllers/AuthController.php';

$controller = new AuthController();
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

if ($uri === '/api/auth/login' && $method === 'POST') {
    $controller->login();
    return;
}

if ($uri === '/api/auth/register' && $method === 'POST') {
    $controller->register();
    return;
}

http_response_code(404);
echo json_encode(['success' => false, 'message' => 'Auth route not found']);
