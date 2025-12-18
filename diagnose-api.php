<?php
/**
 * Gemini API Diagnostic Tool
 * Run this script to diagnose connectivity issues
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/gemini.php';

echo "<h1>Gemini API Diagnostic Tool</h1>";
echo "<style>body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
.success { color: green; font-weight: bold; }
.error { color: red; font-weight: bold; }
.info { color: blue; }
pre { background: #f5f5f5; padding: 15px; border-radius: 5px; overflow-x: auto; }
</style>";

// Test 1: Check if cURL is available
echo "<h2>Test 1: cURL Extension</h2>";
if (function_exists('curl_version')) {
    $curlVersion = curl_version();
    echo "<p class='success'>✓ cURL is installed</p>";
    echo "<pre>Version: " . $curlVersion['version'] . "\n";
    echo "SSL Version: " . $curlVersion['ssl_version'] . "</pre>";
} else {
    echo "<p class='error'>✗ cURL is NOT installed. Please install PHP cURL extension.</p>";
    exit;
}

// Test 2: Check API key configuration
echo "<h2>Test 2: API Key Configuration</h2>";
if (defined('GEMINI_API_KEY') && !empty(GEMINI_API_KEY)) {
    $keyPreview = substr(GEMINI_API_KEY, 0, 10) . '...' . substr(GEMINI_API_KEY, -5);
    echo "<p class='success'>✓ API key is configured</p>";
    echo "<pre>Key Preview: $keyPreview</pre>";
} else {
    echo "<p class='error'>✗ API key is NOT configured in config/gemini.php</p>";
    exit;
}

// Test 3: Check API URL
echo "<h2>Test 3: API URL Configuration</h2>";
if (defined('GEMINI_API_URL') && !empty(GEMINI_API_URL)) {
    echo "<p class='success'>✓ API URL is configured</p>";
    echo "<pre>" . htmlspecialchars(GEMINI_API_URL) . "</pre>";
} else {
    echo "<p class='error'>✗ API URL is NOT configured</p>";
    exit;
}

// Test 4: Check internet connectivity
echo "<h2>Test 4: Internet Connectivity</h2>";
$testUrl = "https://www.google.com";
$ch = curl_init($testUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_NOBODY, true); // HEAD request only
$result = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($httpCode == 200) {
    echo "<p class='success'>✓ Internet connection is working</p>";
} else {
    echo "<p class='error'>✗ Cannot reach internet</p>";
    echo "<pre>HTTP Code: $httpCode\nError: $error</pre>";
    echo "<p class='info'>Please check your internet connection, proxy settings, or firewall.</p>";
    exit;
}

// Test 5: Check Google API accessibility
echo "<h2>Test 5: Google API Accessibility</h2>";
$googleApiTest = "https://generativelanguage.googleapis.com";
$ch = curl_init($googleApiTest);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_NOBODY, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
$result = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "<p class='error'>✗ Cannot reach Google API endpoint</p>";
    echo "<pre>Error: $error</pre>";
    echo "<p class='info'>This might be a firewall or network issue. Google APIs may be blocked on your network.</p>";
} else {
    echo "<p class='success'>✓ Google API endpoint is reachable (HTTP: $httpCode)</p>";
}

// Test 6: Test actual Gemini API call
echo "<h2>Test 6: Gemini API Test Call</h2>";
echo "<p class='info'>Sending test prompt to Gemini API...</p>";

try {
    $testPrompt = "Reply with exactly: API test successful";
    
    $startTime = microtime(true);
    $response = callGeminiAPI($testPrompt, 0); // No retries
    $endTime = microtime(true);
    $duration = round(($endTime - $startTime), 2);
    
    $text = extractGeminiText($response);
    
    echo "<p class='success'>✓ API call successful!</p>";
    echo "<pre>Duration: {$duration} seconds\n";
    echo "Response: " . htmlspecialchars(substr($text, 0, 200)) . "</pre>";
    
    echo "<h2>Summary</h2>";
    echo "<p class='success' style='font-size: 1.2em;'>✓ ALL TESTS PASSED! Your Gemini API is working correctly.</p>";
    echo "<p>You can now use the quiz generation feature.</p>";
    
} catch (Exception $e) {
    echo "<p class='error'>✗ API call failed</p>";
    echo "<pre>Error: " . htmlspecialchars($e->getMessage()) . "</pre>";
    
    echo "<h2>Troubleshooting Steps</h2>";
    echo "<ol>";
    echo "<li><strong>Check your API key:</strong> Verify at <a href='https://makersuite.google.com/app/apikey' target='_blank'>Google AI Studio</a></li>";
    echo "<li><strong>Verify API is enabled:</strong> Make sure Gemini API is enabled for your project</li>";
    echo "<li><strong>Check network:</strong> Ensure your firewall/proxy allows connections to googleapis.com</li>";
    echo "<li><strong>Try different model:</strong> The model might be deprecated or unavailable in your region</li>";
    echo "<li><strong>Check quota:</strong> You might have exceeded your API quota</li>";
    echo "</ol>";
    
    echo "<h3>Recommended Actions:</h3>";
    echo "<ul>";
    echo "<li>Wait a few minutes and try again (temporary network issues)</li>";
    echo "<li>Check if you're behind a corporate firewall that blocks Google APIs</li>";
    echo "<li>Try from a different network (mobile hotspot, different WiFi)</li>";
    echo "<li>Update your API key if it has expired</li>";
    echo "</ul>";
}

echo "<hr>";
echo "<p><a href='dashboard.php'>← Back to Dashboard</a> | <a href='test-gemini-api.php'>Run Detailed API Test</a></p>";
?>

