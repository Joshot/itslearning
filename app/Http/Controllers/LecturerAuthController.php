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
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required' => 'Email tidak boleh kosong.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password tidak boleh kosong.',
        ]);

        $email = strtolower(trim($request->email)); // Normalize email
        \Log::info("Attempting login with email: {$email}");

        if (Auth::guard('lecturer')->attempt(['email' => $email, 'password' => $request->password])) {
            $request->session()->regenerate();
            \Log::info("Login successful for email: {$email}");
            return redirect()->route('lecturer.dashboard');
        }

        \Log::info("Login failed for email: {$email}");
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

    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:lecturers,email'],
            'password' => ['required', 'confirmed', 'min:8'],
            'nidn' => ['required', 'string', 'max:255'],
            'major' => ['required', 'string', 'max:255'],
            'mata_kuliah' => ['required', 'string', 'max:255'],
        ], [
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password tidak boleh kosong.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min' => 'Password minimal 8 karakter.',
            'nidn.required' => 'NIDN tidak boleh kosong.',
            'major.required' => 'Jurusan tidak boleh kosong.',
            'mata_kuliah.required' => 'Mata kuliah tidak boleh kosong.',
        ]);

        Lecturer::create([
            'name' => $request->name,
            'email' => strtolower(trim($request->email)),
            'password' => Hash::make($request->password),
            'nidn' => $request->nidn,
            'major' => $request->major,
            'mata_kuliah' => $request->mata_kuliah,
        ]);

        return redirect('/lecturer/login')->with('success', 'Registrasi berhasil, silakan login.');
    }
}
