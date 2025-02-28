<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\QuizController;

Route::get('/quiz/{courseCode}/{quizId}', [QuizController::class, 'startQuiz'])->name('kuis.start');
Route::post('/quiz/{courseCode}/{quizId}', [QuizController::class, 'submitQuiz'])->name('kuis.submit');

Route::post('/questions', [QuestionController::class, 'store']);
Route::get('/questions', [QuestionController::class, 'index']);

Route::get('/', [AuthController::class, 'index']);
Route::get('/login', [AuthController::class, 'login']);

Route::get('/dashboard', function () {
    return view('dashboard.home');
})->name('dashboard');

Route::get('/course/{courseCode}', [CourseController::class, 'show'])->name('course.show');
