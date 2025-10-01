<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Post;
use App\Models\Category;
use App\Models\Section;
use App\Models\SectionData;
use App\Models\Setting;
use App\Models\Document;
use App\Models\DocumentSection;

class AdminController extends Controller
{
    /**
     * عرض لوحة التحكم الرئيسية
     */
    public function dashboard()
    {
        // إحصائيات عامة
        $stats = [
            'posts_count' => Post::count(),
            'published_posts' => Post::where('status', 'published')->count(),
            'categories_count' => Category::count(),
            'documents_count' => Document::count(),
            'published_documents' => Document::where('is_published', true)->count(),
            'document_sections_count' => DocumentSection::count(),
            // 'sections_count' => Section::count(),
            // 'section_data_count' => SectionData::count(),
        ];

        // آخر المقالات
        $recentPosts = Post::with('category')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // آخر بيانات الأقسام
        // $latestSectionData = SectionData::with(['section', 'user'])
        //     ->orderBy('created_at', 'desc')
        //     ->limit(5)
        //     ->get();

        return view('admin.dashboard', compact('stats', 'recentPosts'));
    }

    /**
     * عرض صفحة الملف الشخصي
     */
    public function profile()
    {
        $user = Auth::user();
        return view('admin.profile', compact('user'));
    }

    /**
     * تحديث الملف الشخصي
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'current_password' => 'nullable|string',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        // تحديث البيانات الأساسية
        $user->name = $request->name;
        $user->email = $request->email;

        // تحديث كلمة المرور إذا تم إدخالها
        if ($request->filled('password')) {
            // التحقق من كلمة المرور الحالية
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors([
                    'current_password' => 'كلمة المرور الحالية غير صحيحة'
                ]);
            }

            $user->password = Hash::make($request->password);
        }

        $user->save();

        return back()->with('success', 'تم تحديث الملف الشخصي بنجاح');
    }

    /**
     * عرض صفحة الإعدادات
     */
    public function settings()
    {
        $settings = [
            'site_name' => Setting::get('site_name', config('app.name', 'MK Snow')),
            'site_description' => Setting::get('site_description', 'موقع إخباري متخصص'),
            'contact_email' => Setting::get('contact_email', 'info@mksnow.com'),
            'maintenance_mode' => Setting::get('maintenance_mode', false),
            'footer_html' => Setting::get('footer_html', ''),
            // About page settings
            'about_html_ar' => Setting::get('about_html_ar', ''),
            'about_html_en' => Setting::get('about_html_en', ''),
            // Contact page settings
            'contact_description_ar' => Setting::get('contact_description_ar', ''),
            'contact_description_en' => Setting::get('contact_description_en', ''),
            'contact_address_ar' => Setting::get('contact_address_ar', ''),
            'contact_address_en' => Setting::get('contact_address_en', ''),
            'contact_phone' => Setting::get('contact_phone', ''),
            // Newsletter banner/popup settings
            'newsletter_banner_enabled' => Setting::get('newsletter_banner_enabled', false),
            'newsletter_banner_style' => Setting::get('newsletter_banner_style', 'bottom'), // top, bottom, side, modal
            'newsletter_banner_title_ar' => Setting::get('newsletter_banner_title_ar', 'اشترك في نشرتنا'),
            'newsletter_banner_title_en' => Setting::get('newsletter_banner_title_en', 'Subscribe to our Newsletter'),
            'newsletter_banner_text_ar' => Setting::get('newsletter_banner_text_ar', 'انضم لقائمة المشتركين لدينا للحصول على أحدث المقالات.'),
            'newsletter_banner_text_en' => Setting::get('newsletter_banner_text_en', 'Join our mailing list to get our latest updates.'),
            'newsletter_banner_bg_color' => Setting::get('newsletter_banner_bg_color', '#111827'),
            'newsletter_banner_text_color' => Setting::get('newsletter_banner_text_color', '#ffffff'),
            'newsletter_banner_button_color' => Setting::get('newsletter_banner_button_color', '#2563eb'),
            'newsletter_banner_delay' => Setting::get('newsletter_banner_delay', 1), // seconds
            'newsletter_banner_cooldown_days' => Setting::get('newsletter_banner_cooldown_days', 30),
        ];
        
        return view('admin.settings', compact('settings'));
    }

    /**
     * تحديث الإعدادات
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'site_name' => 'required|string|max:255',
            'site_description' => 'nullable|string|max:500',
            'contact_email' => 'required|email|max:255',
            'footer_html' => 'nullable|string',
            // About & Contact validations
            'about_html_ar' => 'nullable|string',
            'about_html_en' => 'nullable|string',
            'contact_description_ar' => 'nullable|string',
            'contact_description_en' => 'nullable|string',
            'contact_address_ar' => 'nullable|string|max:500',
            'contact_address_en' => 'nullable|string|max:500',
            'contact_phone' => 'nullable|string|max:50',
            // Newsletter banner/popup validations
            'newsletter_banner_enabled' => 'nullable|boolean',
            'newsletter_banner_style' => 'nullable|in:top,bottom,side,modal',
            'newsletter_banner_title_ar' => 'nullable|string|max:255',
            'newsletter_banner_title_en' => 'nullable|string|max:255',
            'newsletter_banner_text_ar' => 'nullable|string',
            'newsletter_banner_text_en' => 'nullable|string',
            'newsletter_banner_bg_color' => 'nullable|string|max:20',
            'newsletter_banner_text_color' => 'nullable|string|max:20',
            'newsletter_banner_button_color' => 'nullable|string|max:20',
            'newsletter_banner_delay' => 'nullable|integer|min:0|max:120',
            'newsletter_banner_cooldown_days' => 'nullable|integer|min:1|max:365',
        ]);

        // حفظ الإعدادات الحالية
        Setting::set('site_name', $request->site_name, 'string', 'اسم الموقع');
        Setting::set('site_description', $request->site_description, 'string', 'وصف الموقع');
        Setting::set('contact_email', $request->contact_email, 'string', 'البريد الإلكتروني للتواصل');
        Setting::set('maintenance_mode', $request->boolean('maintenance_mode'), 'boolean', 'وضع الصيانة');
        Setting::set('footer_html', $request->footer_html, 'text', 'محتوى الفوتر');
        
        // About & Contact settings
        Setting::set('about_html_ar', $request->about_html_ar, 'text', 'محتوى صفحة من نحن (عربي)');
        Setting::set('about_html_en', $request->about_html_en, 'text', 'محتوى صفحة من نحن (إنجليزي)');
        Setting::set('contact_description_ar', $request->contact_description_ar, 'text', 'وصف صفحة اتصل بنا (عربي)');
        Setting::set('contact_description_en', $request->contact_description_en, 'text', 'وصف صفحة اتصل بنا (إنجليزي)');
        Setting::set('contact_address_ar', $request->contact_address_ar, 'string', 'العنوان (عربي)');
        Setting::set('contact_address_en', $request->contact_address_en, 'string', 'العنوان (إنجليزي)');
        Setting::set('contact_phone', $request->contact_phone, 'string', 'هاتف التواصل');

        // Newsletter banner/popup settings
        Setting::set('newsletter_banner_enabled', $request->boolean('newsletter_banner_enabled'), 'boolean', 'تفعيل شريط/منبثق الاشتراك');
        Setting::set('newsletter_banner_style', $request->newsletter_banner_style ?: 'bottom', 'string', 'نمط العرض (أعلى، أسفل، جانب، منبثق)');
        Setting::set('newsletter_banner_title_ar', $request->newsletter_banner_title_ar, 'string', 'عنوان الشريط (عربي)');
        Setting::set('newsletter_banner_title_en', $request->newsletter_banner_title_en, 'string', 'Banner title (English)');
        Setting::set('newsletter_banner_text_ar', $request->newsletter_banner_text_ar, 'text', 'نص الشريط (عربي)');
        Setting::set('newsletter_banner_text_en', $request->newsletter_banner_text_en, 'text', 'Banner text (English)');
        Setting::set('newsletter_banner_bg_color', $request->newsletter_banner_bg_color ?: '#111827', 'string', 'لون الخلفية');
        Setting::set('newsletter_banner_text_color', $request->newsletter_banner_text_color ?: '#ffffff', 'string', 'لون النص');
        Setting::set('newsletter_banner_button_color', $request->newsletter_banner_button_color ?: '#2563eb', 'string', 'لون زر الاشتراك');
        Setting::set('newsletter_banner_delay', (int) ($request->newsletter_banner_delay ?? 1), 'integer', 'تأخير الظهور بالثواني');
        Setting::set('newsletter_banner_cooldown_days', (int) ($request->newsletter_banner_cooldown_days ?? 30), 'integer', 'مدة عدم الظهور بعد الإغلاق بالأيام');
        
        return back()->with('success', 'تم تحديث الإعدادات بنجاح');
    }


}