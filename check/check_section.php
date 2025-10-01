<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\DocumentSection;

// البحث عن القسم بـ slug 'koanyn'
$section = DocumentSection::where('slug', 'koanyn')->first();

if ($section) {
    echo "تم العثور على القسم:\n";
    echo "ID: " . $section->id . "\n";
    echo "Name: " . $section->name . "\n";
    echo "Slug: " . $section->slug . "\n";
    echo "Is Active: " . ($section->is_active ? 'نعم' : 'لا') . "\n";
} else {
    echo "لم يتم العثور على قسم بـ slug 'koanyn'\n";
    
    // عرض جميع الأقسام المتاحة
    echo "\nالأقسام المتاحة:\n";
    $sections = DocumentSection::all();
    foreach ($sections as $sec) {
        echo "- ID: {$sec->id}, Name: {$sec->name}, Slug: {$sec->slug}, Active: " . ($sec->is_active ? 'نعم' : 'لا') . "\n";
    }
}