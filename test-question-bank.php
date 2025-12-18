<?php
/**
 * Question Bank System Test Script
 * Run this after migration to verify everything works
 */

require_once 'config/database.php';
require_once 'includes/question-bank-functions.php';

echo "=== CS3 Quiz Platform - Question Bank Test ===\n\n";

try {
    $pdo = getDBConnection();
    echo "✓ Database connection successful\n\n";
    
    // Test 1: Check if tables exist
    echo "Test 1: Checking if tables exist...\n";
    $tables = ['question_bank', 'user_question_history'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "  ✓ Table '$table' exists\n";
        } else {
            echo "  ✗ Table '$table' NOT FOUND\n";
            echo "  → Run migration: php run-migration.php\n";
            exit(1);
        }
    }
    echo "\n";
    
    // Test 2: Check question_bank structure
    echo "Test 2: Checking question_bank structure...\n";
    $stmt = $pdo->query("DESCRIBE question_bank");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $requiredColumns = ['id', 'course_id', 'question_type', 'difficulty_level', 
                        'question_text', 'correct_answer', 'times_used'];
    
    foreach ($requiredColumns as $col) {
        if (in_array($col, $columns)) {
            echo "  ✓ Column '$col' exists\n";
        } else {
            echo "  ✗ Column '$col' missing\n";
        }
    }
    echo "\n";
    
    // Test 3: Check user_question_history structure
    echo "Test 3: Checking user_question_history structure...\n";
    $stmt = $pdo->query("DESCRIBE user_question_history");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $requiredColumns = ['id', 'user_id', 'question_bank_id', 'attempt_id', 
                        'was_correct', 'answered_at'];
    
    foreach ($requiredColumns as $col) {
        if (in_array($col, $columns)) {
            echo "  ✓ Column '$col' exists\n";
        } else {
            echo "  ✗ Column '$col' missing\n";
        }
    }
    echo "\n";
    
    // Test 4: Check quiz_responses has question_bank_id
    echo "Test 4: Checking quiz_responses modification...\n";
    $stmt = $pdo->query("DESCRIBE quiz_responses");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (in_array('question_bank_id', $columns)) {
        echo "  ✓ Column 'question_bank_id' exists in quiz_responses\n";
    } else {
        echo "  ✗ Column 'question_bank_id' missing from quiz_responses\n";
        echo "  → Run migration: php run-migration.php\n";
    }
    echo "\n";
    
    // Test 5: Check current data
    echo "Test 5: Checking current data...\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM question_bank");
    $result = $stmt->fetch();
    echo "  • Questions in bank: {$result['count']}\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM user_question_history");
    $result = $stmt->fetch();
    echo "  • User question history records: {$result['count']}\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo "  • Total users: {$result['count']}\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM quiz_attempts");
    $result = $stmt->fetch();
    echo "  • Total quiz attempts: {$result['count']}\n";
    echo "\n";
    
    // Test 6: Test helper functions
    echo "Test 6: Testing helper functions...\n";
    
    // Test getAvailableQuestionCount
    try {
        $count = getAvailableQuestionCount($pdo, 1, 'web_technologies', 'mcq', 'intermediate');
        echo "  ✓ getAvailableQuestionCount() works (found $count questions)\n";
    } catch (Exception $e) {
        echo "  ✗ getAvailableQuestionCount() failed: {$e->getMessage()}\n";
    }
    
    // Test getQuestionBankStats
    try {
        $stats = getQuestionBankStats($pdo);
        echo "  ✓ getQuestionBankStats() works (found " . count($stats) . " stat rows)\n";
    } catch (Exception $e) {
        echo "  ✗ getQuestionBankStats() failed: {$e->getMessage()}\n";
    }
    echo "\n";
    
    // Test 7: Test question bank stats view
    echo "Test 7: Testing question_bank_stats view...\n";
    try {
        $stmt = $pdo->query("SELECT * FROM question_bank_stats LIMIT 1");
        echo "  ✓ question_bank_stats view exists and works\n";
    } catch (PDOException $e) {
        echo "  ✗ question_bank_stats view error: {$e->getMessage()}\n";
    }
    echo "\n";
    
    // Test 8: Test sample question save (if no questions exist)
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM question_bank");
    $result = $stmt->fetch();
    
    if ($result['count'] == 0) {
        echo "Test 8: Testing question save functionality...\n";
        
        $sampleQuestions = [
            [
                'type' => 'mcq',
                'question' => 'What is HTML?',
                'correct_answer' => 'A',
                'options' => ['HyperText Markup Language', 'High Tech Modern Language', 
                             'Home Tool Markup Language', 'None of the above'],
                'explanation' => 'HTML stands for HyperText Markup Language',
                'difficulty' => 'beginner'
            ]
        ];
        
        try {
            $bankIds = saveQuestionsToBank($pdo, 'web_technologies', $sampleQuestions);
            if (count($bankIds) > 0) {
                echo "  ✓ Successfully saved test question to bank (ID: {$bankIds[0]})\n";
                
                // Clean up test question
                $stmt = $pdo->prepare("DELETE FROM question_bank WHERE id = ?");
                $stmt->execute([$bankIds[0]]);
                echo "  ✓ Test question cleaned up\n";
            } else {
                echo "  ✗ Failed to save test question\n";
            }
        } catch (Exception $e) {
            echo "  ✗ Question save test failed: {$e->getMessage()}\n";
        }
        echo "\n";
    }
    
    // Summary
    echo "=== Test Summary ===\n";
    echo "✓ All tests completed!\n\n";
    
    echo "Next Steps:\n";
    echo "1. Generate a quiz through the web interface\n";
    echo "2. Check if questions are saved to question_bank\n";
    echo "3. Complete the quiz\n";
    echo "4. Check if user_question_history is updated\n";
    echo "5. Generate another quiz as a different user\n";
    echo "6. Verify questions are reused (faster generation)\n\n";
    
    echo "Monitor with SQL:\n";
    echo "  SELECT * FROM question_bank;\n";
    echo "  SELECT * FROM user_question_history;\n";
    echo "  SELECT * FROM question_bank_stats;\n\n";
    
} catch (PDOException $e) {
    echo "\n✗ Database Error: " . $e->getMessage() . "\n";
    echo "Make sure:\n";
    echo "1. XAMPP MySQL is running\n";
    echo "2. Database 'cs3_quiz_platform' exists\n";
    echo "3. Database credentials in config/database.php are correct\n";
    exit(1);
} catch (Exception $e) {
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "=== Test Complete ===\n";
?>

