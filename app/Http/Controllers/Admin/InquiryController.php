<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use Illuminate\Http\Request;

class InquiryController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $search = $request->query('q');

        $query = Inquiry::query()->orderByDesc('created_at');

        if (in_array($status, [Inquiry::STATUS_NEW, Inquiry::STATUS_READ])) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        $inquiries = $query->paginate(20)->withQueryString();

        return view('admin.inquiries.index', compact('inquiries', 'status', 'search'));
    }

    public function show(Request $request, Inquiry $inquiry)
    {
        // لا تقم بالتعليم التلقائي كمقروء إذا تم طلب تجاهل ذلك (مثلاً بعد تعيين كغير مقروء وإعادة التحميل)
        if ($inquiry->status === Inquiry::STATUS_NEW && !$request->boolean('skip_mark')) {
            $inquiry->markRead();
        }
        return view('admin.inquiries.show', compact('inquiry'));
    }

    public function destroy(Inquiry $inquiry)
    {
        $inquiry->delete();
        return redirect()->route('admin.inquiries.index')->with('success', 'تم حذف الاستفسار بنجاح');
    }

    public function markRead(Inquiry $inquiry)
    {
        $inquiry->markRead();
        
        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'تم تعليم الاستفسار كمقروء']);
        }
        
        return back()->with('success', 'تم تعليم الاستفسار كمقروء');
    }

    public function markUnread(Inquiry $inquiry)
    {
        $inquiry->markUnread();
        
        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'تم تعليم الاستفسار كغير مقروء']);
        }
        
        return back()->with('success', 'تم تعليم الاستفسار كغير مقروء');
    }
}