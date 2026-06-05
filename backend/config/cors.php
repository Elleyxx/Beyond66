<?php

$allowedOrigins = [
    'http://localhost:5173',
    'http://127.0.0.1:5173',
    'https://beyond66-j79m.vercel.app'
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
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
