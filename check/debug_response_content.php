<?php

// Debug the actual response content to see what error is occurring

echo "=== Debugging Response Content ===\n";

// Test URL with field 9 filter for 'صحافة'
$test_url = 'http://127.0.0.1:8001/ar/documents/section/koanyn?fields%5B9%5D=%D8%B5%D8%AD%D8%A7%D9%81%D8%A9';

echo "Testing URL: $test_url\n";

// Initialize cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $test_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');

// Execute request
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Status Code: $http_code\n";

if ($error) {
    echo "cURL Error: $error\n";
    exit(1);
}

if ($http_code !== 200) {
    echo "HTTP Error: Expected 200, got $http_code\n";
    echo "Response: " . substr($response, 0, 1000) . "\n";
    exit(1);
}

// Save full response to file for analysis
file_put_contents('debug_full_response.html', $response);
echo "Full response saved to debug_full_response.html\n";

// Look for error patterns
echo "\n=== Error Analysis ===\n";

// Check for Laravel error patterns
if (strpos($response, 'Whoops') !== false) {
    echo "Found Laravel Whoops error page\n";
    
    // Extract error message
    if (preg_match('/<h1[^>]*>([^<]+)<\/h1>/', $response, $matches)) {
        echo "Error title: " . trim($matches[1]) . "\n";
    }
    
    if (preg_match('/<pre[^>]*class="[^"]*exception[^"]*"[^>]*>([^<]+)<\/pre>/', $response, $matches)) {
        echo "Exception: " . trim($matches[1]) . "\n";
    }
}

// Check for SQL errors
if (strpos($response, 'SQLSTATE') !== false) {
    echo "Found SQL error\n";
    
    if (preg_match('/SQLSTATE\[[^\]]+\]:[^\(]+\(([^\)]+)\)/', $response, $matches)) {
        echo "SQL Error: " . $matches[1] . "\n";
    }
}

// Check for PHP errors
if (strpos($response, 'Fatal error') !== false || strpos($response, 'Parse error') !== false) {
    echo "Found PHP error\n";
}

// Check for 500 error content
if (strpos($response, '500') !== false && strpos($response, 'Server Error') !== false) {
    echo "Found 500 Server Error\n";
}

// Look for specific error messages
if (preg_match('/class="exception-message[^"]*"[^>]*>([^<]+)</', $response, $matches)) {
    echo "Exception message: " . trim($matches[1]) . "\n";
}

// Check if it's a connection error
if (strpos($response, 'Connection') !== false && strpos($response, 'refused') !== false) {
    echo "Found connection error\n";
}

// Extract first 500 characters for quick analysis
echo "\n=== Response Preview ===\n";
echo substr($response, 0, 500) . "\n";

echo "\n=== Debug Complete ===\n";