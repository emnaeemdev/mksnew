<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\DocumentSection;
use App\Models\Document;
use App\Models\DocumentFieldValue;
use Illuminate\Http\Request;

echo "🔍 اختبار تفصيلي للفلاتر\n";
echo str_repeat('=', 60) . "\n\n";

// الحصول على القسم
$section = DocumentSection::where('slug', 'koanyn')->first();
$customFields = $section->customFields()->active()->orderBy('sort_order')->get();

echo "📁 القسم: {$section->title}\n";
echo "🔢 عدد الحقول المخصصة: {$customFields->count()}\n\n";

echo "📋 قائمة الحقول المخصصة:\n";
foreach ($customFields as $field) {
    echo "  🔢 الحقل {$field->id}: {$field->label} (نوع: {$field->type})\n";
    if ($field->type === 'select' && $field->options) {
        echo "    📋 الخيارات: " . implode(', ', $field->options) . "\n";
    }
}
echo "\n";

// إنشاء طلب HTTP مع فلتر
$request = new Request();
$request->merge([
    'fields' => [
        '6' => 'ساري ومعدل'  // حالة القانون
    ]
]);

echo "🔍 فحص البيانات المرسلة:\n";
echo "  📋 fields: " . json_encode($request->input('fields'), JSON_UNESCAPED_UNICODE) . "\n";
echo "  🎯 الحقل 6: " . $request->input('fields.6') . "\n\n";

echo "🎯 الفلتر المطبق: الحقل 6 = 'ساري ومعدل'\n\n";

// دالة مساعدة لتطبيق جميع الفلاتر المحددة مع إمكانية استبعاد حقل معين
$applyAllFilters = function($query, $excludeFieldId = null) use ($customFields, $request) {
    echo "  🔧 تطبيق الفلاتر (استبعاد الحقل: {$excludeFieldId})\n";
    
    foreach ($customFields as $field) {
        // تخطي الحقل المستبعد
        if ($excludeFieldId && $field->id == $excludeFieldId) {
            echo "    ⏭️ تخطي الحقل {$field->id} ({$field->label})\n";
            continue;
        }
        
        $filterValue = $request->input("fields.{$field->id}");
        
        if (!empty($filterValue) && $filterValue !== '') {
            echo "    ✅ تطبيق فلتر الحقل {$field->id} ({$field->label}): {$filterValue}\n";
            $query->whereHas('fieldValues', function($q) use ($field, $filterValue) {
                $q->where('field_id', $field->id)
                  ->where('value', $filterValue);
            });
        } else {
            echo "    ⚪ لا يوجد فلتر للحقل {$field->id} ({$field->label})\n";
        }
    }
};

echo "📊 اختبار عدادات الحقول:\n\n";

// البحث عن حقل select للاختبار
$selectField = null;
foreach ($customFields as $field) {
    if ($field->type === 'select' && $field->options && $field->id != 6) {
        $selectField = $field;
        break;
    }
}

if ($selectField) {
    echo "🎯 اختبار الحقل {$selectField->id}: {$selectField->label}\n";
    echo "📋 الخيارات المتاحة: " . implode(', ', $selectField->options) . "\n\n";
    
    foreach ($selectField->options as $option) {
        echo "  🔍 اختبار الخيار: '{$option}'\n";
        
        // إنشاء استعلام جديد
        $countQuery = $section->documents()->published();
        
        echo "    📈 عدد الوثائق قبل تطبيق الفلاتر: " . $countQuery->count() . "\n";
        
        // تطبيق جميع الفلاتر عدا الحقل الحالي
        $applyAllFilters($countQuery, $selectField->id);
        
        echo "    📈 عدد الوثائق بعد تطبيق الفلاتر الأخرى: " . $countQuery->count() . "\n";
        
        // إضافة فلتر الخيار الحالي
        $count = $countQuery->whereHas('fieldValues', function($q) use ($selectField, $option) {
            $q->where('field_id', $selectField->id)
              ->where('value', $option);
        })->count();
        
        echo "    📈 عدد الوثائق النهائي للخيار '{$option}': {$count}\n";
        
        // الحصول على IDs الوثائق للتحقق
        $countQuery2 = $section->documents()->published();
        $applyAllFilters($countQuery2, $selectField->id);
        $documentIds = $countQuery2->whereHas('fieldValues', function($q) use ($selectField, $option) {
            $q->where('field_id', $selectField->id)
              ->where('value', $option);
        })->pluck('id')->toArray();
        
        echo "    📄 IDs الوثائق: " . implode(', ', $documentIds) . "\n\n";
    }
} else {
    echo "❌ لم يتم العثور على حقل select مناسب للاختبار\n";
}

echo "\n" . str_repeat('=', 60) . "\n";
echo "🏁 انتهى الاختبار\n";