<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

//Route::view('dashboard', 'dashboard')
//    ->middleware(['auth', 'verified'])
//    ->name('dashboard');


Volt::route('/dashboard', 'pages.dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Volt::route('/servers', 'pages.servers')
    ->middleware(['auth', 'verified'])
    ->name('servers');

Volt::route('/test', 'pages.test')
    ->middleware(['auth', 'verified'])
    ->name('test');



// Route::get('/greeting', function () {
//     $user = Auth()->user();
//     return new App\Mail\Greeting($user);
// })    ->middleware(['auth', 'verified']);


Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';
