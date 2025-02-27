<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'index']);
Route::get('/login', [AuthController::class, 'login']);



Route::get('/dashboard', function () {
    return view('dashboard.home');
});


Route::get('/course', function () {
    return view('matkul.course');
});
