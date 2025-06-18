@extends('layouts.app')

@section('title', 'Feedback - ' . ($course->course_name ?? 'Course'))

@section('content')
<style>
    /* Base container styles */
    .container {
        max-width: 100%;
        margin: 0 auto;
        padding: 1.5rem;
        box-sizing: border-box;
    }

    /* Dashboard layout with sidebar and main content */
    .dashboard-container {
        display: flex;
        gap: 1.5rem;
        justify-content: center;
        align-items: stretch;
        width: 100%;
        max-width: 90%;
        margin: 0 auto;
        min-height: 700px;
    }

    /* Sidebar styling */
    .sidebar {
        flex: 1;
        padding: 1.5rem;
        background: linear-gradient(135deg, #ffffff, #f8fafc);
        border-radius: 1rem;
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.08);
        position: sticky;
        top: 1.5rem;
        display: flex;
        flex-direction: column;
        min-height: 700px;
    }

    /* Main feedback container */
    .feedback-container {
        flex: 3;
        padding: 2rem;
        background: linear-gradient(145deg, #ffffff, #f9fafb);
        border-radius: 1rem;
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.08);
        overflow-y: auto;
        min-height: 700px;
        max-height: 700px;
    }

    /* Card styling for feedback and scores */
    .feedback-card, .score-card {
        background: #ffffff;
        padding: 1.25rem;
        border-radius: 0.75rem;
        margin-bottom: 1.25rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border-left: 4px solid #106587;
    }
    .feedback-card:hover, .score-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    /* Additional task card */
    .additional-task-card {
        background: #ffffff;
        padding: 1.25rem;
        border-radius: 0.75rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border-left: 4px solid #16a34a;
        margin-bottom: 1.25rem;
    }
    .additional-task-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    /* Card row for main feedback and additional task */
    .card-row {
        display: flex;
        gap: 1.25rem;
        align-items: stretch;
        margin-bottom: 1.25rem;
    }
    .main-feedback-card {
        flex: 2;
        background: #ffffff;
        padding: 1.25rem;
        border-radius: 0.75rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border-left: 4px solid #106587;
    }
    .main-feedback-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    .additional-task-wrapper {
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    /* Button styling */
    .btn {
        padding: 0.6rem 1.75rem;
        border-radius: 0.75rem;
        font-size: 0.95rem;
        font-weight: 500;
        transition: background 0.3s ease, transform 0.2s ease, box-shadow 0.2s ease;
        text-align: center;
        display: inline-block;
        border: none;
        cursor: pointer;
    }
    .btn-primary {
        background: linear-gradient(90deg, #106587, #0d4a6b);
        color: white;
    }
    .btn-primary:hover {
        background: linear-gradient(90deg, #0d4a6b, #083a54);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(16, 101, 135, 0.2);
    }
    .btn-disabled {
        background: #6b7280;
        color: white;
        cursor: not-allowed;
        opacity: 0.7;
    }

    /* Sidebar header and navigation */
    .sidebar-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }
    .course-badge {
        padding: 0.5rem 1rem;
        background: linear-gradient(90deg, #106587, #0d4a6b);
        color: white;
        border-radius: 0.75rem;
        font-size: 0.9rem;
        font-weight: 600;
    }
    .back-btn {
        padding: 0.5rem;
        border-radius: 50%;
        background: #f1f5f9;
        transition: background 0.3s ease, transform 0.2s ease;
    }
    .back-btn:hover {
        background: #e2e8f0;
        transform: translateY(-2px);
    }

    /* Typography */
    h1 {
        font-size: 1.75rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 1.5rem;
    }
    h3 {
        font-size: 1.1rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 0.75rem;
    }
    p {
        color: #4b5563;
        font-size: 0.95rem;
        line-height: 1.6;
    }
    .score-value {
        color: #106587;
        font-weight: 600;
    }
    .average-score {
        color: #16a34a;
        font-weight: 600;
    }

    /* List styling */
    ul {
        list-style-type: disc;
        padding-left: 1.5rem;
    }
    li {
        color: #4b5563;
        font-size: 0.95rem;
        margin-bottom: 0.5rem;
    }

    /* Responsive design */
    @media (max-width: 1400px) {
        .dashboard-container {
            max-width: 95%;
        }
    }
    @media (max-width: 1100px) {
        .dashboard-container {
            flex-direction: column;
            gap: 1.25rem;
            max-width: 100%;
            padding: 1rem;
            align-items: center;
            width: 100vw;
            overflow-x: hidden;
        }
        .sidebar, .feedback-container {
            width: 100%;
            max-width: 650px;
            padding: 1.25rem;
            min-height: auto;
        }
        .feedback-container {
            max-height: none;
        }
        .card-row {
            flex-direction: column;
            gap: 1rem;
        }
        .main-feedback-card, .additional-task-wrapper {
            flex: none;
        }
    }
    @media (max-width: 768px) {
        .dashboard-container {
            padding: 0.75rem;
        }
        .sidebar, .feedback-container {
            padding: 1rem;
        }
        .btn {
            padding: 0.5rem 1.25rem;
            font-size: 0.9rem;
        }
        h1 {
            font-size: 1.5rem;
        }
        h3 {
            font-size: 1rem;
        }
        p {
            font-size: 0.9rem;
        }
    }
    @media (max-width: 480px) {
        .dashboard-container {
            gap: 0.75rem;
            padding: 0.5rem;
        }
        .sidebar, .feedback-container {
            padding: 0.75rem;
        }
        .btn {
            padding: 0.4rem 1rem;
            font-size: 0.85rem;
        }
        h1 {
            font-size: 1.25rem;
        }
        h3 {
            font-size: 0.95rem;
        }
        p {
            font-size: 0.85rem;
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
            <div class="sidebar-wrapper">
                <div class="sidebar-header">
                    <a href="{{ route('course.show', ['courseCode' => $courseCode]) }}" class="back-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                    <div class="course-badge">
                        {{ $courseCode ?? 'Unknown Course' }}
                    </div>
                </div>
                <h2 class="text-lg font-semibold text-gray-700 text-center mb-4">
                    {{ $course->course_name ?? 'Unnamed Course' }}
                </h2>
                <h3 class="text-base font-semibold text-gray-700 mb-3">Nilai Tugas</h3>
                <div class="space-y-3">
                    @foreach ($scores as $task => $scoreData)
                    <div class="score-card">
                        <p class="font-semibold">Tugas {{ $task }}</p>
                        <p class="score-value">{{ $scoreData['score'] }}/100 ({{ $grades[$task] }})</p>
                    </div>
                    @endforeach
                    @if ($feedback && $feedback->additional_quiz_id && $additionalAttempt)
                    <div class="score-card">
                        <p class="font-semibold">Rata-rata</p>
                        <p class="average-score">{{ number_format($feedback->average_score, 2) }}/100</p>
                    </div>
                    @else
                    <div class="score-card">
                        <p class="font-semibold">Rata-rata</p>
                        <p class="average-score">{{ number_format($feedback->average_score, 2) }}/100</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="feedback-container">
            <h1 class="text-2xl font-semibold mb-6 text-gray-800">Feedback Matkul {{ $course->course_name }}</h1>
            <div class="card-row">
                <div class="main-feedback-card">
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Feedback</h3>
                    <p class="text-gray-700 whitespace-pre-line">{!! nl2br(e($feedback->description)) !!}</p>

                    @if (!empty($failed_task_materials))
                    <h3 class="text-lg font-semibold text-gray-800 mt-4 mb-2">Materi Tugas yang Gagal</h3>
                    <ul class="list-disc pl-5 text-gray-700">
                        @foreach ($failed_task_materials as $task => $material)
                        <li>Tugas {{ $task }}: {{ $material }}</li>
                        @endforeach
                    </ul>
                    @endif

                    @if (!empty($task_distribution))
                    <h3 class="text-lg font-semibold text-gray-800 mt-4 mb-2">Calon Soal Tugas Tambahan</h3>
                    <ul class="list-disc pl-5 text-gray-700">
                        @foreach ($task_distribution as $task => $dist)
                        <li>Tugas {{ $task }}: Easy {{ $dist['easy'] }}, Medium {{ $dist['medium'] }}, Hard {{ $dist['hard'] }}</li>
                        @endforeach
                    </ul>
                    @endif
                </div>
                <div class="additional-task-wrapper">
                    @if ($feedback && $feedback->additional_quiz_id && !$additionalAttempt)
                    <div class="additional-task-card">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Tugas Tambahan</h3>
                        <p>Kamu perlu mengerjakan tugas tambahan sebanyak 20 soal.</p>
                        <p>Distribusi: {{ $question_distribution['easy'] }} easy, {{ $question_distribution['medium'] }} medium, {{ $question_distribution['hard'] }} hard</p>
                        <p>Bobot: Easy {{ number_format($question_weights['easy'], 2) }}, Medium {{ number_format($question_weights['medium'], 2) }}, Hard {{ number_format($question_weights['hard'], 2) }}</p>
                        <a href="javascript:void(0)"
                           onclick="confirmStartQuiz('{{ route('kuis.start', ['courseCode' => $courseCode, 'quizId' => $feedback->additional_quiz_id]) }}', 5)"
                           class="btn btn-primary mt-2">Kerjakan Tugas Tambahan</a>
                    </div>
                    @elseif ($feedback && $feedback->additional_quiz_id && $additionalAttempt)
                    <div class="additional-task-card">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Tugas Tambahan</h3>
                        <p>Tugas tambahan telah selesai dengan nilai: <span class="score-value">{{ $additionalAttempt->score }}/100</span>.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function confirmStartQuiz(url, quizNumber) {
        Swal.fire({
            title: 'Mulai Tugas Tambahan',
            text: `Apakah kamu yakin ingin memulai Tugas Tambahan (Tugas ${quizNumber})?`,
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

    @if (session('feedback_popup'))
        Swal.fire({
            title: '{{ session('feedback_popup.title') }}',
            text: `{!! nl2br(e(session('feedback_popup.text'))) !!}`,
            icon: 'info',
            confirmButtonColor: '#106587',
            confirmButtonText: 'Lihat Detail',
            allowOutsideClick: false
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '{{ session('feedback_popup.redirect') }}';
            }
        });
    @endif
</script>
@endpush
@endsection
