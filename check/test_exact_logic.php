<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

use App\Models\DocumentSection;
use App\Models\Document;
use App\Models\CustomField;
use App\Models\DocumentFieldValue;

echo "=== اختبار المنطق الدقيق من DocumentController ===\n";

// العثور على القسم
$section = DocumentSection::where('slug', 'koanyn')->first();
if (!$section) {
    echo "خطأ: لم يتم العثور على القسم koanyn\n";
    exit;
}

echo "القسم: {$section->name} (ID: {$section->id})\n";

// الحصول على الحقول المخصصة للقسم
$customFields = $section->customFields()->active()->get();
echo "\nالحقول المخصصة للقسم:\n";
foreach ($customFields as $field) {
    echo "- الحقل {$field->id}: {$field->name} ({$field->type})\n";
}

// العثور على الحقل 9
$field9 = $customFields->where('id', 9)->first();
if (!$field9) {
    echo "\nخطأ: لم يتم العثور على الحقل 9\n";
    exit;
}

echo "\nالحقل 9: {$field9->name} (نوع: {$field9->type})\n";

// بناء الاستعلام الأساسي
$query = Document::published()->where('section_id', $section->id);

echo "\n=== تطبيق الفلتر كما في الكود الأصلي ===\n";

$filterValue = 'صحافة';
echo "قيمة الفلتر: '{$filterValue}'\n";

// تطبيق نفس المنطق من الكود الأصلي
if ($field9->type === 'multiselect' || $field9->type === 'text' || $field9->type === 'textarea') {
    echo "نوع الحقل يتطلب بحث LIKE متعدد\n";
    
    $cleanFilterValue = trim($filterValue);
    echo "القيمة بعد التنظيف: '{$cleanFilterValue}'\n";
    
    // تطبيق نفس الاستعلام
    $query->whereHas('fieldValues', function($q) use ($field9, $cleanFilterValue) {
        $q->where('field_id', $field9->id);
        
        $q->where(function($subQ) use ($cleanFilterValue) {
            $subQ->where('value', 'like', "%{$cleanFilterValue}%")
                 ->orWhere('value', 'like', "% {$cleanFilterValue}%")
                 ->orWhere('value', 'like', "%{$cleanFilterValue} %")
                 ->orWhere('value', 'like', "%, {$cleanFilterValue}%")
                 ->orWhere('value', 'like', "%{$cleanFilterValue},%");
        });
    });
    
    echo "\nأنماط البحث المطبقة:\n";
    echo "1. %{$cleanFilterValue}%\n";
    echo "2. % {$cleanFilterValue}%\n";
    echo "3. %{$cleanFilterValue} %\n";
    echo "4. %, {$cleanFilterValue}%\n";
    echo "5. %{$cleanFilterValue},%\n";
    
} else {
    echo "نوع الحقل يتطلب مطابقة دقيقة\n";
    $query->whereHas('fieldValues', function($q) use ($field9, $filterValue) {
        $q->where('field_id', $field9->id)
          ->where('value', $filterValue);
    });
}

// الحصول على SQL
$sql = $query->toSql();
$bindings = $query->getBindings();

echo "\n=== SQL المُولد ===\n";
echo "SQL: {$sql}\n";
echo "Bindings: " . json_encode($bindings, JSON_UNESCAPED_UNICODE) . "\n";

// تنفيذ الاستعلام
$documents = $query->get();

echo "\n=== النتائج ===\n";
echo "عدد الوثائق: " . $documents->count() . "\n";

foreach ($documents as $doc) {
    echo "- {$doc->title} (ID: {$doc->id})\n";
    
    // عرض قيم الحقل 9
    $fieldValues = DocumentFieldValue::where('document_id', $doc->id)
        ->where('field_id', 9)
        ->get();
    
    foreach ($fieldValues as $fv) {
        echo "  الحقل 9: '{$fv->value}'\n";
    }
}

// اختبار كل نمط بحث منفصل
echo "\n=== اختبار كل نمط بحث منفصل ===\n";

$patterns = [
    "%{$cleanFilterValue}%",
    "% {$cleanFilterValue}%",
    "%{$cleanFilterValue} %",
    "%, {$cleanFilterValue}%",
    "%{$cleanFilterValue},%"
];

foreach ($patterns as $i => $pattern) {
    echo "\nنمط " . ($i + 1) . ": '{$pattern}'\n";
    
    $testResults = DocumentFieldValue::where('field_id', 9)
        ->where('value', 'like', $pattern)
        ->get();
    
    echo "عدد النتائج: " . $testResults->count() . "\n";
    
    foreach ($testResults as $result) {
        echo "  - الوثيقة {$result->document_id}: '{$result->value}'\n";
    }
}

// اختبار بسيط
echo "\n=== اختبار بسيط ===\n";
$simpleResults = DocumentFieldValue::where('field_id', 9)
    ->where('value', 'like', "%صحافة%")
    ->get();

echo "نتائج البحث البسيط (%صحافة%): " . $simpleResults->count() . "\n";
foreach ($simpleResults as $result) {
    echo "  - الوثيقة {$result->document_id}: '{$result->value}'\n";
}