<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\CompetitionController;
use App\Http\Controllers\Dashboard\CompetitionController as DashboardCompetitionController;
use App\Http\Controllers\CrosswordController;
use App\Http\Controllers\Dashboard\CrosswordController as DashboardCrosswordController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\RoleController;
use App\Http\Controllers\Dashboard\UserController;
use App\Http\Controllers\Dashboard\UserProfileController;
use App\Models\Crossword;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $latestCrosswords = Crossword::where('published', true)
        ->with('creator:id,name')
        ->latest()
        ->take(10)
        ->get();

    return view('home', [
        'latestCrosswords' => $latestCrosswords
    ]);
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);

    Route::get('/password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [UserProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [UserProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [UserProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [UserProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::get('/profile/solutions', [UserProfileController::class, 'solutionsHistory'])->name('profile.solutions');
    Route::get('/profile/competitions', [UserProfileController::class, 'competitionsHistory'])->name('profile.competitions');
});
Route::get('/crosswords', [CrosswordController::class, 'index'])->name('crosswords.index');

Route::middleware(['auth'])->group(function () {
    Route::get('/crosswords/create', [CrosswordController::class, 'create'])
        ->middleware('checkPermission:crosswords.create')
        ->name('crosswords.create');

    Route::post('/crosswords', [CrosswordController::class, 'store'])
        ->middleware('checkPermission:crosswords.create')
        ->name('crosswords.store');

    Route::get('/crosswords/{crossword}', [CrosswordController::class, 'show'])->name('crosswords.show');
    Route::get('/crosswords/{crossword}/play', [CrosswordController::class, 'play'])->name('crosswords.play');
    Route::get('/leaderboard', [CrosswordController::class, 'leaderboard'])->name('crosswords.leaderboard');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/competitions', [CompetitionController::class, 'index'])->name('competitions.index');

    Route::get('/competitions/create', [CompetitionController::class, 'create'])
        ->middleware('checkPermission:competitions.create')
        ->name('competitions.create');

    Route::post('/competitions', [CompetitionController::class, 'store'])
        ->middleware('checkPermission:competitions.create')
        ->name('competitions.store');

    Route::get('/competitions/{competition}', [CompetitionController::class, 'show'])->name('competitions.show');
    Route::get('/competitions/{competition}/play', [CompetitionController::class, 'play'])->name('competitions.play');

    Route::post('/competitions/{competition}/terminate', [CompetitionController::class, 'terminate'])
        ->middleware('checkPermission:competitions.terminate')
        ->name('competitions.terminate');
});

Route::middleware(['auth', 'checkRole:administrator,creator', 'checkPermission:dashboard.access'])
    ->prefix('dashboard')
    ->name('dashboard.')
    ->group(function () {
        // Dashboard home
        Route::get('/', [DashboardController::class, 'index'])->name('index');

        // User management (admin only)
        Route::middleware(['checkRole:administrator'])->group(function () {
            Route::resource('users', UserController::class);
            Route::resource('roles', RoleController::class);
        });

        // Crossword management
        Route::resource('crosswords', DashboardCrosswordController::class);
        Route::post('/crosswords/{crossword}/toggle-publish', [DashboardCrosswordController::class, 'togglePublish'])
            ->name('crosswords.toggle-publish');

        // Competition management
        Route::resource('competitions', DashboardCompetitionController::class);
        Route::post('/competitions/{competition}/terminate', [DashboardCompetitionController::class, 'terminate'])
            ->name('competitions.terminate');
    });

Route::middleware(['auth'])->prefix('api')->group(function () {
    // Crossword API routes
    Route::post('/crosswords/generate-preview', [CrosswordController::class, 'generatePreview'])
        ->middleware('checkPermission:crosswords.create');

    Route::post('/crosswords/{crossword}/save-solution', [CrosswordController::class, 'saveSolution']);

    // Competition API routes
    Route::get('/competitions/{competition}/crossword', [CompetitionController::class, 'getCrossword']);
    Route::post('/competitions/{competition}/save-solution', [CompetitionController::class, 'saveSolution']);
});

