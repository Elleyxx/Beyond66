<?php

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

$allowedOrigins = [
    'http://localhost:5173',
    'https://beyond66-j79m.vercel.app',
];

$isAllowedVercelOrigin = (bool) preg_match('/^https:\/\/[a-z0-9-]+\.vercel\.app$/i', $origin);

if (in_array($origin, $allowedOrigins, true) || $isAllowedVercelOrigin) {
    header("Access-Control-Allow-Origin: $origin");
    header('Vary: Origin');
}

header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if (str_contains($_SERVER['REQUEST_URI'], '/api/health')) {
    echo json_encode([
        'success' => true,
        'status' => 'ok'
    ]);
    exit;
}

require_once __DIR__ . '/../config/bootstrap_database.php';

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

ensureDatabaseReady();

http_response_code(404);
echo json_encode(['success' => false, 'message' => 'Route not found']);
