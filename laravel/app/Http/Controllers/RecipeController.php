<?php

namespace App\Http\Controllers;

use App\Services\RecipeService;
use App\Http\Resources\RecipeResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

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
            'page' => 'integer|min:1' // Aceitamos o parâmetro de página
        ]);

        // 1. Busca TODAS as receitas que dão match (Cacheado no Service)
        $allRecipes = $this->recipeService->findByIngredients($validated['ingredients']);

        // 2. Configuração da Paginação
        $page = $request->input('page', 1);
        $perPage = 6; // Quantas receitas por página (6 fica bonito no grid 2x3 ou 3x2)
        $offset = ($page - 1) * $perPage;

        // 3. Fatiar o array (Pega apenas as receitas da página atual)
        $items = array_slice($allRecipes, $offset, $perPage);
        $total = count($allRecipes);

        // 4. Retornar estrutura com metadados
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
}
