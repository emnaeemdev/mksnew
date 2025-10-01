<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\DocumentCustomField;
use App\Models\DocumentSection;

echo "🔍 فحص معرف القسم للحقل 9:\n";

$field = DocumentCustomField::find(9);
if ($field) {
    echo "الحقل 9 ينتمي للقسم: {$field->section_id}\n";
    echo "نوع الحقل: {$field->type}\n";
    echo "تسمية الحقل: {$field->label}\n";
} else {
    echo "الحقل 9 غير موجود\n";
}

echo "\n📂 فحص قسم koanyn:\n";

$section = DocumentSection::where('slug', 'koanyn')->first();
if ($section) {
    echo "قسم koanyn له المعرف: {$section->id}\n";
    echo "اسم القسم: {$section->name}\n";
    
    // فحص الحقول المخصصة لهذا القسم
    $customFields = $section->customFields()->get();
    echo "\n🏷️ الحقول المخصصة لقسم koanyn:\n";
    foreach ($customFields as $customField) {
        echo "  - الحقل {$customField->id}: {$customField->label} ({$customField->type})\n";
    }
} else {
    echo "قسم koanyn غير موجود\n";
}

echo "\n✅ النتيجة: ";
if ($field && $section) {
    if ($field->section_id == $section->id) {
        echo "الحقل 9 ينتمي لقسم koanyn - العلاقة صحيحة\n";
    } else {
        echo "❌ الحقل 9 لا ينتمي لقسم koanyn! هذا سبب المشكلة\n";
        echo "الحقل 9 ينتمي للقسم {$field->section_id} بينما koanyn له المعرف {$section->id}\n";
    }
}