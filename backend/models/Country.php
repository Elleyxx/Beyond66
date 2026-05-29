<?php

class Country
{
    private PDO $pdo;

    public function __construct()
    {
        $config = require __DIR__ . '/../config/database.php';
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $config['host'],
            (int) $config['port'],
            $config['dbname'],
            $config['charset'] ?? 'utf8mb4'
        );

        $this->pdo = new PDO($dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }

    public function getAll(): array
    {
        $stmt = $this->pdo->query('
            SELECT id, name, slug, intro, hero_image_url, best_time_summary
            FROM countries
            ORDER BY name ASC
        ');
        return $stmt->fetchAll();
    }

    public function getBySlug(string $slug): ?array
    {
        $countryStmt = $this->pdo->prepare('
            SELECT id, name, slug, intro, hero_image_url, best_time_summary
            FROM countries
            WHERE slug = :slug
            LIMIT 1
        ');
        $countryStmt->execute(['slug' => $slug]);
        $country = $countryStmt->fetch();

        if (!$country) {
            return null;
        }

        $highlightsStmt = $this->pdo->prepare('
            SELECT
                d.name,
                d.destination_type,
                d.summary
            FROM destinations d
            WHERE d.country_id = :country_id
            ORDER BY d.recommended_days DESC, d.id ASC
            LIMIT 3
        ');
        $highlightsStmt->execute(['country_id' => $country['id']]);
        $highlights = $highlightsStmt->fetchAll();

        $experiencesStmt = $this->pdo->prepare('
            SELECT
                a.name,
                a.description
            FROM attractions a
            INNER JOIN destinations d ON d.id = a.destination_id
            WHERE d.country_id = :country_id
            ORDER BY a.id ASC
            LIMIT 4
        ');
        $experiencesStmt->execute(['country_id' => $country['id']]);
        $experiences = $experiencesStmt->fetchAll();

        return [
            'country' => $country,
            'highlights' => $highlights,
            'experiences' => $experiences,
        ];
    }
}
