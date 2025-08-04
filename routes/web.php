<?php
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\EC2ProductController;
use App\Models\OrganizationMember;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {


        Inertia::share([
            'organizations' => fn () => 
                request()->user()->organizations()
                ->select('organizations.id', 'organizations.name')
                ->get()
        ]);
 

    
    Route::get('dashboard', function () {
        return Inertia::render('dashboard/page');
    })->name('dashboard');

    Route::get('servers', [EC2ProductController::class, 'viewServers'])->name('servers');
    Route::post('servers/create', [EC2ProductController::class, 'createServer'])->name('servers.create');
});




require __DIR__.'/settings.php';
require __DIR__.'/auth.php';