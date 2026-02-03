<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Client\Pool;

class RecipeService
{
    protected string $baseUrl = 'https://www.themealdb.com/api/json/v1/1/';

    // Tempo de vida do cache: 86400 segundos = 24 horas
    protected int $cacheTime = 86400;

    public function findByIngredients(array $userIngredients): array
    {
        $mainIngredient = $userIngredients[0] ?? '';

        if (empty($mainIngredient)) {
            return [];
        }

        // 1. Buscamos a lista de IDs baseada no ingrediente principal (com Cache)
        // A chave do cache será "search_list_chicken"
        $mealsList = Cache::remember("search_list_{$mainIngredient}", $this->cacheTime, function () use ($mainIngredient) {
            $response = Http::get($this->baseUrl . 'filter.php', [
                'i' => $mainIngredient,
            ]);

            if ($response->failed() || is_null($response->json()['meals'])) {
                return [];
            }

            // Limitamos a 20 para ter uma amostra maior para filtragem
            return array_slice($response->json()['meals'], 0, 20);
        });

        if (empty($mealsList)) {
            return [];
        }

        // 2. Separar quais receitas já estão em cache e quais precisamos baixar
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

        // 3. Buscar apenas as que faltam usando HTTP Pool (paralelismo)
        if (!empty($idsToFetch)) {
            $responses = Http::pool(function (Pool $pool) use ($idsToFetch) {
                foreach ($idsToFetch as $id) {
                    $pool->as($id)->get($this->baseUrl . 'lookup.php', ['i' => $id]);
                }
            });

            foreach ($responses as $id => $response) {
                if ($response->ok() && isset($response->json()['meals'][0])) {
                    $mealData = $response->json()['meals'][0];

                    // Salvamos no cache para o futuro
                    Cache::put("meal_detail_{$id}", $mealData, $this->cacheTime);

                    $cachedRecipes[] = $mealData;
                }
            }
        }

        // 4. Filtragem final baseada em todos os ingredientes fornecidos pelo usuário
        return $this->filterRecipes($cachedRecipes, $userIngredients);
    }

    private function filterRecipes(array $recipes, array $userIngredients): array
    {
        $matchedRecipes = [];
        $userIngredientsLower = array_map(fn($i) => trim(strtolower($i)), $userIngredients);

        foreach ($recipes as $meal) {
            $recipeIngredients = $this->extractIngredients($meal);
            $recipeIngredientsLower = array_map(fn($i) => trim(strtolower($i)), $recipeIngredients);

            // Verifica interseção: A receita contém os ingredientes solicitados pelo usuário?
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
        return $ingredients;
    }
}
