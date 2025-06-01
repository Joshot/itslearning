<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseMaterial;
use App\Models\Quiz;
use App\Models\StudentAttempt;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CourseController extends Controller
{
    public function show($courseCode)
    {
        Log::info("Accessing course page", ['courseCode' => $courseCode]);

        $formattedCourseCode = strtoupper(preg_replace('/([a-zA-Z]+)(\d+)([a-zA-Z]*)/', '$1$2-$3', $courseCode));
        $course = Course::where('course_code', $formattedCourseCode)->first();

        if (!$course) {
            Log::error("Course not found for code: {$formattedCourseCode}");
            return redirect()->route('dashboard')->with('error', 'Course not found');
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

        $courseCodeWithoutDash = str_replace('-', '', $formattedCourseCode);

        $quizzes = Quiz::where('course_code', $formattedCourseCode)->get();
        $availableQuizzes = [];
        foreach ($quizzes as $quiz) {
            $availableQuizzes[$quiz->task_number] = $quiz->id;
        }

        $studentId = Auth::guard('student')->id();
        $quizScores = [];
        for ($i = 1; $i <= 4; $i++) {
            if (!isset($availableQuizzes[$i])) {
                $quizScores[$i] = null;
                continue;
            }

            $quizId = $availableQuizzes[$i];
            $attempt = StudentAttempt::where('student_id', $studentId)
                ->where('quiz_id', $quizId)
                ->where('course_id', $course->id)
                ->where('task_number', $i)
                ->first();

            $quizScores[$i] = $attempt ? $attempt->score : null;
        }

        // Cek feedback untuk alert
        $feedback = Feedback::where('student_id', $studentId)
            ->where('course_id', $course->id)
            ->first();

        Log::info("Course data for {$formattedCourseCode}", [
            'courseName' => $course->course_name,
            'formattedCourseCode' => $formattedCourseCode,
            'materials' => $materials,
            'quizScores' => $quizScores,
            'availableQuizzes' => $availableQuizzes,
            'feedbackExists' => !is_null($feedback),
        ]);

        return view('matkul.course', compact(
            'courseCodeWithoutDash',
            'formattedCourseCode',
            'course',
            'materials',
            'quizScores',
            'availableQuizzes',
            'feedback'
        ));
    }
}
