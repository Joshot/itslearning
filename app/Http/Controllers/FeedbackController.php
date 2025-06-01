<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Feedback;
use App\Models\StudentAttempt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FeedbackController extends Controller
{
    public function show($courseCode)
    {
        $studentId = Auth::guard('student')->id();
        $formattedCourseCode = strtoupper(preg_replace('/([a-zA-Z]+)(\d+)([a-zA-Z]*)/', '$1$2-$3', $courseCode));

        $course = Course::where('course_code', $formattedCourseCode)->first();
        if (!$course) {
            Log::error("Course not found", ['courseCode' => $formattedCourseCode]);
            return redirect()->back()->with('error', 'Kursus tidak ditemukan.');
        }

        $courseCodeWithoutDash = strtolower(str_replace('-', '', $course->course_code));

        $feedback = Feedback::where('student_id', $studentId)
            ->where('course_id', $course->id)
            ->first();

        if (!$feedback) {
            Log::error("Feedback not found", ['studentId' => $studentId, 'courseId' => $course->id]);
            return redirect()->route('course.show', ['courseCode' => $courseCodeWithoutDash])
                ->with('error', 'Feedback belum tersedia.');
        }

        // Decode JSON fields
        $question_distribution = json_decode($feedback->question_distribution, true) ?? ['easy' => 9, 'medium' => 6, 'hard' => 5];
        $question_weights = json_decode($feedback->question_weights, true) ?? ['easy' => 2.8, 'medium' => 5.6, 'hard' => 8.3];
        $failed_tasks = json_decode($feedback->failed_tasks, true) ?? [];
        $scores = [];
        $grades = [];

        // Ambil skor dan grade dari student_attempts
        $attempts = StudentAttempt::where('student_id', $studentId)
            ->where('course_id', $course->id)
            ->whereIn('task_number', [1, 2, 3, 4])
            ->pluck('score', 'task_number')
            ->toArray();

        $gradeScale = [
            85 => 'A', 80 => 'A-', 75 => 'B+', 70 => 'B', 65 => 'B-',
            60 => 'C+', 55 => 'C', 45 => 'D', 0 => 'E'
        ];

        foreach ([1, 2, 3, 4] as $task) {
            $score = $attempts[$task] ?? 0;
            $scores[$task] = $score;
            $grade = 'E';
            foreach ($gradeScale as $threshold => $letter) {
                if ($score >= $threshold) {
                    $grade = $letter;
                    break;
                }
            }
            $grades[$task] = $grade;
        }

        // Cek tugas tambahan
        $additionalAttempt = StudentAttempt::where('student_id', $studentId)
            ->where('course_id', $course->id)
            ->where('task_number', 5)
            ->first();

        Log::info("Feedback page accessed", [
            'studentId' => $studentId,
            'courseId' => $course->id,
            'feedbackId' => $feedback->id,
            'scores' => $scores,
            'grades' => $grades,
            'additionalAttempt' => $additionalAttempt ? $additionalAttempt->score : null
        ]);

        return view('matkul.feedback', [
            'course' => $course,
            'courseCode' => $courseCodeWithoutDash,
            'feedback' => $feedback,
            'question_distribution' => $question_distribution,
            'question_weights' => $question_weights,
            'scores' => $scores,
            'grades' => $grades,
            'additionalAttempt' => $additionalAttempt
        ]);
    }
}
