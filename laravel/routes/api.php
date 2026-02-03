<?php

use App\Http\Controllers\RecipeController;
use Illuminate\Support\Facades\Route;

Route::post('/recipes/search', [RecipeController::class, 'search']);
