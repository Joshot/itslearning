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

        // Check if student has completed all tasks
        $attempts = StudentAttempt::where('student_id', $studentId)
            ->where('course_id', $course->id)
            ->whereIn('task_number', [1, 2, 3, 4])
            ->pluck('score', 'task_number')
            ->toArray();

        $feedback = Feedback::where('student_id', $studentId)
            ->where('course_id', $course->id)
            ->first();

        // Initialize variables
        $question_distribution = [];
        $question_weights = [];
        $failed_tasks = [];
        $task_distribution = [];
        $isPerfectScore = false;
        $popup = null;

        if ($feedback) {
            $question_distribution = json_decode($feedback->question_distribution, true) ?? [];
            $question_weights = json_decode($feedback->question_weights, true) ?? [];
            $failed_tasks = json_decode($feedback->failed_tasks, true) ?? [];
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

            $total_questions = array_sum($question_distribution);
            if ($total_questions > 0 && ($total_questions != 20 || $question_distribution['easy'] <= $question_distribution['medium'] || $question_distribution['medium'] < $question_distribution['hard'])) {
                Log::error("Invalid question distribution in feedback", [
                    'studentId' => $studentId,
                    'courseId' => $course->id,
                    'distribution' => $question_distribution
                ]);
                return redirect()->route('course.show', ['courseCode' => $courseCodeWithoutDash])
                    ->with('error', 'Distribusi soal tidak valid.');
            }

            // Determine pop-up and perfect score status
            if (empty($failed_tasks) && $feedback->average_score >= 85) {
                $isPerfectScore = true;
                $popup = [
                    'title' => 'Nilai Sempurna!',
                    'text' => 'Nilai kamu sudah sempurna! Kamu tidak bisa mengerjakan Tugas Tambahan.',
                    'icon' => 'success'
                ];
            } elseif (empty($failed_tasks)) {
                $popup = [
                    'title' => 'Semua Tugas Lulus!',
                    'text' => 'Kamu lulus semua tugas tetapi rata-rata di bawah A. Kamu bisa mengerjakan Tugas Tambahan untuk meningkatkan nilai.',
                    'icon' => 'info'
                ];
            } else {
                $popup = [
                    'title' => 'Tugas Gagal',
                    'text' => 'Kamu memiliki tugas yang gagal. Kerjakan Tugas Tambahan untuk memperbaiki nilai.',
                    'icon' => 'warning'
                ];
            }
        } elseif (count($attempts) < 4) {
            $popup = [
                'title' => 'Feedback Belum Tersedia',
                'text' => 'Selesaikan semua tugas (Tugas 1–4) terlebih dahulu untuk melihat feedback.',
                'icon' => 'info'
            ];
        } else {
            Log::error("Feedback not found", ['studentId' => $studentId, 'courseId' => $course->id]);
            $popup = [
                'title' => 'Feedback Belum Tersedia',
                'text' => 'Feedback belum tersedia. Hubungi admin jika semua tugas sudah selesai.',
                'icon' => 'error'
            ];
        }

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

        // Get failed task materials
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

        $additionalAttempt = null;
        if ($feedback && $feedback->additional_quiz_id) {
            $additionalAttempt = StudentAttempt::where('student_id', $studentId)
                ->where('course_id', $course->id)
                ->where('task_number', 5)
                ->first();
        }

        Log::info("Feedback page accessed", [
            'studentId' => $studentId,
            'courseId' => $course->id,
            'feedbackId' => $feedback ? $feedback->id : null,
            'scores' => $scores,
            'grades' => $grades,
            'task_distribution' => $task_distribution,
            'question_weights' => $question_weights,
            'failed_task_materials' => $failed_task_materials,
            'additionalAttempt' => $additionalAttempt ? $additionalAttempt->score : null,
            'isPerfectScore' => $isPerfectScore,
            'popup' => $popup
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
            'additionalAttempt' => $additionalAttempt,
            'isPerfectScore' => $isPerfectScore,
            'popup' => $popup
        ]);
    }
}
