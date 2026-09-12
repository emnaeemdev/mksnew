<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ActivityLogger;
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

            app(ActivityLogger::class)->record($user, 'login', 'سجّل الدخول إلى لوحة التحكم', [
                'route_name' => 'admin.login',
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'ip_address' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
                'status_code' => 302,
                'properties' => ['actor_name' => $user->name],
            ]);

            return redirect()->intended(route('admin.dashboard'));
        }

        RateLimiter::hit($throttleKey, 60);

        if ($user) {
            $user->registerFailedLogin();
            app(ActivityLogger::class)->record($user, 'login_failed', 'محاولة دخول فاشلة', [
                'route_name' => 'admin.login',
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'ip_address' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
                'status_code' => 422,
                'properties' => [
                    'actor_name' => $user->name,
                    'login' => $login,
                ],
            ]);
        }

        throw ValidationException::withMessages([
            'login' => 'بيانات الدخول غير صحيحة.',
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        if ($user instanceof User) {
            app(ActivityLogger::class)->record($user, 'logout', 'سجّل الخروج من لوحة التحكم', [
                'route_name' => 'admin.logout',
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'ip_address' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
                'status_code' => 302,
                'properties' => ['actor_name' => $user->name],
            ]);
        }

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
