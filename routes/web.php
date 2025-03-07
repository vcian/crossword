<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\PuzzleController;
use App\Http\Controllers\Admin\ClueController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin routes
    Route::group(['prefix' => 'admin', 'as' => 'admin.'], function () {
        Route::resource('puzzles', PuzzleController::class);
        Route::resource('clues', ClueController::class);
    });

    // Game routes
    Route::get('/', [GameController::class, 'index'])->name('home');
    Route::get('/puzzles/{puzzle}', [GameController::class, 'show'])->name('puzzles.play');
    Route::post('/puzzles/{puzzle}/validate', [GameController::class, 'validateAnswer'])->name('puzzles.validate');
    Route::post('/puzzles/{puzzle}/submit', [GameController::class, 'submit'])->name('puzzles.submit');
    Route::post('/puzzles/{puzzle}/state', [GameController::class, 'updateGameState'])->name('puzzles.updateState');
    Route::get('/puzzles/{puzzle}/completion', [GameController::class, 'completion'])->name('puzzles.completion');
    Route::get('/puzzles/{puzzle}/leaderboard', [GameController::class, 'getLeaderboard'])
        ->name('puzzles.leaderboard');

    // Leaderboard routes
    Route::get('/leaderboard', [LeaderboardController::class, 'index'])->name('leaderboard');
    Route::get('/leaderboard/{puzzle}', [LeaderboardController::class, 'show'])->name('leaderboard.puzzle');
});

Route::middleware('guest')->group(function () {
    Route::post('/login/send-otp', [AuthenticatedSessionController::class, 'sendOtp'])
        ->name('login.sendOtp');
    
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->name('login');
});

require __DIR__.'/auth.php';
