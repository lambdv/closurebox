<?php

use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\Verified;

Route::middleware('guest')->group(function () {
    Volt::route('login', 'auth.login')
        ->name('login');

    Volt::route('register', 'auth.register')
        ->name('register');

    Volt::route('forgot-password', 'auth.forgot-password')
        ->name('password.request');

    Volt::route('reset-password/{token}', 'auth.reset-password')
        ->name('password.reset');

});

Route::middleware('auth')->group(function () {
    Volt::route('verify-email', 'auth.verify-email')
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Volt::route('confirm-password', 'auth.confirm-password')
        ->name('password.confirm');
});

Route::post('logout', App\Livewire\Actions\Logout::class)
    ->name('logout');


Route::get('/github/redirect', function () {
    return Socialite::driver('github')->redirect();
});

Route::get('/github/callback', function () {

    
    $githubUser  = Socialite::driver('github')->user();
    $name = $githubUser->getName() ?? $githubUser->getNickname();
    $email = $githubUser->getEmail();

    $existingByEmail = User::where('email', $email)->first();
    $user = $existingByEmail;
    
    if ($existingByEmail) {
        $existingByEmail->forceFill([
            'name' => $existingByEmail->name ?: $name,
            'github_id' => $githubUser->getId(),
            'email_verified_at' => now(),
        ])->save();
        $user = $existingByEmail;
    } 
    else {
        $user = User::updateOrCreate([
            'github_id' => $githubUser->getId(),
        ], [
            'name' => $name,
            'email' => $email,
            'password' => null,
            'email_verified_at' => now(),
        ]);
    }
    $user->save();
    $user->markEmailAsVerified();
    event(new Verified($user));

    Auth::login($user);
    return redirect('/dashboard');
});