<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\QuizController;

Route::middleware(['auth:student'])->group(function () {
    Route::get('/quiz/{courseCode}/{quizId}', [QuizController::class, 'startQuiz'])->name('kuis.start');
    Route::post('/quiz/{courseCode}/{quizId}', [QuizController::class, 'submitQuiz'])->name('kuis.submit');
});

Route::post('/questions', [QuestionController::class, 'store']);
Route::get('/questions', [QuestionController::class, 'index']);

// Redirect '/' ke '/login'
Route::get('/', function () {
    return redirect('/login');
});
Route::get('/login', [AuthController::class, 'index'])->name('login'); // Halaman login
Route::post('/login', [AuthController::class, 'login'])->name('login.process'); // Proses login
Route::post('/logout', [AuthController::class, 'logout'])->name('logout'); // Logout



Route::middleware(['auth:student'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard.home');
    })->name('dashboard');
});



Route::get('/course/{courseCode}', [CourseController::class, 'show'])->name('course.show');
