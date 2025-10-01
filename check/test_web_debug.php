<?php

// Test the web interface with detailed debugging
// This will help us see exactly what's happening in the web request

echo "=== Web Interface Debug Test ===\n";

// Test URL with field 9 filter for 'صحافة'
$test_url = 'http://127.0.0.1:8001/ar/documents/section/koanyn?fields%5B9%5D=%D8%B5%D8%AD%D8%A7%D9%81%D8%A9&debug=1';

echo "Testing URL: $test_url\n";
echo "Decoded field 9 value: " . urldecode('%D8%B5%D8%AD%D8%A7%D9%81%D8%A9') . "\n";

// Initialize cURL with detailed options
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $test_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
curl_setopt($ch, CURLOPT_VERBOSE, true);
$verbose = fopen('php://temp', 'w+');
curl_setopt($ch, CURLOPT_STDERR, $verbose);

// Execute request
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

// Get verbose info
rewind($verbose);
$verboseLog = stream_get_contents($verbose);
fclose($verbose);
curl_close($ch);

echo "\n=== Response Analysis ===\n";
echo "HTTP Status Code: $http_code\n";

if ($error) {
    echo "cURL Error: $error\n";
    exit(1);
}

if ($http_code !== 200) {
    echo "HTTP Error: Expected 200, got $http_code\n";
    echo "Response preview: " . substr($response, 0, 500) . "\n";
    exit(1);
}

// Look for document count
if (preg_match('/تم العثور على <strong>(\d+)<\/strong> وثيقة/', $response, $matches)) {
    $document_count = $matches[1];
    echo "Documents found: $document_count\n";
    
    if ($document_count == 0) {
        echo "❌ Zero documents returned\n";
        
        // Look for any debug information in the response
        if (strpos($response, 'SQL:') !== false) {
            echo "\n=== SQL Debug Info Found ===\n";
            if (preg_match('/SQL: (.+?)(?=\n|$)/s', $response, $sqlMatches)) {
                echo "SQL Query: " . trim($sqlMatches[1]) . "\n";
            }
        }
        
        // Look for Laravel debug bar or error info
        if (strpos($response, 'debugbar') !== false) {
            echo "Laravel Debug Bar detected\n";
        }
        
        // Check if there are any PHP errors
        if (strpos($response, 'ErrorException') !== false || strpos($response, 'FatalError') !== false) {
            echo "PHP Error detected in response\n";
        }
        
    } else {
        echo "✅ Found $document_count document(s)\n";
    }
} else {
    echo "❌ Could not find document count pattern\n";
    
    // Save response for manual inspection
    file_put_contents('web_debug_response.html', $response);
    echo "Response saved to web_debug_response.html\n";
}

// Test without any filters to establish baseline
echo "\n=== Baseline Test (No Filters) ===\n";
$base_url = 'http://127.0.0.1:8001/ar/documents/section/koanyn';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $base_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response_base = curl_exec($ch);
$http_code_base = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Base URL HTTP Status: $http_code_base\n";

if (preg_match('/تم العثور على <strong>(\d+)<\/strong> وثيقة/', $response_base, $matches_base)) {
    $total_documents = $matches_base[1];
    echo "Total documents in section (no filters): $total_documents\n";
    
    if ($total_documents > 0) {
        echo "✅ Section has documents available\n";
        
        // If baseline works but filtered doesn't, it's a filter issue
        if ($document_count == 0) {
            echo "🔍 Issue: Baseline has documents but filter returns 0\n";
            echo "This suggests the filter logic or data matching is the problem\n";
        }
    } else {
        echo "❌ No documents in section at all\n";
    }
} else {
    echo "Could not determine total document count\n";
}

// Test with a different field to see if the issue is specific to field 9
echo "\n=== Alternative Field Test ===\n";
$alt_url = 'http://127.0.0.1:8001/ar/documents/section/koanyn?fields%5B1%5D=قانون';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $alt_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response_alt = curl_exec($ch);
$http_code_alt = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Alternative field URL HTTP Status: $http_code_alt\n";

if (preg_match('/تم العثور على <strong>(\d+)<\/strong> وثيقة/', $response_alt, $matches_alt)) {
    $alt_documents = $matches_alt[1];
    echo "Documents found with field 1 filter: $alt_documents\n";
    
    if ($alt_documents > 0) {
        echo "✅ Field 1 filter works\n";
    } else {
        echo "❌ Field 1 filter also returns 0\n";
    }
} else {
    echo "Could not determine alternative field document count\n";
}

echo "\n=== Debug Test Complete ===\n";