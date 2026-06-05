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
            'data' => $this->community->listPublicPosts(),
        ]);
    }

    public function showPost(int $postId): void
    {
        $post = $this->community->findPublicPost($postId);

        if (!$post) {
            Response::json(['success' => false, 'message' => 'Post not found'], 404);
            return;
        }

        Response::json([
            'success' => true,
            'data' => $post,
        ]);
    }

    public function savePost(int $postId): void
    {
        $this->community->savePost($postId);

        Response::json([
            'success' => true,
            'message' => 'Post saved',
        ]);
    }
}
