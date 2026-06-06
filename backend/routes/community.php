<?php

require_once __DIR__ . '/../controllers/CommunityController.php';

$controller = new CommunityController();
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

if ($uri === '/api/community/posts' && $method === 'GET') {
    $controller->listPosts();
    return;
}

if (preg_match('#^/api/community/posts/(\d+)$#', $uri, $matches) && $method === 'GET') {
    $controller->showPost((int) $matches[1]);
    return;
}

if (preg_match('#^/api/community/posts/(\d+)/save$#', $uri, $matches) && $method === 'POST') {
    $controller->savePost((int) $matches[1]);
    return;
}

if (preg_match('#^/api/community/posts/(\d+)$#', $uri, $matches) && $method === 'PATCH') {
    $controller->updatePost((int) $matches[1]);
    return;
}

if (preg_match('#^/api/community/posts/(\d+)/visibility$#', $uri, $matches) && $method === 'PATCH') {
    $controller->updateVisibility((int) $matches[1]);
    return;
}

http_response_code(404);
echo json_encode(['success' => false, 'message' => 'Community route not found']);
