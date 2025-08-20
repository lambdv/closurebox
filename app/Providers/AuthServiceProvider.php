<?php
namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class AuthServiceProvider extends ServiceProvider{
    public function register(): void{}
    public function boot(): void{
        
        //Gate::define('admin', fn(User $user) => $user->name === 'admin');
    }
}
