<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

use Illuminate\Http\Request;
use App\Models\DocumentSection;
use App\Models\Document;
use App\Models\CustomField;
use App\Models\DocumentFieldValue;

echo "=== تشخيص معالجة الطلب في DocumentController ===\n";

// محاكاة الطلب
$queryParams = [
    'search' => '',
    'sort' => 'latest',
    'per_page' => '12',
    'fields' => [
        '9' => 'صحافة',
        '10' => '',
        '6' => '',
        '3' => '',
        '2' => [
            'day' => '',
            'month' => '',
            'year' => ''
        ]
    ]
];

echo "البيانات المرسلة:\n";
print_r($queryParams);

// إنشاء Request object
$request = Request::create('/ar/documents/section/koanyn', 'GET', $queryParams);

echo "\n=== فحص Request object ===\n";
echo "request->input('fields'): ";
var_dump($request->input('fields'));
echo "request->input('fields.9'): '" . $request->input('fields.9') . "'\n";

// العثور على القسم
$section = DocumentSection::where('slug', 'koanyn')->first();
if (!$section) {
    echo "خطأ: لم يتم العثور على القسم koanyn\n";
    exit;
}

echo "\n=== معلومات القسم ===\n";
echo "اسم القسم: {$section->name}\n";
echo "ID القسم: {$section->id}\n";

// بناء الاستعلام
echo "\n=== بناء الاستعلام ===\n";
$query = Document::published()
    ->where('section_id', $section->id)
    ->with(['section', 'fieldValues.field']);

echo "الاستعلام الأساسي تم إنشاؤه\n";

// تطبيق فلاتر الحقول المخصصة
$customFields = $request->input('fields', []);
echo "\nالحقول المخصصة من الطلب:\n";
print_r($customFields);

if (!empty($customFields)) {
    foreach ($customFields as $fieldId => $filterValue) {
        echo "\n--- معالجة الحقل {$fieldId} ---\n";
        echo "القيمة الأصلية: '" . (is_array($filterValue) ? json_encode($filterValue) : $filterValue) . "'\n";
        
        // تنظيف القيمة
        if (is_array($filterValue)) {
            $cleanFilterValue = array_filter($filterValue, function($v) {
                return !empty(trim($v));
            });
            if (empty($cleanFilterValue)) {
                echo "تم تجاهل الحقل - قيم فارغة\n";
                continue;
            }
        } else {
            $cleanFilterValue = trim($filterValue);
            if (empty($cleanFilterValue)) {
                echo "تم تجاهل الحقل - قيمة فارغة\n";
                continue;
            }
        }
        
        echo "القيمة بعد التنظيف: '" . (is_array($cleanFilterValue) ? json_encode($cleanFilterValue) : $cleanFilterValue) . "'\n";
        
        // العثور على الحقل
        $field = CustomField::find($fieldId);
        if (!$field) {
            echo "تم تجاهل الحقل - غير موجود في قاعدة البيانات\n";
            continue;
        }
        
        echo "نوع الحقل: {$field->type}\n";
        echo "اسم الحقل: {$field->name}\n";
        
        // تطبيق الفلتر حسب نوع الحقل
        if ($field->type === 'date' && is_array($cleanFilterValue)) {
            echo "معالجة حقل التاريخ...\n";
            // منطق التاريخ
        } elseif (in_array($field->type, ['number']) && is_array($cleanFilterValue)) {
            echo "معالجة حقل الرقم...\n";
            // منطق الرقم
        } else {
            echo "معالجة حقل نصي/اختيار...\n";
            
            // فحص القيم الموجودة في هذا الحقل
            $existingValues = DocumentFieldValue::where('field_id', $fieldId)
                ->distinct()
                ->pluck('value')
                ->toArray();
            
            echo "القيم الموجودة في الحقل:\n";
            foreach ($existingValues as $val) {
                echo "  - '{$val}'\n";
            }
            
            // تطبيق الفلتر
            $query->whereHas('fieldValues', function($q) use ($fieldId, $cleanFilterValue) {
                $q->where('field_id', $fieldId)
                  ->where('value', 'like', "%{$cleanFilterValue}%");
            });
            
            echo "تم تطبيق فلتر LIKE: %{$cleanFilterValue}%\n";
            
            // اختبار الفلتر
            $testQuery = DocumentFieldValue::where('field_id', $fieldId)
                ->where('value', 'like', "%{$cleanFilterValue}%")
                ->get();
            
            echo "نتائج اختبار الفلتر:\n";
            foreach ($testQuery as $result) {
                echo "  - الوثيقة {$result->document_id}: '{$result->value}'\n";
            }
        }
    }
}

echo "\n=== تنفيذ الاستعلام النهائي ===\n";

// الحصول على SQL query
$sql = $query->toSql();
$bindings = $query->getBindings();

echo "SQL Query:\n{$sql}\n";
echo "\nBindings:\n";
print_r($bindings);

// تنفيذ الاستعلام
$documents = $query->get();

echo "\nعدد النتائج: " . $documents->count() . "\n";

if ($documents->count() > 0) {
    echo "\nالوثائق الموجودة:\n";
    foreach ($documents as $doc) {
        echo "- {$doc->title} (ID: {$doc->id})\n";
        
        // عرض قيم الحقول المخصصة
        $fieldValues = $doc->fieldValues->where('field_id', 9);
        foreach ($fieldValues as $fv) {
            echo "  الحقل 9: '{$fv->value}'\n";
        }
    }
} else {
    echo "\nلا توجد وثائق تطابق المعايير\n";
    
    // اختبار بدون فلاتر
    echo "\n=== اختبار بدون فلاتر ===\n";
    $allDocs = Document::published()
        ->where('section_id', $section->id)
        ->with(['fieldValues' => function($q) {
            $q->where('field_id', 9);
        }])
        ->get();
    
    echo "عدد الوثائق في القسم: " . $allDocs->count() . "\n";
    
    foreach ($allDocs as $doc) {
        echo "- {$doc->title}\n";
        foreach ($doc->fieldValues as $fv) {
            echo "  الحقل 9: '{$fv->value}'\n";
        }
    }
}