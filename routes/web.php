<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use Illuminate\Support\Str;
use App\Models\Organization;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Services\PGDBManagerService;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;



Route::get('/', function () {
    return Inertia::render('hero');
})->name('home');


Volt::route('/pricing', 'pages.stripeView')->name('stripe'); 



//console
Route::middleware(['auth', 'verified', 
//'paying'
])->group(function () {
    Volt::route('/dashboard', 'pages.dashboard')->name('dashboard');

    Volt::route('/databases', 'pages.databaseProducts')
        ->name('databaseProducts');
    Volt::route('/databases/{instance_id}', 'pages.databaseProductDetails')
        ->name('databaseProducts.show');
    //Volt::route('/keys', 'pages.databaseKeys')->name('databaseKeys');
});



Route::middleware(['auth', 'verified'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});


// Route::get('/verify-email', function (Request $request) {
//     dd($request);
// })->name('verification.notice');

// Route::get('/verify-email/{id}/{hash}', function (Request $request) {
//     dd($request);
// })->name('verification.verify');














// Route::get('/email/verify', function () {

//     dd('verify email');

// })->middleware('auth')->name('verification.notice');

 
// Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {

//     $request->fulfill();
//     dd('fulfilled');
//     //return redirect('/home');

// })->middleware(['auth', 'signed'])->name('verification.verify');


// Route::post('/email/verification-notification', function (Request $request) {

//     $request->user()->sendEmailVerificationNotification();
//     dd('sent');
//     //return back()->with('message', 'Verification link sent!');

// })->middleware(['auth', 'throttle:6,1'])->name('verification.send');




 
;



 
// Route::get('/github/callback', function () {
    
//     $githubUser  = Socialite::driver('github')->user();

//     // Derive safe fallbacks for missing GitHub fields
//     $derivedName = $githubUser->name
//         ?: ($githubUser->nickname ?? null)
//         ?: (isset($githubUser->email) ? Str::before($githubUser->email, '@') : null)
//         ?: 'GitHub User';

//     // Ensure we always have an email (GitHub users can hide email)
//     $derivedEmail = $githubUser->email ?: ($githubUser->user['email'] ?? null);
//     if (!$derivedEmail) {
//         $derivedEmail = ($githubUser->nickname ?? 'github_user')."+{$githubUser->id}@users.noreply.github.com";
//     }

//     // If an account with this email exists, link it to GitHub; otherwise create/update by github_id
//     $existingByEmail = User::where('email', $derivedEmail)->first();
//     if ($existingByEmail) {
//         $existingByEmail->forceFill([
//             'name' => $existingByEmail->name ?: $derivedName,
//             // In many schemas these columns may exist; if not, they will be ignored if not fillable
//             'github_id' => $githubUser->id,
//             'email_verified_at' => now(),
//         ])->save();
//         $user = $existingByEmail;
//     } else {
//         $user = User::updateOrCreate([
//             'github_id' => $githubUser->id,
//         ], [
//             'name' => $derivedName,
//             'email' => $derivedEmail,
//             // Satisfy non-null password column if present; model will hash automatically via cast
//             'password' => Str::password(32),
//             'email_verified_at' => now(),
//         ]);
//     }
 
//     Auth::login($user);
 
//     return redirect('/dashboard');
// });











Route::get('/subscription-checkout', function (Request $request) {
    return $request->user()
        ->newSubscription(env('STRIPE_SUBSCRIPTION_PRODUCT_ID'), env('STRIPE_SUBSCRIPTION_PRO_PRICING'))
        ->trialDays(7)
        ->allowPromotionCodes()
        ->checkout([
            'success_url' => route('home'),
            'cancel_url' => route('home'),
        ]);
})->middleware('auth')->name('stripe.checkout');
















Volt::route('/admin', 'pages.admin') //ONLY FOR TESTING
    ->middleware(['auth', 'verified'])
    ->name('admin');



Route::get('/admin/dbs', function (Request $request) {
    $pg_manager = new PGDBManagerService();
    $dbs = $pg_manager->getAllDatabases();
    dd($dbs);
});

Route::get('/admin/roles', function (Request $request) {
    $pg_manager = new PGDBManagerService();
    $roles = $pg_manager->getUsers();
    dd($roles);
});


require __DIR__.'/auth.php';