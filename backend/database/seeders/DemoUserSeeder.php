<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Meal;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    /**
     * デモユーザーとサンプルデータを作成
     */
    public function run(): void
    {
        // デモユーザーを作成
        $demoUser = User::firstOrCreate(
            ['email' => 'demo@example.com'],
            [
                'name' => 'デモユーザー',
                'password' => Hash::make('demo1234'),
            ]
        );

        // サンプルの食事データを作成
        $sampleMeals = [
            ['name' => '朝食：トースト&コーヒー', 'calories' => 350, 'date' => now()->subDays(0)->format('Y-m-d'), 'portion' => '通常'],
            ['name' => '昼食：ラーメン', 'calories' => 600, 'date' => now()->subDays(0)->format('Y-m-d'), 'portion' => '大盛り'],
            ['name' => '夕食：焼き魚定食', 'calories' => 550, 'date' => now()->subDays(0)->format('Y-m-d'), 'portion' => '通常'],
            
            ['name' => '朝食：おにぎり2個', 'calories' => 400, 'date' => now()->subDays(1)->format('Y-m-d'), 'portion' => '通常'],
            ['name' => '昼食：カツ丼', 'calories' => 800, 'date' => now()->subDays(1)->format('Y-m-d'), 'portion' => '並盛り'],
            ['name' => '夕食：サラダ&パスタ', 'calories' => 650, 'date' => now()->subDays(1)->format('Y-m-d'), 'portion' => '通常'],
            
            ['name' => '朝食：パンケーキ', 'calories' => 450, 'date' => now()->subDays(2)->format('Y-m-d'), 'portion' => '3枚'],
            ['name' => '昼食：うどん', 'calories' => 400, 'date' => now()->subDays(2)->format('Y-m-d'), 'portion' => '並盛り'],
            ['name' => '夕食：寿司', 'calories' => 600, 'date' => now()->subDays(2)->format('Y-m-d'), 'portion' => '10貫'],
            
            ['name' => '朝食：シリアル', 'calories' => 300, 'date' => now()->subDays(3)->format('Y-m-d'), 'portion' => '1ボウル'],
            ['name' => '昼食：カレーライス', 'calories' => 700, 'date' => now()->subDays(3)->format('Y-m-d'), 'portion' => '通常'],
            ['name' => '夕食：焼肉', 'calories' => 900, 'date' => now()->subDays(3)->format('Y-m-d'), 'portion' => '食べ放題'],
            
            ['name' => '朝食：スムージー', 'calories' => 250, 'date' => now()->subDays(4)->format('Y-m-d'), 'portion' => '1杯'],
            ['name' => '昼食：サンドイッチ', 'calories' => 450, 'date' => now()->subDays(4)->format('Y-m-d'), 'portion' => '2個'],
            ['name' => '夕食：鍋料理', 'calories' => 500, 'date' => now()->subDays(4)->format('Y-m-d'), 'portion' => '1人前'],
            
            ['name' => '朝食：目玉焼き&トースト', 'calories' => 380, 'date' => now()->subDays(5)->format('Y-m-d'), 'portion' => '通常'],
            ['name' => '昼食：天ぷらそば', 'calories' => 550, 'date' => now()->subDays(5)->format('Y-m-d'), 'portion' => '並盛り'],
            ['name' => '夕食：餃子定食', 'calories' => 750, 'date' => now()->subDays(5)->format('Y-m-d'), 'portion' => '通常'],
            
            ['name' => '朝食：ヨーグルト&フルーツ', 'calories' => 200, 'date' => now()->subDays(6)->format('Y-m-d'), 'portion' => '1ボウル'],
            ['name' => '昼食：ピザ', 'calories' => 800, 'date' => now()->subDays(6)->format('Y-m-d'), 'portion' => 'Mサイズ半分'],
            ['name' => '夕食：お好み焼き', 'calories' => 600, 'date' => now()->subDays(6)->format('Y-m-d'), 'portion' => '1枚'],
        ];

        // 既存のデータを削除して新しく作成
        Meal::where('user_id', $demoUser->id)->delete();
        
        foreach ($sampleMeals as $meal) {
            Meal::create(array_merge($meal, ['user_id' => $demoUser->id]));
        }

        echo "デモユーザーが作成されました。\n";
        echo "メールアドレス: demo@example.com\n";
        echo "パスワード: demo1234\n";
    }
}