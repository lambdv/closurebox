<?php

use Livewire\Volt\Component;
use Livewire\Attributes\{Layout, Title};
use Illuminate\Support\Facades\Auth;

new
#[Layout('components.layouts.app')]
#[Title('Dashboard')]
class extends Component
{
    public $user;

    public function mount(): void {
        $this->user = Auth::user();
    }

    public function getOrgs(){
        return $this->user->organizations;
    }

}; ?>

<div>
    <h1 class="text-2xl font-bold ">Welcome back {{ $this->user->name}}</h1>
    <div class="dashboard-content">
        <h1>My Organizations</h1>
        <div class="dashboard-orgs p-5 grid grid-cols-3 gap-5">
            @foreach ($this->getOrgs() as $org)
                <div class="px-6 py-6 rounded-sm bg-gray-700 ">
                    <p>{{ $org->name }}</p>
                </div> 
            @endforeach

            </div>

        <div class="dashboard-ec2">

        </div>

        <div class="dashboard-invoices">

        </div>


        <div class="dashboard-payments">

        </div>


    </div>
</div>
