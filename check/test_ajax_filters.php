<?php

// اختبار بسيط للفلاتر
echo "=== اختبار الفلاتر ===\n";

// محاكاة طلب AJAX
$postData = [
    'fields' => [
        '6' => 'ساري ومعدل'
    ],
    '_token' => 'test'
];

echo "البيانات المرسلة:\n";
echo json_encode($postData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

// محاكاة استجابة الخادم
$url = 'http://127.0.0.1:8001/ar/documents/section/test-section/filter-counts';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded',
    'Accept: application/json',
    'X-Requested-With: XMLHttpRequest'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "رمز الاستجابة: {$httpCode}\n";

if ($error) {
    echo "خطأ cURL: {$error}\n";
} else {
    echo "الاستجابة:\n";
    
    $data = json_decode($response, true);
    if ($data) {
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        
        if (isset($data['fieldCounts'])) {
            echo "\n=== تحليل النتائج ===\n";
            foreach ($data['fieldCounts'] as $fieldId => $counts) {
                echo "Field {$fieldId}:\n";
                if (is_array($counts)) {
                    $totalOptions = count($counts);
                    $availableOptions = array_filter($counts, function($count) { return $count > 0; });
                    $availableCount = count($availableOptions);
                    
                    echo "  إجمالي الخيارات: {$totalOptions}\n";
                    echo "  الخيارات المتاحة: {$availableCount}\n";
                    
                    foreach ($counts as $option => $count) {
                        $status = $count > 0 ? '✓' : '✗';
                        echo "  {$status} {$option}: {$count}\n";
                    }
                }
                echo "\n";
            }
        }
    } else {
        echo "استجابة غير صالحة:\n{$response}\n";
    }
}

?>