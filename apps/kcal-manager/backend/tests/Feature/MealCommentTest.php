<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Meal;
use App\Services\LLMService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

class MealCommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_generate_comment_for_meals(): void
    {
        // Create test meals
        Meal::create(['name' => '朝食', 'calories' => 400, 'date' => '2024-01-01']);
        Meal::create(['name' => '昼食', 'calories' => 600, 'date' => '2024-01-01']);
        Meal::create(['name' => '夕食', 'calories' => 800, 'date' => '2024-01-01']);

        // Mock LLMService to avoid actual API calls in tests
        $this->mock(LLMService::class, function ($mock) {
            $mock->shouldReceive('generateMealComment')
                ->once()
                ->andReturn('バランスの良い食事です。カロリー摂取量も適切です。');
        });

        $response = $this->postJson('/api/meals/comment', [
            'date' => '2024-01-01'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'comment',
                    'date',
                    'meal_count',
                    'total_calories'
                ]
            ])
            ->assertJson([
                'data' => [
                    'date' => '2024-01-01',
                    'meal_count' => 3,
                    'total_calories' => 1800
                ]
            ]);
    }

    public function test_returns_message_when_no_meals_for_date(): void
    {
        $response = $this->postJson('/api/meals/comment', [
            'date' => '2024-01-01'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'comment' => 'この日の食事記録がありません。'
                ]
            ]);
    }

    public function test_validates_date_format(): void
    {
        $response = $this->postJson('/api/meals/comment', [
            'date' => 'invalid-date'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['date']);
    }

    public function test_returns_fallback_comment_when_llm_fails(): void
    {
        // Create test meals
        Meal::create(['name' => '朝食', 'calories' => 400, 'date' => '2024-01-01']);
        Meal::create(['name' => '昼食', 'calories' => 600, 'date' => '2024-01-01']);

        // Mock LLMService to throw exception
        $this->mock(LLMService::class, function ($mock) {
            $mock->shouldReceive('generateMealComment')
                ->once()
                ->andThrow(new \Exception('API Error'));
        });

        $response = $this->postJson('/api/meals/comment', [
            'date' => '2024-01-01'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'comment',
                    'date',
                    'meal_count',
                    'total_calories',
                    'is_fallback'
                ]
            ])
            ->assertJson([
                'data' => [
                    'is_fallback' => true,
                    'total_calories' => 1000
                ]
            ]);
    }

    public function test_fallback_comment_for_low_calories(): void
    {
        Meal::create(['name' => '朝食', 'calories' => 300, 'date' => '2024-01-01']);
        Meal::create(['name' => '昼食', 'calories' => 400, 'date' => '2024-01-01']);

        // Mock LLMService to throw exception
        $this->mock(LLMService::class, function ($mock) {
            $mock->shouldReceive('generateMealComment')
                ->once()
                ->andThrow(new \Exception('API Error'));
        });

        $response = $this->postJson('/api/meals/comment', [
            'date' => '2024-01-01'
        ]);

        $response->assertStatus(200);
        $this->assertStringContainsString('摂取カロリーが少なめ', $response->json('data.comment'));
    }

    public function test_fallback_comment_for_high_calories(): void
    {
        Meal::create(['name' => '朝食', 'calories' => 900, 'date' => '2024-01-01']);
        Meal::create(['name' => '昼食', 'calories' => 1000, 'date' => '2024-01-01']);
        Meal::create(['name' => '夕食', 'calories' => 1200, 'date' => '2024-01-01']);

        // Mock LLMService to throw exception
        $this->mock(LLMService::class, function ($mock) {
            $mock->shouldReceive('generateMealComment')
                ->once()
                ->andThrow(new \Exception('API Error'));
        });

        $response = $this->postJson('/api/meals/comment', [
            'date' => '2024-01-01'
        ]);

        $response->assertStatus(200);
        $this->assertStringContainsString('摂取カロリーがやや多め', $response->json('data.comment'));
    }
}