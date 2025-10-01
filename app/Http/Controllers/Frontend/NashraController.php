<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Nashra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\GoogleSheetsService;

class NashraController extends Controller
{
    /**
     * عرض قائمة النشرات للمستخدمين
     */
    public function index(Request $request)
    {
        $query = Nashra::where('status', 1);
        if ($request->filled('year')) {
            $query->whereYear('published_at', $request->year);
        }
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('title_ar', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('subtitle_ar', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('content_ar', 'LIKE', "%{$searchTerm}%");
            });
        }
        $sort = $request->get('sort', 'latest');
        if ($sort === 'oldest') {
            $query->orderBy('published_at', 'asc');
        } else {
            $query->orderBy('published_at', 'desc');
        }
        $query->orderBy('sort_order', 'asc');
        $nashras = $query->paginate(12);

        $years = Nashra::where('status', 1)
                      ->selectRaw('YEAR(published_at) as year')
                      ->distinct()
                      ->orderBy('year', 'desc')
                      ->pluck('year')
                      ->filter()
                      ->values();

        return view('frontend.nashras.index', compact('nashras', 'years'));
    }

    /**
     * عرض تفاصيل نشرة واحدة مع بيانات Google Sheets
     */
    public function show(Request $request, $locale, $nashra)
    {
        Log::info('NashraController@show hit', ['nashra_param' => $nashra, 'locale' => $locale]);

        $nashra = Nashra::findOrFail($nashra);

        $sheetData = null;
        if ($nashra->google_sheet_id) {
            try {
                $sheetData = app(GoogleSheetsService::class)->getSheetData($nashra->google_sheet_id);
            } catch (\Throwable $e) {
                Log::error('Google Sheets fetch failed', [
                    'nashra_id' => $nashra->id,
                    'sheet_id' => $nashra->google_sheet_id,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        $relatedNashras = Nashra::published()
            ->where('id', '!=', $nashra->id)
            ->orderByPublished()
            ->orderBySortOrder()
            ->limit(6)
            ->get();

        return view('frontend.nashras.show', compact('nashra', 'sheetData', 'relatedNashras'));
    }

    /**
     * تحميل ملف PDF للنشرة
     */
    public function downloadPdf($locale, $nashra)
    {
        $nashra = Nashra::where('status', 1)->findOrFail($nashra);
        if (!$nashra->pdf_file) {
            abort(404, 'ملف PDF غير متوفر');
        }
        $filePath = storage_path('app/public/' . $nashra->pdf_file);
        if (!file_exists($filePath)) {
            abort(404, 'الملف غير موجود');
        }
        return response()->download($filePath, $nashra->title_ar . '.pdf');
    }

    /**
     * البحث في النشرات
     */
    public function search(Request $request)
    {
        $query = $request->get('q');
        $nashras = Nashra::published()
                        ->where(function($q) use ($query) {
                            $q->where('title_ar', 'LIKE', "%{$query}%")
                              ->orWhere('subtitle_ar', 'LIKE', "%{$query}%")
                              ->orWhere('content_ar', 'LIKE', "%{$query}%");
                        })
                        ->orderByPublished()
                        ->orderBySortOrder()
                        ->paginate(12);
        return view('frontend.nashras.search', compact('nashras', 'query'));
    }

    /**
     * عرض النشرات حسب السنة
     */
    public function byYear($year)
    {
        $nashras = Nashra::published()
                        ->whereYear('published_at', $year)
                        ->orderByPublished()
                        ->orderBySortOrder()
                        ->paginate(12);
        return view('frontend.nashras.by-year', compact('nashras', 'year'));
    }

    /**
     * عرض أحدث النشرات (للصفحة الرئيسية)
     */
    public function latest($limit = 6)
    {
        $nashras = Nashra::published()
                        ->orderByPublished()
                        ->orderBySortOrder()
                        ->limit($limit)
                        ->get();
        return $nashras;
    }

    /**
     * API endpoint لجلب بيانات Google Sheets
     */
    public function getSheetData($locale, $nashra)
    {
        $nashra = Nashra::published()->findOrFail($nashra);
        try {
            if (!$nashra->google_sheet_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'معرف Google Sheet غير متوفر'
                ], 400);
            }
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
}