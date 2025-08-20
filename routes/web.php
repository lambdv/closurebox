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