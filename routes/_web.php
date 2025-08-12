<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use Illuminate\Support\Str;
use App\Models\Organization;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    // Redirect any /servers* route to the user's first org's servers route
    Route::get('/servers/{any?}', function (?string $any = null) {
        $user = Auth::user();
        $firstOrg = optional($user->organizations->first());
        if (!$firstOrg) {
            abort(404);
        }
        $slug = Str::slug($firstOrg->name);
        $suffix = $any ? '/' . ltrim($any, '/') : '';
        return redirect("/org/{$slug}/servers{$suffix}");
    })->where('any', '.*')->name('servers.redirect');

    // Redirect /org to the first org the user belongs to
    Route::get('/org', function () {
        $user = Auth::user();
        $firstOrg = optional($user->organizations->first());
        if (!$firstOrg) {
            abort(404);
        }
        $slug = Str::slug($firstOrg->name);
        return redirect()->route('org.dashboard', ['org' => $slug]);
    })->name('org.redirect');

    // Group all org routes under /org/{org}
    Route::prefix('/org/{org}')
        ->where(['org' => '[a-z0-9-]+'])
        ->middleware(['org.member'])
        ->group(function () {
            // base dashboard for that org
            Volt::route('/', 'pages.dashboard')->name('org.dashboard');

            // servers list for that org
            Volt::route('/servers', 'pages.org_servers')->name('org.servers');

            // server details within that org
            Volt::route('/servers/{id}', 'pages.serverDetails')->name('org.serverDetails');
        });

        // Route::prefix('/servers')
        // ->middleware(['org.member'])
        // ->group(function () {
        //     // servers list for that org
        //     Volt::route('/servers', 'pages.org_servers')->name('org.servers');

        //     // server details within that org
        //     Volt::route('/servers/{id}', 'pages.serverDetails')->name('org.serverDetails');
        // });
});




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
