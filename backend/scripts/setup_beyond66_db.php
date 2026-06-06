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
        CONSTRAINT fk_trip_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_trips_user_updated (user_id, updated_at),
        INDEX idx_trips_visibility_updated (visibility, updated_at)
    ) ENGINE=InnoDB",

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
        CONSTRAINT fk_post_trip FOREIGN KEY (trip_id) REFERENCES trips(id) ON DELETE CASCADE,
        CONSTRAINT fk_post_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY uk_community_trip (trip_id),
        INDEX idx_post_status_created (status, created_at),
        INDEX idx_post_user_created (user_id, created_at)
    ) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS post_portfolios (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        post_id BIGINT UNSIGNED NOT NULL,
        user_id BIGINT UNSIGNED NOT NULL,
        title VARCHAR(255) NULL,
        content TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_portfolio_post FOREIGN KEY (post_id) REFERENCES community_posts(id) ON DELETE CASCADE,
        CONSTRAINT fk_portfolio_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_portfolio_post_created (post_id, created_at)
    ) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS portfolio_images (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        portfolio_id BIGINT UNSIGNED NOT NULL,
        image_url VARCHAR(500) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_portfolio_image FOREIGN KEY (portfolio_id) REFERENCES post_portfolios(id) ON DELETE CASCADE,
        INDEX idx_portfolio_images_portfolio_id (portfolio_id)
    ) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS post_likes (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        post_id BIGINT UNSIGNED NOT NULL,
        user_id BIGINT UNSIGNED NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_like_post FOREIGN KEY (post_id) REFERENCES community_posts(id) ON DELETE CASCADE,
        CONSTRAINT fk_like_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY unique_like (post_id, user_id)
    ) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS post_comments (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        post_id BIGINT UNSIGNED NOT NULL,
        user_id BIGINT UNSIGNED NOT NULL,
        comment TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_post_comment_post FOREIGN KEY (post_id) REFERENCES community_posts(id) ON DELETE CASCADE,
        CONSTRAINT fk_post_comment_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_post_comment_created (post_id, created_at)
    ) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS saved_posts (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        post_id BIGINT UNSIGNED NOT NULL,
        user_id BIGINT UNSIGNED NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_saved_post_post FOREIGN KEY (post_id) REFERENCES community_posts(id) ON DELETE CASCADE,
        CONSTRAINT fk_saved_post_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY unique_save (post_id, user_id)
    ) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS saved_items (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT UNSIGNED NOT NULL,
        item_type ENUM('planner','community_post','country','destination','attraction') NOT NULL,
        item_id BIGINT UNSIGNED NULL,
        item_slug VARCHAR(180) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_saved_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY uk_saved_unique (user_id, item_type, item_id, item_slug),
        INDEX idx_saved_user_created (user_id, created_at),
        INDEX idx_saved_item_slug (item_slug)
    ) ENGINE=InnoDB",

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
