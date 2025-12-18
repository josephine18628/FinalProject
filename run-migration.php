<?php
/**
 * Question Bank Migration Runner
 * Run this script once to set up the question bank system
 */

require_once 'config/database.php';

echo "=== CS3 Quiz Platform - Question Bank Migration ===\n\n";

try {
    $pdo = getDBConnection();
    
    // Read migration file
    $migrationFile = __DIR__ . '/database-migration-question-bank.sql';
    
    if (!file_exists($migrationFile)) {
        die("ERROR: Migration file not found at: $migrationFile\n");
    }
    
    echo "Reading migration file...\n";
    $sql = file_get_contents($migrationFile);
    
    if ($sql === false) {
        die("ERROR: Could not read migration file\n");
    }
    
    echo "Executing migration...\n\n";
    
    // Split into individual statements
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && 
                   !preg_match('/^--/', $stmt) && 
                   !preg_match('/^USE\s+/i', $stmt);
        }
    );
    
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($statements as $statement) {
        try {
            $pdo->exec($statement);
            $successCount++;
            
            // Show what was executed (first 80 chars)
            $preview = substr(preg_replace('/\s+/', ' ', $statement), 0, 80);
            echo "✓ Executed: " . $preview . "...\n";
            
        } catch (PDOException $e) {
            // Ignore "already exists" errors
            if (strpos($e->getMessage(), 'already exists') !== false) {
                echo "⚠ Skipped (already exists): " . substr($statement, 0, 50) . "...\n";
            } else {
                $errorCount++;
                echo "✗ Error: " . $e->getMessage() . "\n";
                echo "  Statement: " . substr($statement, 0, 100) . "...\n";
            }
        }
    }
    
    echo "\n=== Migration Summary ===\n";
    echo "Successful statements: $successCount\n";
    echo "Errors: $errorCount\n";
    
    // Show current table status
    echo "\n=== Table Status ===\n";
    
    $tables = ['question_bank', 'user_question_history', 'quiz_responses'];
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
            $result = $stmt->fetch();
            echo "$table: {$result['count']} rows\n";
        } catch (PDOException $e) {
            echo "$table: ERROR - {$e->getMessage()}\n";
        }
    }
    
    echo "\n✓ Migration completed!\n";
    echo "\nNext steps:\n";
    echo "1. Test quiz generation - questions will be saved to question_bank\n";
    echo "2. Complete a quiz - responses will be linked to question_bank\n";
    echo "3. Generate another quiz - questions will be reused from bank\n";
    echo "4. Check user_question_history to see which questions users have seen\n\n";
    
} catch (Exception $e) {
    echo "\n✗ FATAL ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
?>

