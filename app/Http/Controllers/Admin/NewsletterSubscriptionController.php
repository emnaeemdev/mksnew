<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscription;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NewsletterSubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('q');
        $query = NewsletterSubscription::query()->orderByDesc('created_at');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $subscriptions = $query->paginate(20)->withQueryString();
        return view('admin.newsletter-subscriptions.index', compact('subscriptions', 'search'));
    }

    public function destroy(NewsletterSubscription $newsletter_subscription)
    {
        $newsletter_subscription->delete();
        return redirect()->route('admin.newsletter-subscriptions.index')->with('success', 'تم حذف الاشتراك بنجاح');
    }

    // تصدير كل النتائج (حسب الفلترة الحالية) إلى CSV
    public function export(Request $request): StreamedResponse
    {
        $search = $request->query('q');
        $query = NewsletterSubscription::query()->orderByDesc('created_at');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $fileName = 'newsletter_subscriptions_' . now()->format('Y_m_d_His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM لضمان عرض عربي صحيح في Excel
            fwrite($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // رؤوس الأعمدة
            fputcsv($handle, ['ID', 'Name', 'Email', 'Subscribed At']);

            $query->chunk(500, function ($rows) use ($handle) {
                foreach ($rows as $row) {
                    fputcsv($handle, [
                        $row->id,
                        $row->name,
                        $row->email,
                        optional($row->created_at)->format('Y-m-d H:i:s'),
                    ]);
                }
            });

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    // حذف جماعي للاشتراكات المحددة
    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        if (!is_array($ids) || empty($ids)) {
            return back()->with('error', 'لم يتم تحديد أي اشتراكات للحذف');
        }

        $count = NewsletterSubscription::whereIn('id', $ids)->count();
        NewsletterSubscription::whereIn('id', $ids)->delete();

        return redirect()->route('admin.newsletter-subscriptions.index')
            ->with('success', "تم حذف {$count} اشتراك/اشتراكات بنجاح");
    }
}