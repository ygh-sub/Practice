<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Meal;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MealApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_all_meals(): void
    {
        Meal::create(['name' => '朝食', 'calories' => 400, 'date' => '2024-01-01']);
        Meal::create(['name' => '昼食', 'calories' => 600, 'date' => '2024-01-01']);

        $response = $this->getJson('/api/meals');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'calories', 'date']
                ]
            ]);
    }

    public function test_can_get_meals_by_date(): void
    {
        Meal::create(['name' => '朝食', 'calories' => 400, 'date' => '2024-01-01']);
        Meal::create(['name' => '昼食', 'calories' => 600, 'date' => '2024-01-01']);
        Meal::create(['name' => '夕食', 'calories' => 800, 'date' => '2024-01-02']);

        $response = $this->getJson('/api/meals?date=2024-01-01');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_can_create_meal(): void
    {
        $mealData = [
            'name' => '朝食',
            'calories' => 500,
            'date' => '2024-01-01'
        ];

        $response = $this->postJson('/api/meals', $mealData);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'name' => '朝食',
                'calories' => 500
            ]);

        $this->assertDatabaseHas('meals', $mealData);
    }

    public function test_can_update_meal(): void
    {
        $meal = Meal::create([
            'name' => '昼食',
            'calories' => 600,
            'date' => '2024-01-01'
        ]);

        $updateData = ['calories' => 700];

        $response = $this->putJson("/api/meals/{$meal->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'calories' => 700
            ]);

        $this->assertDatabaseHas('meals', [
            'id' => $meal->id,
            'calories' => 700
        ]);
    }

    public function test_can_delete_meal(): void
    {
        $meal = Meal::create([
            'name' => '夕食',
            'calories' => 800,
            'date' => '2024-01-01'
        ]);

        $response = $this->deleteJson("/api/meals/{$meal->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('meals', ['id' => $meal->id]);
    }

    public function test_validates_required_fields(): void
    {
        $response = $this->postJson('/api/meals', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'calories', 'date']);
    }

    public function test_validates_calories_is_numeric(): void
    {
        $response = $this->postJson('/api/meals', [
            'name' => '朝食',
            'calories' => 'not_a_number',
            'date' => '2024-01-01'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['calories']);
    }
}