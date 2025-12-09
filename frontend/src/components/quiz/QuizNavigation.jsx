import React from 'react';

const QuizNavigation = ({ onSaveExit, onNext, onPrevious, hasNext, hasPrevious, isSubmitting }) => {
  return (
    <div className="flex justify-between items-center mt-8 max-w-4xl mx-auto">
      <button
        onClick={onSaveExit}
        className="text-text-gray hover:text-text-black font-medium transition-colors"
      >
        Save & Exit
      </button>
      
      <div className="flex items-center gap-4">
        {hasPrevious && (
          <button
            onClick={onPrevious}
            className="px-6 py-3 border-2 border-border-gray rounded-lg hover:bg-gray-50 font-medium text-text-black transition-colors"
          >
            Previous
          </button>
        )}
        {hasNext && (
          <button
            onClick={onNext}
            disabled={isSubmitting}
            className="bg-primary-blue text-white px-6 py-3 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-primary-blue disabled:opacity-50 font-medium flex items-center gap-2"
          >
            Add next question
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
          </button>
        )}
      </div>
    </div>
  );
};

export default QuizNavigation;

