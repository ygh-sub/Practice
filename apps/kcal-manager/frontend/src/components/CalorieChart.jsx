import React, { useState, useEffect } from 'react';
import {
  LineChart,
  Line,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  Legend,
  ResponsiveContainer
} from 'recharts';
import axios from 'axios';

const CalorieChart = ({ dateRange = 30 }) => {
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [summary, setSummary] = useState(null);

  useEffect(() => {
    fetchStatistics();
  }, [dateRange]);

  // 統計情報を取得する関数
  const fetchStatistics = async () => {
    try {
      setLoading(true);
      setError(null);

      // 日付範囲を計算
      const endDate = new Date();
      const startDate = new Date();
      startDate.setDate(startDate.getDate() - dateRange);

      const params = {
        start_date: startDate.toISOString().split('T')[0],
        end_date: endDate.toISOString().split('T')[0]
      };

      const response = await axios.get('/meals/statistics', { params });
      const { daily_stats, summary } = response.data.data;

      // チャート用にデータを変換
      const chartData = [];
      
      // 範囲内のすべての日付を生成し、異なる日は0として表示
      const currentDate = new Date(startDate);
      while (currentDate <= endDate) {
        const dateStr = currentDate.toISOString().split('T')[0];
        const dayData = daily_stats.find(stat => stat.date === dateStr);
        
        chartData.push({
          date: dateStr,
          displayDate: new Date(dateStr).toLocaleDateString('ja-JP', { 
            month: 'short', 
            day: 'numeric' 
          }),
          calories: dayData ? parseInt(dayData.total_calories) : 0,
          meals: dayData ? parseInt(dayData.meal_count) : 0
        });
        
        currentDate.setDate(currentDate.getDate() + 1);
      }

      setData(chartData);
      setSummary(summary);
    } catch (err) {
      setError('統計情報の取得に失敗しました');
      console.error('Error fetching statistics:', err);
    } finally {
      setLoading(false);
    }
  };

  // カスタムツールチップコンポーネント
  const CustomTooltip = ({ active, payload, label }) => {
    if (active && payload && payload.length) {
      const data = payload[0].payload;
      return (
        <div className="bg-white p-3 border border-gray-200 rounded-lg shadow-lg">
          <p className="text-sm font-medium text-gray-900">
            {new Date(data.date).toLocaleDateString('ja-JP', { 
              year: 'numeric',
              month: 'long', 
              day: 'numeric' 
            })}
          </p>
          <p className="text-sm text-indigo-600">
            カロリー: {data.calories}
          </p>
          <p className="text-sm text-gray-600">
            食事数: {data.meals}
          </p>
        </div>
      );
    }
    return null;
  };

  if (loading) {
    return (
      <div className="bg-white p-6 rounded-lg shadow-sm">
        <div className="animate-pulse">
          <div className="h-4 bg-gray-200 rounded w-1/3 mb-4"></div>
          <div className="h-64 bg-gray-200 rounded"></div>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="bg-white p-6 rounded-lg shadow-sm">
        <div className="text-red-600 text-center">
          <p>{error}</p>
          <button
            onClick={fetchStatistics}
            className="mt-2 text-sm text-indigo-600 hover:text-indigo-800"
          >
            再試行
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="bg-white p-6 rounded-lg shadow-sm">
      <div className="mb-6">
        <h3 className="text-lg font-medium text-gray-900 mb-2">
          日々のカロリー摂取量
        </h3>
        {summary && (
          <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
            <div className="text-center">
              <p className="text-2xl font-bold text-indigo-600">
                {summary.total_calories.toLocaleString()}
              </p>
              <p className="text-sm text-gray-600">合計カロリー</p>
            </div>
            <div className="text-center">
              <p className="text-2xl font-bold text-green-600">
                {Math.round(summary.average_daily_calories)}
              </p>
              <p className="text-sm text-gray-600">日平均</p>
            </div>
            <div className="text-center">
              <p className="text-2xl font-bold text-orange-600">
                {summary.total_meals}
              </p>
              <p className="text-sm text-gray-600">合計食事数</p>
            </div>
            <div className="text-center">
              <p className="text-2xl font-bold text-purple-600">
                {summary.days_with_data}
              </p>
              <p className="text-sm text-gray-600">活動日数</p>
            </div>
          </div>
        )}
      </div>
      
      <div className="h-64 w-full">
        <ResponsiveContainer width="100%" height="100%">
          <LineChart
            data={data}
            margin={{
              top: 5,
              right: 30,
              left: 20,
              bottom: 5,
            }}
          >
            <CartesianGrid strokeDasharray="3 3" stroke="#f0f0f0" />
            <XAxis
              dataKey="displayDate"
              tick={{ fontSize: 12 }}
              tickLine={false}
              axisLine={false}
            />
            <YAxis
              tick={{ fontSize: 12 }}
              tickLine={false}
              axisLine={false}
              domain={[0, 'dataMax + 200']}
            />
            <Tooltip content={<CustomTooltip />} />
            <Legend />
            <Line
              type="monotone"
              dataKey="calories"
              stroke="#6366f1"
              strokeWidth={2}
              dot={{ fill: '#6366f1', strokeWidth: 2, r: 4 }}
              activeDot={{ r: 6, stroke: '#6366f1', strokeWidth: 2 }}
              name="カロリー"
            />
          </LineChart>
        </ResponsiveContainer>
      </div>
      
      {data.length === 0 && (
        <div className="text-center py-8 text-gray-500">
          <p>選択した期間のデータがありません</p>
        </div>
      )}
    </div>
  );
};

export default CalorieChart;