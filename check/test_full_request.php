<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Document;
use App\Models\DocumentSection;
use App\Models\DocumentCustomField;
use Illuminate\Http\Request;

echo "🔍 اختبار الطلب الكامل مع جميع المعاملات:\n\n";

// محاكاة الطلب الكامل
$queryString = 'search=&sort=latest&per_page=12&fields%5B9%5D=%D8%B5%D8%AD%D8%A7%D9%81%D8%A9&fields%5B10%5D=%D8%A7%D9%84%D8%AF%D9%88%D9%84%D8%A9+%D9%88%D9%86%D8%B8%D8%A7%D9%85+%D8%A7%D9%84%D8%AD%D9%83%D9%85&fields%5B6%5D=&fields%5B3%5D=&fields%5B2%5D%5Bday%5D=&fields%5B2%5D%5Bmonth%5D=&fields%5B2%5D%5Byear%5D=';

parse_str($queryString, $params);

echo "📋 معاملات الطلب:\n";
print_r($params);
echo "\n";

// الحصول على القسم
$section = DocumentSection::where('slug', 'koanyn')->first();
echo "📂 القسم: {$section->name}\n\n";

// الحصول على الحقول المخصصة
$customFields = $section->customFields()->active()->orderBy('sort_order')->get();
echo "🏷️ الحقول المخصصة:\n";
foreach ($customFields as $field) {
    echo "  - الحقل {$field->id}: {$field->label} ({$field->type})\n";
}
echo "\n";

// بناء الاستعلام
$query = $section->documents()
    ->with(['user'])
    ->published()
    ->latest('published_at');

echo "🔍 معالجة الفلاتر:\n";
$appliedFilters = [];

foreach ($customFields as $field) {
    echo "\n🏷️ معالجة الحقل {$field->id} ({$field->label}):\n";
    
    if ($field->type === 'date') {
        // معالجة حقول التاريخ
        $dayValue = $params['fields'][$field->id]['day'] ?? null;
        $monthValue = $params['fields'][$field->id]['month'] ?? null;
        $yearValue = $params['fields'][$field->id]['year'] ?? null;
        
        echo "  التاريخ: يوم={$dayValue}, شهر={$monthValue}, سنة={$yearValue}\n";
        
        $hasValidDay = !empty($dayValue) && $dayValue !== '';
        $hasValidMonth = !empty($monthValue) && $monthValue !== '';
        $hasValidYear = !empty($yearValue) && $yearValue !== '';
        
        if ($hasValidDay || $hasValidMonth || $hasValidYear) {
            echo "  ✅ تطبيق فلتر التاريخ\n";
            // تطبيق فلتر التاريخ...
        } else {
            echo "  ❌ لا يوجد قيم صالحة للتاريخ\n";
        }
    }
    elseif ($field->type === 'number') {
        // معالجة حقول الأرقام
        $minValue = $params['fields'][$field->id]['min'] ?? null;
        $maxValue = $params['fields'][$field->id]['max'] ?? null;
        
        echo "  الرقم: min={$minValue}, max={$maxValue}\n";
        
        $hasValidMin = !empty($minValue) && $minValue !== '';
        $hasValidMax = !empty($maxValue) && $maxValue !== '';
        
        if ($hasValidMin || $hasValidMax) {
            echo "  ✅ تطبيق فلتر الرقم\n";
        } else {
            echo "  ❌ لا يوجد قيم صالحة للرقم\n";
        }
    }
    else {
        // معالجة باقي أنواع الحقول
        $filterValue = $params['fields'][$field->id] ?? null;
        
        echo "  القيمة الأصلية: '{$filterValue}'\n";
        echo "  Hex الأصلي: " . bin2hex($filterValue ?? '') . "\n";
        
        // تطبيق معالجة UTF-8 كما في الكنترولر
        if (!empty($filterValue) && is_string($filterValue)) {
            $originalValue = $filterValue;
            
            // Ensure proper UTF-8 encoding
            $filterValue = mb_convert_encoding($filterValue, 'UTF-8', 'UTF-8');
            
            // Alternative: try to fix double-encoded UTF-8
            if (mb_check_encoding($filterValue, 'UTF-8') === false) {
                $filterValue = utf8_decode($filterValue);
                $filterValue = utf8_encode($filterValue);
            }
            
            echo "  القيمة بعد معالجة UTF-8: '{$filterValue}'\n";
            echo "  Hex بعد المعالجة: " . bin2hex($filterValue) . "\n";
            echo "  تغيرت القيمة: " . ($originalValue !== $filterValue ? '✅ نعم' : '❌ لا') . "\n";
        }
        
        // التحقق من أن القيمة ليست فارغة
        if (!empty($filterValue) && $filterValue !== '') {
            echo "  ✅ تطبيق الفلتر\n";
            $appliedFilters[$field->id] = $filterValue;
            
            // تطبيق الفلتر على الاستعلام
            $query->whereHas('fieldValues', function($q) use ($field, $filterValue) {
                $q->where('field_id', $field->id);
                
                if ($field->type === 'multiselect' || $field->type === 'text' || $field->type === 'textarea') {
                    $cleanFilterValue = trim($filterValue);
                    $q->where('value', 'like', "%{$cleanFilterValue}%");
                } else {
                    $q->where('value', $filterValue);
                }
            });
        } else {
            echo "  ❌ القيمة فارغة - لا يتم تطبيق الفلتر\n";
        }
    }
}

echo "\n📊 الفلاتر المطبقة:\n";
print_r($appliedFilters);

echo "\n🔍 تنفيذ الاستعلام:\n";
$documents = $query->get();
echo "عدد الوثائق: " . $documents->count() . "\n";

if ($documents->count() > 0) {
    echo "\n📄 الوثائق:\n";
    foreach ($documents as $doc) {
        echo "  - {$doc->title} (ID: {$doc->id})\n";
    }
} else {
    echo "❌ لم يتم العثور على وثائق\n";
}