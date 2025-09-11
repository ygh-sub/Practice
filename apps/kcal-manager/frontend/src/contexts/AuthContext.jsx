import { createContext, useState, useEffect } from 'react';
import axios from 'axios';

export const AuthContext = createContext();

// axiosのデフォルト設定
const API_BASE_URL = 'http://localhost:8000';
axios.defaults.baseURL = API_BASE_URL;
axios.defaults.withCredentials = true;
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null);
  const [token, setToken] = useState(localStorage.getItem('auth_token'));
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  // トークン変更時にaxios認証ヘッダーを設定
  useEffect(() => {
    if (token) {
      axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
      localStorage.setItem('auth_token', token);
    } else {
      delete axios.defaults.headers.common['Authorization'];
      localStorage.removeItem('auth_token');
    }
  }, [token]);

  // アプリ起動時にユーザーの認証状態を確認
  useEffect(() => {
    const checkAuth = async () => {
      // 開発環境の場合、自動的にデモユーザーでログイン
      if (import.meta.env.DEV) {
        const storedToken = localStorage.getItem('auth_token');
        
        // 既にトークンがある場合は、そのトークンを検証
        if (storedToken) {
          try {
            setToken(storedToken);
            axios.defaults.headers.common['Authorization'] = `Bearer ${storedToken}`;
            const response = await axios.get('/api/user');
            setUser(response.data.user);
            setLoading(false);
            return;
          } catch (error) {
            console.log('Stored token invalid, attempting auto-login...');
            localStorage.removeItem('auth_token');
          }
        }
        
        // トークンがない、または無効な場合は自動ログイン
        try {
          await axios.get('/sanctum/csrf-cookie');
          const response = await axios.post('/api/login', {
            email: 'demo@example.com',
            password: 'demo1234'
          });
          
          const { access_token, user } = response.data;
          setToken(access_token);
          setUser(user);
          localStorage.setItem('auth_token', access_token);
          axios.defaults.headers.common['Authorization'] = `Bearer ${access_token}`;
          console.log('Development auto-login successful');
        } catch (error) {
          console.error('Auto-login failed:', error);
          // 自動ログインに失敗した場合は通常のフローに戻る
        }
      } else {
        // 本番環境の場合は通常の認証チェック
        const storedToken = localStorage.getItem('auth_token');
        if (storedToken) {
          try {
            setToken(storedToken);
            axios.defaults.headers.common['Authorization'] = `Bearer ${storedToken}`;
            const response = await axios.get('/api/user');
            setUser(response.data.user);
          } catch (error) {
            console.error('Auth check failed:', error);
            // トークンが無効なので削除
            setToken(null);
            setUser(null);
            localStorage.removeItem('auth_token');
          }
        }
      }
      setLoading(false);
    };

    checkAuth();
  }, []);

  const login = async (email, password) => {
    try {
      setError(null);
      
      // CSRFトークンを取得（SPAモードで必要）
      await axios.get('/sanctum/csrf-cookie');
      
      const response = await axios.post('/api/login', { email, password });
      const { access_token, user } = response.data;
      
      setToken(access_token);
      setUser(user);
      
      return { success: true };
    } catch (error) {
      const errorMessage = error.response?.data?.message || 'ログインに失敗しました';
      setError(errorMessage);
      return { success: false, error: errorMessage };
    }
  };

  const register = async (name, email, password, password_confirmation) => {
    try {
      setError(null);
      
      // CSRFトークンを取得（SPAモードで必要）
      await axios.get('/sanctum/csrf-cookie');
      
      const response = await axios.post('/api/register', {
        name,
        email,
        password,
        password_confirmation
      });
      
      const { access_token, user } = response.data;
      
      setToken(access_token);
      setUser(user);
      
      return { success: true };
    } catch (error) {
      const errorMessage = error.response?.data?.message || 'アカウント作成に失敗しました';
      setError(errorMessage);
      return { success: false, error: errorMessage };
    }
  };

  const logout = async () => {
    try {
      if (token) {
        await axios.post('/api/logout');
      }
    } catch (error) {
      console.error('Logout error:', error);
    } finally {
      setToken(null);
      setUser(null);
      localStorage.removeItem('auth_token');
      delete axios.defaults.headers.common['Authorization'];
    }
  };

  const clearError = () => {
    setError(null);
  };

  const value = {
    user,
    token,
    loading,
    error,
    login,
    register,
    logout,
    clearError,
    isAuthenticated: !!user
  };

  return (
    <AuthContext.Provider value={value}>
      {children}
    </AuthContext.Provider>
  );
};