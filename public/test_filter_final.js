// اختبار شامل للفلاتر المترابطة - الإصدار النهائي
console.log('🔧 بدء اختبار الفلاتر المترابطة...');

// 1. فحص البيئة الأساسية
function checkEnvironment() {
    console.log('\n=== فحص البيئة ===');
    
    const form = document.getElementById('filterForm');
    console.log('نموذج الفلاتر:', form ? '✅ موجود' : '❌ غير موجود');
    
    const selects = document.querySelectorAll('select[name^="fields["]');
    console.log('عدد القوائم المنسدلة:', selects.length);
    
    selects.forEach((select, index) => {
        const fieldId = select.name.match(/fields\[(\d+)\]/)?.[1];
        console.log(`  - حقل ${fieldId}: ${select.options.length} خيارات`);
    });
    
    return { form, selects };
}

// 2. اختبار طلب AJAX للفلاتر
function testFilterAjax(excludeFieldId = null) {
    console.log('\n=== اختبار طلب AJAX ===');
    
    const form = document.getElementById('filterForm');
    if (!form) {
        console.error('❌ لا يمكن العثور على النموذج');
        return;
    }
    
    const formData = new FormData(form);
    if (excludeFieldId) {
        formData.append('exclude_field_id', excludeFieldId);
        console.log(`📤 استبعاد الحقل: ${excludeFieldId}`);
    }
    
    // طباعة البيانات المرسلة
    console.log('📤 البيانات المرسلة:');
    for (let [key, value] of formData.entries()) {
        console.log(`  ${key}: ${value}`);
    }
    
    const url = window.location.pathname + '/filter-counts';
    console.log(`📡 إرسال طلب إلى: ${url}`);
    
    return fetch(url, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => {
        console.log(`📥 استجابة الخادم: ${response.status}`);
        return response.json();
    })
    .then(data => {
        console.log('📊 البيانات المستلمة:', data);
        
        if (data.success && data.fieldCounts) {
            console.log('✅ تم استلام عدادات الحقول بنجاح');
            Object.keys(data.fieldCounts).forEach(fieldId => {
                console.log(`  حقل ${fieldId}:`, data.fieldCounts[fieldId]);
            });
        } else {
            console.error('❌ خطأ في البيانات المستلمة');
        }
        
        return data;
    })
    .catch(error => {
        console.error('❌ خطأ في الطلب:', error);
    });
}

// 3. محاكاة تغيير فلتر
function simulateFilterChange(fieldId, value) {
    console.log(`\n=== محاكاة تغيير الحقل ${fieldId} إلى "${value}" ===`);
    
    const select = document.querySelector(`select[name="fields[${fieldId}]"]`);
    if (!select) {
        console.error(`❌ لا يمكن العثور على الحقل ${fieldId}`);
        return;
    }
    
    // تغيير القيمة
    select.value = value;
    console.log(`✅ تم تغيير قيمة الحقل ${fieldId} إلى "${value}"`);
    
    // إطلاق حدث التغيير
    const changeEvent = new Event('change', { bubbles: true });
    select.dispatchEvent(changeEvent);
    console.log('🔄 تم إطلاق حدث التغيير');
    
    // انتظار قليل ثم اختبار النتيجة
    setTimeout(() => {
        testFilterAjax(fieldId).then(() => {
            console.log('✅ تم اختبار الفلاتر بعد التغيير');
        });
    }, 500);
}

// 4. اختبار شامل
function runFullTest() {
    console.log('\n🚀 بدء الاختبار الشامل...');
    
    // فحص البيئة
    const { form, selects } = checkEnvironment();
    
    if (!form || selects.length === 0) {
        console.error('❌ لا يمكن إجراء الاختبار - البيئة غير مكتملة');
        return;
    }
    
    // اختبار طلب AJAX الأساسي
    testFilterAjax().then(() => {
        console.log('\n⏳ انتظار 2 ثانية قبل اختبار التغيير...');
        
        setTimeout(() => {
            // اختبار تغيير الحقل الأول إذا كان متاحاً
            const firstSelect = selects[0];
            const fieldId = firstSelect.name.match(/fields\[(\d+)\]/)?.[1];
            
            if (fieldId && firstSelect.options.length > 1) {
                const firstOption = firstSelect.options[1].value; // أول خيار غير فارغ
                simulateFilterChange(fieldId, firstOption);
            } else {
                console.log('⚠️ لا توجد خيارات متاحة للاختبار');
            }
        }, 2000);
    });
}

// 5. اختبار محدد للحقل 6
function testField6() {
    console.log('\n=== اختبار محدد للحقل 6 ===');
    simulateFilterChange(6, 'ساري ومعدل');
}

// 6. فحص الأحداث المرتبطة
function checkEventListeners() {
    console.log('\n=== فحص الأحداث المرتبطة ===');
    
    const form = document.getElementById('filterForm');
    if (!form) {
        console.error('❌ لا يمكن العثور على النموذج');
        return;
    }
    
    const selects = form.querySelectorAll('select[name^="fields["]');
    console.log(`🔍 فحص ${selects.length} قائمة منسدلة...`);
    
    selects.forEach((select, index) => {
        const fieldId = select.name.match(/fields\[(\d+)\]/)?.[1];
        
        // إضافة مستمع مؤقت لاختبار الأحداث
        const testListener = (e) => {
            console.log(`🎯 تم تشغيل حدث التغيير للحقل ${fieldId}:`, e.target.value);
        };
        
        select.addEventListener('change', testListener);
        
        // إزالة المستمع بعد 10 ثوان
        setTimeout(() => {
            select.removeEventListener('change', testListener);
        }, 10000);
    });
    
    console.log('✅ تم إضافة مستمعات الاختبار (سيتم إزالتها بعد 10 ثوان)');
}

// تصدير الدوال للاستخدام في وحدة التحكم
window.filterTest = {
    checkEnvironment,
    testFilterAjax,
    simulateFilterChange,
    runFullTest,
    testField6,
    checkEventListeners
};

console.log('\n✅ تم تحميل أدوات اختبار الفلاتر!');
console.log('📋 الدوال المتاحة:');
console.log('  - filterTest.checkEnvironment() - فحص البيئة');
console.log('  - filterTest.testFilterAjax() - اختبار طلب AJAX');
console.log('  - filterTest.simulateFilterChange(fieldId, value) - محاكاة تغيير فلتر');
console.log('  - filterTest.runFullTest() - اختبار شامل');
console.log('  - filterTest.testField6() - اختبار الحقل 6');
console.log('  - filterTest.checkEventListeners() - فحص الأحداث');
console.log('\n🚀 لبدء الاختبار الشامل، اكتب: filterTest.runFullTest()');