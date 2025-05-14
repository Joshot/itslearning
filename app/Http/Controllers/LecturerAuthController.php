<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Lecturer;
use Illuminate\Validation\ValidationException;

class LecturerAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('lecture.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'regex:/^[a-zA-Z]+@lecturer\.umn\.ac\.id$/'],
            'password' => ['required'],
        ], [
            'email.regex' => 'Email harus menggunakan format nama@lecturer.umn.ac.id.',
            'email.required' => 'Email tidak boleh kosong.',
            'password.required' => 'Password tidak boleh kosong.',
        ]);

        if (Auth::guard('lecturer')->attempt($request->only('email', 'password'))) {
            $request->session()->regenerate();
            return redirect()->route('lecturer.dashboard');
        }

        throw ValidationException::withMessages([
            'email' => ['Email atau kata sandi salah.'],
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('lecturer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/lecturer/login')->with('success', 'Logout berhasil');
    }

    // Opsional: Registrasi untuk dosen (bisa dihapus jika tidak diperlukan)
    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'regex:/^[a-zA-Z]+@lecturer\.umn\.ac\.id$/', 'unique:lecturers,email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ], [
            'email.regex' => 'Email harus menggunakan format nama@lecturer.umn.ac.id.',
            'email.unique' => 'Email sudah terdaftar.',
        ]);

        Lecturer::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return redirect('/lecturer/login')->with('success', 'Registrasi berhasil, silakan login.');
    }
}
