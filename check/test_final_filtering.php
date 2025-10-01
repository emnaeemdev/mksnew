<?php

// Test final filtering functionality through web interface
// This script will test the actual HTTP request to verify the fix

echo "=== Final Filtering Test ===\n";

// Test URL with field 9 filter for 'صحافة'
$test_url = 'http://127.0.0.1:8001/ar/documents/section/koanyn?fields%5B9%5D=%D8%B5%D8%AD%D8%A7%D9%81%D8%A9';

echo "Testing URL: $test_url\n";
echo "Decoded field 9 value: " . urldecode('%D8%B5%D8%AD%D8%A7%D9%81%D8%A9') . "\n";

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

echo "\n=== Response Analysis ===\n";
echo "HTTP Status Code: $http_code\n";

if ($error) {
    echo "cURL Error: $error\n";
    exit(1);
}

if ($http_code !== 200) {
    echo "HTTP Error: Expected 200, got $http_code\n";
    exit(1);
}

// Check for document count in response
if (preg_match('/تم العثور على (\d+) وثيقة/', $response, $matches)) {
    $document_count = $matches[1];
    echo "Documents found: $document_count\n";
    
    if ($document_count > 0) {
        echo "✅ SUCCESS: Filter is working! Found $document_count document(s)\n";
        
        // Check if the expected document is in results
        if (strpos($response, 'قانون رقم 2') !== false) {
            echo "✅ Expected document 'قانون رقم 2' found in results\n";
        } else {
            echo "⚠️  Expected document 'قانون رقم 2' not found in results\n";
        }
        
        // Check if the field value is displayed
        if (strpos($response, 'قانون حرية الصحافة') !== false) {
            echo "✅ Expected field value 'قانون حرية الصحافة' found in results\n";
        } else {
            echo "⚠️  Expected field value 'قانون حرية الصحافة' not found in results\n";
        }
    } else {
        echo "❌ FAILURE: No documents found with filter\n";
        
        // Check for 'no documents' message
        if (strpos($response, 'لا توجد وثائق') !== false || strpos($response, 'لم يتم العثور على وثائق') !== false) {
            echo "Found 'no documents' message in response\n";
        }
    }
} else {
    echo "❌ Could not find document count pattern in response\n";
    
    // Check if there's an error message
    if (strpos($response, 'خطأ') !== false || strpos($response, 'Error') !== false) {
        echo "Error message found in response\n";
    }
}

// Test without filter to ensure basic functionality
echo "\n=== Testing without filter ===\n";
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

if (preg_match('/تم العثور على (\d+) وثيقة/', $response_base, $matches_base)) {
    $total_documents = $matches_base[1];
    echo "Total documents in section: $total_documents\n";
    
    if ($total_documents > 0) {
        echo "✅ Section has documents available\n";
    } else {
        echo "⚠️  No documents in section\n";
    }
} else {
    echo "Could not determine total document count\n";
}

echo "\n=== Test Complete ===\n";