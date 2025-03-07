<?php

use App\Http\Controllers\CompetitionController;
use App\Http\Controllers\CrosswordController;
use App\Models\Crossword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::get('/', function () {
    $latestCrosswords = Crossword::where('published', true)
        ->with('creator:id,name')
        ->latest()
        ->take(10)
        ->get();

    return view('home', compact('latestCrosswords'));
})->name('home');

Route::middleware(['auth'])->group(function () {
    // Crossword routes
    Route::get('/crosswords', [CrosswordController::class, 'index'])->name('crosswords.index');
    Route::get('/crosswords/create', [CrosswordController::class, 'create'])->name('crosswords.create');
    Route::get('/crosswords/{crossword}', [CrosswordController::class, 'show'])->name('crosswords.show');
    Route::get('/crosswords/{crossword}/play', [CrosswordController::class, 'play'])->name('crosswords.play');
    Route::get('/leaderboard', [CrosswordController::class, 'leaderboard'])->name('crosswords.leaderboard');

    // Competition routes
    Route::get('/competitions', [CompetitionController::class, 'index'])->name('competitions.index');
    Route::get('/competitions/create', [CompetitionController::class, 'create'])->name('competitions.create');
    Route::post('/competitions', [CompetitionController::class, 'store'])->name('competitions.store');
    Route::get('/competitions/{competition}', [CompetitionController::class, 'show'])->name('competitions.show');
    Route::get('/competitions/{competition}/play', [CompetitionController::class, 'play'])->name('competitions.play');
    Route::post('/competitions/{competition}/terminate', [CompetitionController::class, 'terminate'])
        ->name('competitions.terminate')
        ->middleware('can:update,competition');
});

// Auth routes (Laravel creates these by default)
require __DIR__.'/auth.php';
