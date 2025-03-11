<?php

use App\Http\Controllers\CrosswordController;
use App\Http\Controllers\CompetitionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Services\CrosswordGenerator;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/



// Route::middleware('auth:sanctum')->group(function () {
//     // Crossword routes
//     Route::post('/crosswords/generate-preview', function (Request $request) {
//         $request->validate([
//             'words' => 'required|array|min:3',
//             'words.*.word' => 'required|string|min:2',
//             'words.*.clue' => 'required|string|min:2',
//         ]);

//         $generator = new CrosswordGenerator();

//         foreach ($request->words as $wordData) {
//             $generator->addWord($wordData['word'], $wordData['clue']);
//         }

//         $generator->optimizeGrid();

//         return response()->json([
//             'grid' => $generator->getGrid(),
//             'words' => $generator->getWords(),
//             'gridSize' => $generator->getGridSize()
//         ]);
//     });

//     Route::post('/crosswords', [CrosswordController::class, 'store']);
//     Route::get('/crosswords/{crossword}', [CrosswordController::class, 'show']);
//     Route::post('/crosswords/{crossword}/save-solution', [CrosswordController::class, 'saveSolution']);

//     // Competition routes
//     Route::get('/competitions/{competition}/crossword', function (App\Models\Competition $competition) {
//         return response()->json([
//             'crossword' => $competition->crossword,
//             'competition' => $competition
//         ]);
//     });

//     Route::post('/competitions/{competition}/save-solution', [CompetitionController::class, 'saveSolution']);
// });
