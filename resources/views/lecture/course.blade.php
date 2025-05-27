@extends('layouts.app')

@section('title', $course->course_name ?? 'Course Page')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
    .loading-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 9999;
        justify-content: center;
        align-items: center;
        flex-direction: column;
        gap: 1rem;
    }
    .loading-spinner {
        border: 4px solid #f3f3f3;
        border-top: 4px solid #106587;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        animation: spin 0.8s linear infinite;
    }
    .progress-bar-container {
        width: 80%;
        max-width: 400px;
        background: #e5e7eb;
        border-radius: 0.5rem;
        overflow: hidden;
    }
    .progress-bar {
        height: 1rem;
        background: #106587;
        width: 0%;
        transition: width 0.3s ease;
    }
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    .file-upload-container {
        background: #f9fafb;
        padding: 0.75rem;
        border-radius: 0.5rem;
        border: 1px solid #d1d5db;
        transition: border-color 0.3s ease;
    }
    .file-upload-container:hover {
        border-color: #106587;
    }
    .file-upload-label {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.75rem;
        background: white;
        border: 2px dashed #106587;
        color: #106587;
        border-radius: 0.5rem;
        cursor: pointer;
        transition: background 0.3s ease, color 0.3s ease;
    }
    .file-upload-label:hover {
        background: #106587;
        color: white;
    }
    .file-upload-input {
        display: none;
    }
    .material-card {
        transition: transform 0.2s ease;
        border-radius: 0.5rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    .material-card:hover {
        transform: translateY(-2px);
    }
    .action-btn {
        color: #106587;
        text-decoration: underline;
        transition: color 0.3s ease;
    }
    .action-btn:hover {
        color: #0d4a6b;
    }
    .delete-btn {
        color: #dc2626;
        text-decoration: none;
        font-size: 0.875rem;
        cursor: pointer;
        margin-left: 0.5rem;
    }
    .delete-btn:hover {
        color: #b91c1c;
    }
    .container {
        max-width: 100%;
        margin: 0 auto;
        padding: 1.5rem;
        box-sizing: border-box;
        padding-bottom: 5rem;
    }
    .dashboard-container {
        display: flex;
        gap: 2rem;
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
        justify-content: space-between;
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
    .btn-success {
        background: #16a34a;
        color: white;
    }
    .btn-success:hover {
        background: #15803d;
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
    input, textarea {
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        padding: 0.5rem;
        width: 100%;
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
        font-size: 0.875rem;
    }
    input:focus, textarea:focus {
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
    .checkbox-container {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 0.5rem;
    }
    .checkbox-container input {
        width: 1.25rem;
        height: 1.25rem;
        border: 2px solid #106587;
        border-radius: 0.25rem;
        cursor: pointer;
        transition: background 0.3s ease;
    }
    .checkbox-container input:checked {
        background: #106587;
        border-color: #106587;
    }
    .file-preview {
        margin-top: 0.5rem;
        padding: 0.5rem;
        background: #f1f5f9;
        border-radius: 0.5rem;
    }
    .file-preview-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.25rem 0;
        font-size: 0.875rem;
        color: #374151;
    }
    .file-preview-item button {
        color: #dc2626;
        font-size: 0.875rem;
        cursor: pointer;
        background: none;
        border: none;
        padding: 0;
    }
    .participant-card {
        background: #f9fafb;
        padding: 0.75rem;
        border-radius: 0.5rem;
        margin-bottom: 0.5rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }
    .participant-card p {
        margin: 0;
        font-size: 0.875rem;
        color: #374151;
    }
    .participants-section {
        display: none;
        margin-top: 1rem;
        max-height: 300px;
        overflow-y: auto;
    }
    .participants-section.active {
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
        .dashboard-container {
            padding: 0.5rem;
        }
        .sidebar, .main-content {
            padding: 0.75rem;
        }
        .file-upload-container {
            padding: 0.5rem;
        }
        .file-upload-label {
            padding: 0.5rem;
            font-size: 0.85rem;
        }
        .btn {
            padding: 0.4rem 0.8rem;
            font-size: 0.85rem;
        }
        input, textarea {
            padding: 0.4rem;
            font-size: 0.85rem;
        }
        label {
            font-size: 0.8rem;
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
        .file-upload-container {
            padding: 0.4rem;
        }
        .file-upload-label {
            padding: 0.4rem;
            font-size: 0.8rem;
            gap: 0.3rem;
        }
        .file-upload-label svg {
            width: 1rem;
            height: 1rem;
        }
        .btn {
            padding: 0.3rem 0.7rem;
            font-size: 0.8rem;
        }
        input, textarea {
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
            <div>
                <div class="flex justify-between items-center mb-4">
                    <a href="#" class="p-2 rounded-full bg-gray-100 hover:bg-gray-200 transition" onclick="confirmBack(event)">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                    <div class="px-3 py-1 bg-[#106587] text-white rounded-lg text-sm font-semibold">
                        {{ $formattedCourseCode ?? 'Unknown Course' }}
                    </div>
                </div>
                <h2 class="text-lg font-semibold text-gray-700 text-center mb-4">
                    {{ $course->course_name ?? 'Unnamed Course' }}
                </h2>
                <a href="{{ route('lecture.banksoal', ['courseCode' => $courseCodeWithoutDash]) }}" class="btn btn-info">Bank Soal</a>
            </div>
            <div>
                <button onclick="toggleParticipants()" class="btn btn-primary">Lihat Peserta</button>
                <div id="participantsSection" class="participants-section">
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">Peserta Mata Kuliah</h3>
                    <p class="text-sm text-gray-600 mb-2">Total: {{ $totalParticipants }} ({{ $lecturerCount }} Dosen, {{ $studentCount }} Mahasiswa)</p>
                    @foreach ($assignments as $assignment)
                    @if ($assignment->lecturer)
                    <div class="participant-card">
                        <p><strong>Dosen:</strong> {{ $assignment->lecturer->name }}</p>
                    </div>
                    @endif
                    @endforeach
                    @foreach ($assignments as $assignment)
                    @if ($assignment->student)
                    <div class="participant-card">
                        <p>{{ $assignment->student->name }} ({{ $assignment->student->nim }})</p>
                    </div>
                    @endif
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <h1 class="text-xl font-semibold mb-4 text-gray-800">Materi Kursus</h1>
            <div class="space-y-4">
                @foreach ($materials as $week => $material)
                @php
                $quizWeek = in_array($week + 1, [4, 7, 10, 14]) ? ($week + 1) : null;
                $quiz = $quizWeek ? ($quizzes[$quizWeek] ?? null) : null;
                $taskNumber = $quizWeek ? (int)($quizWeek / 3.5) : null;
                @endphp

                <div class="p-4 bg-gray-50 rounded-xl border border-gray-200 material-card">
                    <h4 class="text-lg font-semibold text-gray-700 mb-2">Week {{ $week + 1 }}</h4>
                    <p class="text-gray-600 text-sm mb-2">Week {{ $week + 1 }}</p>

                    <!-- Form Upload Materi -->
                    <form action="{{ route('lecturer.course.material.store', ['courseCode' => $courseCodeWithoutDash]) }}" method="POST" enctype="multipart/form-data" class="space-y-3" id="material-form-{{ $week + 1 }}" onsubmit="showLoading(event, this)">
                        @csrf
                        <input type="hidden" name="week" value="{{ $week + 1 }}">
                        <div class="file-upload-container">
                            <label for="file-upload-{{ $week + 1 }}" class="file-upload-label">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4-4m0 0l-4 4m4-4v12" />
                                </svg>
                                <span class="text-sm">Choose files to upload</span>
                                <input id="file-upload-{{ $week + 1 }}" type="file" name="files[]" multiple class="file-upload-input" onchange="previewFiles(event, {{ $week + 1 }})">
                            </label>
                            <p class="mt-2 text-xs text-gray-600">Any file type allowed</p>
                            <div class="file-preview" id="file-preview-{{ $week + 1 }}"></div>
                        </div>
                        <div>
                            <label class="text-gray-700 font-semibold text-sm">Video URL</label>
                            <input type="url" name="video_url" value="{{ $material['video_url'] }}" placeholder="https://example.com/video" class="w-full" oninput="markFormDirty({{ $week + 1 }})">
                        </div>
                        <div class="checkbox-container">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_optional" {{ $material['optional'] ? 'checked' : '' }} onchange="markFormDirty({{ $week + 1 }})">
                                <span class="text-gray-700 text-sm ml-2">Optional</span>
                            </label>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Material</button>
                    </form>

                    <!-- Tautan Files -->
                    @if (!empty($material['files']))
                    @foreach ($material['files'] as $index => $file)
                    <div class="flex items-center">
                        <a href="{{ asset($file) }}" target="_blank" class="action-btn text-sm">
                            Open {{ strtoupper(pathinfo($file, PATHINFO_EXTENSION)) }} File
                        </a>
                        <form action="{{ route('lecturer.course.material.delete', ['courseCode' => $courseCodeWithoutDash, 'week' => $week + 1, 'index' => $index]) }}" method="POST" style="display:inline;" onsubmit="return confirmDelete(event)">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="delete-btn">Delete</button>
                        </form>
                    </div>
                    @endforeach
                    @else
                    <p class="text-gray-500 text-sm">No materials available</p>
                    @endif

                    <!-- Video URL -->
                    @if (!empty($material['video_url']))
                    <a href="{{ $material['video_url'] }}" target="_blank" class="action-btn text-sm block">
                        Open Video
                    </a>
                    @else
                    <p class="text-gray-500 text-sm">No video available</p>
                    @endif

                    @if (!empty($material['optional']))
                    <p class="text-gray-500 text-sm">(Optional)</p>
                    @endif

                    <!-- Bank Soal untuk Tugas -->
                    @if ($quiz)
                    <div class="p-3 bg-gray-100 rounded-lg mt-3">
                        <p class="text-gray-700 font-semibold text-sm">{{ $quiz['title'] }}</p>
                        <p class="text-gray-600 text-sm">Total Questions: {{ $quiz['total_questions'] }}</p>
                        <p class="text-gray-600 text-sm">Easy: {{ $quiz['easy_questions'] }}</p>
                        <p class="text-gray-600 text-sm">Medium: {{ $quiz['medium_questions'] }}</p>
                        <p class="text-gray-600 text-sm">Hard: {{ $quiz['hard_questions'] }}</p>
                    </div>
                    @endif

                    <!-- Form Buat Tugas -->
                    @if ($quizWeek && !$quiz)
                    <form id="create-task-form-{{ $week + 1 }}" action="{{ route('lecturer.course.quiz.create', ['courseCode' => $courseCodeWithoutDash]) }}" method="POST" class="mt-3">
                        @csrf
                        <input type="hidden" name="week" value="{{ $week + 1 }}">
                        <input type="hidden" name="title" id="task-title-{{ $week + 1 }}">
                        <button type="button" onclick="confirmCreateTask({{ $taskNumber }}, {{ $week + 1 }})" class="btn btn-success">
                            Create Task
                        </button>
                    </form>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
        <div class="progress-bar-container">
            <div class="progress-bar" id="progressBar"></div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    let formDirtyStates = {};
    let filePreviews = {};

    function toggleParticipants() {
        const section = document.getElementById('participantsSection');
        section.classList.toggle('active');
    }

    function markFormDirty(week) {
        formDirtyStates[week] = true;
    }

    function previewFiles(event, week) {
        markFormDirty(week);
        const files = event.target.files;
        const previewContainer = document.getElementById(`file-preview-${week}`);
        previewContainer.innerHTML = '';

        if (!filePreviews[week]) {
            filePreviews[week] = [];
        }

        for (let file of files) {
            filePreviews[week].push(file);
        }

        filePreviews[week].forEach((file, index) => {
            const div = document.createElement('div');
            div.className = 'file-preview-item';
            div.innerHTML = `
                <span>${file.name}</span>
                <button type="button" onclick="removeFile(${week}, ${index})">Remove</button>
            `;
            previewContainer.appendChild(div);
        });
    }

    function removeFile(week, index) {
        filePreviews[week].splice(index, 1);
        const previewContainer = document.getElementById(`file-preview-${week}`);
        previewContainer.innerHTML = '';

        filePreviews[week].forEach((file, newIndex) => {
            const div = document.createElement('div');
            div.className = 'file-preview-item';
            div.innerHTML = `
                <span>${file.name}</span>
                <button type="button" onclick="removeFile(${week}, ${newIndex})">Remove</button>
            `;
            previewContainer.appendChild(div);
        });

        const input = document.getElementById(`file-upload-${week}`);
        const dataTransfer = new DataTransfer();
        filePreviews[week].forEach(file => dataTransfer.items.add(file));
        input.files = dataTransfer.files;
    }

    function showLoading(event, form) {
        event.preventDefault();

        const week = form.querySelector('input[name="week"]').value;
        const filesInput = form.querySelector('input[type="file"]');
        const videoUrlInput = form.querySelector('input[name="video_url"]');
        const isOptionalInput = form.querySelector('input[name="is_optional"]');

        // Validate input
        if (!filesInput.files.length && !videoUrlInput.value && !isOptionalInput.checked && !formDirtyStates[week]) {
            Swal.fire({
                title: 'Informasi',
                text: 'Silakan pilih file, masukkan URL video, atau ubah status opsional.',
                icon: 'info',
                confirmButtonColor: '#106587',
                confirmButtonText: 'OK'
            });
            return;
        }

        const overlay = document.getElementById('loadingOverlay');
        const progressBar = document.getElementById('progressBar');
        overlay.style.display = 'flex';
        progressBar.style.width = '0%';

        // Use Fetch API instead of XMLHttpRequest for better error handling
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
                    return response.json().then(err => { throw err; });
                }
                return response.json();
            })
            .then(data => {
                overlay.style.display = 'none';
                Swal.fire({
                    title: 'Berhasil!',
                    text: data.message || 'Materi berhasil diunggah!',
                    icon: 'success',
                    confirmButtonColor: '#106587'
                }).then(() => {
                    window.location.reload();
                });
            })
            .catch(error => {
                overlay.style.display = 'none';
                Swal.fire({
                    title: 'Error!',
                    text: error.message || 'Gagal mengunggah materi.',
                    icon: 'error',
                    confirmButtonColor: '#106587'
                });
            });
    }

    function confirmBack(event) {
        event.preventDefault();
        const anyFormDirty = Object.values(formDirtyStates).some(dirty => dirty);
        if (anyFormDirty) {
            Swal.fire({
                title: 'Konfirmasi',
                text: 'Apakah Anda yakin ingin keluar? Anda belum menyimpan perubahan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#106587',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Keluar',
                cancelButtonText: 'Simpan'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "{{ url('/lecturer/dashboard') }}";
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    Swal.fire({
                        title: 'Simpan Perubahan',
                        text: 'Apakah Anda ingin menyimpan semua perubahan sebelum keluar?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#106587',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Simpan',
                        cancelButtonText: 'Batal'
                    }).then((saveResult) => {
                        if (saveResult.isConfirmed) {
                            const dirtyForms = document.querySelectorAll('form[id^="material-form-"]');
                            let submitted = 0;
                            dirtyForms.forEach((form, index) => {
                                if (formDirtyStates[form.querySelector('input[name="week"]').value]) {
                                    setTimeout(() => {
                                        showLoading(new Event('submit'), form);
                                    }, index * 1000);
                                    submitted++;
                                }
                            });
                            if (submitted === 0) {
                                window.location.href = "{{ url('/lecturer/dashboard') }}";
                            }
                        }
                    });
                }
            });
        } else {
            window.location.href = "{{ url('/lecturer/dashboard') }}";
        }
    }

    function confirmCreateTask(taskNumber, week) {
        Swal.fire({
            title: 'Konfirmasi',
            text: `Apakah Anda yakin ingin membuat Tugas ${taskNumber}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#106587',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yakin',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    text: 'Masukkan nama judul materi.',
                    input: 'text',
                    inputPlaceholder: 'Contoh: Dasar HTML',
                    inputAttributes: { autocapitalize: 'off' },
                    showCancelButton: true,
                    confirmButtonColor: '#106587',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Simpan',
                    cancelButtonText: 'Cancel',
                    inputValidator: (value) => {
                        if (!value) return 'Judul tugas tidak boleh kosong!';
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById(`task-title-${week}`).value = `Tugas ${taskNumber}, ${result.value}`;
                        document.getElementById(`create-task-form-${week}`).submit();
                    }
                });
            }
        });
    }

    function confirmDelete(event) {
        event.preventDefault();
        Swal.fire({
            title: 'Konfirmasi',
            text: 'Apakah Anda yakin ingin menghapus file ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#106587',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Hapus',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                event.target.closest('form').submit();
            }
        });
        return false;
    }

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

    @if(session('material_uploaded'))
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: 'Berhasil!',
                text: '{{ session('material_uploaded') }}',
                icon: 'success',
                confirmButtonColor: '#106587',
                confirmButtonText: 'OK'
            });
        });
    @endif
</script>
@endpush
@endsection
