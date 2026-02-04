<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Client\Pool;

class RecipeService
{
    protected string $baseUrl = 'https://www.themealdb.com/api/json/v1/1/';
    protected int $cacheTime = 86400; // 24 horas

    public function findByIngredients(array $userIngredients): array
    {
        $mainIngredient = $userIngredients[0] ?? '';

        if (empty($mainIngredient)) {
            return [];
        }

        $mealsList = Cache::remember("search_list_{$mainIngredient}", $this->cacheTime, function () use ($mainIngredient) {
            $response = Http::get($this->baseUrl . 'filter.php', [
                'i' => $mainIngredient,
            ]);

            if ($response->failed() || is_null($response->json()['meals'])) {
                return [];
            }

            return array_slice($response->json()['meals'], 0, 20);
        });

        if (empty($mealsList)) {
            return [];
        }

        $cachedRecipes = [];
        $idsToFetch = [];

        foreach ($mealsList as $meal) {
            $cacheKey = "meal_detail_{$meal['idMeal']}";

            if (Cache::has($cacheKey)) {
                $cachedRecipes[] = Cache::get($cacheKey);
            } else {
                $idsToFetch[] = $meal['idMeal'];
            }
        }

        if (!empty($idsToFetch)) {
            $responses = Http::pool(function (Pool $pool) use ($idsToFetch) {
                foreach ($idsToFetch as $id) {
                    $pool->as($id)->get($this->baseUrl . 'lookup.php', ['i' => $id]);
                }
            });

            foreach ($responses as $id => $response) {
                if ($response->ok() && isset($response->json()['meals'][0])) {
                    $mealData = $response->json()['meals'][0];
                    Cache::put("meal_detail_{$id}", $mealData, $this->cacheTime);
                    $cachedRecipes[] = $mealData;
                }
            }
        }

        return $this->filterRecipes($cachedRecipes, $userIngredients);
    }

    public function findById(string $id): ?array
    {
        return Cache::remember("recipe_{$id}", $this->cacheTime, function () use ($id) {
            $response = Http::get($this->baseUrl . 'lookup.php', ['i' => $id]);

            if ($response->failed() || empty($response->json()['meals'])) {
                return null;
            }

            $meal = $response->json()['meals'][0];

            return [
                'id' => $meal['idMeal'],
                'title' => trim($meal['strMeal']),
                'image' => $meal['strMealThumb'],
                'raw_ingredients' => $this->extractIngredients($meal)
            ];
        });
    }

    private function filterRecipes(array $recipes, array $userIngredients): array
    {
        $matchedRecipes = [];
        $userIngredientsLower = array_map(fn($i) => trim(strtolower($i)), $userIngredients);

        foreach ($recipes as $meal) {
            $recipeIngredients = $this->extractIngredients($meal);
            $recipeIngredientsLower = array_map(fn($i) => trim(strtolower($i)), $recipeIngredients);

            $missing = array_diff($userIngredientsLower, $recipeIngredientsLower);

            if (empty($missing)) {
                $matchedRecipes[] = $meal;
            }
        }

        return $matchedRecipes;
    }

    private function extractIngredients(array $meal): array
    {
        $ingredients = [];
        for ($i = 1; $i <= 20; $i++) {
            $ingredient = $meal["strIngredient{$i}"] ?? null;
            if (!empty($ingredient) && trim($ingredient) !== '') {
                $ingredients[] = trim($ingredient);
            }
        }
        return array_values(array_unique($ingredients));
    }
}
