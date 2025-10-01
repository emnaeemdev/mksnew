<?php

// Compare direct database filtering vs web interface filtering
// This will help identify where the discrepancy occurs

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

echo "=== Direct Database vs Web Interface Comparison ===\n";

// Test 1: Direct database query (like our previous successful tests)
echo "\n=== Test 1: Direct Database Query ===\n";

try {
    // Get the section
    $section = \App\Models\DocumentSection::where('slug', 'koanyn')->first();
    
    if (!$section) {
        echo "❌ Section 'koanyn' not found\n";
        exit(1);
    }
    
    echo "✅ Section found: {$section->name}\n";
    
    // Get field 9
    $field = \App\Models\DocumentCustomField::where('section_id', $section->id)
        ->where('field_order', 9)
        ->first();
    
    if (!$field) {
        echo "❌ Field 9 not found\n";
        exit(1);
    }
    
    echo "✅ Field 9 found: {$field->label}\n";
    
    // Direct query with exact same logic as controller
    $query = \App\Models\Document::where('section_id', $section->id)
        ->published();
    
    // Apply field filter with exact same logic
    $searchValue = 'صحافة';
    $cleanValue = trim($searchValue);
    
    $query->whereHas('customFieldValues', function($q) use ($field, $cleanValue) {
        $q->where('custom_field_id', $field->id)
          ->where(function($subQ) use ($cleanValue) {
              $subQ->where('value', 'LIKE', "%{$cleanValue}%")
                   ->orWhere('value', 'LIKE', "%{$cleanValue},%")
                   ->orWhere('value', 'LIKE', "%, {$cleanValue}%")
                   ->orWhere('value', 'LIKE', "%, {$cleanValue},%");
          });
    });
    
    $directResults = $query->get();
    echo "Direct query results: {$directResults->count()} documents\n";
    
    foreach ($directResults as $doc) {
        echo "  - {$doc->title}\n";
        
        // Show the field value
        $fieldValue = $doc->customFieldValues
            ->where('custom_field_id', $field->id)
            ->first();
        if ($fieldValue) {
            echo "    Field value: '{$fieldValue->value}'\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Direct query error: " . $e->getMessage() . "\n";
}

// Test 2: Simulate exact controller logic
echo "\n=== Test 2: Simulating Controller Logic ===\n";

try {
    // Simulate the exact request parameters
    $request = new \Illuminate\Http\Request();
    $request->merge([
        'fields' => [
            '9' => 'صحافة'
        ]
    ]);
    
    echo "Request fields: " . json_encode($request->get('fields'), JSON_UNESCAPED_UNICODE) . "\n";
    
    // Get section again
    $section = \App\Models\DocumentSection::where('slug', 'koanyn')->first();
    
    // Start with base query
    $query = \App\Models\Document::where('section_id', $section->id)
        ->published();
    
    echo "Base query count (before filters): " . $query->count() . "\n";
    
    // Apply filters exactly like the controller
    $fields = $request->get('fields', []);
    
    if (!empty($fields)) {
        echo "Applying filters...\n";
        
        foreach ($fields as $fieldOrder => $value) {
            if (!empty($value)) {
                echo "  Processing field {$fieldOrder} with value: '{$value}'\n";
                
                $customField = \App\Models\DocumentCustomField::where('section_id', $section->id)
                    ->where('field_order', $fieldOrder)
                    ->first();
                
                if ($customField) {
                    echo "    Field found: {$customField->label} (type: {$customField->type})\n";
                    
                    if (in_array($customField->type, ['multiselect', 'text', 'textarea'])) {
                        $cleanValue = trim($value);
                        echo "    Clean value: '{$cleanValue}'\n";
                        
                        $query->whereHas('customFieldValues', function($q) use ($customField, $cleanValue) {
                            $q->where('custom_field_id', $customField->id)
                              ->where(function($subQ) use ($cleanValue) {
                                  $subQ->where('value', 'LIKE', "%{$cleanValue}%")
                                       ->orWhere('value', 'LIKE', "%{$cleanValue},%")
                                       ->orWhere('value', 'LIKE', "%, {$cleanValue}%")
                                       ->orWhere('value', 'LIKE', "%, {$cleanValue},%");
                              });
                        });
                        
                        echo "    Filter applied\n";
                    } else {
                        echo "    Field type '{$customField->type}' not supported for LIKE search\n";
                    }
                } else {
                    echo "    ❌ Custom field not found for order {$fieldOrder}\n";
                }
            }
        }
    }
    
    $controllerResults = $query->get();
    echo "Controller simulation results: {$controllerResults->count()} documents\n";
    
    foreach ($controllerResults as $doc) {
        echo "  - {$doc->title}\n";
    }
    
} catch (Exception $e) {
    echo "❌ Controller simulation error: " . $e->getMessage() . "\n";
}

// Test 3: Check if there are any documents in the section at all
echo "\n=== Test 3: Section Document Count ===\n";

try {
    $totalDocs = \App\Models\Document::where('section_id', $section->id)->count();
    $publishedDocs = \App\Models\Document::where('section_id', $section->id)->published()->count();
    
    echo "Total documents in section: {$totalDocs}\n";
    echo "Published documents in section: {$publishedDocs}\n";
    
    // Check field 9 values
    $field9Values = \App\Models\DocumentCustomFieldValue::where('custom_field_id', $field->id)
        ->get();
    
    echo "Field 9 values in database:\n";
    foreach ($field9Values as $value) {
        echo "  - Document {$value->document_id}: '{$value->value}'\n";
    }
    
} catch (Exception $e) {
    echo "❌ Section check error: " . $e->getMessage() . "\n";
}

echo "\n=== Comparison Complete ===\n";