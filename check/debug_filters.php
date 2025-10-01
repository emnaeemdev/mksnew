<?php

require_once 'vendor/autoload.php';

use App\Models\DocumentCustomField;
use App\Models\DocumentSection;
use App\Models\Document;
use App\Models\DocumentFieldValue;
use App\Models\Category;

// تحميل Laravel
$app = require_once 'bootstrap/app.php';
$app->loadEnvironmentFrom('.env');

// تشغيل kernel
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== اختبار الفلاتر المترابطة ===\n\n";

// الحصول على البيانات
try {
    // الحصول على جميع الأقسام المتاحة
    $allSections = App\Models\Category::all();
    echo "📂 الأقسام المتاحة:\n";
    foreach ($allSections as $sec) {
        echo "  - {$sec->name} (slug: {$sec->slug})\n";
    }
    echo "\n";
    
    // الحصول على القسم
    $section = App\Models\Category::where('slug', 'koanyn')->first();
    if (!$section) {
        // جرب أول قسم متاح
        $section = $allSections->first();
        if (!$section) {
            echo "❌ لا توجد أقسام في قاعدة البيانات\n";
            exit;
        }
        echo "⚠️ لم يتم العثور على قسم 'koanyn'، سيتم استخدام: {$section->name}\n\n";
    }
    
    echo "✅ تم العثور على القسم: {$section->name}\n\n";
    
    // الحصول على الحقول المخصصة
    $customFields = App\Models\DocumentCustomField::where('section_id', $section->id)
        ->where('is_active', true)
        ->orderBy('sort_order')
        ->get();
    
    echo "📋 الحقول المخصصة المتاحة:\n";
    foreach ($customFields as $field) {
        echo "  - {$field->label} ({$field->type})\n";
    }
    echo "\n";
    
    // اختبار الفلاتر المترابطة
    echo "🔍 اختبار الفلاتر المترابطة:\n\n";
    
    // الحصول على جميع القيم المتاحة لكل حقل
    foreach ($customFields as $field) {
        if ($field->type == 'select') {
            echo "📊 الحقل: {$field->label}\n";
            
            // الحصول على جميع القيم
            $allValues = DocumentFieldValue::where('field_id', $field->id)
                ->whereHas('document', function($q) use ($section) {
                    $q->where('section_id', $section->id)
                      ->where('is_published', true);
                })
                ->distinct()
                ->pluck('value')
                ->filter()
                ->sort()
                ->values();
            
            echo "  جميع القيم: " . $allValues->implode(', ') . "\n";
            
            // اختبار كل قيمة
            foreach ($allValues as $testValue) {
                echo "\n  🧪 اختبار القيمة: {$testValue}\n";
                
                // بناء استعلام مع هذه القيمة
                $baseQuery = Document::where('section_id', $section->id)
                    ->where('is_published', true)
                    ->whereHas('fieldValues', function($q) use ($field, $testValue) {
                        $q->where('field_id', $field->id)
                          ->where('value', $testValue);
                    });
                
                $documentsCount = $baseQuery->count();
                echo "    📄 عدد الوثائق: {$documentsCount}\n";
                
                // الحصول على القيم المرتبطة في الحقول الأخرى
                foreach ($customFields as $otherField) {
                    if ($otherField->id != $field->id && $otherField->type == 'select') {
                        $relatedValues = DocumentFieldValue::where('field_id', $otherField->id)
                            ->whereIn('document_id', function($q) use ($section, $field, $testValue) {
                                $q->select('id')
                                  ->from('documents')
                                  ->where('section_id', $section->id)
                                  ->where('is_published', true)
                                  ->whereExists(function($subQ) use ($field, $testValue) {
                                      $subQ->select('id')
                                           ->from('document_field_values')
                                           ->whereColumn('document_id', 'documents.id')
                                           ->where('field_id', $field->id)
                                           ->where('value', $testValue);
                                  });
                            })
                            ->distinct()
                            ->pluck('value')
                            ->filter()
                            ->sort()
                            ->values();
                        
                        echo "    🔗 {$otherField->label}: " . ($relatedValues->count() > 0 ? $relatedValues->implode(', ') : 'لا توجد قيم') . "\n";
                    }
                }
            }
            echo "\n" . str_repeat('-', 50) . "\n\n";
        }
    }
    
    // اختبار دالة getFilterCounts
    echo "🔧 اختبار دالة getFilterCounts:\n\n";
    
    $controller = new App\Http\Controllers\Frontend\DocumentController();
    
    // محاكاة طلب HTTP
    $request = new Illuminate\Http\Request();
    $request->merge([
        'fields' => [
            $customFields->first()->id => $allValues->first() ?? 'test'
        ]
    ]);
    
    // استدعاء الدالة
    $response = $controller->getFilterCounts($request, $section->slug);
    $data = json_decode($response->getContent(), true);
    
    if ($data['success']) {
        echo "✅ دالة getFilterCounts تعمل بنجاح\n";
        echo "📊 النتائج:\n";
        foreach ($data['fieldCounts'] as $fieldId => $counts) {
            $field = $customFields->where('id', $fieldId)->first();
            if ($field) {
                echo "  {$field->label}:\n";
                if (isset($counts['options'])) {
                    foreach ($counts['options'] as $option => $count) {
                        echo "    - {$option}: {$count}\n";
                    }
                }
            }
        }
    } else {
        echo "❌ دالة getFilterCounts لا تعمل\n";
    }
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
    echo "📍 الملف: " . $e->getFile() . " السطر: " . $e->getLine() . "\n";
}

echo "\n=== انتهاء الاختبار ===\n";