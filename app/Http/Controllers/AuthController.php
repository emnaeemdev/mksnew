<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended('/admin');
        }

        return back()->withErrors([
            'email' => 'البيانات المدخلة غير صحيحة.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/admin/login');
    }

    // Create default admin user if not exists
    public function createDefaultAdmin()
    {
        $admin = User::where('email', 'admin@mksnow.com')->first();
        
        if (!$admin) {
            User::create([
                'name' => 'Admin',
                'email' => 'admin@mksnow.com',
                'password' => Hash::make('admin123'),
            ]);
            
            return 'Default admin user created: admin@mksnow.com / admin123';
        }
        
        return 'Admin user already exists';
    }
}