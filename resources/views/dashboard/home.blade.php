@extends('layouts.app')

@section('title', 'Home Page')

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
    }
    .tab-button.active {
        border-bottom: 2px solid #106587;
        color: #106587;
    }
</style>

<div class="flex justify-center items-center min-h-[80vh] space-x-8">
    <div class="flex space-x-8 w-full max-w-[80%]">
        <!-- Sidebar (2 bagian) -->
        <div class="flex-[2] bg-white shadow-lg rounded-2xl p-8 h-[600px] overflow-y-auto flex flex-col">
            <h2 class="text-2xl font-semibold">Profile</h2>
            <img src="/images/profile.jpg" alt="Profile Picture" class="w-32 h-32 rounded-full mx-auto mt-4">
            <p class="mt-4 text-center">Name: <strong>{{ Auth::guard('student')->user()->name ?? 'Guest' }}</strong></p>
            <p class="mt-2 text-center">Email Student: <strong>{{ Auth::guard('student')->user()->email ?? 'Guest' }}</strong></p>
            <p class="mt-4 text-center">Lorem ipsum dolor sit amet consectetur adipisicing elit.</p>
        </div>

        <!-- Konten Utama (8 bagian) -->
        <div class="flex-[8] bg-white shadow-lg rounded-2xl p-8 h-[600px] overflow-y-auto flex flex-col">
            <div class="flex border-b">
                <button id="tab-course" class="tab-button active">Course List</button>
                <button id="tab-timeline" class="tab-button">Timeline</button>
            </div>

            <div id="content-course" class="tab-content block flex flex-col items-center">
                @php
                use App\Models\Course;
                $courses = Course::all()->map(function ($course) {
                return [
                'code' => $course->course_code,
                'name' => $course->course_name,
                ];
                })->toArray();
                $imageCount = 6;
                @endphp

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

                <!-- Tampilan Card -->
                <div id="cardView" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 max-w-5xl w-full">
                    @foreach ($courses as $index => $course)
                    @php
                    $imageNumber = ($index % $imageCount) + 1;
                    $imagePath = asset("/images/0$imageNumber.png");
                    $courseId = strtolower(str_replace('-', '', $course['code']));
                    @endphp

                    <a href="{{ url('/course/' . $courseId) }}" class="bg-white p-4 rounded-xl shadow-md hover:shadow-lg transition duration-300 flex flex-col items-center" data-no-prevent>
                        <img src="{{ $imagePath }}" alt="Course Image" class="w-full h-32 object-cover rounded-lg">
                        <div class="w-full text-center mt-3">
                            <h3 class="text-base font-semibold text-gray-800">{{ $course['name'] }}</h3>
                            <p class="text-xs text-gray-500 mt-1">Course Code: {{ $course['code'] }}</p>
                        </div>
                    </a>
                    @endforeach
                </div>

                <!-- Tampilan List -->
                <div id="listView" class="hidden max-w-5xl w-full">
                    <ul class="bg-white rounded-lg shadow-md divide-y">
                        @foreach ($courses as $course)
                        @php
                        $courseId = strtolower(str_replace('-', '', $course['code']));
                        @endphp
                        <li class="p-4 flex justify-between items-center hover:bg-gray-100 cursor-pointer transition rounded-md">
                            <a href="{{ url('/course/' . $courseId) }}" class="w-full text-left flex justify-between items-center" data-no-prevent>
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-800">{{ $course['name'] }}</h3>
                                    <p class="text-xs text-gray-500">Course Code: {{ $course['code'] }}</p>
                                </div>
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div id="content-timeline" class="tab-content hidden flex flex-col items-center justify-center h-full">
                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                </svg>
                <p class="mt-2 text-gray-500">No upcoming activities due</p>
            </div>
        </div>
    </div>
</div>

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

        // Event listener untuk memilih tampilan
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

        // Close dropdown jika klik di luar
        document.addEventListener("click", (event) => {
            if (!dropdownToggle.contains(event.target) && !dropdownMenu.contains(event.target)) {
                dropdownMenu.classList.add("hidden");
            }
        });

        tabs.forEach((tab, index) => {
            tab.addEventListener("click", () => {
                tabs.forEach(t => t.classList.remove("active"));
                contents.forEach(c => c.classList.add("hidden"));

                tab.classList.add("active");
                contents[index].classList.remove("hidden");
            });
        });
    });
</script>
@endsection
