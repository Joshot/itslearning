<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\Student;

class AuthController extends Controller
{
    /**
     * Menampilkan halaman login
     */
    public function index() {
        return view("login/index");
    }

    /**
     * Proses login student
     */
    public function login(Request $request) {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ], [
            'email.required' => 'Email Student is required',
            'email.email' => 'Email Student is invalid',
            'password.required' => 'Password is required'
        ]);

        if (Auth::guard('student')->attempt(['email' => $request->email, 'password' => $request->password])) {
            $request->session()->regenerate();
            return redirect('/dashboard')->with('success', 'Login Berhasil');
        } else {
            return redirect('/')->with('error', 'Email atau Password salah!');
        }
    }

    /**
     * Logout student
     */
    public function logout(Request $request) {
        Auth::guard('student')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Logout berhasil');
    }
}
