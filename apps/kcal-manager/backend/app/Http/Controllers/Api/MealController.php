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
    /**
     * 食事一覧を表示
     */
    public function index(Request $request): JsonResponse
    {
        // 認証されたユーザーの食事のみを取得
        $query = $request->user()->meals();

        // 日付が指定されている場合はフィルタリング
        if ($request->has('date')) {
            $query->where('date', $request->date);
        }

        $meals = $query->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['data' => $meals]);
    }

    /**
     * 新しい食事を保存
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'portion' => 'nullable|string|max:255',
            'calories' => 'nullable|integer|min:0',
            'date' => 'required|date_format:Y-m-d',
            'is_estimated' => 'nullable|boolean'
        ]);

        // カロリーが指定されていない場合は、portionが指定されているか自動推定が必要
        if (!isset($validated['calories']) || $validated['calories'] === null) {
            $validated['calories'] = 0; // Will be set later if estimation succeeds
            $validated['is_estimated'] = true;
        }

        $validated['user_id'] = $request->user()->id;
        $meal = Meal::create($validated);

        return response()->json(['data' => $meal], 201);
    }

    public function show(Request $request, Meal $meal): JsonResponse
    {
        // Ensure the meal belongs to the authenticated user
        if ($meal->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return response()->json(['data' => $meal]);
    }

    public function update(Request $request, Meal $meal): JsonResponse
    {
        // Ensure the meal belongs to the authenticated user
        if ($meal->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'calories' => 'sometimes|integer|min:0',
            'date' => 'sometimes|date_format:Y-m-d'
        ]);

        $meal->update($validated);

        return response()->json(['data' => $meal]);
    }

    public function destroy(Request $request, Meal $meal): JsonResponse
    {
        // Ensure the meal belongs to the authenticated user
        if ($meal->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $meal->delete();

        return response()->json(null, 204);
    }

    /**
     * Get statistics for calorie intake over a date range
     */
    public function statistics(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date'
        ]);

        $startDate = $validated['start_date'];
        $endDate = $validated['end_date'];

        // Get daily calorie totals for the date range
        $dailyStats = $request->user()->meals()
            ->selectRaw('date, SUM(calories) as total_calories, COUNT(*) as meal_count')
            ->whereBetween('date', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Calculate overall statistics
        $totalCalories = $dailyStats->sum('total_calories');
        $totalMeals = $dailyStats->sum('meal_count');
        $averageCalories = $dailyStats->count() > 0 ? $totalCalories / $dailyStats->count() : 0;

        return response()->json([
            'data' => [
                'daily_stats' => $dailyStats,
                'summary' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'total_calories' => $totalCalories,
                    'total_meals' => $totalMeals,
                    'average_daily_calories' => round($averageCalories, 2),
                    'days_with_data' => $dailyStats->count()
                ]
            ]
        ]);
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
        $meals = $request->user()->meals()->where('date', $date)->get();

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