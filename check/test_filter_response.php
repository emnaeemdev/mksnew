<?php

require_once 'vendor/autoload.php';

// إعداد قاعدة البيانات
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=mksnew', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== اختبار الفلاتر النهائي ===\n\n";
    
    // فحص الحقول المخصصة
    echo "الحقول المخصصة:\n";
    $stmt = $pdo->query("SELECT id, name FROM document_custom_fields ORDER BY id");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "ID: {$row['id']}, الاسم: {$row['name']}\n";
    }
    
    echo "\n" . str_repeat("=", 50) . "\n\n";
    
    // اختبار السيناريو: اختيار "ساري ومعدل" من الحقل 6
    echo "🔍 اختبار السيناريو: اختيار 'ساري ومعدل' من حالة القانون\n\n";
    
    // الحصول على الوثائق التي تحتوي على "ساري ومعدل" في الحقل 6
    $stmt = $pdo->prepare("SELECT document_id FROM document_field_values WHERE field_id = 6 AND value = ?");
    $stmt->execute(['ساري ومعدل']);
    $filteredDocumentIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "عدد الوثائق المفلترة: " . count($filteredDocumentIds) . "\n";
    echo "IDs الوثائق المفلترة: " . implode(', ', $filteredDocumentIds) . "\n\n";
    
    if (count($filteredDocumentIds) > 0) {
        // فحص القيم المتاحة في الحقول الأخرى للوثائق المفلترة
        echo "القيم المتاحة في الحقول الأخرى للوثائق المفلترة:\n\n";
        
        // فحص كل حقل
        for ($fieldId = 1; $fieldId <= 10; $fieldId++) {
            if ($fieldId == 6) continue; // تخطي الحقل المفلتر
            
            $placeholders = str_repeat('?,', count($filteredDocumentIds) - 1) . '?';
            $stmt = $pdo->prepare("SELECT DISTINCT value FROM document_field_values WHERE field_id = ? AND document_id IN ($placeholders) AND value IS NOT NULL AND value != '' ORDER BY value");
            $params = array_merge([$fieldId], $filteredDocumentIds);
            $stmt->execute($params);
            $values = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (!empty($values)) {
                echo "الحقل {$fieldId}: " . implode(', ', $values) . "\n";
            }
        }
        
        echo "\n" . str_repeat("=", 50) . "\n\n";
        
        // مقارنة مع جميع القيم في النظام
        echo "مقارنة مع جميع القيم في النظام:\n\n";
        
        for ($fieldId = 1; $fieldId <= 10; $fieldId++) {
            if ($fieldId == 6) continue;
            
            $stmt = $pdo->prepare("SELECT DISTINCT value FROM document_field_values WHERE field_id = ? AND value IS NOT NULL AND value != '' ORDER BY value");
            $stmt->execute([$fieldId]);
            $allValues = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (!empty($allValues)) {
                echo "الحقل {$fieldId} (جميع القيم): " . implode(', ', $allValues) . "\n";
            }
        }
        
        echo "\n" . str_repeat("=", 50) . "\n\n";
        
        // التحقق من صحة الفلترة
        echo "✅ نتيجة الاختبار:\n";
        echo "إذا كانت القيم المتاحة في الحقول الأخرى أقل من جميع القيم في النظام،\n";
        echo "فهذا يعني أن الفلترة تعمل بشكل صحيح!\n\n";
        
        // اختبار محدد للحقل 10
        echo "🎯 اختبار محدد للحقل 10 (التصنيف وفقاً للحق):\n";
        
        $placeholders = str_repeat('?,', count($filteredDocumentIds) - 1) . '?';
        $stmt = $pdo->prepare("SELECT DISTINCT value, COUNT(*) as count FROM document_field_values WHERE field_id = 10 AND document_id IN ($placeholders) GROUP BY value ORDER BY count DESC");
        $stmt->execute($filteredDocumentIds);
        $filteredField10Values = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "القيم المتاحة في الحقل 10 للوثائق المفلترة:\n";
        foreach ($filteredField10Values as $row) {
            echo "- {$row['value']}: {$row['count']} وثيقة\n";
        }
        
        echo "\nجميع القيم في الحقل 10:\n";
        $stmt = $pdo->query("SELECT DISTINCT value, COUNT(*) as count FROM document_field_values WHERE field_id = 10 GROUP BY value ORDER BY count DESC");
        $allField10Values = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($allField10Values as $row) {
            echo "- {$row['value']}: {$row['count']} وثيقة\n";
        }
        
        if (count($filteredField10Values) < count($allField10Values)) {
            echo "\n✅ الفلترة تعمل بشكل صحيح! القيم المتاحة أقل من جميع القيم.\n";
        } else {
            echo "\n❌ الفلترة لا تعمل بشكل صحيح! جميع القيم ما زالت تظهر.\n";
        }
    }
    
} catch (PDOException $e) {
    echo "خطأ في قاعدة البيانات: " . $e->getMessage() . "\n";
}

echo "\n=== انتهى الاختبار ===\n";
?>