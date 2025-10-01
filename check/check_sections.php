<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\DocumentSection;

echo "الأقسام المتاحة:\n";
echo "================\n";

$sections = DocumentSection::all();

if ($sections->count() > 0) {
    foreach ($sections as $section) {
        echo "ID: {$section->id}\n";
        echo "Name: {$section->name}\n";
        echo "Slug: {$section->slug}\n";
        echo "Active: " . ($section->is_active ? 'نعم' : 'لا') . "\n";
        echo "Documents Count: " . $section->documents()->count() . "\n";
        echo "-------------------\n";
    }
} else {
    echo "لا توجد أقسام في قاعدة البيانات\n";
}

echo "\nالرابط الصحيح للوثائق:\n";
echo "http://127.0.0.1:8001/ar/documents\n";

if ($sections->count() > 0) {
    $firstSection = $sections->first();
    echo "مثال على رابط قسم: http://127.0.0.1:8001/ar/documents/section/{$firstSection->slug}\n";
}