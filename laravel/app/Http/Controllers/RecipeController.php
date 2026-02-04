<?php

namespace App\Http\Controllers;

use App\Services\RecipeService;
use App\Http\Resources\RecipeResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class RecipeController extends Controller
{
    protected RecipeService $recipeService;

    public function __construct(RecipeService $recipeService)
    {
        $this->recipeService = $recipeService;
    }

    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ingredients' => 'required|array',
            'ingredients.*' => 'string|max:50',
            'page' => 'integer|min:1'
        ]);

        $allRecipes = $this->recipeService->findByIngredients($validated['ingredients']);

        $page = $request->input('page', 1);
        $perPage = 6;
        $offset = ($page - 1) * $perPage;

        $items = array_slice($allRecipes, $offset, $perPage);
        $total = count($allRecipes);

        return response()->json([
            'data' => RecipeResource::collection($items),
            'meta' => [
                'current_page' => (int)$page,
                'last_page' => (int)ceil($total / $perPage),
                'total' => $total,
                'per_page' => $perPage
            ]
        ]);
    }

    public function exportPdf(Request $request, string $id)
    {
        $validated = $request->validate([
            'user_ingredients' => 'array',
        ]);

        $recipe = $this->recipeService->findById($id);

        if (!$recipe) {
            return response()->json(['error' => 'Recipe not found'], 404);
        }

        $userIngredients = array_map('strtolower', $validated['user_ingredients'] ?? []);
        $processedIngredients = [];

        $rawIngredients = $recipe['raw_ingredients'] ?? [];

        foreach ($rawIngredients as $ingName) {
            $ingLower = strtolower($ingName);
            $has = false;

            foreach ($userIngredients as $userIng) {
                if (str_contains($ingLower, $userIng)) {
                    $has = true;
                    break;
                }
            }

            $processedIngredients[] = [
                'name' => $ingName,
                'has_ingredient' => $has
            ];
        }

        $recipe['ingredients'] = $processedIngredients;

        // Gera o PDF
        $pdf = Pdf::loadView('pdf.recipe', ['recipe' => $recipe]);

        // Limpeza de buffer
        if (ob_get_length()) {
            ob_end_clean();
        }

        // Download direto
        return $pdf->download("receita-{$id}.pdf");
    }
}
