# دليل نقل البيانات من قاعدة البيانات القديمة

## نظرة عامة

هذا الدليل يوضح كيفية نقل البيانات من قاعدة البيانات القديمة (جدول `lawegs` واحد) إلى هيكل قاعدة البيانات الجديدة (عدة جداول منفصلة).

## هيكل البيانات

### قاعدة البيانات القديمة
- **جدول واحد**: `lawegs`
- **الحقول الرئيسية**: `title_ar`, `description_ar`, `laweg_number`, `laweg_release_date`, `laweg_subject`, `laweg_issuer_pub`, `laweg_type`

### قاعدة البيانات الجديدة
- **جدول الوثائق**: `documents` (العنوان والمحتوى)
- **جدول المستخدمين**: `users` (سيتم ربط جميع الوثائق بالمستخدم رقم 1)
- **جدول قيم الحقول المخصصة**: `document_field_values` (الحقول المخصصة)

## ربط الحقول

| الحقل القديم | الحقل الجديد | النوع | الوصف |
|--------------|-------------|-------|-------|
| `laweg_number` | `law_number` (ID: 9) | text | رقم القانون |
| `laweg_release_date` | `law_date` (ID: 2) | date | تاريخ الوثيقة |
| `laweg_subject` | `law_subject` (ID: 10) | select | التصنيف وفقاً للحق |
| `laweg_issuer_pub` | `law_issuer_pub` (ID: 3) | select | جهة الإصدار |
| `laweg_type` | `law_type` (ID: 6) | select | حالة القانون |

## الطرق المتاحة للنقل

### الطريقة الأولى: استخدام سكريبت PHP التفاعلي

```bash
php migrate_old_data.php
```

**المميزات:**
- تفاعلي وسهل الاستخدام
- يتعامل مع الأخطاء تلقائياً
- يعرض تقرير مفصل عن النقل
- يتحقق من صحة البيانات

**المتطلبات:**
- تعديل إعدادات الاتصال بقاعدة البيانات القديمة في السكريبت
- التأكد من وجود المستخدم رقم 1

### الطريقة الثانية: استخدام استعلامات SQL مباشرة

```bash
php generate_migration_sql.php > migration_queries.sql
```

**المميزات:**
- استعلامات SQL جاهزة للتنفيذ
- يمكن تنفيذها في phpMyAdmin أو أي أداة إدارة قواعد بيانات
- مرونة أكبر في التحكم
- يمكن مراجعة الاستعلامات قبل التنفيذ

**خطوات التنفيذ:**
1. تشغيل مولد الاستعلامات
2. نسخ الاستعلامات من ملف `migration_queries.sql`
3. استبدال `old_database` باسم قاعدة البيانات القديمة
4. تنفيذ الاستعلامات بالترتيب

## خطوات النقل التفصيلية

### 1. التحضير

```sql
-- التأكد من وجود المستخدم الافتراضي
INSERT IGNORE INTO users (id, name, email, email_verified_at, created_at, updated_at) 
VALUES (1, 'مدير النظام', 'admin@example.com', NOW(), NOW(), NOW());
```

### 2. نقل الوثائق الأساسية

```sql
INSERT INTO documents (
    section_id, user_id, title, content, excerpt, slug,
    is_published, is_featured, views_count, downloads_count, shares_count,
    published_at, created_at, updated_at
) 
SELECT 
    1 as section_id, -- قسم القوانين
    1 as user_id,
    title_ar as title,
    COALESCE(description_ar, '') as content,
    COALESCE(brief_ar, '') as excerpt,
    COALESCE(slug_ar, CONCAT('law-', id)) as slug,
    1 as is_published,
    0 as is_featured,
    COALESCE(total_views, 0) as views_count,
    COALESCE(total_downloads, 0) as downloads_count,
    COALESCE(total_shares, 0) as shares_count,
    COALESCE(created_at, NOW()) as published_at,
    COALESCE(created_at, NOW()) as created_at,
    COALESCE(updated_at, NOW()) as updated_at
FROM old_database.lawegs 
WHERE status = 'published' AND deleted_at IS NULL;
```

### 3. نقل الحقول المخصصة

```sql
-- رقم القانون
INSERT INTO document_field_values (document_id, field_id, value, created_at, updated_at)
SELECT 
    d.id as document_id,
    9 as field_id, -- رقم القانون
    old_l.laweg_number as value,
    NOW() as created_at,
    NOW() as updated_at
FROM documents d
JOIN old_database.lawegs old_l ON (
    d.title = old_l.title_ar 
    AND d.section_id = 1
)
WHERE old_l.laweg_number IS NOT NULL 
  AND old_l.laweg_number != '' 
  AND old_l.status = 'published' 
  AND old_l.deleted_at IS NULL;

-- تاريخ الوثيقة
INSERT INTO document_field_values (document_id, field_id, value, created_at, updated_at)
SELECT 
    d.id as document_id,
    2 as field_id, -- تاريخ الوثيقة
    old_l.laweg_release_date as value,
    NOW() as created_at,
    NOW() as updated_at
FROM documents d
JOIN old_database.lawegs old_l ON (
    d.title = old_l.title_ar 
    AND d.section_id = 1
)
WHERE old_l.laweg_release_date IS NOT NULL 
  AND old_l.laweg_release_date != '' 
  AND old_l.status = 'published' 
  AND old_l.deleted_at IS NULL;

-- التصنيف وفقاً للحق
INSERT INTO document_field_values (document_id, field_id, value, created_at, updated_at)
SELECT 
    d.id as document_id,
    10 as field_id, -- التصنيف وفقاً للحق
    old_l.laweg_subject as value,
    NOW() as created_at,
    NOW() as updated_at
FROM documents d
JOIN old_database.lawegs old_l ON (
    d.title = old_l.title_ar 
    AND d.section_id = 1
)
WHERE old_l.laweg_subject IS NOT NULL 
  AND old_l.laweg_subject != '' 
  AND old_l.status = 'published' 
  AND old_l.deleted_at IS NULL;

-- جهة الإصدار
INSERT INTO document_field_values (document_id, field_id, value, created_at, updated_at)
SELECT 
    d.id as document_id,
    3 as field_id, -- جهة الإصدار
    old_l.laweg_issuer_pub as value,
    NOW() as created_at,
    NOW() as updated_at
FROM documents d
JOIN old_database.lawegs old_l ON (
    d.title = old_l.title_ar 
    AND d.section_id = 1
)
WHERE old_l.laweg_issuer_pub IS NOT NULL 
  AND old_l.laweg_issuer_pub != '' 
  AND old_l.status = 'published' 
  AND old_l.deleted_at IS NULL;

-- حالة القانون
INSERT INTO document_field_values (document_id, field_id, value, created_at, updated_at)
SELECT 
    d.id as document_id,
    6 as field_id, -- حالة القانون
    old_l.laweg_type as value,
    NOW() as created_at,
    NOW() as updated_at
FROM documents d
JOIN old_database.lawegs old_l ON (
    d.title = old_l.title_ar 
    AND d.section_id = 1
)
WHERE old_l.laweg_type IS NOT NULL 
  AND old_l.laweg_type != '' 
  AND old_l.status = 'published' 
  AND old_l.deleted_at IS NULL;
```

## التحقق من النتائج

```sql
-- عدد الوثائق المنقولة
SELECT COUNT(*) as total_documents 
FROM documents 
WHERE section_id = 1;

-- عدد قيم الحقول المخصصة
SELECT 
    cf.label as field_name, 
    COUNT(dfv.id) as values_count
FROM document_custom_fields cf
LEFT JOIN document_field_values dfv ON cf.id = dfv.field_id
JOIN documents d ON d.id = dfv.document_id
WHERE cf.section_id = 1 AND d.section_id = 1
GROUP BY cf.id, cf.label
ORDER BY cf.label;
```

## نصائح مهمة

### قبل النقل
1. **عمل نسخة احتياطية** من قاعدة البيانات الجديدة
2. **اختبار النقل** على عينة صغيرة أولاً
3. **التحقق من وجود المستخدم رقم 1** في جدول `users`
4. **مراجعة قيم الحقول** للتأكد من توافقها مع الخيارات المحددة

### أثناء النقل
1. **تنفيذ الاستعلامات بالترتيب** المحدد
2. **مراقبة الأخطاء** والتعامل معها فوراً
3. **التحقق من النتائج** بعد كل خطوة

### بعد النقل
1. **التحقق من صحة البيانات** المنقولة
2. **اختبار البحث والفلترة** للتأكد من عمل الحقول المخصصة
3. **مراجعة الروابط والصور** إذا كانت موجودة

## استعلامات التنظيف

```sql
-- في حالة الحاجة لإعادة النقل
DELETE FROM document_field_values 
WHERE document_id IN (
    SELECT id FROM documents WHERE section_id = 1
);

DELETE FROM documents WHERE section_id = 1;
```

## الأقسام الأخرى

لنقل بيانات أقسام أخرى:
1. تحديد معرف القسم الجديد
2. تحديد الحقول المخصصة للقسم
3. تعديل الاستعلامات وفقاً لذلك
4. تكرار عملية النقل

## الدعم والمساعدة

في حالة مواجهة أي مشاكل:
1. مراجعة ملفات السجلات (logs)
2. التحقق من صحة الاستعلامات
3. التأكد من صحة أسماء قواعد البيانات والجداول
4. مراجعة صلاحيات قاعدة البيانات

---

**ملاحظة**: هذا الدليل مخصص لقسم القوانين، ولكن يمكن تطبيق نفس المبادئ على الأقسام الأخرى مع تعديل معرفات الأقسام والحقول المخصصة.