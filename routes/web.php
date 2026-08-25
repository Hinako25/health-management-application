<?php

use App\Http\Controllers\CreateAccountController;
use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\WorkCountController;
use App\Http\Controllers\CreateGolasController;
use App\Http\Controllers\TaskController;

Route::view('/', 'welcome')->name('welcome');

Route::get('/createaccount', [CreateAccountController::class, 'create'])->name('createaccount');
Route::post('/createaccount/submit', [CreateAccountController::class, 'store'])->name('createaccount.submit');


Route::get('/login', [LoginController::class, 'create'])->name('login');
Route::post('/login/submit', [LoginController::class, 'store'])->name('login.submit');


Route::middleware('auth')->group(function () {
    Route::get('/creategoals', [CreateGolasController::class, 'create'])->name('creategoals');
    Route::post('/creategoals/submit',[CreateGolasController::class, 'store'])->name('creategoals.submit');
    Route::get('/home', [HomeController::class, 'show'])->name('home');
    Route::post('/home/completed-tasks', [TaskController::class, 'updateCompletedTasks'])->name('home.completed-tasks');
    Route::post('/home/sound-enabled', [TaskController::class, 'updateSoundEnabled'])->name('home.sound-enabled');
    Route::get('/dashboard', [DashboardController::class, 'show'])->name('dashboard');
    Route::post('/dashboard/update', [DashboardController::class, 'dashboardUpdate'])->name('dashboard.update');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});