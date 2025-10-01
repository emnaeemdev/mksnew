<?php

// Simple test to check if our filtering logic works
// This will help us understand why the web interface returns 0 results

echo "=== Simple Filter Logic Test ===\n";

// Test the exact search pattern that should work
$searchValue = 'صحافة';
$testValues = [
    'قانون حرية الصحافة',
    'قانون الصحافة والإعلام',
    'صحافة',
    'الصحافة المصرية',
    'قانون رقم 2 للصحافة',
    'تنظيم الصحافة',
    'حرية الصحافة والتعبير'
];

echo "Search value: '{$searchValue}'\n";
echo "Clean value: '" . trim($searchValue) . "'\n\n";

// Test each LIKE pattern
$patterns = [
    "%{$searchValue}%",
    "%{$searchValue},%",
    "%, {$searchValue}%",
    "%, {$searchValue},%"
];

echo "Testing LIKE patterns:\n";
foreach ($patterns as $i => $pattern) {
    echo "Pattern " . ($i + 1) . ": '{$pattern}'\n";
    
    foreach ($testValues as $value) {
        // Simulate SQL LIKE behavior
        $regexPattern = str_replace(['%', '_'], ['.*', '.'], preg_quote($pattern, '/'));
        $regexPattern = '/^' . $regexPattern . '$/u';
        
        if (preg_match($regexPattern, $value)) {
            echo "  ✅ MATCH: '{$value}'\n";
        }
    }
    echo "\n";
}

// Test with the exact value we know exists
echo "=== Testing with known database value ===\n";
$knownValue = 'قانون حرية الصحافة';
echo "Known value: '{$knownValue}'\n";

foreach ($patterns as $i => $pattern) {
    $regexPattern = str_replace(['%', '_'], ['.*', '.'], preg_quote($pattern, '/'));
    $regexPattern = '/^' . $regexPattern . '$/u';
    
    if (preg_match($regexPattern, $knownValue)) {
        echo "Pattern " . ($i + 1) . " ('{$pattern}'): ✅ MATCH\n";
    } else {
        echo "Pattern " . ($i + 1) . " ('{$pattern}'): ❌ NO MATCH\n";
    }
}

// Test URL decoding
echo "\n=== URL Decoding Test ===\n";
$urlEncoded = '%D8%B5%D8%AD%D8%A7%D9%81%D8%A9';
$decoded = urldecode($urlEncoded);

echo "URL encoded: {$urlEncoded}\n";
echo "Decoded: '{$decoded}'\n";
echo "Original: '{$searchValue}'\n";
echo "Match: " . ($decoded === $searchValue ? '✅ YES' : '❌ NO') . "\n";

// Test character encoding
echo "\n=== Character Encoding Test ===\n";
echo "Search value bytes: " . bin2hex($searchValue) . "\n";
echo "Decoded value bytes: " . bin2hex($decoded) . "\n";
echo "Known value bytes: " . bin2hex($knownValue) . "\n";

// Test if the search value is contained in the known value
echo "\n=== Substring Test ===\n";
if (strpos($knownValue, $searchValue) !== false) {
    echo "✅ '{$searchValue}' found in '{$knownValue}'\n";
} else {
    echo "❌ '{$searchValue}' NOT found in '{$knownValue}'\n";
}

// Test with mb_strpos for Unicode
if (mb_strpos($knownValue, $searchValue, 0, 'UTF-8') !== false) {
    echo "✅ Unicode search: '{$searchValue}' found in '{$knownValue}'\n";
} else {
    echo "❌ Unicode search: '{$searchValue}' NOT found in '{$knownValue}'\n";
}

echo "\n=== Test Complete ===\n";