<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Document;
use App\Models\DocumentSection;
use App\Models\DocumentCustomField;
use Illuminate\Http\Request;

echo "🔍 اختبار الإصلاح للفلترة:\n\n";

// محاكاة الطلب
$queryString = 'search=&sort=latest&per_page=12&fields%5B9%5D=%D8%B5%D8%AD%D8%A7%D9%81%D8%A9&fields%5B10%5D=%D8%A7%D9%84%D8%AF%D9%88%D9%84%D8%A9+%D9%88%D9%86%D8%B8%D8%A7%D9%85+%D8%A7%D9%84%D8%AD%D9%83%D9%85&fields%5B6%5D=&fields%5B3%5D=&fields%5B2%5D%5Bday%5D=&fields%5B2%5D%5Bmonth%5D=&fields%5B2%5D%5Byear%5D=';

parse_str($queryString, $params);

// إنشاء كائن Request
$request = new Request($params);

// الحصول على القسم
$section = DocumentSection::where('slug', 'koanyn')->first();
echo "📂 القسم: {$section->name}\n\n";

// الحصول على الحقول المخصصة
$customFields = $section->customFields()->active()->orderBy('sort_order')->get();

// بناء الاستعلام
$query = $section->documents()
    ->with(['user'])
    ->published()
    ->latest('published_at');

echo "🔍 تطبيق الفلاتر (الكود المُحدث):\n";
$appliedFilters = [];

foreach ($customFields as $field) {
    if ($field->type === 'date') {
        // معالجة حقول التاريخ
        $dayValue = $request->input("fields.{$field->id}.day");
        $monthValue = $request->input("fields.{$field->id}.month");
        $yearValue = $request->input("fields.{$field->id}.year");
        
        $hasValidDay = !empty($dayValue) && $dayValue !== '';
        $hasValidMonth = !empty($monthValue) && $monthValue !== '';
        $hasValidYear = !empty($yearValue) && $yearValue !== '';
        
        if ($hasValidDay || $hasValidMonth || $hasValidYear) {
            echo "  ✅ تطبيق فلتر التاريخ للحقل {$field->id}\n";
            $appliedFilters[$field->id] = [
                'day' => $hasValidDay ? $dayValue : null,
                'month' => $hasValidMonth ? $monthValue : null,
                'year' => $hasValidYear ? $yearValue : null
            ];
        }
    }
    elseif ($field->type === 'number') {
        // معالجة حقول الأرقام
        $minValue = $request->input("fields.{$field->id}.min");
        $maxValue = $request->input("fields.{$field->id}.max");
        
        $hasValidMin = !empty($minValue) && $minValue !== '';
        $hasValidMax = !empty($maxValue) && $maxValue !== '';
        
        if ($hasValidMin || $hasValidMax) {
            echo "  ✅ تطبيق فلتر الرقم للحقل {$field->id}\n";
            $appliedFilters[$field->id] = [
                'min' => $hasValidMin ? $minValue : null,
                'max' => $hasValidMax ? $maxValue : null
            ];
        }
    }
    else {
        // معالجة باقي أنواع الحقول (الكود المُحدث)
        $fieldKey = "fields.{$field->id}";
        $fieldArrayKey = "fields[{$field->id}]";
        
        // التحقق من وجود البيانات بالصيغتين
        $filterValue = $request->input($fieldArrayKey) ?? $request->input($fieldKey);
        
        echo "  🏷️ الحقل {$field->id} ({$field->label}): '{$filterValue}'\n";
        
        // التحقق من أن القيمة ليست فارغة
        if (!empty($filterValue) && $filterValue !== '') {
            echo "    ✅ تطبيق الفلتر\n";
            $appliedFilters[$field->id] = $filterValue;
            
            $query->whereHas('fieldValues', function($q) use ($field, $filterValue) {
                $q->where('field_id', $field->id);
                
                if ($field->type === 'multiselect' || $field->type === 'text' || $field->type === 'textarea') {
                    // تنظيف قيمة البحث من المسافات الإضافية
                    $cleanFilterValue = trim($filterValue);
                    echo "    📝 استخدام LIKE search: '%{$cleanFilterValue}%'\n";
                    // البحث بالنص كما هو (كلمة واحدة أو عبارة كاملة)
                    $q->where('value', 'like', "%{$cleanFilterValue}%");
                } else {
                    echo "    📝 استخدام exact match: '{$filterValue}'\n";
                    $q->where('value', $filterValue);
                }
            });
        } else {
            echo "    ❌ القيمة فارغة - لا يتم تطبيق الفلتر\n";
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
    echo "\n✅ الإصلاح نجح! الفلترة تعمل الآن بشكل صحيح.\n";
} else {
    echo "❌ لا تزال هناك مشكلة في الفلترة\n";
}