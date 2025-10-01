<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Section;
use App\Models\CustomField;
use App\Models\SectionData;
use App\Models\SectionDataValue;
use App\Models\User;

class SectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // إنشاء مستخدم تجريبي إذا لم يكن موجوداً
        $user = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'مدير النظام',
                'password' => bcrypt('password'),
            ]
        );

        // إنشاء قسم المنتجات
        $productsSection = Section::create([
            'name' => 'المنتجات',
            'slug' => 'products',
            'description' => 'قسم خاص بعرض المنتجات والخدمات',
            'icon' => 'fas fa-box',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        // إنشاء حقول مخصصة للمنتجات
        $priceField = CustomField::create([
            'section_id' => $productsSection->id,
            'name' => 'price',
            'label' => 'السعر',
            'type' => 'number',
            'placeholder' => 'أدخل السعر',
            'help_text' => 'السعر بالريال السعودي',
            'is_required' => true,
            'is_searchable' => true,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $categoryField = CustomField::create([
            'section_id' => $productsSection->id,
            'name' => 'category',
            'label' => 'الفئة',
            'type' => 'select',
            'options' => json_encode(['إلكترونيات', 'ملابس', 'كتب', 'رياضة', 'منزل وحديقة']),
            'is_required' => true,
            'is_searchable' => true,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $descriptionField = CustomField::create([
            'section_id' => $productsSection->id,
            'name' => 'description',
            'label' => 'الوصف',
            'type' => 'textarea',
            'placeholder' => 'وصف تفصيلي للمنتج',
            'is_required' => false,
            'is_searchable' => true,
            'is_active' => true,
            'sort_order' => 3,
        ]);

        $availableField = CustomField::create([
            'section_id' => $productsSection->id,
            'name' => 'available',
            'label' => 'متوفر',
            'type' => 'boolean',
            'is_required' => false,
            'is_searchable' => true,
            'is_active' => true,
            'sort_order' => 4,
        ]);

        // إنشاء بيانات تجريبية للمنتجات
        $products = [
            [
                'title' => 'لابتوب Dell XPS 13',
                'price' => '4500',
                'category' => 'إلكترونيات',
                'description' => 'لابتوب عالي الأداء مع معالج Intel Core i7 وذاكرة 16GB RAM',
                'available' => '1'
            ],
            [
                'title' => 'قميص قطني أزرق',
                'price' => '120',
                'category' => 'ملابس',
                'description' => 'قميص قطني عالي الجودة مناسب للاستخدام اليومي',
                'available' => '1'
            ],
            [
                'title' => 'كتاب تعلم البرمجة',
                'price' => '85',
                'category' => 'كتب',
                'description' => 'دليل شامل لتعلم البرمجة للمبتدئين',
                'available' => '0'
            ],
            [
                'title' => 'كرة قدم احترافية',
                'price' => '200',
                'category' => 'رياضة',
                'description' => 'كرة قدم معتمدة من الفيفا للاستخدام الاحترافي',
                'available' => '1'
            ],
        ];

        foreach ($products as $productData) {
            $sectionData = SectionData::create([
                'section_id' => $productsSection->id,
                'title' => $productData['title'],
                'slug' => \Str::slug($productData['title']),
                'status' => 'published',
                'published_at' => now(),
                'user_id' => $user->id,
            ]);

            // إنشاء قيم الحقول المخصصة
            SectionDataValue::create([
                'section_data_id' => $sectionData->id,
                'custom_field_id' => $priceField->id,
                'value' => $productData['price'],
            ]);

            SectionDataValue::create([
                'section_data_id' => $sectionData->id,
                'custom_field_id' => $categoryField->id,
                'value' => $productData['category'],
            ]);

            SectionDataValue::create([
                'section_data_id' => $sectionData->id,
                'custom_field_id' => $descriptionField->id,
                'value' => $productData['description'],
            ]);

            SectionDataValue::create([
                'section_data_id' => $sectionData->id,
                'custom_field_id' => $availableField->id,
                'value' => $productData['available'],
            ]);
        }

        // إنشاء قسم الأخبار
        $newsSection = Section::create([
            'name' => 'الأخبار',
            'slug' => 'news',
            'description' => 'قسم الأخبار والمقالات',
            'icon' => 'fas fa-newspaper',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        // إنشاء حقول مخصصة للأخبار
        $authorField = CustomField::create([
            'section_id' => $newsSection->id,
            'name' => 'author',
            'label' => 'الكاتب',
            'type' => 'text',
            'placeholder' => 'اسم الكاتب',
            'is_required' => false,
            'is_searchable' => true,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $contentField = CustomField::create([
            'section_id' => $newsSection->id,
            'name' => 'content',
            'label' => 'المحتوى',
            'type' => 'textarea',
            'placeholder' => 'محتوى المقال',
            'is_required' => true,
            'is_searchable' => true,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $tagsField = CustomField::create([
            'section_id' => $newsSection->id,
            'name' => 'tags',
            'label' => 'العلامات',
            'type' => 'checkbox',
            'options' => json_encode(['تقنية', 'رياضة', 'اقتصاد', 'صحة', 'تعليم']),
            'is_required' => false,
            'is_searchable' => true,
            'is_active' => true,
            'sort_order' => 3,
        ]);

        // إنشاء بيانات تجريبية للأخبار
        $news = [
            [
                'title' => 'تطورات جديدة في عالم التقنية',
                'author' => 'أحمد محمد',
                'content' => 'شهد عالم التقنية تطورات مهمة في الآونة الأخيرة، حيث تم الإعلان عن تقنيات جديدة في مجال الذكاء الاصطناعي والحوسبة السحابية.',
                'tags' => json_encode(['تقنية'])
            ],
            [
                'title' => 'نصائح للحفاظ على الصحة',
                'author' => 'د. فاطمة علي',
                'content' => 'يعتبر الحفاظ على الصحة من أهم الأولويات في حياتنا اليومية. إليكم مجموعة من النصائح المهمة للحفاظ على صحة جيدة.',
                'tags' => json_encode(['صحة'])
            ],
        ];

        foreach ($news as $newsData) {
            $sectionData = SectionData::create([
                'section_id' => $newsSection->id,
                'title' => $newsData['title'],
                'slug' => \Str::slug($newsData['title']),
                'status' => 'published',
                'published_at' => now(),
                'user_id' => $user->id,
            ]);

            // إنشاء قيم الحقول المخصصة
            SectionDataValue::create([
                'section_data_id' => $sectionData->id,
                'custom_field_id' => $authorField->id,
                'value' => $newsData['author'],
            ]);

            SectionDataValue::create([
                'section_data_id' => $sectionData->id,
                'custom_field_id' => $contentField->id,
                'value' => $newsData['content'],
            ]);

            SectionDataValue::create([
                'section_data_id' => $sectionData->id,
                'custom_field_id' => $tagsField->id,
                'value' => $newsData['tags'],
            ]);
        }
    }
}
