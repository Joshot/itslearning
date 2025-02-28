@extends('layouts.app')

@section('title', 'Quiz - ' . $courseName)

@section('content')
<div class="flex justify-center items-start min-h-[80vh] space-x-6 p-4">
    <div class="w-1/5 bg-white shadow-lg rounded-2xl min-h-[700px] max-h-[700px] p-4 sticky top-4 h-fit flex flex-col">
        <!-- Sidebar (kode yang sama dengan course.blade.php) -->
    </div>

    <!-- Main Content -->
    <div class="w-4/5 bg-white shadow-lg rounded-2xl p-8 overflow-y-auto min-h-[700px] max-h-[700px]">
        <h2 class="text-xl font-semibold mb-4">Mulai Kuis</h2>

        <form action="{{ route('kuis.submit', $courseCodeFull) }}" method="POST">
            @csrf
            @foreach($questions as $index => $question)
            <div class="space-y-4">
                <div class="flex flex-col p-4 bg-gray-50 rounded-lg shadow-md">
                    <p class="font-semibold text-gray-800">{{ $index + 1 }}. {{ $question->question_text }}</p>
                    <div class="mt-3">
                        @foreach (json_decode($question->options) as $option)
                        <div class="flex items-center space-x-2">
                            <input type="radio" name="answers[{{ $question->id }}]" value="{{ $option }}" class="form-radio text-blue-500">
                            <label for="option">{{ $option }}</label>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endforeach
            <button type="submit" class="mt-6 p-3 bg-blue-600 text-white rounded-lg w-full">Selesai</button>
        </form>
    </div>
</div>
@endsection
