<?php

require_once __DIR__ . '/../utils/Response.php';

class DashboardController
{
    private \PDO $pdo;

    private array $countryCodes = [
        'Norway' => 'NOR',
        'Sweden' => 'SWE',
        'Finland' => 'FIN',
        'Iceland' => 'ICE',
        'Denmark' => 'DEN',
    ];

    private array $countrySlugs = [
        'norway' => 'Norway',
        'sweden' => 'Sweden',
        'finland' => 'Finland',
        'iceland' => 'Iceland',
        'denmark' => 'Denmark',
    ];

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

    public function profile(): void
    {
        $userId = $this->currentUserId();
        if (!$userId) {
            Response::json(['success' => false, 'message' => 'Authentication required'], 401);
            return;
        }

        try {
            $user = $this->getUser($userId);
            if (!$user) {
                Response::json(['success' => false, 'message' => 'User not found'], 404);
                return;
            }

            $journeys = $this->getJourneys($userId);
            $savedDestinations = $this->getSavedDestinations($userId);
            $publicPosts = $this->getPublicPosts($userId);
            $visitedCountries = $this->getVisitedCountries($journeys);

            Response::json([
                'success' => true,
                'data' => [
                    'user' => $this->formatUser($user, $visitedCountries),
                    'stats' => $this->buildStats($visitedCountries, $journeys, $savedDestinations, $publicPosts),
                    'passportCountries' => $this->buildPassportCountries($visitedCountries, $journeys),
                    'achievements' => $this->buildAchievements($visitedCountries, $journeys, $savedDestinations, $publicPosts),
                    'journeys' => $journeys,
                    'savedDestinations' => $savedDestinations,
                    'publicPosts' => $publicPosts,
                ],
            ]);
        } catch (\Throwable $e) {
            Response::json([
                'success' => false,
                'message' => 'Failed to load profile data',
            ], 500);
        }
    }

    private function getUser(int $userId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, email, username, display_name, avatar_url, created_at FROM users WHERE id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    private function getJourneys(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                t.id,
                t.title,
                t.description,
                t.cover_image,
                t.visibility,
                t.trip_data,
                t.created_at,
                t.updated_at,
                cp.id AS post_id
             FROM trips t
             LEFT JOIN community_posts cp ON cp.trip_id = t.id AND cp.user_id = t.user_id AND cp.status = "public"
             WHERE t.user_id = :user_id
             ORDER BY t.updated_at DESC, t.created_at DESC'
        );
        $stmt->execute(['user_id' => $userId]);

        return array_map(fn ($trip) => $this->formatJourney($trip), $stmt->fetchAll());
    }

    private function getSavedDestinations(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT item_slug, created_at
             FROM saved_items
             WHERE user_id = :user_id AND item_type = 'destination'
             ORDER BY created_at DESC"
        );
        $stmt->execute(['user_id' => $userId]);
        $savedRows = $stmt->fetchAll();

        if (!$savedRows) {
            return [];
        }

        $saved = [];
        $detailsBySlug = $this->getDestinationDetailsBySlug(array_column($savedRows, 'item_slug'));

        foreach ($savedRows as $row) {
            $slug = (string) ($row['item_slug'] ?? '');
            if ($slug === '') {
                continue;
            }

            $details = $detailsBySlug[$slug] ?? $this->destinationFromSlug($slug);
            $saved[] = [
                'name' => $details['name'],
                'country' => $details['country'],
                'slug' => $slug,
                'image' => $this->destinationImage($details['country'], $details['name']),
                'savedAt' => $row['created_at'] ?? null,
            ];
        }

        return $saved;
    }

    private function getDestinationDetailsBySlug(array $slugs): array
    {
        $slugs = array_values(array_filter(array_unique(array_map('strval', $slugs))));
        if (!$slugs || !$this->hasTable('destinations') || !$this->hasTable('countries')) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($slugs), '?'));
        $stmt = $this->pdo->prepare(
            "SELECT
                CONCAT(c.slug, '-', d.slug) AS saved_slug,
                d.name,
                c.name AS country
             FROM destinations d
             INNER JOIN countries c ON c.id = d.country_id
             WHERE d.slug IN ($placeholders) OR CONCAT(c.slug, '-', d.slug) IN ($placeholders)"
        );
        $stmt->execute([...$slugs, ...$slugs]);

        $details = [];
        foreach ($stmt->fetchAll() as $row) {
            $details[(string) $row['saved_slug']] = [
                'name' => (string) $row['name'],
                'country' => (string) $row['country'],
            ];
        }

        return $details;
    }

    private function getPublicPosts(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT
                cp.id,
                cp.trip_id,
                cp.title,
                cp.description,
                cp.cover_image,
                cp.created_at,
                t.trip_data,
                COUNT(DISTINCT pl.id) AS likes,
                COUNT(DISTINCT pc.id) AS comments
             FROM community_posts cp
             INNER JOIN trips t ON t.id = cp.trip_id
             LEFT JOIN post_likes pl ON pl.post_id = cp.id
             LEFT JOIN post_comments pc ON pc.post_id = cp.id
             WHERE cp.user_id = :user_id AND cp.status = 'public'
             GROUP BY cp.id, cp.trip_id, cp.title, cp.description, cp.cover_image, cp.created_at, t.trip_data
             ORDER BY cp.created_at DESC"
        );
        $stmt->execute(['user_id' => $userId]);

        return array_map(fn ($post) => $this->formatPost($post), $stmt->fetchAll());
    }

    private function formatUser(array $user, array $visitedCountries): array
    {
        $joinedYear = $this->yearFromDate($user['created_at'] ?? null);
        $displayName = (string) ($user['display_name'] ?? $user['username'] ?? 'Traveller');

        return [
            'id' => (int) $user['id'],
            'name' => $displayName,
            'username' => (string) ($user['username'] ?? ''),
            'email' => (string) ($user['email'] ?? ''),
            'title' => $this->profileTitle(count($visitedCountries)),
            'joined' => $joinedYear ? "Joined $joinedYear" : 'Joined recently',
            'avatar' => (string) ($user['avatar_url'] ?? ''),
            'cover' => '/assets/images/aurora_mountain.jpg',
        ];
    }

    private function formatJourney(array $trip): array
    {
        $tripData = json_decode((string) ($trip['trip_data'] ?? '{}'), true) ?: [];
        $meta = is_array($tripData['meta'] ?? null) ? $tripData['meta'] : [];
        $timeline = is_array($tripData['timeline'] ?? null) ? $tripData['timeline'] : [];
        $diary = is_array($tripData['diary'] ?? null) ? $tripData['diary'] : null;
        $country = (string) ($meta['country'] ?? $this->firstCountryFromRoute($meta['countryRoute'] ?? []) ?? 'Nordic');
        $duration = (int) ($meta['duration'] ?? count($timeline));
        $coverImage = (string) ($trip['cover_image'] ?? '');

        return [
            'id' => (int) $trip['id'],
            'title' => (string) ($trip['title'] ?? "$country Trip Plan"),
            'country' => $country,
            'city' => $this->journeyStampLocation($timeline, $country),
            'days' => $duration > 0 ? $duration : null,
            'date' => $this->formatJourneyDate($meta['startDate'] ?? $trip['created_at'] ?? null),
            'image' => $coverImage ?: $this->countryImage($country),
            'hasPlanner' => true,
            'hasDiary' => $this->hasDiary($diary),
            'hasCommunityPost' => !empty($trip['post_id']),
            'isPublic' => (string) ($trip['visibility'] ?? 'private') === 'public',
            'postId' => isset($trip['post_id']) ? (int) $trip['post_id'] : null,
            'visibility' => (string) ($trip['visibility'] ?? 'private'),
            'diary' => $diary,
            'postType' => $this->journeyPostType(true, $this->hasDiary($diary)),
        ];
    }

    private function formatPost(array $post): array
    {
        $tripData = json_decode((string) ($post['trip_data'] ?? '{}'), true) ?: [];
        $meta = is_array($tripData['meta'] ?? null) ? $tripData['meta'] : [];
        $timeline = is_array($tripData['timeline'] ?? null) ? $tripData['timeline'] : [];
        $diary = is_array($tripData['diary'] ?? null) ? $tripData['diary'] : null;
        $hasPlanner = !empty($timeline);
        $hasDiary = $this->hasDiary($diary);

        return [
            'id' => (int) $post['id'],
            'tripId' => (int) $post['trip_id'],
            'type' => $this->journeyPostType($hasPlanner, $hasDiary),
            'title' => (string) ($post['title'] ?? 'Untitled public journey'),
            'desc' => (string) ($post['description'] ?? $tripData['summary'] ?? 'Shared to the Beyond 66 community.'),
            'icon' => 'mdi-map',
            'country' => (string) ($meta['country'] ?? 'Nordic'),
            'image' => (string) ($post['cover_image'] ?? ''),
            'likes' => (int) ($post['likes'] ?? 0),
            'comments' => (int) ($post['comments'] ?? 0),
            'createdAt' => $post['created_at'] ?? null,
        ]; 
    }

    private function buildStats(array $visitedCountries, array $journeys, array $savedDestinations, array $publicPosts): array
    {
        return [
            ['label' => 'Countries Explored', 'value' => count($visitedCountries), 'icon' => 'bi bi-globe-europe-africa'],
            ['label' => 'Journeys Created', 'value' => count($journeys), 'icon' => 'bi bi-map'],
            ['label' => 'Travel Diaries', 'value' => count(array_filter($journeys, fn ($journey) => $journey['hasDiary'])), 'icon' => 'bi bi-journal-richtext'],
            ['label' => 'Saved Places', 'value' => count($savedDestinations), 'icon' => 'bi bi-bookmark-heart'],
            ['label' => 'Public Stories', 'value' => count($publicPosts), 'icon' => 'bi bi-people'],
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

    private function buildPassportCountries(array $visitedCountries, array $journeys): array
    {
        $visitedLookup = array_flip($visitedCountries);
        $passport = [];

        foreach ($this->countryCodes as $country => $code) {
            $journey = $this->firstJourneyForCountry($journeys, $country);
            $meta = $this->passportMeta($country);

            $passport[] = [
                'name' => $country,
                'code' => $code,
                'stamped' => isset($visitedLookup[$country]),
                'city' => $journey['city'] ?? $meta['city'],
                'date' => $journey['date'] ?? '',
                'color' => $meta['color'],
                'shape' => $meta['shape'],
                'icon' => $meta['icon'],
            ];
        }

        return $passport;
    }

    private function firstJourneyForCountry(array $journeys, string $country): ?array
    {
        foreach ($journeys as $journey) {
            if (($journey['country'] ?? '') === $country) {
                return $journey;
            }
        }

        return null;
    }

    private function passportMeta(string $country): array
    {
        $meta = [
            'Norway' => [
                'city' => 'Bergen',
                'color' => '#2563EB',
                'shape' => 'circle',
                'icon' => 'bi bi-gem',
            ],
            'Sweden' => [
                'city' => 'Stockholm',
                'color' => '#EAB308',
                'shape' => 'hex',
                'icon' => 'bi bi-tree-fill',
            ],
            'Finland' => [
                'city' => 'Lapland',
                'color' => '#06B6D4',
                'shape' => 'shield',
                'icon' => 'bi bi-snow2',
            ],
            'Iceland' => [
                'city' => 'Reykjavik',
                'color' => '#EF4444',
                'shape' => 'diamond',
                'icon' => 'bi bi-tsunami',
            ],
            'Denmark' => [
                'city' => 'Copenhagen',
                'color' => '#8B5CF6',
                'shape' => 'square',
                'icon' => 'bi bi-water',
            ],
        ];

        return $meta[$country] ?? [
            'city' => 'Nordic',
            'color' => '#1265b3',
            'shape' => 'circle',
            'icon' => 'bi bi-compass',
        ];
    }

    private function buildAchievements(array $visitedCountries, array $journeys, array $savedDestinations, array $publicPosts): array
    {
        $achievements = [];

        if (count($visitedCountries) >= 1) {
            $achievements[] = [
                'title' => 'Nordic Passport',
                'desc' => 'Explored ' . count($visitedCountries) . ' Nordic ' . (count($visitedCountries) === 1 ? 'country' : 'countries'),
                'icon' => 'mdi-passport',
            ];
        }

        if (count($journeys) >= 1) {
            $achievements[] = [
                'title' => 'Route Builder',
                'desc' => 'Created ' . count($journeys) . ' travel ' . (count($journeys) === 1 ? 'journey' : 'journeys'),
                'icon' => 'mdi-map-outline',
            ];
        }

        if (count($savedDestinations) >= 3) {
            $achievements[] = [
                'title' => 'Dream List Curator',
                'desc' => 'Saved ' . count($savedDestinations) . ' destinations for later',
                'icon' => 'mdi-bookmark-check',
            ];
        }

        if (count($publicPosts) >= 1) {
            $achievements[] = [
                'title' => 'Storyteller',
                'desc' => 'Shared ' . count($publicPosts) . ' public travel ' . (count($publicPosts) === 1 ? 'story' : 'stories'),
                'icon' => 'mdi-feather',
            ];
        }

        if (!$achievements) {
            $achievements[] = [
                'title' => 'First Steps',
                'desc' => 'Create a planner, save destinations, or publish a story to unlock badges',
                'icon' => 'mdi-compass-outline',
            ];
        }

        return $achievements;
    }

    private function getVisitedCountries(array $journeys): array
    {
        $countries = [];
        foreach ($journeys as $journey) {
            $country = (string) ($journey['country'] ?? '');
            if ($country !== '' && $country !== 'Nordic') {
                $countries[$country] = true;
            }
        }

        return array_keys($countries);
    }

    private function journeyStampLocation(array $timeline, string $country): string
    {
        foreach ($timeline as $day) {
            if (!is_array($day)) {
                continue;
            }

            $destination = trim((string) ($day['destination'] ?? ''));
            if ($destination !== '') {
                return $destination;
            }
        }

        return $this->passportMeta($country)['city'];
    }

    private function destinationFromSlug(string $slug): array
    {
        $parts = explode('-', $slug);
        $country = 'Nordic';

        if ($parts && isset($this->countrySlugs[$parts[0]])) {
            $country = $this->countrySlugs[array_shift($parts)];
        }

        $name = implode(' ', $parts);
        $name = $name !== '' ? ucwords($name) : 'Saved destination';

        return [
            'name' => $name,
            'country' => $country,
        ];
    }

    private function hasTable(string $table): bool
    {
        $stmt = $this->pdo->prepare('SHOW TABLES LIKE :table_name');
        $stmt->execute(['table_name' => $table]);

        return (bool) $stmt->fetchColumn();
    }

    private function profileTitle(int $countryCount): string
    {
        if ($countryCount >= 5) {
            return 'Nordic Explorer';
        }

        if ($countryCount >= 3) {
            return 'Arctic Wayfinder';
        }

        if ($countryCount >= 1) {
            return 'Nordic Traveller';
        }

        return 'Future Nordic Explorer';
    }

    private function countryImage(string $country): string
    {
        $images = [
            'Norway' => '/assets/images/Norway/Geirangerfjord.jpg',
            'Sweden' => '/assets/images/Sweden/AbiskoNationalPark.jpg',
            'Finland' => '/assets/images/Finland/Rovaniemi.jpg',
            'Iceland' => '/assets/images/Iceland/JokulsarlonGlacierLagoon.jpg',
            'Denmark' => '/assets/images/Denmark/Nyhavn.jpg',
        ];

        return $images[$country] ?? '/assets/images/aurora_mountain.jpg';
    }

    private function destinationImage(string $country, string $name): string
    {
        $normalized = strtolower($name);
        $specific = [
            'geirangerfjord' => '/assets/images/Norway/Geirangerfjord.jpg',
            'abisko national park' => '/assets/images/Sweden/AbiskoNationalPark.jpg',
            'nyhavn' => '/assets/images/Denmark/Nyhavn.jpg',
            'rovaniemi' => '/assets/images/Finland/Rovaniemi.jpg',
            'jokulsarlon glacier lagoon' => '/assets/images/Iceland/JokulsarlonGlacierLagoon.jpg',
        ];

        return $specific[$normalized] ?? $this->countryImage($country);
    }

    private function firstCountryFromRoute(mixed $route): ?string
    {
        return is_array($route) && !empty($route[0]) ? (string) $route[0] : null;
    }

    private function yearFromDate(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        $timestamp = strtotime($value);
        return $timestamp ? date('Y', $timestamp) : null;
    }

    private function formatJourneyDate(?string $value): string
    {
        if (!$value) {
            return 'Recently';
        }

        $timestamp = strtotime($value);
        return $timestamp ? date('M Y', $timestamp) : 'Recently';
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
