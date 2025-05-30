@extends('layouts.app')

@section('title', 'Kuis - ' . ($course->course_name ?? 'Course'))

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

    .nav-item.answered {
        background: #106587;
        color: white;
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

    .option-btn {
        display: block;
        width: 100%;
        padding: 0.75rem 1rem;
        margin: 0.5rem 0;
        background: #ffffff;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .option-btn:hover {
        border-color: #106587;
        background: #f1f5f9;
    }

    .option-btn.active {
        background: #106587;
        color: white;
        border-color: #106587;
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

    .option-input {
        display: none;
    }

    .result-card {
        background: #f1f5f9;
        border-radius: 0.75rem;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .btn {
        padding: 0.75rem 1.5rem;
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
        background: #0d4a6b;
        transform: translateY(-2px);
    }

    .btn-secondary {
        background: #6b7280;
        color: white;
    }

    .btn-secondary:hover {
        background: #4b5563;
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
                <div class="nav-item {{ isset($studentAnswers[$question->id]) ? 'answered' : '' }}
                                {{ isset($attempt) && isset($studentAnswers[$question->id]) ? ($studentAnswers[$question->id]->is_correct ? 'correct' : 'incorrect') : '' }}"
                     id="nav-{{ $index + 1 }}"
                     onclick="showQuestion({{ $index + 1 }})">
                    {{ $index + 1 }}
                </div>
                @endforeach
            </div>
        </div>

        <div class="main-content">
            <div class="quiz-header">
                <h2>Kuis {{ $quiz->task_number }} - {{ $course->course_name }}</h2>
            </div>

            @if (isset($attempt))
            <div class="result-card">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Hasil Kuis</h3>
                <p>Skor Anda: <strong>{{ $score }}/100</strong></p>
                <a href="{{ route('course.show', ['courseCode' => strtolower(str_replace('-', '', $courseCode))]) }}"
                   class="btn btn-primary mt-4 inline-block">Kembali ke Kursus</a>
            </div>
            @foreach ($questions as $index => $question)
            <div class="question-card" id="question-{{ $index + 1 }}">
                <h4>Soal {{ $index + 1 }} ({{ ucfirst($question->difficulty) }})</h4>
                <p>{{ $question->question_text }}</p>
                @foreach (['A', 'B', 'C', 'D'] as $option)
                @php
                $isSelected = isset($studentAnswers[$question->id]) && $studentAnswers[$question->id]->selected_option == $option;
                $isCorrectOption = $question->correct_option == $option;
                $isIncorrect = $isSelected && !$studentAnswers[$question->id]->is_correct;
                @endphp
                <div class="option-btn {{ $isSelected ? 'active' : '' }} {{ $isCorrectOption ? 'correct' : ($isIncorrect ? 'incorrect' : '') }}">
                    {{ $option }}. {{ $question->{'option_' . strtolower($option)} }}
                </div>
                @if ($isSelected && !$isCorrectOption)
                <p class="text-sm text-gray-600 mt-2">Jawaban yang benar: {{ $question->correct_option }}. {{ $question->{'option_' . strtolower($question->correct_option)} }}</p>
                @endif
                @endforeach
            </div>
            @endforeach
            @else
            <form id="quiz-form" action="{{ route('kuis.submit', ['courseCode' => $courseCode, 'quizId' => $quizId]) }}" method="POST">
                @csrf
                @foreach ($questions as $index => $question)
                <div class="question-card" id="question-{{ $index + 1 }}" style="display: {{ $index == 0 ? 'block' : 'none' }};">
                    <h4>Soal {{ $index + 1 }} ({{ ucfirst($question->difficulty) }})</h4>
                    <p>{{ $question->question_text }}</p>
                    @foreach (['A', 'B', 'C', 'D'] as $option)
                    <input type="radio" name="answers[{{ $question->id }}]" value="{{ $option }}"
                           id="option-{{ $question->id }}-{{ $option }}"
                           class="option-input" onchange="markAnswered({{ $index + 1 }})">
                    <label for="option-{{ $question->id }}-{{ $option }}" class="option-btn">
                        {{ $option }}. {{ $question->{'option_' . strtolower($option)} }}
                    </label>
                    @endforeach
                </div>
                @endforeach

                <div class="flex justify-between mt-6 gap-2">
                    <button type="button" class="btn btn-secondary" id="back-btn" onclick="navigateQuestion(-1)" style="display: none;">Back</button>
                    <button type="button" class="btn btn-primary" id="next-btn" onclick="navigateQuestion(1)">Next</button>
                    <button type="submit" class="btn btn-primary" id="submit-btn" style="display: none;">Submit</button>
                </div>
            </form>
            @endif
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
        document.querySelectorAll('.question-card').forEach(card => card.style.display = @if(!isset($attempt)) 'none' @else 'block' @endif);
        const targetQuestion = document.getElementById(`question-${number}`);
        if (targetQuestion) {
            targetQuestion.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
        currentQuestion = number;

    @if (!isset($attempt))
            document.getElementById('back-btn').style.display = currentQuestion === 1 ? 'none' : 'inline-block';
        document.getElementById('next-btn').style.display = currentQuestion === totalQuestions ? 'none' : 'inline-block';
        document.getElementById('submit-btn').style.display = currentQuestion === totalQuestions ? 'inline-block' : 'none';
    @endif

        updateActiveStates();
    }

    function navigateQuestion(direction) {
        showQuestion(currentQuestion + direction);
    }

    function markAnswered(questionNumber) {
        const navItem = document.getElementById(`nav-${questionNumber}`);
        if (navItem) {
            navItem.classList.add('answered');
        }
        updateActiveStates();
    }

    function updateActiveStates() {
        document.querySelectorAll('.question-card').forEach(card => {
            const inputs = card.querySelectorAll('.option-input');
            const labels = card.querySelectorAll('.option-btn');
            labels.forEach(label => label.classList.remove('active'));
            inputs.forEach(input => {
                if (input.checked) {
                    const label = document.querySelector(`label[for="${input.id}"]`);
                    if (label) label.classList.add('active');
                }
            });
        });
    }

    document.getElementById('quiz-form')?.addEventListener('submit', function(event) {
        event.preventDefault();
        const form = event.target;
        const inputs = form.querySelectorAll('input[type="radio"]:checked');

        if (inputs.length < totalQuestions) {
            Swal.fire({
                title: 'Peringatan!',
                text: 'Harap jawab semua soal sebelum mengirimkan kuis.',
                icon: 'warning',
                confirmButtonColor: '#106587'
            });
            return;
        }

        Swal.fire({
            title: 'Konfirmasi',
            text: 'Apakah Anda yakin ingin mengirimkan kuis? Jawaban tidak dapat diubah setelah ini.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#106587',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Kirim',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

    updateActiveStates();
    @if (isset($score))
        Swal.fire({
            title: 'Kuis Selesai!',
            text: 'Skor Anda: {{ $score }}/100',
            icon: 'success',
            confirmButtonColor: '#106587'
        });
    @endif
</script>
@endpush
@endsection
