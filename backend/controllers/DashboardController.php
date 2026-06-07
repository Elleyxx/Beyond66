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
            $savedPosts = $this->getSavedPosts($userId);
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
                    'savedPosts' => $savedPosts,
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
                'image' => $this->destinationImage($details['country'], $details['name'], $slug),
                'savedAt' => $row['created_at'] ?? null,
            ];
        }

        return $saved;
    }

    private function getSavedPosts(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT
                sp.post_id,
                sp.created_at AS saved_at,
                cp.title,
                cp.description,
                cp.cover_image,
                u.display_name AS author_name,
                t.trip_data,
                COUNT(DISTINCT pl.id) AS likes,
                COUNT(DISTINCT pc.id) AS comments
             FROM saved_posts sp
             INNER JOIN community_posts cp ON cp.id = sp.post_id AND cp.status = 'public'
             INNER JOIN users u ON u.id = cp.user_id
             INNER JOIN trips t ON t.id = cp.trip_id
             LEFT JOIN post_likes pl ON pl.post_id = cp.id
             LEFT JOIN post_comments pc ON pc.post_id = cp.id
             WHERE sp.user_id = :user_id
             GROUP BY sp.post_id, sp.created_at, cp.title, cp.description, cp.cover_image,
                      u.display_name, t.trip_data
             ORDER BY sp.created_at DESC"
        );
        $stmt->execute(['user_id' => $userId]);

        return array_map(fn ($post) => $this->formatSavedPost($post), $stmt->fetchAll());
    }

    private function formatSavedPost(array $post): array
    {
        $tripData = json_decode((string) ($post['trip_data'] ?? '{}'), true) ?: [];
        $meta = is_array($tripData['meta'] ?? null) ? $tripData['meta'] : [];

        return [
            'id'          => (int) $post['post_id'],
            'title'       => (string) ($post['title'] ?? 'Untitled post'),
            'description' => (string) ($post['description'] ?? ''),
            'coverImage'  => (string) ($post['cover_image'] ?? ''),
            'authorName'  => (string) ($post['author_name'] ?? 'Traveller'),
            'country'     => (string) ($meta['country'] ?? 'Nordic'),
            'likes'       => (int) ($post['likes'] ?? 0),
            'comments'    => (int) ($post['comments'] ?? 0),
            'savedAt'     => $post['saved_at'] ?? null,
        ];
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
        $countryCount = count($visitedCountries);

        return [
            'id'         => (int) $user['id'],
            'name'       => $displayName,
            'username'   => (string) ($user['username'] ?? ''),
            'email'      => (string) ($user['email'] ?? ''),
            'titleKey'   => $this->profileTitleKey($countryCount),
            'joinedYear' => $joinedYear,
            'avatar'     => (string) ($user['avatar_url'] ?? ''),
            'cover'      => '/assets/images/aurora_mountain.jpg',
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
            ['key' => 'profilePage.stats.countriesExplored', 'value' => count($visitedCountries), 'icon' => 'bi bi-globe-europe-africa'],
            ['key' => 'profilePage.stats.journeysCreated', 'value' => count($journeys), 'icon' => 'bi bi-map'],
            ['key' => 'profilePage.stats.travelDiaries', 'value' => count(array_filter($journeys, fn ($journey) => $journey['hasDiary'])), 'icon' => 'bi bi-journal-richtext'],
            ['key' => 'profilePage.stats.savedPlaces', 'value' => count($savedDestinations), 'icon' => 'bi bi-bookmark-heart'],
            ['key' => 'profilePage.stats.publicStories', 'value' => count($publicPosts), 'icon' => 'bi bi-people'],
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
        $countryCount      = count($visitedCountries);
        $journeyCount      = count($journeys);
        $savedCount        = count($savedDestinations);
        $publicPostCount   = count($publicPosts);

        $achievements = [];

        if ($countryCount >= 1) {
            $achievements[] = [
                'badgeKey' => 'nordicPassport',
                'icon'     => 'mdi-passport',
                'current'  => $countryCount,
                'unit'     => 'countries',
            ];
        }

        if ($journeyCount >= 1) {
            $achievements[] = [
                'badgeKey' => 'routeBuilder',
                'icon'     => 'mdi-map-outline',
                'current'  => $journeyCount,
                'unit'     => 'journeys',
            ];
        }

        if ($savedCount >= 3) {
            $achievements[] = [
                'badgeKey' => 'dreamListCurator',
                'icon'     => 'mdi-bookmark-check',
                'current'  => $savedCount,
                'unit'     => 'saved',
            ];
        }

        if ($publicPostCount >= 1) {
            $achievements[] = [
                'badgeKey' => 'storyteller',
                'icon'     => 'mdi-feather',
                'current'  => $publicPostCount,
                'unit'     => 'stories',
            ];
        }

        if (!$achievements) {
            $achievements[] = [
                'badgeKey' => 'firstSteps',
                'icon'     => 'mdi-compass-outline',
                'current'  => 0,
                'unit'     => 'progress',
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
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?'
        );
        $stmt->execute([$table]);

        return (bool) $stmt->fetchColumn();
    }

    private function profileTitleKey(int $countryCount): string
    {
        if ($countryCount >= 5) return 'profilePage.hero.titleExplorer';
        if ($countryCount >= 3) return 'profilePage.hero.titleWayfinder';
        if ($countryCount >= 1) return 'profilePage.hero.titleTraveller';
        return 'profilePage.hero.titleFuture';
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

    private function destinationImage(string $country, string $name, string $slug = ''): string
    {
        $bySlug = [
            // Norway
            'norway-oslo'                  => '/assets/images/Norway/oslo1.jpg',
            'norway-bergen'                => '/assets/images/Norway/bergen1.jpeg',
            'norway-troms'                 => '/assets/images/Norway/tromso1.jpg',
            'norway-lofoten-islands'       => '/assets/images/Norway/lofoten1.jpg',
            'norway-geirangerfjord'        => '/assets/images/Norway/geirangerfjord1.jpg',
            'norway-n-r-yfjord'            => '/assets/images/Norway/naeroyfjord1.jpeg',
            'norway-flam-railway'          => '/assets/images/Norway/flamrailway1.jpeg',
            'norway-preikestolen'          => '/assets/images/Norway/preikestolen1.jpeg',
            'norway-trolltunga'            => '/assets/images/Norway/trolltunga1.jpg',
            'norway-atlantic-ocean-road'   => '/assets/images/Norway/atlanticroad1.jpeg',
            'norway-svalbard'              => '/assets/images/Norway/svalbard1.jpeg',
            'norway-alesund'               => '/assets/images/Norway/alesund1.jpg',
            // Sweden
            'sweden-stockholm'             => '/assets/images/Sweden/stockholm1.jpg',
            'sweden-gamla-stan'            => '/assets/images/Sweden/gamlastan1.jpeg',
            'sweden-stockholm-archipelago' => '/assets/images/Sweden/stockholmarchipelago1.jpg',
            'sweden-gothenburg'            => '/assets/images/Sweden/gothenburg1.jpeg',
            'sweden-abisko-national-park'  => '/assets/images/Sweden/abisko1.jpg',
            'sweden-kiruna'                => '/assets/images/Sweden/kiruna1.jpg',
            'sweden-icehotel'              => '/assets/images/Sweden/icehotel1.jpeg',
            'sweden-visby'                 => '/assets/images/Sweden/visby1.jpeg',
            'sweden-malmo'                 => '/assets/images/Sweden/malmo1.jpg',
            'sweden-dalarna'               => '/assets/images/Sweden/dalarna1.jpg',
            'sweden-gotland'               => '/assets/images/Sweden/gotland1.jpeg',
            'sweden-uppsala'               => '/assets/images/Sweden/uppsala1.jpeg',
            // Finland
            'finland-helsinki'             => '/assets/images/Finland/helsinki1.jpeg',
            'finland-rovaniemi'            => '/assets/images/Finland/rovaniemi1.jpeg',
            'finland-santa-claus-village'  => '/assets/images/Finland/santaclaus1.jpg',
            'finland-suomenlinna-sea-fortress' => '/assets/images/Finland/suomenlinna1.jpg',
            'finland-lake-saimaa'          => '/assets/images/Finland/saimaa1.jpg',
            'finland-levi'                 => '/assets/images/Finland/levi1.jpeg',
            'finland-porvoo'               => '/assets/images/Finland/porvoo1.jpg',
            'finland-turku'                => '/assets/images/Finland/turku1.jpg',
            'finland-nuuksio-national-park' => '/assets/images/Finland/nuuksio1.jpeg',
            'finland-kakslauttanen'        => '/assets/images/Finland/kakslauttanen1.jpg',
            'finland-oulanka-national-park' => '/assets/images/Finland/oulanka1.jpeg',
            'finland-aland-islands'        => '/assets/images/Finland/aland1.jpeg',
            // Iceland
            'iceland-reykjavik'            => '/assets/images/Iceland/Reykjavík1.jpeg',
            'iceland-the-golden-circle'    => '/assets/images/Iceland/goldencircle1.jpeg',
            'iceland-jokulsarlon-glacier-lagoon' => '/assets/images/Iceland/jokulsarlon1.jpeg',
            'iceland-diamond-beach'        => '/assets/images/Iceland/diamondbeach1.jpeg',
            'iceland-vik-i-myrdal'         => '/assets/images/Iceland/vik1.jpg',
            'iceland-blue-lagoon'          => '/assets/images/Iceland/bluelagoon1.jpeg',
            'iceland-sky-lagoon'           => '/assets/images/Iceland/skylagoon1.jpeg',
            'iceland-skogafoss'            => '/assets/images/Iceland/skogafoss1.jpg',
            'iceland-seljalandsfoss'       => '/assets/images/Iceland/seljalandsfoss1.jpg',
            'iceland-sn-fellsnes-peninsula' => '/assets/images/Iceland/snaefellsnes1.jpg',
            'iceland-akureyri'             => '/assets/images/Iceland/akureyri1.jpg',
            'iceland-reynisfjara-black-sand-beach' => '/assets/images/Iceland/reynisfjara1.jpg',
            // Denmark
            'denmark-copenhagen'           => '/assets/images/Denmark/copenhagen1.jpeg',
            'denmark-nyhavn'               => '/assets/images/Denmark/nyhavn1.jpg',
            'denmark-tivoli-gardens'       => '/assets/images/Denmark/tivoli1.jpg',
            'denmark-aarhus'               => '/assets/images/Denmark/aarhus1.jpg',
            'denmark-odense'               => '/assets/images/Denmark/odense1.jpeg',
            'denmark-skagen'               => '/assets/images/Denmark/skagen1.jpeg',
            'denmark-legoland-billund'     => '/assets/images/Denmark/legoland1.jpg',
            'denmark-lego-house'           => '/assets/images/Denmark/legohouse1.jpg',
            'denmark-rosenborg-castle'     => '/assets/images/Denmark/rosenborg1.jpg',
            'denmark-kronborg-castle'      => '/assets/images/Denmark/kronborg1.jpeg',
            'denmark-m-ns-klint'           => '/assets/images/Denmark/monsklint1.jpg',
            'denmark-ribe'                 => '/assets/images/Denmark/ribe1.jpg',
        ];

        if ($slug !== '' && isset($bySlug[$slug])) {
            return $bySlug[$slug];
        }

        return $this->countryImage($country);
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
