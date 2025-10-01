<?php

// Test to debug the exact controller logic with real web request simulation
// This will help us understand what's happening in the actual filtering process

echo "=== Controller Debug Test ===\n";

// Simulate the exact web request
$url = 'http://127.0.0.1:8001/ar/documents/section/koanyn?fields%5B9%5D=%D8%B5%D8%AD%D8%A7%D9%81%D8%A9';
echo "Testing URL: $url\n";

// Add debug parameter to get more information
$debug_url = $url . '&debug_filters=1';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $debug_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');

// Add headers to simulate a real browser request
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
    'Accept-Language: ar,en;q=0.5',
    'Accept-Encoding: gzip, deflate',
    'Connection: keep-alive',
    'Upgrade-Insecure-Requests: 1'
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Status: $http_code\n";

if ($error) {
    echo "cURL Error: $error\n";
    exit(1);
}

if ($http_code !== 200) {
    echo "HTTP Error: $http_code\n";
    exit(1);
}

// Look for debug information in the response
echo "\n=== Looking for Debug Information ===\n";

// Check if there's any debug output
if (strpos($response, 'DEBUG:') !== false) {
    echo "Debug information found in response\n";
    
    // Extract debug lines
    if (preg_match_all('/DEBUG: (.+?)(?=\n|$)/m', $response, $matches)) {
        foreach ($matches[1] as $debugLine) {
            echo "  $debugLine\n";
        }
    }
} else {
    echo "No debug information found\n";
}

// Check for SQL queries in the response
if (strpos($response, 'SQL:') !== false) {
    echo "\nSQL queries found in response\n";
    
    if (preg_match_all('/SQL: (.+?)(?=\n|$)/m', $response, $matches)) {
        foreach ($matches[1] as $sqlLine) {
            echo "  $sqlLine\n";
        }
    }
}

// Look for filter information
if (strpos($response, 'FILTER:') !== false) {
    echo "\nFilter information found in response\n";
    
    if (preg_match_all('/FILTER: (.+?)(?=\n|$)/m', $response, $matches)) {
        foreach ($matches[1] as $filterLine) {
            echo "  $filterLine\n";
        }
    }
}

// Check document count
if (preg_match('/تم العثور على <strong>(\d+)<\/strong> وثيقة/', $response, $matches)) {
    $count = $matches[1];
    echo "\nDocument count: $count\n";
    
    if ($count == 0) {
        echo "❌ Zero documents found - filter is not working\n";
    } else {
        echo "✅ Found $count document(s)\n";
    }
} else {
    echo "\nCould not find document count\n";
}

// Save response for inspection
file_put_contents('controller_debug_response.html', $response);
echo "\nResponse saved to controller_debug_response.html\n";

// Now let's test with a simple direct database query to compare
echo "\n=== Direct Database Comparison ===\n";

// Test if we can connect to the database directly
try {
    // Try to connect using Laravel's database configuration
    $config = [
        'host' => '127.0.0.1',
        'port' => '3306',
        'database' => 'mksnow',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4'
    ];
    
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ]);
    
    echo "Database connection successful\n";
    
    // Test the exact query that should be running
    $sql = "
        SELECT d.id, d.title, dfv.value as field_value
        FROM documents d
        INNER JOIN document_sections ds ON d.section_id = ds.id
        INNER JOIN document_field_values dfv ON d.id = dfv.document_id
        WHERE ds.slug = 'koanyn'
        AND d.is_published = 1
        AND d.published_at <= NOW()
        AND dfv.field_id = 9
        AND dfv.value LIKE '%صحافة%'
        ORDER BY d.published_at DESC
    ";
    
    echo "\nExecuting direct SQL query:\n";
    echo "SQL: " . preg_replace('/\s+/', ' ', trim($sql)) . "\n";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $results = $stmt->fetchAll();
    
    echo "\nDirect query results: " . count($results) . " documents\n";
    
    if (count($results) > 0) {
        echo "✅ Direct database query finds documents\n";
        foreach ($results as $result) {
            echo "  - {$result['title']} (Field value: {$result['field_value']})\n";
        }
        echo "\n🔍 Issue: Direct query works but web interface doesn't\n";
        echo "This suggests a problem in the controller logic or request processing\n";
    } else {
        echo "❌ Direct database query also returns 0 results\n";
        echo "This suggests a data or query issue\n";
    }
    
} catch (Exception $e) {
    echo "Database connection failed: " . $e->getMessage() . "\n";
}

echo "\n=== Debug Test Complete ===\n";