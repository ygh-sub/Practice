import React, { useState, useEffect } from 'react';
import MealForm from './components/MealForm';
import MealList from './components/MealList';
import CaloriesSummary from './components/CaloriesSummary';
import mealApi from './api/mealApi';

function App() {
  const [meals, setMeals] = useState([]);
  const [selectedDate, setSelectedDate] = useState(
    new Date().toISOString().split('T')[0]
  );
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  useEffect(() => {
    fetchMeals();
  }, []);

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
    <div className="min-h-screen bg-gray-100">
      <div className="container mx-auto px-4 py-8 max-w-6xl">
        <header className="mb-8">
          <h1 className="text-4xl font-bold text-center text-gray-800">
            カロリー管理アプリ
          </h1>
        </header>

        {error && (
          <div className="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">
            {error}
          </div>
        )}

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <div className="lg:col-span-1">
            <div className="space-y-6">
              <CaloriesSummary meals={meals} selectedDate={selectedDate} />
              <MealForm onSubmit={handleAddMeal} />
            </div>
          </div>

          <div className="lg:col-span-2">
            {loading ? (
              <div className="bg-white rounded-lg shadow-md p-6 text-center">
                <div className="text-gray-500">読み込み中...</div>
              </div>
            ) : (
              <MealList meals={meals} onDelete={handleDeleteMeal} />
            )}
          </div>
        </div>
      </div>
    </div>
  );
}

export default App;