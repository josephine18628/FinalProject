import React, { useState, useEffect } from 'react';
import { coursesAPI, quizAPI } from '../../services/api';
import { useHistory } from 'react-router-dom';

const QuizBuilder = ({ onQuizCreated }) => {
  const [courses, setCourses] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [formData, setFormData] = useState({
    course_id: '',
    format: 'mcq',
    difficulty: 'beginner',
    question_count: 10,
    mixed_config: {},
  });
  const navigate = useNavigate();

  useEffect(() => {
    loadCourses();
  }, []);

  const loadCourses = async () => {
    try {
      const response = await coursesAPI.list();
      setCourses(response.data);
    } catch (error) {
      setError('Failed to load courses');
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setLoading(true);

    try {
      const payload = {
        course_id: formData.course_id,
        format: formData.format,
        difficulty: formData.difficulty,
        question_count: parseInt(formData.question_count),
      };

      if (formData.format === 'mixed') {
        payload.mixed_config = formData.mixed_config;
      }

      const response = await quizAPI.generate(payload);
      const sessionId = response.data.session_id;
      
      if (onQuizCreated) {
        onQuizCreated(sessionId);
      } else {
        history.push(`/quiz/${sessionId}`);
      }
    } catch (error) {
      setError(error.response?.data?.detail || 'Failed to generate quiz');
    } finally {
      setLoading(false);
    }
  };

  const handleMixedConfigChange = (type, value) => {
    setFormData({
      ...formData,
      mixed_config: {
        ...formData.mixed_config,
        [type]: parseInt(value) || 0,
      },
    });
  };

  return (
    <div className="bg-white p-6 rounded-lg shadow-md">
      <h2 className="text-2xl font-bold mb-4">Create New Quiz</h2>

      {error && (
        <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
          {error}
        </div>
      )}

      <form onSubmit={handleSubmit}>
        <div className="mb-4">
          <label className="block text-gray-700 font-bold mb-2">
            Course *
          </label>
          <select
            value={formData.course_id}
            onChange={(e) => setFormData({ ...formData, course_id: e.target.value })}
            required
            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Select a course</option>
            {courses.map((course) => (
              <option key={course.id} value={course.id}>
                {course.code} - {course.name}
              </option>
            ))}
          </select>
        </div>

        <div className="mb-4">
          <label className="block text-gray-700 font-bold mb-2">
            Question Format *
          </label>
          <div className="space-y-2">
            {['mcq', 'tf', 'essay', 'calculation', 'mixed'].map((format) => (
              <label key={format} className="flex items-center">
                <input
                  type="radio"
                  name="format"
                  value={format}
                  checked={formData.format === format}
                  onChange={(e) => setFormData({ ...formData, format: e.target.value })}
                  className="mr-2"
                />
                <span className="capitalize">{format === 'tf' ? 'True/False' : format}</span>
              </label>
            ))}
          </div>
        </div>

        {formData.format === 'mixed' && (
          <div className="mb-4 p-4 bg-gray-50 rounded">
            <label className="block text-gray-700 font-bold mb-2">
              Mixed Format Distribution
            </label>
            {['mcq', 'tf', 'essay', 'calculation'].map((type) => (
              <div key={type} className="mb-2">
                <label className="block text-sm text-gray-600 capitalize">
                  {type === 'tf' ? 'True/False' : type}:
                </label>
                <input
                  type="number"
                  min="0"
                  value={formData.mixed_config[type] || 0}
                  onChange={(e) => handleMixedConfigChange(type, e.target.value)}
                  className="w-full px-3 py-2 border border-gray-300 rounded-md"
                />
              </div>
            ))}
          </div>
        )}

        <div className="mb-4">
          <label className="block text-gray-700 font-bold mb-2">
            Difficulty *
          </label>
          <div className="space-y-2">
            {['beginner', 'intermediate', 'advanced'].map((difficulty) => (
              <label key={difficulty} className="flex items-center">
                <input
                  type="radio"
                  name="difficulty"
                  value={difficulty}
                  checked={formData.difficulty === difficulty}
                  onChange={(e) => setFormData({ ...formData, difficulty: e.target.value })}
                  className="mr-2"
                />
                <span className="capitalize">{difficulty}</span>
              </label>
            ))}
          </div>
        </div>

        <div className="mb-4">
          <label className="block text-gray-700 font-bold mb-2">
            Number of Questions *
          </label>
          <input
            type="number"
            min="1"
            max="50"
            value={formData.question_count}
            onChange={(e) => setFormData({ ...formData, question_count: e.target.value })}
            required
            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <button
          type="submit"
          disabled={loading}
          className="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50"
        >
          {loading ? 'Generating Quiz...' : 'Generate Quiz'}
        </button>
      </form>
    </div>
  );
};

export default QuizBuilder;

