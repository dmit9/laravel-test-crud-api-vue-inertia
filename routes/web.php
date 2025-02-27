<?php

use App\Http\Controllers\FrontendController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Api\V1\PositionController;
use App\Http\Controllers\Api\V1\TokenController;
use App\Http\Controllers\Api\V1\UserController;

Route::prefix('api/v1')->group(function () {
    Route::apiResource('users', UserController::class);
    Route::get('positions', [PositionController::class, 'index']);
 //   Route::get('/token', [TokenController::class, 'generateToken']);
});

Route::get('/', [FrontendController::class, 'index'])->name('home');
 Route::get('/user/{user}', [FrontendController::class, 'show'])->name('show');
 Route::get('/user/{user}/edit', [FrontendController::class, 'edit'])->name('edit');
Route::post('/user/{user}', [FrontendController::class, 'update'])->name('update');
Route::delete('/user/{user}', [FrontendController::class, 'delete'])->name('delete');


Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
