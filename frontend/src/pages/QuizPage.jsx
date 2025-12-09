import React, { useState, useEffect } from 'react';
import { useParams, useHistory } from 'react-router-dom';
import { quizAPI } from '../services/api';
import Layout from '../components/common/Layout';
import QuizTimer from '../components/quiz/QuizTimer';
import QuestionCard from '../components/quiz/QuestionCard';
import QuizNavigation from '../components/quiz/QuizNavigation';
import QuizResults from '../components/quiz/QuizResults';

const QuizPage = () => {
  const { sessionId } = useParams();
  const history = useHistory();
  const [quiz, setQuiz] = useState(null);
  const [answers, setAnswers] = useState({});
  const [results, setResults] = useState(null);
  const [loading, setLoading] = useState(true);
  const [submitting, setSubmitting] = useState(false);
  const [started, setStarted] = useState(false);
  const [currentQuestionIndex, setCurrentQuestionIndex] = useState(0);
  const [error, setError] = useState('');

  useEffect(() => {
    loadQuiz();
  }, [sessionId]);

  const loadQuiz = async () => {
    try {
      const response = await quizAPI.getQuiz(sessionId);
      setQuiz(response.data);
      setLoading(false);
    } catch (error) {
      setError('Failed to load quiz');
      setLoading(false);
    }
  };

  const handleStart = async () => {
    try {
      await quizAPI.startQuiz(sessionId);
      setStarted(true);
    } catch (error) {
      setError('Failed to start quiz');
    }
  };

  const handleAnswerChange = (questionId, answer) => {
    setAnswers({
      ...answers,
      [questionId]: answer,
    });
  };

  const handleNext = () => {
    if (currentQuestionIndex < quiz.questions.length - 1) {
      setCurrentQuestionIndex(currentQuestionIndex + 1);
    }
  };

  const handlePrevious = () => {
    if (currentQuestionIndex > 0) {
      setCurrentQuestionIndex(currentQuestionIndex - 1);
    }
  };

  const handleTimeUp = () => {
    handleSubmit(true);
  };

  const handleSaveExit = () => {
    history.push('/dashboard');
  };

  const handleSubmit = async (isAutoSubmit = false) => {
    if (!isAutoSubmit && Object.keys(answers).length < quiz.questions.length) {
      const confirmed = window.confirm(
        'You have not answered all questions. Are you sure you want to submit?'
      );
      if (!confirmed) return;
    }

    setSubmitting(true);
    setError('');

    try {
      const submitData = {
        answers: quiz.questions.map((q) => ({
          question_id: q.id,
          answer: answers[q.id] || null,
        })),
      };

      const response = await quizAPI.submitQuiz(sessionId, submitData);
      setResults(response.data);
    } catch (error) {
      setError(error.response?.data?.detail || 'Failed to submit quiz');
      setSubmitting(false);
    }
  };

  if (loading) {
    return (
      <Layout>
        <div className="flex items-center justify-center min-h-screen">
          <div className="text-lg text-text-gray">Loading quiz...</div>
        </div>
      </Layout>
    );
  }

  if (error && !quiz) {
    return (
      <Layout>
        <div className="bg-soft-red border border-red-300 text-red-700 px-6 py-4 rounded-lg">
          {error}
        </div>
      </Layout>
    );
  }

  if (results) {
    return (
      <Layout>
        <QuizResults results={results} />
      </Layout>
    );
  }

  if (!started) {
    return (
      <Layout>
        <div className="bg-white rounded-card p-12 shadow-card max-w-2xl mx-auto text-center">
          <h2 className="text-3xl font-semibold mb-6 text-text-black">Ready to Start?</h2>
          <p className="text-lg text-text-gray mb-8">
            This quiz has {quiz.questions.length} questions and will take approximately{' '}
            {quiz.duration_minutes} minutes.
          </p>
          <button
            onClick={handleStart}
            className="bg-primary-blue text-white px-8 py-3 rounded-lg hover:bg-blue-700 font-medium"
          >
            Start Quiz
          </button>
        </div>
      </Layout>
    );
  }

  const currentQuestion = quiz.questions[currentQuestionIndex];
  const hasNext = currentQuestionIndex < quiz.questions.length - 1;
  const hasPrevious = currentQuestionIndex > 0;

  return (
    <Layout>
      {error && (
        <div className="bg-soft-red border border-red-300 text-red-700 px-6 py-4 rounded-lg mb-6 max-w-4xl mx-auto">
          {error}
        </div>
      )}

      {/* Timer */}
      <div className="mb-6 max-w-4xl mx-auto">
        <QuizTimer
          durationMinutes={quiz.duration_minutes}
          onTimeUp={handleTimeUp}
        />
      </div>

      {/* Current Question */}
      <QuestionCard
        question={currentQuestion}
        answer={answers[currentQuestion.id]}
        onAnswerChange={handleAnswerChange}
        questionNumber={currentQuestionIndex + 1}
      />

      {/* Navigation */}
      <QuizNavigation
        onSaveExit={handleSaveExit}
        onNext={hasNext ? handleNext : () => handleSubmit(false)}
        onPrevious={handlePrevious}
        hasNext={hasNext || true} // Always show submit if last question
        hasPrevious={hasPrevious}
        isSubmitting={submitting}
      />
    </Layout>
  );
};

export default QuizPage;

