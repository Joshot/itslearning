@extends('layouts.app')

@section('title', 'Quiz - ' . $courseName)

@section('content')
<div class="flex justify-center items-start min-h-[80vh] space-x-6 p-4">
    <!-- Sidebar untuk nomor soal -->
    <div class="w-1/5 bg-white shadow-lg rounded-2xl min-h-[700px] max-h-[700px] p-4 sticky top-4 h-fit flex flex-col">
        <h3 class="text-lg font-semibold text-gray-800 text-center mb-4">Nomor Soal</h3>
        <div class="grid grid-cols-5 gap-2">
            @foreach ($questions as $index => $question)
            <button
                class="question-number w-12 h-12 rounded-lg text-white font-semibold
                        {{ session('answers.'.$question->id) ? 'bg-green-500' : 'bg-gray-400' }}"
                onclick="showQuestion({{ $index }})">
                {{ $index + 1 }}
            </button>
            @endforeach
        </div>
    </div>

    <!-- Main Content -->
    <div class="w-4/5 bg-white shadow-lg rounded-2xl p-8 overflow-y-auto min-h-[700px] max-h-[700px]">
        <h2 class="text-xl font-semibold mb-4">Mulai Kuis</h2>

        <form id="quizForm" action="{{ route('kuis.submit', ['courseCode' => $courseCode, 'quizId' => $quizId]) }}" method="POST">
            @csrf
            <input type="hidden" name="quiz_id" value="{{ $quizId }}">

            @foreach ($questions as $index => $question)
            <div class="question-card" id="question-{{ $index }}" style="display: {{ $index === 0 ? 'block' : 'none' }}">
                <p class="text-lg font-semibold mb-2">{{ $index + 1 }}. {{ $question->question_text }}</p>

                <div class="space-y-2">
                    @foreach (['A', 'B', 'C', 'D'] as $option)
                    <label class="answer-label block bg-gray-100 p-3 rounded-lg cursor-pointer hover:text-black hover:bg-gray-300 transition-all duration-200"
                           data-index="{{ $index }}" data-value="{{ $option }}">
                        <input type="radio" name="answers[{{ $question->id }}]" value="{{ $option }}"
                               class="hidden" onchange="markAnswered({{ $index }}, this)">
                        <span class="answer-text">{{ $option }}. {{ $question['option_' . strtolower($option)] }}</span>
                    </label>
                    @endforeach
                </div>

                <!-- Navigasi Soal -->
                <div class="mt-4 flex justify-between">
                    @if ($index > 0)
                    <button type="button" class="px-4 py-2 bg-gray-400 text-white rounded-lg" onclick="showQuestion({{ $index - 1 }})">Back</button>
                    @else
                    <span></span> <!-- Placeholder agar Next tetap di kanan -->
                    @endif

                    @if ($index < count($questions) - 1)
                    <button type="button" class="px-4 py-2 bg-blue-500 text-white rounded-lg ml-auto" onclick="showQuestion({{ $index + 1 }})">Next</button>
                    @else
                    <button type="button" class="px-4 py-2 bg-[#234e7f] text-white rounded-lg ml-auto" onclick="validateQuiz()">Submit</button>
                    @endif
                </div>
            </div>
            @endforeach
        </form>
    </div>
</div>

<!-- Tambahkan SweetAlert2 untuk alert yang lebih bagus -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function showQuestion(index) {
        document.querySelectorAll('.question-card').forEach((el, i) => {
            el.style.display = i === index ? 'block' : 'none';
        });
    }

    function markAnswered(index, element) {
        // Ubah warna nomor soal di sidebar menjadi hijau
        document.querySelectorAll('.question-number')[index].classList.remove('bg-gray-400');
        document.querySelectorAll('.question-number')[index].classList.add('bg-green-500');

        // Hapus highlight dari semua opsi dalam soal ini
        let parentDiv = element.closest('.space-y-2');
        parentDiv.querySelectorAll('.answer-label').forEach(label => {
            label.classList.remove('bg-[#234e7f]', 'text-white');
            label.classList.add('bg-gray-100', 'text-black');
        });

        // Tambahkan highlight ke jawaban yang dipilih
        let selectedLabel = element.closest('.answer-label');
        selectedLabel.classList.add('bg-[#234e7f]', 'text-white');
        selectedLabel.classList.remove('bg-gray-100', 'text-black');
    }

    function validateQuiz() {
        let totalQuestions = document.querySelectorAll('.question-card').length;
        let answeredQuestions = document.querySelectorAll('.question-number.bg-green-500').length;

        if (answeredQuestions < totalQuestions) {
            Swal.fire({
                title: 'Oops!',
                text: 'Harap jawab semua pertanyaan sebelum mengirim kuis!',
                icon: 'warning',
                confirmButtonText: 'OK',
                confirmButtonColor: '#234e7f'
            });
        } else {
            Swal.fire({
                title: 'Yakin ingin submit?',
                text: 'Pastikan semua jawaban sudah benar sebelum mengirim!',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Submit',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#234e7f'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('quizForm').submit();
                }
            });
        }
    }
</script>

@endsection
