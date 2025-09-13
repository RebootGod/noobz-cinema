<?php
// ========================================
// WEB ROUTES - FIXED VERSION
// ========================================
// File: routes/web.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AdminMovieController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminInviteCodeController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Home
Route::get('/', [HomeController::class, 'index'])->name('home');

// Movies (Public)
Route::get('/movies', [MovieController::class, 'index'])->name('movies.index');
Route::get('/movies/search', [MovieController::class, 'search'])->name('movies.search');
Route::get('/movie/{movie:slug}', [MovieController::class, 'show'])->name('movies.show');
Route::get('/movies/suggestions', [MovieController::class, 'searchSuggestions'])->name('movies.suggestions');
Route::get('/movies/popular-searches', [MovieController::class, 'popularSearches'])->name('movies.popular');

// Genre
Route::get('/genre/{genre:slug}', [MovieController::class, 'genre'])->name('movies.genre');

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    // Login
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    
    // Register
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
    
    // Check invite code (AJAX)
    Route::get('/check-invite-code', [RegisterController::class, 'checkInviteCode']);
});

// Logout
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Authenticated User Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'check.user.status'])->group(function () {
    // Movie Player (Members only)
    Route::get('/movie/{movie:slug}/play', [MovieController::class, 'play'])->name('movies.play');

    // Track View and Report
    Route::post('/movie/{movie}/track-view', [MovieController::class, 'trackView'])->name('movies.track-view');
    Route::post('/movie/{movie}/report', [MovieController::class, 'reportIssue'])->name('movies.report');
    
    // User Dashboard
    Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('user.dashboard');
    Route::get('/history', [HomeController::class, 'history'])->name('user.history');

    // User Profile Routes
    Route::prefix('profile')->name('profile.')->group(function () {
        // Main profile
        Route::get('/', [ProfileController::class, 'index'])->name('index');
        Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
        
        // Update profile
        Route::patch('/username', [ProfileController::class, 'updateUsername'])->name('update.username');
        Route::patch('/email', [ProfileController::class, 'updateEmail'])->name('update.email');
        Route::patch('/password', [ProfileController::class, 'updatePassword'])->name('update.password');
        
        // Watchlist
        Route::get('/watchlist', [ProfileController::class, 'watchlist'])->name('watchlist');
        Route::post('/watchlist/add/{movie}', [ProfileController::class, 'addToWatchlist'])->name('watchlist.add');
        Route::delete('/watchlist/remove/{movie}', [ProfileController::class, 'removeFromWatchlist'])->name('watchlist.remove');
        
        // History
        Route::get('/history', [ProfileController::class, 'history'])->name('history');
        Route::delete('/history/clear', [ProfileController::class, 'clearHistory'])->name('history.clear');
    });
    
    // Quick watchlist route (outside profile prefix for AJAX)
    Route::post('/watchlist/add/{movie}', [ProfileController::class, 'addToWatchlist']);
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin', 'check.user.status'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // TMDB Routes
    Route::get('/movies/tmdb', function() {
        return view('admin.movies.tmdb-search');
    })->name('movies.tmdb');
    
    Route::get('/movies/tmdb/search', [AdminMovieController::class, 'tmdbSearch'])->name('movies.tmdb.search');
    Route::get('/movies/tmdb/details', [AdminMovieController::class, 'tmdbDetails'])->name('movies.tmdb.details');
    Route::post('/movies/tmdb/import', [AdminMovieController::class, 'tmdbImport'])->name('movies.tmdb.import');
    
    // Movies Management
    Route::resource('movies', AdminMovieController::class);
    Route::post('/movies/{movie}/toggle-status', [AdminMovieController::class, 'toggleStatus'])->name('movies.toggle-status');

    // Movie Sources Management
    Route::get('/movies/{movie}/sources', [AdminMovieController::class, 'sources'])->name('movies.sources');
    Route::post('/movies/{movie}/sources', [AdminMovieController::class, 'storeSource'])->name('movies.sources.store');
    Route::post('/movies/{movie}/sources/{source}/toggle', [AdminMovieController::class, 'toggleSource'])->name('movies.sources.toggle');
    Route::delete('/movies/{movie}/sources/{source}', [AdminMovieController::class, 'destroySource'])->name('movies.sources.destroy');
    Route::post('/movies/{movie}/sources/{source}/reset-reports', [AdminMovieController::class, 'resetReports'])->name('movies.sources.reset-reports');
    Route::post('/movies/{movie}/sources/migrate', [AdminMovieController::class, 'migrateSource'])->name('movies.sources.migrate');
    
    // Users Management
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/export', [AdminUserController::class, 'export'])->name('users.export');
    Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('users.show');
    Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
    
    // User Actions
    Route::post('/users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::post('/users/{user}/toggle-ban', [AdminUserController::class, 'toggleBan'])->name('users.toggle-ban');
    Route::post('/users/{user}/reset-password', [AdminUserController::class, 'resetPassword'])->name('users.reset-password');
    Route::get('/users/generate-password', [AdminUserController::class, 'generatePassword'])->name('users.generate-password');

    // Invite Codes Management
    Route::get('/invite-codes', [AdminInviteCodeController::class, 'index'])->name('invite-codes.index');
    Route::get('/invite-codes/create', [AdminInviteCodeController::class, 'create'])->name('invite-codes.create');
    Route::post('/invite-codes', [AdminInviteCodeController::class, 'store'])->name('invite-codes.store');
    Route::post('/invite-codes/generate', [AdminInviteCodeController::class, 'generate'])->name('invite-codes.generate');
    Route::post('/invite-codes/{inviteCode}/toggle-status', [AdminInviteCodeController::class, 'toggleStatus'])->name('invite-codes.toggle-status');
    Route::delete('/invite-codes/{inviteCode}', [AdminInviteCodeController::class, 'destroy'])->name('invite-codes.destroy');

    // Broken Link Reports
    Route::get('/reports', [AdminMovieController::class, 'reports'])->name('reports.index');
    Route::patch('/reports/{report}', [AdminMovieController::class, 'updateReport'])->name('reports.update');
});