<?php

use App\Filament\Pages\CourseAssignments;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\LecturerAuthController;
use App\Http\Controllers\LecturerCourseController;
use App\Http\Controllers\ProfileController;
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
    Route::get('/profile/student', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile/student', [ProfileController::class, 'update'])->name('profile.update');
});

Route::middleware(['auth:lecturer'])->group(function () {
    Route::get('/lecturer/dashboard', function () {
        return view('lecture.dashboard');
    })->name('lecturer.dashboard');
    Route::get('/lecturer/course/{courseCode}', [LecturerCourseController::class, 'show'])->name('lecturer.course.show');
    Route::post('/lecturer/course/{courseCode}/material', [LecturerCourseController::class, 'storeMaterial'])->name('lecturer.course.material.store');
    Route::delete('/lecturer/course/{courseCode}/material/{week}/{index}', [LecturerCourseController::class, 'deleteMaterial'])->name('lecturer.course.material.delete');
    Route::post('/lecturer/course/{courseCode}/quiz', [LecturerCourseController::class, 'createQuiz'])->name('lecturer.course.quiz.create');
    Route::patch('/lecturer/course/{courseCode}/quiz/{quiz}', [LecturerCourseController::class, 'updateQuiz'])->name('lecturer.course.quiz.update');
    Route::delete('/lecturer/course/{courseCode}/quiz/{quiz}', [LecturerCourseController::class, 'deleteQuiz'])->name('lecturer.course.quiz.delete');
    Route::get('/lecturer/course/{courseCode}/bank-soal', [LecturerCourseController::class, 'showBankSoal'])->name('lecture.banksoal');
    Route::get('/profile/lecturer', [ProfileController::class, 'edit'])->name('profile.edit.lecturer');
    Route::patch('/profile/lecturer', [ProfileController::class, 'update'])->name('profile.update.lecturer');
});

Route::post('/questions', [QuestionController::class, 'store'])->name('questions.store');
Route::get('/questions', [QuestionController::class, 'index'])->name('questions.index');

Route::get('/admin/course-assignments/{course_code}', CourseAssignments::class)
    ->name('filament.admin.pages.course-assignments')
    ->middleware(['auth', 'verified']);
