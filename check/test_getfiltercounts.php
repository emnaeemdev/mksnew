<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\DocumentSection;
use App\Http\Controllers\Frontend\DocumentController;
use Illuminate\Http\Request;

echo "🧪 اختبار دالة getFilterCounts مباشرة\n";
echo str_repeat('=', 50) . "\n\n";

// الحصول على القسم
$section = DocumentSection::where('slug', 'koanyn')->first();

if (!$section) {
    echo "❌ لم يتم العثور على القسم\n";
    exit;
}

echo "📁 القسم: {$section->title}\n";
echo "🆔 معرف القسم: {$section->id}\n\n";

// إنشاء طلب HTTP مع فلتر
$request = new Request();
$request->merge([
    'fields' => [
        '6' => 'ساري ومعدل'  // حالة القانون
    ]
]);

echo "🔍 الفلاتر المطبقة:\n";
foreach ($request->input('fields', []) as $fieldId => $value) {
    echo "  - الحقل {$fieldId}: {$value}\n";
}
echo "\n";

// إنشاء الكنترولر واستدعاء الدالة
$controller = new DocumentController();

try {
    $response = $controller->getFilterCounts($request, $section);
    $data = json_decode($response->getContent(), true);
    
    echo "📊 نتيجة دالة getFilterCounts:\n";
    echo "✅ نجحت: " . ($data['success'] ? 'نعم' : 'لا') . "\n\n";
    
    if ($data['success'] && isset($data['fieldCounts'])) {
        echo "📈 عدادات الحقول:\n";
        foreach ($data['fieldCounts'] as $fieldId => $counts) {
            echo "\n🔢 الحقل {$fieldId}:\n";
            if (is_array($counts)) {
                foreach ($counts as $value => $count) {
                    echo "  - {$value}: {$count} وثيقة\n";
                }
            } else {
                echo "  - نوع البيانات: " . gettype($counts) . "\n";
                echo "  - القيمة: " . print_r($counts, true) . "\n";
            }
        }
    } else {
        echo "❌ لا توجد عدادات في الاستجابة\n";
        echo "📄 محتوى الاستجابة الكامل:\n";
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ خطأ في تنفيذ الدالة: " . $e->getMessage() . "\n";
    echo "📍 الملف: " . $e->getFile() . "\n";
    echo "📍 السطر: " . $e->getLine() . "\n";
}

echo "\n" . str_repeat('=', 50) . "\n";
echo "🏁 انتهى الاختبار\n";