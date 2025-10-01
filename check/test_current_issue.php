<?php

// Test the specific URL provided by user to confirm the filtering issue
require_once 'vendor/autoload.php';

// Test URL from user
$testUrl = 'http://localhost:8001/ar/documents/section/koanyn?search=&sort=latest&per_page=12&fields%5B9%5D=%D8%B5%D8%AD%D8%A7%D9%81%D8%A9&fields%5B10%5D=%D8%A7%D9%84%D8%AF%D9%88%D9%84%D8%A9+%D9%88%D9%86%D8%B8%D8%A7%D9%85+%D8%A7%D9%84%D8%AD%D9%83%D9%85&fields%5B6%5D=&fields%5B3%5D=&fields%5B2%5D%5Bday%5D=&fields%5B2%5D%5Bmonth%5D=&fields%5B2%5D%5Byear%5D=';

echo "Testing URL: " . $testUrl . "\n\n";

// Decode URL parameters
$parsedUrl = parse_url($testUrl);
parse_str($parsedUrl['query'], $params);

echo "Decoded parameters:\n";
print_r($params);

echo "\nField 9 value (raw): " . ($params['fields'][9] ?? 'NOT SET') . "\n";
echo "Field 9 value (decoded): " . urldecode($params['fields'][9] ?? '') . "\n";
echo "Field 10 value (raw): " . ($params['fields'][10] ?? 'NOT SET') . "\n";
echo "Field 10 value (decoded): " . urldecode($params['fields'][10] ?? '') . "\n";

// Make HTTP request
echo "\n=== Making HTTP Request ===\n";
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: ar,en;q=0.5',
            'Accept-Encoding: gzip, deflate',
            'Connection: keep-alive'
        ],
        'timeout' => 30
    ]
]);

$response = file_get_contents($testUrl, false, $context);

if ($response === false) {
    echo "Failed to get response\n";
    exit(1);
}

echo "Response received, length: " . strlen($response) . " bytes\n";

// Check for document count in response
if (preg_match('/تم العثور على (\d+) وثيقة/', $response, $matches)) {
    echo "Documents found: " . $matches[1] . "\n";
} elseif (preg_match('/(\d+) وثيقة/', $response, $matches)) {
    echo "Documents found: " . $matches[1] . "\n";
} else {
    echo "Could not find document count in response\n";
}

// Check for specific messages
if (strpos($response, 'تم العثور على 0 وثيقة مع الفلاتر المحددة') !== false) {
    echo "CONFIRMED: Zero documents found with filters message detected\n";
}

if (strpos($response, 'لا توجد وثائق') !== false) {
    echo "CONFIRMED: No documents available message detected\n";
}

// Save response for analysis
file_put_contents('current_issue_response.html', $response);
echo "\nResponse saved to current_issue_response.html\n";

echo "\n=== Test completed ===\n";
?>