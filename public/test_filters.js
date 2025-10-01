// اختبار الفلاتر في الكونسول
console.log('=== اختبار الفلاتر ===');

// الحصول على النموذج
const form = document.querySelector('form');
if (!form) {
    console.error('لم يتم العثور على النموذج');
} else {
    console.log('تم العثور على النموذج:', form);
}

// اختبار طلب AJAX للفلاتر
function testFilterAjax() {
    console.log('بدء اختبار AJAX...');
    
    // إنشاء FormData
    const formData = new FormData();
    formData.append('fields[6]', 'ساري ومعدل');
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    
    // الحصول على URL من الصفحة الحالية
    const currentUrl = window.location.href;
    const filterUrl = currentUrl + '/filter-counts';
    
    console.log('URL للفلاتر:', filterUrl);
    console.log('البيانات المرسلة:', Object.fromEntries(formData));
    
    fetch(filterUrl, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        console.log('رمز الاستجابة:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('استجابة AJAX:', data);
        
        if (data.success && data.fieldCounts) {
            console.log('=== تحليل النتائج ===');
            Object.keys(data.fieldCounts).forEach(fieldId => {
                const counts = data.fieldCounts[fieldId];
                console.log(`Field ${fieldId}:`, counts);
                
                if (typeof counts === 'object' && !Array.isArray(counts)) {
                    const totalOptions = Object.keys(counts).length;
                    const availableOptions = Object.values(counts).filter(count => count > 0).length;
                    
                    console.log(`  إجمالي الخيارات: ${totalOptions}`);
                    console.log(`  الخيارات المتاحة: ${availableOptions}`);
                    
                    Object.entries(counts).forEach(([option, count]) => {
                        const status = count > 0 ? '✓' : '✗';
                        console.log(`  ${status} ${option}: ${count}`);
                    });
                }
            });
        } else {
            console.error('فشل في الاستجابة:', data);
        }
    })
    .catch(error => {
        console.error('خطأ في AJAX:', error);
    });
}

// اختبار تحديث الخيارات
function testUpdateOptions() {
    console.log('=== اختبار تحديث الخيارات ===');
    
    // البحث عن قوائم منسدلة
    const selects = document.querySelectorAll('select[name^="fields["]');
    console.log('عدد القوائم المنسدلة:', selects.length);
    
    selects.forEach((select, index) => {
        console.log(`قائمة ${index + 1}:`, {
            name: select.name,
            value: select.value,
            options: Array.from(select.options).map(opt => ({
                value: opt.value,
                text: opt.textContent,
                hidden: opt.style.display === 'none'
            }))
        });
    });
}

// اختبار محاكاة تغيير فلتر
function simulateFilterChange() {
    console.log('=== محاكاة تغيير فلتر ===');
    
    const field6Select = document.querySelector('select[name="fields[6]"]');
    if (field6Select) {
        console.log('تم العثور على Field 6');
        
        // تغيير القيمة
        field6Select.value = 'ساري ومعدل';
        
        // إطلاق حدث التغيير
        const changeEvent = new Event('change', { bubbles: true });
        field6Select.dispatchEvent(changeEvent);
        
        console.log('تم إطلاق حدث التغيير');
        
        // انتظار ثم فحص النتائج
        setTimeout(() => {
            testUpdateOptions();
        }, 1000);
    } else {
        console.error('لم يتم العثور على Field 6');
    }
}

// تشغيل الاختبارات
console.log('الاختبارات المتاحة:');
console.log('- testFilterAjax(): اختبار طلب AJAX');
console.log('- testUpdateOptions(): فحص حالة الخيارات');
console.log('- simulateFilterChange(): محاكاة تغيير فلتر');

// تشغيل اختبار أولي
testUpdateOptions();