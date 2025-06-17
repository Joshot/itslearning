@extends('layouts.app')

@section('title', 'Bank Soal - ' . ($course->course_name ?? 'Course'))

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<style>
    /* Unchanged container styles */
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
    /* Unchanged button styles */
    .btn {
        padding: 0.6rem 1.2rem;
        border-radius: 0.75rem;
        font-size: 0.9rem;
        font-weight: 600;
        letter-spacing: 0.025em;
        text-align: center;
        display: inline-block;
        border: none;
        cursor: pointer;
        transition: transform 0.2s ease, box-shadow 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    .btn-primary {
        background: linear-gradient(135deg, #0d9488 0%, #34d399 100%);
        color: white;
        box-shadow: 0 2px 8px rgba(13, 148, 136, 0.3);
    }
    .btn-primary:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 12px rgba(13, 148, 136, 0.5);
        animation: pulse 1.5s infinite;
    }
    .btn-info {
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        color: white;
        box-shadow: 0 2px 8px rgba(79, 70, 229, 0.3);
    }
    .btn-info:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.5);
        animation: pulse 1.5s infinite;
    }
    .btn-active {
        background: linear-gradient(135deg, #1e3a8a 0%, #4f46e5 100%) !important;
        transform: scale(1.02);
        box-shadow: 0 4px 12px rgba(30, 58, 138, 0.5) !important;
    }
    .btn-primary.btn-active {
        background: linear-gradient(135deg, #065f46 0%, #0d9488 100%) !important;
    }
    .btn-all-soal {
        margin-bottom: 1.5rem;
        width: 100%;
        padding: 0.8rem 1.2rem;
        font-size: 1rem;
    }
    .btn-primary:not(:last-child) {
        margin-bottom: 0.75rem;
    }
    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.4); }
        70% { box-shadow: 0 0 0 10px rgba(255, 255, 255, 0); }
        100% { box-shadow: 0 0 0 0 rgba(255, 255, 255, 0); }
    }
    /* Unchanged question-card container */
    .question-card {
        background: #f9fafb;
        padding: 1rem;
        border-radius: 0.5rem;
        margin-bottom: 1rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    /* Redesigned question-card content */
    .question-header {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 1rem;
        font-weight: 700;
        color: #111827;
        margin-bottom: 0.5rem;
        border-bottom: 1px solid #e5e7eb;
        padding-bottom: 0.25rem;
    }
    .question-number {
        background: #0d9488;
        color: white;
        padding: 0.2rem 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.85rem;
    }
    .question-text {
        font-size: 0.95rem;
        color: #1f2937;
        line-height: 1.5;
        word-break: break-word;
    }
    .options-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.5rem;
        margin: 0.5rem 0;
    }
    .option {
        display: flex;
        align-items: flex-start;
        gap: 0.5rem;
        font-size: 0.9rem;
        color: #1f2937;
    }
    .option-label {
        font-weight: 600;
        color: #111827;
        min-width: 20px;
    }
    .option-label::before {
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        color: #0d9488;
        margin-right: 0.25rem;
        content: '\f111';
    }
    .option-text {
        word-break: break-word;
        line-height: 1.4;
    }
    .footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 0.5rem;
        padding-top: 0.5rem;
        border-top: 1px solid #e5e7eb;
    }
    .correct-answer {
        font-size: 0.9rem;
        color: #1f2937;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .correct-answer::before {
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        color: #10b981;
        content: '\f00c';
    }
    .difficulty-badge {
        padding: 0.2rem 0.75rem;
        border-radius: 1rem;
        font-size: 0.8rem;
        font-weight: 600;
        color: white;
    }
    .difficulty-badge.easy { background: #10b981; }
    .difficulty-badge.medium { background: #f59e0b; }
    .difficulty-badge.hard { background: #ef4444; }
    .question-image {
        max-width: 200px;
        max-height: 200px;
        object-fit: contain;
        border-radius: 0.5rem;
        margin: 0.5rem auto;
        display: block;
        border: 1px solid #e5e7eb;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
    }
    .question-image:hover {
        transform: scale(1.1);
    }
    /* Unchanged other styles */
    input, textarea, select {
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        padding: 0.5rem;
        width: 100%;
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
        font-size: 0.875rem;
    }
    input:focus, textarea:focus, select:focus {
        border-color: #0d9488;
        box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.1);
        outline: none;
    }
    label {
        font-size: 0.875rem;
        font-weight: 500;
        color: #374151;
        margin-bottom: 0.5rem;
        display: block;
    }
    .summary-section {
        margin-top: auto;
        padding-top: 1rem;
        border-top: 1px solid #e5e7eb;
        background: #f9fafb;
        border-radius: 0.5rem;
        padding: 1rem;
    }
    .summary-section h3 {
        font-size: 0.875rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.75rem;
    }
    .summary-section p {
        font-size: 0.75rem;
        color: #4b5563;
        margin: 0.25rem 0;
        line-height: 1.4;
    }
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        justify-content: center;
        align-items: center;
        z-index: 1000;
    }
    .modal-content {
        background: white;
        padding: 1.5rem;
        border-radius: 0.5rem;
        max-width: 600px;
        width: 90%;
        max-height: 80vh;
        overflow-y: auto;
        position: relative;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
    }
    .modal-close {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
        background: none;
        border: none;
        font-size: 1.25rem;
        cursor: pointer;
        color: #374151;
    }
    .modal-close:hover {
        color: #0d9488;
    }
    .modal.active {
        display: flex;
    }
    .action-buttons {
        display: flex;
        gap: 0.5rem;
        justify-content: flex-end;
        margin-bottom: 1rem;
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
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
        }
        .options-grid {
            grid-template-columns: 1fr;
        }
        .question-header, .question-text, .option, .footer, .correct-answer {
            font-size: 0.85rem;
        }
        .question-image {
            max-width: 150px;
            max-height: 150px;
        }
        input, textarea, select {
            padding: 0.4rem;
            font-size: 0.85rem;
        }
        label {
            font-size: 0.8rem;
        }
        .summary-section {
            padding: 0.75rem;
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
            padding: 0.4rem 0.8rem;
            font-size: 0.8rem;
        }
        .question-header, .question-text, .option, .footer, .correct-answer {
            font-size: 0.8rem;
        }
        .question-image {
            max-width: 100px;
            max-height: 100px;
        }
        input, textarea, select {
            padding: 0.3rem;
            font-size: 0.8rem;
        }
        label {
            font-size: 0.75rem;
        }
        .summary-section {
            padding: 0.5rem;
        }
        .modal-content {
            width: 95%;
            padding: 1rem;
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
                <a href="{{ route('lecturer.course.show', ['courseCode' => $course->course_code ? str_replace('-', '', $course->course_code) : 'unknown']) }}" class="p-2 rounded-full bg-gray-100 hover:bg-gray-200 transition">
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
            <!-- Summary Section -->
            <div class="summary-section">
                <h3>Ringkasan Soal</h3>
                <div id="summary-content">
                    <p>Menunggu data soal...</p>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="action-buttons">
                <button id="importExcelBtn" class="btn btn-info">Import Excel</button>
                <button id="addQuestionBtn" class="btn btn-primary">Tambah Soal</button>
            </div>
            <h1 class="text-xl font-semibold mb-4 text-gray-800">Bank Soal</h1>
            <!-- Add Question Modal -->
            <div id="addQuestionModal" class="modal">
                <div class="modal-content">
                    <button class="modal-close">×</button>
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Tambah Soal Baru</h3>
                    <form id="questionForm" method="POST" action="{{ route('questions.store') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="course_id" value="{{ $course->id }}">
                        <div class="mb-3">
                            <label for="task_number">Nomor Tugas</label>
                            <select name="task_number" id="task_number" required>
                                <option value="" disabled selected>Pilih nomor tugas</option>
                                <option value="1">Tugas 1</option>
                                <option value="2">Tugas 2</option>
                                <option value="3">Tugas 3</option>
                                <option value="4">Tugas 4</option>
                            </select>
                        </div>
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
                        <div class="mb-3">
                            <label for="image">Gambar (Opsional)</label>
                            <input type="file" name="image" id="image" accept="image/jpeg,image/png,image/jpg,image/gif">
                        </div>
                        <button type="submit" class="btn btn-primary">Tambah Soal</button>
                    </form>
                </div>
            </div>
            <!-- Import Excel Modal -->
            <div id="importExcelModal" class="modal">
                <div class="modal-content">
                    <button class="modal-close">×</button>
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Import Soal dari Excel</h3>
                    <form id="importExcelForm" method="POST" action="{{ route('questions.import') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="course_id" value="{{ $course->id }}">
                        <div class="mb-3">
                            <label for="import_task_number">Nomor Tugas</label>
                            <select name="task_number" id="import_task_number" required>
                                <option value="" disabled selected>Pilih nomor tugas</option>
                                <option value="1">Tugas 1</option>
                                <option value="2">Tugas 2</option>
                                <option value="3">Tugas 3</option>
                                <option value="4">Tugas 4</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="excel_file">File Excel (CSV)</label>
                            <input type="file" name="excel_file" id="excel_file" accept=".csv" required>
                            <p class="text-sm text-gray-500 mt-1">Format: Question, Option A, Option B, Option C, Option D, Correct Option, Difficulty</p>
                        </div>
                        <button type="submit" class="btn btn-primary">Import</button>
                    </form>
                </div>
            </div>
            <!-- Question List -->
            <div class="space-y-4" id="questionList">
                @if ($questions->isEmpty())
                <p class="text-gray-500 text-sm">Tidak ada soal tersedia untuk mata kuliah ini.</p>
                @else
                @foreach ($questions->groupBy('task_number') as $taskNumber => $taskQuestions)
                @if ($taskNumber)
                <div class="task-group" data-task="{{ $taskNumber }}">
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">Tugas {{ $taskNumber }}</h3>
                    @foreach ($taskQuestions as $index => $question)
                    <div class="question-card">
                        <div class="question-header">
                            <span class="question-number">Soal {{ $index + 1 }}</span>
                            <span class="question-text">{{ $question->question_text }}</span>
                        </div>
                        @if ($question->image)
                        <img src="{{ asset('storage/' . $question->image) }}" alt="Question Image" class="question-image">
                        @endif
                        <div class="options-grid">
                            <div class="option">
                                <span class="option-label">A</span>
                                <span class="option-text">{{ $question->option_a }}</span>
                            </div>
                            <div class="option">
                                <span class="option-label">C</span>
                                <span class="option-text">{{ $question->option_c }}</span>
                            </div>
                            <div class="option">
                                <span class="option-label">B</span>
                                <span class="option-text">{{ $question->option_b }}</span>
                            </div>
                            <div class="option">
                                <span class="option-label">D</span>
                                <span class="option-text">{{ $question->option_d }}</span>
                            </div>
                        </div>
                        <div class="footer">
                            <span class="correct-answer">Kunci Jawaban: {{ $question->correct_option }}</span>
                            <span class="difficulty-badge {{ $question->difficulty }}">{{ ucfirst($question->difficulty) }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="task-group" data-task="null">
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">Soal Tanpa Tugas</h3>
                    @foreach ($taskQuestions as $index => $question)
                    <div class="question-card">
                        <div class="question-header">
                            <span class="question-number">Soal {{ $index + 1 }}</span>
                            <span class="question-text">{{ $question->question_text }}</span>
                        </div>
                        @if ($question->image)
                        <img src="{{ asset('storage/' . $question->image) }}" alt="Question Image" class="question-image">
                        @endif
                        <div class="options-grid">
                            <div class="option">
                                <span class="option-label">A</span>
                                <span class="option-text">{{ $question->option_a }}</span>
                            </div>
                            <div class="option">
                                <span class="option-label">C</span>
                                <span class="option-text">{{ $question->option_c }}</span>
                            </div>
                            <div class="option">
                                <span class="option-label">B</span>
                                <span class="option-text">{{ $question->option_b }}</span>
                            </div>
                            <div class="option">
                                <span class="option-label">D</span>
                                <span class="option-text">{{ $question->option_d }}</span>
                            </div>
                        </div>
                        <div class="footer">
                            <span class="correct-answer">Kunci Jawaban: {{ $question->correct_option }}</span>
                            <span class="difficulty-badge {{ $question->difficulty }}">{{ ucfirst($question->difficulty) }}</span>
                        </div>
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
    const questionCounts = @json($questionCounts) || {
        total: 0,
        1: { easy: 0, medium: 0, hard: 0, total: 0 },
        2: { easy: 0, medium: 0, hard: 0, total: 0 },
        3: { easy: 0, medium: 0, hard: 0, total: 0 },
        4: { easy: 0, medium: 0, hard: 0, total: 0 }
    };

    function updateSummary(task) {
        const summaryContent = document.getElementById('summary-content');
        let html = '';

        if (task === 'all') {
            html = `<p><strong>Total Soal:</strong> ${questionCounts.total || 0}</p>`;
            [1, 2, 3, 4].forEach(taskNum => {
                if (questionCounts[taskNum] && questionCounts[taskNum].total > 0) {
                    html += `
                        <p><strong>Tugas ${taskNum}:</strong>
                            ${questionCounts[taskNum].easy} Mudah,
                            ${questionCounts[taskNum].medium} Sedang,
                            ${questionCounts[taskNum].hard} Sulit,
                            Total: ${questionCounts[taskNum].total}
                        </p>
                    `;
                }
            });
            if (html === `<p><strong>Total Soal:</strong> ${questionCounts.total || 0}</p>`) {
                html += '<p>Tidak ada soal untuk tugas 1-4.</p>';
            }
        } else {
            if (questionCounts[task] && questionCounts[task].total > 0) {
                html = `
                    <p><strong>Tugas ${task}:</strong>
                        ${questionCounts[task].easy} Mudah,
                        ${questionCounts[task].medium} Sedang,
                        ${questionCounts[task].hard} Sulit,
                        Total: ${questionCounts[task].total}
                    </p>
                `;
            } else {
                html = `<p>Tidak ada soal untuk Tugas ${task}.</p>`;
            }
        }

        summaryContent.innerHTML = html;
    }

    function filterQuestions(task) {
        Swal.fire({
            title: 'Memuat Soal',
            text: `Menampilkan soal untuk ${task === 'all' ? 'Semua Bank Soal' : 'Tugas ' + task}...`,
            icon: 'info',
            timer: 1000,
            showConfirmButton: false
        });

        const questionList = document.getElementById('questionList');
        const taskGroups = document.querySelectorAll('.task-group');

        document.querySelectorAll('.btn-all-soal, .btn-primary').forEach(btn => btn.classList.remove('btn-active'));
        const activeBtn = document.getElementById(`btn-${task}`);
        if (activeBtn) activeBtn.classList.add('btn-active');

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

        const visibleGroups = Array.from(taskGroups).filter(group => group.style.display !== 'none');
        if (visibleGroups.length === 0 && task !== 'all') {
            questionList.innerHTML = '<p class="text-gray-500 text-sm">Tidak ada soal tersedia untuk tugas ini.</p>';
        } else if (task === 'all' && !questionList.querySelector('.task-group')) {
            questionList.innerHTML = '<p class="text-gray-500 text-sm">Tidak ada soal tersedia untuk mata kuliah ini.</p>';
        }

        updateSummary(task);
    }

    const addQuestionModal = document.getElementById('addQuestionModal');
    const importExcelModal = document.getElementById('importExcelModal');
    const addQuestionBtn = document.getElementById('addQuestionBtn');
    const importExcelBtn = document.getElementById('importExcelBtn');
    const closeModalButtons = document.querySelectorAll('.modal-close');

    addQuestionBtn.addEventListener('click', () => {
        addQuestionModal.classList.add('active');
    });

    importExcelBtn.addEventListener('click', () => {
        importExcelModal.classList.add('active');
    });

    closeModalButtons.forEach(button => {
        button.addEventListener('click', () => {
            addQuestionModal.classList.remove('active');
            importExcelModal.classList.remove('active');
        });
    });

    window.addEventListener('click', (e) => {
        if (e.target === addQuestionModal) {
            addQuestionModal.classList.remove('active');
        }
        if (e.target === importExcelModal) {
            importExcelModal.classList.remove('active');
        }
    });

    document.getElementById('questionForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;

        const questionText = form.querySelector('#question_text').value.trim();
        const optionA = form.querySelector('#option_a').value.trim();
        const optionB = form.querySelector('#option_b').value.trim();
        const optionC = form.querySelector('#option_c').value.trim();
        const optionD = form.querySelector('#option_d').value.trim();
        const correctOption = form.querySelector('#correct_option').value;
        const difficulty = form.querySelector('#difficulty').value;
        const taskNumber = form.querySelector('#task_number').value;

        if (!questionText || !optionA || !optionB || !optionC || !optionD || !correctOption || !difficulty || !taskNumber) {
            Swal.fire({
                title: 'Error!',
                text: 'Semua kolom wajib diisi kecuali gambar!',
                icon: 'error',
                confirmButtonColor: '#0d9488'
            });
            return;
        }

        Swal.fire({
            title: 'Konfirmasi',
            text: 'Apakah Anda yakin ingin menambahkan soal ini?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0d9488',
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
                    .then(response => response.json())
                    .then(data => {
                        if (data.message) {
                            Swal.fire({
                                title: 'Berhasil!',
                                text: data.message,
                                icon: 'success',
                                confirmButtonColor: '#0d9488'
                            }).then(() => {
                                form.reset();
                                addQuestionModal.classList.remove('active');
                                window.location.reload();
                            });
                        } else {
                            throw new Error(data.error || 'Gagal menyimpan soal');
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Gagal menambahkan soal: ' + error.message,
                            icon: 'error',
                            confirmButtonColor: '#0d9488'
                        });
                    });
            }
        });
    });

    document.getElementById('importExcelForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;

        const taskNumber = form.querySelector('#import_task_number').value;
        const excelFile = form.querySelector('#excel_file').files[0];

        if (!taskNumber || !excelFile) {
            Swal.fire({
                title: 'Error!',
                text: 'Pilih nomor tugas dan file CSV!',
                icon: 'error',
                confirmButtonColor: '#0d9488'
            });
            return;
        }

        if (excelFile.type !== 'text/csv') {
            Swal.fire({
                title: 'Error!',
                text: 'File harus berformat CSV!',
                icon: 'error',
                confirmButtonColor: '#0d9488'
            });
            return;
        }

        Swal.fire({
            title: 'Konfirmasi',
            text: 'Apakah Anda yakin ingin mengimpor soal dari file ini?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0d9488',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Impor',
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
                    .then(response => response.json())
                    .then(data => {
                        if (data.message) {
                            Swal.fire({
                                title: 'Berhasil!',
                                text: data.message,
                                icon: 'success',
                                confirmButtonColor: '#0d9488'
                            }).then(() => {
                                form.reset();
                                importExcelModal.classList.remove('active');
                                window.location.reload();
                            });
                        } else {
                            throw new Error(data.error || 'Gagal mengimpor soal');
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Gagal mengimpor soal: ' + error.message,
                            icon: 'error',
                            confirmButtonColor: '#0d9488'
                        });
                    });
            }
        });
    });

    filterQuestions('all');

    @if(session('success'))
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: 'Berhasil!',
                text: '{{ session('success') }}',
                icon: 'success',
                confirmButtonColor: '#0d9488',
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
                confirmButtonColor: '#0d9488',
                confirmButtonText: 'OK'
            });
        });
    @endif
</script>
@endpush
@endsection
