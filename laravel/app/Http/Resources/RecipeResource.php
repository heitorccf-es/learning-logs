<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecipeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $meal = $this->resource;

        return [
            'id' => $meal['idMeal'],
            'title' => trim($meal['strMeal']),
            'image' => $meal['strMealThumb'],
            'ingredients' => $this->extractIngredients($meal),
        ];
    }

    private function extractIngredients(array $meal): array
    {
        $ingredients = [];

        for ($i = 1; $i <= 20; $i++) {
            $name = $meal["strIngredient{$i}"] ?? null;
            $measure = $meal["strMeasure{$i}"] ?? null;

            // Pula se o nome for vazio ou nulo
            if (empty($name) || trim($name) === '') {
                continue;
            }

            // Limpeza: remove espaços extras e padroniza capitalização
            $cleanName = ucwords(strtolower(trim($name)));
            $cleanMeasure = trim($measure);

            // Se tiver medida, concatena. Se não, usa só o nome.
            if (!empty($cleanMeasure)) {
                $ingredients[] = "{$cleanMeasure} {$cleanName}";
            } else {
                $ingredients[] = $cleanName;
            }
        }

        // Remove duplicatas (ex: "Salt" aparecendo 2x) e reindexa o array
        return array_values(array_unique($ingredients));
    }
}
