<?php

use Livewire\Volt\Component;
use Livewire\Attributes\{Layout, Title};
use Illuminate\Support\Facades\Auth;

new
#[Layout('components.layouts.app')]
#[Title('Dashboard')]
class extends Component
{
    // public $user;

    // public function mount(): void {
    //     $this->user = Auth::user();
    // }

    // public function getOrgs(){
    //     return $this->user->organizations;
    // }

}; ?>

<div>
    {{-- <h1 class="text-2xl font-bold ">Welcome back {{ $this->user->name}}</h1>
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


    </div> --}}

    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
            </div>
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
            </div>
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
            </div>
        </div>
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
        </div>
    </div>
</div>
