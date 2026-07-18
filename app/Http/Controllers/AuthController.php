<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string|max:255',
            'password' => 'required|string',
        ], [
            'login.required' => 'أدخل اسم المستخدم أو البريد الإلكتروني.',
            'password.required' => 'أدخل كلمة المرور.',
        ]);

        $login = trim((string) $request->input('login'));
        $throttleKey = $this->throttleKey($request, $login);

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'login' => "محاولات كثيرة. حاول مرة أخرى بعد {$seconds} ثانية.",
            ]);
        }

        $user = $this->findUserByLogin($login);

        if ($user && $user->isLocked()) {
            $seconds = max(60, $user->locked_until->getTimestamp() - now()->getTimestamp());
            $minutes = (int) ceil($seconds / 60);

            throw ValidationException::withMessages([
                'login' => "تم قفل الحساب مؤقتاً بسبب محاولات فاشلة. حاول بعد حوالي {$minutes} دقيقة.",
            ]);
        }

        if ($user && Hash::check($request->input('password'), $user->password)) {
            if (!$user->isStaff()) {
                throw ValidationException::withMessages([
                    'login' => 'ليس لديك صلاحية الدخول إلى لوحة التحكم.',
                ]);
            }

            Auth::login($user, $request->boolean('remember'));
            $user->clearLoginThrottle();
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            return redirect()->intended(route('admin.dashboard'));
        }

        RateLimiter::hit($throttleKey, 60);

        if ($user) {
            $user->registerFailedLogin();
        }

        throw ValidationException::withMessages([
            'login' => 'بيانات الدخول غير صحيحة.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    protected function findUserByLogin(string $login): ?User
    {
        return User::query()
            ->where(function ($q) use ($login) {
                $q->where('email', $login)
                    ->orWhere('username', $login);
            })
            ->first();
    }

    protected function throttleKey(Request $request, string $login): string
    {
        return Str::transliterate(Str::lower($login) . '|' . $request->ip());
    }
}
