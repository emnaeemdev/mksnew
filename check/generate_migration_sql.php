<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\DocumentSection;
use App\Models\DocumentCustomField;
use App\Models\User;

echo "مولد استعلامات SQL لنقل البيانات\n";
echo "================================\n";

// الحصول على قسم القوانين
$lawsSection = DocumentSection::where('slug', 'koanyn')->first();
if (!$lawsSection) {
    echo "❌ لم يتم العثور على قسم القوانين\n";
    exit(1);
}

// الحصول على الحقول المخصصة
$customFields = $lawsSection->customFields->keyBy('name');

// ربط الحقول
$fieldMapping = [
    'laweg_number' => 'law_number',
    'laweg_release_date' => 'law_date',
    'laweg_subject' => 'law_subject',
    'laweg_issuer_pub' => 'law_issuer_pub',
    'laweg_type' => 'law_type'
];

echo "-- استعلامات SQL لنقل البيانات من قاعدة البيانات القديمة\n";
echo "-- تاريخ الإنشاء: " . date('Y-m-d H:i:s') . "\n";
echo "-- قسم القوانين ID: {$lawsSection->id}\n";
echo "\n";

echo "-- ===================================\n";
echo "-- الخطوة 1: إدراج الوثائق في جدول documents\n";
echo "-- ===================================\n";
echo "\n";

echo "INSERT INTO documents (\n";
echo "    section_id, user_id, title, content, excerpt, slug,\n";
echo "    is_published, is_featured, views_count, downloads_count, shares_count,\n";
echo "    published_at, created_at, updated_at\n";
echo ") \n";
echo "SELECT \n";
echo "    {$lawsSection->id} as section_id,\n";
echo "    1 as user_id, -- المستخدم الافتراضي\n";
echo "    title_ar as title,\n";
echo "    COALESCE(description_ar, '') as content,\n";
echo "    COALESCE(brief_ar, '') as excerpt,\n";
echo "    COALESCE(slug_ar, CONCAT('law-', id)) as slug,\n";
echo "    1 as is_published,\n";
echo "    0 as is_featured,\n";
echo "    COALESCE(total_views, 0) as views_count,\n";
echo "    COALESCE(total_downloads, 0) as downloads_count,\n";
echo "    COALESCE(total_shares, 0) as shares_count,\n";
echo "    COALESCE(created_at, NOW()) as published_at,\n";
echo "    COALESCE(created_at, NOW()) as created_at,\n";
echo "    COALESCE(updated_at, NOW()) as updated_at\n";
echo "FROM old_database.lawegs \n";
echo "WHERE status = 'published' AND deleted_at IS NULL;\n";
echo "\n";

echo "-- ===================================\n";
echo "-- الخطوة 2: إدراج قيم الحقول المخصصة\n";
echo "-- ===================================\n";
echo "\n";

foreach ($fieldMapping as $oldField => $newField) {
    if (!isset($customFields[$newField])) {
        echo "-- تحذير: الحقل {$newField} غير موجود\n";
        continue;
    }
    
    $fieldId = $customFields[$newField]->id;
    $fieldLabel = $customFields[$newField]->label;
    
    echo "-- {$fieldLabel} ({$oldField} -> {$newField})\n";
    echo "INSERT INTO document_field_values (document_id, field_id, value, created_at, updated_at)\n";
    echo "SELECT \n";
    echo "    d.id as document_id,\n";
    echo "    {$fieldId} as field_id,\n";
    echo "    old_l.{$oldField} as value,\n";
    echo "    NOW() as created_at,\n";
    echo "    NOW() as updated_at\n";
    echo "FROM documents d\n";
    echo "JOIN old_database.lawegs old_l ON (\n";
    echo "    d.title = old_l.title_ar \n";
    echo "    AND d.section_id = {$lawsSection->id}\n";
    echo ")\n";
    echo "WHERE old_l.{$oldField} IS NOT NULL \n";
    echo "  AND old_l.{$oldField} != '' \n";
    echo "  AND old_l.status = 'published' \n";
    echo "  AND old_l.deleted_at IS NULL;\n";
    echo "\n";
}

echo "-- ===================================\n";
echo "-- الخطوة 3: التحقق من النتائج\n";
echo "-- ===================================\n";
echo "\n";

echo "-- عدد الوثائق المنقولة\n";
echo "SELECT COUNT(*) as total_documents \n";
echo "FROM documents \n";
echo "WHERE section_id = {$lawsSection->id};\n";
echo "\n";

echo "-- عدد قيم الحقول المخصصة لكل حقل\n";
foreach ($fieldMapping as $oldField => $newField) {
    if (!isset($customFields[$newField])) continue;
    
    $fieldId = $customFields[$newField]->id;
    $fieldLabel = $customFields[$newField]->label;
    
    echo "SELECT '{$fieldLabel}' as field_name, COUNT(*) as values_count\n";
    echo "FROM document_field_values \n";
    echo "WHERE field_id = {$fieldId}\n";
    echo "UNION ALL\n";
}
echo "SELECT 'المجموع' as field_name, COUNT(*) as values_count\n";
echo "FROM document_field_values dfv\n";
echo "JOIN documents d ON d.id = dfv.document_id\n";
echo "WHERE d.section_id = {$lawsSection->id};\n";
echo "\n";

echo "-- ===================================\n";
echo "-- معلومات إضافية\n";
echo "-- ===================================\n";
echo "\n";

echo "-- معرفات الحقول المخصصة:\n";
foreach ($customFields as $field) {
    echo "-- {$field->label}: ID = {$field->id}, Name = {$field->name}, Type = {$field->type}\n";
}
echo "\n";

echo "-- ملاحظات مهمة:\n";
echo "-- 1. استبدل 'old_database' باسم قاعدة البيانات القديمة الفعلي\n";
echo "-- 2. تأكد من وجود المستخدم رقم 1 في جدول users\n";
echo "-- 3. قم بعمل نسخة احتياطية قبل تنفيذ الاستعلامات\n";
echo "-- 4. اختبر الاستعلامات على عينة صغيرة أولاً\n";
echo "-- 5. تحقق من توافق قيم الحقول مع الخيارات المحددة\n";
echo "\n";

echo "-- ===================================\n";
echo "-- استعلامات التنظيف (اختيارية)\n";
echo "-- ===================================\n";
echo "\n";

echo "-- حذف الوثائق المنقولة (في حالة الحاجة لإعادة النقل)\n";
echo "-- DELETE FROM document_field_values \n";
echo "-- WHERE document_id IN (\n";
echo "--     SELECT id FROM documents WHERE section_id = {$lawsSection->id}\n";
echo "-- );\n";
echo "-- \n";
echo "-- DELETE FROM documents WHERE section_id = {$lawsSection->id};\n";
echo "\n";

echo "✅ تم إنشاء استعلامات SQL بنجاح!\n";
echo "📝 يمكنك نسخ الاستعلامات أعلاه وتنفيذها في phpMyAdmin أو أي أداة إدارة قواعد بيانات\n";