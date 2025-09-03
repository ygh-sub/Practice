import React from 'react';

const CaloriesSummary = ({ meals, selectedDate }) => {
  const totalCalories = meals
    .filter(meal => meal.date === selectedDate)
    .reduce((sum, meal) => sum + meal.calories, 0);

  return (
    <div className="bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg shadow-md p-6">
      <h2 className="text-2xl font-bold mb-2">本日の合計カロリー</h2>
      
      <div className="text-sm opacity-90 mb-4">
        {selectedDate}
      </div>
      
      <div className="flex items-baseline">
        <span className="text-5xl font-bold">{totalCalories}</span>
        <span className="text-2xl ml-2">kcal</span>
      </div>
      
      <div className="mt-4 pt-4 border-t border-white/30">
        <div className="text-sm opacity-90">
          記録数: {meals.filter(meal => meal.date === selectedDate).length}件
        </div>
      </div>
    </div>
  );
};

export default CaloriesSummary;