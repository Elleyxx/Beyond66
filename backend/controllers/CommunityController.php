<?php

require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../models/Community.php';

class CommunityController
{
    private Community $community;

    public function __construct()
    {
        $this->community = new Community();
    }

    public function listPosts(): void
    {
        Response::json([
            'success' => true,
            'data' => $this->community->listPublicPosts($this->currentUserId()),
        ]);
    }

    public function showPost(int $postId): void
    {
        $post = $this->community->findPublicPost($postId, $this->currentUserId());

        if (!$post) {
            Response::json(['success' => false, 'message' => 'Post not found'], 404);
            return;
        }

        Response::json([
            'success' => true,
            'data' => [
                'post'     => $post,
                'comments' => $this->community->getComments($postId),
            ],
        ]);
    }

    public function savePost(int $postId): void
    {
        $userId = $this->currentUserId();
        if (!$userId) {
            Response::json(['success' => false, 'message' => 'Authentication required'], 401);
            return;
        }

        $post = $this->community->findPublicPost($postId, $userId);
        if ($post && ($post['isOwner'] ?? false)) {
            Response::json(['success' => false, 'message' => 'You cannot save your own post'], 422);
            return;
        }

        $this->community->savePost($postId, $userId);

        Response::json([
            'success' => true,
            'message' => 'Post saved',
        ]);
    }

    public function unsavePost(int $postId): void
    {
        $userId = $this->currentUserId();
        if (!$userId) {
            Response::json(['success' => false, 'message' => 'Authentication required'], 401);
            return;
        }

        $this->community->unsavePost($postId, $userId);

        Response::json(['success' => true, 'message' => 'Post unsaved']);
    }

    public function updatePost(int $postId): void
    {
        $userId = $this->currentUserId();
        if (!$userId) {
            Response::json(['success' => false, 'message' => 'Authentication required'], 401);
            return;
        }

        $payload = json_decode(file_get_contents('php://input'), true) ?: [];

        try {
            $post = $this->community->updateOwnedPost($postId, $userId, $payload);
            if (!$post) {
                Response::json(['success' => false, 'message' => 'Post not found or not owned by user'], 404);
                return;
            }

            Response::json([
                'success' => true,
                'data' => $post,
            ]);
        } catch (\Throwable $e) {
            Response::json(['success' => false, 'message' => 'Failed to update post'], 500);
        }
    }

    public function updateVisibility(int $postId): void
    {
        $userId = $this->currentUserId();
        if (!$userId) {
            Response::json(['success' => false, 'message' => 'Authentication required'], 401);
            return;
        }

        $payload = json_decode(file_get_contents('php://input'), true) ?: [];
        $status = strtolower((string) ($payload['status'] ?? 'private')) === 'public' ? 'public' : 'private';

        $updated = $this->community->updateOwnedPostVisibility($postId, $userId, $status);
        if (!$updated) {
            Response::json(['success' => false, 'message' => 'Post not found or not owned by user'], 404);
            return;
        }

        Response::json([
            'success' => true,
            'data' => ['status' => $status],
        ]);
    }

    public function addComment(int $postId): void
    {
        $userId = $this->currentUserId();
        if (!$userId) {
            Response::json(['success' => false, 'message' => 'Authentication required'], 401);
            return;
        }

        $payload = json_decode(file_get_contents('php://input'), true) ?: [];
        $comment = trim((string) ($payload['comment'] ?? ''));

        if ($comment === '') {
            Response::json(['success' => false, 'message' => 'Comment cannot be empty'], 422);
            return;
        }

        $newComment = $this->community->addComment($postId, $userId, $comment);

        Response::json([
            'success' => true,
            'data'    => $newComment,
        ]);
    }

    public function toggleLike(int $postId): void
    {
        $userId = $this->currentUserId();
        if (!$userId) {
            Response::json(['success' => false, 'message' => 'Authentication required'], 401);
            return;
        }

        $result = $this->community->toggleLike($postId, $userId);

        Response::json([
            'success' => true,
            'data'    => $result,
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
