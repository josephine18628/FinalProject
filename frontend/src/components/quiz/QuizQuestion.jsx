import React, { useState } from 'react';

const QuizQuestion = ({ question, answer, onAnswerChange, questionNumber }) => {
  const handleAnswerChange = (value) => {
    onAnswerChange(question.id, value);
  };

  return (
    <div className="bg-white p-6 rounded-lg shadow-md mb-4">
      <div className="mb-4">
        <span className="text-sm font-semibold text-gray-500">
          Question {questionNumber}
        </span>
        <span className="ml-2 text-sm text-gray-500">
          ({question.type.toUpperCase()} - {question.difficulty})
        </span>
      </div>

      <h3 className="text-lg font-bold mb-4">{question.question_text}</h3>

      {question.type === 'mcq' && (
        <div className="space-y-2">
          {question.options.map((option) => (
            <label
              key={option.id}
              className={`flex items-center p-3 border-2 rounded cursor-pointer ${
                answer === option.option_letter
                  ? 'border-blue-500 bg-blue-50'
                  : 'border-gray-200 hover:border-gray-300'
              }`}
            >
              <input
                type="radio"
                name={`question-${question.id}`}
                value={option.option_letter}
                checked={answer === option.option_letter}
                onChange={(e) => handleAnswerChange(e.target.value)}
                className="mr-3"
              />
              <span className="font-semibold mr-2">{option.option_letter}.</span>
              <span>{option.option_text}</span>
            </label>
          ))}
        </div>
      )}

      {question.type === 'tf' && (
        <div className="space-y-2">
          {['True', 'False'].map((value) => (
            <label
              key={value}
              className={`flex items-center p-3 border-2 rounded cursor-pointer ${
                answer === value
                  ? 'border-blue-500 bg-blue-50'
                  : 'border-gray-200 hover:border-gray-300'
              }`}
            >
              <input
                type="radio"
                name={`question-${question.id}`}
                value={value}
                checked={answer === value}
                onChange={(e) => handleAnswerChange(e.target.value)}
                className="mr-3"
              />
              <span>{value}</span>
            </label>
          ))}
        </div>
      )}

      {question.type === 'essay' && (
        <textarea
          value={answer || ''}
          onChange={(e) => handleAnswerChange(e.target.value)}
          rows={6}
          className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          placeholder="Type your answer here..."
        />
      )}

      {question.type === 'calculation' && (
        <input
          type="number"
          step="any"
          value={answer || ''}
          onChange={(e) => handleAnswerChange(e.target.value)}
          className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          placeholder="Enter your numerical answer"
        />
      )}
    </div>
  );
};

export default QuizQuestion;

