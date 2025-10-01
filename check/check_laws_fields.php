<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\DocumentSection;
use App\Models\DocumentCustomField;

echo "فحص الحقول المخصصة في قسم القوانين:\n";
echo "=====================================\n";

// البحث عن قسم القوانين
$section = DocumentSection::where('slug', 'koanyn')
    ->orWhere('name', 'like', '%قوانين%')
    ->first();

if ($section) {
    echo "القسم: {$section->name} (ID: {$section->id})\n";
    echo "Slug: {$section->slug}\n";
    echo "نشط: " . ($section->is_active ? 'نعم' : 'لا') . "\n";
    echo "عدد الوثائق: " . $section->documents()->count() . "\n";
    echo "\n";
    
    $fields = $section->customFields;
    
    if ($fields->count() > 0) {
        echo "الحقول المخصصة ({$fields->count()} حقل):\n";
        echo "---------------------------\n";
        
        foreach ($fields as $field) {
            echo "ID: {$field->id}\n";
            echo "Name: {$field->name}\n";
            echo "Label: {$field->label}\n";
            echo "Type: {$field->type}\n";
            echo "Required: " . ($field->is_required ? 'نعم' : 'لا') . "\n";
            echo "Active: " . ($field->is_active ? 'نعم' : 'لا') . "\n";
            echo "Sort Order: {$field->sort_order}\n";
            
            if ($field->options) {
                if (is_string($field->options)) {
                    $options = json_decode($field->options, true);
                } else {
                    $options = $field->options;
                }
                if ($options && is_array($options)) {
                    echo "Options: " . implode(', ', $options) . "\n";
                }
            }
            
            echo "---\n";
        }
    } else {
        echo "لا توجد حقول مخصصة في هذا القسم\n";
    }
    
} else {
    echo "لم يتم العثور على قسم القوانين\n";
    
    // عرض جميع الأقسام المتاحة
    echo "\nالأقسام المتاحة:\n";
    $allSections = DocumentSection::all();
    foreach ($allSections as $sec) {
        echo "- {$sec->name} (slug: {$sec->slug}, ID: {$sec->id})\n";
    }
}