<?php
use Illuminate\Support\Facades\Session;
?>
@extends('layouts.app')

@section('title', 'Tugas Mahasiswa - ' . ($course->course_name ?? 'Course'))

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<style>
    /* Reused container styles from banksoal.blade.php */
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
    /* Reused button styles */
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
    /* New table styles */
    .task-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        background: #f9fafb;
        border-radius: 0.5rem;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        margin-bottom: 1rem;
    }
    .task-table th, .task-table td {
        padding: 0.75rem;
        text-align: left;
        font-size: 0.9rem;
        color: #1f2937;
    }
    .task-table th {
        background: #0d9488;
        color: white;
        font-weight: 600;
        border-bottom: 2px solid #e5e7eb;
    }
    .task-table td {
        border-bottom: 1px solid #e5e7eb;
    }
    .task-table tr:last-child td {
        border-bottom: none;
    }
    .task-table tr:hover {
        background: #f1f5f9;
    }
    .summary-card {
        background: #f9fafb;
        padding: 1rem;
        border-radius: 0.5rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        margin-bottom: 1rem;
    }
    .summary-card h3 {
        font-size: 1rem;
        font-weight: 600;
        color: #111827;
        margin-bottom: 0.5rem;
    }
    .summary-card p {
        font-size: 0.9rem;
        color: #1f2937;
        margin: 0.25rem 0;
    }
    .no-data {
        font-size: 0.9rem;
        color: #4b5563;
        text-align: center;
        padding: 1rem;
    }
    /* Responsive adjustments */
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
        .task-table th, .task-table td {
            font-size: 0.85rem;
            padding: 0.5rem;
        }
        .summary-card h3, .summary-card p {
            font-size: 0.85rem;
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
        .task-table {
            display: block;
            overflow-x: auto;
        }
        .task-table th, .task-table td {
            font-size: 0.8rem;
            padding: 0.4rem;
        }
        .summary-card h3, .summary-card p {
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
                Tugas Mahasiswa {{ $course->course_code ?? '' }} {{ $course->course_name ?? 'Unnamed Course' }}
            </h2>
            <button id="btn-average" onclick="showAverage()" class="btn btn-info btn-all-soal">Rata-Rata Mahasiswa</button>
            <button id="btn-1" onclick="showTask(1)" class="btn btn-primary">Tugas 1</button>
            <button id="btn-2" onclick="showTask(2)" class="btn btn-primary">Tugas 2</button>
            <button id="btn-3" onclick="showTask(3)" class="btn btn-primary">Tugas 3</button>
            <button id="btn-4" onclick="showTask(4)" class="btn btn-primary">Tugas 4</button>
            <button id="btn-5" onclick="showTask(5)" class="btn btn-primary">Tugas Tambahan</button>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <h1 class="text-xl font-semibold mb-4 text-gray-800">Tugas Mahasiswa</h1>
            <div id="content-area">
                <!-- Average Scores (default) -->
                <div id="average-content" class="content-section">
                    <div class="summary-card">
                        <h3>Rata-Rata Nilai Mahasiswa</h3>
                        @if ($averageScores->isEmpty())
                        <p class="no-data">Belum ada mahasiswa yang mengerjakan tugas.</p>
                        @else
                        <table class="task-table">
                            <thead>
                            <tr>
                                <th>Nama (NIM)</th>
                                <th>Rata-Rata Nilai</th>
                                <th>Tugas Dikerjakan</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($averageScores as $student)
                            <tr>
                                <td>{{ $student->name }} ({{ $student->nim }})</td>
                                <td>{{ number_format($student->average_score, 2) }}</td>
                                <td>{{ $student->tasks_completed }}</td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                        @endif
                    </div>
                </div>
                <!-- Task Details -->
                @foreach ([1, 2, 3, 4, 5] as $taskNumber)
                <div id="task-{{ $taskNumber }}-content" class="content-section" style="display: none;">
                    <div class="summary-card">
                        <h3>Detail Tugas {{ $taskNumber == 5 ? 'Tambahan' : $taskNumber }}</h3>
                        @if ($taskDetails[$taskNumber]->isEmpty())
                        <p class="no-data">Belum ada mahasiswa yang mengerjakan tugas ini.</p>
                        @else
                        <table class="task-table">
                            <thead>
                            <tr>
                                <th>Nama (NIM)</th>
                                <th>Nilai</th>
                                <th>Kesalahan Easy</th>
                                <th>Kesalahan Medium</th>
                                <th>Kesalahan Hard</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($taskDetails[$taskNumber] as $attempt)
                            <tr>
                                <td>{{ $attempt->student->name }} ({{ $attempt->student->nim }})</td>
                                <td>{{ $attempt->score }}</td>
                                <td>{{ $attempt->errors_easy }}</td>
                                <td>{{ $attempt->errors_medium }}</td>
                                <td>{{ $attempt->errors_hard }}</td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function showAverage() {
        Swal.fire({
            title: 'Memuat Data',
            text: 'Menampilkan rata-rata nilai mahasiswa...',
            icon: 'info',
            timer: 1000,
            showConfirmButton: false
        });

        document.querySelectorAll('.content-section').forEach(section => section.style.display = 'none');
        document.getElementById('average-content').style.display = 'block';

        document.querySelectorAll('.btn').forEach(btn => btn.classList.remove('btn-active'));
        document.getElementById('btn-average').classList.add('btn-active');
    }

    function showTask(taskNumber) {
        Swal.fire({
            title: 'Memuat Data',
            text: `Menampilkan detail Tugas ${taskNumber == 5 ? 'Tambahan' : taskNumber}...`,
            icon: 'info',
            timer: 1000,
            showConfirmButton: false
        });

        document.querySelectorAll('.content-section').forEach(section => section.style.display = 'none');
        document.getElementById(`task-${taskNumber}-content`).style.display = 'block';

        document.querySelectorAll('.btn').forEach(btn => btn.classList.remove('btn-active'));
        document.getElementById(`btn-${taskNumber}`).classList.add('btn-active');
    }

    // Show average scores by default
    document.addEventListener('DOMContentLoaded', function() {
        showAverage();
    });

    @if(Session::has('success'))
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Berhasil!',
                text: '{{ Session::get('success') }}',
                icon: 'success',
                confirmButtonColor: '#0d9488'
            });
        });
    @endif

    @if(Session::has('error'))
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Error!',
                text: '{{ Session::get('error') }}',
                icon: 'error',
                confirmButtonColor: '#0d9488'
            });
        });
    @endif
</script>
@endpush
@endsection
