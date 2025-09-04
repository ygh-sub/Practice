<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MealController;

Route::apiResource('meals', MealController::class);
Route::post('meals/comment', [MealController::class, 'generateComment']);