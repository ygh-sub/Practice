<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Meal;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MealTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_meal(): void
    {
        $meal = Meal::create([
            'name' => '朝食',
            'calories' => 500,
            'date' => '2024-01-01'
        ]);

        $this->assertDatabaseHas('meals', [
            'name' => '朝食',
            'calories' => 500,
            'date' => '2024-01-01'
        ]);

        $this->assertEquals('朝食', $meal->name);
        $this->assertEquals(500, $meal->calories);
        $this->assertEquals('2024-01-01', $meal->date->format('Y-m-d'));
    }

    public function test_can_update_meal(): void
    {
        $meal = Meal::create([
            'name' => '昼食',
            'calories' => 600,
            'date' => '2024-01-01'
        ]);

        $meal->update([
            'calories' => 700
        ]);

        $this->assertDatabaseHas('meals', [
            'name' => '昼食',
            'calories' => 700,
            'date' => '2024-01-01'
        ]);
    }

    public function test_can_delete_meal(): void
    {
        $meal = Meal::create([
            'name' => '夕食',
            'calories' => 800,
            'date' => '2024-01-01'
        ]);

        $meal->delete();

        $this->assertDatabaseMissing('meals', [
            'name' => '夕食',
            'calories' => 800,
            'date' => '2024-01-01'
        ]);
    }

    public function test_can_get_meals_by_date(): void
    {
        Meal::create(['name' => '朝食', 'calories' => 400, 'date' => '2024-01-01']);
        Meal::create(['name' => '昼食', 'calories' => 600, 'date' => '2024-01-01']);
        Meal::create(['name' => '夕食', 'calories' => 800, 'date' => '2024-01-02']);

        $meals = Meal::where('date', '2024-01-01')->get();

        $this->assertCount(2, $meals);
    }
}