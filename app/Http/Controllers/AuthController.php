<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\Student;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Menampilkan halaman login
     */
    public function index()
    {
        return view("login/index");
    }

    /**
     * Proses login student
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required' => 'Email tidak boleh kosong.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password tidak boleh kosong.',
        ]);

        $email = strtolower(trim($request->email)); // Normalize email
        \Log::info("Attempting student login with email: {$email}");

        if (Auth::guard('student')->attempt(['email' => $email, 'password' => $request->password])) {
            $request->session()->regenerate();
            \Log::info("Student login successful for email: {$email}");
            return redirect('/dashboard')->with('success', 'Login Berhasil');
        }

        \Log::info("Student login failed for email: {$email}");
        throw ValidationException::withMessages([
            'email' => ['Email atau Password salah!'],
        ]);
    }

    /**
     * Logout student
     */
    public function logout(Request $request)
    {
        Auth::guard('student')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Logout berhasil');
    }
}
