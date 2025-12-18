<?php
/**
 * Database Configuration and Connection
 * CS3 Quiz Platform
 */

// Database configuration constants
define('DB_HOST', 'localhost');
define('DB_NAME', 'cs3_quiz_platform');
define('DB_USER', 'root');
define('DB_PASS', ''); // Default XAMPP password is empty
define('DB_CHARSET', 'utf8mb4');

/**
 * Get database connection using PDO
 * @return PDO Database connection object
 * @throws PDOException if connection fails
 */
function getDBConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_PERSISTENT         => true
            ];
            
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            throw new PDOException("Database connection failed. Please check your configuration.");
        }
    }
    
    return $pdo;
}

/**
 * Test database connection
 * @return bool True if connection successful, false otherwise
 */
function testDBConnection() {
    try {
        $pdo = getDBConnection();
        return $pdo !== null;
    } catch (Exception $e) {
        return false;
    }
}
?>

