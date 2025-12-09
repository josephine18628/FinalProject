import axios from 'axios';

const API_URL = process.env.REACT_APP_API_URL || 'http://localhost:8000';

const api = axios.create({
  baseURL: API_URL,
  headers: {
    'Content-Type': 'application/json',
  },
});

// Request interceptor to add JWT token
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Response interceptor to handle 401
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('token');
      localStorage.removeItem('user');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

// Auth endpoints
export const authAPI = {
  register: (data) => api.post('/auth/register', data),
  login: (data) => api.post('/auth/login', data),
  adminRegister: (data) => api.post('/auth/admin-register', data),
  getMe: () => api.get('/auth/me'),
};

// Quiz endpoints
export const quizAPI = {
  generate: (data) => api.post('/quiz/generate', data),
  getQuiz: (sessionId) => api.get(`/quiz/${sessionId}`),
  startQuiz: (sessionId) => api.post(`/quiz/${sessionId}/start`),
  submitQuiz: (sessionId, data) => api.post(`/quiz/${sessionId}/submit`, data),
  getHistory: () => api.get('/quiz/history'), // Get user's quiz history
};

// Courses endpoints
export const coursesAPI = {
  list: () => api.get('/courses/'),
};

// Questions endpoints
export const questionsAPI = {
  list: (params) => api.get('/questions/', { params }),
};

// Admin endpoints
export const adminAPI = {
  // Questions
  listQuestions: (params) => api.get('/admin/questions', { params }),
  getQuestion: (id) => api.get(`/admin/questions/${id}`),
  createQuestion: (data) => api.post('/admin/questions', data),
  updateQuestion: (id, data) => api.put(`/admin/questions/${id}`, data),
  deleteQuestion: (id) => api.delete(`/admin/questions/${id}`),
  
  // Courses
  listCourses: () => api.get('/admin/courses'),
  createCourse: (data) => api.post('/admin/courses', data),
  updateCourse: (id, data) => api.put(`/admin/courses/${id}`, data),
  deleteCourse: (id) => api.delete(`/admin/courses/${id}`),
  
  // Logs & Stats
  getLogs: (params) => api.get('/admin/logs', { params }),
  getStats: () => api.get('/admin/stats'),
};

export default api;

