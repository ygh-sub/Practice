import axios from 'axios';

const API_BASE_URL = 'http://localhost:8000/api';

const api = axios.create({
  baseURL: API_BASE_URL,
  withCredentials: true,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Request interceptor to add auth token
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Response interceptor to handle auth errors
api.interceptors.response.use(
  (response) => {
    return response;
  },
  (error) => {
    if (error.response?.status === 401) {
      // Token is invalid or expired, remove it
      localStorage.removeItem('auth_token');
      // Redirect to login if not already there
      if (window.location.pathname !== '/login' && window.location.pathname !== '/register') {
        window.location.href = '/login';
      }
    }
    return Promise.reject(error);
  }
);

export const mealApi = {
  getAllMeals: async () => {
    const response = await api.get('/meals');
    return response.data.data;
  },

  getMealsByDate: async (date) => {
    const response = await api.get('/meals', { params: { date } });
    return response.data.data;
  },

  createMeal: async (mealData) => {
    const response = await api.post('/meals', mealData);
    return response.data.data;
  },

  updateMeal: async (id, mealData) => {
    const response = await api.put(`/meals/${id}`, mealData);
    return response.data.data;
  },

  deleteMeal: async (id) => {
    await api.delete(`/meals/${id}`);
  },

  generateComment: async (date) => {
    const response = await api.post('/meals/comment', { date });
    return response.data.data;
  },

  estimateCalories: async (name, portion) => {
    const response = await api.post('/meals/estimate-calories', { name, portion });
    return response.data.data;
  },

  getStatistics: async (startDate, endDate) => {
    const response = await api.get('/meals/statistics', { 
      params: { start_date: startDate, end_date: endDate } 
    });
    return response.data.data;
  },
};

export default mealApi;