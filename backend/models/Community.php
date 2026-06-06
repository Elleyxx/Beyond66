<?php

class Community
{
    private \PDO $pdo;

    public function __construct()
    {
        $config = require __DIR__ . '/../config/database.php';

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $config['host'],
            $config['port'],
            $config['dbname'],
            $config['charset']
        );

        $this->pdo = new \PDO($dsn, $config['username'], $config['password'], [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }

    public function listPublicPosts(): array
    {
        $stmt = $this->pdo->query(
            "SELECT
                cp.id,
                cp.trip_id,
                cp.user_id,
                cp.title,
                cp.description,
                cp.cover_image,
                cp.status,
                cp.created_at,
                u.display_name AS author_name,
                u.username AS author_username,
                t.trip_data,
                COUNT(DISTINCT pl.id) AS likes,
                COUNT(DISTINCT pc.id) AS comments
             FROM community_posts cp
             INNER JOIN users u ON u.id = cp.user_id
             INNER JOIN trips t ON t.id = cp.trip_id
             LEFT JOIN post_likes pl ON pl.post_id = cp.id
             LEFT JOIN post_comments pc ON pc.post_id = cp.id
             WHERE cp.status = 'public'
             GROUP BY cp.id, cp.trip_id, cp.user_id, cp.title, cp.description, cp.cover_image,
                      cp.status, cp.created_at, u.display_name, u.username, t.trip_data
             ORDER BY cp.created_at DESC"
        );

        return array_map(fn ($post) => $this->formatPost($post), $stmt->fetchAll());
    }

    public function findPublicPost(int $postId): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT
                cp.id,
                cp.trip_id,
                cp.user_id,
                cp.title,
                cp.description,
                cp.cover_image,
                cp.status,
                cp.created_at,
                u.display_name AS author_name,
                u.username AS author_username,
                t.trip_data,
                COUNT(DISTINCT pl.id) AS likes,
                COUNT(DISTINCT pc.id) AS comments
             FROM community_posts cp
             INNER JOIN users u ON u.id = cp.user_id
             INNER JOIN trips t ON t.id = cp.trip_id
             LEFT JOIN post_likes pl ON pl.post_id = cp.id
             LEFT JOIN post_comments pc ON pc.post_id = cp.id
             WHERE cp.id = :id AND cp.status = 'public'
             GROUP BY cp.id, cp.trip_id, cp.user_id, cp.title, cp.description, cp.cover_image,
                      cp.status, cp.created_at, u.display_name, u.username, t.trip_data
             LIMIT 1"
        );
        $stmt->execute(['id' => $postId]);
        $post = $stmt->fetch();

        return $post ? $this->formatPost($post) : null;
    }

    public function savePost(int $postId, int $userId = 1): void
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO saved_posts (post_id, user_id)
             VALUES (:post_id, :user_id)
             ON DUPLICATE KEY UPDATE created_at = created_at"
        );
        $stmt->execute([
            'post_id' => $postId,
            'user_id' => $userId,
        ]);
    }

    private function formatPost(array $post): array
    {
        $trip = json_decode((string) ($post['trip_data'] ?? '{}'), true) ?: [];
        $meta = is_array($trip['meta'] ?? null) ? $trip['meta'] : [];

        return [
            'id' => (int) $post['id'],
            'tripId' => (int) $post['trip_id'],
            'userId' => (int) $post['user_id'],
            'title' => (string) ($post['title'] ?? 'Untitled trip'),
            'description' => (string) ($post['description'] ?? ''),
            'coverImage' => (string) ($post['cover_image'] ?? ''),
            'cover_image' => (string) ($post['cover_image'] ?? ''),
            'type' => 'plan',
            'typeLabel' => 'Trip Plan',
            'category' => 'plan',
            'authorName' => (string) ($post['author_name'] ?? 'Traveller'),
            'author_name' => (string) ($post['author_name'] ?? 'Traveller'),
            'country' => (string) ($meta['country'] ?? 'Nordic'),
            'duration' => (int) ($meta['duration'] ?? 0),
            'season' => (string) ($meta['season'] ?? 'Any season'),
            'likes' => (int) ($post['likes'] ?? 0),
            'comments' => (int) ($post['comments'] ?? 0),
            'tags' => is_array($trip['tags'] ?? null) ? $trip['tags'] : [],
            'createdAt' => $post['created_at'] ?? null,
            'trip' => [
                'meta' => $meta,
                'timeline' => is_array($trip['timeline'] ?? null) ? $trip['timeline'] : [],
                'budget' => is_array($trip['budget'] ?? null) ? $trip['budget'] : [],
                'checklist' => is_array($trip['checklist'] ?? null) ? $trip['checklist'] : [],
                'summary' => (string) ($trip['summary'] ?? ''),
                'tags' => is_array($trip['tags'] ?? null) ? $trip['tags'] : [],
            ],
        ];
    }
}
