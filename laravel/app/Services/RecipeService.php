<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class RecipeService
{
    protected string $baseUrl = 'https://www.themealdb.com/api/json/v1/1/';

    public function findByIngredients(array $ingredients): array
    {
        $mainIngredient = $ingredients[0] ?? '';

        if (empty($mainIngredient)) {
            return [];
        }

        $response = Http::get($this->baseUrl . 'filter.php', [
            'i' => $mainIngredient,
        ]);

        if ($response->failed()) {
            return [];
        }

        return $response->json()['meals'] ?? [];
    }
}
