<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\LLMService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CalorieEstimationTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_estimate_calories_for_meal(): void
    {
        // Mock LLMService
        $this->mock(LLMService::class, function ($mock) {
            $mock->shouldReceive('estimateCalories')
                ->once()
                ->with('カレーライス', '大盛り')
                ->andReturn([
                    'calories' => 900,
                    'explanation' => 'カレーライス大盛りの一般的なカロリー'
                ]);
        });

        $response = $this->postJson('/api/meals/estimate-calories', [
            'name' => 'カレーライス',
            'portion' => '大盛り'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'calories',
                    'explanation',
                    'meal_name',
                    'portion',
                    'is_estimated'
                ]
            ])
            ->assertJson([
                'data' => [
                    'calories' => 900,
                    'meal_name' => 'カレーライス',
                    'portion' => '大盛り',
                    'is_estimated' => true
                ]
            ]);
    }

    public function test_can_estimate_calories_without_portion(): void
    {
        // Mock LLMService
        $this->mock(LLMService::class, function ($mock) {
            $mock->shouldReceive('estimateCalories')
                ->once()
                ->with('ラーメン', null)
                ->andReturn([
                    'calories' => 500,
                    'explanation' => 'ラーメン一杯の標準的なカロリー'
                ]);
        });

        $response = $this->postJson('/api/meals/estimate-calories', [
            'name' => 'ラーメン'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'calories' => 500,
                    'meal_name' => 'ラーメン',
                    'is_estimated' => true
                ]
            ]);
    }

    public function test_returns_fallback_when_llm_fails(): void
    {
        // Mock LLMService to throw exception
        $this->mock(LLMService::class, function ($mock) {
            $mock->shouldReceive('estimateCalories')
                ->once()
                ->andThrow(new \Exception('API Error'));
        });

        $response = $this->postJson('/api/meals/estimate-calories', [
            'name' => 'カレー'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'calories',
                    'explanation',
                    'meal_name',
                    'is_estimated',
                    'is_fallback'
                ]
            ])
            ->assertJson([
                'data' => [
                    'calories' => 700, // Fallback value for カレー
                    'is_fallback' => true
                ]
            ]);
    }

    public function test_validates_meal_name_is_required(): void
    {
        $response = $this->postJson('/api/meals/estimate-calories', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_can_store_meal_with_portion(): void
    {
        $response = $this->postJson('/api/meals', [
            'name' => '朝食',
            'portion' => 'パン2枚と卵',
            'calories' => 400,
            'date' => '2024-01-01',
            'is_estimated' => true
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'name' => '朝食',
                'portion' => 'パン2枚と卵',
                'calories' => 400,
                'is_estimated' => true
            ]);

        $this->assertDatabaseHas('meals', [
            'name' => '朝食',
            'portion' => 'パン2枚と卵',
            'calories' => 400,
            'is_estimated' => true
        ]);
    }

    public function test_can_store_meal_without_calories(): void
    {
        $response = $this->postJson('/api/meals', [
            'name' => '昼食',
            'portion' => '定食',
            'date' => '2024-01-01'
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('meals', [
            'name' => '昼食',
            'portion' => '定食',
            'calories' => 0,
            'is_estimated' => true
        ]);
    }
}