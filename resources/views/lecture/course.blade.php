@extends('layouts.app')

@section('title', $course->course_name ?? 'Course Page')

@section('content')
<div class="flex justify-center items-start min-h-[80vh] space-x-6 p-4">
    <!-- Sidebar -->
    <div class="w-1/5 bg-white shadow-lg rounded-2xl min-h-[700px] max-h-[700px] p-4 sticky top-4 h-fit flex flex-col">
        <div class="flex justify-between w-full items-center">
            <!-- Tombol Back -->
            <a href="{{ url('/lecturer/dashboard') }}" class="p-3 rounded-full transition shadow-md hover:shadow-lg" data-no-prevent>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-800" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <!-- Kode Course -->
            <div class="px-4 py-2 bg-[#234e7f] text-white rounded-lg text-sm font-semibold">
                {{ $formattedCourseCode ?? 'Unknown Course' }}
            </div>
        </div>

        <!-- Nama Course -->
        <h2 class="text-lg font-semibold text-gray-800 text-center mt-4">{{ $course->course_name ?? 'Unnamed Course' }}</h2>
    </div>

    <!-- Konten Utama -->
    <div class="w-4/5 bg-white shadow-lg rounded-2xl p-8 overflow-y-auto min-h-[700px] max-h-[700px]">
        <h2 class="text-xl font-semibold mb-4">Materi Kursus</h2>
        <div class="space-y-6">
            @foreach ($materials as $week => $material)
            @php
            $quizWeek = in_array($week + 1, [4, 7, 10, 14]) ? ($week + 1) : null;
            $quiz = $quizWeek ? ($quizzes[$quizWeek] ?? null) : null;
            $taskNumber = $quizWeek ? (int) ($quizWeek / 3.5) : null; // 4->1, 7->2, 10->3, 14->4
            @endphp

            <div class="p-6 bg-white rounded-xl shadow-md flex flex-col space-y-3 border border-gray-200">
                <h4 class="text-lg font-semibold text-gray-800">Week {{ $week + 1 }}</h4>
                <p class="text-gray-600">Materi Week {{ $week + 1 }}</p>

                <!-- Form Upload Materi -->
                <form action="{{ route('lecturer.course.material.store', ['courseCode' => $courseCodeWithoutDash]) }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    <input type="hidden" name="week" value="{{ $week + 1 }}">
                    <div>
                        <label class="text-gray-700 font-semibold">Upload PDF</label>
                        <input type="file" name="pdf" accept="application/pdf" class="w-full p-2 border rounded-lg">
                    </div>
                    <div>
                        <label class="text-gray-700 font-semibold">Video URL</label>
                        <input type="url" name="video_url" value="{{ $material['video_url'] }}" placeholder="https://example.com/video" class="w-full p-2 border rounded-lg">
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" name="is_optional" {{ $material['optional'] ? 'checked' : '' }} class="mr-2">
                            <span class="text-gray-700">Optional</span>
                        </label>
                    </div>
                    <button type="submit" class="px-4 py-2 bg-[#234e7f] text-white rounded-lg hover:bg-blue-700">Save Material</button>
                </form>

                <!-- Tautan PDF -->
                @if (!empty($material['pdf']))
                <a href="{{ asset($material['pdf']) }}" target="_blank" class="text-blue-600 font-semibold hover:underline">
                    Open PDF
                </a>
                @else
                <p class="text-gray-500">Materi tidak tersedia</p>
                @endif

                <!-- Video URL -->
                @if (!empty($material['video_url']))
                <a href="{{ $material['video_url'] }}" target="_blank" class="text-blue-600 font-semibold hover:underline">
                    Open Video
                </a>
                @else
                <p class="text-gray-500">Video tidak tersedia</p>
                @endif

                @if (!empty($material['optional']))
                <p class="text-gray-500">(Optional)</p>
                @endif

                <!-- Bank Soal untuk Tugas -->
                @if ($quiz)
                <div class="p-4 bg-gray-100 rounded-lg shadow-inner">
                    <p class="text-gray-700 font-semibold">{{ $quiz['title'] }}</p>
                    <p class="text-gray-600">Total Soal: {{ $quiz['total_questions'] }}</p>
                    <p class="text-gray-600">Easy: {{ $quiz['easy_questions'] }}</p>
                    <p class="text-gray-600">Medium: {{ $quiz['medium_questions'] }}</p>
                    <p class="text-gray-600">Hard: {{ $quiz['hard_questions'] }}</p>
                </div>
                @endif

                <!-- Form Buat Tugas -->
                @if ($quizWeek && !$quiz)
                <form id="create-task-form-{{ $week + 1 }}" action="{{ route('lecturer.course.quiz.create', ['courseCode' => $courseCodeWithoutDash]) }}" method="POST" class="mt-3">
                    @csrf
                    <input type="hidden" name="week" value="{{ $week + 1 }}">
                    <input type="hidden" name="title" id="task-title-{{ $week + 1 }}">
                    <button type="button" onclick="confirmCreateTask({{ $taskNumber }}, {{ $week + 1 }})" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        Buat Tugas
                    </button>
                </form>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- SweetAlert untuk Notifikasi dan Konfirmasi -->
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function confirmCreateTask(taskNumber, week) {
        Swal.fire({
            title: 'Konfirmasi',
            text: `Apakah yakin ingin membuat Tugas ${taskNumber}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#234e7f',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yakin',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Masukkan Nama Materi Tugas',
                    input: 'text',
                    inputPlaceholder: 'Contoh: HTML Dasar',
                    inputAttributes: {
                        autocapitalize: 'off'
                    },
                    showCancelButton: true,
                    confirmButtonColor: '#234e7f',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Simpan',
                    cancelButtonText: 'Batal',
                    inputValidator: (value) => {
                        if (!value) {
                            return 'Judul tugas tidak boleh kosong!';
                        }
                    }
                }).then((inputResult) => {
                    if (inputResult.isConfirmed) {
                        const titleInput = document.getElementById(`task-title-${week}`);
                        titleInput.value = `Tugas ${taskNumber}, ${inputResult.value}`;
                        document.getElementById(`create-task-form-${week}`).submit();
                    }
                });
            }
        });
    }
</script>
@if(session('success'))
<script>
    document.addEventListener("DOMContentLoaded", function() {
        Swal.fire({
            title: 'Success!',
            text: '{{ session('success') }}',
            icon: 'success',
            confirmButtonColor: '#234e7f',
            confirmButtonText: 'OK'
        });
    });
</script>
@endif
@if(session('error'))
<script>
    document.addEventListener("DOMContentLoaded", function() {
        Swal.fire({
            title: 'Oops!',
            text: '{{ session('error') }}',
            icon: 'error',
            confirmButtonColor: '#234e7f',
            confirmButtonText: 'OK'
        });
    });
</script>
@endif
@endpush
@endsection
