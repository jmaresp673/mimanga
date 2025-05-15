<?php

use App\Http\Controllers\AuthorController;
use App\Http\Controllers\EditionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SeriesController;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MangaSearchController;

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {

    // Manga Search Routes
    Route::get('/', [MangaSearchController::class, 'index'])->name('manga.search');
    Route::get('/search', [MangaSearchController::class, 'search'])->name('manga.search.dynamic');
    Route::post('/', [MangaSearchController::class, 'search'])->name('manga.search.perform');

    //Profile Controller
    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'edit')->name('profile.edit');
        Route::patch('/profile', 'update')->name('profile.update');
        Route::delete('/profile', 'destroy')->name('profile.destroy');
        Route::post('/profile/photo/update', 'updateProfilePhoto')->name('profile.update.photo');
    });

    //Author Routes
    Route::controller(AuthorController::class)->group(function () {
        Route::get('/authors/{anilistId}', [AuthorController::class, 'show'])->name('authors.show');
    });

    //Series Routes
    Route::controller(SeriesController::class)->group(function () {
        Route::get('/series/{anilistId}', [SeriesController::class, 'show'])->name('series.show');
        Route::post('/series/{anilistId}/add', [SeriesController::class, 'add'])->name('series.add');
        Route::post('/series/{anilistId}/remove', [SeriesController::class, 'remove'])->name('series.remove');
    });

    //Edition Routes
    Route::controller(EditionController::class)->group(function () {
        Route::get('/editions/{id}', [EditionController::class, 'show'])->name('editions.show');
    });

});


// Ruta para enviar un correo de prueba
Route::get('/test-email', function () {
    Mail::raw('Esto es un correo de prueba desde Laravel usando Gmail SMTP.', function ($message) {
        $message->to('javiermartinezespinar.jg@gmail.com')
            ->subject('Correo de prueba');
    });

    return 'Correo enviado correctamente.';
});


require __DIR__ . '/auth.php';
