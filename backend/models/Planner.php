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
        string $summary = ''
    ): int
    {
        $this->pdo->beginTransaction();
        try {
            $title = trim((string) ($tripMeta['country'] ?? 'Nordic Trip')) . ' Trip Plan';
            $countrySlug = $this->slugify((string) ($tripMeta['country'] ?? ''));
            $style = $this->mapTravelStyle((string) ($tripMeta['style'] ?? 'balanced'));
            $days = max(1, min(30, (int) ($tripMeta['duration'] ?? 1)));
            $budgetMin = isset($budgetEstimate['total']) ? (float) $budgetEstimate['total'] * 0.9 : null;
            $budgetMax = isset($budgetEstimate['total']) ? (float) $budgetEstimate['total'] * 1.1 : null;
            $notes = json_encode([
                'tripMeta' => $tripMeta,
                'budgetEstimate' => $budgetEstimate,
                'packingList' => $packingList,
                'weatherForecast' => $weatherForecast,
                'auroraForecast' => $auroraForecast,
                'summary' => $summary,
            ]);

            $insertPlan = $this->pdo->prepare(
                'INSERT INTO planner_plans (user_id, country_slug, title, travel_style, total_days, estimated_budget_min, estimated_budget_max, notes)
                 VALUES (:user_id, :country_slug, :title, :travel_style, :total_days, :budget_min, :budget_max, :notes)'
            );
            $insertPlan->execute([
                'user_id' => $userId,
                'country_slug' => $countrySlug ?: null,
                'title' => $title,
                'travel_style' => $style,
                'total_days' => $days,
                'budget_min' => $budgetMin,
                'budget_max' => $budgetMax,
                'notes' => $notes,
            ]);

            $planId = (int) $this->pdo->lastInsertId();

            $insertDay = $this->pdo->prepare(
                'INSERT INTO planner_days (planner_plan_id, day_number, day_title, notes)
                 VALUES (:plan_id, :day_number, :day_title, :notes)'
            );

            $insertItem = $this->pdo->prepare(
                "INSERT INTO planner_items (planner_day_id, item_type, title, sort_order)
                 VALUES (:planner_day_id, 'custom', :title, :sort_order)"
            );

            foreach ($timelineDays as $dayIndex => $day) {
                $dayNumber = max(1, (int) ($day['day'] ?? ($dayIndex + 1)));
                $items = is_array($day['items'] ?? null) ? $day['items'] : [];
                $insertDay->execute([
                    'plan_id' => $planId,
                    'day_number' => $dayNumber,
                    'day_title' => 'Day ' . $dayNumber,
                    'notes' => null,
                ]);

                $plannerDayId = (int) $this->pdo->lastInsertId();
                foreach ($items as $idx => $titleItem) {
                    $cleanTitle = trim((string) $titleItem);
                    if ($cleanTitle === '') continue;
                    $insertItem->execute([
                        'planner_day_id' => $plannerDayId,
                        'title' => $cleanTitle,
                        'sort_order' => $idx + 1,
                    ]);
                }
            }

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
        $planStmt = $this->pdo->prepare(
            'SELECT * FROM planner_plans WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 1'
        );
        $planStmt->execute(['user_id' => $userId]);
        $plan = $planStmt->fetch();

        if (!$plan) {
            return null;
        }

        $daysStmt = $this->pdo->prepare(
            'SELECT * FROM planner_days WHERE planner_plan_id = :plan_id ORDER BY day_number ASC'
        );
        $daysStmt->execute(['plan_id' => $plan['id']]);
        $days = $daysStmt->fetchAll();

        $itemsStmt = $this->pdo->prepare(
            'SELECT planner_day_id, title, sort_order FROM planner_items
             WHERE planner_day_id IN (SELECT id FROM planner_days WHERE planner_plan_id = :plan_id)
             ORDER BY planner_day_id ASC, sort_order ASC'
        );
        $itemsStmt->execute(['plan_id' => $plan['id']]);
        $itemsRows = $itemsStmt->fetchAll();

        $itemsByDay = [];
        foreach ($itemsRows as $row) {
            $dayId = (int) $row['planner_day_id'];
            if (!isset($itemsByDay[$dayId])) $itemsByDay[$dayId] = [];
            $itemsByDay[$dayId][] = $row['title'];
        }

        $timelineDays = array_map(function ($day) use ($itemsByDay) {
            $dayId = (int) $day['id'];
            return [
                'day' => (int) $day['day_number'],
                'items' => $itemsByDay[$dayId] ?? [],
            ];
        }, $days);

        $decodedNotes = json_decode((string) ($plan['notes'] ?? '{}'), true);
        $tripMeta = $decodedNotes['tripMeta'] ?? [
            'country' => ucfirst(str_replace('-', ' ', (string) ($plan['country_slug'] ?? 'Norway'))),
            'countryRoute' => [ucfirst(str_replace('-', ' ', (string) ($plan['country_slug'] ?? 'Norway')))],
            'startDate' => null,
            'endDate' => null,
            'pax' => 1,
            'duration' => (int) ($plan['total_days'] ?? 1),
            'style' => (string) ($plan['travel_style'] ?? 'Adventure'),
            'budget' => 'Medium',
            'accommodation' => 'Hotel',
            'transport' => 'Train',
            'season' => 'Winter',
            'tripType' => 'Couple',
            'activityLevel' => 'Moderate',
            'interests' => [],
        ];

        return [
            'plan_id' => (int) $plan['id'],
            'tripMeta' => $tripMeta,
            'timelineDays' => $timelineDays,
            'budgetEstimate' => $decodedNotes['budgetEstimate'] ?? null,
            'packingList' => $decodedNotes['packingList'] ?? [],
            'weatherForecast' => $decodedNotes['weatherForecast'] ?? [],
            'auroraForecast' => $decodedNotes['auroraForecast'] ?? null,
            'summary' => $decodedNotes['summary'] ?? '',
            'savedAt' => $plan['created_at'] ?? null,
        ];
    }

    private function slugify(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/', '-', $value);
        return trim((string) $value, '-');
    }

    private function mapTravelStyle(string $style): string
    {
        $styleLower = strtolower(trim($style));
        return match ($styleLower) {
            'low', 'budget' => 'budget',
            'high', 'luxury' => 'luxury',
            'relax', 'comfort' => 'comfort',
            default => 'balanced',
        };
    }
}
