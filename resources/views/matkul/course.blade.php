@extends('layouts.app')

@section('title', $course->course_name ?? 'Course Page')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
    .container {
        max-width: 100%;
        margin: 0 auto;
        padding: 1.5rem;
        box-sizing: border-box;
    }
    .dashboard-container {
        display: flex;
        gap: 1.5rem;
        justify-content: center;
        align-items: stretch;
        width: 100%;
        max-width: 80%;
        margin: 0 auto;
        min-height: 700px;
    }
    .sidebar {
        flex: 1;
        padding: 1.5rem;
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        position: sticky;
        top: 1rem;
        display: flex;
        flex-direction: column;
        min-height: 700px;
    }
    .main-content {
        flex: 3;
        padding: 2rem;
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        overflow-y: auto;
        min-height: 700px;
        max-height: 700px;
    }
    .material-card {
        transition: transform 0.2s ease;
        border-radius: 0.75rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        border: 1px solid #e5e7eb;
    }
    .material-card:hover {
        transform: translateY(-3px);
    }
    .action-btn {
        color: #106587;
        text-decoration: none;
        font-weight: 500;
        transition: color 0.3s ease;
    }
    .action-btn:hover {
        color: #0d4a6b;
        text-decoration: underline;
    }
    .btn {
        padding: 0.5rem 1.5rem;
        border-radius: 0.5rem;
        font-size: 0.9rem;
        font-weight: 500;
        transition: background 0.3s ease, transform 0.2s ease;
        text-align: center;
        display: inline-block;
        border: none;
        cursor: pointer;
    }
    .btn-primary {
        background: #106587;
        color: white;
    }
    .btn-primary:hover {
        background: #0d4a6b;
        transform: translateY(-2px);
    }
    .quiz-card {
        background: #f9fafb;
        padding: 1rem;
        border-radius: 0.5rem;
        margin-bottom: 0.75rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }
    .quiz-card p {
        margin: 0;
        font-size: 0.875rem;
        color: #374151;
    }
    @media (max-width: 1400px) {
        .dashboard-container {
            max-width: 90%;
        }
    }
    @media (max-width: 1100px) {
        .dashboard-container {
            flex-direction: column;
            gap: 1rem;
            max-width: 100%;
            padding: 1rem;
            align-items: center;
        }
        .sidebar, .main-content {
            width: 100%;
            max-width: 700px;
            padding: 1rem;
            min-height: auto;
        }
        .main-content {
            max-height: none;
        }
    }
    @media (max-width: 768px) {
        .dashboard-container {
            padding: 0.5rem;
        }
        .sidebar, .main-content {
            padding: 0.75rem;
        }
        .btn {
            padding: 0.4rem 1rem;
            font-size: 0.85rem;
        }
    }
    @media (max-width: 480px) {
        .dashboard-container {
            gap: 0.75rem;
            padding: 0.5rem;
        }
        .sidebar, .main-content {
            padding: 0.5rem;
        }
        .btn {
            padding: 0.3rem 0.8rem;
            font-size: 0.8rem;
        }
    }
    html, body {
        margin: 0;
        padding: 0;
        width: 100%;
        overflow-x: hidden;
    }
</style>

<div class="container">
    <div class="dashboard-container">
        <div class="sidebar">
            <div class="flex justify-between items-center mb-6">
                <a href="{{ route('dashboard') }}" class="p-2 rounded-full bg-gray-100 hover:bg-gray-200 transition shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <div class="px-4 py-2 bg-[#106587] text-white rounded-lg text-sm font-semibold shadow-md">
                    {{ $formattedCourseCode ?? 'Unknown Course' }}
                </div>
            </div>
            <h2 class="text-xl font-semibold text-gray-800 text-center mb-6">
                {{ $course->course_name ?? 'Unnamed Course' }}
            </h2>
            <h3 class="text-base font-semibold text-gray-700 mb-4">Nilai Kuis</h3>
            <div class="space-y-3">
                @for ($i = 1; $i <= 4; $i++)
                <div class="quiz-card">
                    <p class="font-semibold">Kuis {{ $i }}</p>
                    <p class="text-gray-600">
                        @if (isset($quizScores[$i]))
                        <strong>{{ $quizScores[$i] }}/100</strong>
                        @else
                        Belum Dikerjakan
                        @endif
                    </p>
                </div>
                @endfor
            </div>
        </div>

        <div class="main-content">
            <h1 class="text-2xl font-semibold mb-6 text-gray-800">Materi Kursus</h1>
            <div class="space-y-6">
                @foreach ($materials as $week => $material)
                @php
                $quizWeek = in_array($week + 1, [4, 7, 10, 14]) ? ($week + 1) : null;
                $quizId = $quizWeek ? [4 => 1, 7 => 2, 10 => 3, 14 => 4][$quizWeek] : null;
                $quizAvailable = isset($availableQuizzes[$quizId]);
                $quizCompleted = isset($quizScores[$quizId]);
                @endphp

                <div class="p-6 bg-white rounded-xl material-card">
                    <h4 class="text-lg font-semibold text-gray-700 mb-3">Week {{ $week + 1 }}</h4>
                    <p class="text-gray-600 text-sm mb-2">Materi Week {{ $week + 1 }}</p>

                    @if (!empty($material['files']))
                    @foreach ($material['files'] as $file)
                    <div class="flex items-center mb-2">
                        <a href="#" onclick="confirmOpenFile('{{ asset($file) }}', '{{ strtoupper(pathinfo($file, PATHINFO_EXTENSION)) }}')"
                           class="action-btn text-sm">
                            Buka File {{ strtoupper(pathinfo($file, PATHINFO_EXTENSION)) }}
                        </a>
                    </div>
                    @endforeach
                    @else
                    <p class="text-gray-500 text-sm">Tidak ada materi tersedia</p>
                    @endif

                    @if (!empty($material['video_url']))
                    <a href="#" onclick="confirmOpenFile('{{ $material['video_url'] }}', 'Video')"
                       class="action-btn text-sm block mb-2">
                        Buka Video
                    </a>
                    @else
                    <p class="text-gray-500 text-sm">Tidak ada video tersedia</p>
                    @endif

                    @if ($material['optional'])
                    <p class="text-gray-500 text-sm italic mt-2">(Optional)</p>
                    @endif

                    @if ($quizWeek)
                    <div class="mt-4 quiz-card">
                        <p class="text-gray-700 font-semibold text-sm">Kuis {{ $quizId }}</p>
                        @if ($quizCompleted)
                        <p class="text-gray-500 text-sm">Sudah mengerjakan Kuis</p>
                        @elseif ($quizAvailable)
                        <a href="#" onclick="confirmStartQuiz('{{ route('kuis.start', ['courseCode' => $courseCodeWithoutDash, 'quizId' => $availableQuizzes[$quizId]]) }}', {{ $quizId }})"
                           class="btn btn-primary mt-2 inline-block">
                            Mulai Kuis {{ $quizId }}
                        </a>
                        @else
                        <p class="text-gray-500 text-sm">Kuis belum tersedia</p>
                        @endif
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function confirmOpenFile(url, type) {
        Swal.fire({
            title: 'Buka File',
            text: `Apakah Anda ingin membuka file ${type} ini?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#106587',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Buka',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.open(url, '_blank');
            }
        });
    }

    function confirmStartQuiz(url, quizNumber) {
        Swal.fire({
            title: 'Mulai Kuis',
            text: `Apakah Anda ingin memulai Kuis ${quizNumber}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#106587',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Mulai',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    }
</script>
@if (session('quiz_completed'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let quizData = @json(session('quiz_completed'));
        Swal.fire({
            title: `Kuis ${quizData.quiz_number} Selesai!`,
            text: `Skor Anda: ${quizData.score}/100`,
            icon: 'success',
            confirmButtonColor: '#106587'
        });
    });
</script>
@endif
@if (session('error'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            title: 'Error!',
            text: '{{ session('error') }}',
            icon: 'error',
            confirmButtonColor: '#106587'
        });
    });
</script>
@endif
@endpush
@endsection
