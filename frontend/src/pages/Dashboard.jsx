import React, { useState, useEffect } from 'react';
import { useHistory } from 'react-router-dom';
import Layout from '../components/common/Layout';
import QuizBuilderCard from '../components/quiz/QuizBuilderCard';
import { quizAPI } from '../services/api';

const Dashboard = () => {
  const [showBuilder, setShowBuilder] = useState(true);
  const [quizHistory, setQuizHistory] = useState([]);
  const [loadingHistory, setLoadingHistory] = useState(false);
  const history = useHistory();

  useEffect(() => {
    if (!showBuilder) {
      loadQuizHistory();
    }
  }, [showBuilder]);

  const loadQuizHistory = async () => {
    setLoadingHistory(true);
    try {
      const response = await quizAPI.getHistory();
      setQuizHistory(response.data.history || []);
    } catch (error) {
      console.error('Failed to load quiz history', error);
    } finally {
      setLoadingHistory(false);
    }
  };

  return (
    <Layout>
      {!showBuilder ? (
        <div className="space-y-6">
          <div className="flex justify-between items-center">
            <h1 className="text-3xl font-semibold text-text-black">My Quizzes</h1>
            <button
              onClick={() => setShowBuilder(true)}
              className="bg-primary-blue text-white px-6 py-3 rounded-lg hover:bg-blue-700 font-medium flex items-center gap-2"
            >
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
                  d="M12 4v16m8-8H4"
                />
              </svg>
              Create New Quiz
            </button>
          </div>

          <div className="bg-white rounded-card p-8 shadow-card">
            <h2 className="text-xl font-semibold mb-6 text-text-black">Past Quiz Results</h2>
            {loadingHistory ? (
              <p className="text-text-gray">Loading quiz history...</p>
            ) : quizHistory.length === 0 ? (
              <p className="text-text-gray">No quiz history available. Create your first quiz!</p>
            ) : (
              <div className="space-y-4">
                {quizHistory.map((quiz) => (
                  <div
                    key={quiz.session_id}
                    className="border border-border-gray rounded-lg p-6 hover:shadow-md transition-shadow"
                  >
                    <div className="flex justify-between items-start">
                      <div>
                        <h3 className="text-lg font-semibold text-text-black mb-2">
                          {quiz.course_name}
                        </h3>
                        <div className="space-y-1 text-sm text-text-gray">
                          <p>Questions: {quiz.question_count}</p>
                          <p>Duration: {quiz.duration_minutes} minutes</p>
                          {quiz.completed_at && (
                            <p>Completed: {new Date(quiz.completed_at).toLocaleString()}</p>
                          )}
                        </div>
                      </div>
                      <div className="text-right">
                        {quiz.score !== null && (
                          <div className="text-2xl font-bold text-primary-blue mb-2">
                            {quiz.score.toFixed(1)}%
                          </div>
                        )}
                        <span
                          className={`px-3 py-1 rounded-full text-xs font-medium ${
                            quiz.status === 'completed'
                              ? 'bg-green-100 text-green-700'
                              : quiz.status === 'in_progress'
                              ? 'bg-blue-100 text-blue-700'
                              : 'bg-gray-100 text-gray-700'
                          }`}
                        >
                          {quiz.status}
                        </span>
                      </div>
                    </div>
                    {quiz.status === 'completed' && (
                      <button
                        onClick={() => history.push(`/quiz/${quiz.session_id}/results`)}
                        className="mt-4 text-primary-blue hover:underline text-sm font-medium"
                      >
                        View Results →
                      </button>
                    )}
                  </div>
                ))}
              </div>
            )}
          </div>
        </div>
      ) : (
        <div>
          <button
            onClick={() => setShowBuilder(false)}
            className="mb-6 text-text-gray hover:text-text-black font-medium flex items-center gap-2"
          >
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
                d="M15 19l-7-7 7-7"
              />
            </svg>
            Back to Dashboard
          </button>
          <QuizBuilderCard />
        </div>
      )}
    </Layout>
  );
};

export default Dashboard;

