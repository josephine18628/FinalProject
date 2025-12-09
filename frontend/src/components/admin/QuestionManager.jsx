import React, { useState, useEffect } from 'react';
import { adminAPI, coursesAPI } from '../../services/api';

const QuestionManager = () => {
  const [questions, setQuestions] = useState([]);
  const [courses, setCourses] = useState([]);
  const [loading, setLoading] = useState(false);
  const [filters, setFilters] = useState({
    course_id: '',
    difficulty: '',
    question_type: '',
    is_ai_generated: '',
    search: '',
  });
  const [showModal, setShowModal] = useState(false);
  const [editingQuestion, setEditingQuestion] = useState(null);
  const [formData, setFormData] = useState({
    course_id: '',
    type: 'mcq',
    difficulty: 'beginner',
    question_text: '',
    correct_answer: '',
    explanation: '',
    options: [],
  });

  useEffect(() => {
    loadQuestions();
    loadCourses();
  }, [filters]);

  const loadCourses = async () => {
    try {
      const response = await coursesAPI.list();
      setCourses(response.data);
    } catch (error) {
      console.error('Failed to load courses', error);
    }
  };

  const loadQuestions = async () => {
    setLoading(true);
    try {
      const params = {};
      if (filters.course_id) params.course_id = filters.course_id;
      if (filters.difficulty) params.difficulty = filters.difficulty;
      if (filters.question_type) params.question_type = filters.question_type;
      if (filters.is_ai_generated !== '') params.is_ai_generated = filters.is_ai_generated === 'true';
      if (filters.search) params.search = filters.search;

      const response = await adminAPI.listQuestions(params);
      setQuestions(response.data);
    } catch (error) {
      console.error('Failed to load questions', error);
    } finally {
      setLoading(false);
    }
  };

  const handleDelete = async (id) => {
    if (!window.confirm('Are you sure you want to delete this question?')) return;

    try {
      await adminAPI.deleteQuestion(id);
      loadQuestions();
    } catch (error) {
      alert('Failed to delete question');
    }
  };

  const handleEdit = (question) => {
    setEditingQuestion(question);
    setFormData({
      course_id: question.course_id,
      type: question.type,
      difficulty: question.difficulty,
      question_text: question.question_text,
      correct_answer: typeof question.correct_answer === 'object' 
        ? question.correct_answer.answer 
        : question.correct_answer,
      explanation: question.explanation || '',
      options: question.options || [],
    });
    setShowModal(true);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      const payload = {
        course_id: formData.course_id,
        type: formData.type,
        difficulty: formData.difficulty,
        question_text: formData.question_text,
        correct_answer: { answer: formData.correct_answer },
        explanation: formData.explanation,
        options: formData.options,
      };

      if (editingQuestion) {
        await adminAPI.updateQuestion(editingQuestion.id, payload);
      } else {
        await adminAPI.createQuestion(payload);
      }

      setShowModal(false);
      setEditingQuestion(null);
      loadQuestions();
    } catch (error) {
      alert('Failed to save question');
    }
  };

  return (
    <div className="bg-white p-8 rounded-card shadow-card">
      <div className="flex justify-between items-center mb-6">
        <h2 className="text-2xl font-semibold text-text-black">Question Management</h2>
        <button
          onClick={() => {
            setEditingQuestion(null);
            setFormData({
              course_id: '',
              type: 'mcq',
              difficulty: 'beginner',
              question_text: '',
              correct_answer: '',
              explanation: '',
              options: [],
            });
            setShowModal(true);
          }}
          className="bg-primary-blue text-white px-6 py-3 rounded-lg hover:bg-blue-700 font-medium"
        >
          Add New Question
        </button>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-5 gap-4 mb-4">
        <input
          type="text"
          placeholder="Search..."
          value={filters.search}
          onChange={(e) => setFilters({ ...filters, search: e.target.value })}
          className="px-3 py-2 border rounded"
        />
        <select
          value={filters.course_id}
          onChange={(e) => setFilters({ ...filters, course_id: e.target.value })}
          className="px-3 py-2 border rounded"
        >
          <option value="">All Courses</option>
          {courses.map((c) => (
            <option key={c.id} value={c.id}>
              {c.code}
            </option>
          ))}
        </select>
        <select
          value={filters.difficulty}
          onChange={(e) => setFilters({ ...filters, difficulty: e.target.value })}
          className="px-3 py-2 border rounded"
        >
          <option value="">All Difficulties</option>
          <option value="beginner">Beginner</option>
          <option value="intermediate">Intermediate</option>
          <option value="advanced">Advanced</option>
        </select>
        <select
          value={filters.question_type}
          onChange={(e) => setFilters({ ...filters, question_type: e.target.value })}
          className="px-3 py-2 border rounded"
        >
          <option value="">All Types</option>
          <option value="mcq">MCQ</option>
          <option value="tf">True/False</option>
          <option value="essay">Essay</option>
          <option value="calculation">Calculation</option>
        </select>
        <select
          value={filters.is_ai_generated}
          onChange={(e) => setFilters({ ...filters, is_ai_generated: e.target.value })}
          className="px-3 py-2 border rounded"
        >
          <option value="">All Sources</option>
          <option value="true">AI Generated</option>
          <option value="false">Manual</option>
        </select>
      </div>

      {loading ? (
        <div>Loading...</div>
      ) : (
        <div className="overflow-x-auto">
          <table className="min-w-full table-auto">
            <thead>
              <tr className="bg-gray-200">
                <th className="px-4 py-2">ID</th>
                <th className="px-4 py-2">Question</th>
                <th className="px-4 py-2">Type</th>
                <th className="px-4 py-2">Difficulty</th>
                <th className="px-4 py-2">Source</th>
                <th className="px-4 py-2">Actions</th>
              </tr>
            </thead>
            <tbody>
              {questions.map((q) => (
                <tr key={q.id} className="border-b">
                  <td className="px-4 py-2 text-sm">{q.id.substring(0, 8)}...</td>
                  <td className="px-4 py-2">{q.question_text.substring(0, 50)}...</td>
                  <td className="px-4 py-2">{q.type}</td>
                  <td className="px-4 py-2 capitalize">{q.difficulty}</td>
                  <td className="px-4 py-2">
                    {q.is_ai_generated ? 'AI' : 'Manual'}
                  </td>
                  <td className="px-4 py-2">
                    <button
                      onClick={() => handleEdit(q)}
                      className="text-blue-600 hover:underline mr-2"
                    >
                      Edit
                    </button>
                    <button
                      onClick={() => handleDelete(q.id)}
                      className="text-red-600 hover:underline"
                    >
                      Delete
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      {showModal && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
          <div className="bg-white p-6 rounded-lg max-w-2xl w-full max-h-screen overflow-y-auto">
            <h3 className="text-xl font-bold mb-4">
              {editingQuestion ? 'Edit Question' : 'New Question'}
            </h3>
            <form onSubmit={handleSubmit}>
              <div className="space-y-4">
                <div>
                  <label className="block font-semibold mb-1">Course</label>
                  <select
                    value={formData.course_id}
                    onChange={(e) => setFormData({ ...formData, course_id: e.target.value })}
                    required
                    className="w-full px-3 py-2 border rounded"
                  >
                    <option value="">Select Course</option>
                    {courses.map((c) => (
                      <option key={c.id} value={c.id}>
                        {c.code} - {c.name}
                      </option>
                    ))}
                  </select>
                </div>
                <div>
                  <label className="block font-semibold mb-1">Question Text</label>
                  <textarea
                    value={formData.question_text}
                    onChange={(e) => setFormData({ ...formData, question_text: e.target.value })}
                    required
                    rows={3}
                    className="w-full px-3 py-2 border rounded"
                  />
                </div>
                <div>
                  <label className="block font-semibold mb-1">Correct Answer</label>
                  <input
                    type="text"
                    value={formData.correct_answer}
                    onChange={(e) => setFormData({ ...formData, correct_answer: e.target.value })}
                    required
                    className="w-full px-3 py-2 border rounded"
                  />
                </div>
                <div>
                  <label className="block font-semibold mb-1">Explanation</label>
                  <textarea
                    value={formData.explanation}
                    onChange={(e) => setFormData({ ...formData, explanation: e.target.value })}
                    rows={2}
                    className="w-full px-3 py-2 border rounded"
                  />
                </div>
              </div>
              <div className="mt-4 flex justify-end space-x-2">
                <button
                  type="button"
                  onClick={() => setShowModal(false)}
                  className="px-4 py-2 border rounded"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
                >
                  Save
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};

export default QuestionManager;

