<?php

require_once __DIR__ . '/../models/Planner.php';
require_once __DIR__ . '/../utils/Response.php';

class PlannerController
{
    public function save(): void
    {
        $userId = $this->currentUserId();
        if (!$userId) {
            Response::json(['success' => false, 'message' => 'Authentication required'], 401);
            return;
        }

        $payload = json_decode(file_get_contents('php://input'), true) ?: [];
        $tripMeta = is_array($payload['tripMeta'] ?? null) ? $payload['tripMeta'] : [];
        $timelineDays = is_array($payload['timelineDays'] ?? null) ? $payload['timelineDays'] : [];
        $budgetEstimate = is_array($payload['budgetEstimate'] ?? null) ? $payload['budgetEstimate'] : [];
        $packingList = is_array($payload['packingList'] ?? null) ? $payload['packingList'] : [];
        $weatherForecast = is_array($payload['weatherForecast'] ?? null) ? $payload['weatherForecast'] : [];
        $auroraForecast = is_array($payload['auroraForecast'] ?? null) ? $payload['auroraForecast'] : [];
        $summary = is_string($payload['summary'] ?? null) ? $payload['summary'] : '';
        $planId = isset($payload['plan_id']) ? (int) $payload['plan_id'] : null;
        $title = is_string($payload['title'] ?? null) ? $payload['title'] : '';
        $description = is_string($payload['description'] ?? null) ? $payload['description'] : '';
        $visibility = is_string($payload['visibility'] ?? null) ? $payload['visibility'] : 'private';
        $coverImage = is_string($payload['coverImage'] ?? null) ? $payload['coverImage'] : '';

        if (!$tripMeta || !$timelineDays) {
            Response::json(['success' => false, 'message' => 'tripMeta and timelineDays are required'], 422);
            return;
        }

        try {
            $planner = new Planner();
            $planId = $planner->savePlanDraft(
                $userId,
                $tripMeta,
                $timelineDays,
                $budgetEstimate,
                $packingList,
                $weatherForecast,
                $auroraForecast,
                $summary,
                $planId,
                $title,
                $description,
                $visibility,
                $coverImage
            );

            Response::json([
                'success' => true,
                'data' => [
                    'plan_id' => $planId,
                    'savedAt' => date('c'),
                ],
            ]);
        } catch (\Throwable $e) {
            Response::json([
                'success' => false,
                'message' => 'Failed to save planner draft',
            ], 500);
        }
    }

    public function latest(): void
    {
        $userId = $this->currentUserId();
        if (!$userId) {
            Response::json(['success' => false, 'message' => 'Authentication required'], 401);
            return;
        }

        try {
            $planner = new Planner();
            $result = $planner->getLatestPlanByUser($userId);

            Response::json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Throwable $e) {
            Response::json([
                'success' => false,
                'message' => 'Failed to fetch latest planner draft',
            ], 500);
        }
    }

    public function list(): void
    {
        $userId = $this->currentUserId();
        if (!$userId) {
            Response::json(['success' => false, 'message' => 'Authentication required'], 401);
            return;
        }

        try {
            $planner = new Planner();
            $result = $planner->getPlansByUser($userId);

            Response::json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Throwable $e) {
            Response::json([
                'success' => false,
                'message' => 'Failed to fetch planner drafts',
            ], 500);
        }
    }

    private function currentUserId(): ?int
    {
        $header = $this->authorizationHeader();
        if (!preg_match('/Bearer\s+(.+)/i', $header, $matches)) {
            return null;
        }

        $decoded = json_decode(base64_decode($matches[1], true) ?: '', true);
        $userId = (int) ($decoded['sub'] ?? 0);

        return $userId > 0 ? $userId : null;
    }

    private function authorizationHeader(): string
    {
        if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
            return (string) $_SERVER['HTTP_AUTHORIZATION'];
        }

        if (!empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            return (string) $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        }

        if (function_exists('getallheaders')) {
            foreach (getallheaders() as $name => $value) {
                if (strtolower((string) $name) === 'authorization') {
                    return (string) $value;
                }
            }
        }

        return '';
    }
}
