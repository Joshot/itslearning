<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\StudentAttempt;
use App\Models\StudentAnswer;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class QuizController extends Controller
{
    /**
     * Menampilkan halaman kuis dan mengambil soal dari database berdasarkan algoritma CAT.
     */
    public function startQuiz($courseCode, $quizId)
    {
        $studentId = auth()->id();

        // Format kode kursus agar sesuai dengan konfigurasi
        $formattedCourseCode = strtoupper(preg_replace('/([a-zA-Z]+)(\d+)([a-zA-Z]*)/', '$1$2-$3', $courseCode));

        // Ambil daftar kursus dari konfigurasi
        $courses = config('courses');

        // Cari kursus berdasarkan kode yang diformat
        $course = collect($courses)->firstWhere('code', $formattedCourseCode);

        if (!$course) {
            return redirect()->route('dashboard')->with('error', 'Course not found');
        }

        // Ambil nama kursus
        $courseName = $course['name'];

        // Ambil kuis berdasarkan `course_code` dan `quizId`
        $quiz = Quiz::where('course_code', $formattedCourseCode)
            ->where('id', $quizId) // Perbaikan: pastikan kuis sesuai dengan quizId
            ->first();

        if (!$quiz) {
            return redirect()->route('dashboard')->with('error', 'Quiz tidak ditemukan.');
        }

        // Ambil soal kuis sesuai aturan CAT
        $questions = $this->getQuestionsForQuiz($quizId);

        return view('matkul.kuis', compact('questions', 'courseCode', 'quizId', 'courseName'));
    }





    /**
     * Menyimpan hasil kuis dan jawaban mahasiswa ke database.
     */
    public function submitQuiz(Request $request, $courseCode, $quizId)
    {
        $studentId = Auth::guard('student')->id();

        if (!$studentId) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $answers = $request->input('answers');

        $existingAttempt = StudentAttempt::where('student_id', $studentId)
            ->where('quiz_id', $quizId)
            ->first();

        if ($existingAttempt) {
            return redirect()->route('course.show', ['courseCode' => strtolower(str_replace('-', '', $courseCode))])
                ->with('error', 'Anda sudah mengerjakan kuis ini.');
        }

        $attempt = new StudentAttempt();
        $attempt->student_id = $studentId;
        $attempt->quiz_id = $quizId;
        $attempt->score = 0;
        $attempt->save();

        $score = 0;
        $totalQuestions = count($answers);

        foreach ($answers as $questionId => $selectedOption) {
            $question = Question::find($questionId);
            if (!$question) continue;

            $isCorrect = $question->correct_option == $selectedOption;
            if ($isCorrect) {
                $score++;
            }

            StudentAnswer::create([
                'attempt_id' => $attempt->id,
                'question_id' => $questionId,
                'selected_option' => $selectedOption,
                'is_correct' => $isCorrect,
            ]);
        }

        $finalScore = ($score / $totalQuestions) * 100;
        $attempt->score = $finalScore;
        $attempt->save();

        return redirect()->route('course.show', ['courseCode' => strtolower(str_replace('-', '', $courseCode))])
            ->with('quiz_completed', [
                'quiz_number' => $quizId,
                'score' => round($finalScore, 2),
            ]);
    }


    /**
     * Mengambil soal berdasarkan aturan CAT (default pertama kali).
     */
    private function getQuestionsForQuiz($quizId)
    {
        return collect([
            'easy' => Question::where('difficulty', 'easy')->where('quiz_id', $quizId)->inRandomOrder()->limit(4)->get(),
            'medium' => Question::where('difficulty', 'medium')->where('quiz_id', $quizId)->inRandomOrder()->limit(3)->get(),
            'hard' => Question::where('difficulty', 'hard')->where('quiz_id', $quizId)->inRandomOrder()->limit(3)->get(),
        ])->flatten();
    }

    /**
     * Mengecek apakah semua kuis sudah selesai untuk mengaktifkan ITS.
     */
    private function checkITS($studentId)
    {
        $totalQuizzes = Quiz::count();
        $attemptedQuizzes = StudentAttempt::where('student_id', $studentId)->count();

        if ($attemptedQuizzes >= $totalQuizzes) {
            // Panggil fungsi analisis ITS
            $this->analyzeITS($studentId);
        }
    }

    /**
     * Menganalisis hasil kuis mahasiswa berdasarkan ITS.
     */
    private function analyzeITS($studentId)
    {
        $attempts = StudentAttempt::where('student_id', $studentId)->get();
        $totalScore = 0;
        $errorCount = ['easy' => 0, 'medium' => 0, 'hard' => 0];

        foreach ($attempts as $attempt) {
            $totalScore += $attempt->score;

            $answers = StudentAnswer::where('attempt_id', $attempt->id)->get();
            foreach ($answers as $answer) {
                if (!$answer->is_correct) {
                    $question = Question::find($answer->question_id);
                    $errorCount[$question->difficulty]++;
                }
            }
        }

        $averageScore = $totalScore / count($attempts);
        $feedback = $this->getFeedback($averageScore);

        // Simpan hasil ITS
        return [
            'average_score' => $averageScore,
            'feedback' => $feedback,
            'errors' => $errorCount,
        ];
    }

    /**
     * Memberikan feedback berdasarkan skor rata-rata.
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
