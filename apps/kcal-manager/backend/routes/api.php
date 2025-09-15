<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MealController;
use App\Http\Controllers\Api\AuthController;

// Authentication routes (public)
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// Protected routes
// Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('user', [AuthController::class, 'user']);
    
    // Meal routes - specific routes first, then resource routes
    Route::post('meals/comment', [MealController::class, 'generateComment']);
    Route::post('meals/estimate-calories', [MealController::class, 'estimateCalories']);
    Route::get('meals/statistics', [MealController::class, 'statistics']);
    Route::apiResource('meals', MealController::class);
// });