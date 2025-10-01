<?php

echo "=== تشخيص بسيط للطلب ===\n";

// محاكاة البيانات المرسلة من المتصفح
$queryString = 'search=&sort=latest&per_page=12&fields%5B9%5D=%D8%B5%D8%AD%D8%A7%D9%81%D8%A9&fields%5B10%5D=&fields%5B6%5D=&fields%5B3%5D=&fields%5B2%5D%5Bday%5D=&fields%5B2%5D%5Bmonth%5D=&fields%5B2%5D%5Byear%5D=';

echo "Query String الأصلي:\n{$queryString}\n\n";

// تحليل Query String
parse_str($queryString, $parsedData);

echo "البيانات بعد التحليل:\n";
print_r($parsedData);

echo "\n=== فحص قيمة الحقل 9 ===\n";
echo "قيمة fields[9]: '" . ($parsedData['fields'][9] ?? 'غير موجود') . "'\n";
echo "طول النص: " . strlen($parsedData['fields'][9] ?? '') . " حرف\n";
echo "تشفير UTF-8: " . (mb_check_encoding($parsedData['fields'][9] ?? '', 'UTF-8') ? 'صحيح' : 'خطأ') . "\n";

// فحص التشفير
$originalValue = $parsedData['fields'][9] ?? '';
echo "\nالقيمة بالـ hex: " . bin2hex($originalValue) . "\n";
echo "الأحرف:\n";
for ($i = 0; $i < mb_strlen($originalValue, 'UTF-8'); $i++) {
    $char = mb_substr($originalValue, $i, 1, 'UTF-8');
    echo "  {$i}: '{$char}' (" . bin2hex($char) . ")\n";
}

// مقارنة مع النص المتوقع
$expectedText = 'صحافة';
echo "\n=== مقارنة مع النص المتوقع ===\n";
echo "النص المتوقع: '{$expectedText}'\n";
echo "النص المستلم: '{$originalValue}'\n";
echo "هل متطابقان؟ " . ($originalValue === $expectedText ? 'نعم' : 'لا') . "\n";

if ($originalValue !== $expectedText) {
    echo "\nالاختلافات:\n";
    echo "طول المتوقع: " . mb_strlen($expectedText, 'UTF-8') . "\n";
    echo "طول المستلم: " . mb_strlen($originalValue, 'UTF-8') . "\n";
    echo "المتوقع hex: " . bin2hex($expectedText) . "\n";
    echo "المستلم hex: " . bin2hex($originalValue) . "\n";
}

// اختبار البحث بنمط LIKE
echo "\n=== اختبار نمط البحث ===\n";
$searchPattern = "%{$originalValue}%";
echo "نمط البحث: '{$searchPattern}'\n";

$testValues = [
    'قانون حرية الصحافة',
    'صحافة',
    'الصحافة المصرية',
    'قانون الصحافة'
];

echo "\nاختبار التطابق:\n";
foreach ($testValues as $testValue) {
    $matches = (strpos($testValue, $originalValue) !== false);
    echo "  '{$testValue}' -> " . ($matches ? 'يطابق' : 'لا يطابق') . "\n";
}

// اختبار مع تنظيف النص
echo "\n=== اختبار مع تنظيف النص ===\n";
$cleanedValue = trim($originalValue);
echo "القيمة بعد trim: '{$cleanedValue}'\n";
echo "هل تغيرت؟ " . ($cleanedValue !== $originalValue ? 'نعم' : 'لا') . "\n";

if ($cleanedValue !== $originalValue) {
    echo "الفرق: '" . str_replace($cleanedValue, '', $originalValue) . "'\n";
}