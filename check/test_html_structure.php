<?php

// Test to examine the actual HTML structure of responses
// This will help us understand how document counts are displayed

echo "=== HTML Structure Analysis ===\n";

// Test both filtered and unfiltered requests
$urls = [
    'baseline' => 'http://127.0.0.1:8001/ar/documents/section/koanyn',
    'filtered' => 'http://127.0.0.1:8001/ar/documents/section/koanyn?fields%5B9%5D=%D8%B5%D8%AD%D8%A7%D9%81%D8%A9'
];

foreach ($urls as $type => $url) {
    echo "\n=== Testing $type URL ===\n";
    echo "URL: $url\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    echo "HTTP Status: $http_code\n";
    
    if ($error) {
        echo "cURL Error: $error\n";
        continue;
    }
    
    if ($http_code !== 200) {
        echo "HTTP Error: $http_code\n";
        continue;
    }
    
    // Save full response
    $filename = "response_{$type}.html";
    file_put_contents($filename, $response);
    echo "Response saved to: $filename\n";
    
    // Look for various document count patterns
    $patterns = [
        '/تم العثور على <strong>(\d+)<\/strong> وثيقة/',
        '/تم العثور على (\d+) وثيقة/',
        '/<strong>(\d+)<\/strong> وثيقة/',
        '/(\d+) وثيقة/',
        '/العثور على (\d+)/',
        '/وثيقة.*?(\d+)/',
        '/(\d+).*?وثيقة/'
    ];
    
    echo "\nSearching for document count patterns:\n";
    $found = false;
    
    foreach ($patterns as $i => $pattern) {
        if (preg_match($pattern, $response, $matches)) {
            echo "  Pattern " . ($i + 1) . ": Found {$matches[1]} documents\n";
            $found = true;
        }
    }
    
    if (!$found) {
        echo "  No document count patterns found\n";
        
        // Look for any Arabic text containing numbers
        if (preg_match_all('/(\d+)/', $response, $allNumbers)) {
            echo "  All numbers found: " . implode(', ', array_unique($allNumbers[1])) . "\n";
        }
        
        // Look for key Arabic words
        $arabicWords = ['وثيقة', 'وثائق', 'العثور', 'تم', 'نتائج'];
        foreach ($arabicWords as $word) {
            if (strpos($response, $word) !== false) {
                echo "  Found Arabic word: $word\n";
            }
        }
    }
    
    // Check response length
    echo "Response length: " . strlen($response) . " bytes\n";
    
    // Look for any error messages
    if (strpos($response, 'error') !== false || strpos($response, 'Error') !== false) {
        echo "  Error text found in response\n";
    }
    
    // Look for Laravel-specific content
    if (strpos($response, 'laravel') !== false || strpos($response, 'Laravel') !== false) {
        echo "  Laravel content detected\n";
    }
    
    // Check if it's a valid HTML document
    if (strpos($response, '<html') !== false && strpos($response, '</html>') !== false) {
        echo "  Valid HTML document\n";
    } else {
        echo "  Not a complete HTML document\n";
        echo "  Response preview: " . substr($response, 0, 200) . "...\n";
    }
}

echo "\n=== Analysis Complete ===\n";
echo "Check the saved HTML files for detailed inspection\n";