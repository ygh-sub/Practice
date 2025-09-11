import React, { useState, useEffect } from 'react';
import mealApi from '../api/mealApi';

const MealForm = ({ onSubmit, defaultDate }) => {
  const isDevelopment = import.meta.env.DEV;
  
  const [formData, setFormData] = useState({
    name: isDevelopment ? 'ハンバーガーセット' : '',
    portion: isDevelopment ? '大盛り' : '',
    calories: isDevelopment ? '850' : '',
    date: defaultDate || new Date().toISOString().split('T')[0],
  });
  
  const [isEstimating, setIsEstimating] = useState(false);
  const [estimationError, setEstimationError] = useState(null);
  const [manualMode, setManualMode] = useState(false);
  const [isEstimated, setIsEstimated] = useState(false);

  // defaultDateが変更された時に日付を更新
  useEffect(() => {
    if (defaultDate) {
      setFormData(prev => ({ ...prev, date: defaultDate }));
    }
  }, [defaultDate]);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData((prev) => ({
      ...prev,
      [name]: value,
    }));
    
    // ユーザーが手動でカロリーを変更した時に推定ステータスをリセット
    if (name === 'calories') {
      setIsEstimated(false);
    }
  };

  const handleEstimateCalories = async () => {
    if (!formData.name) {
      setEstimationError('食事名を入力してください');
      return;
    }

    setIsEstimating(true);
    setEstimationError(null);
    
    try {
      const result = await mealApi.estimateCalories(formData.name, formData.portion);
      setFormData(prev => ({
        ...prev,
        calories: result.calories.toString(),
      }));
      setIsEstimated(true);
      
      // 利用可能な場合はユーザーに説明を表示
      if (result.explanation) {
        setEstimationError(result.explanation); // 情報メッセージにエラー状態を使用
      }
    } catch (error) {
      console.error('Error estimating calories:', error);
      setEstimationError('カロリー推定に失敗しました');
    } finally {
      setIsEstimating(false);
    }
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    
    if (!formData.name || !formData.date) {
      return;
    }

    // カロリーなしでも送信を許可（0または推定値になる）
    const caloriesValue = formData.calories ? parseInt(formData.calories, 10) : 0;

    onSubmit({
      name: formData.name,
      portion: formData.portion || null,
      calories: caloriesValue,
      date: formData.date,
      is_estimated: isEstimated,
    });

    // フォームをリセット
    setFormData({
      name: '',
      portion: '',
      calories: '',
      date: formData.date,
    });
    setIsEstimated(false);
    setEstimationError(null);
    setManualMode(false);
  };

  return (
    <form onSubmit={handleSubmit} className="bg-white rounded-lg shadow-md p-6">
      <h2 className="text-xl font-bold mb-4">食事を記録</h2>
      
      <div className="mb-4">
        <label htmlFor="name" className="block text-sm font-medium text-gray-700 mb-1">
          食事名 <span className="text-red-500">*</span>
        </label>
        <input
          type="text"
          id="name"
          name="name"
          value={formData.name}
          onChange={handleChange}
          required
          className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          placeholder="例: カレーライス、朝食セット"
        />
      </div>

      <div className="mb-4">
        <label htmlFor="portion" className="block text-sm font-medium text-gray-700 mb-1">
          分量（任意）
        </label>
        <input
          type="text"
          id="portion"
          name="portion"
          value={formData.portion}
          onChange={handleChange}
          className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          placeholder="例: 大盛り、小盛り、2人前"
        />
      </div>

      <div className="mb-4">
        <label htmlFor="calories" className="block text-sm font-medium text-gray-700 mb-1">
          カロリー (kcal)
          {!manualMode && (
            <span className="text-sm text-gray-500 ml-2">
              （自動推定または
              <button
                type="button"
                onClick={() => setManualMode(true)}
                className="text-blue-600 hover:underline"
              >
                手動入力
              </button>
              ）
            </span>
          )}
        </label>
        
        <div className="flex gap-2">
          <input
            type="number"
            id="calories"
            name="calories"
            value={formData.calories}
            onChange={handleChange}
            min="0"
            disabled={!manualMode && isEstimating}
            className={`flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 ${
              !manualMode && isEstimating ? 'bg-gray-100' : ''
            }`}
            placeholder={manualMode ? "例: 500" : "自動推定されます"}
          />
          
          {!manualMode && (
            <button
              type="button"
              onClick={handleEstimateCalories}
              disabled={isEstimating || !formData.name}
              className={`px-4 py-2 rounded-md transition duration-200 ${
                isEstimating || !formData.name
                  ? 'bg-gray-300 text-gray-500 cursor-not-allowed'
                  : 'bg-green-500 text-white hover:bg-green-600'
              }`}
            >
              {isEstimating ? '推定中...' : '推定'}
            </button>
          )}
          
          {manualMode && (
            <button
              type="button"
              onClick={() => {
                setManualMode(false);
                setFormData(prev => ({ ...prev, calories: '' }));
                setIsEstimated(false);
              }}
              className="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition duration-200"
            >
              自動に戻す
            </button>
          )}
        </div>
        
        {estimationError && (
          <p className={`text-sm mt-1 ${isEstimated ? 'text-green-600' : 'text-red-600'}`}>
            {estimationError}
          </p>
        )}
        
        {isEstimated && (
          <p className="text-sm text-blue-600 mt-1">
            推定値: {formData.calories} kcal
          </p>
        )}
      </div>

      <div className="mb-4">
        <label htmlFor="date" className="block text-sm font-medium text-gray-700 mb-1">
          日付 <span className="text-red-500">*</span>
        </label>
        <input
          type="date"
          id="date"
          name="date"
          value={formData.date}
          onChange={handleChange}
          required
          className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
      </div>

      <button
        type="submit"
        disabled={!formData.name || !formData.date || isEstimating}
        className={`w-full py-2 px-4 rounded-md transition duration-200 ${
          !formData.name || !formData.date || isEstimating
            ? 'bg-gray-300 text-gray-500 cursor-not-allowed'
            : 'bg-blue-500 text-white hover:bg-blue-600'
        }`}
      >
        追加
      </button>
    </form>
  );
};

export default MealForm;