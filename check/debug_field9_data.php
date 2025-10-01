<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\DocumentFieldValue;
use App\Models\Document;
use App\Models\DocumentSection;

echo "=== فحص البيانات في الحقل رقم 9 ===\n";

// البحث عن جميع القيم في الحقل رقم 9
$fieldValues = DocumentFieldValue::where('field_id', 9)->get();

echo "عدد القيم الموجودة في الحقل رقم 9: " . $fieldValues->count() . "\n\n";

foreach ($fieldValues as $value) {
    echo "Document ID: {$value->document_id}\n";
    echo "القيمة: '{$value->value}'\n";
    echo "طول النص: " . strlen($value->value) . " حرف\n";
    echo "القيمة مع عرض الأحرف الخفية: " . json_encode($value->value) . "\n";
    echo "---\n";
}

echo "\n=== اختبار البحث بكلمة 'صحافة' ===\n";

// اختبار البحث بكلمة صحافة
$searchTerm = 'صحافة';
$matchingValues = DocumentFieldValue::where('field_id', 9)
    ->where('value', 'like', "%{$searchTerm}%")
    ->get();

echo "عدد النتائج للبحث بـ '{$searchTerm}': " . $matchingValues->count() . "\n";

foreach ($matchingValues as $value) {
    echo "Document ID: {$value->document_id}\n";
    echo "القيمة الكاملة: '{$value->value}'\n";
    echo "---\n";
}

echo "\n=== اختبار البحث في القسم koanyn ===\n";

// البحث في القسم koanyn
$section = DocumentSection::where('slug', 'koanyn')->first();
if ($section) {
    echo "تم العثور على القسم: {$section->name}\n";
    
    $documentsInSection = Document::where('section_id', $section->id)
        ->whereHas('fieldValues', function($q) use ($searchTerm) {
            $q->where('field_id', 9)
              ->where('value', 'like', "%{$searchTerm}%");
        })
        ->get();
    
    echo "عدد الوثائق في القسم مع كلمة '{$searchTerm}': " . $documentsInSection->count() . "\n";
    
    foreach ($documentsInSection as $doc) {
        echo "وثيقة: {$doc->title}\n";
        $fieldValue = $doc->fieldValues()->where('field_id', 9)->first();
        if ($fieldValue) {
            echo "قيمة الحقل: '{$fieldValue->value}'\n";
        }
        echo "---\n";
    }
} else {
    echo "لم يتم العثور على القسم koanyn\n";
}