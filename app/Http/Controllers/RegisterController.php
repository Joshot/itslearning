<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Course;
use App\Models\CourseAssignment;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RegisterController extends Controller
{
    public function index()
    {
        return view('login.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'nim' => ['required', 'string', 'max:255', 'unique:students'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:students'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'major' => ['required', 'in:Informatika,Pertanian,Sistem Informasi,Teknik Komputer,Biologi,Kedokteran,Ilmu Komunikasi,Manajemen,Film,DKV'],
            'angkatan' => ['required', 'digits:4'],
        ], [
            'nim.required' => 'NIM tidak boleh kosong.',
            'nim.unique' => 'NIM sudah terdaftar.',
            'name.required' => 'Nama tidak boleh kosong.',
            'email.required' => 'Email tidak boleh kosong.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password tidak boleh kosong.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'major.required' => 'Jurusan tidak boleh kosong.',
            'angkatan.required' => 'Angkatan tidak boleh kosong.',
            'angkatan.digits' => 'Angkatan harus 4 digit.',
        ]);

        try {
            \Log::debug("Registration attempt with data: " . json_encode($request->all()));

            DB::beginTransaction();

            // Create student
            $student = Student::create([
                'nim' => $request->nim,
                'name' => $request->name,
                'email' => strtolower(trim($request->email)),
                'password' => Hash::make($request->password),
                'major' => $request->major,
                'angkatan' => $request->angkatan,
                'profile_photo' => '/images/profile.jpg',
                'motto' => 'Veni, Vidi, Vici',
            ]);

            \Log::info("Student created successfully: {$student->email}, ID: {$student->id}");

            // Assign student to all courses with matching course_milik
            $courses = Course::where('course_milik', $request->major)->get();
            if ($courses->isEmpty()) {
                \Log::warning("No courses found for major: {$request->major}");
                DB::commit();
                return redirect('/login')->with('success', 'Registrasi berhasil! Tidak ada mata kuliah yang tersedia untuk jurusan ini. Silakan login.');
            }

            foreach ($courses as $course) {
                CourseAssignment::create([
                    'course_code' => $course->course_code,
                    'student_id' => $student->id,
                    'lecturer_id' => null,
                ]);
                \Log::info("Assigned student {$student->id} to course {$course->course_code}");
            }

            DB::commit();
            \Log::info("Student registered successfully: {$student->email}");
            return redirect('/login')->with('success', 'Registrasi berhasil! Silakan login.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Registration failed for email {$request->email}: {$e->getMessage()}");
            \Log::error("Stack trace: {$e->getTraceAsString()}");
            throw ValidationException::withMessages([
                'email' => ["Registrasi gagal: {$e->getMessage()}"],
            ]);
        }
    }
}
