<?php

use App\Http\Controllers\CreateAccountController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\CreateGoalsController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\WorkController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('welcome');

Route::get('/createaccount', [CreateAccountController::class, 'create'])->name('createaccount');
Route::post('/createaccount/submit', [CreateAccountController::class, 'store'])->name('createaccount.submit');


Route::get('/login', [LoginController::class, 'create'])->name('login');
Route::post('/login/submit', [LoginController::class, 'store'])->name('login.submit');


Route::middleware('auth')->group(function () {
    Route::get('/creategoals', [CreateGoalsController::class, 'create'])->name('creategoals');
    Route::post('/creategoals/submit', [CreateGoalsController::class, 'store'])->name('creategoals.submit');
    Route::get('/home', [HomeController::class, 'show'])->name('home');
    Route::post('/home/work', [WorkController::class, 'work'])->name('home.work');
    Route::post('/home/completed-tasks', [TaskController::class, 'completedTasks'])->name('home.completed-tasks');
    Route::post('/home/sound-enabled', [WorkController::class, 'updateSoundEnabled'])->name('home.sound-enabled');
    Route::get('/dashboard', [DashboardController::class, 'show'])->name('dashboard');
    Route::post('/dashboard/update', [DashboardController::class, 'dashboardUpdate'])->name('dashboard.update');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});