<?php

// اختبار الطلب عبر HTTP
$url = 'http://localhost:8001/ar/documents/section/koanyn';
$postData = [
    'search' => '',
    'sort' => 'latest',
    'per_page' => '12',
    'fields' => [
        '9' => 'صحافة',
        '10' => '',
        '6' => '',
        '3' => '',
        '2' => [
            'day' => '',
            'month' => '',
            'year' => ''
        ]
    ]
];

echo "=== اختبار الطلب عبر HTTP ===\n";
echo "الرابط: {$url}\n";
echo "البيانات المرسلة:\n";
print_r($postData);

// إعداد cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded',
    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
]);

// تنفيذ الطلب
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "\n=== نتيجة الطلب ===\n";
echo "HTTP Code: {$httpCode}\n";

if ($error) {
    echo "خطأ cURL: {$error}\n";
} else {
    echo "طول الاستجابة: " . strlen($response) . " حرف\n";
    
    // البحث عن عدد النتائج في HTML
    if (preg_match('/تم العثور على (\d+) وثيقة/', $response, $matches)) {
        echo "عدد الوثائق الموجودة: {$matches[1]}\n";
    } elseif (preg_match('/Found (\d+) documents/', $response, $matches)) {
        echo "عدد الوثائق الموجودة: {$matches[1]}\n";
    } elseif (strpos($response, 'تم العثور على 0 وثيقة') !== false) {
        echo "تم العثور على 0 وثيقة\n";
    } else {
        echo "لم يتم العثور على نص عدد الوثائق\n";
    }
    
    // البحث عن أي أخطاء في HTML
    if (strpos($response, 'Error') !== false || strpos($response, 'Exception') !== false) {
        echo "تم العثور على خطأ في الاستجابة\n";
        
        // استخراج جزء من الخطأ
        if (preg_match('/<title>([^<]+)<\/title>/', $response, $titleMatches)) {
            echo "عنوان الصفحة: {$titleMatches[1]}\n";
        }
    }
    
    // حفظ الاستجابة في ملف للفحص
    file_put_contents('response_debug.html', $response);
    echo "تم حفظ الاستجابة في ملف response_debug.html\n";
}

echo "\n=== اختبار GET request ===\n";

// اختبار GET request
$getUrl = $url . '?' . http_build_query($postData);
echo "رابط GET: {$getUrl}\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $getUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
]);

$getResponse = curl_exec($ch);
$getHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$getError = curl_error($ch);
curl_close($ch);

echo "HTTP Code للـ GET: {$getHttpCode}\n";

if ($getError) {
    echo "خطأ cURL للـ GET: {$getError}\n";
} else {
    echo "طول استجابة GET: " . strlen($getResponse) . " حرف\n";
    
    // البحث عن عدد النتائج في HTML
    if (preg_match('/تم العثور على (\d+) وثيقة/', $getResponse, $matches)) {
        echo "عدد الوثائق في GET: {$matches[1]}\n";
    } elseif (strpos($getResponse, 'تم العثور على 0 وثيقة') !== false) {
        echo "تم العثور على 0 وثيقة في GET\n";
    } else {
        echo "لم يتم العثور على نص عدد الوثائق في GET\n";
    }
    
    // حفظ استجابة GET
    file_put_contents('get_response_debug.html', $getResponse);
    echo "تم حفظ استجابة GET في ملف get_response_debug.html\n";
}