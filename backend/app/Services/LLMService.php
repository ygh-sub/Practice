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
    private bool $useMock;

    public function __construct()
    {
        $this->useMock = config('llm.use_mock', false);
        $this->provider = config('llm.provider');
        $this->apiKey = config('llm.api_key');
        $this->apiUrl = config('llm.api_url');
        $this->model = config('llm.model');
        $this->temperature = config('llm.temperature');
        $this->maxTokens = config('llm.max_tokens');
    }

    /**
     * 食事のカロリーを推定
     * 
     * @param string $mealName 食事名
     * @param string|null $portion 分量（オプション）
     * @return array 推定カロリーと説明
     * @throws Exception
     */
    public function estimateCalories(string $mealName, ?string $portion = null): array
    {
        if ($this->useMock) {
            return $this->getMockCaloriesEstimate($mealName, $portion);
        }

        if (empty($this->apiKey)) {
            throw new Exception('LLM API key is not configured');
        }

        $prompt = $this->buildCaloriesPrompt($mealName, $portion);

        try {
            $response = $this->callLLMApi($prompt);
            $content = $this->parseResponse($response);
            
            // レスポンスからカロリーを抽出
            return $this->parseCaloriesResponse($content);
        } catch (Exception $e) {
            Log::error('LLM API Error for calorie estimation: ' . $e->getMessage());
            throw new Exception('Failed to estimate calories: ' . $e->getMessage());
        }
    }

    /**
     * カロリー推定用のプロンプトを構築
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
     * カロリー推定レスポンスを解析
     */
    private function parseCaloriesResponse(string $content): array
    {
        // レスポンスからJSONを抽出
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

        // フォールバック：カロリーらしき数値を探す
        if (preg_match('/(\d{2,4})/', $content, $matches)) {
            return [
                'calories' => (int) $matches[1],
                'explanation' => '推定値です'
            ];
        }

        // 解析できない場合はデフォルト値を返す
        return [
            'calories' => 0,
            'explanation' => 'カロリーを推定できませんでした'
        ];
    }

    /**
     * 日々の食事に対するコメントを生成
     * 
     * @param array $meals 特定の日付の食事データ配列
     * @param string $date 食事の日付
     * @return string 生成されたコメント
     * @throws Exception
     */
    public function generateMealComment(array $meals, string $date): string
    {
        if ($this->useMock) {
            return $this->getMockMealComment($meals, $date);
        }

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
     * LLM用のプロンプトを構築
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
     * LLM APIを呼び出す
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
     * プロバイダーに基づいてAPIペイロードを構築
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
     * LLM APIからのレスポンスを解析
     */
    private function parseResponse(array $response): string
    {
        switch ($this->provider) {
            case 'openai':
                return $response['choices'][0]['message']['content'] ?? 'コメントを生成できませんでした。';
            
            case 'anthropic':
                return $response['content'][0]['text'] ?? 'コメントを生成できませんでした。';
            
            default:
                // デフォルトでOpenAI形式を試す
                return $response['choices'][0]['message']['content'] ?? 'コメントを生成できませんでした。';
        }
    }

    /**
     * テスト用のモックカロリー推定を取得
     */
    private function getMockCaloriesEstimate(string $mealName, ?string $portion = null): array
    {
        // 一般的な食品に基づいたシンプルなモックカロリー計算
        $mockCalories = [
            'ラーメン' => 500,
            'カレーライス' => 600,
            '寿司' => 400,
            'サラダ' => 150,
            '唐揚げ' => 450,
            'パスタ' => 550,
            'うどん' => 350,
            'そば' => 300,
            '牛丼' => 700,
            '焼肉' => 800,
            'おにぎり' => 180,
            'サンドイッチ' => 300,
            'ピザ' => 650,
            'ハンバーガー' => 550,
        ];

        // 食事名に既知の食品が含まれているかチェック
        $estimatedCalories = 300; // デフォルト
        foreach ($mockCalories as $food => $calories) {
            if (mb_stripos($mealName, $food) !== false) {
                $estimatedCalories = $calories;
                break;
            }
        }

        // 分量が指定されている場合は調整
        if ($portion) {
            if (mb_stripos($portion, '大盛') !== false) {
                $estimatedCalories = (int)($estimatedCalories * 1.3);
            } elseif (mb_stripos($portion, '小盛') !== false || mb_stripos($portion, '少なめ') !== false) {
                $estimatedCalories = (int)($estimatedCalories * 0.7);
            }
        }

        return [
            'calories' => $estimatedCalories,
            'explanation' => 'モックデータによる推定値です'
        ];
    }

    /**
     * テスト用のモック食事コメントを取得
     */
    private function getMockMealComment(array $meals, string $date): string
    {
        $totalCalories = array_sum(array_column($meals, 'calories'));
        
        $comments = [
            'low' => "本日の総カロリーは{$totalCalories}kcalと控えめです。バランスの良い食事を心がけ、必要に応じて間食で栄養補給をしましょう。",
            'normal' => "本日の総カロリーは{$totalCalories}kcalで適切な範囲です。野菜を増やすとさらに栄養バランスが良くなります。",
            'high' => "本日の総カロリーは{$totalCalories}kcalとやや多めです。明日は軽めの食事を意識してバランスを取りましょう。"
        ];

        if ($totalCalories < 1500) {
            return $comments['low'];
        } elseif ($totalCalories <= 2500) {
            return $comments['normal'];
        } else {
            return $comments['high'];
        }
    }
}