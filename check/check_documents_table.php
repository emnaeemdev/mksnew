<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "=== فحص بنية جدول documents ===\n";

try {
    $columns = Schema::getColumnListing('documents');
    echo "الأعمدة الموجودة في جدول documents:\n";
    foreach ($columns as $column) {
        echo "- {$column}\n";
    }
    
    echo "\n=== عينة من البيانات ===\n";
    $documents = DB::table('documents')->limit(3)->get();
    foreach ($documents as $doc) {
        echo "ID: {$doc->id}\n";
        echo "Title: {$doc->title}\n";
        echo "Section ID: {$doc->section_id}\n";
        echo "---\n";
    }
    
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage() . "\n";
}