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

        <form action="{{ route('kuis.submit', ['courseCode' => $courseCode, 'quizId' => $quizId]) }}" method="POST">
            @csrf
            <input type="hidden" name="quiz_id" value="{{ $quizId }}">
            @foreach ($questions as $question)
            <p>{{ $question->question_text }}</p>
            <label><input type="radio" name="answers[{{ $question->id }}]" value="A"> {{ $question->option_a }}</label>
            <label><input type="radio" name="answers[{{ $question->id }}]" value="B"> {{ $question->option_b }}</label>
            <label><input type="radio" name="answers[{{ $question->id }}]" value="C"> {{ $question->option_c }}</label>
            <label><input type="radio" name="answers[{{ $question->id }}]" value="D"> {{ $question->option_d }}</label>
            @endforeach
            <button type="submit">Submit</button>
        </form>


    </div>
</div>
@endsection
