<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class LLMService
{
    private string $provider;
    private string $apiKey;
    private string $apiUrl;
    private string $model;
    private float $temperature;
    private int $maxTokens;

    public function __construct()
    {
        $this->provider = config('llm.provider');
        $this->apiKey = config('llm.api_key');
        $this->apiUrl = config('llm.api_url');
        $this->model = config('llm.model');
        $this->temperature = config('llm.temperature');
        $this->maxTokens = config('llm.max_tokens');
    }

    /**
     * Estimate calories for a meal
     * 
     * @param string $mealName The name of the meal
     * @param string|null $portion The portion/quantity (optional)
     * @return array The estimated calories and explanation
     * @throws Exception
     */
    public function estimateCalories(string $mealName, ?string $portion = null): array
    {
        if (empty($this->apiKey)) {
            throw new Exception('LLM API key is not configured');
        }

        $prompt = $this->buildCaloriesPrompt($mealName, $portion);

        try {
            $response = $this->callLLMApi($prompt);
            $content = $this->parseResponse($response);
            
            // Parse the response to extract calories
            return $this->parseCaloriesResponse($content);
        } catch (Exception $e) {
            Log::error('LLM API Error for calorie estimation: ' . $e->getMessage());
            throw new Exception('Failed to estimate calories: ' . $e->getMessage());
        }
    }

    /**
     * Build the prompt for calorie estimation
     */
    private function buildCaloriesPrompt(string $mealName, ?string $portion): string
    {
        $portionText = $portion ? "分量: {$portion}" : "通常の一人前";
        
        return sprintf(
            "以下の食事のカロリーを推定してください。\n\n" .
            "食事名: %s\n" .
            "%s\n\n" .
            "回答は以下のJSON形式で返してください（JSONのみ、説明文は不要）:\n" .
            '{"calories": カロリー数値, "explanation": "簡潔な説明（50文字以内）"}' . "\n\n" .
            "例: {\"calories\": 500, \"explanation\": \"白米茶碗1杯と焼き鮭、味噌汁の一般的な朝食\"}",
            $mealName,
            $portionText
        );
    }

    /**
     * Parse the calories estimation response
     */
    private function parseCaloriesResponse(string $content): array
    {
        // Try to extract JSON from the response
        $pattern = '/\{[^}]*"calories"\s*:\s*(\d+)[^}]*\}/';
        if (preg_match($pattern, $content, $matches)) {
            $jsonStr = $matches[0];
            $data = json_decode($jsonStr, true);
            
            if ($data && isset($data['calories'])) {
                return [
                    'calories' => (int) $data['calories'],
                    'explanation' => $data['explanation'] ?? '推定値です'
                ];
            }
        }

        // Fallback: try to find a number that could be calories
        if (preg_match('/(\d{2,4})/', $content, $matches)) {
            return [
                'calories' => (int) $matches[1],
                'explanation' => '推定値です'
            ];
        }

        // If we can't parse, return a default
        return [
            'calories' => 0,
            'explanation' => 'カロリーを推定できませんでした'
        ];
    }

    /**
     * Generate a comment for daily meals
     * 
     * @param array $meals Array of meal data for a specific date
     * @param string $date The date for the meals
     * @return string The generated comment
     * @throws Exception
     */
    public function generateMealComment(array $meals, string $date): string
    {
        if (empty($this->apiKey)) {
            throw new Exception('LLM API key is not configured');
        }

        $prompt = $this->buildPrompt($meals, $date);

        try {
            $response = $this->callLLMApi($prompt);
            return $this->parseResponse($response);
        } catch (Exception $e) {
            Log::error('LLM API Error: ' . $e->getMessage());
            throw new Exception('Failed to generate comment: ' . $e->getMessage());
        }
    }

    /**
     * Build the prompt for the LLM
     */
    private function buildPrompt(array $meals, string $date): string
    {
        $totalCalories = array_sum(array_column($meals, 'calories'));
        $mealList = array_map(function ($meal) {
            return sprintf('%s: %dkcal', $meal['name'], $meal['calories']);
        }, $meals);

        $promptText = sprintf(
            "以下は%sの食事記録です。\n\n" .
            "食事内容:\n%s\n\n" .
            "合計カロリー: %dkcal\n\n" .
            "この日の食事について、栄養バランスや健康の観点から簡潔なアドバイスを日本語で提供してください。" .
            "ポジティブな点と改善できる点の両方を含めてください。150文字以内でお願いします。",
            $date,
            implode("\n", $mealList),
            $totalCalories
        );

        return $promptText;
    }

    /**
     * Call the LLM API
     */
    private function callLLMApi(string $prompt): array
    {
        $payload = $this->buildPayload($prompt);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(30)->post($this->apiUrl, $payload);

        if (!$response->successful()) {
            throw new Exception('LLM API request failed: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Build the API payload based on provider
     */
    private function buildPayload(string $prompt): array
    {
        switch ($this->provider) {
            case 'openai':
                return [
                    'model' => $this->model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'あなたは栄養と健康に関する専門家です。ユーザーの食事記録を見て、簡潔で実用的なアドバイスを提供してください。'
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ],
                    'temperature' => $this->temperature,
                    'max_tokens' => $this->maxTokens,
                ];
            
            case 'anthropic':
                return [
                    'model' => $this->model,
                    'max_tokens' => $this->maxTokens,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ],
                    'temperature' => $this->temperature,
                ];
            
            default:
                // Default OpenAI-compatible format
                return [
                    'model' => $this->model,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ],
                    'temperature' => $this->temperature,
                    'max_tokens' => $this->maxTokens,
                ];
        }
    }

    /**
     * Parse the response from the LLM API
     */
    private function parseResponse(array $response): string
    {
        switch ($this->provider) {
            case 'openai':
                return $response['choices'][0]['message']['content'] ?? 'コメントを生成できませんでした。';
            
            case 'anthropic':
                return $response['content'][0]['text'] ?? 'コメントを生成できませんでした。';
            
            default:
                // Try OpenAI format as default
                return $response['choices'][0]['message']['content'] ?? 'コメントを生成できませんでした。';
        }
    }
}