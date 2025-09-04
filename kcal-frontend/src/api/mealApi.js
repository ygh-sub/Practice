import axios from 'axios';

const API_BASE_URL = 'http://localhost:8000/api';

const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

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
};

export default mealApi;