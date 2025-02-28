<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\QuizController;

Route::get('/quiz/{courseCode}', [QuizController::class, 'startQuiz'])->name('kuis.start');
Route::post('/quiz/{courseCode}', [QuizController::class, 'submitQuiz'])->name('kuis.submit');




Route::post('/questions', [QuestionController::class, 'store']);
Route::get('/questions', [QuestionController::class, 'index']);

Route::get('/', [AuthController::class, 'index']);
Route::get('/login', [AuthController::class, 'login']);



Route::get('/dashboard', function () {
    return view('dashboard.home');
});



Route::get('/course/{courseCode}', [CourseController::class, 'show']);

//Route::get('/course', function () {
//    return view('matkul.course');
//});
