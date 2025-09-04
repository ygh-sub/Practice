<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Meal;
use App\Services\LLMService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;

class MealController extends Controller
{
    protected $llmService;

    public function __construct(LLMService $llmService)
    {
        $this->llmService = $llmService;
    }
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
            'portion' => 'nullable|string|max:255',
            'calories' => 'nullable|integer|min:0',
            'date' => 'required|date_format:Y-m-d',
            'is_estimated' => 'nullable|boolean'
        ]);

        // If calories not provided, must have either portion or auto-estimate
        if (!isset($validated['calories']) || $validated['calories'] === null) {
            $validated['calories'] = 0; // Will be set later if estimation succeeds
            $validated['is_estimated'] = true;
        }

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

    /**
     * Generate a comment for meals on a specific date using LLM
     */
    public function generateComment(Request $request): JsonResponse
    {
        $request->validate([
            'date' => 'required|date_format:Y-m-d'
        ]);

        $date = $request->input('date');
        $meals = Meal::where('date', $date)->get();

        if ($meals->isEmpty()) {
            return response()->json([
                'data' => [
                    'comment' => 'この日の食事記録がありません。'
                ]
            ]);
        }

        try {
            $comment = $this->llmService->generateMealComment($meals->toArray(), $date);

            return response()->json([
                'data' => [
                    'comment' => $comment,
                    'date' => $date,
                    'meal_count' => $meals->count(),
                    'total_calories' => $meals->sum('calories')
                ]
            ]);
        } catch (Exception $e) {
            // Return a fallback message if LLM service fails
            $totalCalories = $meals->sum('calories');
            $fallbackComment = $this->generateFallbackComment($totalCalories, $meals->count());

            return response()->json([
                'data' => [
                    'comment' => $fallbackComment,
                    'date' => $date,
                    'meal_count' => $meals->count(),
                    'total_calories' => $totalCalories,
                    'is_fallback' => true
                ]
            ]);
        }
    }

    /**
     * Estimate calories for a meal using LLM
     */
    public function estimateCalories(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'portion' => 'nullable|string|max:255'
        ]);

        $mealName = $request->input('name');
        $portion = $request->input('portion');

        try {
            $result = $this->llmService->estimateCalories($mealName, $portion);
            
            return response()->json([
                'data' => [
                    'calories' => $result['calories'],
                    'explanation' => $result['explanation'],
                    'meal_name' => $mealName,
                    'portion' => $portion,
                    'is_estimated' => true
                ]
            ]);
        } catch (Exception $e) {
            // Return a fallback estimation based on common meals
            $fallbackCalories = $this->getFallbackCalories($mealName);
            
            return response()->json([
                'data' => [
                    'calories' => $fallbackCalories,
                    'explanation' => '一般的な目安値です',
                    'meal_name' => $mealName,
                    'portion' => $portion,
                    'is_estimated' => true,
                    'is_fallback' => true
                ]
            ]);
        }
    }

    /**
     * Get fallback calories for common meals
     */
    private function getFallbackCalories(string $mealName): int
    {
        $commonMeals = [
            '朝食' => 400,
            '昼食' => 700,
            '夕食' => 800,
            'ご飯' => 250,
            'パン' => 200,
            'サラダ' => 150,
            'ラーメン' => 500,
            'カレー' => 700,
            '定食' => 800,
            '弁当' => 600,
            'おにぎり' => 180,
            'サンドイッチ' => 300,
            'パスタ' => 600,
            'うどん' => 350,
            'そば' => 300,
        ];

        // Check if meal name contains any common meal
        foreach ($commonMeals as $meal => $calories) {
            if (mb_strpos($mealName, $meal) !== false) {
                return $calories;
            }
        }

        // Default estimate
        return 500;
    }

    /**
     * Generate a fallback comment when LLM service is unavailable
     */
    private function generateFallbackComment(int $totalCalories, int $mealCount): string
    {
        if ($totalCalories < 1500) {
            return sprintf(
                '本日は%d回の食事で合計%dkcalを摂取されました。摂取カロリーが少なめですので、栄養バランスに気をつけながら適切な量を摂取することをお勧めします。',
                $mealCount,
                $totalCalories
            );
        } elseif ($totalCalories > 2500) {
            return sprintf(
                '本日は%d回の食事で合計%dkcalを摂取されました。摂取カロリーがやや多めですので、次回は量を調整することを検討してみてください。',
                $mealCount,
                $totalCalories
            );
        } else {
            return sprintf(
                '本日は%d回の食事で合計%dkcalを摂取されました。適切なカロリー摂取量です。引き続き栄養バランスに気をつけて健康的な食生活を心がけましょう。',
                $mealCount,
                $totalCalories
            );
        }
    }
}