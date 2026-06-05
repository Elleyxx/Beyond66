<?php

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/bootstrap_database.php';

header('Content-Type: application/json; charset=utf-8');

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

if (str_starts_with($uri, '/api/auth')) {
    require_once __DIR__ . '/../routes/auth.php';
    exit;
}

if (str_starts_with($uri, '/api/countries')) {
    require_once __DIR__ . '/../routes/countries.php';
    exit;
}

if (str_starts_with($uri, '/api/planner')) {
    require_once __DIR__ . '/../routes/planner.php';
    exit;
}

if (str_starts_with($uri, '/api/community')) {
    require_once __DIR__ . '/../routes/community.php';
    exit;
}

if (str_starts_with($uri, '/api/dashboard')) {
    require_once __DIR__ . '/../routes/dashboard.php';
    exit;
}

if (str_starts_with($uri, '/api/saved-items')) {
    require_once __DIR__ . '/../routes/saved_items.php';
    exit;
}

if ($uri === '/api/health') {
    echo json_encode(['success' => true, 'status' => 'ok']);
    exit;
}

ensureDatabaseReady();

http_response_code(404);
echo json_encode(['success' => false, 'message' => 'Route not found']);
