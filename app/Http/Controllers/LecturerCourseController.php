<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseMaterial;
use App\Models\CourseAssignment;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\StudentAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class LecturerCourseController extends Controller
{
    // Helper method to map week to task number
    private function getTaskNumberFromWeek($week)
    {
        $quizWeeks = [
            4 => 1,
            7 => 2,
            10 => 3,
            14 => 4
        ];
        return $quizWeeks[$week] ?? null;
    }

    public function show($courseCode)
    {
        $formattedCourseCode = strtoupper(preg_replace('/([a-zA-Z]+)(\d+)([a-zA-Z]*)/', '$1$2-$3', $courseCode));
        $course = Course::where('course_code', $formattedCourseCode)->first();

        if (!$course) {
            Log::error("Course not found for code: {$formattedCourseCode}");
            return redirect()->route('lecturer.dashboard')->with('error', 'Course not found');
        }

        $courseMaterials = CourseMaterial::where('course_id', $course->id)->get();
        $materials = [];
        for ($week = 0; $week < 14; $week++) {
            $material = $courseMaterials->firstWhere('week', $week + 1);
            $materials[$week] = [
                'files' => $material && $material->files ? array_map(fn($file) => 'storage/' . $file, $material->files) : [],
                'video_url' => $material ? $material->video_url : null,
                'optional' => $material ? (bool) $material->is_optional : false,
            ];
        }

        $taskToWeek = [
            1 => 4,
            2 => 7,
            3 => 10,
            4 => 14
        ];

        $quizzes = Quiz::where('course_code', $formattedCourseCode)->get()->mapWithKeys(function ($quiz) use ($course, $taskToWeek) {
            $taskNumber = $quiz->task_number;

            if (!isset($taskToWeek[$taskNumber])) {
                return [];
            }

            $week = $taskToWeek[$taskNumber];

            $easyQuestions = Question::where('course_id', $course->id)
                ->where('task_number', $taskNumber)
                ->where('difficulty', 'easy')
                ->inRandomOrder()
                ->take(4)
                ->get();
            $mediumQuestions = Question::where('course_id', $course->id)
                ->where('task_number', $taskNumber)
                ->where('difficulty', 'medium')
                ->inRandomOrder()
                ->take(3)
                ->get();
            $hardQuestions = Question::where('course_id', $course->id)
                ->where('task_number', $taskNumber)
                ->where('difficulty', 'hard')
                ->inRandomOrder()
                ->take(3)
                ->get();

            $questions = $easyQuestions->merge($mediumQuestions)->merge($hardQuestions);

            return [$week => [
                'id' => $quiz->id,
                'title' => $quiz->title,
                'task_number' => $taskNumber,
                'start_time' => $quiz->start_time,
                'end_time' => $quiz->end_time,
                'total_questions' => $questions->count(),
                'easy_questions' => $easyQuestions->count(),
                'medium_questions' => $mediumQuestions->count(),
                'hard_questions' => $hardQuestions->count(),
            ]];
        })->filter();

        $assignments = CourseAssignment::where('course_code', $formattedCourseCode)
            ->with(['lecturer', 'student'])
            ->get();
        $lecturerCount = $assignments->whereNotNull('lecturer_id')->count();
        $studentCount = $assignments->whereNotNull('student_id')->count();
        $totalParticipants = $lecturerCount + $studentCount;

        $courseCodeWithoutDash = str_replace('-', '', $formattedCourseCode);

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

    public function showBankSoal($courseCode)
    {
        $formattedCourseCode = strtoupper(preg_replace('/([a-zA-Z]+)(\d+)([a-zA-Z]*)/', '$1$2-$3', $courseCode));
        $course = Course::where('course_code', $formattedCourseCode)->firstOrFail();
        $courseCodeWithoutDash = str_replace('-', '', $formattedCourseCode);
        $questions = Question::where('course_id', $course->id)->get();

        // Initialize question counts
        $questionCounts = [
            'total' => $questions->count(),
            1 => ['easy' => 0, 'medium' => 0, 'hard' => 0, 'total' => 0],
            2 => ['easy' => 0, 'medium' => 0, 'hard' => 0, 'total' => 0],
            3 => ['easy' => 0, 'medium' => 0, 'hard' => 0, 'total' => 0],
            4 => ['easy' => 0, 'medium' => 0, 'hard' => 0, 'total' => 0],
            5 => ['easy' => 0, 'medium' => 0, 'hard' => 0, 'total' => 0],
        ];

        // Calculate counts per task
        foreach ($questions as $question) {
            if ($question->task_number && in_array($question->task_number, [1, 2, 3, 4, 5])) {
                $task = $question->task_number;
                $difficulty = $question->difficulty;
                if (in_array($difficulty, ['easy', 'medium', 'hard'])) {
                    $questionCounts[$task][$difficulty]++;
                    $questionCounts[$task]['total']++;
                }
            }
        }

        // Log for debugging
        Log::info('questionCounts', ['counts' => $questionCounts]);

        return view('lecture.banksoal', compact('course', 'questions', 'courseCodeWithoutDash', 'questionCounts'));
    }

    public function showStudentTasks($courseCode)
    {
        $formattedCourseCode = strtoupper(preg_replace('/([a-zA-Z]+)(\d+)([a-zA-Z]*)/', '$1$2-$3', $courseCode));
        $course = Course::where('course_code', $formattedCourseCode)->firstOrFail();
        $courseCodeWithoutDash = str_replace('-', '', $formattedCourseCode);

        // Calculate average scores and tasks completed per student
        $averageScores = StudentAttempt::where('course_id', $course->id)
            ->join('students', 'student_attempts.student_id', '=', 'students.id')
            ->select(
                'students.id',
                'students.name',
                'students.nim',
                DB::raw('AVG(student_attempts.score) as average_score'),
                DB::raw('COUNT(student_attempts.id) as tasks_completed')
            )
            ->groupBy('students.id', 'students.name', 'students.nim')
            ->orderBy('students.name')
            ->get();

        // Fetch task details for each task (1-5)
        $taskDetails = [];
        foreach ([1, 2, 3, 4, 5] as $taskNumber) {
            $taskDetails[$taskNumber] = StudentAttempt::where('course_id', $course->id)
                ->where('task_number', $taskNumber)
                ->with('student')
                ->orderBy('student_id')
                ->get();
        }

        return view('lecture.tugasmahasiswa', compact(
            'course',
            'courseCodeWithoutDash',
            'averageScores',
            'taskDetails'
        ));
    }

    public function storeMaterial(Request $request, $courseCode)
    {
        try {
            $request->validate([
                'week' => 'required|integer|min:1|max:14',
                'files.*' => ['nullable', 'file', 'max:20480'],
                'video_url' => 'nullable|url',
                'is_optional' => 'nullable|boolean',
            ]);

            $formattedCourseCode = strtoupper(preg_replace('/([a-zA-Z]+)(\d+)([a-zA-Z]*)/', '$1$2-$3', $courseCode));
            $course = Course::where('course_code', $formattedCourseCode)->firstOrFail();
            $week = $request->week;

            $material = CourseMaterial::updateOrCreate(
                ['course_id' => $course->id, 'week' => $week],
                [
                    'video_url' => $request->video_url,
                    'is_optional' => $request->has('is_optional'),
                ]
            );

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
                $message .= " ($fileTypes)";
            }
            if ($request->video_url) {
                $message .= " dengan URL video";
            }

            Log::info("Material uploaded successfully", [
                'course_code' => $formattedCourseCode,
                'week' => $week
            ]);

            return response()->json(['message' => $message], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning("Validation failed for material upload", [
                'errors' => $e->errors(),
                'course_code' => $courseCode
            ]);
            return response()->json(['error' => 'Validation failed', 'messages' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error("Error uploading material: {$e->getMessage()}", [
                'course_code' => $courseCode,
                'week' => $request->week
            ]);
            return response()->json(['error' => 'Failed to upload material', 'message' => $e->getMessage()], 500);
        }
    }

    public function deleteMaterial(Request $request, $courseCode, $week, $index)
    {
        try {
            $formattedCourseCode = strtoupper(preg_replace('/([a-zA-Z]+)(\d+)([a-zA-Z]*)/', '$1$2-$3', $courseCode));
            $course = Course::where('course_code', $formattedCourseCode)->firstOrFail();
            $material = CourseMaterial::where('course_id', $course->id)->where('week', $week)->first();

            if (!$material || !isset($material->files[$index])) {
                Log::warning("Material or file not found", [
                    'course_id' => $course->id,
                    'week' => $week,
                    'index' => $index
                ]);
                return response()->json(['error' => 'File tidak tersedia'], 404);
            }

            Storage::disk('public')->delete($material->files[$index]);
            $files = $material->files;
            unset($files[$index]);
            $material->files = array_values($files);
            $material->save();

            Log::info("File deleted successfully", [
                'course_code' => $formattedCourseCode,
                'week' => $week,
                'index' => $index
            ]);

            return response()->json(['message' => 'File successfully deleted.'], 200);
        } catch (\Exception $e) {
            Log::error("Error deleting material: {$e->getMessage()}", [
                'course_code' => $courseCode,
                'week' => $week,
                'index' => $index
            ]);
            return response()->json(['error' => 'Failed to delete file', 'message' => $e->getMessage()], 500);
        }
    }

    public function createQuiz(Request $request, $courseCode)
    {
        try {
            $request->validate([
                'week' => 'required|integer|in:4,7,10,14',
                'title' => 'required|string|max:255',
            ]);

            $week = $request->week;
            $title = $request->title;
            $formattedCourseCode = strtoupper(preg_replace('/([a-zA-Z]+)(\d+)([a-zA-Z]*)/', '$1$2-$3', $courseCode));
            $course = Course::where('course_code', $formattedCourseCode)->firstOrFail();

            $taskNumber = $this->getTaskNumberFromWeek($week);
            if ($taskNumber === null) {
                return response()->json(['message' => 'Minggu tidak valid untuk tugas'], 422);
            }

            if (Quiz::where('course_code', $formattedCourseCode)->where('task_number', $taskNumber)->exists()) {
                return response()->json(['message' => 'Tugas untuk minggu ini sudah ada'], 422);
            }

            $easyQuestions = Question::where('difficulty', 'easy')
                ->where('course_id', $course->id)
                ->where('task_number', $taskNumber)
                ->inRandomOrder()
                ->take(4)
                ->get();
            $mediumQuestions = Question::where('difficulty', 'medium')
                ->where('course_id', $course->id)
                ->where('task_number', $taskNumber)
                ->inRandomOrder()
                ->take(3)
                ->get();
            $hardQuestions = Question::where('difficulty', 'hard')
                ->where('course_id', $course->id)
                ->where('task_number', $taskNumber)
                ->inRandomOrder()
                ->take(3)
                ->get();

            if ($easyQuestions->count() < 4 || $mediumQuestions->count() < 3 || $hardQuestions->count() < 3) {
                return response()->json(['message' => 'Tidak cukup soal untuk Tugas ' . $taskNumber . '. Min: 4 mudah, 3 sedang, 3 sulit'], 400);
            }

            $quiz = Quiz::create([
                'course_code' => $formattedCourseCode,
                'task_number' => $taskNumber,
                'title' => $title,
                'start_time' => now(),
                'end_time' => now()->addDays(7),
            ]);

            $questions = $easyQuestions->merge($mediumQuestions)->merge($hardQuestions);

            Log::info("Tugas dibuat", [
                'quiz_id' => $quiz->id,
                'title' => $title,
                'task_number' => $taskNumber,
                'course_id' => $course->id,
                'total_questions' => $questions->count(),
                'easy_questions' => $easyQuestions->count(),
                'medium_questions' => $mediumQuestions->count(),
                'hard_questions' => $hardQuestions->count(),
            ]);

            return redirect()->route('lecturer.course.show', ['courseCode' => $courseCode])
                ->with('success', "Tugas: {$title} dibuat dengan {$questions->count()} soal");
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning("Validation failed for quiz creation", [
                'errors' => $e->errors(),
                'course_code' => $courseCode
            ]);
            return response()->json(['error' => 'Validation failed', 'messages' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error("Error creating quiz: {$e->getMessage()}", [
                'course_code' => $courseCode,
                'week' => $request->week
            ]);
            return response()->json(['message' => 'Gagal membuat tugas'], 500);
        }
    }

    public function updateQuiz(Request $request, $courseCode, Quiz $quiz)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'end_time' => 'required|date|after:now',
            ]);

            $quiz->update([
                'title' => $request->title,
                'end_time' => $request->end_time,
            ]);

            Log::info("Quiz updated", [
                'quiz_id' => $quiz->id,
                'course_code' => $courseCode
            ]);

            return response()->json(['message' => 'Tugas berhasil diperbarui']);
        } catch (\Exception $e) {
            Log::error("Error updating quiz: {$e->getMessage()}", [
                'quiz_id' => $quiz->id,
                'course_code' => $courseCode
            ]);
            return response()->json(['message' => 'Gagal memperbarui tugas'], 500);
        }
    }

    public function deleteQuiz($courseCode, Quiz $quiz)
    {
        try {
            $quiz->delete();
            Log::info("Quiz deleted", [
                'quiz_id' => $quiz->id,
                'course_code' => $courseCode
            ]);
            return response()->json(['message' => 'Tugas berhasil dihapus']);
        } catch (\Exception $e) {
            Log::error("Error deleting quiz: {$e->getMessage()}", [
                'quiz_id' => $quiz->id,
                'course_code' => $courseCode
            ]);
            return response()->json(['message' => 'Gagal menghapus tugas'], 500);
        }
    }

    public function getStudentTasks($courseCode)
    {
        try {
            $formattedCourseCode = strtoupper(preg_replace('/([a-zA-Z]+)(\d+)([a-zA-Z]*)/', '$1$2-$3', $courseCode));
            $course = Course::where('course_code', $formattedCourseCode)->firstOrFail();

            $quizzes = Quiz::where('course_code', $formattedCourseCode)
                ->select('id', 'title', 'task_number')
                ->get()
                ->map(function ($quiz) {
                    return [
                        'quiz_id' => $quiz->id,
                        'task_number' => $quiz->task_number,
                        'title' => $quiz->title
                    ];
                })
                ->sortBy('task_number')
                ->values();

            return response()->json(['tasks' => $quizzes], 200);
        } catch (\Exception $e) {
            Log::error("Error fetching student tasks: {$e->getMessage()}", [
                'course_code' => $courseCode
            ]);
            return response()->json(['message' => 'Gagal memuat data tugas'], 500);
        }
    }

    public function getTaskDetails($courseCode, $task_number)
    {
        try {
            $formattedCourseCode = strtoupper(preg_replace('/([a-zA-Z]+)(\d+)([a-zA-Z]*)/', '$1$2-$3', $courseCode));
            $course = Course::where('course_code', $formattedCourseCode)->firstOrFail();

            if (!in_array($task_number, [1, 2, 3, 4, 5])) {
                return response()->json(['message' => 'Nomor tugas tidak valid'], 422);
            }

            $attempts = StudentAttempt::where('course_id', $course->id)
                ->where('task_number', $task_number)
                ->with('student')
                ->get()
                ->map(function ($attempt) {
                    return [
                        'quiz_id' => $attempt->quiz_id,
                        'student_name' => $attempt->student->name,
                        'student_nim' => $attempt->student->nim,
                        'score' => $attempt->score,
                        'errors_easy' => $attempt->errors_easy,
                        'errors_medium' => $attempt->errors_medium,
                        'errors_hard' => $attempt->errors_hard
                    ];
                })
                ->sortBy('student_name')
                ->values();

            return response()->json(['attempts' => $attempts], 200);
        } catch (\Exception $e) {
            Log::error("Error fetching task details: {$e->getMessage()}", [
                'course_code' => $courseCode,
                'task_number' => $task_number
            ]);
            return response()->json(['message' => 'Gagal memuat detail tugas'], 500);
        }
    }
}
