import React, { useState } from 'react';
import mealApi from '../api/mealApi';

const DailyComment = ({ date, hasMeals }) => {
  const [comment, setComment] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState(null);
  const [showComment, setShowComment] = useState(false);

  // AIアドバイスを生成する関数
  const handleGenerateComment = async () => {
    if (!hasMeals) {
      setError('この日の食事記録がありません');
      return;
    }

    setIsLoading(true);
    setError(null);
    
    try {
      const response = await mealApi.generateComment(date);
      setComment(response.comment);
      setShowComment(true);
    } catch (err) {
      console.error('Error generating comment:', err);
      setError('コメントの生成に失敗しました。もう一度お試しください。');
    } finally {
      setIsLoading(false);
    }
  };

  // コメントをクリアする関数
  const handleClearComment = () => {
    setComment('');
    setShowComment(false);
    setError(null);
  };

  return (
    <div className="bg-white rounded-lg shadow-md p-6">
      <h2 className="text-xl font-bold mb-4">AIアドバイス</h2>
      
      <div className="mb-4">
        <p className="text-sm text-gray-600 mb-2">
          選択中の日付: <span className="font-medium">{date}</span>
        </p>
      </div>

      {error && (
        <div className="mb-4 p-3 bg-red-100 text-red-700 rounded-md text-sm">
          {error}
        </div>
      )}

      {!showComment ? (
        <button
          onClick={handleGenerateComment}
          disabled={isLoading || !hasMeals}
          className={`w-full py-2 px-4 rounded-md transition duration-200 ${
            isLoading || !hasMeals
              ? 'bg-gray-300 text-gray-500 cursor-not-allowed'
              : 'bg-green-500 text-white hover:bg-green-600'
          }`}
        >
          {isLoading ? (
            <span className="flex items-center justify-center">
              <svg className="animate-spin h-5 w-5 mr-2" viewBox="0 0 24 24">
                <circle
                  className="opacity-25"
                  cx="12"
                  cy="12"
                  r="10"
                  stroke="currentColor"
                  strokeWidth="4"
                  fill="none"
                />
                <path
                  className="opacity-75"
                  fill="currentColor"
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                />
              </svg>
              生成中...
            </span>
          ) : (
            'AIアドバイスを生成'
          )}
        </button>
      ) : (
        <div className="space-y-4">
          <div className="p-4 bg-blue-50 rounded-md">
            <div className="flex items-start">
              <svg
                className="w-5 h-5 text-blue-500 mt-0.5 mr-2 flex-shrink-0"
                fill="currentColor"
                viewBox="0 0 20 20"
              >
                <path
                  fillRule="evenodd"
                  d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                  clipRule="evenodd"
                />
              </svg>
              <p className="text-gray-700 text-sm leading-relaxed">{comment}</p>
            </div>
          </div>

          <button
            onClick={handleClearComment}
            className="w-full py-2 px-4 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition duration-200"
          >
            クリア
          </button>
        </div>
      )}

      {!hasMeals && (
        <p className="text-sm text-gray-500 mt-2 text-center">
          食事を記録してからアドバイスを生成してください
        </p>
      )}
    </div>
  );
};

export default DailyComment;