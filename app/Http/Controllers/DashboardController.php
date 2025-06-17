<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CourseAssignment;
use App\Models\Course;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index()
    {
        if (Auth::guard('student')->check()) {
            $userId = Auth::guard('student')->id();
            $guard = 'student';
            $view = 'dashboard.home';
            Log::info("Fetching dashboard for student ID: {$userId}");
        } elseif (Auth::guard('lecturer')->check()) {
            $userId = Auth::guard('lecturer')->id();
            $guard = 'lecturer';
            $view = 'lecture.dashboard';
            Log::info("Fetching dashboard for lecturer ID: {$userId}");
        } else {
            Log::error("No authenticated user found for dashboard");
            return redirect()->route('login');
        }

        // Fetch courses assigned to the user via course_assignments
        $courses = CourseAssignment::where("{$guard}_id", $userId)
            ->join('courses', 'course_assignments.course_code', '=', 'courses.course_code')
            ->select('courses.course_code', 'courses.course_name')
            ->get()
            ->map(function ($course) {
                // Format course code for URL (e.g., PT540-D to pt540d)
                $course->course_id = strtolower(str_replace('-', '', $course->course_code));
                return $course;
            });

        if ($courses->isEmpty()) {
            Log::warning("No assigned courses found for {$guard} ID: {$userId}");
        } else {
            Log::info("Found assigned courses for {$guard} ID: {$userId}", ['courses' => $courses->pluck('course_code')->toArray()]);
        }

        return view($view, [
            'courses' => $courses,
        ]);
    }
}
