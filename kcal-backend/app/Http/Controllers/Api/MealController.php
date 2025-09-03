<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Meal;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MealController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Meal::query();

        if ($request->has('date')) {
            $query->where('date', $request->date);
        }

        $meals = $query->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['data' => $meals]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'calories' => 'required|integer|min:0',
            'date' => 'required|date_format:Y-m-d'
        ]);

        $meal = Meal::create($validated);

        return response()->json(['data' => $meal], 201);
    }

    public function show(Meal $meal): JsonResponse
    {
        return response()->json(['data' => $meal]);
    }

    public function update(Request $request, Meal $meal): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'calories' => 'sometimes|integer|min:0',
            'date' => 'sometimes|date_format:Y-m-d'
        ]);

        $meal->update($validated);

        return response()->json(['data' => $meal]);
    }

    public function destroy(Meal $meal): JsonResponse
    {
        $meal->delete();

        return response()->json(null, 204);
    }
}