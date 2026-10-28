<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AppealController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BlockController;
use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Pages publiques
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/regles', [HomeController::class, 'rules'])->name('rules');
Route::get('/confidentialite', [HomeController::class, 'privacy'])->name('privacy');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');

/*
|--------------------------------------------------------------------------
| Authentification (invités uniquement)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/inscription', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/inscription', [AuthController::class, 'register']);
    Route::get('/connexion', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/connexion', [AuthController::class, 'login']);
});

/*
|--------------------------------------------------------------------------
| Espace Membres (authentifiés)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::post('/deconnexion', [AuthController::class, 'logout'])->name('logout');

    // Fil commun chronologique strict
    Route::get('/fil', [FeedController::class, 'index'])->name('feed');

    // Publications
    Route::post('/publications', [PostController::class, 'store'])->name('posts.store');
    Route::get('/publications/{id}', [PostController::class, 'show'])->name('posts.show');
    Route::get('/publications/{id}/modifier', [PostController::class, 'edit'])->name('posts.edit');
    Route::put('/publications/{id}', [PostController::class, 'update'])->name('posts.update');
    Route::delete('/publications/{id}', [PostController::class, 'destroy'])->name('posts.destroy');
    Route::post('/publications/{id}/commentaires/bascule', [PostController::class, 'toggleComments'])->name('posts.toggle_comments');

    // Commentaires
    Route::post('/publications/{id}/commentaires', [CommentController::class, 'store'])->name('comments.store');
    Route::delete('/commentaires/{id}', [CommentController::class, 'destroy'])->name('comments.destroy');

    // Profils
    Route::get('/membres/{handle}', [ProfileController::class, 'show'])->name('profiles.show');

    // Sauvegardes privées
    Route::get('/sauvegardes', [BookmarkController::class, 'index'])->name('bookmarks.index');
    Route::post('/publications/{id}/sauvegarde', [BookmarkController::class, 'toggle'])->name('bookmarks.toggle');

    // Blocages
    Route::post('/blocages', [BlockController::class, 'store'])->name('blocks.store');
    Route::delete('/blocages/{target_id}', [BlockController::class, 'destroy'])->name('blocks.destroy');

    // Réglages & compte
    Route::get('/reglages', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('/reglages/profil', [SettingsController::class, 'updateProfile'])->name('settings.profile');
    Route::put('/reglages/mot-de-passe', [SettingsController::class, 'updatePassword'])->name('settings.password');
    Route::delete('/reglages/compte', [SettingsController::class, 'destroyAccount'])->name('settings.account.destroy');

    // Signalements & Contestations
    Route::post('/signalements', [ReportController::class, 'store'])->name('reports.store');
    Route::get('/contestations/nouvelle', [AppealController::class, 'create'])->name('appeals.create');
    Route::post('/contestations', [AppealController::class, 'store'])->name('appeals.store');

    // Médias privés sécurisés
    Route::get('/media/{id}', [MediaController::class, 'show'])->name('media.show');
    Route::get('/avatars/{userId}', [MediaController::class, 'avatar'])->name('media.avatar');

    /*
    |--------------------------------------------------------------------------
    | Administration
    |--------------------------------------------------------------------------
    */
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('index');
        Route::get('/signalements/{id}', [AdminController::class, 'showReport'])->name('reports.show');
        Route::post('/signalements/{id}/traiter', [AdminController::class, 'resolveReport'])->name('reports.resolve');
        Route::post('/membres/{id}/moderer', [AdminController::class, 'moderateUser'])->name('users.moderate');
        Route::post('/publications/{id}/moderer', [AdminController::class, 'moderatePost'])->name('posts.moderate');
        Route::post('/contestations/{id}/traiter', [AdminController::class, 'handleAppeal'])->name('appeals.resolve');
    });
});
