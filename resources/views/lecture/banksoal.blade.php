@extends('layouts.app')

@section('title', 'Bank Soal - ' . ($course->course_name ?? 'Course'))

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
    .container {
        max-width: 100%;
        margin: 0 auto;
        padding: 1.5rem;
        box-sizing: border-box;
        padding-bottom: 5rem;
    }
    .dashboard-container {
        display: flex;
        gap: 1.5rem;
        justify-content: center;
        align-items: stretch;
        width: 100%;
        max-width: 80%;
        margin: 0 auto;
        min-height: 600px;
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
        justify-content: flex-start;
        min-height: 600px;
    }
    .main-content {
        flex: 3;
        padding: 1.5rem;
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        overflow-y: auto;
        min-height: 600px;
        max-height: 600px;
    }
    .btn {
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        font-weight: 500;
        transition: background 0.3s ease, transform 0.2s ease;
        text-align: center;
        display: block;
        width: 100%;
        margin-bottom: 0.5rem;
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
    .btn-info {
        background: #3b82f6;
        color: white;
    }
    .btn-info:hover {
        background: #2563eb;
        transform: translateY(-2px);
    }
    .btn-active {
        background: #0a4560 !important;
        transform: translateY(-2px);
    }
    .btn-all-soal {
        margin-bottom: 1rem; /* Extra spacing below Semua Bank Soal */
    }
    .question-card {
        background: #f9fafb;
        padding: 0.75rem;
        border-radius: 0.5rem;
        margin-bottom: 0.5rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }
    .question-card p {
        margin: 0;
        font-size: 0.875rem;
        color: #374151;
    }
    .add-question-form {
        display: none;
        margin-top: 1rem;
        background: #f9fafb;
        padding: 1rem;
        border-radius: 0.5rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }
    .add-question-form.active {
        display: block;
    }
    input, textarea, select {
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        padding: 0.5rem;
        width: 100%;
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
        font-size: 0.875rem;
    }
    input:focus, textarea:focus, select:focus {
        border-color: #106587;
        box-shadow: 0 0 0 3px rgba(16, 101, 135, 0.1);
        outline: none;
    }
    label {
        font-size: 0.875rem;
        font-weight: 500;
        color: #374151;
        margin-bottom: 0.5rem;
        display: block;
    }
    .swal2-popup {
        font-size: 1rem !important;
        padding: 1rem !important;
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
            width: 100vw;
            overflow-x: hidden;
        }
        .sidebar, .main-content {
            width: 100%;
            max-width: 600px;
            padding: 1rem;
            min-height: auto;
        }
        .main-content {
            max-height: none;
        }
    }
    @media (max-width: 768px) {
        .container {
            padding: 0.5rem;
        }
        .sidebar, .main-content {
            padding: 0.75rem;
        }
        .btn {
            padding: 0.4rem 0.8rem;
            font-size: 0.85rem;
        }
        input, textarea, select {
            padding: 0.4rem;
            font-size: 0.85rem;
        }
        label {
            font-size: 0.8rem;
        }
    }
    @media (max-width: 480px) {
        .container {
            gap: 0.75rem;
            padding: 0.5rem;
        }
        .sidebar, .main-content {
            padding: 0.5rem;
        }
        .btn {
            padding: 0.3rem 0.7rem;
            font-size: 0.8rem;
        }
        input, textarea, select {
            padding: 0.3rem;
            font-size: 0.8rem;
        }
        label {
            font-size: 0.75rem;
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
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="flex justify-between items-center mb-4">
                <a href="{{ route('lecturer.course.show', ['courseCode' => $courseCodeWithoutDash]) }}" class="p-2 rounded-full bg-gray-100 hover:bg-gray-200 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <div class="px-3 py-1 bg-blue-500 text-white rounded-lg text-sm font-semibold">
                    {{ $course->course_code ?? 'Unknown Course' }}
                </div>
            </div>
            <h2 class="text-lg font-semibold text-gray-700 text-center mb-4">
                Bank Soal {{ $course->course_code ?? '' }} {{ $course->course_name ?? 'Unnamed Course' }}
            </h2>
            <button id="btn-all" onclick="filterQuestions('all')" class="btn btn-info btn-all-soal">Semua Bank Soal</button>
            <button id="btn-1" onclick="filterQuestions(1)" class="btn btn-primary">Tugas 1</button>
            <button id="btn-2" onclick="filterQuestions(2)" class="btn btn-primary">Tugas 2</button>
            <button id="btn-3" onclick="filterQuestions(3)" class="btn btn-primary">Tugas 3</button>
            <button id="btn-4" onclick="filterQuestions(4)" class="btn btn-primary">Tugas 4</button>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <h1 class="text-xl font-semibold mb-4 text-gray-800">Bank Soal</h1>
            <div id="addQuestionForm" class="add-question-form">
                <h3 class="text-sm font-semibold text-gray-700 mb-2">Tambah Soal Baru untuk Tugas <span id="taskNumberLabel"></span></h3>
                <form id="questionForm" method="POST" action="{{ route('questions.store') }}">
                    @csrf
                    <input type="hidden" name="task_number" id="taskNumberInput" value="">
                    <input type="hidden" name="course_id" value="{{ $course->id }}">
                    <div class="mb-3">
                        <label for="question_text">Teks Soal</label>
                        <textarea name="question_text" id="question_text" required placeholder="Masukkan teks soal"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="option_a">Pilihan A</label>
                        <input type="text" name="option_a" id="option_a" required placeholder="Pilihan A">
                    </div>
                    <div class="mb-3">
                        <label for="option_b">Pilihan B</label>
                        <input type="text" name="option_b" id="option_b" required placeholder="Pilihan B">
                    </div>
                    <div class="mb-3">
                        <label for="option_c">Pilihan C</label>
                        <input type="text" name="option_c" id="option_c" required placeholder="Pilihan C">
                    </div>
                    <div class="mb-3">
                        <label for="option_d">Pilihan D</label>
                        <input type="text" name="option_d" id="option_d" required placeholder="Pilihan D">
                    </div>
                    <div class="mb-3">
                        <label for="correct_option">Jawaban Benar</label>
                        <select name="correct_option" id="correct_option" required>
                            <option value="" disabled selected>Pilih jawaban benar</option>
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="C">C</option>
                            <option value="D">D</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="difficulty">Tingkat Kesulitan</label>
                        <select name="difficulty" id="difficulty" required>
                            <option value="" disabled selected>Pilih tingkat kesulitan</option>
                            <option value="easy">Mudah</option>
                            <option value="medium">Sedang</option>
                            <option value="hard">Sulit</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Tambah Soal</button>
                </form>
            </div>
            <div class="space-y-4" id="questionList">
                @if ($questions->isEmpty())
                <p class="text-gray-500 text-sm">Tidak ada soal tersedia untuk mata kuliah ini.</p>
                @else
                @foreach ($questions->groupBy('task_number') as $taskNumber => $taskQuestions)
                @if ($taskNumber)
                <div class="task-group" data-task="{{ $taskNumber }}">
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">Tugas {{ $taskNumber }}</h3>
                    @foreach ($taskQuestions as $question)
                    <div class="question-card">
                        <p><strong>Soal:</strong> {{ $question->question_text }}</p>
                        <p><strong>Pilihan A:</strong> {{ $question->option_a }}</p>
                        <p><strong>Pilihan B:</strong> {{ $question->option_b }}</p>
                        <p><strong>Pilihan C:</strong> {{ $question->option_c }}</p>
                        <p><strong>Pilihan D:</strong> {{ $question->option_d }}</p>
                        <p><strong>Jawaban Benar:</strong> {{ $question->correct_option }}</p>
                        <p><strong>Kesulitan:</strong> {{ ucfirst($question->difficulty) }}</p>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="task-group" data-task="null">
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">Soal Tanpa Tugas</h3>
                    @foreach ($taskQuestions as $question)
                    <div class="question-card">
                        <p><strong>Soal:</strong> {{ $question->question_text }}</p>
                        <p><strong>Pilihan A:</strong> {{ $question->option_a }}</p>
                        <p><strong>Pilihan B:</strong> {{ $question->option_b }}</p>
                        <p><strong>Pilihan C:</strong> {{ $question->option_c }}</p>
                        <p><strong>Pilihan D:</strong> {{ $question->option_d }}</p>
                        <p><strong>Jawaban Benar:</strong> {{ $question->correct_option }}</p>
                        <p><strong>Kesulitan:</strong> {{ ucfirst($question->difficulty) }}</p>
                    </div>
                    @endforeach
                </div>
                @endif
                @endforeach
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    let currentTask = 'all';

    function filterQuestions(task) {
        Swal.fire({
            title: 'Memuat Soal',
            text: `Menampilkan soal untuk ${task === 'all' ? 'Semua Bank Soal' : 'Tugas ' + task}...`,
            icon: 'info',
            timer: 1000,
            showConfirmButton: false
        });

        currentTask = task;
        const questionList = document.getElementById('questionList');
        const taskGroups = document.querySelectorAll('.task-group');
        const addQuestionForm = document.getElementById('addQuestionForm');
        const taskNumberInput = document.getElementById('taskNumberInput');
        const taskNumberLabel = document.getElementById('taskNumberLabel');

        // Update active button
        document.querySelectorAll('.btn').forEach(btn => btn.classList.remove('btn-active'));
        const activeBtn = document.getElementById(`btn-${task}`);
        if (activeBtn) activeBtn.classList.add('btn-active');

        // Filter questions
        taskGroups.forEach(group => {
            const groupTask = group.getAttribute('data-task');
            if (task === 'all') {
                group.style.display = 'block';
            } else if (groupTask === task.toString()) {
                group.style.display = 'block';
            } else {
                group.style.display = 'none';
            }
        });

        // Show/hide add question form
        if (task !== 'all') {
            addQuestionForm.classList.add('active');
            taskNumberInput.value = task;
            taskNumberLabel.textContent = task;
        } else {
            addQuestionForm.classList.remove('active');
            taskNumberInput.value = '';
            taskNumberLabel.textContent = '';
        }

        // Update empty message
        const visibleGroups = Array.from(taskGroups).filter(group => group.style.display !== 'none');
        if (visibleGroups.length === 0 && task !== 'all') {
            questionList.innerHTML = '<p class="text-gray-500 text-sm">Tidak ada soal tersedia untuk tugas ini.</p>';
        } else if (task === 'all' && !questionList.querySelector('.task-group')) {
            questionList.innerHTML = '<p class="text-gray-500 text-sm">Tidak ada soal tersedia untuk mata kuliah ini.</p>';
        }
    }

    document.getElementById('questionForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;

        // Validate form fields
        const questionText = form.querySelector('#question_text').value;
        const optionA = form.querySelector('#option_a').value;
        const optionB = form.querySelector('#option_b').value;
        const optionC = form.querySelector('#option_c').value;
        const optionD = form.querySelector('#option_d').value;
        const correctOption = form.querySelector('#correct_option').value;
        const difficulty = form.querySelector('#difficulty').value;
        const taskNumber = form.querySelector('#taskNumberInput').value;

        if (!questionText || !optionA || !optionB || !optionC || !optionD || !correctOption || !difficulty || !taskNumber) {
            Swal.fire({
                title: 'Error!',
                text: 'Semua kolom harus diisi!',
                icon: 'error',
                confirmButtonColor: '#106587'
            });
            return;
        }

        Swal.fire({
            title: 'Konfirmasi',
            text: 'Apakah Anda yakin ingin menambahkan soal ini?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#106587',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Tambah',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData(form);
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Gagal menyimpan soal');
                        }
                        return response.json();
                    })
                    .then(data => {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: data.message || 'Soal berhasil ditambahkan!',
                            icon: 'success',
                            confirmButtonColor: '#106587'
                        }).then(() => {
                            form.reset();
                            window.location.reload();
                        });
                    })
                    .catch(error => {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Gagal menambahkan soal: ' + error.message,
                            icon: 'error',
                            confirmButtonColor: '#106587'
                        });
                    });
            }
        });
    });

    // Initial filter
    filterQuestions('all');

    @if(session('success'))
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: 'Berhasil!',
                text: '{{ session('success') }}',
                icon: 'success',
                confirmButtonColor: '#106587',
                confirmButtonText: 'OK'
            });
        });
    @endif

    @if(session('error'))
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: 'Error!',
                text: '{{ session('error') }}',
                icon: 'error',
                confirmButtonColor: '#106587',
                confirmButtonText: 'OK'
            });
        });
    @endif
</script>
@endpush
@endsection
