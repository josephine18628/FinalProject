import React, { useState } from 'react';

const QuestionCard = ({ question, answer, onAnswerChange, questionNumber, isReadOnly = false }) => {
  const handleAnswerChange = (value) => {
    if (!isReadOnly) {
      onAnswerChange(question.id, value);
    }
  };

  const getOptionBackground = (optionLetter, isSelected) => {
    if (isReadOnly && question.correct_answer === optionLetter) {
      return 'bg-soft-green';
    }
    if (isSelected) {
      return 'bg-soft-green border-2 border-green-400';
    }
    return 'bg-soft-red';
  };

  return (
    <div className="bg-white rounded-card p-12 shadow-card max-w-4xl mx-auto">
      {/* Question Header */}
      <div className="flex justify-between items-center mb-8">
        <span className="text-sm text-text-gray font-medium">
          Question {questionNumber}
        </span>
        <div className="flex items-center gap-3">
          <span className="text-xs text-text-gray uppercase">
            {question.type === 'tf' ? 'True/False' : question.type} • {question.difficulty}
          </span>
        </div>
      </div>

      {/* Question Type Selector (Centered) */}
      <div className="flex justify-center mb-8">
        <div className="px-4 py-2 bg-gray-50 rounded-lg border border-border-gray">
          <span className="text-sm font-medium text-text-black uppercase">
            {question.type === 'mcq' ? 'Multiple Choice' : question.type === 'tf' ? 'True/False' : question.type}
          </span>
        </div>
      </div>

      {/* Question Text */}
      <div className="text-center mb-10">
        <h3 className="text-2xl font-semibold text-text-black leading-relaxed">
          {question.question_text}
        </h3>
      </div>

      {/* Answer Options */}
      {question.type === 'mcq' && (
        <div className="space-y-5 mb-8">
          {question.options.map((option, index) => {
            const optionLetter = option.option_letter || ['A', 'B', 'C', 'D'][index];
            const isSelected = answer === optionLetter;
            
            return (
              <div key={option.id || index} className="space-y-3">
                <div
                  className={`${getOptionBackground(optionLetter, isSelected)} p-5 rounded-lg flex items-center gap-4 transition-all ${
                    !isReadOnly && 'cursor-pointer hover:shadow-md'
                  }`}
                  onClick={() => !isReadOnly && handleAnswerChange(optionLetter)}
                >
                  {/* Letter Badge */}
                  <div
                    className={`min-w-[40px] h-10 rounded-lg flex items-center justify-center font-bold text-white ${
                      isSelected || (isReadOnly && question.correct_answer === optionLetter)
                        ? 'bg-green-600'
                        : 'bg-red-500'
                    }`}
                  >
                    {optionLetter}
                  </div>
                  
                  {/* Answer Text */}
                  <div className="flex-1">
                    <p className="text-base text-text-black font-medium">
                      {option.option_text}
                    </p>
                  </div>
                </div>
                
                {/* Mark as Correct Radio */}
                {!isReadOnly && (
                  <div className="flex items-center gap-2 pl-1">
                    <input
                      type="radio"
                      name={`question-${question.id}`}
                      value={optionLetter}
                      checked={isSelected}
                      onChange={(e) => handleAnswerChange(e.target.value)}
                      className="w-4 h-4 text-primary-blue focus:ring-primary-blue"
                    />
                    <label className="text-sm text-text-gray">Mark as correct</label>
                  </div>
                )}
              </div>
            );
          })}
        </div>
      )}

      {/* True/False Options */}
      {question.type === 'tf' && (
        <div className="space-y-5 mb-8">
          {['True', 'False'].map((value, index) => {
            const isSelected = answer === value;
            const isCorrect = isReadOnly && question.correct_answer?.toLowerCase() === value.toLowerCase();
            
            return (
              <div key={value} className="space-y-3">
                <div
                  className={`${
                    isCorrect || isSelected
                      ? 'bg-soft-green border-2 border-green-400'
                      : 'bg-soft-red'
                  } p-5 rounded-lg flex items-center gap-4 transition-all ${
                    !isReadOnly && 'cursor-pointer hover:shadow-md'
                  }`}
                  onClick={() => !isReadOnly && handleAnswerChange(value)}
                >
                  <div
                    className={`min-w-[40px] h-10 rounded-lg flex items-center justify-center font-bold text-white ${
                      isCorrect || isSelected ? 'bg-green-600' : 'bg-red-500'
                    }`}
                  >
                    {index === 0 ? 'T' : 'F'}
                  </div>
                  <div className="flex-1">
                    <p className="text-base text-text-black font-medium">{value}</p>
                  </div>
                </div>
                {!isReadOnly && (
                  <div className="flex items-center gap-2 pl-1">
                    <input
                      type="radio"
                      name={`question-${question.id}`}
                      value={value}
                      checked={isSelected}
                      onChange={(e) => handleAnswerChange(e.target.value)}
                      className="w-4 h-4 text-primary-blue focus:ring-primary-blue"
                    />
                    <label className="text-sm text-text-gray">Mark as correct</label>
                  </div>
                )}
              </div>
            );
          })}
        </div>
      )}

      {/* Essay Input */}
      {question.type === 'essay' && (
        <div className="mb-8">
          <textarea
            value={answer || ''}
            onChange={(e) => handleAnswerChange(e.target.value)}
            disabled={isReadOnly}
            rows={8}
            className="w-full px-4 py-3 border-2 border-border-gray rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-blue text-text-black resize-none"
            placeholder="Type your answer here..."
          />
        </div>
      )}

      {/* Calculation Input */}
      {question.type === 'calculation' && (
        <div className="mb-8">
          <input
            type="number"
            step="any"
            value={answer || ''}
            onChange={(e) => handleAnswerChange(e.target.value)}
            disabled={isReadOnly}
            className="w-full px-4 py-3 border-2 border-border-gray rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-blue text-text-black text-center text-xl"
            placeholder="Enter your numerical answer"
          />
        </div>
      )}
    </div>
  );
};

export default QuestionCard;

