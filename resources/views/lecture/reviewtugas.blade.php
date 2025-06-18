@extends('layouts.app')

@section('title', 'Review Kuis - ' . ($course->course_name ?? 'Course'))

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    body {
        font-family: 'Inter', sans-serif;
        background: #f3f4f6;
        color: #1f2937;
    }

    .container {
        max-width: 100%;
        margin: 0 auto;
        padding: 1rem;
    }

    .quiz-container {
        display: flex;
        gap: 1.5rem;
        width: 100%;
        max-width: 80%;
        margin: 0 auto;
        min-height: 80vh;
    }

    .sidebar {
        width: 20%;
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        padding: 1.5rem;
        position: sticky;
        top: 1rem;
        min-height: 600px;
        display: flex;
        flex-direction: column;
    }

    .sidebar-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .sidebar-header a {
        padding: 0.5rem;
        border-radius: 8px;
        background: #f1f5f9;
        transition: background 0.2s ease;
        cursor: pointer;
    }

    .sidebar-header a:hover {
        background: #e5e7eb;
    }

    .sidebar-header .course-code {
        padding: 0.5rem 1rem;
        background: #106587;
        color: white;
        border-radius: 8px;
        font-size: 0.9rem;
        font-weight: 600;
    }

    .nav-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.75rem;
    }

    .nav-item {
        background: #e5e7eb;
        border-radius: 8px;
        text-align: center;
        font-size: 0.9rem;
        font-weight: 500;
        cursor: pointer;
        padding: 0.75rem;
        transition: all 0.2s ease;
    }

    .nav-item:hover {
        background: #d1d5db;
    }

    .nav-item.correct {
        background: #22c55e;
        color: white;
    }

    .nav-item.incorrect {
        background: #ef4444;
        color: white;
    }

    .main-content {
        width: 80%;
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        padding: 2rem;
        overflow-y: auto;
        min-height: 600px;
        max-height: 80vh;
    }

    .quiz-header h2 {
        font-size: 1.75rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 1.5rem;
    }

    .question-card {
        background: white;
        padding: 1rem;
        border-radius: 0.75rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        margin-bottom: 1.5rem;
    }

    .question-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
    }

    .question-card h4 {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 1rem;
    }

    .question-card p {
        font-size: 1rem;
        color: #4b5563;
        margin-bottom: 1.5rem;
    }

    .question-image {
        max-width: 100%;
        max-height: 300px;
        object-fit: contain;
        margin-bottom: 1rem;
        border-radius: 8px;
    }

    .option-btn {
        display: block;
        width: 100%;
        padding: 0.75rem 1rem;
        margin: 0.5rem 0;
        background: white;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 1rem;
        cursor: text;
        transition: all 0.2s ease;
        user-select: none;
    }

    .option-btn.correct {
        background: #22c55e;
        border-color: #22c55e;
        color: white;
    }

    .option-btn.incorrect {
        background: #ef4444;
        border-color: #ef4444;
        color: white;
    }

    .correct-answer-card {
        background: #f0fdf4;
        border: 1px solid #22c55e;
        border-radius: 6px;
        padding: 0.5rem 1rem;
        margin-bottom: 1rem;
        font-size: 0.9rem;
        color: #166534;
        box-shadow: 0 1px 2px rgba(0,0,0,0.1);
    }

    .result-card, .summary-card {
        background: #f1f5f9;
        border-radius: 0.75rem;
        padding: 1rem;
        margin-bottom: 2rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .btn {
        padding: 0.75rem 1rem;
        border-radius: 0.5rem;
        font-size: 1rem;
        font-weight: 500;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-primary {
        background: #106587;
        color: white;
    }

    .btn-primary:hover {
        background: #0d4f6b;
        transform: translateY(-2px);
    }

    @media (max-width: 1400px) {
        .quiz-container {
            max-width: 90%;
        }
    }

    @media (max-width: 1100px) {
        .quiz-container {
            flex-direction: column;
            gap: 1.5rem;
            max-width: 100%;
            padding: 1rem;
        }
        .sidebar, .main-content {
            width: 100%;
            min-height: auto;
        }
        .main-content {
            max-height: none;
        }
        .nav-grid {
            grid-template-columns: repeat(5, 1fr);
        }
    }

    @media (max-width: 768px) {
        .quiz-container {
            padding: 0.5rem;
        }
        .sidebar, .main-content {
            padding: 1rem;
        }
        .quiz-header h2 {
            font-size: 1.5rem;
        }
        .question-card {
            padding: 0.8rem;
        }
        .question-card h4 {
            font-size: 1.1rem;
        }
        .question-card p {
            font-size: 0.95rem;
        }
        .option-btn {
            padding: 0.6rem;
            font-size: 0.9rem;
        }
        .btn {
            padding: 0.6rem 1.2rem;
            font-size: 0.9rem;
        }
        .nav-item {
            padding: 0.6rem;
            font-size: 0.85rem;
        }
    }

    @media (max-width: 480px) {
        .quiz-container {
            gap: 1rem;
            padding: 0.5rem;
        }
        .sidebar, .main-content {
            padding: 0.75rem;
        }
        .quiz-header h2 {
            font-size: 1.25rem;
        }
        .question-card {
            padding: 0.6rem;
        }
        .question-card h4 {
            font-size: 1rem;
        }
        .question-card p {
            font-size: 0.9rem;
        }
        .option-btn {
            padding: 0.5rem;
            font-size: 0.85rem;
        }
        .btn {
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
        }
        .nav-grid {
            grid-template-columns: repeat(4, 1fr);
        }
        .nav-item {
            padding: 0.5rem;
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
    <div class="quiz-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <a href="{{ route('course.show', ['courseCode' => strtolower(str_replace('-', '', $courseCode))]) }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-800" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <span class="course-code">{{ strtoupper(str_replace('-', '', $courseCode)) }}</span>
            </div>
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Soal</h3>
            <div class="nav-grid">
                @foreach ($questions as $index => $question)
                @php
                $answer = $studentAnswers[$question->id] ?? null;
                $isCorrect = $answer && $answer->is_correct;
                @endphp
                <div class="nav-item {{ $isCorrect ? 'correct' : ($answer ? 'incorrect' : '') }}"
                     id="nav-{{ $index + 1 }}"
                     onclick="showQuestion({{ $index + 1 }})">
                    {{ $index + 1 }}
                </div>
                @endforeach
            </div>
        </div>
        <div class="main-content">
            <div class="quiz-header">
                <h2>Review Kuis {{ $quiz->task_number }} - {{ $course->course_name }}</h2>
            </div>
            <div class="result-card">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Hasil Kuis</h3>
                <p>Skor Anda: <strong>{{ $attempt->score }}/100</strong></p>
                <p>Jumlah Benar: {{ $studentAnswers->filter(fn($answer) => $answer->is_correct)->count() }}/{{ $questions->count() }}</p>
                <a href="{{ route('course.show', ['courseCode' => strtolower(str_replace('-', '', $courseCode))]) }}"
                   class="btn btn-primary mt-4 inline-block">Kembali ke Course</a>
            </div>
            <div class="summary-card">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Ringkasan</h3>
                @php
                $easyCorrect = $studentAnswers->filter(function($answer) use ($questions) {
                $question = $questions->firstWhere('id', $answer->question_id);
                return $question && $question->difficulty == 'easy' && $answer->is_correct;
                })->count();
                $mediumCorrect = $studentAnswers->filter(function($answer) use ($questions) {
                $question = $questions->firstWhere('id', $answer->question_id);
                return $question && $question->difficulty == 'medium' && $answer->is_correct;
                })->count();
                $hardCorrect = $studentAnswers->filter(function($answer) use ($questions) {
                $question = $questions->firstWhere('id', $answer->question_id);
                return $question && $question->difficulty == 'hard' && $answer->is_correct;
                })->count();
                $easyTotal = $questions->where('difficulty', 'easy')->count();
                $mediumTotal = $questions->where('difficulty', 'medium')->count();
                $hardTotal = $questions->where('difficulty', 'hard')->count();
                @endphp
                <p>Easy: {{ $easyCorrect }} / {{ $easyTotal }} benar</p>
                <p>Medium: {{ $mediumCorrect }} / {{ $mediumTotal }} benar</p>
                <p>Hard: {{ $hardCorrect }} / {{ $hardTotal }} benar</p>
                <p>Kesalahan: Mudah {{ $attempt->errors_easy }}, Sedang {{ $attempt->errors_medium }}, Sulit {{ $attempt->errors_hard }}</p>
            </div>
            @foreach ($questions as $index => $question)
            @php
            $answer = $studentAnswers[$question->id] ?? null;
            $isCorrect = $answer && $answer->is_correct;
            @endphp
            <div class="question-card" id="question-{{ $index + 1 }}">
                <h4>Soal {{ $index + 1 }} ({{ ucfirst($question->difficulty) }})</h4>
                @if ($question->image)
                <img src="{{ asset('storage/' . $question->image) }}" alt="Question Image" class="question-image">
                @endif
                <p>{{ $question->question_text }}</p>
                @if ($answer && !$isCorrect)
                <div class="correct-answer-card">
                    Jawaban yang benar: {{ $question->correct_option }}. {{ $question->{'option_' . strtolower($question->correct_option)} }}
                </div>
                @endif
                @foreach (['A', 'B', 'C', 'D'] as $option)
                @php
                $isSelected = $answer && $answer->selected_option == $option;
                $isCorrectOption = $question->correct_option == $option;
                $optionClass = $isSelected ? ($isCorrect ? 'correct' : 'incorrect') : ($isCorrectOption ? 'correct' : '');
                @endphp
                <div class="option-btn {{ $optionClass }}">
                    {{ $option }}. {{ $question->{'option_' . strtolower($option)} }}
                </div>
                @endforeach
            </div>
            @endforeach
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    let currentQuestion = 1;
    const totalQuestions = {{ $questions->count() }};

    function showQuestion(number) {
        if (number < 1 || number > totalQuestions) return;
        console.log('Showing question:', number);
        const targetQuestion = document.getElementById(`question-${number}`);
        if (targetQuestion) {
            targetQuestion.scrollIntoView({ behavior: 'smooth', block: 'start' });
        } else {
            console.error('Question not found:', `question-${number}`);
        }
        currentQuestion = number;
    }

    @if (session('quiz_completed'))
        Swal.fire({
            title: 'Kuis Selesai!',
            html: `Tugas {{ $quiz->task_number }} telah selesai.<br>Skor: {{ $attempt->score }}/100`,
            icon: 'success',
            confirmButtonColor: '#106587',
        });
    @endif
    @if (session('feedback_popup'))
        Swal.fire({
            title: '{{ session('feedback_popup.title') }}',
            html: `{!! nl2br(e(session('feedback_popup.text'))) !!}`,
            icon: 'info',
            confirmButtonColor: '#106587',
            confirmButtonText: 'Lihat Feedback',
            showCancelButton: true,
            cancelButtonColor: '#d33',
            cancelButtonText: 'Tutup',
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '{{ session('feedback_popup.redirect') }}';
            }
        });
    @endif
</script>
@endpush
@endsection
