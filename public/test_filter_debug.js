// اختبار شامل للفلاتر المترابطة
console.log('🔍 بدء اختبار الفلاتر الشامل...');

// دالة لطباعة معلومات مفصلة
function logDetailed(title, data) {
    console.log(`\n=== ${title} ===`);
    console.log(data);
    console.log('========================\n');
}

// 1. فحص وجود العناصر الأساسية
function checkBasicElements() {
    logDetailed('فحص العناصر الأساسية', {
        'نموذج الفلترة': !!document.getElementById('filterForm'),
        'CSRF Token': !!document.querySelector('meta[name="csrf-token"]'),
        'عدد القوائم المنسدلة': document.querySelectorAll('select[name^="fields["]').length,
        'URL الحالي': window.location.href
    });
}

// 2. فحص القوائم المنسدلة بالتفصيل
function checkDropdowns() {
    const selects = document.querySelectorAll('select[name^="fields["]');
    const dropdownInfo = [];
    
    selects.forEach((select, index) => {
        const fieldMatch = select.name.match(/fields\[(\d+)\]/);
        const fieldId = fieldMatch ? fieldMatch[1] : 'غير محدد';
        
        dropdownInfo.push({
            'الفهرس': index + 1,
            'معرف الحقل': fieldId,
            'الاسم': select.name,
            'القيمة الحالية': select.value || 'فارغ',
            'عدد الخيارات': select.options.length,
            'الخيارات المرئية': Array.from(select.options).filter(opt => opt.style.display !== 'none').length
        });
    });
    
    logDetailed('تفاصيل القوائم المنسدلة', dropdownInfo);
    return dropdownInfo;
}

// 3. اختبار AJAX مع تفاصيل كاملة
function testAjaxRequest(fieldId = '6', value = 'ساري ومعدل') {
    console.log(`\n🚀 اختبار AJAX للحقل ${fieldId} بالقيمة: ${value}`);
    
    const formData = new FormData();
    formData.append(`fields[${fieldId}]`, value);
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    if (!csrfToken) {
        console.error('❌ لا يوجد CSRF Token!');
        return;
    }
    
    const url = `/ar/documents/section/test-section/filter-counts`;
    console.log('📡 إرسال الطلب إلى:', url);
    
    fetch(url, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => {
        console.log('📥 استجابة الخادم:', {
            'الحالة': response.status,
            'نوع المحتوى': response.headers.get('content-type'),
            'حجم الاستجابة': response.headers.get('content-length')
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        return response.text();
    })
    .then(text => {
        console.log('📄 النص الخام للاستجابة:', text.substring(0, 500) + '...');
        
        try {
            const data = JSON.parse(text);
            logDetailed('بيانات الاستجابة المحللة', data);
            
            if (data.fieldCounts) {
                console.log('✅ تم العثور على fieldCounts');
                Object.keys(data.fieldCounts).forEach(fId => {
                    console.log(`الحقل ${fId}:`, data.fieldCounts[fId]);
                });
            } else {
                console.log('❌ لا توجد fieldCounts في الاستجابة');
            }
            
            if (data.documentCount !== undefined) {
                console.log(`📊 عدد الوثائق: ${data.documentCount}`);
            }
            
        } catch (e) {
            console.error('❌ خطأ في تحليل JSON:', e);
            console.log('النص الكامل:', text);
        }
    })
    .catch(error => {
        console.error('❌ خطأ في الطلب:', error);
    });
}

// 4. محاكاة تغيير الفلتر ومراقبة النتائج
function simulateFilterChange(fieldId = '6', value = 'ساري ومعدل') {
    console.log(`\n🎭 محاكاة تغيير الحقل ${fieldId} إلى: ${value}`);
    
    const select = document.querySelector(`select[name="fields[${fieldId}]"]`);
    
    if (!select) {
        console.error(`❌ لم يتم العثور على الحقل ${fieldId}`);
        return;
    }
    
    console.log('📋 حالة الحقل قبل التغيير:', {
        'القيمة': select.value,
        'عدد الخيارات': select.options.length
    });
    
    // تغيير القيمة
    select.value = value;
    
    // إطلاق حدث التغيير
    const changeEvent = new Event('change', { bubbles: true });
    select.dispatchEvent(changeEvent);
    
    console.log('✅ تم إطلاق حدث التغيير');
    
    // مراقبة التغييرات بعد ثانيتين
    setTimeout(() => {
        console.log('\n📊 حالة الفلاتر بعد التغيير:');
        checkDropdowns();
    }, 2000);
}

// 5. فحص مستمعي الأحداث
function checkEventListeners() {
    const form = document.getElementById('filterForm');
    if (!form) {
        console.error('❌ لا يوجد نموذج فلترة');
        return;
    }
    
    console.log('\n👂 فحص مستمعي الأحداث...');
    
    // محاولة الوصول لمستمعي الأحداث (قد لا يعمل في جميع المتصفحات)
    const selects = document.querySelectorAll('select[name^="fields["]');
    selects.forEach((select, index) => {
        console.log(`القائمة ${index + 1} (${select.name}):`, {
            'لديها مستمع تغيير': select.onchange !== null,
            'معرف العنصر': select.id || 'بدون معرف'
        });
    });
}

// 6. اختبار شامل
function runFullTest() {
    console.clear();
    console.log('🧪 بدء الاختبار الشامل للفلاتر...');
    
    // الخطوة 1: فحص العناصر الأساسية
    checkBasicElements();
    
    // الخطوة 2: فحص القوائم المنسدلة
    const dropdowns = checkDropdowns();
    
    // الخطوة 3: فحص مستمعي الأحداث
    checkEventListeners();
    
    // الخطوة 4: اختبار AJAX
    setTimeout(() => {
        testAjaxRequest();
    }, 1000);
    
    // الخطوة 5: محاكاة تغيير الفلتر
    setTimeout(() => {
        simulateFilterChange();
    }, 3000);
    
    console.log('\n⏰ سيتم تشغيل الاختبارات تدريجياً...');
}

// تشغيل الاختبار الشامل
runFullTest();

// إضافة دوال للاستخدام اليدوي
window.testFilters = {
    runFullTest,
    checkBasicElements,
    checkDropdowns,
    testAjaxRequest,
    simulateFilterChange,
    checkEventListeners
};

console.log('\n🎯 يمكنك استخدام الدوال التالية يدوياً:');
console.log('- testFilters.runFullTest() - تشغيل الاختبار الشامل');
console.log('- testFilters.checkDropdowns() - فحص القوائم المنسدلة');
console.log('- testFilters.testAjaxRequest(fieldId, value) - اختبار AJAX');
console.log('- testFilters.simulateFilterChange(fieldId, value) - محاكاة تغيير الفلتر');

console.log('\n🚀 انتهى إعداد الاختبارات. تحقق من النتائج أعلاه.');}}}