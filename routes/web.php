<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\VideoStreamController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::view('/login', 'login')->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth.video'])->group(function () {
    Route::get('/videos', [VideoController::class, 'index'])->name('videos.index');
    Route::get('/videos/{video}', [VideoController::class, 'show'])->name('videos.show');
});

Route::get('/stream/{token}', [VideoStreamController::class, 'stream'])
    ->middleware(['auth.video', 'throttle:180,1'])
    ->name('videos.stream');


