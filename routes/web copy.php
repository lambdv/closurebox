<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');





Route::middleware(['auth', 'verified'])->group(function () {
    Volt::route('/dashboard', 'pages.dashboard')->name('dashboard');

    Volt::route('/databases', 'pages.databaseProducts')->name('databaseProducts');
    Volt::route('/databases/{id}', 'pages.databaseProductDetails')->name('databaseProductDetails');
});


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