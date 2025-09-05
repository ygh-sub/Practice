import React from 'react';

const MealList = ({ meals, onDelete, selectedDate, showAllDates = false }) => {

  // 食事記録がない場合の表示
  if (meals.length === 0) {
    return (
      <div className="bg-white rounded-lg shadow-md p-6">
        <h2 className="text-xl font-bold mb-4">
          {selectedDate ? `${selectedDate}の食事記録` : '食事記録'}
        </h2>
        <div className="text-center text-gray-500">
          この日の食事記録がありません
        </div>
      </div>
    );
  }

  return (
    <div className="bg-white rounded-lg shadow-md p-6">
      <h2 className="text-xl font-bold mb-4">
        {selectedDate ? `${selectedDate}の食事記録` : '食事記録'}
      </h2>
      
      <div className="space-y-2">
        {/* 各食事記録をマップして表示 */}
        {meals.map((meal) => (
          <div
            key={meal.id}
            className="flex items-center justify-between p-3 bg-gray-50 rounded-md"
          >
            <div className="flex-1">
              <span className="font-medium text-gray-800">{meal.name}</span>
              {meal.portion && (
                <span className="ml-2 text-sm text-gray-500">({meal.portion})</span>
              )}
              <span className="ml-4 text-gray-600">{meal.calories} kcal</span>
              {showAllDates && (
                <span className="ml-2 text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">
                  {meal.date}
                </span>
              )}
              {meal.is_estimated && (
                <span className="ml-2 text-xs text-blue-600 bg-blue-50 px-2 py-1 rounded">
                  推定値
                </span>
              )}
            </div>
            
            <button
              onClick={() => onDelete(meal.id)}
              className="px-3 py-1 text-red-600 hover:bg-red-50 rounded-md transition duration-200"
              aria-label="削除"
            >
              削除
            </button>
          </div>
        ))}
      </div>
    </div>
  );
};

export default MealList;