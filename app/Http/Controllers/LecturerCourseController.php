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
            ->mapWithKeys(function ($quiz, $index) {
                $week = [4, 7, 10, 14][$index] ?? null;
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

        $course = Course::where('course_code', $courseCode)->firstOrFail();
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
}
