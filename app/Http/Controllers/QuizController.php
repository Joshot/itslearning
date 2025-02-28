<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\StudentAttempt;
use App\Models\Quiz;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    /**
     * Menampilkan halaman kuis dan soal untuk mahasiswa.
     */
    public function startQuiz($courseCode)
    {
        // Ambil soal untuk kuis berdasarkan courseCode
        $questions = $this->getQuestionsForQuiz($courseCode);

        return view('matkul.kuis', compact('questions', 'courseCode'));
    }

    /**
     * Mengirimkan hasil kuis dan menyimpan ke database.
     */
    public function submitQuiz(Request $request, $courseCode)
    {
        $answers = $request->input('answers');
        $score = 0;

        // Validasi dan hitung skor berdasarkan jawaban
        foreach ($answers as $questionId => $answer) {
            $question = Question::find($questionId);
            if ($question && $question->correct_option == $answer) {
                $score++;
            }
        }

        // Ambil quiz berdasarkan courseCode (untuk mendapatkan quiz_id)
        $quiz = Quiz::where('course_code', $courseCode)->first();

        if (!$quiz) {
            return response()->json(['error' => 'Quiz not found'], 404);
        }

        // Simpan hasil kuis ke tabel student_attempts
        $attempt = new StudentAttempt();
        $attempt->student_id = auth()->user()->id;
        $attempt->quiz_id = $quiz->id;
        $attempt->score = $score;
        $attempt->save();

        // Evaluasi dan kirim feedback berdasarkan skor
        $feedback = $this->getFeedback($score);

        return redirect()->route('kuis.start', ['courseCode' => $courseCode])
            ->with('success', "Kuis selesai! Skor Anda: $score. $feedback");
    }

    /**
     * Mengambil soal-soal kuis berdasarkan courseCode
     */
    private function getQuestionsForQuiz($courseCode)
    {
        // Ambil soal berdasarkan kesulitan (easy, medium, hard)
        $easyQuestions = Question::where('difficulty', 'easy')->get();
        $mediumQuestions = Question::where('difficulty', 'medium')->get();
        $hardQuestions = Question::where('difficulty', 'hard')->get();

        // Pilih soal sesuai dengan jumlah yang ditentukan
        $selectedQuestions = [
            'easy' => $easyQuestions->random(4),
            'medium' => $mediumQuestions->random(3),
            'hard' => $hardQuestions->random(3),
        ];

        return collect($selectedQuestions)->flatten();
    }

    /**
     * Memberikan feedback berdasarkan skor
     */
    private function getFeedback($score)
    {
        if ($score >= 85) return 'A';
        if ($score >= 80) return 'A-';
        if ($score >= 75) return 'B+';
        if ($score >= 70) return 'B';
        if ($score >= 65) return 'B-';
        if ($score >= 60) return 'C+';
        if ($score >= 55) return 'C';
        if ($score >= 45) return 'D';
        return 'E';
    }
}
