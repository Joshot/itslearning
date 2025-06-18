<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CourseAssignment;
use App\Models\Course;
use App\Models\Quiz;
use App\Models\StudentAttempt;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        if (Auth::guard('student')->check()) {
            $userId = Auth::guard('student')->id();
            $guard = 'student';
            $view = 'dashboard.home';
            Log::info("Fetching dashboard for student ID: {$userId}");

            // Fetch courses assigned to the student
            $courses = CourseAssignment::where('student_id', $userId)
                ->join('courses', 'course_assignments.course_code', '=', 'courses.course_code')
                ->select('courses.course_code', 'courses.course_name')
                ->get()
                ->map(function ($course) {
                    $course->course_id = strtolower(str_replace('-', '', $course->course_code));
                    return $course;
                });

            // Fetch quizzes for assigned courses with future or current end_time, not attempted by student, and task_number != 5
            $quizzes = Quiz::whereIn('course_code', $courses->pluck('course_code'))
                ->where('end_time', '>=', Carbon::now('Asia/Jakarta'))
                ->where('task_number', '!=', 5)
                ->whereNotExists(function ($query) use ($userId) {
                    $query->select('id')
                        ->from('student_attempts')
                        ->whereColumn('student_attempts.quiz_id', 'quizzes.id')
                        ->where('student_attempts.student_id', $userId);
                })
                ->select('course_code', 'task_number', 'title', 'end_time')
                ->get()
                ->map(function ($quiz) use ($courses) {
                    $quiz->course_name = $courses->firstWhere('course_code', $quiz->course_code)->course_name ?? 'Unknown';
                    $quiz->days_remaining = Carbon::today('Asia/Jakarta')->diffInDays($quiz->end_time, false);
                    Log::debug("Quiz end_time for {$quiz->title}: {$quiz->end_time}, Days remaining: {$quiz->days_remaining}");
                    return $quiz;
                })
                ->sortBy('end_time');

            if ($courses->isEmpty()) {
                Log::warning("No assigned courses found for student ID: {$userId}");
            } else {
                Log::info("Found assigned courses for student ID: {$userId}", ['courses' => $courses->pluck('course_code')->toArray()]);
            }

            if ($quizzes->isEmpty()) {
                Log::warning("No upcoming unattempted quizzes (excluding task_number 5) found for student ID: {$userId}");
            } else {
                Log::info("Found upcoming unattempted quizzes (excluding task_number 5) for student ID: {$userId}", ['quizzes' => $quizzes->pluck('title')->toArray()]);
            }

            return view($view, [
                'courses' => $courses,
                'quizzes' => $quizzes,
            ]);
        } elseif (Auth::guard('lecturer')->check()) {
            $userId = Auth::guard('lecturer')->id();
            $guard = 'lecturer';
            $view = 'lecture.dashboard';
            Log::info("Fetching dashboard for lecturer ID: {$userId}");

            // Fetch courses assigned to the lecturer
            $courses = CourseAssignment::where('lecturer_id', $userId)
                ->join('courses', 'course_assignments.course_code', '=', 'courses.course_code')
                ->select('courses.course_code', 'courses.course_name')
                ->get()
                ->map(function ($course) {
                    $course->course_id = strtolower(str_replace('-', '', $course->course_code));
                    return $course;
                });

            // Fetch quizzes for assigned courses with future or current end_time
            $quizzes = Quiz::whereIn('course_code', $courses->pluck('course_code'))
                ->where('end_time', '>=', Carbon::now('Asia/Jakarta'))
                ->select('course_code', 'task_number', 'title', 'end_time')
                ->get()
                ->map(function ($quiz) use ($courses) {
                    $quiz->course_name = $courses->firstWhere('course_code', $quiz->course_code)->course_name ?? 'Unknown';
                    $quiz->days_remaining = Carbon::today('Asia/Jakarta')->diffInDays($quiz->end_time, false);
                    Log::debug("Quiz end_time for {$quiz->title}: {$quiz->end_time}, Days remaining: {$quiz->days_remaining}");
                    return $quiz;
                })
                ->sortBy('end_time');

            if ($courses->isEmpty()) {
                Log::warning("No assigned courses found for lecturer ID: {$userId}");
            } else {
                Log::info("Found assigned courses for lecturer ID: {$userId}", ['courses' => $courses->pluck('course_code')->toArray()]);
            }

            if ($quizzes->isEmpty()) {
                Log::warning("No upcoming quizzes found for lecturer ID: {$userId}");
            } else {
                Log::info("Found upcoming quizzes for lecturer ID: {$userId}", ['quizzes' => $quizzes->pluck('title')->toArray()]);
            }

            return view($view, [
                'courses' => $courses,
                'quizzes' => $quizzes,
            ]);
        } else {
            Log::error("No authenticated user found for dashboard");
            return redirect()->route('login');
        }
    }
}
