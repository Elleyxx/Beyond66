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
        $tags = is_array($payload['tags'] ?? null) ? $payload['tags'] : [];
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
                $tags,
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

    public function saveDiary(int $tripId): void
    {
        $userId = $this->currentUserId();
        if (!$userId) {
            Response::json(['success' => false, 'message' => 'Authentication required'], 401);
            return;
        }

        $payload = json_decode(file_get_contents('php://input'), true) ?: [];

        try {
            $planner = new Planner();
            $result = $planner->saveJourneyDiary($userId, $tripId, $payload);
            if (!$result) {
                Response::json(['success' => false, 'message' => 'Journey not found'], 404);
                return;
            }

            Response::json(['success' => true, 'data' => $result]);
        } catch (\Throwable $e) {
            Response::json(['success' => false, 'message' => 'Failed to save diary'], 500);
        }
    }

    public function updateVisibility(int $tripId): void
    {
        $userId = $this->currentUserId();
        if (!$userId) {
            Response::json(['success' => false, 'message' => 'Authentication required'], 401);
            return;
        }

        $payload = json_decode(file_get_contents('php://input'), true) ?: [];
        $visibility = is_string($payload['visibility'] ?? null) ? $payload['visibility'] : 'private';

        try {
            $planner = new Planner();
            $result = $planner->updateJourneyVisibility($userId, $tripId, $visibility);
            if (!$result) {
                Response::json(['success' => false, 'message' => 'Journey not found'], 404);
                return;
            }

            Response::json(['success' => true, 'data' => $result]);
        } catch (\Throwable $e) {
            Response::json(['success' => false, 'message' => 'Failed to update journey visibility'], 500);
        }
    }

    public function uploadDiaryImages(): void
    {
        $userId = $this->currentUserId();
        if (!$userId) {
            Response::json(['success' => false, 'message' => 'Authentication required'], 401);
            return;
        }

        $files = $_FILES['images'] ?? null;
        if (!$files || empty($files['name'])) {
            Response::json(['success' => false, 'message' => 'No images uploaded'], 422);
            return;
        }

        $uploadDir = realpath(__DIR__ . '/../public') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'diaries';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
            Response::json(['success' => false, 'message' => 'Failed to prepare upload directory'], 500);
            return;
        }

        $names = is_array($files['name']) ? $files['name'] : [$files['name']];
        $tmpNames = is_array($files['tmp_name']) ? $files['tmp_name'] : [$files['tmp_name']];
        $errors = is_array($files['error']) ? $files['error'] : [$files['error']];
        $sizes = is_array($files['size']) ? $files['size'] : [$files['size']];
        $urls = [];
        $allowedTypes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
        ];

        $count = min(count($names), 10);
        for ($index = 0; $index < $count; $index++) {
            if (($errors[$index] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                continue;
            }

            if ((int) ($sizes[$index] ?? 0) > 5 * 1024 * 1024) {
                continue;
            }

            $tmpName = (string) ($tmpNames[$index] ?? '');
            if ($tmpName === '' || !is_uploaded_file($tmpName)) {
                continue;
            }

            $mimeType = mime_content_type($tmpName) ?: '';
            if (!isset($allowedTypes[$mimeType])) {
                continue;
            }

            $fileName = sprintf(
                'diary-%d-%s-%d.%s',
                $userId,
                bin2hex(random_bytes(8)),
                time(),
                $allowedTypes[$mimeType]
            );
            $target = $uploadDir . DIRECTORY_SEPARATOR . $fileName;

            if (move_uploaded_file($tmpName, $target)) {
                $urls[] = '/uploads/diaries/' . $fileName;
            }
        }

        if (!$urls) {
            Response::json(['success' => false, 'message' => 'No valid images were uploaded'], 422);
            return;
        }

        Response::json([
            'success' => true,
            'data' => [
                'urls' => $urls,
            ],
        ]);
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
