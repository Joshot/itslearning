@extends('layouts.app')

@section('title', $courseName ?? 'Course Page')

@section('content')
<div class="flex justify-center items-start min-h-[80vh] space-x-6 p-4">
    <!-- Sidebar dengan Header -->
    <div class="w-1/5 bg-white shadow-lg rounded-2xl min-h-[700px] max-h-[700px] p-4 sticky top-4 h-fit flex flex-col">
        <div class="flex justify-between w-full items-center">
            <!-- Tombol Back -->
            <a href="{{ url('/dashboard') }}" class="p-3 rounded-full transition shadow-md hover:shadow-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-800" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <!-- Kode Course -->
            <div class="px-4 py-2 bg-[#234e7f] text-white rounded-lg text-sm font-semibold">
                {{ $formattedCourseCode ?? 'Unknown Course' }}
            </div>
        </div>

        <!-- Nama Course -->
        <h2 class="text-lg font-semibold text-gray-800 text-center mt-4">{{ $courseName ?? 'Unnamed Course' }}</h2>

        <!-- Nilai Kuis -->
        <h3 class="text-lg font-semibold text-gray-800 mt-6">Nilai Kuis</h3>
        <ul class="mt-2 space-y-2 w-full">
            @if(isset($quizScores) && count($quizScores) > 0)
            @foreach ($quizScores as $index => $score)
            <li class="flex justify-between bg-gray-100 p-2 rounded-lg">
                <span>Kuis {{ $index + 1 }}</span>
                <span>{{ $score }}</span>
            </li>
            @endforeach
            @else
            <li class="text-gray-500 text-center">Belum ada nilai kuis</li>
            @endif
        </ul>
    </div>

    <!-- Konten Utama -->
    <div class="w-4/5 bg-white shadow-lg rounded-2xl p-8 overflow-y-auto min-h-[700px] max-h-[700px]">
        <h2 class="text-xl font-semibold mb-4">Materi Kursus</h2>
        <div class="space-y-6">
            @foreach ($materials as $week => $material)
            <div class="p-6 bg-white rounded-xl shadow-md flex flex-col space-y-3 border border-gray-200">
                <h4 class="text-lg font-semibold text-gray-800">Week {{ $week + 1 }}</h4>
                <p class="text-gray-600">Materi Week {{ $week + 1 }}</p>

                <!-- Link PDF hanya ditampilkan jika tersedia -->
                @if (!empty($material['pdf']))
                <a href="{{ asset($material['pdf']) }}" target="_blank" class="text-blue-600 font-semibold hover:underline">
                    Open PDF
                </a>
                @else
                <p class="text-gray-500">Materi tidak tersedia</p>
                @endif

                <!-- Penandaan Optional -->
                @if (!empty($material['optional']))
                <p class="text-gray-500">(Optional)</p>
                @endif

                <!-- Tampilkan Kuis jika minggu ke-4, 7, 10, atau 14 -->
                @if (in_array($week + 1, [4, 7, 10, 14]))
                @php
                $quizMapping = [4 => 1, 7 => 2, 10 => 3, 14 => 4];
                $quizId = $quizMapping[$week + 1];
                @endphp
                <div class="p-4 bg-gray-100 rounded-lg shadow-inner">
                    <p class="text-gray-700 font-semibold">Kuis {{ $quizId }}</p>

                    <a href="{{ route('kuis.start', ['courseCode' => $courseCodeWithoutDash, 'quizId' => $quizId]) }}"
                       class="text-blue-600 font-semibold hover:underline">
                        Mulai Kuis {{ $quizId }}
                    </a>
                </div>
                @endif




                <!-- Tempat untuk Video Materi (placeholder untuk sekarang) -->
                <div class="p-4 bg-gray-100 rounded-lg shadow-inner">
                    <p class="text-gray-700 font-semibold">Video Materi Week {{ $week + 1 }}</p>
                    <p class="text-gray-500">Belum tersedia</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
