<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');


Volt::route('/dashboard', 'pages.dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');


Volt::route('/org/{org}/servers', 'pages.org_servers')
    ->middleware(['auth', 'verified'])
    ->name('org.servers');

Volt::route('/servers', 'pages.servers')
    ->middleware(['auth', 'verified'])
    ->name('servers');

Volt::route('/servers/{id}', 'pages.serverDetails')
    ->middleware(['auth', 'verified'])
    ->name('serverDetails');




// Route::middleware(['auth', 'verified'])->group(function () {
//     Volt::route('/{org}', 'pages.dashboard')->name('dashboard');
//     Volt::route('/{org}/servers', 'pages.servers')->name('servers');
//     Volt::route('/{org}/servers/{id}', 'pages.serverDetails')->name('serverDetails');
// });


Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');


    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

Volt::route('/admin', 'pages.admin') //ONLY FOR TESTING
    ->middleware(['auth', 'verified'])
    ->name('test');

require __DIR__.'/auth.php';
