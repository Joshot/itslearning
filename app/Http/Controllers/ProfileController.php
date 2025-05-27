<?php

namespace App\Http\Controllers;

use App\Models\Lecturer;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function edit()
    {
        if (Auth::guard('lecturer')->check()) {
            return view('lecture.profile');
        } else {
            return view('dashboard.profile');
        }
    }

    public function update(Request $request)
    {
        $request->validate([
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'motto' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'delete_photo' => ['nullable', 'boolean'],
        ], [
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'image.image' => 'File harus berupa gambar.',
            'image.mimes' => 'File harus berekstensi jpg, jpeg, atau png.',
            'image.max' => 'Ukuran file maksimal 2MB.',
        ]);

        try {
            if (Auth::guard('lecturer')->check()) {
                $user = Auth::guard('lecturer')->user();
                $model = Lecturer::findOrFail($user->id);
                $guard = 'lecturer';
            } else {
                $user = Auth::guard('student')->user();
                $model = Student::findOrFail($user->id);
                $guard = 'student';
            }

            $data = [];

            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            if ($request->filled('motto')) {
                $data['motto'] = $request->motto;
            }

            if ($request->has('delete_photo') && $request->delete_photo) {
                if ($user->profile_photo && $user->profile_photo !== '/images/profile.jpg') {
                    Storage::disk('public')->delete($user->profile_photo);
                }
                $data['profile_photo'] = '/images/profile.jpg';
            } elseif ($request->hasFile('image')) {
                if ($user->profile_photo && $user->profile_photo !== '/images/profile.jpg') {
                    Storage::disk('public')->delete($user->profile_photo);
                }
                $path = $request->file('image')->store('pictures', 'public');
                $data['profile_photo'] = $path;
            }

            $model->update($data);

            if ($guard === 'lecturer') {
                return redirect()->route('lecturer.dashboard')->with('success', 'Profile updated successfully');
            } else {
                return redirect()->route('dashboard')->with('success', 'Profile updated successfully');
            }

        } catch (\Exception $e) {
            \Log::error('Profile update failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update profile: ' . $e->getMessage());
        }
    }
}
