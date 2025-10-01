<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Nashra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class NashraController extends Controller
{
    /**
     * عرض قائمة النشرات
     */
    public function index()
    {
        $nashras = Nashra::orderBy('sort_order', 'asc')
                        ->orderBy('published_at', 'desc')
                        ->paginate(15);
        
        return view('admin.nashras.index', compact('nashras'));
    }

    /**
     * عرض نموذج إنشاء نشرة جديدة
     */
    public function create()
    {
        return view('admin.nashras.create');
    }

    /**
     * حفظ نشرة جديدة
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title_ar' => 'required|string|max:255',
            'subtitle_ar' => 'nullable|string|max:255',
            'google_drive_url' => 'required|url',
            'published_at' => 'nullable|date',
            'content_ar' => 'nullable|string',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'pdf_file' => 'nullable|mimes:pdf|max:10240',
            'status' => 'required|in:0,1',
            'sort_order' => 'integer|min:0'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        $data = $request->all();
        
        // رفع الصورة المميزة
        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $request->file('featured_image')
                                             ->store('nashras/images', 'public');
        }
        
        // رفع ملف PDF
        if ($request->hasFile('pdf_file')) {
            $data['pdf_file'] = $request->file('pdf_file')
                                      ->store('nashras/pdfs', 'public');
        }
        
        // تعيين القيم الافتراضية
        $data['status'] = (int) $request->input('status', 1);
        $data['sort_order'] = $data['sort_order'] ?? 0;
        
        // استخراج معرف Google Sheet من الرابط
        if ($data['google_drive_url']) {
            preg_match('/\/spreadsheets\/d\/([a-zA-Z0-9-_]+)/', $data['google_drive_url'], $matches);
            $data['google_sheet_id'] = isset($matches[1]) ? $matches[1] : null;
        }
        
        Nashra::create($data);
        
        return redirect()->route('admin.nashras.index')
                       ->with('success', 'تم إنشاء النشرة بنجاح');
    }

    /**
     * عرض تفاصيل نشرة
     */
    public function show(Nashra $nashra)
    {
        return view('admin.nashras.show', compact('nashra'));
    }

    /**
     * عرض نموذج تعديل النشرة
     */
    public function edit(Nashra $nashra)
    {
        return view('admin.nashras.edit', compact('nashra'));
    }

    /**
     * تحديث النشرة
     */
    public function update(Request $request, Nashra $nashra)
    {
        $validator = Validator::make($request->all(), [
            'title_ar' => 'required|string|max:255',
            'subtitle_ar' => 'nullable|string|max:255',
            'google_drive_url' => 'required|url',
            'published_at' => 'nullable|date',
            'content_ar' => 'nullable|string',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'pdf_file' => 'nullable|mimes:pdf|max:20240',
            'status' => 'boolean',
            'sort_order' => 'integer|min:0'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        $data = $request->all();
        
        // رفع الصورة المميزة الجديدة
        if ($request->hasFile('featured_image')) {
            // حذف الصورة القديمة
            if ($nashra->featured_image) {
                Storage::disk('public')->delete($nashra->featured_image);
            }
            $data['featured_image'] = $request->file('featured_image')
                                             ->store('nashras/images', 'public');
        }
        
        // رفع ملف PDF الجديد
        if ($request->hasFile('pdf_file')) {
            // حذف الملف القديم
            if ($nashra->pdf_file) {
                Storage::disk('public')->delete($nashra->pdf_file);
            }
            $data['pdf_file'] = $request->file('pdf_file')
                                      ->store('nashras/pdfs', 'public');
        }
        
        // تعيين القيم
        $data['status'] = (int) $request->input('status', 0);
        $data['sort_order'] = $data['sort_order'] ?? 0;
        
        
        $nashra->update($data);
        
        return redirect()->route('admin.nashras.index')
                       ->with('success', 'تم تحديث النشرة بنجاح');
    }

    /**
     * حذف النشرة
     */
    public function destroy(Nashra $nashra)
    {
        // حذف الملفات المرفقة
        if ($nashra->featured_image) {
            Storage::disk('public')->delete($nashra->featured_image);
        }
        
        if ($nashra->pdf_file) {
            Storage::disk('public')->delete($nashra->pdf_file);
        }
        
        $nashra->delete();
        
        return redirect()->route('admin.nashras.index')
                       ->with('success', 'تم حذف النشرة بنجاح');
    }

    /**
     * جلب بيانات Google Sheets من رابط
     */
    public function fetchSheets(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'google_drive_url' => 'required|url'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'رابط Google Drive غير صحيح'
                ], 400);
            }

            $url = $request->google_drive_url;
            
            // استخراج معرف الجدول من الرابط
            preg_match('/\/spreadsheets\/d\/([a-zA-Z0-9-_]+)/', $url, $matches);
            
            if (!isset($matches[1])) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن استخراج معرف الجدول من الرابط'
                ], 400);
            }
            
            $spreadsheetId = $matches[1];
            
            // محاولة جلب البيانات من Google Sheets API
            $googleSheetsService = app('App\\Services\\GoogleSheetsService');
            $sheetData = $googleSheetsService->getSheetData($spreadsheetId);
            
            return response()->json([
                'success' => true,
                'sheets' => $sheetData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في جلب البيانات: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * جلب بيانات Google Sheets لنشرة موجودة
     */
    public function getSheetData(Nashra $nashra)
    {
        try {
            $googleSheetsService = app('App\\Services\\GoogleSheetsService');
            $sheetData = $googleSheetsService->getSheetData($nashra->google_sheet_id);
            
            return response()->json([
                'success' => true,
                'data' => $sheetData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في جلب البيانات: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * تبديل حالة النشر
     */
    public function toggleStatus(Nashra $nashra)
    {
        $nashra->update(['status' => !$nashra->status]);
        
        $message = $nashra->status ? 'تم نشر النشرة' : 'تم إلغاء نشر النشرة';
        
        return redirect()->back()->with('success', $message);
    }
}