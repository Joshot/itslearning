<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseMaterial;
use App\Models\Quiz;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class LecturerCourseController extends Controller
{
    public function show($courseCode)
    {
        Log::info("Accessing lecturer course page", ['courseCode' => $courseCode]);

        // Format kode kursus (if540d menjadi IF540-D)
        $formattedCourseCode = strtoupper(preg_replace('/([a-zA-Z]+)(\d+)([a-zA-Z]*)/', '$1$2-$3', $courseCode));

        // Ambil kursus
        $course = Course::where('course_code', $formattedCourseCode)->first();

        if (!$course) {
            Log::error("Course not found for code: {$formattedCourseCode}");
            return redirect()->route('lecturer.dashboard')->with('error', 'Course not found');
        }

        // Ambil materi kursus
        $courseMaterials = CourseMaterial::where('course_id', $course->id)->get();

        // Inisialisasi materials untuk minggu 1-14
        $materials = [];
        for ($week = 0; $week < 14; $week++) {
            $material = $courseMaterials->firstWhere('week', $week + 1);
            $materials[$week] = [
                'pdf' => $material && $material->pdf_path ? 'storage/' . $material->pdf_path : null,
                'video_url' => $material ? $material->video_url : null,
                'optional' => $material ? (bool) $material->is_optional : false,
            ];
        }

        // Ambil kuis untuk bank soal (minggu 4, 7, 10, 14)
        $quizzes = Quiz::where('course_code', $formattedCourseCode)
            ->with(['questions' => function ($query) {
                $query->select('id', 'quiz_id', 'difficulty');
            }])
            ->get()
            ->mapWithKeys(function ($quiz) {
                // Map minggu berdasarkan title (Tugas 1 -> minggu 4, Tugas 2 -> minggu 7, dst.)
                $taskNumber = (int) preg_replace('/Tugas (\d+),.*/', '$1', $quiz->title);
                $week = [1 => 4, 2 => 7, 3 => 10, 4 => 14][$taskNumber] ?? null;
                if ($week) {
                    return [$week => [
                        'id' => $quiz->id,
                        'title' => $quiz->title,
                        'total_questions' => $quiz->questions->count(),
                        'easy_questions' => $quiz->questions->where('difficulty', 'easy')->count(),
                        'medium_questions' => $quiz->questions->where('difficulty', 'medium')->count(),
                        'hard_questions' => $quiz->questions->where('difficulty', 'hard')->count(),
                    ]];
                }
                return [];
            });

        // Hapus tanda "-" untuk Blade
        $courseCodeWithoutDash = str_replace('-', '', $formattedCourseCode);

        Log::info("Course data for {$formattedCourseCode}", [
            'courseName' => $course->course_name,
            'materials' => $materials,
            'quizzes' => $quizzes->toArray(),
        ]);

        return view('lecture.course', compact(
            'courseCodeWithoutDash',
            'formattedCourseCode',
            'course',
            'materials',
            'quizzes'
        ));
    }

    public function storeMaterial(Request $request, $courseCode)
    {
        $request->validate([
            'week' => 'required|integer|between:1,14',
            'pdf' => 'nullable|file|mimes:pdf|max:10000',
            'video_url' => 'nullable|url',
        ]);

        $formattedCourseCode = strtoupper(preg_replace('/([a-zA-Z]+)(\d+)([a-zA-Z]*)/', '$1$2-$3', $courseCode));
        $course = Course::where('course_code', $formattedCourseCode)->firstOrFail();
        $week = $request->week;

        // Proses upload PDF
        $pdfPath = null;
        if ($request->hasFile('pdf')) {
            $pdfPath = $request->file('pdf')->store('materi', 'public');
        }

        // Simpan atau perbarui materi
        CourseMaterial::updateOrCreate(
            ['course_id' => $course->id, 'week' => $week],
            [
                'pdf_path' => $pdfPath ?? CourseMaterial::where('course_id', $course->id)->where('week', $week)->first()->pdf_path ?? null,
                'video_url' => $request->video_url,
                'is_optional' => $request->has('is_optional'),
            ]
        );

        return redirect()->back()->with('success', 'Material updated successfully');
    }

    public function createQuiz(Request $request, $courseCode)
    {
        $request->validate([
            'week' => 'required|integer|in:4,7,10,14',
            'title' => 'required|string|max:255',
        ]);

        $week = $request->week;
        $title = $request->title;
        $formattedCourseCode = strtoupper(preg_replace('/([a-zA-Z]+)(\d+)([a-zA-Z]*)/', '$1$2-$3', $courseCode));
        $course = Course::where('course_code', $formattedCourseCode)->firstOrFail();

        // Cek apakah kuis untuk minggu ini sudah ada
        $taskNumber = (int) ($week / 3.5);
        if (Quiz::where('course_code', $formattedCourseCode)->where('title', 'like', "Tugas {$taskNumber},%")->exists()) {
            return redirect()->back()->with('error', 'Tugas untuk minggu ini sudah ada.');
        }

        // Buat kuis baru
        $quiz = Quiz::create([
            'course_code' => $formattedCourseCode,
            'title' => $title,
            'start_time' => now(),
            'end_time' => now()->addDays(7),
        ]);

        // Pilih soal acak: 4 easy, 3 medium, 3 hard
        $easyQuestions = Question::where('difficulty', 'easy')
            ->inRandomOrder()
            ->take(4)
            ->get();
        $mediumQuestions = Question::where('difficulty', 'medium')
            ->inRandomOrder()
            ->take(3)
            ->get();
        $hardQuestions = Question::where('difficulty', 'hard')
            ->inRandomOrder()
            ->take(3)
            ->get();

        // Validasi jumlah soal
        if ($easyQuestions->count() < 4 || $mediumQuestions->count() < 3 || $hardQuestions->count() < 3) {
            $quiz->delete();
            return redirect()->back()->with('error', 'Tidak cukup soal dengan tingkat kesulitan yang diperlukan.');
        }

        // Perbarui quiz_id untuk soal yang dipilih
        $questions = $easyQuestions->merge($mediumQuestions)->merge($hardQuestions);
        foreach ($questions as $question) {
            $question->update(['quiz_id' => $quiz->id]);
        }

        Log::info("Quiz created for course {$formattedCourseCode}", [
            'quiz_id' => $quiz->id,
            'title' => $quiz->title,
            'total_questions' => $questions->count(),
            'easy_questions' => $easyQuestions->count(),
            'medium_questions' => $mediumQuestions->count(),
            'hard_questions' => $hardQuestions->count(),
        ]);

        return redirect()->back()->with('success', "Tugas berhasil dibuat: {$title} dengan 10 soal (4 easy, 3 medium, 3 hard).");
    }
}
