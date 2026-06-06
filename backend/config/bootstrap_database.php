<?php

function ensureDatabaseReady(): void
{
    static $initialized = false;

    if ($initialized) {
        return;
    }

    $initialized = true;

    $config = require __DIR__ . '/database.php';

    $dbName = $config['dbname'];
    $charset = $config['charset'] ?? 'utf8mb4';

    $dbConn = new mysqli(
        $config['host'],
        $config['username'],
        $config['password'],
        $dbName,
        (int) $config['port']
    );

    if ($dbConn->connect_error) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Database connection failed'
        ]);
        exit;
    }

    $dbConn->set_charset($charset);

    $oldCommunityExists = $dbConn->query("SHOW TABLES LIKE 'community_posts'");
    if ($oldCommunityExists && $oldCommunityExists->num_rows > 0) {
        $oldCommunityColumn = $dbConn->query("SHOW COLUMNS FROM community_posts LIKE 'trip_id'");
        $hasNewCommunitySchema = $oldCommunityColumn && $oldCommunityColumn->num_rows > 0;

        if (!$hasNewCommunitySchema) {
            $dbConn->query("DROP TABLE IF EXISTS forum_likes");
            $dbConn->query("DROP TABLE IF EXISTS post_images");
            $dbConn->query("DROP TABLE IF EXISTS community_comments");
            $dbConn->query("DROP TABLE IF EXISTS community_posts");
        }
    }

    $tables = [

        // USERS
        "CREATE TABLE IF NOT EXISTS users (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(191) NOT NULL UNIQUE,
            username VARCHAR(50) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            display_name VARCHAR(120) NOT NULL,
            avatar_url VARCHAR(500) NULL,
            preferred_language VARCHAR(10) DEFAULT 'en',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB",

        // TRIPS
        "CREATE TABLE IF NOT EXISTS trips (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NOT NULL,
            title VARCHAR(255) NULL,
            description TEXT NULL,
            cover_image MEDIUMTEXT NULL,
            visibility ENUM('private','public') DEFAULT 'private',
            trip_data JSON NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            CONSTRAINT fk_trip_user
                FOREIGN KEY (user_id) REFERENCES users(id)
                ON DELETE CASCADE,

            INDEX idx_trips_user_updated (user_id, updated_at),
            INDEX idx_trips_visibility_updated (visibility, updated_at)
        ) ENGINE=InnoDB",

        // COMMUNITY POSTS
        "CREATE TABLE IF NOT EXISTS community_posts (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            trip_id BIGINT UNSIGNED NOT NULL,
            user_id BIGINT UNSIGNED NOT NULL,
            title VARCHAR(255) NULL,
            description TEXT NULL,
            cover_image MEDIUMTEXT NULL,
            status ENUM('public','private') DEFAULT 'public',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            CONSTRAINT fk_post_trip
                FOREIGN KEY (trip_id) REFERENCES trips(id)
                ON DELETE CASCADE,

            CONSTRAINT fk_post_user
                FOREIGN KEY (user_id) REFERENCES users(id)
                ON DELETE CASCADE,

            UNIQUE KEY uk_community_trip (trip_id),
            INDEX idx_post_status_created (status, created_at),
            INDEX idx_post_user_created (user_id, created_at)
        ) ENGINE=InnoDB",

        // POST PORTFOLIOS
        "CREATE TABLE IF NOT EXISTS post_portfolios (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            post_id BIGINT UNSIGNED NOT NULL,
            user_id BIGINT UNSIGNED NOT NULL,
            title VARCHAR(255) NULL,
            content TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

            CONSTRAINT fk_portfolio_post
                FOREIGN KEY (post_id) REFERENCES community_posts(id)
                ON DELETE CASCADE,

            CONSTRAINT fk_portfolio_user
                FOREIGN KEY (user_id) REFERENCES users(id)
                ON DELETE CASCADE,

            INDEX idx_portfolio_post_created (post_id, created_at)
        ) ENGINE=InnoDB",

        // PORTFOLIO IMAGES
        "CREATE TABLE IF NOT EXISTS portfolio_images (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            portfolio_id BIGINT UNSIGNED NOT NULL,
            image_url VARCHAR(500) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

            CONSTRAINT fk_portfolio_image
                FOREIGN KEY (portfolio_id) REFERENCES post_portfolios(id)
                ON DELETE CASCADE,

            INDEX idx_portfolio_images_portfolio_id (portfolio_id)
        ) ENGINE=InnoDB",

        // POST LIKES
        "CREATE TABLE IF NOT EXISTS post_likes (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            post_id BIGINT UNSIGNED NOT NULL,
            user_id BIGINT UNSIGNED NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

            CONSTRAINT fk_like_post
                FOREIGN KEY (post_id) REFERENCES community_posts(id)
                ON DELETE CASCADE,

            CONSTRAINT fk_like_user
                FOREIGN KEY (user_id) REFERENCES users(id)
                ON DELETE CASCADE,

            UNIQUE KEY unique_like (post_id, user_id)
        ) ENGINE=InnoDB",

        // POST COMMENTS
        "CREATE TABLE IF NOT EXISTS post_comments (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            post_id BIGINT UNSIGNED NOT NULL,
            user_id BIGINT UNSIGNED NOT NULL,
            comment TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

            CONSTRAINT fk_post_comment_post
                FOREIGN KEY (post_id) REFERENCES community_posts(id)
                ON DELETE CASCADE,

            CONSTRAINT fk_post_comment_user
                FOREIGN KEY (user_id) REFERENCES users(id)
                ON DELETE CASCADE,

            INDEX idx_post_comment_created (post_id, created_at)
        ) ENGINE=InnoDB",

        // SAVED POSTS
        "CREATE TABLE IF NOT EXISTS saved_posts (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            post_id BIGINT UNSIGNED NOT NULL,
            user_id BIGINT UNSIGNED NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

            CONSTRAINT fk_saved_post_post
                FOREIGN KEY (post_id) REFERENCES community_posts(id)
                ON DELETE CASCADE,

            CONSTRAINT fk_saved_post_user
                FOREIGN KEY (user_id) REFERENCES users(id)
                ON DELETE CASCADE,

            UNIQUE KEY unique_save (post_id, user_id)
        ) ENGINE=InnoDB",

        // SAVED ITEMS
        // Kept for destination/country/attraction saves used outside community posts.
        "CREATE TABLE IF NOT EXISTS saved_items (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NOT NULL,
            item_type ENUM('planner','community_post','country','destination','attraction') NOT NULL,
            item_id BIGINT UNSIGNED NULL,
            item_slug VARCHAR(180) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

            CONSTRAINT fk_saved_user
                FOREIGN KEY (user_id) REFERENCES users(id)
                ON DELETE CASCADE,

            UNIQUE KEY uk_saved_unique (user_id, item_type, item_id, item_slug),
            INDEX idx_saved_user_created (user_id, created_at),
            INDEX idx_saved_item_slug (item_slug)
        ) ENGINE=InnoDB"
    ];

    foreach ($tables as $sql) {
        if (!$dbConn->query($sql)) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Table migration failed',
                'error' => $dbConn->error
            ]);
            exit;
        }
    }

    $oldPlannerExists = $dbConn->query("SHOW TABLES LIKE 'planner_plans'");
    if ($oldPlannerExists && $oldPlannerExists->num_rows > 0) {
        $oldPlans = $dbConn->query("SELECT * FROM planner_plans ORDER BY id ASC");
        if ($oldPlans) {
            while ($plan = $oldPlans->fetch_assoc()) {
                $tripExistsStmt = $dbConn->prepare("SELECT id FROM trips WHERE id = ? LIMIT 1");
                $oldPlanId = (int) $plan['id'];
                $tripExistsStmt->bind_param('i', $oldPlanId);
                $tripExistsStmt->execute();
                $tripExistsResult = $tripExistsStmt->get_result();
                $tripExists = $tripExistsResult && $tripExistsResult->num_rows > 0;
                $tripExistsStmt->close();

                if ($tripExists) {
                    continue;
                }

                $decodedNotes = json_decode((string) ($plan['notes'] ?? '{}'), true);
                if (!is_array($decodedNotes)) {
                    $decodedNotes = [];
                }

                $days = [];
                $daysStmt = $dbConn->prepare(
                    "SELECT id, day_number FROM planner_days WHERE planner_plan_id = ? ORDER BY day_number ASC"
                );
                $daysStmt->bind_param('i', $oldPlanId);
                $daysStmt->execute();
                $daysResult = $daysStmt->get_result();
                while ($day = $daysResult->fetch_assoc()) {
                    $dayId = (int) $day['id'];
                    $items = [];
                    $itemsStmt = $dbConn->prepare(
                        "SELECT title FROM planner_items WHERE planner_day_id = ? ORDER BY sort_order ASC"
                    );
                    $itemsStmt->bind_param('i', $dayId);
                    $itemsStmt->execute();
                    $itemsResult = $itemsStmt->get_result();
                    while ($item = $itemsResult->fetch_assoc()) {
                        $items[] = (string) $item['title'];
                    }
                    $itemsStmt->close();

                    $days[] = [
                        'day' => (int) $day['day_number'],
                        'items' => $items,
                    ];
                }
                $daysStmt->close();

                $tripMeta = is_array($decodedNotes['tripMeta'] ?? null) ? $decodedNotes['tripMeta'] : [
                    'country' => ucfirst(str_replace('-', ' ', (string) ($plan['country_slug'] ?? 'Nordic'))),
                    'duration' => (int) ($plan['total_days'] ?? max(1, count($days))),
                    'style' => (string) ($plan['travel_style'] ?? 'balanced'),
                ];

                $tripData = json_encode([
                    'meta' => $tripMeta,
                    'timeline' => is_array($decodedNotes['timelineDays'] ?? null) ? $decodedNotes['timelineDays'] : $days,
                    'budget' => is_array($decodedNotes['budgetEstimate'] ?? null) ? $decodedNotes['budgetEstimate'] : [],
                    'weather' => is_array($decodedNotes['weatherForecast'] ?? null) ? $decodedNotes['weatherForecast'] : [],
                    'aurora' => $decodedNotes['auroraForecast'] ?? [],
                    'checklist' => is_array($decodedNotes['packingList'] ?? null) ? $decodedNotes['packingList'] : [],
                    'summary' => (string) ($decodedNotes['summary'] ?? ''),
                ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

                $visibility = ((int) ($plan['is_public'] ?? 0) === 1) ? 'public' : 'private';
                $description = (string) ($decodedNotes['description'] ?? '');
                $coverImage = (string) ($decodedNotes['coverImage'] ?? '');
                $title = (string) ($plan['title'] ?? 'Nordic Trip Plan');
                $userId = (int) $plan['user_id'];
                $createdAt = (string) ($plan['created_at'] ?? date('Y-m-d H:i:s'));
                $updatedAt = (string) ($plan['updated_at'] ?? $createdAt);

                $insertTrip = $dbConn->prepare(
                    "INSERT INTO trips (id, user_id, title, description, cover_image, visibility, trip_data, created_at, updated_at)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
                );
                $insertTrip->bind_param(
                    'iisssssss',
                    $oldPlanId,
                    $userId,
                    $title,
                    $description,
                    $coverImage,
                    $visibility,
                    $tripData,
                    $createdAt,
                    $updatedAt
                );
                $insertTrip->execute();
                $insertTrip->close();
            }
        }

        $dbConn->query("DROP TABLE IF EXISTS planner_items");
        $dbConn->query("DROP TABLE IF EXISTS planner_days");
        $dbConn->query("DROP TABLE IF EXISTS planner_plans");
    }

    $dbConn->query("DROP TABLE IF EXISTS forum_likes");
    $dbConn->query("DROP TABLE IF EXISTS post_images");
    $dbConn->query("DROP TABLE IF EXISTS community_comments");

    $portfolioImagesColumn = $dbConn->query("SHOW COLUMNS FROM post_portfolios LIKE 'images'");
    if ($portfolioImagesColumn && $portfolioImagesColumn->num_rows > 0) {
        $portfolioRows = $dbConn->query("SELECT id, images FROM post_portfolios WHERE images IS NOT NULL AND images != ''");
        if ($portfolioRows) {
            $insertPortfolioImage = $dbConn->prepare(
                "INSERT INTO portfolio_images (portfolio_id, image_url) VALUES (?, ?)"
            );

            while ($portfolio = $portfolioRows->fetch_assoc()) {
                $portfolioId = (int) $portfolio['id'];
                $images = json_decode((string) $portfolio['images'], true);
                if (!is_array($images)) {
                    continue;
                }

                foreach ($images as $image) {
                    $imageUrl = is_array($image)
                        ? (string) ($image['image_url'] ?? $image['url'] ?? '')
                        : (string) $image;
                    $imageUrl = trim($imageUrl);
                    if ($imageUrl === '') {
                        continue;
                    }

                    $insertPortfolioImage->bind_param('is', $portfolioId, $imageUrl);
                    $insertPortfolioImage->execute();
                }
            }

            $insertPortfolioImage->close();
        }

        $dbConn->query("ALTER TABLE post_portfolios DROP COLUMN images");
    }

    $oldPrefs = $dbConn->query("SHOW TABLES LIKE 'user_preferences'");
    if ($oldPrefs && $oldPrefs->num_rows > 0) {
        $prefRows = $dbConn->query("SELECT COUNT(*) AS count_rows FROM user_preferences");
        $prefCount = $prefRows ? (int) ($prefRows->fetch_assoc()['count_rows'] ?? 0) : 0;
        if ($prefCount === 0) {
            $dbConn->query("DROP TABLE IF EXISTS user_preferences");
        }
    }

    // Backward-safe migration: add username if missing
    $columnExistsResult = $dbConn->query("SHOW COLUMNS FROM users LIKE 'username'");
    $columnExists = $columnExistsResult && $columnExistsResult->num_rows > 0;

    if (!$columnExists) {
        $dbConn->query("ALTER TABLE users ADD COLUMN username VARCHAR(50) NULL AFTER email");
        $dbConn->query("UPDATE users SET username = display_name WHERE username IS NULL OR username = ''");
        $dbConn->query("ALTER TABLE users MODIFY username VARCHAR(50) NOT NULL");
    }

    // Backward-safe migration: add preferred_language if missing
    $languageColumnResult = $dbConn->query("SHOW COLUMNS FROM users LIKE 'preferred_language'");
    $languageColumnExists = $languageColumnResult && $languageColumnResult->num_rows > 0;

    if (!$languageColumnExists) {
        $dbConn->query("ALTER TABLE users ADD COLUMN preferred_language VARCHAR(10) DEFAULT 'en' AFTER avatar_url");
    }

    // Backward-safe migration: add avatar_url if missing
    $avatarColumnResult = $dbConn->query("SHOW COLUMNS FROM users LIKE 'avatar_url'");
    $avatarColumnExists = $avatarColumnResult && $avatarColumnResult->num_rows > 0;

    if (!$avatarColumnExists) {
        $dbConn->query("ALTER TABLE users ADD COLUMN avatar_url VARCHAR(500) NULL AFTER display_name");
    }

    $dbConn->query("ALTER TABLE trips MODIFY cover_image MEDIUMTEXT NULL");
    $dbConn->query("ALTER TABLE community_posts MODIFY cover_image MEDIUMTEXT NULL");

    // Backward-safe migration: older setup scripts created saved_items without item_slug
    // and with a narrower item_type enum.
    $savedItemSlugResult = $dbConn->query("SHOW COLUMNS FROM saved_items LIKE 'item_slug'");
    $savedItemSlugExists = $savedItemSlugResult && $savedItemSlugResult->num_rows > 0;

    if (!$savedItemSlugExists) {
        $dbConn->query("ALTER TABLE saved_items ADD COLUMN item_slug VARCHAR(180) NULL AFTER item_id");
    }

    $dbConn->query(
        "ALTER TABLE saved_items MODIFY item_type ENUM('planner','community_post','country','destination','attraction') NOT NULL"
    );

    $savedUniqueIndexResult = $dbConn->query("SHOW INDEX FROM saved_items WHERE Key_name = 'uk_saved_unique'");
    if ($savedUniqueIndexResult && $savedUniqueIndexResult->num_rows > 0) {
        $dbConn->query("ALTER TABLE saved_items DROP INDEX uk_saved_unique");
    }
    $dbConn->query("ALTER TABLE saved_items ADD UNIQUE KEY uk_saved_unique (user_id, item_type, item_id, item_slug)");

    $savedSlugIndexResult = $dbConn->query("SHOW INDEX FROM saved_items WHERE Key_name = 'idx_saved_item_slug'");
    if (!$savedSlugIndexResult || $savedSlugIndexResult->num_rows === 0) {
        $dbConn->query("ALTER TABLE saved_items ADD INDEX idx_saved_item_slug (item_slug)");
    }

    $seedEmail = 'demo@beyond66.com';
    $seedUsername = 'demo';
    $seedPasswordHash = password_hash('password123', PASSWORD_DEFAULT);
    $seedDisplayName = 'Demo User';

    $seedStmt = $dbConn->prepare(
        'INSERT INTO users (email, username, password_hash, display_name)
         SELECT ?, ?, ?, ?
         WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = ? OR username = ?)'
    );
    $seedStmt->bind_param(
        'ssssss',
        $seedEmail,
        $seedUsername,
        $seedPasswordHash,
        $seedDisplayName,
        $seedEmail,
        $seedUsername
    );
    $seedStmt->execute();
    $seedStmt->close();

    $communityUsers = [
        ['ellie@beyond66.com', 'ellie', 'Ellie Frost'],
        ['sarah@beyond66.com', 'sarah', 'Sarah North'],
        ['mika@beyond66.com', 'mika', 'Mika Lane'],
    ];

    $communityUserStmt = $dbConn->prepare(
        'INSERT INTO users (email, username, password_hash, display_name)
         SELECT ?, ?, ?, ?
         WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = ? OR username = ?)'
    );
    $communityPasswordHash = password_hash('password123', PASSWORD_DEFAULT);
    foreach ($communityUsers as [$email, $username, $displayName]) {
        $communityUserStmt->bind_param(
            'ssssss',
            $email,
            $username,
            $communityPasswordHash,
            $displayName,
            $email,
            $username
        );
        $communityUserStmt->execute();
    }
    $communityUserStmt->close();

    $lookupUserStmt = $dbConn->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
    $getUserId = function (string $username) use ($lookupUserStmt): int {
        $lookupUserStmt->bind_param('s', $username);
        $lookupUserStmt->execute();
        $result = $lookupUserStmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;

        return (int) ($row['id'] ?? 1);
    };

    $communitySeeds = [
        [
            'username' => 'ellie',
            'title' => 'Aurora Nights in Tromso',
            'description' => 'A slow winter route for chasing northern lights, warming up in cafes, and drifting through snowy fjords.',
            'cover' => '/assets/images/Norway/TromsøNorway.jpg',
            'meta' => [
                'country' => 'Norway',
                'duration' => 5,
                'season' => 'Winter',
                'style' => 'Nature',
                'tripType' => 'Friends',
            ],
            'timeline' => [
                ['day' => 1, 'items' => ['Arrive in Tromso', 'Explore the harbour', 'Dinner in the city centre']],
                ['day' => 2, 'items' => ['Fjord cruise', 'Cable car viewpoint', 'Northern lights chase']],
                ['day' => 3, 'items' => ['Dog sledding tour', 'Sauna evening']],
            ],
            'likes' => ['demo', 'sarah', 'mika'],
            'comments' => [
                ['demo', 'This route feels perfect for a first aurora trip.'],
                ['sarah', 'Adding the cable car viewpoint to my list.'],
            ],
        ],
        [
            'username' => 'sarah',
            'title' => 'Iceland Ring Road Highlights',
            'description' => 'Waterfalls, black sand beaches, glacier lagoons, and compact stops for a scenic Iceland road trip.',
            'cover' => '/assets/images/Iceland/JokulsarlonGlacierLagoon.jpg',
            'meta' => [
                'country' => 'Iceland',
                'duration' => 7,
                'season' => 'Autumn',
                'style' => 'Road Trip',
                'tripType' => 'Couple',
            ],
            'timeline' => [
                ['day' => 1, 'items' => ['Reykjavik arrival', 'Golden Circle stops']],
                ['day' => 2, 'items' => ['Seljalandsfoss', 'Skogafoss', 'Reynisfjara']],
                ['day' => 3, 'items' => ['Jokulsarlon Glacier Lagoon', 'Diamond Beach']],
            ],
            'likes' => ['demo', 'ellie'],
            'comments' => [
                ['mika', 'The glacier lagoon photo stop is unreal.'],
            ],
        ],
        [
            'username' => 'mika',
            'title' => 'Finland Cabin and Sauna Escape',
            'description' => 'A quiet Lapland plan with forest cabins, sauna nights, snowy trails, and a gentle Rovaniemi day.',
            'cover' => '/assets/images/Finland/Rovaniemi.jpg',
            'meta' => [
                'country' => 'Finland',
                'duration' => 4,
                'season' => 'Winter',
                'style' => 'Relaxed',
                'tripType' => 'Family',
            ],
            'timeline' => [
                ['day' => 1, 'items' => ['Arrive in Rovaniemi', 'Cabin check-in', 'Private sauna']],
                ['day' => 2, 'items' => ['Snowshoe trail', 'Local market lunch']],
                ['day' => 3, 'items' => ['Santa Claus Village', 'Evening aurora watch']],
            ],
            'likes' => ['demo', 'ellie', 'sarah'],
            'comments' => [
                ['ellie', 'This looks cozy and easy to follow.'],
            ],
        ],
    ];

    $findTripStmt = $dbConn->prepare('SELECT id FROM trips WHERE user_id = ? AND title = ? LIMIT 1');
    $insertTripStmt = $dbConn->prepare(
        'INSERT INTO trips (user_id, title, description, cover_image, visibility, trip_data)
         VALUES (?, ?, ?, ?, "public", ?)'
    );
    $updateTripStmt = $dbConn->prepare(
        'UPDATE trips
         SET description = ?, cover_image = ?, visibility = "public", trip_data = ?
         WHERE id = ?'
    );
    $upsertPostStmt = $dbConn->prepare(
        'INSERT INTO community_posts (trip_id, user_id, title, description, cover_image, status)
         VALUES (?, ?, ?, ?, ?, "public")
         ON DUPLICATE KEY UPDATE
             title = VALUES(title),
             description = VALUES(description),
             cover_image = VALUES(cover_image),
             status = "public"'
    );
    $findPostStmt = $dbConn->prepare('SELECT id FROM community_posts WHERE trip_id = ? LIMIT 1');
    $insertLikeStmt = $dbConn->prepare(
        'INSERT IGNORE INTO post_likes (post_id, user_id) VALUES (?, ?)'
    );
    $insertCommentStmt = $dbConn->prepare(
        'INSERT INTO post_comments (post_id, user_id, comment)
         SELECT ?, ?, ?
         WHERE NOT EXISTS (
             SELECT 1 FROM post_comments
             WHERE post_id = ? AND user_id = ? AND comment = ?
         )'
    );

    foreach ($communitySeeds as $seed) {
        $userId = $getUserId($seed['username']);
        $title = $seed['title'];
        $description = $seed['description'];
        $cover = $seed['cover'];
        $tripData = json_encode([
            'meta' => $seed['meta'],
            'timeline' => $seed['timeline'],
            'budget' => [],
            'weather' => [],
            'aurora' => [],
            'checklist' => [],
            'summary' => $description,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $findTripStmt->bind_param('is', $userId, $title);
        $findTripStmt->execute();
        $tripResult = $findTripStmt->get_result();
        $trip = $tripResult ? $tripResult->fetch_assoc() : null;

        if ($trip) {
            $tripId = (int) $trip['id'];
            $updateTripStmt->bind_param('sssi', $description, $cover, $tripData, $tripId);
            $updateTripStmt->execute();
        } else {
            $insertTripStmt->bind_param('issss', $userId, $title, $description, $cover, $tripData);
            $insertTripStmt->execute();
            $tripId = (int) $dbConn->insert_id;
        }

        $upsertPostStmt->bind_param('iisss', $tripId, $userId, $title, $description, $cover);
        $upsertPostStmt->execute();

        $findPostStmt->bind_param('i', $tripId);
        $findPostStmt->execute();
        $postResult = $findPostStmt->get_result();
        $post = $postResult ? $postResult->fetch_assoc() : null;
        $postId = (int) ($post['id'] ?? 0);

        if ($postId <= 0) {
            continue;
        }

        foreach ($seed['likes'] as $likedBy) {
            $likedByUserId = $getUserId($likedBy);
            $insertLikeStmt->bind_param('ii', $postId, $likedByUserId);
            $insertLikeStmt->execute();
        }

        foreach ($seed['comments'] as [$commentBy, $comment]) {
            $commentByUserId = $getUserId($commentBy);
            $insertCommentStmt->bind_param(
                'iisiss',
                $postId,
                $commentByUserId,
                $comment,
                $postId,
                $commentByUserId,
                $comment
            );
            $insertCommentStmt->execute();
        }
    }

    $lookupUserStmt->close();
    $findTripStmt->close();
    $insertTripStmt->close();
    $updateTripStmt->close();
    $upsertPostStmt->close();
    $findPostStmt->close();
    $insertLikeStmt->close();
    $insertCommentStmt->close();

    $dbConn->close();
}
