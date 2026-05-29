<?php

function ensureDatabaseReady(): void
{
    static $initialized = false;

    if ($initialized) {
        return;
    }

    $initialized = true;

    $config = require __DIR__ . '/database.php';

    $serverConn = new mysqli(
        $config['host'],
        $config['username'],
        $config['password'],
        '',
        (int) $config['port']
    );

    if ($serverConn->connect_error) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Database server connection failed'
        ]);
        exit;
    }

    $dbName = $config['dbname'];
    $charset = $config['charset'] ?? 'utf8mb4';

    $createDbSql = "
        CREATE DATABASE IF NOT EXISTS `$dbName`
        CHARACTER SET $charset
        COLLATE {$charset}_unicode_ci
    ";

    if (!$serverConn->query($createDbSql)) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Database creation failed'
        ]);
        exit;
    }

    $serverConn->close();

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

        // USER PREFERENCES
        "CREATE TABLE IF NOT EXISTS user_preferences (
            user_id BIGINT UNSIGNED PRIMARY KEY,
            preferred_travel_style ENUM('budget','balanced','comfort','luxury') DEFAULT 'balanced',
            preferred_season ENUM('spring','summer','autumn','winter') NULL,
            budget_min DECIMAL(10,2) NULL,
            budget_max DECIMAL(10,2) NULL,
            interests JSON NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT fk_pref_user
                FOREIGN KEY (user_id) REFERENCES users(id)
                ON DELETE CASCADE
        ) ENGINE=InnoDB",

        // PLANNER PLANS
        "CREATE TABLE IF NOT EXISTS planner_plans (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NOT NULL,
            country_slug VARCHAR(120) NULL,
            title VARCHAR(180) NOT NULL,
            travel_style ENUM('budget','balanced','comfort','luxury') DEFAULT 'balanced',
            total_days TINYINT UNSIGNED NOT NULL,
            estimated_budget_min DECIMAL(10,2) NULL,
            estimated_budget_max DECIMAL(10,2) NULL,
            notes TEXT NULL,
            is_public TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            CONSTRAINT fk_planner_user
                FOREIGN KEY (user_id) REFERENCES users(id)
                ON DELETE CASCADE,

            INDEX idx_planner_user_created (user_id, created_at),
            INDEX idx_planner_country_slug (country_slug)
        ) ENGINE=InnoDB",

        // PLANNER DAYS
        "CREATE TABLE IF NOT EXISTS planner_days (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            planner_plan_id BIGINT UNSIGNED NOT NULL,
            day_number TINYINT UNSIGNED NOT NULL,
            base_destination_slug VARCHAR(180) NULL,
            day_title VARCHAR(180) NULL,
            notes TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            CONSTRAINT fk_planday_plan
                FOREIGN KEY (planner_plan_id) REFERENCES planner_plans(id)
                ON DELETE CASCADE,

            UNIQUE KEY uk_planner_day (planner_plan_id, day_number)
        ) ENGINE=InnoDB",

        // PLANNER ITEMS
        "CREATE TABLE IF NOT EXISTS planner_items (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            planner_day_id BIGINT UNSIGNED NOT NULL,
            attraction_slug VARCHAR(180) NULL,
            item_type ENUM('attraction','transport','meal','free_time','custom') DEFAULT 'custom',
            title VARCHAR(200) NOT NULL,
            start_time TIME NULL,
            end_time TIME NULL,
            est_cost DECIMAL(10,2) NULL,
            notes TEXT NULL,
            sort_order SMALLINT UNSIGNED DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            CONSTRAINT fk_planitem_day
                FOREIGN KEY (planner_day_id) REFERENCES planner_days(id)
                ON DELETE CASCADE,

            INDEX idx_planitem_day_order (planner_day_id, sort_order),
            INDEX idx_planitem_attraction_slug (attraction_slug)
        ) ENGINE=InnoDB",

        // COMMUNITY POSTS
        "CREATE TABLE IF NOT EXISTS community_posts (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NOT NULL,
            country_slug VARCHAR(120) NULL,
            title VARCHAR(220) NOT NULL,
            body MEDIUMTEXT NOT NULL,
            tags JSON NULL,
            like_count INT UNSIGNED DEFAULT 0,
            comment_count INT UNSIGNED DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            CONSTRAINT fk_post_user
                FOREIGN KEY (user_id) REFERENCES users(id)
                ON DELETE CASCADE,

            INDEX idx_post_country_created (country_slug, created_at),
            INDEX idx_post_user_created (user_id, created_at)
        ) ENGINE=InnoDB",

        // COMMUNITY COMMENTS
        "CREATE TABLE IF NOT EXISTS community_comments (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            post_id BIGINT UNSIGNED NOT NULL,
            user_id BIGINT UNSIGNED NOT NULL,
            body TEXT NOT NULL,
            parent_comment_id BIGINT UNSIGNED NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            CONSTRAINT fk_comment_post
                FOREIGN KEY (post_id) REFERENCES community_posts(id)
                ON DELETE CASCADE,

            CONSTRAINT fk_comment_user
                FOREIGN KEY (user_id) REFERENCES users(id)
                ON DELETE CASCADE,

            CONSTRAINT fk_comment_parent
                FOREIGN KEY (parent_comment_id) REFERENCES community_comments(id)
                ON DELETE CASCADE,

            INDEX idx_comment_post_created (post_id, created_at)
        ) ENGINE=InnoDB",

        // POST LIKES
        "CREATE TABLE IF NOT EXISTS forum_likes (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NOT NULL,
            post_id BIGINT UNSIGNED NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

            CONSTRAINT fk_like_user
                FOREIGN KEY (user_id) REFERENCES users(id)
                ON DELETE CASCADE,

            CONSTRAINT fk_like_post
                FOREIGN KEY (post_id) REFERENCES community_posts(id)
                ON DELETE CASCADE,

            UNIQUE KEY uk_user_post_like (user_id, post_id)
        ) ENGINE=InnoDB",

        // POST IMAGES
        "CREATE TABLE IF NOT EXISTS post_images (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            post_id BIGINT UNSIGNED NOT NULL,
            image_url VARCHAR(500) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

            CONSTRAINT fk_post_image
                FOREIGN KEY (post_id) REFERENCES community_posts(id)
                ON DELETE CASCADE,

            INDEX idx_post_images_post_id (post_id)
        ) ENGINE=InnoDB",

        // SAVED ITEMS
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

    $dbConn->close();
}
