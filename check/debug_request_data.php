<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\DocumentFieldValue;
use App\Models\Document;
use App\Models\DocumentSection;
use Illuminate\Http\Request;

echo "=== محاكاة الطلب من الرابط ===\n";

// محاكاة البيانات من الرابط المرسل
$requestData = [
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
print_r($requestData);

echo "\n=== اختبار الفلترة ===\n";

// البحث عن القسم
$section = DocumentSection::where('slug', 'koanyn')->first();
if (!$section) {
    echo "خطأ: لم يتم العثور على القسم koanyn\n";
    exit;
}

echo "القسم: {$section->name}\n";

// بناء الاستعلام باستخدام scope method
$query = $section->documents()->published();

// تطبيق فلتر الحقل رقم 9
$filterValue = $requestData['fields']['9'];
echo "قيمة الفلتر للحقل 9: '{$filterValue}'\n";
echo "هل القيمة فارغة؟ " . (empty($filterValue) ? 'نعم' : 'لا') . "\n";
echo "هل القيمة تساوي فراغ؟ " . ($filterValue === '' ? 'نعم' : 'لا') . "\n";

if (!empty($filterValue) && $filterValue !== '') {
    echo "تطبيق الفلتر...\n";
    
    $query->whereHas('fieldValues', function($q) use ($filterValue) {
        $q->where('field_id', 9);
        
        // تنظيف قيمة البحث من المسافات الإضافية
        $cleanFilterValue = trim($filterValue);
        echo "القيمة بعد التنظيف: '{$cleanFilterValue}'\n";
        
        // البحث بالنص كما هو
        $q->where('value', 'like', "%{$cleanFilterValue}%");
    });
} else {
    echo "لم يتم تطبيق الفلتر - القيمة فارغة\n";
}

echo "\nSQL للاستعلام: " . $query->toSql() . "\n";
echo "المعاملات: " . json_encode($query->getBindings()) . "\n";

// تنفيذ الاستعلام
$documents = $query->get();

echo "\nعدد النتائج: " . $documents->count() . "\n";

foreach ($documents as $doc) {
    echo "وثيقة: {$doc->title}\n";
    echo "منشورة؟ " . ($doc->is_published ? 'نعم' : 'لا') . "\n";
    echo "تاريخ النشر: " . ($doc->published_at ? $doc->published_at->format('Y-m-d H:i:s') : 'غير محدد') . "\n";
    $fieldValue = $doc->fieldValues()->where('field_id', 9)->first();
    if ($fieldValue) {
        echo "قيمة الحقل: '{$fieldValue->value}'\n";
    }
    echo "---\n";
}

echo "\n=== اختبار مباشر للاستعلام ===\n";

// اختبار مباشر
$directQuery = Document::where('section_id', $section->id)
    ->where('is_published', true)
    ->where('published_at', '<=', now())
    ->whereHas('fieldValues', function($q) {
        $q->where('field_id', 9)
          ->where('value', 'like', '%صحافة%');
    });

echo "SQL للاستعلام المباشر: " . $directQuery->toSql() . "\n";
echo "المعاملات: " . json_encode($directQuery->getBindings()) . "\n";

$directResults = $directQuery->get();
echo "النتائج المباشرة: " . $directResults->count() . "\n";