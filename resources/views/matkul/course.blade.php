@extends('layouts.app')

@section('title', isset($courseName) ? $courseName : 'Course Page')

@section('content')

@php
// Ambil kode dari URL (tanpa tanda '-')
$courseCodeFromUrl = request()->segment(2); // Misal: "IF540D"

// Ambil courses dari config/courses.php
$courses = config('courses', []);

// Pastikan config berhasil di-load
if (!is_array($courses)) {
$courses = [];
}

// Mencari course yang cocok dengan kode dari URL
$matchedCourse = collect($courses)->first(function ($course) use ($courseCodeFromUrl) {
return str_replace('-', '', $course['code']) === $courseCodeFromUrl;
});

// Jika ditemukan, ambil nama dan kode
$courseName = $matchedCourse['name'] ?? 'Unknown Course';
$courseCode = $matchedCourse['code'] ?? '';
@endphp

<div class="flex justify-center items-center min-h-[80vh]">
    <div class="w-full max-w-[80%] flex flex-col space-y-4">
        <!-- Card Header -->
        <div class="bg-white shadow-lg rounded-2xl p-4 flex items-center justify-between">
            <!-- Tombol Back -->
            <a href="{{ url('/dashboard') }}" class="p-3 rounded-full transition shadow-md hover:shadow-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-800" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </a>

            <!-- Nama Course -->
            <h2 class="text-lg font-semibold text-gray-800">{{ $courseName }}</h2>

            <!-- Kode Course -->
            <div class="px-4 py-2 bg-[#234e7f] text-white rounded-lg text-sm font-semibold">
                {{ $courseCode }}
            </div>
        </div>

        <!-- Konten Utama -->
        <div class="flex-1 bg-white shadow-lg rounded-2xl p-8 overflow-y-auto min-h-[600px] max-h-[600px]">
            {{-- Tambahkan konten kursus di sini --}}
        </div>
    </div>
</div>

@endsection
