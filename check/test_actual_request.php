<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\DocumentFieldValue;
use App\Models\Document;
use App\Models\DocumentSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

echo "=== محاكاة الطلب الفعلي من الرابط ===\n";

// محاكاة الرابط المرسل من المستخدم
$url = 'http://localhost:8001/ar/documents/section/koanyn?search=&sort=latest&per_page=12&fields%5B9%5D=%D8%B5%D8%AD%D8%A7%D9%81%D8%A9&fields%5B10%5D=&fields%5B6%5D=&fields%5B3%5D=&fields%5B2%5D%5Bday%5D=&fields%5B2%5D%5Bmonth%5D=&fields%5B2%5D%5Byear%5D=';

// استخراج query parameters
$parsedUrl = parse_url($url);
parse_str($parsedUrl['query'], $queryParams);

echo "Query Parameters المستخرجة:\n";
print_r($queryParams);

echo "\n=== فحص قيمة الحقل 9 ===\n";
echo "قيمة fields[9]: '" . ($queryParams['fields'][9] ?? 'غير موجود') . "'\n";
echo "URL decoded: '" . urldecode($queryParams['fields'][9] ?? '') . "'\n";
echo "هل القيمة فارغة؟ " . (empty($queryParams['fields'][9]) ? 'نعم' : 'لا') . "\n";
echo "هل القيمة تساوي فراغ؟ " . (($queryParams['fields'][9] ?? '') === '' ? 'نعم' : 'لا') . "\n";

// محاكاة Request object
$request = new Request($queryParams);

echo "\n=== اختبار Request object ===\n";
echo "request->input('fields.9'): '" . $request->input('fields.9') . "'\n";
echo "request->input('fields')[9]: '" . ($request->input('fields')[9] ?? 'غير موجود') . "'\n";

// البحث عن القسم
$section = DocumentSection::where('slug', 'koanyn')->first();
if (!$section) {
    echo "خطأ: لم يتم العثور على القسم koanyn\n";
    exit;
}

echo "\n=== تطبيق نفس منطق DocumentController ===\n";

$query = $section->documents()->published();

// تطبيق فلتر الحقل رقم 9 بنفس الطريقة المستخدمة في Controller
$filterValue = $request->input('fields.9');
echo "قيمة الفلتر من Request: '{$filterValue}'\n";

if (!empty($filterValue) && $filterValue !== '') {
    echo "تطبيق الفلتر...\n";
    
    $query->whereHas('fieldValues', function($q) use ($filterValue) {
        $q->where('field_id', 9);
        
        // تنظيف قيمة البحث من المسافات الإضافية
        $cleanFilterValue = trim($filterValue);
        echo "القيمة بعد التنظيف: '{$cleanFilterValue}'\n";
        
        // البحث بالنص كما هو (كلمة واحدة أو عبارة كاملة)
        $q->where('value', 'like', "%{$cleanFilterValue}%");
    });
    
    echo "تم تطبيق الفلتر بنجاح\n";
} else {
    echo "لم يتم تطبيق الفلتر - القيمة فارغة أو null\n";
}

// تنفيذ الاستعلام
$documents = $query->get();

echo "\nعدد النتائج النهائية: " . $documents->count() . "\n";

foreach ($documents as $doc) {
    echo "وثيقة: {$doc->title}\n";
    $fieldValue = $doc->fieldValues()->where('field_id', 9)->first();
    if ($fieldValue) {
        echo "قيمة الحقل: '{$fieldValue->value}'\n";
    }
    echo "---\n";
}

echo "\n=== اختبار مع قيم مختلفة ===\n";

// اختبار مع قيم مختلفة
$testValues = [
    'صحافة',
    urlencode('صحافة'),
    '%D8%B5%D8%AD%D8%A7%D9%81%D8%A9',
    urldecode('%D8%B5%D8%AD%D8%A7%D9%81%D8%A9')
];

foreach ($testValues as $testValue) {
    echo "اختبار مع القيمة: '{$testValue}'\n";
    
    $testQuery = $section->documents()->published()
        ->whereHas('fieldValues', function($q) use ($testValue) {
            $q->where('field_id', 9)
              ->where('value', 'like', "%{$testValue}%");
        });
    
    $count = $testQuery->count();
    echo "عدد النتائج: {$count}\n";
    echo "---\n";
}