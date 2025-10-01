<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\DocumentSection;
use App\Models\DocumentCustomField;

$section = DocumentSection::where('slug', 'koanyn')->first();
$field7 = $section->customFields()->where('id', 7)->first();

if($field7) {
    echo "الحقل 7: {$field7->label} (نوع: {$field7->type})\n";
    if($field7->options) {
        echo "الخيارات: " . implode(', ', $field7->options) . "\n";
    } else {
        echo "لا توجد خيارات\n";
    }
} else {
    echo "الحقل 7 غير موجود\n";
}

// عرض جميع الحقول
echo "\nجميع الحقول:\n";
$customFields = $section->customFields()->active()->orderBy('sort_order')->get();
foreach ($customFields as $field) {
    echo "الحقل {$field->id}: {$field->label} (نوع: {$field->type})\n";
    if ($field->type === 'select' && $field->options) {
        echo "  الخيارات: " . implode(', ', $field->options) . "\n";
    }
}