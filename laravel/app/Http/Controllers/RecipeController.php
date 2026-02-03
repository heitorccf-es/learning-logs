<?php

namespace App\Http\Controllers;

use App\Services\RecipeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
        ]);

        $recipes = $this->recipeService->findByIngredients($validated['ingredients']);

        return response()->json([
            'data' => $recipes,
            'count' => count($recipes)
        ]);
    }
}
