<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Feedback;
use App\Models\StudentAttempt;
use App\Models\Quiz;
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
            return redirect()->back()->with('error', 'Matkul tidak ditemukan.');
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

        $question_distribution = json_decode($feedback->question_distribution, true);
        $question_weights = json_decode($feedback->question_weights, true);
        $failed_tasks = json_decode($feedback->failed_tasks, true);
        $task_distribution = json_decode($feedback->task_distribution, true) ?? [];

        if (!$question_distribution || !$question_weights || !is_array($failed_tasks)) {
            Log::error("Invalid feedback data", [
                'studentId' => $studentId,
                'courseId' => $course->id,
                'distribution' => $feedback->question_distribution,
                'weights' => $feedback->question_weights,
                'failed_tasks' => $feedback->failed_tasks,
                'task_distribution' => $feedback->task_distribution
            ]);
            return redirect()->route('course.show', ['courseCode' => $courseCodeWithoutDash])
                ->with('error', 'Data feedback tidak valid.');
        }

        $total_questions = $question_distribution['easy'] + $question_distribution['medium'] + $question_distribution['hard'];
        if ($total_questions != 20 || $question_distribution['easy'] <= $question_distribution['medium'] || $question_distribution['medium'] < $question_distribution['hard']) {
            Log::error("Invalid question distribution in feedback", [
                'studentId' => $studentId,
                'courseId' => $course->id,
                'distribution' => $question_distribution
            ]);
            return redirect()->route('course.show', ['courseCode' => $courseCodeWithoutDash])
                ->with('error', 'Distribusi soal tidak valid.');
        }

        $attempts = StudentAttempt::where('student_id', $studentId)
            ->where('course_id', $course->id)
            ->whereIn('task_number', [1, 2, 3, 4])
            ->pluck('score', 'task_number')
            ->toArray();

        $gradeScale = [
            85 => 'A', 80 => 'A-', 75 => 'B+', 70 => 'B', 65 => 'B-',
            60 => 'C+', 55 => 'C', 45 => 'D', 0 => 'E'
        ];

        $scores = [];
        $grades = [];
        foreach ([1, 2, 3, 4] as $task) {
            $score = $attempts[$task] ?? 0;
            $scores[$task] = ['score' => $score];
            $grade = 'E';
            foreach ($gradeScale as $threshold => $letter) {
                if ($score >= $threshold) {
                    $grade = $letter;
                    break;
                }
            }
            $grades[$task] = $grade;
        }

        // Ambil materi tugas yang gagal
        $failed_task_materials = [];
        if (!empty($failed_tasks)) {
            $quizzes = Quiz::where('course_code', $course->course_code)
                ->whereIn('task_number', $failed_tasks)
                ->pluck('title', 'task_number')
                ->toArray();
            foreach ($failed_tasks as $task) {
                $failed_task_materials[$task] = $quizzes[$task] ?? "Materi Tugas $task";
            }
        }

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
            'task_distribution' => $task_distribution,
            'question_weights' => $question_weights,
            'failed_task_materials' => $failed_task_materials,
            'additionalAttempt' => $additionalAttempt ? $additionalAttempt->score : null
        ]);

        return view('matkul.feedback', [
            'course' => $course,
            'courseCode' => $courseCodeWithoutDash,
            'feedback' => $feedback,
            'question_distribution' => $question_distribution,
            'question_weights' => $question_weights,
            'task_distribution' => $task_distribution,
            'scores' => $scores,
            'grades' => $grades,
            'failed_task_materials' => $failed_task_materials,
            'additionalAttempt' => $additionalAttempt
        ]);
    }
}
