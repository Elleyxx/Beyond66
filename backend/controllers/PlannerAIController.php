<?php

require_once __DIR__ . '/../utils/Response.php';

class PlannerAIController
{
    public function generatePlan()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::json(['success' => false, 'message' => 'Method Not Allowed'], 405);
        }

        $preferences = json_decode(file_get_contents('php://input'), true);

        if (!$preferences || empty($preferences['countryRoute']) || !is_array($preferences['countryRoute'])) {
            Response::json([
                'success' => false,
                'message' => 'Missing or invalid travel preferences payload.',
            ], 400);
        }

        $apiKey = $this->geminiApiKey();

        if (!$apiKey) {
            Response::json([
                'success' => false,
                'message' => 'GEMINI_API_KEY is not set on the PHP backend.',
            ], 500);
        }

        $countries = implode(', ', $preferences['countryRoute']);
        $startDate = $preferences['startDate'] ?? 'TBD';
        $endDate = $preferences['endDate'] ?? 'TBD';
        $duration = (int)($preferences['duration'] ?? 5);
        $budget = $preferences['budget'] ?? 'Medium';
        $season = $preferences['season'] ?? 'Winter';
        $pax = (int)($preferences['pax'] ?? 1);
        $style = $preferences['style'] ?? 'Adventure';
        $interests = isset($preferences['interests']) && is_array($preferences['interests'])
            ? implode(', ', $preferences['interests'])
            : 'Nature';
        $tripType = $preferences['tripType'] ?? 'Couple';
        $transport = $preferences['transport'] ?? 'Train';
        $accommodation = $preferences['accommodation'] ?? 'Hotel';
        $activityLevel = $preferences['activityLevel'] ?? 'Moderate';

        $prompt = "You are an expert AI travel planner for Beyond66.\n"
            . "Create a comprehensive, realistic Nordic travel plan based strictly on these constraints:\n"
            . "- Country Route: {$countries}\n"
            . "- Start Date: {$startDate}\n"
            . "- End Date: {$endDate}\n"
            . "- Duration: {$duration} days\n"
            . "- Budget Tier (Affordability Preference): {$budget}\n"
            . "- Season: {$season}\n"
            . "- Pax: {$pax}\n"
            . "- Style: {$style}\n"
            . "- Interests: {$interests}\n"
            . "- Trip Type: {$tripType}\n"
            . "- Transport: {$transport}\n"
            . "- Accommodation: {$accommodation}\n"
            . "- Activity Level: {$activityLevel}\n\n"
            . "Requirements:\n"
            . "1. Provide a daily timeline for exactly {$duration} days. For each day include day, country, destination, imageSearchQuery, and items.\n"
            . "2. Provide a realistic AI market budget estimate in USD based on country realities, duration, pax, season, accommodation, transport, travel style, activity level, and interests. Do not force it to match the selected budget tier. Use the selected budget tier only for affordability tips and comparison. Total must equal stay + food + transport + activities + emergency.\n"
            . "3. Include a packing list with unique integer IDs.\n"
            . "4. Include weather forecast or seasonal estimate data for the travel days.\n"
            . "5. Include realistic aurora metrics when northern latitude and season make aurora viewing possible.\n"
            . "6. Keep the summary concise and tailored to the user's route and preferences.";

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                    ],
                ],
            ],
            'generationConfig' => [
                'responseMimeType' => 'application/json',
                'temperature' => 0.3,
                'responseSchema' => $this->responseSchema(),
            ],
        ];

        $response = $this->callGemini($apiKey, $payload);

        if (!$response['success']) {
            Response::json([
                'success' => false,
                'message' => $response['message'],
            ], $response['status']);
        }

        Response::json([
            'success' => true,
            'data' => $response['data'],
        ]);
    }

    private function geminiApiKey()
    {
        $envValue = getenv('GEMINI_API_KEY');
        if ($envValue) {
            return $envValue;
        }

        $envPath = __DIR__ . '/../.env';
        if (!file_exists($envPath)) {
            return '';
        }

        foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            if (trim($key) === 'GEMINI_API_KEY') {
                return trim($value, " \t\n\r\0\x0B\"'");
            }
        }

        return '';
    }

    private function callGemini($apiKey, $payload)
    {
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key='
            . urlencode($apiKey);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        $rawResponse = curl_exec($ch);

        if (curl_errno($ch)) {
            $message = 'cURL transport failure: ' . curl_error($ch);
            curl_close($ch);
            return ['success' => false, 'message' => $message, 'status' => 502];
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $decoded = json_decode($rawResponse, true);

        if (isset($decoded['error'])) {
            return [
                'success' => false,
                'message' => 'Gemini engine error: ' . ($decoded['error']['message'] ?? 'Unknown error'),
                'status' => $httpCode >= 400 ? $httpCode : 400,
            ];
        }

        $planJson = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? null;
        if (!$planJson) {
            return ['success' => false, 'message' => 'Failed to get structured output from Gemini.', 'status' => 500];
        }

        $plan = json_decode($planJson, true);
        if (!$plan) {
            return ['success' => false, 'message' => 'Gemini returned invalid JSON.', 'status' => 500];
        }

        return ['success' => true, 'data' => $plan, 'status' => 200];
    }

    private function responseSchema()
    {
        return [
            'type' => 'OBJECT',
            'properties' => [
                'timelineDays' => [
                    'type' => 'ARRAY',
                    'items' => [
                        'type' => 'OBJECT',
                        'properties' => [
                            'day' => ['type' => 'INTEGER'],
                            'country' => ['type' => 'STRING'],
                            'destination' => ['type' => 'STRING'],
                            'imageSearchQuery' => ['type' => 'STRING'],
                            'items' => ['type' => 'ARRAY', 'items' => ['type' => 'STRING']],
                        ],
                        'required' => ['day', 'country', 'destination', 'imageSearchQuery', 'items'],
                    ],
                ],
                'budgetEstimate' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'stay' => ['type' => 'NUMBER'],
                        'food' => ['type' => 'NUMBER'],
                        'transport' => ['type' => 'NUMBER'],
                        'activities' => ['type' => 'NUMBER'],
                        'emergency' => ['type' => 'NUMBER'],
                        'total' => ['type' => 'NUMBER'],
                        'perPerson' => ['type' => 'NUMBER'],
                        'dailyAverage' => ['type' => 'NUMBER'],
                        'currency' => ['type' => 'STRING'],
                        'tips' => ['type' => 'ARRAY', 'items' => ['type' => 'STRING']],
                    ],
                    'required' => [
                        'stay',
                        'food',
                        'transport',
                        'activities',
                        'emergency',
                        'total',
                        'perPerson',
                        'dailyAverage',
                        'currency',
                        'tips',
                    ],
                ],
                'packingList' => [
                    'type' => 'ARRAY',
                    'items' => [
                        'type' => 'OBJECT',
                        'properties' => [
                            'id' => ['type' => 'INTEGER'],
                            'name' => ['type' => 'STRING'],
                            'checked' => ['type' => 'BOOLEAN'],
                        ],
                        'required' => ['id', 'name', 'checked'],
                    ],
                ],
                'weatherForecast' => [
                    'type' => 'ARRAY',
                    'items' => [
                        'type' => 'OBJECT',
                        'properties' => [
                            'date' => ['type' => 'STRING'],
                            'temp' => ['type' => 'NUMBER'],
                            'condition' => ['type' => 'STRING'],
                        ],
                        'required' => ['temp', 'condition'],
                    ],
                ],
                'auroraForecast' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'date' => ['type' => 'STRING'],
                        'kp' => ['type' => 'STRING'],
                        'chance' => ['type' => 'INTEGER'],
                        'window' => ['type' => 'STRING'],
                    ],
                    'required' => ['kp', 'chance', 'window'],
                ],
                'summary' => ['type' => 'STRING'],
            ],
            'required' => [
                'timelineDays',
                'budgetEstimate',
                'packingList',
                'weatherForecast',
                'auroraForecast',
                'summary',
            ],
        ];
    }
}
