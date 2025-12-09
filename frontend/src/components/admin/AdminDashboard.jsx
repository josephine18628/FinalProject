import React, { useState, useEffect } from 'react';
import { adminAPI } from '../../services/api';
import QuestionManager from './QuestionManager';
import CourseManager from './CourseManager';

const AdminDashboard = () => {
  const [stats, setStats] = useState(null);
  const [logs, setLogs] = useState([]);
  const [activeTab, setActiveTab] = useState('stats');

  useEffect(() => {
    loadStats();
    if (activeTab === 'logs') {
      loadLogs();
    }
  }, [activeTab]);

  const loadStats = async () => {
    try {
      const response = await adminAPI.getStats();
      setStats(response.data);
    } catch (error) {
      console.error('Failed to load stats', error);
    }
  };

  const loadLogs = async () => {
    try {
      const response = await adminAPI.getLogs({ limit: 50 });
      setLogs(response.data.logs);
    } catch (error) {
      console.error('Failed to load logs', error);
    }
  };

  return (
    <div>
      <h1 className="text-3xl font-semibold mb-8 text-text-black">Admin Dashboard</h1>

      <div className="flex space-x-4 mb-8 border-b border-border-gray">
        <button
          onClick={() => setActiveTab('stats')}
          className={`px-6 py-3 font-medium transition-colors ${
            activeTab === 'stats'
              ? 'border-b-2 border-primary-blue text-primary-blue'
              : 'text-text-gray hover:text-text-black'
          }`}
        >
          Statistics
        </button>
        <button
          onClick={() => setActiveTab('questions')}
          className={`px-6 py-3 font-medium transition-colors ${
            activeTab === 'questions'
              ? 'border-b-2 border-primary-blue text-primary-blue'
              : 'text-text-gray hover:text-text-black'
          }`}
        >
          Questions
        </button>
        <button
          onClick={() => setActiveTab('courses')}
          className={`px-6 py-3 font-medium transition-colors ${
            activeTab === 'courses'
              ? 'border-b-2 border-primary-blue text-primary-blue'
              : 'text-text-gray hover:text-text-black'
          }`}
        >
          Courses
        </button>
        <button
          onClick={() => setActiveTab('logs')}
          className={`px-6 py-3 font-medium transition-colors ${
            activeTab === 'logs'
              ? 'border-b-2 border-primary-blue text-primary-blue'
              : 'text-text-gray hover:text-text-black'
          }`}
        >
          AI Logs
        </button>
      </div>

      {activeTab === 'stats' && stats && (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
          <div className="bg-white p-8 rounded-card shadow-card">
            <h3 className="text-base font-medium text-text-gray mb-2">Total Questions</h3>
            <p className="text-4xl font-bold text-primary-blue mb-2">{stats.total_questions}</p>
            <p className="text-sm text-text-gray">
              {stats.ai_questions} AI-generated, {stats.manual_questions} manual
            </p>
          </div>
          <div className="bg-white p-8 rounded-card shadow-card">
            <h3 className="text-base font-medium text-text-gray mb-2">Total Courses</h3>
            <p className="text-4xl font-bold text-green-600">{stats.total_courses}</p>
          </div>
          <div className="bg-white p-8 rounded-card shadow-card">
            <h3 className="text-base font-medium text-text-gray mb-2">Total Quizzes</h3>
            <p className="text-4xl font-bold text-purple-600">{stats.total_quizzes}</p>
          </div>
          <div className="bg-white p-8 rounded-card shadow-card">
            <h3 className="text-base font-medium text-text-gray mb-2">Total Users</h3>
            <p className="text-4xl font-bold text-orange-600">{stats.total_users}</p>
          </div>
        </div>
      )}

      {activeTab === 'questions' && <QuestionManager />}
      {activeTab === 'courses' && <CourseManager />}

      {activeTab === 'logs' && (
        <div className="bg-white p-8 rounded-card shadow-card">
          <h2 className="text-2xl font-semibold mb-6 text-text-black">AI Generation Logs</h2>
          <div className="overflow-x-auto">
            <table className="min-w-full table-auto">
              <thead>
                <tr className="bg-gray-200">
                  <th className="px-4 py-2">Date</th>
                  <th className="px-4 py-2">Course</th>
                  <th className="px-4 py-2">Generated</th>
                  <th className="px-4 py-2">Stored</th>
                  <th className="px-4 py-2">Duplicates</th>
                </tr>
              </thead>
              <tbody>
                {logs.map((log) => (
                  <tr key={log.id} className="border-b">
                    <td className="px-4 py-2">
                      {new Date(log.created_at).toLocaleString()}
                    </td>
                    <td className="px-4 py-2">{log.course_id.substring(0, 8)}...</td>
                    <td className="px-4 py-2">{log.questions_generated}</td>
                    <td className="px-4 py-2">{log.questions_stored}</td>
                    <td className="px-4 py-2">{log.duplicates_found}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      )}
    </div>
  );
};

export default AdminDashboard;

