import React, { useState } from 'react';
import CalorieChart from './CalorieChart';

const Analytics = () => {
  const [selectedPeriod, setSelectedPeriod] = useState(30);

  // 時期選択のオプション
  const periods = [
    { label: '1週間', value: 7 },
    { label: '1ヶ月', value: 30 },
    { label: '3ヶ月', value: 90 },
    { label: '1年', value: 365 }
  ];

  return (
    <div>
      <header className="mb-8">
        <h1 className="text-3xl font-bold text-gray-900">
          分析
        </h1>
        <p className="text-gray-600 mt-2">
          時間の経過とともにカロリー摂取パターンやトレンドを分析します
        </p>
      </header>

      <div className="mb-6">
        <label className="block text-sm font-medium text-gray-700 mb-3">
          時間範囲
        </label>
        <div className="flex flex-wrap gap-2">
          {periods.map((period) => (
            <button
              key={period.value}
              onClick={() => setSelectedPeriod(period.value)}
              className={`px-4 py-2 rounded-lg text-sm font-medium transition-colors ${
                selectedPeriod === period.value
                  ? 'bg-indigo-600 text-white'
                  : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50'
              }`}
            >
              {period.label}
            </button>
          ))}
        </div>
      </div>

      <CalorieChart dateRange={selectedPeriod} />
      
      <div className="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
        <div className="bg-white p-6 rounded-lg shadow-sm border">
          <h3 className="text-lg font-medium text-gray-900 mb-4">
            インサイトとティップス
          </h3>
          <div className="space-y-3 text-sm text-gray-600">
            <p>• 毎日一貫したカロリー追跡は健康的な食事習慣の維持に役立ちます</p>
            <p>• 激しい日々の変動よりも安定したカロリー摂取を目指しましょう</p>
            <p>• 食習慣のパターンを見つけて改善できる部分を特定しましょう</p>
            <p>• データを使って健康ジャーニーの現実的な目標を設定しましょう</p>
          </div>
        </div>
        
        <div className="bg-white p-6 rounded-lg shadow-sm border">
          <h3 className="text-lg font-medium text-gray-900 mb-4">
            推奨日常摂取量
          </h3>
          <div className="space-y-3 text-sm text-gray-600">
            <div className="flex justify-between">
              <span>運動不足の成人女性:</span>
              <span className="font-medium">1,600-2,000 kcal</span>
            </div>
            <div className="flex justify-between">
              <span>運動不足の成人男性:</span>
              <span className="font-medium">2,000-2,400 kcal</span>
            </div>
            <div className="flex justify-between">
              <span>活動的な成人女性:</span>
              <span className="font-medium">2,000-2,400 kcal</span>
            </div>
            <div className="flex justify-between">
              <span>活動的な成人男性:</span>
              <span className="font-medium">2,400-3,000 kcal</span>
            </div>
          </div>
          <p className="text-xs text-gray-500 mt-3">
            * これらは一般的なガイドラインです。個別の推奨事項については医療専門家にご相談ください。
          </p>
        </div>
      </div>
    </div>
  );
};

export default Analytics;