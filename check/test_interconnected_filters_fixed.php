<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use App\Models\DocumentSection;
use App\Models\DocumentCustomField;
use App\Models\DocumentFieldValue;
use App\Models\Document;

// تحميل Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->loadEnvironmentFrom('.env');
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== اختبار الفلاتر المترابطة بعد الإصلاح ===\n\n";

// البحث عن قسم للاختبار
$section = DocumentSection::where('is_active', true)->first();

if (!$section) {
    echo "لم يتم العثور على أقسام نشطة\n";
    exit(1);
}

echo "القسم المستخدم: {$section->name}\n\n";

// الحصول على الحقول المخصصة من نوع select
$selectFields = $section->customFields()
    ->where('is_active', true)
    ->where('type', 'select')
    ->orderBy('sort_order')
    ->get();

if ($selectFields->count() < 2) {
    echo "يحتاج القسم إلى حقلين على الأقل من نوع select للاختبار\n";
    exit(1);
}

echo "الحقول المتاحة للاختبار:\n";
foreach ($selectFields as $field) {
    echo "- {$field->name} (ID: {$field->id})\n";
    if ($field->options) {
        echo "  الخيارات: " . implode(', ', $field->options) . "\n";
    }
}
echo "\n";

// اختبار الترابط بين أول حقلين
$field1 = $selectFields->first();
$field2 = $selectFields->skip(1)->first();

echo "=== اختبار الترابط بين '{$field1->name}' و '{$field2->name}' ===\n\n";

// اختيار أول قيمة من الحقل الأول
if ($field1->options && count($field1->options) > 0) {
    $selectedValue = $field1->options[0];
    echo "اختيار القيمة '{$selectedValue}' من حقل '{$field1->name}'\n\n";
    
    // البحث عن الوثائق التي تحتوي على هذه القيمة
    $documentsWithValue = $section->documents()
        ->published()
        ->whereHas('fieldValues', function($q) use ($field1, $selectedValue) {
            $q->where('field_id', $field1->id)
              ->where('value', $selectedValue);
        })
        ->pluck('id');
    
    echo "عدد الوثائق التي تحتوي على هذه القيمة: " . $documentsWithValue->count() . "\n\n";
    
    if ($documentsWithValue->count() > 0) {
        // الحصول على القيم المتاحة في الحقل الثاني للوثائق المفلترة
        $availableValuesInField2 = DocumentFieldValue::where('field_id', $field2->id)
            ->whereIn('document_id', $documentsWithValue)
            ->distinct()
            ->pluck('value')
            ->toArray();
        
        echo "القيم المتاحة في حقل '{$field2->name}' بعد تطبيق الفلتر:\n";
        if (empty($availableValuesInField2)) {
            echo "- لا توجد قيم متاحة\n";
        } else {
            foreach ($availableValuesInField2 as $value) {
                // حساب عدد الوثائق لكل قيمة
                $count = DocumentFieldValue::where('field_id', $field2->id)
                    ->where('value', $value)
                    ->whereIn('document_id', $documentsWithValue)
                    ->count();
                echo "- {$value} ({$count} وثيقة)\n";
            }
        }
        
        echo "\n";
        
        // مقارنة مع جميع القيم المتاحة في الحقل الثاني
        $allValuesInField2 = $field2->options ?: [];
        echo "جميع القيم المتاحة في حقل '{$field2->name}' (بدون فلتر):\n";
        foreach ($allValuesInField2 as $value) {
            $totalCount = DocumentFieldValue::where('field_id', $field2->id)
                ->where('value', $value)
                ->whereHas('document', function($q) use ($section) {
                    $q->where('section_id', $section->id)
                      ->where('is_published', true);
                })
                ->count();
            
            $isAvailable = in_array($value, $availableValuesInField2);
            $status = $isAvailable ? '✓ متاح' : '✗ مخفي';
            echo "- {$value} ({$totalCount} وثيقة إجمالي) - {$status}\n";
        }
        
        echo "\n=== النتيجة ===\n";
        $hiddenCount = count($allValuesInField2) - count($availableValuesInField2);
        echo "تم إخفاء {$hiddenCount} من أصل " . count($allValuesInField2) . " خيارات\n";
        
        if ($hiddenCount > 0) {
            echo "✓ الفلاتر المترابطة تعمل بشكل صحيح!\n";
        } else {
            echo "⚠ لا يوجد ترابط واضح بين الحقول\n";
        }
    } else {
        echo "لا توجد وثائق تحتوي على القيمة المحددة\n";
    }
} else {
    echo "الحقل الأول لا يحتوي على خيارات\n";
}

echo "\n=== انتهاء الاختبار ===\n";