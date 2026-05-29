<?php

require_once __DIR__ . '/../controllers/CountryController.php';

$controller = new CountryController();
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET' && $uri === '/api/countries') {
    $controller->index();
    return;
}

if ($method === 'GET' && preg_match('#^/api/countries/([a-z0-9-]+)$#i', $uri, $matches)) {
    $controller->show($matches[1]);
    return;
}

http_response_code(404);
echo json_encode(['success' => false, 'message' => 'Countries route not found']);
