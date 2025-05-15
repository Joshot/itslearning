<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\LecturerAuthController;
use App\Http\Controllers\LecturerCourseController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\QuizController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
})->name('home');

Route::get('/login', [AuthController::class, 'index'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.process');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/lecturer/login', [LecturerAuthController::class, 'showLoginForm'])->name('lecturer.login');
Route::post('/lecturer/login', [LecturerAuthController::class, 'login'])->name('lecturer.login.process');
Route::post('/lecturer/logout', [LecturerAuthController::class, 'logout'])->name('lecturer.logout');

Route::middleware(['auth:student'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard.home');
    })->name('dashboard');
    Route::get('/course/{courseCode}', [CourseController::class, 'show'])->name('course.show');
    Route::get('/quiz/{courseCode}/{quizId}', [QuizController::class, 'startQuiz'])->name('kuis.start');
    Route::post('/quiz/{courseCode}/{quizId}', [QuizController::class, 'submitQuiz'])->name('kuis.submit');
});

Route::middleware(['auth:lecturer'])->group(function () {
    Route::get('/lecturer/dashboard', function () {
        return view('lecture.dashboard');
    })->name('lecturer.dashboard');
    Route::get('/lecturer/course/{courseCode}', [LecturerCourseController::class, 'show'])->name('lecturer.course.show');
    Route::post('/lecturer/course/{courseCode}/material', [LecturerCourseController::class, 'storeMaterial'])->name('lecturer.course.material.store');
});

Route::post('/questions', [QuestionController::class, 'store'])->name('questions.store');
Route::get('/questions', [QuestionController::class, 'index'])->name('questions.index');
