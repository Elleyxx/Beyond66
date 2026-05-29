<?php

class SavedItem
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

    public function listDestinationSlugs(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT item_slug FROM saved_items
             WHERE user_id = :user_id AND item_type = 'destination'
             ORDER BY created_at DESC"
        );
        $stmt->execute(['user_id' => $userId]);

        return array_values(array_filter(array_column($stmt->fetchAll(), 'item_slug')));
    }

    public function isDestinationSaved(int $userId, string $slug): bool
    {
        $stmt = $this->pdo->prepare(
            "SELECT id FROM saved_items
             WHERE user_id = :user_id AND item_type = 'destination' AND item_slug = :item_slug
             LIMIT 1"
        );
        $stmt->execute([
            'user_id' => $userId,
            'item_slug' => $slug,
        ]);

        return (bool) $stmt->fetch();
    }

    public function saveDestination(int $userId, string $slug): void
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO saved_items (user_id, item_type, item_id, item_slug)
             VALUES (:user_id, 'destination', 0, :item_slug)
             ON DUPLICATE KEY UPDATE created_at = created_at"
        );
        $stmt->execute([
            'user_id' => $userId,
            'item_slug' => $slug,
        ]);
    }

    public function unsaveDestination(int $userId, string $slug): void
    {
        $stmt = $this->pdo->prepare(
            "DELETE FROM saved_items
             WHERE user_id = :user_id AND item_type = 'destination' AND item_slug = :item_slug"
        );
        $stmt->execute([
            'user_id' => $userId,
            'item_slug' => $slug,
        ]);
    }
}
