<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        $this->ensureAdminUser();

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $this->ensureAdminUser();

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, true)) {
            return back()->withErrors(['email' => 'อีเมลหรือรหัสผ่านไม่ถูกต้อง'])->onlyInput('email');
        }

        if (Auth::user()?->email !== env('ADMIN_EMAIL', 'admin@example.com')) {
            Auth::logout();

            return back()->withErrors(['email' => 'บัญชีนี้ไม่มีสิทธิ์เข้าหน้าแอดมิน']);
        }

        $request->session()->regenerate();

        return redirect()->route('items.create');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    private function ensureAdminUser(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@example.com');
        $password = env('ADMIN_PASSWORD', 'change-me-please');
        $name = env('ADMIN_NAME', 'Collection Admin');

        if (! User::where('email', $email)->exists()) {
            User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
            ]);
        }
    }
}
