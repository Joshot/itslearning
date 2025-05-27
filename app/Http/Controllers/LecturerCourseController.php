<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseMaterial;
use App\Models\CourseAssignment;
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
            return redirect()->route('lecture.dashboard')->with('error', 'Course not found');
        }

        // Ambil materi kursus
        $courseMaterials = CourseMaterial::where('course_id', $course->id)->get();

        // Inisialisasi materials untuk minggu 1-14
        $materials = [];
        for ($week = 0; $week < 14; $week++) {
            $material = $courseMaterials->firstWhere('week', $week + 1);
            $materials[$week] = [
                'files' => $material && $material->files ? array_map(fn($file) => 'storage/' . $file, $material->files) : [],
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

        // Ambil data course assignments
        $assignments = CourseAssignment::where('course_code', $formattedCourseCode)
            ->with(['lecturer', 'student'])
            ->get();
        $lecturerCount = $assignments->whereNotNull('lecturer_id')->count();
        $studentCount = $assignments->whereNotNull('student_id')->count();
        $totalParticipants = $lecturerCount + $studentCount;

        // Hapus tanda "-" untuk Blade
        $courseCodeWithoutDash = str_replace('-', '', $formattedCourseCode);

        Log::info("Course data for {$formattedCourseCode}", [
            'courseName' => $course->course_name,
            'materials' => $materials,
            'quizzes' => $quizzes->toArray(),
            'totalParticipants' => $totalParticipants,
        ]);

        return view('lecture.course', compact(
            'courseCodeWithoutDash',
            'formattedCourseCode',
            'course',
            'materials',
            'quizzes',
            'assignments',
            'lecturerCount',
            'studentCount',
            'totalParticipants'
        ));
    }

    public function storeMaterial(Request $request, $courseCode)
    {
        $request->validate([
            'week' => 'required|integer|min:1|max:14',
            'files.*' => [
                'nullable',
                'file',
                'max:20480', // maksimal 20MB per file
            ],
            'video_url' => 'nullable|url',
            'is_optional' => 'nullable|boolean',
        ]);

        $formattedCourseCode = strtoupper(preg_replace('/([a-zA-Z]+)(\d+)([a-zA-Z]*)/', '$1$2-$3', $courseCode));
        $course = Course::where('course_code', $formattedCourseCode)->firstOrFail();
        $week = $request->week;

        // Ambil material yang sudah ada atau buat baru
        $material = CourseMaterial::updateOrCreate(
            ['course_id' => $course->id, 'week' => $week],
            [
                'video_url' => $request->video_url,
                'is_optional' => $request->has('is_optional'),
            ]
        );

        // Proses upload file
        if ($request->hasFile('files')) {
            $files = $material->files ?? [];
            foreach ($request->file('files') as $file) {
                $path = $file->store('materials', 'public');
                $files[] = $path;
            }
            $material->files = $files;
        }

        $material->save();

        $fileTypes = collect($request->file('files') ?? [])->map(function ($file) {
            return strtoupper(pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION));
        })->implode(', ');

        $message = "Materi Minggu {$week} berhasil diunggah";
        if ($fileTypes) {
            $message .= " (File: {$fileTypes})";
        }
        if ($request->video_url) {
            $message .= " dengan URL video";
        }

        return redirect()->back()->with('material_uploaded', $message);
    }

    public function deleteMaterial(Request $request, $courseCode, $week, $index)
    {
        $formattedCourseCode = strtoupper(preg_replace('/([a-zA-Z]+)(\d+)([a-zA-Z]*)/', '$1$2-$3', $courseCode));
        $course = Course::where('course_code', $formattedCourseCode)->firstOrFail();
        $material = CourseMaterial::where('course_id', $course->id)->where('week', $week)->first();

        if (!$material || !isset($material->files[$index])) {
            return redirect()->back()->with('error', 'File tidak ditemukan.');
        }

        // Hapus file dari storage
        Storage::disk('public')->delete($material->files[$index]);

        // Hapus file dari array
        $files = $material->files;
        unset($files[$index]);
        $material->files = array_values($files); // Reindex array

        $material->save();

        return redirect()->back()->with('success', 'File berhasil dihapus.');
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
        $easyQuestions = Question::where('difficulty', 'easy')->inRandomOrder()->take(4)->get();
        $mediumQuestions = Question::where('difficulty', 'medium')->inRandomOrder()->take(3)->get();
        $hardQuestions = Question::where('difficulty', 'hard')->inRandomOrder()->take(3)->get();

        // Validasi jumlah soal
        if ($easyQuestions->count() < 4 || $mediumQuestions->count() < 3 || $hardQuestions->count() < 3) {
            $quiz->delete();
            return redirect()->back()->with('error', 'Tidak cukup soal dengan tingkat kesulitan yang dibutuhkan.');
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

    public function showBankSoal($courseCode)
    {
        $formattedCourseCode = strtoupper(preg_replace('/([a-zA-Z]+)(\d+)([a-zA-Z]*)/', '$1$2-$3', $courseCode));
        $course = Course::where('course_code', $formattedCourseCode)->firstOrFail();
        $courseCodeWithoutDash = str_replace('-', '', $formattedCourseCode);
        $questions = Question::where('course_id', $course->id)->get();

        return view('lecture.banksoal', compact('course', 'questions', 'courseCodeWithoutDash'));
    }
}
