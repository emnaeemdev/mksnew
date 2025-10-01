<?php

// Test to confirm the parameter format issue
// This will simulate how Laravel processes different parameter formats

echo "=== Parameter Format Test ===\n";

// Simulate the URL parameter: fields[9]=صحافة
$_GET = [
    'fields' => [
        '9' => 'صحافة'
    ]
];

// Test different ways to access the parameter
echo "URL parameter format: fields[9]=صحافة\n";
echo "URL encoded: fields%5B9%5D=%D8%B5%D8%AD%D8%A7%D9%81%D8%A9\n\n";

// Method 1: Array notation (what the URL actually sends)
echo "Method 1 - Array notation:\n";
if (isset($_GET['fields']['9'])) {
    echo "  ✅ Found: '{$_GET['fields']['9']}'\n";
} else {
    echo "  ❌ Not found\n";
}

// Method 2: Dot notation (what the controller is looking for)
echo "\nMethod 2 - Dot notation simulation:\n";
// Laravel's input() method with dot notation
function getInputWithDot($key) {
    $keys = explode('.', $key);
    $value = $_GET;
    
    foreach ($keys as $k) {
        if (isset($value[$k])) {
            $value = $value[$k];
        } else {
            return null;
        }
    }
    
    return $value;
}

$dotValue = getInputWithDot('fields.9');
if ($dotValue !== null) {
    echo "  ✅ Found with dot notation: '{$dotValue}'\n";
} else {
    echo "  ❌ Not found with dot notation\n";
}

// Test what Laravel's Request::input() would actually return
echo "\n=== Laravel Request Simulation ===\n";

// Simulate Laravel's request input method
class MockRequest {
    private $data;
    
    public function __construct($data) {
        $this->data = $data;
    }
    
    public function input($key, $default = null) {
        // Laravel's input method supports both dot notation and array access
        if (strpos($key, '.') !== false) {
            $keys = explode('.', $key);
            $value = $this->data;
            
            foreach ($keys as $k) {
                if (is_array($value) && array_key_exists($k, $value)) {
                    $value = $value[$k];
                } else {
                    return $default;
                }
            }
            
            return $value;
        }
        
        return $this->data[$key] ?? $default;
    }
    
    public function has($key) {
        return $this->input($key) !== null;
    }
}

$request = new MockRequest($_GET);

// Test the exact code from the controller
$fieldId = 9;
$filterValue = $request->input("fields.{$fieldId}");

echo "Controller code test:\n";
echo "  Looking for: fields.{$fieldId}\n";
if ($filterValue !== null) {
    echo "  ✅ Found: '{$filterValue}'\n";
} else {
    echo "  ❌ Not found (this is the bug!)\n";
}

// Test the correct way to access it
$correctValue = $request->input('fields')[$fieldId] ?? null;
echo "\nCorrect access method:\n";
echo "  Looking for: fields[{$fieldId}]\n";
if ($correctValue !== null) {
    echo "  ✅ Found: '{$correctValue}'\n";
} else {
    echo "  ❌ Not found\n";
}

// Test alternative approaches
echo "\n=== Alternative Approaches ===\n";

// Approach 1: Check if fields array exists and has the field ID
if ($request->has('fields') && isset($request->input('fields')[$fieldId])) {
    $value1 = $request->input('fields')[$fieldId];
    echo "Approach 1 - Direct array access: ✅ '{$value1}'\n";
} else {
    echo "Approach 1 - Direct array access: ❌ Not found\n";
}

// Approach 2: Use Laravel's array input method
$fieldsArray = $request->input('fields', []);
if (isset($fieldsArray[$fieldId])) {
    $value2 = $fieldsArray[$fieldId];
    echo "Approach 2 - Array input method: ✅ '{$value2}'\n";
} else {
    echo "Approach 2 - Array input method: ❌ Not found\n";
}

echo "\n=== Conclusion ===\n";
echo "The issue is that the controller uses dot notation 'fields.{$fieldId}'\n";
echo "but the URL sends array notation 'fields[{$fieldId}]'\n";
echo "Laravel should handle this automatically, but there might be a version issue\n";
echo "or the controller needs to be updated to use the correct access method.\n";