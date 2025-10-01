<?php

require_once '../vendor/autoload.php';

$app = require_once '../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Document;
use App\Models\DocumentSection;
use App\Models\DocumentCustomField;
use App\Models\DocumentFieldValue;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

echo "سكريبت نقل البيانات من قاعدة البيانات القديمة\n";
echo "==========================================\n";

// إعدادات قاعدة البيانات القديمة
$oldDbConfig = [
    'host' => 'localhost',
    'database' => 'mks', // ضع اسم قاعدة البيانات القديمة هنا
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
];

echo "تكوين الاتصال بقاعدة البيانات القديمة...\n";

try {
    // إنشاء اتصال بقاعدة البيانات القديمة
    $oldPdo = new PDO(
        "mysql:host={$oldDbConfig['host']};dbname={$oldDbConfig['database']};charset={$oldDbConfig['charset']}",
        $oldDbConfig['username'],
        $oldDbConfig['password']
    );
    $oldPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ تم الاتصال بقاعدة البيانات القديمة بنجاح\n";
    
} catch (PDOException $e) {
    echo "❌ خطأ في الاتصال بقاعدة البيانات القديمة: " . $e->getMessage() . "\n";
    echo "\n📝 تعليمات الإعداد:\n";
    echo "1. قم بتعديل إعدادات قاعدة البيانات القديمة في السطر 18-24\n";
    echo "2. تأكد من أن قاعدة البيانات القديمة متاحة ويمكن الوصول إليها\n";
    exit(1);
}

// الحصول على قسم القوانين
$lawsSection = DocumentSection::where('slug', 'laws')->first();
if (!$lawsSection) {
    echo "❌ لم يتم العثور على قسم القوانين\n";
    exit(1);
}

echo "✅ تم العثور على قسم القوانين: {$lawsSection->name} (ID: {$lawsSection->id})\n";

// الحصول على الحقول المخصصة
$customFields = $lawsSection->customFields->keyBy('name');
echo "✅ تم العثور على {$customFields->count()} حقل مخصص\n";

// ربط الحقول القديمة بالجديدة
$fieldMapping = [
    'laweg_number' => 'law_number',        // رقم القانون
    'laweg_release_date' => 'law_date',    // تاريخ القانون
    'laweg_subject' => 'law_subject',      // التصنيف وفقًا للحق
    'laweg_issuer_pub' => 'law_issuer_pub', // جهة الإصدار
    'laweg_type' => 'law_type'             // حالة القانون
];

echo "\n📋 ربط الحقول:\n";
foreach ($fieldMapping as $oldField => $newField) {
    if (isset($customFields[$newField])) {
        echo "  {$oldField} → {$newField} (ID: {$customFields[$newField]->id})\n";
    } else {
        echo "  ❌ {$oldField} → {$newField} (غير موجود)\n";
    }
}

// الحصول على المستخدم الافتراضي (ID: 1)
$defaultUser = User::find(2);
if (!$defaultUser) {
    echo "❌ لم يتم العثور على المستخدم رقم 1\n";
    exit(1);
}

echo "✅ سيتم تعيين جميع الوثائق للمستخدم: {$defaultUser->name}\n";

echo "\n🔄 بدء عملية النقل...\n";
echo "========================\n";

try {
    // جلب البيانات من قاعدة البيانات القديمة
    $stmt = $oldPdo->prepare("
        SELECT * FROM lawegs 
        WHERE status = 'published' 
        AND deleted_at IS NULL
        ORDER BY id
    ");
    $stmt->execute();
    $oldRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $recordCount = count($oldRecords);
    echo "📊 تم العثور على {$recordCount} سجل في قاعدة البيانات القديمة\n\n";
    
    $successCount = 0;
    $errorCount = 0;
    
    DB::beginTransaction();
    
    foreach ($oldRecords as $index => $record) {
        try {
            echo "معالجة السجل " . ($index + 1) . ": {$record['title_ar']}\n";
            
            // إنشاء الوثيقة الجديدة
            $document = new Document();
            $document->section_id = $lawsSection->id;
            $document->user_id = $defaultUser->id;
            $document->title = $record['title_ar'];
            $document->content = $record['description_ar'] ?? '';
            $document->excerpt = $record['brief_ar'] ?? '';
            $document->slug = $record['slug_ar'] ?? Str::slug($record['title_ar']);
            $document->is_published = true;
            $document->is_featured = false;
            $document->views_count = mt_rand(1000, 5000);
            $document->published_at = $record['created_at'] ?? now();
            $document->created_at = $record['created_at'] ?? now();
            $document->updated_at = $record['updated_at'] ?? now();
            
            $document->save();
            
            echo "  ✅ تم إنشاء الوثيقة (ID: {$document->id})\n";
            
            // إضافة قيم الحقول المخصصة
            foreach ($fieldMapping as $oldField => $newField) {
                if (isset($customFields[$newField]) && !empty($record[$oldField])) {
                    $customField = $customFields[$newField];
                    
                    // التحقق من توافق القيمة مع نوع الحقل
                    $value = trim($record[$oldField]);
                    
                    // للحقول من نوع select، التحقق من وجود القيمة في الخيارات
                    if ($customField->type === 'select' && !empty($customField->options)) {
                        $options = is_array($customField->options) ? $customField->options : json_decode($customField->options, true);
                        if (is_array($options) && !in_array($value, $options)) {
                            echo "    ⚠️ تحذير: القيمة '{$value}' غير موجودة في خيارات {$newField}\n";
                            echo "    📋 الخيارات المتاحة: " . implode(', ', $options) . "\n";
                            // يمكن تخطي هذا الحقل أو استخدام قيمة افتراضية
                            continue;
                        }
                    }
                    
                    $fieldValue = new DocumentFieldValue();
                    $fieldValue->document_id = $document->id;
                    $fieldValue->field_id = $customField->id;
                    $fieldValue->value = $value;
                    $fieldValue->save();
                    
                    echo "    ✅ {$newField}: {$value}\n";
                }
            }
            
            $successCount++;
            echo "  ✅ تم نقل السجل بنجاح\n\n";
            
        } catch (Exception $e) {
            echo "  ❌ خطأ في نقل السجل: " . $e->getMessage() . "\n\n";
            $errorCount++;
        }
    }
    
    DB::commit();
    
    echo "\n🎉 انتهت عملية النقل!\n";
    echo "===================\n";
    echo "✅ تم نقل {$successCount} سجل بنجاح\n";
    echo "❌ فشل في نقل {$errorCount} سجل\n";
    echo "📊 إجمالي السجلات: {$recordCount}\n";
    
} catch (Exception $e) {
    DB::rollback();
    echo "❌ خطأ عام في عملية النقل: " . $e->getMessage() . "\n";
    echo "📍 ملف الخطأ: " . $e->getFile() . "\n";
    echo "📍 سطر الخطأ: " . $e->getLine() . "\n";
    echo "📍 تفاصيل الخطأ:\n" . $e->getTraceAsString() . "\n";
}

echo "\n📝 ملاحظات مهمة:\n";
echo "================\n";
echo "1. تأكد من مراجعة البيانات المنقولة في لوحة التحكم\n";
echo "2. قم بفحص الروابط والصور إذا كانت موجودة\n";
echo "3. تأكد من صحة التواريخ والحقول المخصصة\n";
echo "4. يمكنك حذف هذا الملف بعد التأكد من نجاح النقل\n";