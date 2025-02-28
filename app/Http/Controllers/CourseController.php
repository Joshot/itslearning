<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function show(Request $request, $courseCode)
    {
        // Ambil daftar courses dari config
        $courses = config('courses', []);

        // Ubah input jadi huruf besar untuk pencocokan
        $courseCode = strtoupper($courseCode);

        // Mencari kecocokan dengan menghapus tanda '-' dari kode dalam config
        $matchedCourse = collect($courses)->first(function ($course) use ($courseCode) {
            return strtoupper(str_replace('-', '', $course['code'])) === $courseCode;
        });

        // Jika ditemukan, gunakan data course; jika tidak, berikan default
        $courseName = $matchedCourse['name'] ?? 'Unknown Course';
        $courseCodeFull = $matchedCourse['code'] ?? 'N/A';

        // **Pastikan materials selalu array**
        $materials = $matchedCourse['materials'] ?? [];

        // Ambil nilai kuis dari cookie, jika tidak ada maka default [0, 0, 0, 0]
        $quizScores = json_decode($request->cookie("quiz_scores_$courseCode"), true) ?? [0, 0, 0, 0];

        // Kirim data ke Blade
        return view('matkul.course', compact('courseName', 'courseCodeFull', 'materials', 'quizScores'));
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
