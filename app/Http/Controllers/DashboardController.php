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
        $studentId = Auth::guard('student')->id();
        Log::info("Fetching dashboard for student ID: {$studentId}");

        // Fetch courses assigned to the student via course_assignments
        $courses = CourseAssignment::where('student_id', $studentId)
            ->join('courses', 'course_assignments.course_code', '=', 'courses.course_code')
            ->select('courses.course_code', 'courses.course_name')
            ->get()
            ->map(function ($course) {
                // Format course code for URL (e.g., PT540-D to PT540D)
                $course->course_id = strtolower(str_replace('-', '', $course->course_code));
                return $course;
            });

        if ($courses->isEmpty()) {
            Log::warning("No assigned courses found for student ID: {$studentId}");
        } else {
            Log::info("Found assigned courses for student ID: {$studentId}", ['courses' => $courses->pluck('course_code')->toArray()]);
        }

        return view('dashboard.home', [
            'courses' => $courses,
        ]);
    }
}
