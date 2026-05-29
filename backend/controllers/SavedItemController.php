<?php

require_once __DIR__ . '/../models/SavedItem.php';
require_once __DIR__ . '/../utils/Response.php';

class SavedItemController
{
    public function index(): void
    {
        $userId = $this->currentUserId();
        if (!$userId) {
            Response::json(['success' => false, 'message' => 'Authentication required'], 401);
            return;
        }

        $savedItem = new SavedItem();

        Response::json([
            'success' => true,
            'data' => [
                'destinations' => $savedItem->listDestinationSlugs($userId),
            ],
        ]);
    }

    public function toggleDestination(): void
    {
        $userId = $this->currentUserId();
        if (!$userId) {
            Response::json(['success' => false, 'message' => 'Authentication required'], 401);
            return;
        }

        $payload = json_decode(file_get_contents('php://input'), true) ?: [];
        $slug = trim((string) ($payload['item_slug'] ?? ''));

        if ($slug === '') {
            Response::json(['success' => false, 'message' => 'Destination slug is required'], 422);
            return;
        }

        $savedItem = new SavedItem();
        $isSaved = $savedItem->isDestinationSaved($userId, $slug);

        if ($isSaved) {
            $savedItem->unsaveDestination($userId, $slug);
            Response::json(['success' => true, 'data' => ['saved' => false, 'item_slug' => $slug]]);
            return;
        }

        $savedItem->saveDestination($userId, $slug);
        Response::json(['success' => true, 'data' => ['saved' => true, 'item_slug' => $slug]]);
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
