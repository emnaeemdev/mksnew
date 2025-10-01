<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Document;
use App\Models\DocumentSection;
use Illuminate\Support\Facades\DB;

echo "=== Testing Encoding Issue ===\n\n";

// Test the exact filter value from URL
$urlFilterValue = urldecode('%D8%B5%D8%AD%D8%A7%D9%81%D8%A9'); // صحافة
echo "URL Filter Value: " . $urlFilterValue . "\n";
echo "URL Filter Value (hex): " . bin2hex($urlFilterValue) . "\n\n";

// Get section
$section = DocumentSection::where('slug', 'koanyn')->first();
if (!$section) {
    echo "Section 'koanyn' not found!\n";
    exit(1);
}

echo "Section found: {$section->title}\n\n";

// Test direct database query for field 9 values
echo "=== Field 9 Values in Database ===\n";
$field9Values = DB::table('document_field_values')
    ->where('field_id', 9)
    ->select('value', 'document_id')
    ->get();

foreach ($field9Values as $value) {
    echo "Document {$value->document_id}: '{$value->value}' (hex: " . bin2hex($value->value) . ")\n";
    
    // Test if this value contains our search term
    $contains = strpos($value->value, $urlFilterValue) !== false;
    $containsLike = stripos($value->value, $urlFilterValue) !== false;
    echo "  Contains '{$urlFilterValue}': " . ($contains ? 'YES' : 'NO') . "\n";
    echo "  Contains (case-insensitive): " . ($containsLike ? 'YES' : 'NO') . "\n";
    
    // Test LIKE pattern
    $likePattern = "%{$urlFilterValue}%";
    $likeTest = DB::table('document_field_values')
        ->where('field_id', 9)
        ->where('document_id', $value->document_id)
        ->where('value', 'like', $likePattern)
        ->exists();
    echo "  LIKE pattern matches: " . ($likeTest ? 'YES' : 'NO') . "\n\n";
}

// Test the exact query that should be executed
echo "=== Testing Exact Query ===\n";
$documents = $section->documents()
    ->published()
    ->whereHas('fieldValues', function($q) use ($urlFilterValue) {
        $q->where('field_id', 9)
          ->where('value', 'like', "%{$urlFilterValue}%");
    })
    ->get();

echo "Documents found with exact query: " . $documents->count() . "\n";

if ($documents->count() > 0) {
    foreach ($documents as $doc) {
        echo "  - Document ID: {$doc->id}, Title: {$doc->title}\n";
    }
}

// Test with different encoding approaches
echo "\n=== Testing Different Encoding Approaches ===\n";

$testValues = [
    'original' => $urlFilterValue,
    'mb_convert' => mb_convert_encoding($urlFilterValue, 'UTF-8', 'UTF-8'),
    'utf8_decode_encode' => utf8_encode(utf8_decode($urlFilterValue)),
    'html_entity_decode' => html_entity_decode($urlFilterValue, ENT_QUOTES, 'UTF-8')
];

foreach ($testValues as $method => $testValue) {
    echo "Testing {$method}: '{$testValue}' (hex: " . bin2hex($testValue) . ")\n";
    
    $count = $section->documents()
        ->published()
        ->whereHas('fieldValues', function($q) use ($testValue) {
            $q->where('field_id', 9)
              ->where('value', 'like', "%{$testValue}%");
        })
        ->count();
    
    echo "  Documents found: {$count}\n\n";
}

echo "=== Test completed ===\n";
?>