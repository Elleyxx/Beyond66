<?php

require_once __DIR__ . '/../controllers/SavedItemController.php';

$controller = new SavedItemController();

if ($uri === '/api/saved-items' && $method === 'GET') {
    $controller->index();
    exit;
}

if ($uri === '/api/saved-items/destinations/toggle' && $method === 'POST') {
    $controller->toggleDestination();
    exit;
}

Response::json(['success' => false, 'message' => 'Saved items route not found'], 404);
