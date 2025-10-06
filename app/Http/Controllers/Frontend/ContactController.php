<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inquiry;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function submit($locale = null, Request $request)
    {
        // تحقق الكابتشا قبل التحقق العام
        $request->validate([
            'captcha_answer' => 'required|numeric'
        ], [
            'captcha_answer.required' => __('messages.captcha_required'),
            'captcha_answer.numeric' => __('messages.captcha_invalid'),
        ]);

        $expected = session('contact_captcha_answer');
        if (!$expected || (int)$request->input('captcha_answer') !== (int)$expected) {
            return back()->withInput()->with('error', __('messages.captcha_wrong'));
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string|min:10',
        ]);

        $inquiry = Inquiry::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'subject' => $validated['subject'] ?? null,
            'message' => $validated['message'],
            'status' => Inquiry::STATUS_NEW,
            'locale' => app()->getLocale(),
        ]);

        // إرسال إشعار بالبريد إذا تم إعداد البريد
        if (config('mail.default')) {
            try {
                $to = setting('contact_email', 'info@mksnow.com');
                if ($to) {
                    Mail::raw("New inquiry from: {$inquiry->name}\nEmail: {$inquiry->email}\nPhone: {$inquiry->phone}\nSubject: {$inquiry->subject}\nMessage:\n{$inquiry->message}", function ($m) use ($to) {
                        $m->to($to)->subject('New Contact Inquiry');
                    });
                }
            } catch (\Throwable $e) {
                \Log::warning('Mail send failed for inquiry', ['error' => $e->getMessage()]);
            }
        }

        // إعادة توليد الكابتشا بعد الإرسال الناجح لمنع إعادة استخدام نفس الإجابة
        session()->forget('contact_captcha_answer');

        return back()->with('success', __('messages.contact_submitted'));
    }
}