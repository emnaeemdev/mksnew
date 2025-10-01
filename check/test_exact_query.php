<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Document;
use App\Models\DocumentSection;
use App\Models\DocumentCustomField;
use App\Models\DocumentFieldValue;

echo "🔍 اختبار الاستعلام الدقيق للفلترة:\n\n";

// الحصول على القسم
$section = DocumentSection::where('slug', 'koanyn')->first();
echo "📂 القسم: {$section->name} (ID: {$section->id})\n\n";

// الحصول على الحقل 9
$field = DocumentCustomField::find(9);
echo "🏷️ الحقل: {$field->label} (ID: {$field->id}, Type: {$field->type})\n\n";

// قيمة الفلتر
$filterValue = 'صحافة';
echo "🔎 قيمة الفلتر: {$filterValue}\n";
echo "🔢 Hex: " . bin2hex($filterValue) . "\n\n";

// محاكاة الاستعلام الدقيق من الكنترولر
echo "📊 محاكاة الاستعلام من الكنترولر:\n";

$query = $section->documents()->published();

// تطبيق الفلتر كما في الكنترولر
$query->whereHas('fieldValues', function($q) use ($field, $filterValue) {
    $q->where('field_id', $field->id);
    
    if ($field->type === 'multiselect' || $field->type === 'text' || $field->type === 'textarea') {
        $cleanFilterValue = trim($filterValue);
        $q->where('value', 'like', "%{$cleanFilterValue}%");
    } else {
        $q->where('value', $filterValue);
    }
});

// تنفيذ الاستعلام
$documents = $query->get();
echo "عدد الوثائق الموجودة: " . $documents->count() . "\n\n";

if ($documents->count() > 0) {
    echo "📄 الوثائق الموجودة:\n";
    foreach ($documents as $doc) {
        echo "  - {$doc->title} (ID: {$doc->id})\n";
        
        // عرض قيم الحقل 9 لهذه الوثيقة
        $fieldValues = $doc->fieldValues()->where('field_id', 9)->get();
        foreach ($fieldValues as $fv) {
            echo "    الحقل 9: {$fv->value} (Hex: " . bin2hex($fv->value) . ")\n";
        }
    }
} else {
    echo "❌ لم يتم العثور على وثائق\n";
    
    // فحص جميع قيم الحقل 9 في هذا القسم
    echo "\n🔍 جميع قيم الحقل 9 في هذا القسم:\n";
    $allValues = DocumentFieldValue::where('field_id', 9)
        ->whereHas('document', function($q) use ($section) {
            $q->where('section_id', $section->id)->where('is_published', true);
        })
        ->get();
    
    foreach ($allValues as $value) {
        echo "  - '{$value->value}' (Hex: " . bin2hex($value->value) . ")\n";
        
        // اختبار المطابقة
        $cleanFilterValue = trim($filterValue);
        $isMatch = strpos($value->value, $cleanFilterValue) !== false;
        echo "    مطابقة مع '{$filterValue}': " . ($isMatch ? '✅ نعم' : '❌ لا') . "\n";
    }
}

echo "\n🧪 اختبار مباشر للاستعلام SQL:\n";
$sql = "SELECT d.* FROM documents d 
        INNER JOIN document_field_values dfv ON d.id = dfv.document_id 
        WHERE d.section_id = ? 
        AND d.is_published = 1 
        AND dfv.field_id = 9 
        AND dfv.value LIKE ?";

$cleanFilterValue = trim($filterValue);
$likeValue = "%{$cleanFilterValue}%";

echo "SQL: {$sql}\n";
echo "Parameters: section_id={$section->id}, like_value='{$likeValue}'\n";

$results = \DB::select($sql, [$section->id, $likeValue]);
echo "نتائج SQL المباشر: " . count($results) . " وثيقة\n";