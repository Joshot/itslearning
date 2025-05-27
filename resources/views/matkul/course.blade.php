@extends('layouts.app')

@section('title', $course->course_name ?? 'Course Page')

@section('content')
<div class="flex justify-center items-start min-h-[80vh] space-x-6 p-4">
    <!-- Sidebar dengan Header -->
    <div class="w-1/5 bg-white shadow-lg rounded-2xl min-h-[700px] max-h-[700px] p-4 sticky top-4 h-fit flex flex-col">
        <div class="flex justify-between w-full items-center">
            <!-- Tombol Back -->
            <a href="{{ url('/dashboard') }}" class="p-3 rounded-full transition shadow-md hover:shadow-lg" data-no-prevent>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-800" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <!-- Kode Course -->
            <div class="px-4 py-2 bg-[#106587] text-white rounded-lg text-sm font-semibold">
                {{ $formattedCourseCode ?? 'Unknown Course' }}
            </div>
        </div>

        <!-- Nama Course -->
        <h2 class="text-lg font-semibold text-gray-800 text-center mt-4">{{ $course->course_name ?? 'Unnamed Course' }}</h2>

        <!-- Nilai Kuis -->
        <h3 class="text-lg font-semibold text-gray-800 mt-6">Nilai Kuis</h3>
        <div class="space-y-3">
            @for ($i = 1; $i <= 4; $i++)
            <div class="p-4 bg-gray-100 rounded-lg shadow-md">
                <p class="text-gray-700 font-semibold">Kuis {{ $i }}</p>
                <p class="text-gray-500">
                    @if (isset($quizScores[$i]))
                    <strong>{{ $quizScores[$i] }}/100</strong>
                    @else
                    Belum ada Nilai
                    @endif
                </p>
            </div>
            @endfor
        </div>
    </div>

    <!-- Konten Utama -->
    <div class="w-4/5 bg-white shadow-lg rounded-2xl p-8 overflow-y-auto min-h-[700px] max-h-[700px]">
        <h2 class="text-xl font-semibold mb-4">Materi Kursus</h2>
        <div class="space-y-6">
            @foreach ($materials as $week => $material)
            @php
            $quizMapping = [4 => 1, 7 => 2, 10 => 3, 14 => 4];
            $quizId = $quizMapping[$week + 1] ?? null;
            $quizAvailable = isset($availableQuizzes[(string) $quizId]);
            $quizCompleted = isset($quizScores[$quizId]);
            @endphp

            <div class="p-6 bg-white rounded-xl shadow-md flex flex-col space-y-3 border border-gray-200">
                <h4 class="text-lg font-semibold text-gray-800">Week {{ $week + 1 }}</h4>
                <p class="text-gray-600">Materi Week {{ $week + 1 }}</p>

                @if (!empty($material['pdf']))
                <a href="{{ asset($material['pdf']) }}" target="_blank" class="text-blue-600 font-semibold hover:underline">
                    Open PDF
                </a>
                @else
                <p class="text-gray-500">Materi tidak tersedia</p>
                @endif

                @if (!empty($material['optional']))
                <p class="text-gray-500">(Optional)</p>
                @endif

                <!-- Tampilkan Kuis jika minggu ke-4, 7, 10, atau 14 -->
                @if ($quizId)
                <div class="p-4 bg-gray-100 rounded-lg shadow-inner">
                    <p class="text-gray-700 font-semibold">Kuis {{ $quizId }}</p>
                    @if ($quizCompleted)
                    <p class="text-gray-500">Sudah mengerjakan Kuis</p>
                    @elseif ($quizAvailable)
                    <a href="{{ route('kuis.start', ['courseCode' => $formattedCourseCode, 'quizId' => $availableQuizzes[(string) $quizId] ?? null]) }}"
                       class="text-blue-600 font-semibold hover:underline">
                        Mulai Kuis {{ $quizId }}
                    </a>
                    @else
                    <p class="text-gray-500">Kuis {{ $quizId }} belum tersedia</p>
                    @endif
                </div>
                @endif

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

<!-- SweetAlert Ditaruh di Stack Scripts -->
@push('scripts')
@if(session('quiz_completed'))
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        let quizData = @json(session('quiz_completed'));
        if (quizData) {
            Swal.fire({
                title: `Kuis ${quizData.quiz_number} Selesai!`,
                text: `Anda mendapatkan nilai ${quizData.score}`,
                icon: 'success',
                confirmButtonColor: '#106587',
                confirmButtonText: 'OK'
            });
        }
    });
</script>
@endif
@if(session('error'))
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        Swal.fire({
            title: 'Oops!',
            text: '{{ session('error') }}',
            icon: 'error',
            confirmButtonColor: '#106587',
            confirmButtonText: 'OK'
        });
    });
</script>
@endif
@endpush
