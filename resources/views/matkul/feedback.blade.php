@extends('layouts.app')

@section('title', 'Feedback - ' . ($course->course_name ?? 'Course'))

@section('content')
<style>
    .container {
        max-width: 100%;
        margin: 0 auto;
        padding: 1.5rem;
    }
    .feedback-container {
        max-width: 80%;
        margin: 0 auto;
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        padding: 2rem;
        min-height: 700px;
    }
    .feedback-card {
        background: #f9fafb;
        padding: 1rem;
        border-radius: 0.5rem;
        margin-bottom: 1rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }
    .btn {
        padding: 0.5rem 1.5rem;
        border-radius: 0.5rem;
        font-size: 0.95rem;
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
    .btn-disabled {
        background: #6b7280;
        color: white;
        cursor: not-allowed;
    }
</style>

<div class="container">
    <div class="feedback-container">
        <h1 class="text-2xl font-semibold mb-6 text-gray-800">Feedback Kursus {{ $course->course_name }}</h1>
        <div class="feedback-card">
            <p class="text-gray-700">{!! nl2br(e($feedback->description)) !!}</p>
        </div>
        <div class="feedback-card">
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Nilai Tugas</h3>
            @foreach ($scores as $task => $score)
            <p>Tugas {{ $task }}: <strong>{{ $score }}/100 ({{ $grades[$task] }})</strong></p>
            @endforeach
            @if ($additionalAttempt)
            <p>Tugas Tambahan: <strong>{{ $additionalAttempt->score }}/100</strong></p>
            @endif
            <p>Rata-rata: <strong>{{ number_format($feedback->average_score, 2) }}/100</strong></p>
        </div>
        @if ($feedback && $feedback->additional_quiz_id && !$additionalAttempt)
        <div class="feedback-card">
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Tugas Tambahan</h3>
            <p>Kamu perlu mengerjakan tugas tambahan sebanyak 20 soal.</p>
            <p>Distribusi: {{ $question_distribution['easy'] }} mudah, {{ $question_distribution['medium'] }} sedang, {{ $question_distribution['hard'] }} sulit</p>
            <p>Bobot: Mudah {{ number_format($question_weights['easy'], 2) }}, Sedang {{ number_format($question_weights['medium'], 2) }}, Sulit {{ number_format($question_weights['hard'], 2) }}</p>
            <a href="javascript:void(0)"
               onclick="confirmStartQuiz('{{ route('kuis.start', ['courseCode' => $courseCode, 'quizId' => $feedback->additional_quiz_id]) }}', 5)"
               class="btn btn-primary mt-2">Kerjakan Tugas Tambahan</a>
        </div>
        @elseif ($feedback && $feedback->additional_quiz_id && $additionalAttempt)
        <div class="feedback-card">
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Tugas Tambahan</h3>
            <p>Tugas tambahan telah selesai dengan nilai: <strong>{{ $additionalAttempt->score }}/100</strong>.</p>
        </div>
        @endif
        <a href="{{ route('course.show', ['courseCode' => $courseCode]) }}"
           class="btn btn-primary mt-4">Kembali ke Kursus</a>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function confirmStartQuiz(url, quizNumber) {
        Swal.fire({
            title: 'Mulai Tugas Tambahan',
            text: `Apakah kamu yakin ingin memulai Tugas ${quizNumber}?`,
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
@endpush
@endsection
