<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseMaterial;
use App\Models\Quiz;
use App\Models\StudentAnswer;
use App\Models\StudentAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CourseController extends Controller
{
    public function show($courseCode)
    {
        Log::info("Accessing course page", ['courseCode' => $courseCode]);

        // Format kode kursus dari URL (if540d menjadi IF540-D)
        $formattedCourseCode = strtoupper(preg_replace('/([a-zA-Z]+)(\d+)([a-zA-Z]*)/', '$1$2-$3', $courseCode));

        // Ambil kursus dari database
        $course = Course::where('course_code', $formattedCourseCode)->first();

        if (!$course) {
            Log::error("Course not found for code: {$formattedCourseCode}");
            return redirect()->route('dashboard')->with('error', 'Course not found');
        }

        // Ambil materi kursus
        $courseMaterials = CourseMaterial::where('course_id', $course->id)->get();

        // Log data untuk debugging
        Log::info("Course materials retrieved", ['courseMaterials' => $courseMaterials->toArray()]);

        // Inisialisasi materials sebagai array untuk minggu 1-14
        $materials = [];
        for ($week = 0; $week < 14; $week++) {
            $material = $courseMaterials->firstWhere('week', $week + 1);
            $materials[$week] = [
                'pdf' => $material && $material->pdf_path ? 'storage/' . $material->pdf_path : null,
                'optional' => $material ? (bool) $material->is_optional : false,
            ];
        }

        // Hapus tanda "-" dari course code untuk ditampilkan di Blade
        $courseCodeWithoutDash = str_replace('-', '', $formattedCourseCode);

        // Ambil semua kuis berdasarkan course_code
        $quizzes = Quiz::where('course_code', $formattedCourseCode)->get();

        // Buat mapping antara title kuis (1,2,3,4) dengan ID sebenarnya
        $availableQuizzes = [];
        foreach ($quizzes as $quiz) {
            $availableQuizzes[(int)$quiz->title] = $quiz->id;
        }

        // Ambil nilai kuis dari student_attempts berdasarkan student_answers
        $quizScores = [];
        $studentId = Auth::guard('student')->id();
        Log::info("Student ID", ['studentId' => $studentId]);

        for ($i = 1; $i <= 4; $i++) {
            if (!isset($availableQuizzes[$i])) {
                $quizScores[$i] = null;
                continue;
            }

            $quizId = $availableQuizzes[$i];

            // Cari attempt berdasarkan student_id dan quiz_id
            $attempt = StudentAttempt::where('student_id', $studentId)
                ->where('quiz_id', $quizId)
                ->first();

            if ($attempt) {
                // Ambil jumlah jawaban yang benar
                $correctAnswers = StudentAnswer::where('attempt_id', $attempt->id)
                    ->where('is_correct', true)
                    ->count();

                // Ambil total jumlah soal
                $totalQuestions = StudentAnswer::where('attempt_id', $attempt->id)->count();

                // Hitung skor
                $score = $totalQuestions > 0 ? round(($correctAnswers / $totalQuestions) * 100, 2) : 0;
                $quizScores[$i] = $score;
            } else {
                $quizScores[$i] = null;
            }
        }

        Log::info("Course data for {$formattedCourseCode}", [
            'courseName' => $course->course_name,
            'formattedCourseCode' => $formattedCourseCode,
            'materials' => $materials,
            'quizScores' => $quizScores,
            'availableQuizzes' => $availableQuizzes,
        ]);

        return view('matkul.course', compact(
            'courseCodeWithoutDash',
            'formattedCourseCode',
            'course',
            'materials',
            'quizScores',
            'availableQuizzes'
        ));
    }



    public function saveQuizScore(Request $request, $courseCode, $quizNumber)
    {
        // Ambil nilai kuis dari cookie
        $quizScores = json_decode($request->cookie("quiz_scores_$courseCode"), true) ?? [0, 0, 0, 0];

        // Pastikan quizNumber valid (1-4)
        if ($quizNumber < 1 || $quizNumber > 4) {
            return response()->json(['error' => 'Invalid quiz number'], 400);
        }

        // Simpan skor baru
        $quizScores[$quizNumber - 1] = $request->input('score', 0);

        // Simpan ke cookie selama 30 hari
        return response()->json(['message' => 'Score saved successfully'])
            ->cookie("quiz_scores_$courseCode", json_encode($quizScores), 43200); // 30 hari
    }
}
