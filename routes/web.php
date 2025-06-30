<?php

use App\Http\Controllers\FrontendController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TelegramController;
use App\Http\Controllers\WeatherController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Api\V1\PositionController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\TaskController;

Route::prefix('api/v1')->group(function () {
    Route::apiResource('users', UserController::class);
    Route::get('positions', [PositionController::class, 'index']);
});

Route::get('/', [FrontendController::class, 'index'])->name('home');
 Route::get('/user/{user}', [FrontendController::class, 'show'])->name('show');
 Route::get('/user/{user}/edit', [FrontendController::class, 'edit'])->name('edit');
Route::post('/user/{user}', [FrontendController::class, 'update'])->name('update');
Route::delete('/user/{user}', [FrontendController::class, 'delete'])->name('delete');
Route::get('/weather', [WeatherController::class,'weather'])->name('weather');
Route::get('/telegram', [TelegramController::class,'telegram'])->name('telegram');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/tasks', [TaskController::class, 'index']);
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::get('/tasks/{id}', [TaskController::class, 'show']);
    Route::put('/tasks/{id}', [TaskController::class, 'update']);
    Route::delete('/tasks/{id}', [TaskController::class, 'destroy']);
    Route::post('/tasks/{id}/files', [TaskController::class, 'attachFile']);
});

require __DIR__.'/auth.php';
