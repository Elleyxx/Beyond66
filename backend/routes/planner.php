<?php

require_once __DIR__ . '/../controllers/PlannerController.php';
require_once __DIR__ . '/../controllers/PlannerAIController.php';
require_once __DIR__ . '/../utils/Response.php';

$controller = new PlannerController();
$aiController = new PlannerAIController();

if ($uri === '/api/planner/ai/generate' && $method === 'POST') {
    $aiController->generatePlan();
    exit;
}

if ($uri === '/api/planner/latest' && $method === 'GET') {
    $controller->latest();
    exit;
}

if ($uri === '/api/planner/save' && $method === 'POST') {
    $controller->save();
    exit;
}

Response::json(['success' => false, 'message' => 'Planner route not found'], 404);
