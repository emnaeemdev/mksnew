// اختبار مباشر ومفصل للفلاتر
console.clear();
console.log('🔥 اختبار مباشر للفلاتر - تشخيص شامل');
console.log('='.repeat(50));

// 1. فحص البيئة الأساسية
function diagnoseEnvironment() {
    console.log('\n🔍 فحص البيئة الأساسية:');
    
    const results = {
        'URL الحالي': window.location.href,
        'نموذج الفلترة موجود': !!document.getElementById('filterForm'),
        'CSRF Token موجود': !!document.querySelector('meta[name="csrf-token"]'),
        'jQuery محمل': typeof $ !== 'undefined',
        'عدد القوائم المنسدلة': document.querySelectorAll('select[name^="fields["]').length
    };
    
    Object.entries(results).forEach(([key, value]) => {
        const icon = value === true || (typeof value === 'number' && value > 0) ? '✅' : '❌';
        console.log(`${icon} ${key}: ${value}`);
    });
    
    return results;
}

// 2. فحص تفصيلي للقوائم المنسدلة
function analyzeDropdowns() {
    console.log('\n📋 تحليل القوائم المنسدلة:');
    
    const selects = document.querySelectorAll('select[name^="fields["]');
    const analysis = [];
    
    selects.forEach((select, index) => {
        const fieldMatch = select.name.match(/fields\\[(\\d+)\\]/);
        const fieldId = fieldMatch ? fieldMatch[1] : null;
        
        const info = {
            index: index + 1,
            fieldId: fieldId,
            name: select.name,
            currentValue: select.value || 'فارغ',
            totalOptions: select.options.length,
            visibleOptions: Array.from(select.options).filter(opt => opt.style.display !== 'none').length,
            hasChangeListener: !!select.onchange,
            isDisabled: select.disabled
        };
        
        analysis.push(info);
        
        console.log(`📌 القائمة ${info.index}:`);
        console.log(`   معرف الحقل: ${info.fieldId}`);
        console.log(`   الاسم: ${info.name}`);
        console.log(`   القيمة الحالية: ${info.currentValue}`);
        console.log(`   الخيارات: ${info.visibleOptions}/${info.totalOptions} مرئية`);
        console.log(`   مستمع التغيير: ${info.hasChangeListener ? 'موجود' : 'غير موجود'}`);
        console.log(`   معطل: ${info.isDisabled ? 'نعم' : 'لا'}`);
        console.log('');
    });
    
    return analysis;
}

// 3. اختبار AJAX مع تتبع مفصل
function testAjaxDetailed(fieldId = '6', value = 'ساري ومعدل') {
    console.log(`\n🚀 اختبار AJAX مفصل - الحقل ${fieldId}:`);
    
    const formData = new FormData();
    formData.append(`fields[${fieldId}]`, value);
    
    // إضافة جميع القيم الحالية للفلاتر
    const allSelects = document.querySelectorAll('select[name^="fields["]');
    allSelects.forEach(select => {
        if (select.value && select.name !== `fields[${fieldId}]`) {
            formData.append(select.name, select.value);
            console.log(`📎 إضافة فلتر موجود: ${select.name} = ${select.value}`);
        }
    });
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const url = `/ar/documents/section/test-section/filter-counts`;
    
    console.log(`📡 إرسال طلب إلى: ${url}`);
    console.log(`🔑 CSRF Token: ${csrfToken ? 'موجود' : 'غير موجود'}`);
    
    // طباعة محتويات FormData
    console.log('📦 محتويات الطلب:');
    for (let [key, value] of formData.entries()) {
        console.log(`   ${key}: ${value}`);
    }
    
    const startTime = Date.now();
    
    return fetch(url, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => {
        const duration = Date.now() - startTime;
        console.log(`⏱️ مدة الاستجابة: ${duration}ms`);
        console.log(`📥 حالة الاستجابة: ${response.status} ${response.statusText}`);
        console.log(`📋 نوع المحتوى: ${response.headers.get('content-type')}`);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        return response.text();
    })
    .then(text => {
        console.log(`📄 حجم الاستجابة: ${text.length} حرف`);
        console.log(`📝 أول 200 حرف: ${text.substring(0, 200)}...`);
        
        try {
            const data = JSON.parse(text);
            console.log('✅ تم تحليل JSON بنجاح');
            
            // تحليل البيانات
            if (data.fieldCounts) {
                console.log(`📊 عدد الحقول في fieldCounts: ${Object.keys(data.fieldCounts).length}`);
                
                Object.entries(data.fieldCounts).forEach(([fId, counts]) => {
                    console.log(`   الحقل ${fId}: ${Object.keys(counts).length} خيار متاح`);
                    
                    // طباعة أول 3 خيارات كمثال
                    const entries = Object.entries(counts).slice(0, 3);
                    entries.forEach(([option, count]) => {
                        console.log(`     - ${option}: ${count} وثيقة`);
                    });
                    
                    if (Object.keys(counts).length > 3) {
                        console.log(`     ... و ${Object.keys(counts).length - 3} خيار آخر`);
                    }
                });
            } else {
                console.log('❌ لا توجد fieldCounts في الاستجابة');
            }
            
            if (data.documentCount !== undefined) {
                console.log(`📄 عدد الوثائق المطابقة: ${data.documentCount}`);
            }
            
            return data;
            
        } catch (e) {
            console.error('❌ خطأ في تحليل JSON:', e.message);
            console.log('📄 النص الكامل للاستجابة:');
            console.log(text);
            throw e;
        }
    })
    .catch(error => {
        console.error('❌ خطأ في الطلب:', error.message);
        console.error('🔍 تفاصيل الخطأ:', error);
        throw error;
    });
}

// 4. محاكاة تغيير الفلتر مع مراقبة شاملة
function simulateFilterChangeDetailed(fieldId = '6', value = 'ساري ومعدل') {
    console.log(`\n🎭 محاكاة تغيير مفصلة - الحقل ${fieldId}:`);
    
    const select = document.querySelector(`select[name="fields[${fieldId}]"]`);
    
    if (!select) {
        console.error(`❌ لم يتم العثور على الحقل ${fieldId}`);
        return Promise.reject(new Error(`Field ${fieldId} not found`));
    }
    
    // حفظ الحالة قبل التغيير
    const beforeState = {
        value: select.value,
        optionsCount: select.options.length,
        visibleOptions: Array.from(select.options).filter(opt => opt.style.display !== 'none').length
    };
    
    console.log('📋 الحالة قبل التغيير:', beforeState);
    
    // تغيير القيمة
    select.value = value;
    console.log(`✏️ تم تغيير القيمة إلى: ${value}`);
    
    // إطلاق حدث التغيير
    const changeEvent = new Event('change', { bubbles: true });
    select.dispatchEvent(changeEvent);
    console.log('🔔 تم إطلاق حدث التغيير');
    
    // مراقبة التغييرات
    return new Promise((resolve) => {
        setTimeout(() => {
            console.log('\n📊 مراقبة التغييرات بعد 2 ثانية:');
            
            const afterState = {
                value: select.value,
                optionsCount: select.options.length,
                visibleOptions: Array.from(select.options).filter(opt => opt.style.display !== 'none').length
            };
            
            console.log('📋 الحالة بعد التغيير:', afterState);
            
            // مقارنة الحالات
            const changes = {
                valueChanged: beforeState.value !== afterState.value,
                optionsCountChanged: beforeState.optionsCount !== afterState.optionsCount,
                visibleOptionsChanged: beforeState.visibleOptions !== afterState.visibleOptions
            };
            
            console.log('🔄 التغييرات المكتشفة:', changes);
            
            // فحص القوائم الأخرى
            const otherSelects = document.querySelectorAll('select[name^="fields["]');
            console.log('\n🔍 فحص القوائم الأخرى:');
            
            otherSelects.forEach((otherSelect, index) => {
                if (otherSelect !== select) {
                    const otherFieldMatch = otherSelect.name.match(/fields\\[(\\d+)\\]/);
                    const otherFieldId = otherFieldMatch ? otherFieldMatch[1] : 'غير محدد';
                    
                    console.log(`   القائمة ${otherFieldId}: ${otherSelect.options.length} خيار، ${Array.from(otherSelect.options).filter(opt => opt.style.display !== 'none').length} مرئي`);
                }
            });
            
            resolve({ beforeState, afterState, changes });
        }, 2000);
    });
}

// 5. اختبار شامل متسلسل
async function runComprehensiveTest() {
    console.log('🧪 بدء الاختبار الشامل المتسلسل...');
    
    try {
        // الخطوة 1: فحص البيئة
        const envResults = diagnoseEnvironment();
        
        if (!envResults['نموذج الفلترة موجود']) {
            throw new Error('نموذج الفلترة غير موجود!');
        }
        
        // الخطوة 2: تحليل القوائم
        const dropdownAnalysis = analyzeDropdowns();
        
        if (dropdownAnalysis.length === 0) {
            throw new Error('لا توجد قوائم منسدلة!');
        }
        
        // الخطوة 3: اختبار AJAX
        console.log('\n⏳ انتظار ثانية واحدة قبل اختبار AJAX...');
        await new Promise(resolve => setTimeout(resolve, 1000));
        
        const ajaxResult = await testAjaxDetailed();
        
        // الخطوة 4: محاكاة التغيير
        console.log('\n⏳ انتظار ثانية واحدة قبل محاكاة التغيير...');
        await new Promise(resolve => setTimeout(resolve, 1000));
        
        const simulationResult = await simulateFilterChangeDetailed();
        
        // النتيجة النهائية
        console.log('\n🎉 انتهى الاختبار الشامل!');
        console.log('='.repeat(50));
        
        const summary = {
            environment: envResults,
            dropdowns: dropdownAnalysis.length,
            ajaxSuccess: !!ajaxResult,
            simulationSuccess: !!simulationResult
        };
        
        console.log('📋 ملخص النتائج:', summary);
        
        return summary;
        
    } catch (error) {
        console.error('💥 فشل الاختبار:', error.message);
        console.error('🔍 تفاصيل الخطأ:', error);
        throw error;
    }
}

// تشغيل الاختبار الشامل
runComprehensiveTest()
    .then(results => {
        console.log('\n✅ تم الانتهاء من جميع الاختبارات بنجاح!');
        console.log('📊 النتائج النهائية:', results);
    })
    .catch(error => {
        console.log('\n❌ فشلت الاختبارات!');
        console.error('السبب:', error.message);
    });

// إضافة الدوال للاستخدام اليدوي
window.filterDiagnostics = {
    diagnoseEnvironment,
    analyzeDropdowns,
    testAjaxDetailed,
    simulateFilterChangeDetailed,
    runComprehensiveTest
};

console.log('\n🎯 الدوال المتاحة للاستخدام اليدوي:');
console.log('- filterDiagnostics.runComprehensiveTest() - الاختبار الشامل');
console.log('- filterDiagnostics.diagnoseEnvironment() - فحص البيئة');
console.log('- filterDiagnostics.analyzeDropdowns() - تحليل القوائم');
console.log('- filterDiagnostics.testAjaxDetailed(fieldId, value) - اختبار AJAX');
console.log('- filterDiagnostics.simulateFilterChangeDetailed(fieldId, value) - محاكاة التغيير');

console.log('\n🚀 الاختبار الشامل قيد التشغيل... تابع النتائج أعلاه.');