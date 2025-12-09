import React, { useState } from 'react';
import { useHistory } from 'react-router-dom';
import QuestionCard from './QuestionCard';

const QuizResults = ({ results }) => {
  const history = useHistory();
  const [currentIndex, setCurrentIndex] = useState(0);

  if (!results) {
    return <div>Loading results...</div>;
  }

  const percentage = ((results.correct_answers / results.total_questions) * 100).toFixed(1);
  const currentResult = results.results[currentIndex];

  // Format answer for display
  const formatAnswer = (answer) => {
    if (answer === null || answer === undefined || answer === '') return 'No answer provided';
    if (typeof answer === 'object') return JSON.stringify(answer);
    return String(answer);
  };

  return (
    <div className="space-y-6">
      {/* Score Summary */}
      <div className="bg-white rounded-card p-12 shadow-card max-w-4xl mx-auto text-center">
        <h2 className="text-3xl font-semibold mb-8 text-text-black">Quiz Results</h2>
        
        <div className="mb-8">
          <div className="text-7xl font-bold text-primary-blue mb-4">{percentage}%</div>
          <div className="text-2xl text-text-gray mb-2">
            {results.correct_answers} out of {results.total_questions} correct
          </div>
          <div className="text-lg text-text-gray">
            Score: {results.score.toFixed(2)}
          </div>
        </div>

        {/* Question Review */}
        <div className="mt-8">
          <div className="bg-white rounded-card p-8 shadow-card mb-6">
            <div className="mb-4">
              <span className="text-sm text-text-gray font-medium">
                Question {currentIndex + 1}
              </span>
              {currentResult.question_type && (
                <span className="ml-2 text-xs text-text-gray uppercase">
                  ({currentResult.question_type})
                </span>
              )}
            </div>
            <h3 className="text-2xl font-semibold mb-6 text-text-black">
              {currentResult.question_text}
            </h3>
            
            <div className="space-y-4 mb-6">
              <div className="p-4 border-2 border-border-gray rounded-lg">
                <span className="font-semibold text-text-black block mb-2">Your Answer: </span>
                <span className="text-text-gray text-lg">
                  {formatAnswer(currentResult.student_answer)}
                </span>
              </div>
              <div className={`p-4 border-2 rounded-lg ${
                currentResult.is_correct ? 'border-green-500 bg-soft-green' : 'border-red-500 bg-soft-red'
              }`}>
                <span className="font-semibold text-text-black block mb-2">Correct Answer: </span>
                <span className={`font-medium text-lg ${
                  currentResult.is_correct ? 'text-green-700' : 'text-red-700'
                }`}>
                  {formatAnswer(currentResult.correct_answer)}
                </span>
              </div>
            </div>

          {/* Result Details */}
          <div className={`mt-6 p-6 rounded-lg ${
            currentResult.is_correct ? 'bg-soft-green' : 'bg-soft-red'
          }`}>
            <div className="flex items-center justify-between mb-4">
              <span className={`font-semibold text-lg ${
                currentResult.is_correct ? 'text-green-700' : 'text-red-700'
              }`}>
                {currentResult.is_correct ? 'Correct' : 'Incorrect'}
              </span>
            </div>
            
            {currentResult.explanation && (
              <div className="mb-4">
                <p className="font-medium text-text-black mb-2">Explanation:</p>
                <p className="text-text-gray">{currentResult.explanation}</p>
              </div>
            )}
            
            {currentResult.feedback && (
              <div>
                <p className="font-medium text-text-black mb-2">Feedback:</p>
                <p className="text-text-gray">{currentResult.feedback}</p>
              </div>
            )}
          </div>

          {/* Navigation */}
          <div className="flex justify-between items-center mt-8">
            <button
              onClick={() => setCurrentIndex(Math.max(0, currentIndex - 1))}
              disabled={currentIndex === 0}
              className="px-6 py-3 border-2 border-border-gray rounded-lg hover:bg-gray-50 font-medium text-text-black disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Previous
            </button>
            
            <span className="text-text-gray">
              Question {currentIndex + 1} of {results.results.length}
            </span>
            
            <button
              onClick={() => setCurrentIndex(Math.min(results.results.length - 1, currentIndex + 1))}
              disabled={currentIndex === results.results.length - 1}
              className="px-6 py-3 border-2 border-border-gray rounded-lg hover:bg-gray-50 font-medium text-text-black disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Next
            </button>
          </div>
          </div>
        </div>

        {/* Return Button */}
        <div className="mt-8 pt-8 border-t border-border-gray">
          <button
            onClick={() => history.push('/dashboard')}
            className="bg-primary-blue text-white px-8 py-3 rounded-lg hover:bg-blue-700 font-medium"
          >
            Return to Dashboard
          </button>
        </div>
      </div>
    </div>
  );
};

export default QuizResults;

