import React, { useState, useEffect } from 'react';
import MealForm from './MealForm';
import MealList from './MealList';
import mealApi from '../api/mealApi';

const MealsPage = () => {
  const [meals, setMeals] = useState([]);
  const [selectedDate, setSelectedDate] = useState('');
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

  // 日付でフィルターした食事リスト
  const filteredMeals = selectedDate 
    ? meals.filter(meal => meal.date === selectedDate)
    : meals;

  return (
    <div>
      <header className="mb-8">
        <h1 className="text-3xl font-bold text-gray-900">
          食事管理
        </h1>
        <p className="text-gray-600 mt-2">
          すべての食事を管理し、栄養摂取を追跡しましょう
        </p>
      </header>

      {error && (
        <div className="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">
          {error}
        </div>
      )}

      <div className="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <div className="lg:col-span-1">
          <div className="space-y-6">
            <MealForm onSubmit={handleAddMeal} />
            
            {/* 日付フィルター */}
            <div className="bg-white rounded-lg shadow-sm p-4 border">
              <label htmlFor="filter-date" className="block text-sm font-medium text-gray-700 mb-2">
                日付で絞り込み
              </label>
              <input
                type="date"
                id="filter-date"
                value={selectedDate}
                onChange={(e) => setSelectedDate(e.target.value)}
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 mb-3"
              />
              <button
                onClick={() => setSelectedDate('')}
                className="w-full px-3 py-2 text-sm bg-gray-200 hover:bg-gray-300 rounded transition"
              >
                すべての食事を表示
              </button>
            </div>
          </div>
        </div>

        <div className="lg:col-span-3">
          {loading ? (
            <div className="bg-white rounded-lg shadow-sm p-6 text-center border">
              <div className="text-gray-500">読み込み中...</div>
            </div>
          ) : (
            <div>
              <div className="mb-4 flex justify-between items-center">
                <h2 className="text-lg font-medium text-gray-900">
                  {selectedDate ? `${selectedDate}の食事` : 'すべての食事'} 
                  <span className="text-sm text-gray-500 ml-2">
                    ({filteredMeals.length}件)
                  </span>
                </h2>
              </div>
              <MealList 
                meals={filteredMeals} 
                onDelete={handleDeleteMeal} 
                selectedDate={selectedDate}
                showAllDates={!selectedDate}
              />
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default MealsPage;