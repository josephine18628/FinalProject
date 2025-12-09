import React, { useState, useEffect } from 'react';
import { coursesAPI, quizAPI } from '../../services/api';
import { useHistory } from 'react-router-dom';

const QuizBuilderCard = () => {
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
  const history = useHistory();

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
      history.push(`/quiz/${sessionId}`);
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
    <div className="bg-white rounded-card p-12 shadow-card max-w-4xl mx-auto">
      <h2 className="text-3xl font-semibold mb-8 text-center text-text-black">Create New Quiz</h2>

      {error && (
        <div className="bg-soft-red border border-red-300 text-red-700 px-4 py-3 rounded-lg mb-6">
          {error}
        </div>
      )}

      <form onSubmit={handleSubmit} className="space-y-6">
        <div>
          <label className="block text-sm font-medium text-text-gray mb-2">
            Course
          </label>
          <select
            value={formData.course_id}
            onChange={(e) => setFormData({ ...formData, course_id: e.target.value })}
            required
            className="w-full px-4 py-3 border border-border-gray rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-blue text-text-black"
          >
            <option value="">Select a course</option>
            {courses.map((course) => (
              <option key={course.id} value={course.id}>
                {course.code} - {course.name}
              </option>
            ))}
          </select>
        </div>

        <div>
          <label className="block text-sm font-medium text-text-gray mb-3">
            Question Format
          </label>
          <div className="grid grid-cols-2 md:grid-cols-5 gap-3">
            {['mcq', 'tf', 'essay', 'calculation', 'mixed'].map((format) => (
              <label
                key={format}
                className={`flex items-center justify-center p-4 border-2 rounded-lg cursor-pointer transition-colors ${
                  formData.format === format
                    ? 'border-primary-blue bg-blue-50 text-primary-blue'
                    : 'border-border-gray hover:border-primary-blue'
                }`}
              >
                <input
                  type="radio"
                  name="format"
                  value={format}
                  checked={formData.format === format}
                  onChange={(e) => setFormData({ ...formData, format: e.target.value })}
                  className="sr-only"
                />
                <span className="font-medium capitalize text-center">
                  {format === 'tf' ? 'True/False' : format}
                </span>
              </label>
            ))}
          </div>
        </div>

        {formData.format === 'mixed' && (
          <div className="p-6 bg-gray-50 rounded-lg border border-border-gray">
            <label className="block text-sm font-medium text-text-gray mb-4">
              Mixed Format Distribution
            </label>
            <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
              {['mcq', 'tf', 'essay', 'calculation'].map((type) => (
                <div key={type}>
                  <label className="block text-sm text-text-gray mb-2 capitalize">
                    {type === 'tf' ? 'True/False' : type}
                  </label>
                  <input
                    type="number"
                    min="0"
                    value={formData.mixed_config[type] || 0}
                    onChange={(e) => handleMixedConfigChange(type, e.target.value)}
                    className="w-full px-3 py-2 border border-border-gray rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-blue"
                  />
                </div>
              ))}
            </div>
          </div>
        )}

        <div>
          <label className="block text-sm font-medium text-text-gray mb-3">
            Difficulty Level
          </label>
          <div className="grid grid-cols-3 gap-3">
            {['beginner', 'intermediate', 'advanced'].map((difficulty) => (
              <label
                key={difficulty}
                className={`flex items-center justify-center p-4 border-2 rounded-lg cursor-pointer transition-colors ${
                  formData.difficulty === difficulty
                    ? 'border-primary-blue bg-blue-50 text-primary-blue'
                    : 'border-border-gray hover:border-primary-blue'
                }`}
              >
                <input
                  type="radio"
                  name="difficulty"
                  value={difficulty}
                  checked={formData.difficulty === difficulty}
                  onChange={(e) => setFormData({ ...formData, difficulty: e.target.value })}
                  className="sr-only"
                />
                <span className="font-medium capitalize">{difficulty}</span>
              </label>
            ))}
          </div>
        </div>

        <div>
          <label className="block text-sm font-medium text-text-gray mb-2">
            Number of Questions
          </label>
          <input
            type="number"
            min="1"
            max="50"
            value={formData.question_count}
            onChange={(e) => setFormData({ ...formData, question_count: e.target.value })}
            required
            className="w-full px-4 py-3 border border-border-gray rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-blue text-text-black"
          />
        </div>

        <div className="flex justify-end pt-4">
          <button
            type="submit"
            disabled={loading}
            className="bg-primary-blue text-white px-8 py-3 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-primary-blue disabled:opacity-50 font-medium flex items-center gap-2"
          >
            {loading ? 'Generating...' : 'Generate Quiz'}
            {!loading && (
              <svg
                className="w-5 h-5"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  strokeWidth={2}
                  d="M9 5l7 7-7 7"
                />
              </svg>
            )}
          </button>
        </div>
      </form>
    </div>
  );
};

export default QuizBuilderCard;

