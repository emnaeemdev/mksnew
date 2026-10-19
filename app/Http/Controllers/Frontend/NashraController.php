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
        $query = Nashra::where('status', 1)->forCurrentLocale();
        if ($request->filled('year')) {
            $query->whereYear('published_at', $request->year);
        }
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('title_ar', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('title_en', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('subtitle_ar', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('subtitle_en', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('content_ar', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('content_en', 'LIKE', "%{$searchTerm}%");
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
                      ->forCurrentLocale()
                      ->selectRaw('YEAR(published_at) as year')
                      ->distinct()
                      ->orderBy('year', 'desc')
                      ->pluck('year')
                      ->filter()
                      ->values();

        return view('frontend.nashras.index', compact('nashras', 'years'));
    }

    /**
     * عرض تفاصيل نشرة واحدة مع بيانات Google Sheets من الكاش المشترك
     */
    public function show(Request $request, $locale, $nashra)
    {
        $nashra = Nashra::findOrFail($nashra);

        $sheetPayload = null;
        $sheetId = $nashra->localizedGoogleSheetId();
        if ($sheetId) {
            try {
                $sheetPayload = app(GoogleSheetsService::class)
                    ->getCachedSheetPayload($sheetId);
            } catch (\Throwable $e) {
                Log::error('Google Sheets fetch failed', [
                    'nashra_id' => $nashra->id,
                    'sheet_id' => $sheetId,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        $relatedNashras = Nashra::published()
            ->forCurrentLocale()
            ->where('id', '!=', $nashra->id)
            ->orderByPublished()
            ->orderBySortOrder()
            ->limit(6)
            ->get();

        return view('frontend.nashras.show', compact('nashra', 'sheetPayload', 'relatedNashras'));
    }

    /**
     * تحميل ملف PDF للنشرة
     */
    public function downloadPdf($locale, $nashra)
    {
        $nashra = Nashra::where('status', 1)->findOrFail($nashra);
        $pdfFile = $nashra->localizedPdfFile();
        if (!$pdfFile) {
            abort(404, 'ملف PDF غير متوفر');
        }
        $filePath = storage_path('app/public/' . $pdfFile);
        if (!file_exists($filePath)) {
            abort(404, 'الملف غير موجود');
        }

        // منع تكرار العد في نفس الجلسة
        $sessionKey = 'downloaded_nashras';
        $downloaded = session()->get($sessionKey, []);
        if (!in_array($nashra->id, $downloaded, true)) {
            $nashra->increment('download_count');
            $downloaded[] = $nashra->id;
            session()->put($sessionKey, $downloaded);
        }

        // منع الكاش حتى لا يعرض المتصفح نسخة لغة أخرى محفوظة مسبقاً لنفس الرابط
        return response()->download($filePath, ($nashra->title ?: 'nashra') . '.pdf', [
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
        ]);
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
                              ->orWhere('title_en', 'LIKE', "%{$query}%")
                              ->orWhere('subtitle_ar', 'LIKE', "%{$query}%")
                              ->orWhere('subtitle_en', 'LIKE', "%{$query}%")
                              ->orWhere('content_ar', 'LIKE', "%{$query}%")
                              ->orWhere('content_en', 'LIKE', "%{$query}%");
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
     * API endpoint لجلب بيانات Google Sheets (من الكاش أو تحديث يدوي)
     */
    public function getSheetData(Request $request, $locale, $nashra)
    {
        $nashra = Nashra::published()->findOrFail($nashra);

        try {
            $sheetId = $nashra->localizedGoogleSheetId();
            if (!$sheetId) {
                return response()->json([
                    'success' => false,
                    'message' => 'معرف Google Sheet غير متوفر',
                ], 400);
            }

            $forceRefresh = $request->boolean('refresh') && auth()->check();
            $googleSheetsService = app(GoogleSheetsService::class);
            $payload = $googleSheetsService->getCachedSheetPayload($sheetId, $forceRefresh);

            $response = [
                'success' => true,
                'data' => $payload['data'],
            ];

            if (auth()->check()) {
                $response['cached_at'] = $payload['cached_at'];
                $response['from_cache'] = $payload['from_cache'];
            }

            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في جلب البيانات: ' . $e->getMessage(),
            ], 500);
        }
    }
}