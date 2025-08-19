<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use Illuminate\Support\Str;
use App\Models\Organization;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Services\PGDBManagerService;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');


Route::middleware(['auth', 'verified', 'paying'])->group(function () {
    Volt::route('/dashboard', 'pages.dashboard')->name('dashboard');

    Volt::route('/databases', 'pages.databaseProducts')
        ->name('databaseProducts');
    Volt::route('/databases/{instance_id}', 'pages.databaseProductDetails')
        ->name('databaseProducts.show');
    //Volt::route('/keys', 'pages.databaseKeys')->name('databaseKeys');

    Volt::route('/stripe', 'pages.stripeView')->name('stripe'); 

    // // PostgreSQL Admin routes
    Route::get('/postgres-admin', [App\Http\Controllers\PostgresAdminController::class, 'index'])->name('postgres-admin.index');
    Route::get('/postgres-admin/test-env', [App\Http\Controllers\PostgresAdminController::class, 'testEnvironment'])->name('postgres-admin.test-env');
    Route::post('/postgres-admin/connect', [App\Http\Controllers\PostgresAdminController::class, 'connect'])->name('postgres-admin.connect');
    Route::post('/postgres-admin/execute-query', [App\Http\Controllers\PostgresAdminController::class, 'executeQuery'])->name('postgres-admin.execute-query');
    Route::post('/postgres-admin/tables', [App\Http\Controllers\PostgresAdminController::class, 'getTables'])->name('postgres-admin.tables');
    Route::post('/postgres-admin/table-structure', [App\Http\Controllers\PostgresAdminController::class, 'getTableStructure'])->name('postgres-admin.table-structure');    
});


Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');


    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

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

require __DIR__.'/auth.php';