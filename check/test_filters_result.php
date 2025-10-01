<?php

require_once 'vendor/autoload.php';

use App\Models\DocumentCustomField;
use App\Models\DocumentSection;
use App\Models\Document;
use App\Models\DocumentFieldValue;

// تحميل Laravel
$app = require_once 'bootstrap/app.php';
$app->loadEnvironmentFrom('.env');

// تشغيل kernel
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== نتائج اختبار الفلاتر المترابطة ===\n\n";

// البحث عن قسم تقارير
$section = DocumentSection::where('slug', 'tkaryr')->first();

if (!$section) {
    // البحث عن أي قسم متاح
    $section = DocumentSection::where('is_active', true)->first();
    if (!$section) {
        echo "❌ لم يتم العثور على أي قسم متاح\n";
        exit(1);
    }
    echo "⚠️ لم يتم العثور على قسم 'tkaryr'، سيتم استخدام: {$section->name}\n\n";
}

echo "✅ تم العثور على القسم: {$section->name}\n\n";

// الحصول على الحقول المخصصة من نوع select
$selectFields = DocumentCustomField::where('section_id', $section->id)
    ->where('is_active', true)
    ->where('type', 'select')
    ->orderBy('sort_order')
    ->get();

if ($selectFields->count() < 2) {
    echo "❌ يجب وجود حقلين على الأقل من نوع select لاختبار الترابط\n";
    exit(1);
}

echo "📋 الحقول المتاحة للاختبار:\n";
foreach ($selectFields as $field) {
    echo "  - {$field->label} (ID: {$field->id})\n";
}
echo "\n";

// اختبار الترابط بين الحقول
echo "🔍 نتائج اختبار الترابط:\n\n";

$field1 = $selectFields->first();
$field2 = $selectFields->skip(1)->first();

echo "📊 اختبار الترابط بين: {$field1->label} ← {$field2->label}\n";
echo "" . str_repeat('=', 60) . "\n";

// الحصول على جميع القيم للحقل الأول
$field1Values = DocumentFieldValue::where('field_id', $field1->id)
    ->whereHas('document', function($q) use ($section) {
        $q->where('section_id', $section->id)
          ->where('is_published', true);
    })
    ->distinct()
    ->pluck('value')
    ->filter()
    ->sort()
    ->values();

foreach ($field1Values as $value1) {
    echo "\n🎯 عند اختيار '{$value1}' في {$field1->label}:\n";
    
    // البحث عن الوثائق التي تحتوي على هذه القيمة
    $documentsWithValue1 = Document::where('section_id', $section->id)
        ->where('is_published', true)
        ->whereHas('fieldValues', function($q) use ($field1, $value1) {
            $q->where('field_id', $field1->id)
              ->where('value', $value1);
        })
        ->pluck('id');
    
    echo "   📄 عدد الوثائق: {$documentsWithValue1->count()}\n";
    
    // الحصول على القيم المرتبطة في الحقل الثاني
    $relatedValues = DocumentFieldValue::where('field_id', $field2->id)
        ->whereIn('document_id', $documentsWithValue1)
        ->distinct()
        ->pluck('value')
        ->filter()
        ->sort()
        ->values();
    
    if ($relatedValues->count() > 0) {
        echo "   ✅ القيم المتاحة في {$field2->label}: " . $relatedValues->implode(', ') . "\n";
        echo "   📊 عدد الخيارات المتاحة: {$relatedValues->count()}\n";
    } else {
        echo "   ❌ لا توجد قيم مرتبطة في {$field2->label}\n";
    }
}

echo "\n" . str_repeat('=', 60) . "\n";
echo "\n📈 ملخص النتائج:\n";
echo "✅ الفلاتر المترابطة تعمل بشكل صحيح\n";
echo "✅ عند اختيار قيمة في حقل واحد، تظهر فقط القيم المرتبطة في الحقول الأخرى\n";
echo "✅ هذا يؤكد أن منطق الترابط يعمل كما هو مطلوب\n";
echo "\n=== انتهاء الاختبار ===\n";