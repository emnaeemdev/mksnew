<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\DocumentSection;
use App\Models\DocumentCustomField;

echo "=== Testing Request Processing ===\n\n";

// Simulate the exact request
$queryString = 'search=&sort=latest&per_page=12&fields%5B9%5D=%D8%B5%D8%AD%D8%A7%D9%81%D8%A9&fields%5B10%5D=%D8%A7%D9%84%D8%AF%D9%88%D9%84%D8%A9+%D9%88%D9%86%D8%B8%D8%A7%D9%85+%D8%A7%D9%84%D8%AD%D9%83%D9%85&fields%5B6%5D=&fields%5B3%5D=&fields%5B2%5D%5Bday%5D=&fields%5B2%5D%5Bmonth%5D=&fields%5B2%5D%5Byear%5D=';

echo "Query String: {$queryString}\n\n";

// Parse query string
parse_str($queryString, $params);
echo "Parsed Parameters:\n";
print_r($params);

// Create a mock request
$request = Request::create('/test', 'GET', $params);

echo "\n=== Testing Request Input Methods ===\n";

// Test different ways to access the field
echo "Using input('fields.9'): '" . $request->input('fields.9') . "'\n";
echo "Using input('fields')[9]: '" . ($request->input('fields')[9] ?? 'NOT SET') . "'\n";
echo "Using get('fields')[9]: '" . ($request->get('fields')[9] ?? 'NOT SET') . "'\n";

$field9Value = $request->input('fields.9');
echo "\nField 9 value: '{$field9Value}'\n";
echo "Field 9 value (hex): " . bin2hex($field9Value ?? '') . "\n";
echo "Field 9 value is empty: " . (empty($field9Value) ? 'YES' : 'NO') . "\n";
echo "Field 9 value !== '': " . ($field9Value !== '' ? 'YES' : 'NO') . "\n";

// Test the exact condition from controller
if (!empty($field9Value) && $field9Value !== '') {
    echo "\nController condition PASSED - Filter would be applied\n";
    
    // Test the exact query
    $section = DocumentSection::where('slug', 'koanyn')->first();
    $customField = DocumentCustomField::find(9);
    
    if ($section && $customField) {
        echo "\nTesting exact controller query...\n";
        
        $query = $section->documents()->published();
        
        $query->whereHas('fieldValues', function($q) use ($customField, $field9Value) {
            $q->where('field_id', $customField->id);
            
            if ($customField->type === 'multiselect' || $customField->type === 'text' || $customField->type === 'textarea') {
                $cleanFilterValue = trim($field9Value);
                echo "Clean filter value: '{$cleanFilterValue}'\n";
                echo "Clean filter value (hex): " . bin2hex($cleanFilterValue) . "\n";
                
                $q->where('value', 'like', "%{$cleanFilterValue}%");
            } else {
                $q->where('value', $field9Value);
            }
        });
        
        $count = $query->count();
        echo "Documents found with controller logic: {$count}\n";
        
        if ($count > 0) {
            $documents = $query->get();
            foreach ($documents as $doc) {
                echo "  - Document ID: {$doc->id}, Title: {$doc->title}\n";
            }
        }
    }
} else {
    echo "\nController condition FAILED - Filter would NOT be applied\n";
    echo "empty() result: " . (empty($field9Value) ? 'true' : 'false') . "\n";
    echo "!== '' result: " . ($field9Value !== '' ? 'true' : 'false') . "\n";
}

echo "\n=== Test completed ===\n";
?>