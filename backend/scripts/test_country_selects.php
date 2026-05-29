<?php

$config = require __DIR__ . '/../config/database.php';

$conn = new mysqli(
    $config['host'],
    $config['username'],
    $config['password'],
    $config['dbname'],
    (int) $config['port']
);

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error . PHP_EOL);
}

$conn->set_charset($config['charset'] ?? 'utf8mb4');

$queries = [
    'Countries' => 'SELECT id, name, slug FROM countries ORDER BY id',
    'Destinations (first 10)' => 'SELECT id, country_id, name, slug FROM destinations ORDER BY id LIMIT 10',
    'Attractions (first 10)' => 'SELECT id, destination_id, name, category FROM attractions ORDER BY id LIMIT 10',
];

foreach ($queries as $label => $sql) {
    echo PHP_EOL . "=== $label ===" . PHP_EOL;
    $result = $conn->query($sql);
    if (!$result) {
        echo 'Query failed: ' . $conn->error . PHP_EOL;
        continue;
    }

    while ($row = $result->fetch_assoc()) {
        echo json_encode($row, JSON_UNESCAPED_SLASHES) . PHP_EOL;
    }
}

$conn->close();
echo PHP_EOL . 'SELECT tests finished.' . PHP_EOL;

