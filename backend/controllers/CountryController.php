<?php

require_once __DIR__ . '/../models/Country.php';
require_once __DIR__ . '/../utils/Response.php';

class CountryController
{
    private Country $countryModel;

    public function __construct()
    {
        $this->countryModel = new Country();
    }

    public function index(): void
    {
        try {
            $countries = $this->countryModel->getAll();
            Response::json([
                'success' => true,
                'data' => $countries,
            ]);
        } catch (Throwable $exception) {
            Response::json([
                'success' => false,
                'message' => 'Failed to fetch countries',
                'error' => $exception->getMessage(),
            ], 500);
        }
    }

    public function show(string $slug): void
    {
        try {
            $countryData = $this->countryModel->getBySlug($slug);
            if (!$countryData) {
                Response::json([
                    'success' => false,
                    'message' => 'Country not found',
                ], 404);
                return;
            }

            Response::json([
                'success' => true,
                'data' => $countryData,
            ]);
        } catch (Throwable $exception) {
            Response::json([
                'success' => false,
                'message' => 'Failed to fetch country details',
                'error' => $exception->getMessage(),
            ], 500);
        }
    }
}
