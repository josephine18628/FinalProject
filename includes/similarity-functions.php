<?php
/**
 * Text Similarity Functions
 * CS3 Quiz Platform - For grading essay and calculation answers
 */

/**
 * Calculate similarity between two texts using multiple methods
 * @param string $answer1 First text (user answer)
 * @param string $answer2 Second text (model answer)
 * @return float Similarity score (0-100)
 */
function calculateTextSimilarity($answer1, $answer2) {
    if (empty($answer1) || empty($answer2)) {
        return 0.0;
    }
    
    // Normalize texts
    $text1 = normalizeText($answer1);
    $text2 = normalizeText($answer2);
    
    if (empty($text1) || empty($text2)) {
        return 0.0;
    }
    
    // Use multiple similarity metrics and combine them
    $levenshteinSim = calculateLevenshteinSimilarity($text1, $text2);
    $wordOverlapSim = calculateWordOverlapSimilarity($text1, $text2);
    $keywordSim = calculateKeywordSimilarity($text1, $text2);
    
    // Weighted average (word overlap is most important for essays)
    $similarity = ($wordOverlapSim * 0.5) + ($keywordSim * 0.3) + ($levenshteinSim * 0.2);
    
    return round($similarity, 2);
}

/**
 * Normalize text for comparison
 * @param string $text Input text
 * @return string Normalized text
 */
function normalizeText($text) {
    // Convert to lowercase
    $text = mb_strtolower($text, 'UTF-8');
    
    // Remove extra whitespace
    $text = preg_replace('/\s+/', ' ', $text);
    
    // Remove punctuation for comparison
    $text = preg_replace('/[^\w\s]/', '', $text);
    
    return trim($text);
}

/**
 * Calculate Levenshtein distance based similarity
 * @param string $text1 First text
 * @param string $text2 Second text
 * @return float Similarity percentage (0-100)
 */
function calculateLevenshteinSimilarity($text1, $text2) {
    $maxLength = max(mb_strlen($text1), mb_strlen($text2));
    
    if ($maxLength == 0) {
        return 100.0;
    }
    
    // For very long texts, use sample to avoid performance issues
    if ($maxLength > 1000) {
        $text1 = mb_substr($text1, 0, 1000);
        $text2 = mb_substr($text2, 0, 1000);
        $maxLength = 1000;
    }
    
    $distance = levenshtein($text1, $text2);
    $similarity = (1 - ($distance / $maxLength)) * 100;
    
    return max(0, $similarity);
}

/**
 * Calculate word overlap similarity
 * @param string $text1 First text
 * @param string $text2 Second text
 * @return float Similarity percentage (0-100)
 */
function calculateWordOverlapSimilarity($text1, $text2) {
    // Split into words
    $words1 = array_filter(explode(' ', $text1));
    $words2 = array_filter(explode(' ', $text2));
    
    if (empty($words1) || empty($words2)) {
        return 0.0;
    }
    
    // Remove common stop words
    $stopWords = ['the', 'is', 'at', 'which', 'on', 'a', 'an', 'and', 'or', 'but', 'in', 'with', 'to', 'for', 'of', 'as', 'by'];
    $words1 = array_diff($words1, $stopWords);
    $words2 = array_diff($words2, $stopWords);
    
    // Calculate intersection
    $intersection = array_intersect($words1, $words2);
    $union = array_unique(array_merge($words1, $words2));
    
    if (empty($union)) {
        return 0.0;
    }
    
    // Jaccard similarity
    $similarity = (count($intersection) / count($union)) * 100;
    
    return $similarity;
}

/**
 * Calculate keyword/concept similarity
 * @param string $text1 First text
 * @param string $text2 Second text
 * @return float Similarity percentage (0-100)
 */
function calculateKeywordSimilarity($text1, $text2) {
    // Extract significant words (3+ characters)
    $words1 = array_filter(explode(' ', $text1), function($word) {
        return mb_strlen($word) >= 3;
    });
    
    $words2 = array_filter(explode(' ', $text2), function($word) {
        return mb_strlen($word) >= 3;
    });
    
    if (empty($words1) || empty($words2)) {
        return 0.0;
    }
    
    // Count word frequencies
    $freq1 = array_count_values($words1);
    $freq2 = array_count_values($words2);
    
    // Calculate cosine similarity based on word frequencies
    $dotProduct = 0;
    $magnitude1 = 0;
    $magnitude2 = 0;
    
    $allWords = array_unique(array_merge(array_keys($freq1), array_keys($freq2)));
    
    foreach ($allWords as $word) {
        $count1 = $freq1[$word] ?? 0;
        $count2 = $freq2[$word] ?? 0;
        
        $dotProduct += $count1 * $count2;
        $magnitude1 += $count1 * $count1;
        $magnitude2 += $count2 * $count2;
    }
    
    $magnitude1 = sqrt($magnitude1);
    $magnitude2 = sqrt($magnitude2);
    
    if ($magnitude1 == 0 || $magnitude2 == 0) {
        return 0.0;
    }
    
    $similarity = ($dotProduct / ($magnitude1 * $magnitude2)) * 100;
    
    return $similarity;
}

/**
 * Extract key concepts from text for content coverage analysis
 * @param string $text Text to extract concepts from
 * @return array Array of key concept words
 */
function extractKeyConcepts($text) {
    $normalized = normalizeText($text);
    $words = array_filter(explode(' ', $normalized), function($word) {
        return mb_strlen($word) >= 4; // Only significant words (4+ chars)
    });
    
    // Remove very common words even if 4+ chars
    $commonWords = ['that', 'this', 'with', 'from', 'have', 'been', 'were', 'will', 'your', 'they', 'what', 'when', 'make', 'like', 'time', 'than', 'into', 'them', 'some', 'these', 'would', 'other', 'which', 'their', 'about', 'there', 'could', 'should'];
    $words = array_diff($words, $commonWords);
    
    return array_unique($words);
}

/**
 * Calculate content coverage (what percentage of model answer concepts are in user answer)
 * @param string $userAnswer User's answer
 * @param string $modelAnswer Model answer
 * @return array ['coverage' => float, 'found_concepts' => int, 'total_concepts' => int]
 */
function calculateContentCoverage($userAnswer, $modelAnswer) {
    $userConcepts = extractKeyConcepts($userAnswer);
    $modelConcepts = extractKeyConcepts($modelAnswer);
    
    if (empty($modelConcepts)) {
        return ['coverage' => 0, 'found_concepts' => 0, 'total_concepts' => 0];
    }
    
    // Count how many model concepts are in user answer
    $foundConcepts = 0;
    foreach ($modelConcepts as $concept) {
        if (in_array($concept, $userConcepts)) {
            $foundConcepts++;
        }
    }
    
    $coverage = ($foundConcepts / count($modelConcepts)) * 100;
    
    return [
        'coverage' => $coverage,
        'found_concepts' => $foundConcepts,
        'total_concepts' => count($modelConcepts)
    ];
}

/**
 * Grade an essay answer based on content coverage
 * 
 * NOTE: This grades ONE INDIVIDUAL QUESTION, not the entire quiz.
 * Each question receives its own score which contributes to the overall quiz score.
 * 
 * GRADING RULES (Per Question):
 * - Match > 80%  → 100% (Full marks for this question)
 * - Match 50-80% → 50%  (Half marks for this question)
 * - Match 20-50% → 25%  (Quarter marks for this question)
 * - Match < 20%  → 10%  (Minimal credit for this question)
 * 
 * @param string $userAnswer User's answer
 * @param string $modelAnswer Model answer
 * @param array $keyPoints Optional key points that should be mentioned
 * @return array ['similarity' => float, 'grade' => bool, 'feedback' => string]
 */
function gradeEssayAnswer($userAnswer, $modelAnswer, $keyPoints = []) {
    // Calculate content coverage (how much of model answer is in user answer)
    $coverage = calculateContentCoverage($userAnswer, $modelAnswer);
    $coveragePercent = $coverage['coverage'];
    $foundConcepts = $coverage['found_concepts'];
    $totalConcepts = $coverage['total_concepts'];
    
    // Calculate text similarity as secondary metric
    $textSimilarity = calculateTextSimilarity($userAnswer, $modelAnswer);
    
    // Check key points coverage if provided
    $keyPointsScore = 0;
    $keyPointsCovered = 0;
    if (!empty($keyPoints)) {
        $userAnswerNorm = normalizeText($userAnswer);
        
        foreach ($keyPoints as $point) {
            $pointConcepts = extractKeyConcepts($point);
            $foundPointConcepts = 0;
            
            foreach ($pointConcepts as $concept) {
                if (strpos($userAnswerNorm, $concept) !== false) {
                    $foundPointConcepts++;
                }
            }
            
            // If at least 50% of key concepts found, consider point covered
            if (count($pointConcepts) > 0 && $foundPointConcepts >= count($pointConcepts) * 0.5) {
                $keyPointsCovered++;
            }
        }
        
        $keyPointsScore = (count($keyPoints) > 0) ? ($keyPointsCovered / count($keyPoints)) * 100 : 0;
    }
    
    // GRADING LOGIC (Based on content match percentage):
    // 1. Match > 80% → Full marks (100%)
    // 2. Match 50-80% → Half marks (50%)
    // 3. Match 20-50% → Quarter marks (25%)
    // 4. Match < 20% → Minimal credit (10%)
    
    $finalScore = 0;
    
    if ($coveragePercent > 80) {
        // Match > 80% → FULL MARKS
        $finalScore = 100;
        $gradeLevel = "full";
    } elseif ($coveragePercent >= 50) {
        // Match 50-80% → HALF MARKS
        $finalScore = 50;
        $gradeLevel = "half";
    } elseif ($coveragePercent >= 20) {
        // Match 20-50% → QUARTER MARKS
        $finalScore = 25;
        $gradeLevel = "quarter";
    } else {
        // Match < 20% → MINIMAL CREDIT
        $finalScore = 10;
        $gradeLevel = "minimal";
    }
    
    // Bonus: If key points are well covered, add bonus points (max 10%)
    if (!empty($keyPoints) && $keyPointsScore >= 80) {
        $finalScore = min(100, $finalScore + 10);
    }
    
    // Determine if answer passes (50% threshold for partial credit)
    $passes = $finalScore >= 50;
    
    // Generate feedback
    $feedback = generateContentFeedback($finalScore, $coveragePercent, $foundConcepts, $totalConcepts, $keyPointsScore, $keyPointsCovered, count($keyPoints), $gradeLevel);
    
    return [
        'similarity' => $finalScore,
        'grade' => $passes,
        'feedback' => $feedback,
        'coverage' => $coveragePercent,
        'key_points_score' => $keyPointsScore
    ];
}

/**
 * Generate detailed feedback based on content coverage
 * @param float $finalScore Final score
 * @param float $coveragePercent Content coverage percentage
 * @param int $foundConcepts Number of concepts found
 * @param int $totalConcepts Total concepts in model answer
 * @param float $keyPointsScore Key points coverage score
 * @param int $keyPointsCovered Number of key points covered
 * @param int $totalKeyPoints Total number of key points
 * @param string $gradeLevel Grade level (full, high, half, low)
 * @return string Feedback message
 */
function generateContentFeedback($finalScore, $coveragePercent, $foundConcepts, $totalConcepts, $keyPointsScore, $keyPointsCovered, $totalKeyPoints, $gradeLevel) {
    $feedback = '';
    
    // Main feedback based on grade level
    if ($gradeLevel === "full") {
        $feedback = "✓ FULL MARKS (100%): Excellent! Your answer matches the model answer with over 80% coverage. You have demonstrated comprehensive understanding of the topic.";
    } elseif ($gradeLevel === "half") {
        $feedback = "◐ HALF MARKS (50%): Your answer matches the model answer with 50-80% coverage. You have captured the main concepts but need more detail for full marks.";
    } elseif ($gradeLevel === "quarter") {
        $feedback = "◔ QUARTER MARKS (25%): Your answer matches the model answer with 20-50% coverage. You have some understanding but are missing several key concepts.";
    } else {
        $feedback = "✗ MINIMAL CREDIT (10%): Your answer has limited coverage of the key concepts (below 20%). Review the model answer to understand what was expected.";
    }
    
    // Add detailed breakdown
    $feedback .= "\n\n📊 Content Coverage: {$foundConcepts}/{$totalConcepts} key concepts found (" . round($coveragePercent) . "%)";
    
    // Add specific feedback about key points if provided
    if ($totalKeyPoints > 0) {
        $feedback .= "\n📝 Key Points: {$keyPointsCovered}/{$totalKeyPoints} covered (" . round($keyPointsScore) . "%)";
        
        if ($keyPointsCovered === $totalKeyPoints) {
            $feedback .= " - All key points mentioned!";
        } elseif ($keyPointsCovered >= $totalKeyPoints * 0.5) {
            $feedback .= " - Most key points mentioned.";
        } else {
            $feedback .= " - Try to include more key points in your answer.";
        }
    }
    
    // Add score
    $feedback .= "\n\n🎯 Final Score: " . round($finalScore) . "/100";
    
    // Add guidance
    if ($finalScore < 100) {
        $feedback .= "\n\n💡 To improve: Compare your answer with the model answer below to see what concepts or details you may have missed.";
    }
    
    return $feedback;
}

/**
 * Grade calculation answer with detailed step analysis
 * 
 * NOTE: This grades ONE INDIVIDUAL QUESTION, not the entire quiz.
 * Each question receives its own score which contributes to the overall quiz score.
 * 
 * GRADING RULES (Per Question):
 * - Correct final answer → 100% (Full marks for this question)
 * - Wrong answer + Method >80% match → 50% (Half marks for correct method)
 * - Wrong answer + Method 50-80% match → 25% (Quarter marks for partial method)
 * - Wrong answer + Method 20-50% match → 10% (Minimal credit)
 * - Wrong answer + Method <20% match → 5-10% (Very limited credit)
 * 
 * @param string $userAnswer User's calculation answer
 * @param string $correctAnswer Correct answer
 * @param string $working User's working/steps
 * @return array ['is_correct' => bool, 'similarity' => float, 'feedback' => string]
 */
function gradeCalculationAnswer($userAnswer, $correctAnswer, $working = '') {
    // Extract numeric values
    $userNumeric = extractNumericValue($userAnswer);
    $correctNumeric = extractNumericValue($correctAnswer);
    
    $isCorrect = false;
    $similarity = 0;
    $feedback = '';
    $methodScore = 0;
    
    // If both are numeric, compare values
    if ($userNumeric !== null && $correctNumeric !== null) {
        $tolerance = 0.01;
        $difference = abs($userNumeric - $correctNumeric);
        $exactMatch = $difference < $tolerance;
        
        // Calculate percentage error
        $percentError = ($correctNumeric != 0) ? abs(($userNumeric - $correctNumeric) / $correctNumeric) * 100 : 100;
        
        if ($exactMatch) {
            // FULL MARKS: Correct answer
            $similarity = 100;
            $isCorrect = true;
            $feedback = "✓ FULL MARKS: Correct! Your numerical answer is accurate.";
        } else {
            // Check if working shows correct method
            if (!empty($working)) {
                // Analyze working for key concepts
                $workingCoverage = calculateContentCoverage($working, $correctAnswer);
                $methodScore = $workingCoverage['coverage'];
                
                // Apply same grading thresholds to method coverage
                if ($methodScore > 80) {
                    // Method matches > 80% but wrong final answer
                    $similarity = 50;
                    $isCorrect = false;
                    $feedback = "◐ HALF MARKS (50%): Your method is correct (>{$methodScore}% match), but there's a calculation error in the final answer. ";
                    $feedback .= "Your answer: {$userNumeric}, Correct answer: {$correctNumeric}. Review your arithmetic.";
                } elseif ($methodScore >= 50) {
                    // Method matches 50-80%
                    $similarity = 25;
                    $feedback = "◔ QUARTER MARKS (25%): Your working shows partial understanding (50-80% match), but the method needs improvement. ";
                    $feedback .= "Your answer: {$userNumeric}, Correct answer: {$correctNumeric}.";
                } elseif ($methodScore >= 20) {
                    // Method matches 20-50%
                    $similarity = 10;
                    $feedback = "✗ MINIMAL CREDIT (10%): Your working shows limited understanding (20-50% match). ";
                    $feedback .= "Your answer: {$userNumeric}, Correct answer: {$correctNumeric}.";
                } else {
                    // Minimal credit based on how close the answer is
                    if ($percentError < 10) {
                        $similarity = 10;
                        $feedback = "✗ Incorrect answer with minimal method shown. Your answer is within 10% of correct.";
                    } else {
                        $similarity = 5;
                        $feedback = "✗ Incorrect. The answer differs significantly from the expected result.";
                    }
                }
            } else {
                // No working provided - grade only on answer closeness
                if ($percentError < 5) {
                    $similarity = 25;
                    $feedback = "◔ QUARTER MARKS (25%): Very close (within 5%), but not quite correct. Your answer: {$userNumeric}, Correct: {$correctNumeric}. ";
                    $feedback .= "Show your working to earn up to 50% credit even with calculation errors.";
                } elseif ($percentError < 10) {
                    $similarity = 10;
                    $feedback = "✗ MINIMAL CREDIT (10%): Close (within 10%), but incorrect. Your answer: {$userNumeric}, Correct: {$correctNumeric}. ";
                    $feedback .= "Show your working to earn more credit.";
                } else {
                    $similarity = 5;
                    $feedback = "✗ Incorrect. Your answer: {$userNumeric}, Correct: {$correctNumeric}. ";
                    $feedback .= "Show your working to demonstrate your understanding and earn partial credit.";
                }
            }
        }
    } else {
        // Non-numeric answers - use content coverage
        if (!empty($working)) {
            // Analyze both answer and working
            $combinedText = $userAnswer . " " . $working;
            $coverage = calculateContentCoverage($combinedText, $correctAnswer);
            $coveragePercent = $coverage['coverage'];
            
            // Apply same grading thresholds
            if ($coveragePercent > 80) {
                // Match > 80% → FULL MARKS
                $similarity = 100;
                $isCorrect = true;
                $feedback = "✓ FULL MARKS (100%): Your answer matches the model with >80% coverage. Complete understanding demonstrated.";
            } elseif ($coveragePercent >= 50) {
                // Match 50-80% → HALF MARKS
                $similarity = 50;
                $isCorrect = false;
                $feedback = "◐ HALF MARKS (50%): Your answer matches the model with 50-80% coverage. You have the main concepts but need more detail.";
            } elseif ($coveragePercent >= 20) {
                // Match 20-50% → QUARTER MARKS
                $similarity = 25;
                $isCorrect = false;
                $feedback = "◔ QUARTER MARKS (25%): Your answer matches the model with 20-50% coverage. Review the full solution below.";
            } else {
                // Match < 20% → MINIMAL CREDIT
                $similarity = 10;
                $feedback = "✗ MINIMAL CREDIT (10%): Your answer has limited coverage (<20%) of the expected concepts.";
            }
        } else {
            // Just compare answer text using content coverage
            $coverage = calculateContentCoverage($userAnswer, $correctAnswer);
            $coveragePercent = $coverage['coverage'];
            
            if ($coveragePercent > 80) {
                $similarity = 100;
                $isCorrect = true;
                $feedback = "✓ FULL MARKS (100%): Your answer matches the expected answer.";
            } elseif ($coveragePercent >= 50) {
                $similarity = 50;
                $isCorrect = false;
                $feedback = "◐ HALF MARKS (50%): Your answer partially matches. Show working for better credit.";
            } elseif ($coveragePercent >= 20) {
                $similarity = 25;
                $feedback = "◔ QUARTER MARKS (25%): Your answer has limited match. Show your working for better credit.";
            } else {
                $similarity = 10;
                $feedback = "✗ MINIMAL CREDIT (10%): Your answer doesn't match the expected answer. Show your working for partial credit.";
            }
        }
    }
    
    // Encouragement for showing working
    if (!empty($working)) {
        $feedback .= "\n\n📝 Good work showing your calculations - this helps earn partial credit even if the final answer isn't perfect!";
    } else if ($similarity < 100) {
        $feedback .= "\n\n💡 Tip: Always show your working! Even if your final answer is wrong, you can earn up to 50% credit for correct method.";
    }
    
    // Add score
    $feedback .= "\n\n🎯 Score: " . round($similarity) . "/100";
    
    return [
        'is_correct' => $isCorrect,
        'similarity' => $similarity,
        'feedback' => $feedback
    ];
}

/**
 * Extract numeric value from text
 * @param string $text Text containing number
 * @return float|null Extracted number or null
 */
function extractNumericValue($text) {
    // Remove common units and text
    $text = preg_replace('/[a-zA-Z]+/', '', $text);
    $text = preg_replace('/[^\d.\-+\s]/', '', $text);
    $text = trim($text);
    
    // Get first number
    if (preg_match('/-?\d+\.?\d*/', $text, $matches)) {
        return floatval($matches[0]);
    }
    
    return null;
}
?>

