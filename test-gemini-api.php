<?php
/**
 * Test Gemini API Connection
 * Run this file directly to test if your API key works
 * Access: http://localhost/Individual%20Project/test-gemini-api.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Your API key
$apiKey = 'AIzaSyBjy0VIHkAYrBMK03UhFS5DPsNDgnDn9Bw';

// Test with gemini-2.5-flash (December 2025 current model)
$model = 'gemini-2.5-flash';
$url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

$data = [
    'contents' => [
        [
            'parts' => [
                ['text' => 'Say "Hello, the API is working!" in JSON format with a key "message"']
            ]
        ]
    ]
];

echo "<h2>Testing Gemini API Connection</h2>";
echo "<p><strong>API Key:</strong> " . substr($apiKey, 0, 20) . "...</p>";
echo "<p><strong>Model:</strong> {$model}</p>";
echo "<p><strong>URL:</strong> {$url}</p>";
echo "<hr>";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

echo "<h3>Response:</h3>";
echo "<p><strong>HTTP Code:</strong> {$httpCode}</p>";

if ($curlError) {
    echo "<p style='color: red;'><strong>cURL Error:</strong> {$curlError}</p>";
}

echo "<h4>Raw Response:</h4>";
echo "<pre style='background: #f4f4f4; padding: 15px; overflow-x: auto;'>";
echo htmlspecialchars($response);
echo "</pre>";

if ($httpCode === 200) {
    echo "<h3 style='color: green;'>✅ SUCCESS! API is working!</h3>";
    $result = json_decode($response, true);
    if ($result) {
        echo "<h4>Parsed Response:</h4>";
        echo "<pre>";
        print_r($result);
        echo "</pre>";
    }
} else {
    echo "<h3 style='color: red;'>❌ ERROR! API call failed</h3>";
    $errorData = json_decode($response, true);
    if ($errorData && isset($errorData['error'])) {
        echo "<p><strong>Error Message:</strong> " . htmlspecialchars($errorData['error']['message']) . "</p>";
        echo "<p><strong>Error Code:</strong> " . ($errorData['error']['code'] ?? 'N/A') . "</p>";
    }
    
    echo "<h4>Possible Fixes:</h4>";
    echo "<ul>";
    echo "<li>Verify your API key at: <a href='https://makersuite.google.com/app/apikey' target='_blank'>Google AI Studio</a></li>";
    echo "<li>Check if the API is enabled for your project</li>";
    echo "<li>Try a different model (gemini-1.5-pro, gemini-2.0-flash-exp)</li>";
    echo "<li>Verify your API quota hasn't been exceeded</li>";
    echo "</ul>";
}
?>

