import React, { useState, useEffect } from 'react';
import MealForm from './MealForm';
import MealList from './MealList';
import CaloriesSummary from './CaloriesSummary';
import DailyComment from './DailyComment';
import mealApi from '../api/mealApi';

const Dashboard = () => {
  const [meals, setMeals] = useState([]);
  const [selectedDate, setSelectedDate] = useState(
    new Date().toISOString().split('T')[0]
  );
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  useEffect(() => {
    fetchMeals();
  }, []);

  // 食事データの取得
  const fetchMeals = async () => {
    try {
      setLoading(true);
      setError(null);
      const data = await mealApi.getAllMeals();
      setMeals(data);
    } catch (err) {
      setError('データの取得に失敗しました');
      console.error('Error fetching meals:', err);
    } finally {
      setLoading(false);
    }
  };

  // 食事の追加処理
  const handleAddMeal = async (mealData) => {
    try {
      setError(null);
      const newMeal = await mealApi.createMeal(mealData);
      setMeals([...meals, newMeal]);
      // 新しい食事の日付に選択日を更新
      setSelectedDate(mealData.date);
    } catch (err) {
      setError('食事の追加に失敗しました');
      console.error('Error adding meal:', err);
    }
  };

  // 食事の削除処理
  const handleDeleteMeal = async (id) => {
    try {
      setError(null);
      await mealApi.deleteMeal(id);
      setMeals(meals.filter(meal => meal.id !== id));
    } catch (err) {
      setError('食事の削除に失敗しました');
      console.error('Error deleting meal:', err);
    }
  };

  return (
    <div>
      <header className="mb-8">
        <h1 className="text-3xl font-bold text-gray-900">
          ダッシュボード
        </h1>
        <p className="text-gray-600 mt-2">
          日々のカロリー摂取を記録し、食事を管理しましょう
        </p>
      </header>

      {error && (
        <div className="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">
          {error}
        </div>
      )}

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div className="lg:col-span-1">
          <div className="space-y-6">
            {/* 日付選択と ナビゲーション */}
            <div className="bg-white rounded-lg shadow-sm p-4 border">
              <label htmlFor="view-date" className="block text-sm font-medium text-gray-700 mb-2">
                表示日付
              </label>
              <input
                type="date"
                id="view-date"
                value={selectedDate}
                onChange={(e) => setSelectedDate(e.target.value)}
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 mb-3"
              />
              
              <div className="flex gap-2">
                <button
                  onClick={() => {
                    const yesterday = new Date(selectedDate);
                    yesterday.setDate(yesterday.getDate() - 1);
                    setSelectedDate(yesterday.toISOString().split('T')[0]);
                  }}
                  className="flex-1 px-2 py-1 text-sm bg-gray-200 hover:bg-gray-300 rounded transition"
                >
                  前日
                </button>
                <button
                  onClick={() => setSelectedDate(new Date().toISOString().split('T')[0])}
                  className="flex-1 px-2 py-1 text-sm bg-indigo-500 text-white hover:bg-indigo-600 rounded transition"
                >
                  今日
                </button>
                <button
                  onClick={() => {
                    const tomorrow = new Date(selectedDate);
                    tomorrow.setDate(tomorrow.getDate() + 1);
                    setSelectedDate(tomorrow.toISOString().split('T')[0]);
                  }}
                  className="flex-1 px-2 py-1 text-sm bg-gray-200 hover:bg-gray-300 rounded transition"
                >
                  翌日
                </button>
              </div>
            </div>
            
            <CaloriesSummary meals={meals} selectedDate={selectedDate} />
            <DailyComment 
              date={selectedDate} 
              hasMeals={meals.filter(meal => meal.date === selectedDate).length > 0}
            />
            <MealForm onSubmit={handleAddMeal} defaultDate={selectedDate} />
          </div>
        </div>

        <div className="lg:col-span-2">
          {loading ? (
            <div className="bg-white rounded-lg shadow-sm p-6 text-center border">
              <div className="text-gray-500">読み込み中...</div>
            </div>
          ) : (
            <MealList 
              meals={meals.filter(meal => meal.date === selectedDate)} 
              onDelete={handleDeleteMeal} 
              selectedDate={selectedDate}
            />
          )}
        </div>
      </div>
    </div>
  );
};

export default Dashboard;