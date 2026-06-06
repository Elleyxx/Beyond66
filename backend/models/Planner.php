<?php

class Planner
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

    public function savePlanDraft(
        int $userId,
        array $tripMeta,
        array $timelineDays,
        array $budgetEstimate,
        array $packingList,
        array $weatherForecast = [],
        array $auroraForecast = [],
        string $summary = '',
        array $tags = [],
        ?int $planId = null,
        string $title = '',
        string $description = '',
        string $visibility = 'private',
        string $coverImage = ''
    ): int
    {
        $this->pdo->beginTransaction();

        try {
            $title = trim($title) ?: trim((string) ($tripMeta['country'] ?? 'Nordic Trip')) . ' Trip Plan';
            $description = trim($description);
            $coverImage = trim($coverImage);
            $visibility = strtolower($visibility) === 'public' ? 'public' : 'private';
            $existingDiary = $planId ? $this->getExistingDiary($planId, $userId) : null;
            $tripData = $this->encodeTripData(
                $tripMeta,
                $timelineDays,
                $budgetEstimate,
                $packingList,
                $weatherForecast,
                $auroraForecast,
                $summary,
                $tags,
                $existingDiary
            );

            if ($planId) {
                $ownerStmt = $this->pdo->prepare(
                    'SELECT id FROM trips WHERE id = :id AND user_id = :user_id LIMIT 1'
                );
                $ownerStmt->execute([
                    'id' => $planId,
                    'user_id' => $userId,
                ]);

                if (!$ownerStmt->fetch()) {
                    throw new \RuntimeException('Trip not found');
                }

                $stmt = $this->pdo->prepare(
                    'UPDATE trips
                     SET title = :title,
                         description = :description,
                         cover_image = :cover_image,
                         visibility = :visibility,
                         trip_data = :trip_data
                     WHERE id = :id AND user_id = :user_id'
                );
                $stmt->execute([
                    'id' => $planId,
                    'user_id' => $userId,
                    'title' => $title,
                    'description' => $description,
                    'cover_image' => $coverImage ?: null,
                    'visibility' => $visibility,
                    'trip_data' => $tripData,
                ]);
            } else {
                $stmt = $this->pdo->prepare(
                    'INSERT INTO trips (user_id, title, description, cover_image, visibility, trip_data)
                     VALUES (:user_id, :title, :description, :cover_image, :visibility, :trip_data)'
                );
                $stmt->execute([
                    'user_id' => $userId,
                    'title' => $title,
                    'description' => $description,
                    'cover_image' => $coverImage ?: null,
                    'visibility' => $visibility,
                    'trip_data' => $tripData,
                ]);

                $planId = (int) $this->pdo->lastInsertId();
            }

            $this->syncCommunityPost($planId, $userId, $title, $description, $coverImage, $visibility);

            $this->pdo->commit();
            return $planId;
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    public function getLatestPlanByUser(int $userId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM trips WHERE user_id = :user_id ORDER BY updated_at DESC, created_at DESC LIMIT 1'
        );
        $stmt->execute(['user_id' => $userId]);
        $trip = $stmt->fetch();

        return $trip ? $this->hydrateTrip($trip) : null;
    }

    public function getPlansByUser(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM trips WHERE user_id = :user_id ORDER BY updated_at DESC, created_at DESC'
        );
        $stmt->execute(['user_id' => $userId]);

        return array_map(fn ($trip) => $this->hydrateTrip($trip), $stmt->fetchAll());
    }

    public function saveJourneyDiary(int $userId, int $tripId, array $diary): ?array
    {
        $trip = $this->getOwnedTrip($tripId, $userId);
        if (!$trip) {
            return null;
        }

        $tripData = json_decode((string) ($trip['trip_data'] ?? '{}'), true) ?: [];
        $tripData['diary'] = [
            'title' => trim((string) ($diary['title'] ?? '')),
            'story' => trim((string) ($diary['story'] ?? '')),
            'country' => trim((string) ($diary['country'] ?? ($tripData['meta']['country'] ?? ''))),
            'photos' => array_values(array_filter(array_map('strval', is_array($diary['photos'] ?? null) ? $diary['photos'] : []))),
            'updatedAt' => date('c'),
        ];

        $description = trim((string) ($trip['description'] ?? ''));
        if ($description === '' && $tripData['diary']['story'] !== '') {
            $description = $tripData['diary']['story'];
        }

        $stmt = $this->pdo->prepare(
            'UPDATE trips
             SET description = :description,
                 trip_data = :trip_data
             WHERE id = :id AND user_id = :user_id'
        );
        $stmt->execute([
            'id' => $tripId,
            'user_id' => $userId,
            'description' => $description,
            'trip_data' => json_encode($tripData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        ]);

        $this->syncCommunityPost(
            $tripId,
            $userId,
            (string) ($trip['title'] ?? 'Untitled journey'),
            $description,
            (string) ($trip['cover_image'] ?? ''),
            (string) ($trip['visibility'] ?? 'private')
        );

        return $this->getLatestHydratedTrip($tripId, $userId);
    }

    public function updateJourneyVisibility(int $userId, int $tripId, string $visibility): ?array
    {
        $trip = $this->getOwnedTrip($tripId, $userId);
        if (!$trip) {
            return null;
        }

        $visibility = strtolower($visibility) === 'public' ? 'public' : 'private';
        $stmt = $this->pdo->prepare(
            'UPDATE trips SET visibility = :visibility WHERE id = :id AND user_id = :user_id'
        );
        $stmt->execute([
            'id' => $tripId,
            'user_id' => $userId,
            'visibility' => $visibility,
        ]);

        $this->syncCommunityPost(
            $tripId,
            $userId,
            (string) ($trip['title'] ?? 'Untitled journey'),
            (string) ($trip['description'] ?? ''),
            (string) ($trip['cover_image'] ?? ''),
            $visibility
        );

        return $this->getLatestHydratedTrip($tripId, $userId);
    }

    private function encodeTripData(
        array $tripMeta,
        array $timelineDays,
        array $budgetEstimate,
        array $packingList,
        array $weatherForecast,
        array $auroraForecast,
        string $summary,
        array $tags,
        ?array $diary = null
    ): string {
        $data = [
            'meta' => $tripMeta,
            'timeline' => $timelineDays,
            'budget' => $budgetEstimate,
            'weather' => $weatherForecast,
            'aurora' => $auroraForecast,
            'checklist' => $packingList,
            'summary' => $summary,
            'tags' => $this->normalizeTags($tags),
        ];

        if ($diary) {
            $data['diary'] = $diary;
        }

        return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
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

    private function syncCommunityPost(
        int $tripId,
        int $userId,
        string $title,
        string $description,
        string $coverImage,
        string $visibility
    ): void {
        $existingStmt = $this->pdo->prepare(
            'SELECT id FROM community_posts WHERE trip_id = :trip_id AND user_id = :user_id LIMIT 1'
        );
        $existingStmt->execute([
            'trip_id' => $tripId,
            'user_id' => $userId,
        ]);
        $existing = $existingStmt->fetch();

        if ($existing) {
            $updateStmt = $this->pdo->prepare(
                'UPDATE community_posts
                 SET title = :title,
                     description = :description,
                     cover_image = :cover_image,
                     status = :status
                 WHERE id = :id AND user_id = :user_id'
            );
            $updateStmt->execute([
                'id' => (int) $existing['id'],
                'user_id' => $userId,
                'title' => $title,
                'description' => $description,
                'cover_image' => $coverImage ?: null,
                'status' => $visibility,
            ]);
            return;
        }

        if ($visibility !== 'public') {
            return;
        }

        $insertStmt = $this->pdo->prepare(
            'INSERT INTO community_posts (trip_id, user_id, title, description, cover_image, status)
             VALUES (:trip_id, :user_id, :title, :description, :cover_image, :status)'
        );
        $insertStmt->execute([
            'trip_id' => $tripId,
            'user_id' => $userId,
            'title' => $title,
            'description' => $description,
            'cover_image' => $coverImage ?: null,
            'status' => 'public',
        ]);
    }

    private function hydrateTrip(array $trip): array
    {
        $tripData = json_decode((string) ($trip['trip_data'] ?? '{}'), true) ?: [];
        $meta = is_array($tripData['meta'] ?? null) ? $tripData['meta'] : [];
        $diary = is_array($tripData['diary'] ?? null) ? $tripData['diary'] : null;

        return [
            'plan_id' => (int) $trip['id'],
            'trip_id' => (int) $trip['id'],
            'title' => (string) ($trip['title'] ?? ''),
            'tripMeta' => $meta,
            'timelineDays' => is_array($tripData['timeline'] ?? null) ? $tripData['timeline'] : [],
            'budgetEstimate' => is_array($tripData['budget'] ?? null) ? $tripData['budget'] : null,
            'packingList' => is_array($tripData['checklist'] ?? null) ? $tripData['checklist'] : [],
            'weatherForecast' => is_array($tripData['weather'] ?? null) ? $tripData['weather'] : [],
            'auroraForecast' => $tripData['aurora'] ?? null,
            'summary' => (string) ($tripData['summary'] ?? ''),
            'diary' => $diary,
            'hasPlanner' => true,
            'hasDiary' => $this->hasDiary($diary),
            'tags' => is_array($tripData['tags'] ?? null) ? $tripData['tags'] : [],
            'description' => (string) ($trip['description'] ?? ''),
            'visibility' => (string) ($trip['visibility'] ?? 'private'),
            'coverImage' => (string) ($trip['cover_image'] ?? ''),
            'savedAt' => $trip['created_at'] ?? null,
            'updatedAt' => $trip['updated_at'] ?? null,
        ];
    }

    private function getExistingDiary(int $tripId, int $userId): ?array
    {
        $trip = $this->getOwnedTrip($tripId, $userId);
        if (!$trip) {
            return null;
        }

        $tripData = json_decode((string) ($trip['trip_data'] ?? '{}'), true) ?: [];
        return is_array($tripData['diary'] ?? null) ? $tripData['diary'] : null;
    }

    private function getOwnedTrip(int $tripId, int $userId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM trips WHERE id = :id AND user_id = :user_id LIMIT 1');
        $stmt->execute([
            'id' => $tripId,
            'user_id' => $userId,
        ]);
        $trip = $stmt->fetch();

        return $trip ?: null;
    }

    private function getLatestHydratedTrip(int $tripId, int $userId): ?array
    {
        $trip = $this->getOwnedTrip($tripId, $userId);
        return $trip ? $this->hydrateTrip($trip) : null;
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
}
