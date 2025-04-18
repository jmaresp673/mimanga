<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {

    //Profile Controller
    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'edit')->name('profile.edit');
        Route::patch('/profile', 'update')->name('profile.update');
        Route::delete('/profile', 'destroy')->name('profile.destroy');
        Route::post('/profile/photo/update', 'updateProfilePhoto')->name('profile.update.photo');
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
