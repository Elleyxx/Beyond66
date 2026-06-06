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

    public function listPublicPosts(?int $currentUserId = null): array
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
             WHERE cp.status = 'public' OR (:current_user_id_check > 0 AND cp.user_id = :current_user_id_owner)
             GROUP BY cp.id, cp.trip_id, cp.user_id, cp.title, cp.description, cp.cover_image,
                      cp.status, cp.created_at, u.display_name, u.username, t.trip_data
             ORDER BY cp.created_at DESC"
        );
        $stmt->execute([
            'current_user_id_check' => (int) ($currentUserId ?? 0),
            'current_user_id_owner' => (int) ($currentUserId ?? 0),
        ]);

        return array_map(fn ($post) => $this->formatPost($post, $currentUserId), $stmt->fetchAll());
    }

    public function findPublicPost(int $postId, ?int $currentUserId = null): ?array
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
             WHERE cp.id = :id AND (cp.status = 'public' OR (:current_user_id_check > 0 AND cp.user_id = :current_user_id_owner))
             GROUP BY cp.id, cp.trip_id, cp.user_id, cp.title, cp.description, cp.cover_image,
                      cp.status, cp.created_at, u.display_name, u.username, t.trip_data
             LIMIT 1"
        );
        $stmt->execute([
            'id' => $postId,
            'current_user_id_check' => (int) ($currentUserId ?? 0),
            'current_user_id_owner' => (int) ($currentUserId ?? 0),
        ]);
        $post = $stmt->fetch();

        return $post ? $this->formatPost($post, $currentUserId) : null;
    }

    public function savePost(int $postId, int $userId): void
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

    public function updateOwnedPost(int $postId, int $userId, array $payload): ?array
    {
        $ownerStmt = $this->pdo->prepare(
            'SELECT cp.id, cp.trip_id
             FROM community_posts cp
             WHERE cp.id = :id AND cp.user_id = :user_id
             LIMIT 1'
        );
        $ownerStmt->execute([
            'id' => $postId,
            'user_id' => $userId,
        ]);
        $ownedPost = $ownerStmt->fetch();

        if (!$ownedPost) {
            return null;
        }

        $title = trim((string) ($payload['title'] ?? ''));
        $description = trim((string) ($payload['description'] ?? ''));
        $coverImage = trim((string) ($payload['coverImage'] ?? $payload['cover_image'] ?? ''));
        $tags = $this->normalizeTags(is_array($payload['tags'] ?? null) ? $payload['tags'] : []);
        $status = strtolower((string) ($payload['status'] ?? 'public')) === 'private' ? 'private' : 'public';

        if ($title === '') {
            $title = 'Untitled trip';
        }

        $this->pdo->beginTransaction();

        try {
            $postStmt = $this->pdo->prepare(
                'UPDATE community_posts
                 SET title = :title,
                     description = :description,
                     cover_image = :cover_image,
                     status = :status
                 WHERE id = :id AND user_id = :user_id'
            );
            $postStmt->execute([
                'id' => $postId,
                'user_id' => $userId,
                'title' => $title,
                'description' => $description,
                'cover_image' => $coverImage ?: null,
                'status' => $status,
            ]);

            $trip = $this->getTripForUpdate((int) $ownedPost['trip_id'], $userId);
            if ($trip) {
                $tripData = json_decode((string) ($trip['trip_data'] ?? '{}'), true) ?: [];
                $tripData['summary'] = $description;
                $tripData['tags'] = $tags;

                $tripStmt = $this->pdo->prepare(
                    'UPDATE trips
                     SET title = :title,
                         description = :description,
                         cover_image = :cover_image,
                         visibility = :visibility,
                         trip_data = :trip_data
                     WHERE id = :id AND user_id = :user_id'
                );
                $tripStmt->execute([
                    'id' => (int) $ownedPost['trip_id'],
                    'user_id' => $userId,
                    'title' => $title,
                    'description' => $description,
                    'cover_image' => $coverImage ?: null,
                    'visibility' => $status,
                    'trip_data' => json_encode($tripData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                ]);
            }

            $this->pdo->commit();
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }

        return $this->findPublicPost($postId, $userId);
    }

    public function updateOwnedPostVisibility(int $postId, int $userId, string $status): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE community_posts cp
             INNER JOIN trips t ON t.id = cp.trip_id AND t.user_id = cp.user_id
             SET cp.status = :status,
                 t.visibility = :visibility
             WHERE cp.id = :id AND cp.user_id = :user_id'
        );
        $stmt->execute([
            'id' => $postId,
            'user_id' => $userId,
            'status' => $status,
            'visibility' => $status,
        ]);

        return $stmt->rowCount() > 0;
    }

    private function getTripForUpdate(int $tripId, int $userId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, trip_data FROM trips WHERE id = :id AND user_id = :user_id LIMIT 1'
        );
        $stmt->execute([
            'id' => $tripId,
            'user_id' => $userId,
        ]);
        $trip = $stmt->fetch();

        return $trip ?: null;
    }

    private function normalizeTags(array $tags): array
    {
        $normalized = [];
        $seen = [];

        foreach ($tags as $tag) {
            $value = trim(ltrim((string) $tag, '#'));
            if ($value === '') {
                continue;
            }

            $key = function_exists('mb_strtolower') ? mb_strtolower($value) : strtolower($value);
            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $normalized[] = $value;

            if (count($normalized) >= 8) {
                break;
            }
        }

        return $normalized;
    }

    private function formatPost(array $post, ?int $currentUserId = null): array
    {
        $trip = json_decode((string) ($post['trip_data'] ?? '{}'), true) ?: [];
        $meta = is_array($trip['meta'] ?? null) ? $trip['meta'] : [];
        $timeline = is_array($trip['timeline'] ?? null) ? $trip['timeline'] : [];
        $diary = is_array($trip['diary'] ?? null) ? $trip['diary'] : null;
        $hasPlanner = !empty($timeline);
        $hasDiary = $this->hasDiary($diary);
        $postType = $this->journeyPostType($hasPlanner, $hasDiary);

        return [
            'id' => (int) $post['id'],
            'tripId' => (int) $post['trip_id'],
            'userId' => (int) $post['user_id'],
            'isOwner' => $currentUserId !== null && (int) $post['user_id'] === $currentUserId,
            'title' => (string) ($post['title'] ?? 'Untitled trip'),
            'description' => (string) ($post['description'] ?? ''),
            'coverImage' => (string) ($post['cover_image'] ?? ''),
            'cover_image' => (string) ($post['cover_image'] ?? ''),
            'status' => (string) ($post['status'] ?? 'public'),
            'type' => $hasDiary && !$hasPlanner ? 'diary' : 'plan',
            'typeLabel' => $postType,
            'category' => $hasDiary && !$hasPlanner ? 'diary' : 'plan',
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
                'diary' => $diary,
                'budget' => is_array($trip['budget'] ?? null) ? $trip['budget'] : [],
                'checklist' => is_array($trip['checklist'] ?? null) ? $trip['checklist'] : [],
                'summary' => (string) ($trip['summary'] ?? ''),
                'tags' => is_array($trip['tags'] ?? null) ? $trip['tags'] : [],
            ],
        ];
    }

    private function hasDiary(?array $diary): bool
    {
        if (!$diary) {
            return false;
        }

        return trim((string) ($diary['story'] ?? '')) !== ''
            || trim((string) ($diary['title'] ?? '')) !== ''
            || !empty($diary['photos']);
    }

    private function journeyPostType(bool $hasPlanner, bool $hasDiary): string
    {
        if ($hasPlanner && $hasDiary) {
            return 'Complete Journey';
        }

        if ($hasPlanner) {
            return 'Journey Planner';
        }

        if ($hasDiary) {
            return 'Travel Diary';
        }

        return 'Discussion';
    }
}
