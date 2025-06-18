@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

<style>
    .tab-button {
        flex: 1;
        text-align: center;
        padding: 10px;
        cursor: pointer;
        font-weight: bold;
        border-bottom: 2px solid transparent;
        transition: all 0.3s ease;
        position: relative;
    }
    .tab-button.active {
        border-bottom: 2px solid #106587;
        color: #106587;
    }
    .dashboard-container {
        display: flex;
        gap: 2rem;
        width: 100%;
        max-width: 80%;
        margin: 0 auto;
        padding: 1rem 0;
        justify-content: center;
        align-items: flex-start;
        box-sizing: border-box;
    }
    .sidebar {
        flex: 2;
        padding: 2rem;
        min-width: 0;
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    .main-content {
        flex: 8;
        padding: 2rem;
        min-width: 0;
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    .profile-card img {
        object-fit: cover;
        border-radius: 9999px;
    }
    .edit-profile-btn {
        background: #106587;
        padding: 0.75rem 1.5rem;
        border-radius: 0.5rem;
        font-size: 1rem;
        font-weight: 500;
        transition: background 0.3s ease;
        display: inline-block;
    }
    .edit-profile-btn:hover {
        background: #0d4a6b;
    }
    .course-card, .timeline-card {
        background: white;
        padding: 1rem;
        border-radius: 0.75rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .course-card:hover, .timeline-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
    }
    #dropdownToggle {
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        transition: background 0.3s ease;
    }
    #dropdownToggle:hover {
        background: #e2e8f0;
    }
    .notification-badge {
        position: absolute;
        top: -8px;
        right: 10px;
        background: #dc2626;
        color: white;
        border-radius: 9999px;
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: bold;
    }
    .swal2-popup {
        font-size: 1rem !important;
        padding: 1rem !important;
    }
    .swal2-title {
        font-size: 1.25rem !important;
    }
    .swal2-content {
        font-size: 0.875rem !important;
    }
    .swal2-confirm, .swal2-cancel {
        font-size: 0.875rem !important;
        padding: 0.5rem 1rem !important;
    }
    @media (max-width: 1400px) {
        #cardView {
            grid-template-columns: repeat(2, 1fr);
        }
        #listView ul {
            grid-template-columns: repeat(2, 1fr);
        }
        .sidebar, .main-content {
            flex: 1;
        }
    }
    @media (max-width: 1100px) {
        .dashboard-container {
            flex-direction: column;
            gap: 1.5rem;
            max-width: 100%;
            padding: 1rem;
            align-items: center;
            width: 100vw;
            overflow-x: hidden;
        }
        .sidebar, .main-content {
            flex: none;
            width: 100%;
            max-width: 600px;
            height: auto;
            min-height: 400px;
            padding: 1.5rem;
            margin: 0 auto;
            box-sizing: border-box;
        }
        #cardView, #listView {
            width: 100%;
            max-width: 100%;
        }
        #cardView {
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }
        #cardView img.w-full.h-32 {
            height: 10rem;
        }
        #cardView h3 {
            font-size: 1.1rem;
        }
        #cardView p {
            font-size: 0.85rem;
        }
        .profile-card img.w-32.h-32 {
            width: 8rem;
            height: 8rem;
        }
        .text-2xl {
            font-size: 1.75rem;
        }
        .mt-4.text-center p {
            font-size: 1rem;
        }
        .mt-4.text-center strong {
            font-size: 1.1rem;
        }
        .edit-profile-btn {
            padding: 0.6rem 1.2rem;
            font-size: 0.95rem;
        }
        #listView li {
            padding: 1rem;
        }
        #listView h3 {
            font-size: 1rem;
        }
        #listView p {
            font-size: 0.8rem;
        }
        #dropdownToggle {
            padding: 0.5rem 0.8rem;
            font-size: 0.9rem;
        }
        #dropdownMenu {
            width: 6rem;
        }
        #dropdownMenu button {
            font-size: 0.85rem;
            padding: 0.5rem;
        }
        .tab-button {
            padding: 0.75rem;
            font-size: 0.95rem;
        }
        .notification-badge {
            width: 18px;
            height: 18px;
            font-size: 0.7rem;
        }
    }
    @media (max-width: 768px) {
        .dashboard-container {
            padding: 0.5rem;
            width: 100vw;
            max-width: 100%;
            margin: 0;
        }
        #cardView {
            grid-template-columns: 1fr;
        }
        #listView ul {
            grid-template-columns: 1fr;
        }
        #cardView img.w-full.h-32 {
            height: 9rem;
        }
        #cardView h3 {
            font-size: 1rem;
        }
        #cardView p {
            font-size: 0.8rem;
        }
        .profile-card img.w-32.h-32 {
            width: 7rem;
            height: 7rem;
        }
        .text-2xl {
            font-size: 1.5rem;
        }
        .mt-4.text-center p {
            font-size: 0.95rem;
        }
        .mt-4.text-center strong {
            font-size: 1rem;
        }
        .edit-profile-btn {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
        }
        #listView li {
            padding: 0.8rem;
        }
        #listView h3 {
            font-size: 0.95rem;
        }
        #listView p {
            font-size: 0.75rem;
        }
        #dropdownToggle {
            padding: 0.4rem 0.7rem;
            font-size: 0.85rem;
        }
        #dropdownMenu {
            width: 5.5rem;
        }
        #dropdownMenu button {
            font-size: 0.8rem;
            padding: 0.4rem;
        }
        .tab-button {
            padding: 0.6rem;
            font-size: 0.9rem;
        }
        #content-timeline p {
            font-size: 0.95rem;
        }
        #content-timeline svg {
            width: 2rem;
            height: 2rem;
        }
        .swal2-popup {
            width: 90% !important;
            max-width: 300px !important;
            font-size: 0.9rem !important;
            padding: 0.75rem !important;
        }
        .swal2-title {
            font-size: 1.1rem !important;
        }
        .swal2-content {
            font-size: 0.8rem !important;
        }
        .swal2-confirm, .swal2-cancel {
            font-size: 0.8rem !important;
            padding: 0.4rem 0.8rem !important;
        }
        .notification-badge {
            width: 16px;
            height: 16px;
            font-size: 0.65rem;
        }
    }
    @media (max-width: 480px) {
        .dashboard-container {
            max-width: 100%;
            width: 100vw;
            gap: 1rem;
            padding: 0.5rem;
            margin: 0;
            overflow-x: hidden;
        }
        .sidebar, .main-content {
            padding: 1rem;
            min-height: 350px;
            margin: 0;
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
        }
        .profile-card img.w-32.h-32 {
            width: 6rem;
            height: 6rem;
        }
        .text-2xl {
            font-size: 1.25rem;
        }
        .mt-4.text-center p {
            font-size: 0.9rem;
        }
        .mt-4.text-center strong {
            font-size: 0.95rem;
        }
        .edit-profile-btn {
            padding: 0.5rem 0.8rem;
            font-size: 0.85rem;
        }
        #cardView img.w-full.h-32 {
            height: 8rem;
        }
        #cardView h3 {
            font-size: 0.95rem;
        }
        #cardView p {
            font-size: 0.75rem;
        }
        .swal2-popup {
            width: 85% !important;
            max-width: 280px !important;
            font-size: 0.85rem !important;
            padding: 0.5rem !important;
        }
        .swal2-title {
            font-size: 1rem !important;
        }
        .swal2-content {
            font-size: 0.75rem !important;
        }
        .swal2-confirm, .swal2-cancel {
            font-size: 0.75rem !important;
            padding: 0.3rem 0.7rem !important;
        }
        .notification-badge {
            width: 14px;
            height: 14px;
            font-size: 0.6rem;
        }
    }
    html, body {
        margin: 0;
        padding: 0;
        width: 100%;
        overflow-x: hidden;
    }
</style>

<div class="flex justify-center items-center min-h-[80vh]">
    <div class="flex w-full max-w-[100%] dashboard-container">
        <!-- Sidebar -->
        <div class="flex-[2] bg-white shadow-lg rounded-2xl p-8 h-[600px] overflow-y-auto flex flex-col profile-card sidebar">
            <h2 class="text-2xl font-semibold">Profile</h2>
            @php
            $photoPath = Auth::guard('student')->user()->profile_photo ?? '/images/profile.jpg';
            $isDefault = $photoPath === '/images/profile.jpg';
            @endphp
            <img src="{{ $isDefault ? asset($photoPath) : asset('storage/' . $photoPath) }}"
                 alt="Profile Picture" class="w-32 h-32 rounded-full mx-auto mt-4">
            <p class="mt-4 text-center">
                Name: <strong>{{ Auth::guard('student')->user()->name ?? 'Guest' }}
                    ({{ Auth::guard('student')->user()->nim ?? 'Guest' }})</strong>
            </p>
            <p class="mt-4 text-center">Major: <strong>{{ Auth::guard('student')->user()->major ?? 'Not Set' }}</strong></p>
            <p class="mt-2 text-center">Email: <strong>{{ Auth::guard('student')->user()->email ?? 'Guest' }}</strong></p>
            <p class="mt-2 text-center">Motto: <strong>{{ Auth::guard('student')->user()->motto ?? 'Not Set' }}</strong></p>
            <a href="{{ route('profile.edit') }}" class="mt-8 text-white py-2 px-4 rounded-xl text-center edit-profile-btn">Edit Profile</a>
        </div>

        <!-- Main Content -->
        <div class="flex-[8] bg-white shadow-lg rounded-2xl p-8 h-[600px] overflow-y-auto flex flex-col main-content">
            <div class="flex border-b">
                <button id="tab-course" class="tab-button active">Course List</button>
                <button id="tab-timeline" class="tab-button">
                    Timeline
                    @if ($quizzes->isNotEmpty())
                    <span class="notification-badge">{{ $quizzes->count() }}</span>
                    @endif
                </button>
            </div>
            <div id="content-course" class="tab-content block flex flex-col items-center">
                <!-- Dropdown Toggle -->
                <div class="w-full flex justify-end mb-2 mt-4">
                    <div class="relative">
                        <button id="dropdownToggle" class="bg-gray-50 shadow-md text-gray-700 px-4 py-2 rounded-lg text-sm flex items-center">
                            <span id="dropdownText">Card</span>
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div id="dropdownMenu" class="hidden absolute right-0 mt-2 w-24 bg-white shadow-lg rounded-md text-sm">
                            <button class="w-full px-4 py-2 hover:bg-gray-100 select-view" data-view="card">Card</button>
                            <button class="w-full px-4 py-2 hover:bg-gray-100 select-view" data-view="list">List</button>
                        </div>
                    </div>
                </div>
                <!-- Card View -->
                <div id="cardView" class="grid grid-cols-3 md:grid-cols-2 sm:grid-cols-2 gap-6 max-w-5xl w-full">
                    @if ($courses->isEmpty())
                    <p class="text-gray-600 col-span-full text-center">Belum ada mata kuliah yang ditugaskan untuk Anda.</p>
                    @else
                    @foreach ($courses as $index => $course)
                    @php
                    $imageCount = 6;
                    $imageNumber = ($index % $imageCount) + 1;
                    $imagePath = asset("/images/0$imageNumber.png");
                    @endphp
                    <a href="{{ route('course.show', $course->course_id) }}" class="bg-white p-4 rounded-xl shadow-md hover:shadow-lg transition duration-300 flex flex-col items-center course-card" data-no-prevent>
                        <img src="{{ $imagePath }}" alt="Course Image" class="w-full h-32 object-cover rounded-lg">
                        <div class="w-full text-center mt-3">
                            <h3 class="text-base font-semibold text-gray-800">{{ $course->course_name }}</h3>
                            <p class="text-xs text-gray-500 mt-1">Course Code: {{ $course->course_code }}</p>
                        </div>
                    </a>
                    @endforeach
                    @endif
                </div>
                <!-- List View -->
                <div id="listView" class="hidden max-w-5xl w-full">
                    <ul class="bg-white rounded-lg shadow-md divide-y">
                        @if ($courses->isEmpty())
                        <li class="p-4 text-gray-600 text-center">Belum ada mata kuliah yang ditugaskan untuk Anda.</li>
                        @else
                        @foreach ($courses as $course)
                        <li class="p-4 flex justify-between items-center hover:bg-gray-100 cursor-pointer transition rounded-md">
                            <a href="{{ route('course.show', $course->course_id) }}" class="w-full text-left flex justify-between items-center" data-no-prevent>
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-800">{{ $course->course_name }}</h3>
                                    <p class="text-xs text-gray-500">Course Code: {{ $course->course_code }}</p>
                                </div>
                            </a>
                        </li>
                        @endforeach
                        @endif
                    </ul>
                </div>
            </div>
            <div id="content-timeline" class="tab-content hidden flex flex-col items-center justify-start h-full pt-4">
                @if ($quizzes->isEmpty())
                <div class="flex flex-col items-center justify-center h-full">
                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <p class="mt-2 text-gray-500">No upcoming activities due</p>
                </div>
                @else
                <div class="w-full max-w-5xl flex flex-col items-center gap-4">
                    @foreach ($quizzes as $quiz)
                    @php
                    $formattedEndTime = \Carbon\Carbon::parse($quiz->end_time)->format('d M Y, H:i');
                    $courseId = strtolower(str_replace('-', '', $quiz->course_code));
                    $daysRemaining = floor($quiz->days_remaining);
                    @endphp
                    <a href="{{ route('course.show', $courseId) }}" class="w-full max-w-md timeline-card" data-no-prevent>
                        <div class="p-4">
                            <h3 class="text-base font-semibold text-gray-800">{{ $quiz->title }}</h3>
                            <p class="text-xs text-gray-500 mt-1">Course: {{ $quiz->course_name }} ({{ $quiz->course_code }})</p>
                            <p class="text-xs text-gray-500 mt-1">Task Number: {{ $quiz->task_number }}</p>
                            <p class="text-xs text-gray-500 mt-1">Due: {{ $formattedEndTime }}</p>
                            <p class="text-xs text-gray-600 mt-2">
                                @if ($daysRemaining > 0)
                                {{ $daysRemaining }} day{{ $daysRemaining > 1 ? 's' : '' }} remaining
                                @elseif ($daysRemaining == 0)
                                Due today!
                                @else
                                Due soon!
                                @endif
                            </p>
                        </div>
                    </a>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const tabs = document.querySelectorAll(".tab-button");
        const contents = document.querySelectorAll(".tab-content");
        const dropdownToggle = document.getElementById("dropdownToggle");
        const dropdownMenu = document.getElementById("dropdownMenu");
        const dropdownText = document.getElementById("dropdownText");
        const cardView = document.getElementById("cardView");
        const listView = document.getElementById("listView");

        // Toggle dropdown visibility
        dropdownToggle.addEventListener("click", () => {
            dropdownMenu.classList.toggle("hidden");
        });

        // Handle view selection
        document.querySelectorAll(".select-view").forEach(button => {
            button.addEventListener("click", (event) => {
                const selectedView = event.target.dataset.view;
                dropdownText.textContent = selectedView.charAt(0).toUpperCase() + selectedView.slice(1);
                if (selectedView === "card") {
                    cardView.classList.remove("hidden");
                    listView.classList.add("hidden");
                } else {
                    cardView.classList.add("hidden");
                    listView.classList.remove("hidden");
                }
                dropdownMenu.classList.add("hidden");
            });
        });

        // Close dropdown on outside click
        document.addEventListener("click", (event) => {
            if (!dropdownToggle.contains(event.target) && !dropdownMenu.contains(event.target)) {
                dropdownMenu.classList.add("hidden");
            }
        });

        // Handle tab switching
        tabs.forEach((tab, index) => {
            tab.addEventListener("click", () => {
                tabs.forEach(t => t.classList.remove("active"));
                contents.forEach(c => c.classList.add("hidden"));
                tab.classList.add("active");
                contents[index].classList.remove("hidden");
            });
        });

        // SweetAlert2 for pending quizzes
    @if ($quizzes->isNotEmpty())
            let pendingQuizzes = [];
    @foreach ($quizzes as $quiz)
    @php
        $formattedEndTime = \Carbon\Carbon::parse($quiz->end_time)->format('d M Y, H:i');
    @endphp
        pendingQuizzes.push({
            title: "{{ $quiz->title }}",
            course_code: "{{ $quiz->course_code }}",
            end_time: "{{ $formattedEndTime }}"
        });
    @endforeach
        if (pendingQuizzes.length > 0) {
            let quizList = pendingQuizzes.map(quiz => `
                    <div style="text-align: left; margin-bottom: 10px;">
                        <strong>Tugas:</strong> ${quiz.title}<br>
                        <strong>Kode Mata Kuliah:</strong> ${quiz.course_code}<br>
                        <strong>Batas Waktu:</strong> ${quiz.end_time}
                    </div>
                `).join('');
            Swal.fire({
                title: 'Ada tugas yang harus diselesaikan!',
                html: quizList,
                icon: 'warning',
                confirmButtonColor: '#106587',
                confirmButtonText: 'OK'
            });
        }
    @endif
    });
</script>
@endpush
@endsection
