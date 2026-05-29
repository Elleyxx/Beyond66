<?php

$server = '127.0.0.1';
$username = 'root';
$password = '';
$dbname = 'beyond66';

$conn = new mysqli($server, $username, $password);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error . PHP_EOL);
}

if (!$conn->query("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
    die('Create database failed: ' . $conn->error . PHP_EOL);
}

$conn->close();

$tableConn = new mysqli($server, $username, $password, $dbname);
if ($tableConn->connect_error) {
    die('Database connection failed: ' . $tableConn->connect_error . PHP_EOL);
}

$tableConn->set_charset('utf8mb4');

$tables = [
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

    "CREATE TABLE IF NOT EXISTS countries (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        slug VARCHAR(120) NOT NULL UNIQUE,
        intro TEXT NULL,
        hero_image_url VARCHAR(500) NULL,
        best_time_summary VARCHAR(255) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS destinations (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        country_id BIGINT UNSIGNED NOT NULL,
        name VARCHAR(150) NOT NULL,
        slug VARCHAR(180) NOT NULL,
        destination_type ENUM('city','region','nature','island','village','landmark') DEFAULT 'city',
        latitude DECIMAL(10,7) NULL,
        longitude DECIMAL(10,7) NULL,
        summary TEXT NULL,
        recommended_days TINYINT UNSIGNED NULL,
        avg_daily_budget_min DECIMAL(10,2) NULL,
        avg_daily_budget_max DECIMAL(10,2) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        CONSTRAINT fk_dest_country FOREIGN KEY (country_id) REFERENCES countries(id) ON DELETE CASCADE,
        UNIQUE KEY uk_destination_slug_country (country_id, slug),
        INDEX idx_dest_country_name (country_id, name)
    ) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS attractions (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        destination_id BIGINT UNSIGNED NOT NULL,
        name VARCHAR(180) NOT NULL,
        category ENUM('nature','culture','food','adventure','museum','scenic','activity') DEFAULT 'scenic',
        difficulty ENUM('easy','moderate','hard') DEFAULT 'easy',
        best_season ENUM('spring','summer','autumn','winter','all_year') DEFAULT 'all_year',
        description TEXT NULL,
        est_cost DECIMAL(10,2) NULL,
        recommended_month_start TINYINT UNSIGNED NULL,
        recommended_month_end TINYINT UNSIGNED NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        CONSTRAINT fk_attr_destination FOREIGN KEY (destination_id) REFERENCES destinations(id) ON DELETE CASCADE,
        INDEX idx_attr_destination (destination_id),
        INDEX idx_attr_category (category)
    ) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS planner_plans (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT UNSIGNED NOT NULL,
        country_id BIGINT UNSIGNED NULL,
        title VARCHAR(180) NOT NULL,
        travel_style ENUM('budget','balanced','comfort','luxury') DEFAULT 'balanced',
        total_days TINYINT UNSIGNED NOT NULL,
        estimated_budget_min DECIMAL(10,2) NULL,
        estimated_budget_max DECIMAL(10,2) NULL,
        notes TEXT NULL,
        is_public TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        CONSTRAINT fk_planner_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        CONSTRAINT fk_planner_country FOREIGN KEY (country_id) REFERENCES countries(id) ON DELETE SET NULL,
        INDEX idx_planner_user_created (user_id, created_at)
    ) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS planner_days (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        planner_plan_id BIGINT UNSIGNED NOT NULL,
        day_number TINYINT UNSIGNED NOT NULL,
        base_destination_id BIGINT UNSIGNED NULL,
        day_title VARCHAR(180) NULL,
        notes TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        CONSTRAINT fk_planday_plan FOREIGN KEY (planner_plan_id) REFERENCES planner_plans(id) ON DELETE CASCADE,
        CONSTRAINT fk_planday_destination FOREIGN KEY (base_destination_id) REFERENCES destinations(id) ON DELETE SET NULL,
        UNIQUE KEY uk_planner_day (planner_plan_id, day_number)
    ) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS planner_items (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        planner_day_id BIGINT UNSIGNED NOT NULL,
        attraction_id BIGINT UNSIGNED NULL,
        item_type ENUM('attraction','transport','meal','free_time','custom') DEFAULT 'custom',
        title VARCHAR(200) NOT NULL,
        start_time TIME NULL,
        end_time TIME NULL,
        est_cost DECIMAL(10,2) NULL,
        notes TEXT NULL,
        sort_order SMALLINT UNSIGNED DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        CONSTRAINT fk_planitem_day FOREIGN KEY (planner_day_id) REFERENCES planner_days(id) ON DELETE CASCADE,
        CONSTRAINT fk_planitem_attr FOREIGN KEY (attraction_id) REFERENCES attractions(id) ON DELETE SET NULL,
        INDEX idx_planitem_day_order (planner_day_id, sort_order)
    ) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS community_posts (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT UNSIGNED NOT NULL,
        country_id BIGINT UNSIGNED NULL,
        title VARCHAR(220) NOT NULL,
        body MEDIUMTEXT NOT NULL,
        tags JSON NULL,
        like_count INT UNSIGNED DEFAULT 0,
        comment_count INT UNSIGNED DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        CONSTRAINT fk_post_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        CONSTRAINT fk_post_country FOREIGN KEY (country_id) REFERENCES countries(id) ON DELETE SET NULL,
        INDEX idx_post_country_created (country_id, created_at),
        INDEX idx_post_user_created (user_id, created_at)
    ) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS forum_likes (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT UNSIGNED NOT NULL,
        post_id BIGINT UNSIGNED NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_like_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        CONSTRAINT fk_like_post FOREIGN KEY (post_id) REFERENCES community_posts(id) ON DELETE CASCADE,
        UNIQUE KEY uk_user_post_like (user_id, post_id)
    ) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS post_images (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        post_id BIGINT UNSIGNED NOT NULL,
        image_url VARCHAR(500) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_post_image FOREIGN KEY (post_id) REFERENCES community_posts(id) ON DELETE CASCADE,
        INDEX idx_post_images_post_id (post_id)
    ) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS community_comments (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        post_id BIGINT UNSIGNED NOT NULL,
        user_id BIGINT UNSIGNED NOT NULL,
        body TEXT NOT NULL,
        parent_comment_id BIGINT UNSIGNED NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        CONSTRAINT fk_comment_post FOREIGN KEY (post_id) REFERENCES community_posts(id) ON DELETE CASCADE,
        CONSTRAINT fk_comment_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        CONSTRAINT fk_comment_parent FOREIGN KEY (parent_comment_id) REFERENCES community_comments(id) ON DELETE CASCADE,
        INDEX idx_comment_post_created (post_id, created_at)
    ) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS saved_items (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT UNSIGNED NOT NULL,
        item_type ENUM('planner','community_post','destination') NOT NULL,
        item_id BIGINT UNSIGNED NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_saved_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY uk_saved_unique (user_id, item_type, item_id),
        INDEX idx_saved_user_created (user_id, created_at)
    ) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS user_preferences (
        user_id BIGINT UNSIGNED PRIMARY KEY,
        preferred_travel_style ENUM('budget','balanced','comfort','luxury') DEFAULT 'balanced',
        preferred_season ENUM('spring','summer','autumn','winter') NULL,
        budget_min DECIMAL(10,2) NULL,
        budget_max DECIMAL(10,2) NULL,
        interests JSON NULL,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        CONSTRAINT fk_pref_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB"
];

foreach ($tables as $index => $sql) {
    if ($tableConn->query($sql) === true) {
        echo 'Table ' . ($index + 1) . ' created/exists.' . PHP_EOL;
    } else {
        echo 'Error on table ' . ($index + 1) . ': ' . $tableConn->error . PHP_EOL;
    }
}

echo 'Beyond66 database setup complete.' . PHP_EOL;
$tableConn->close();
