<?php
use Livewire\Volt\Component;
use Livewire\Attributes\{Layout, Title};
use App\Jobs\CreateEc2Product;
use App\Models\ProductRequest;
use App\Models\EC2Product;
use Illuminate\Support\Facades\Auth;
use App\Jobs\TestJob;
use App\Services\EC2Service;
use App\Models\PGDBProduct;
use App\Models\PGDBRole;
new
#[Layout('components.layouts.app')]
#[Title('Servers')]
class extends Component {
    public $pgdb_product;
    public $pgdb_keys = [];

    public function mount(string $instance_id)  // matches {id} in route
    {
        $this->pgdb_product = PGDBProduct::where('instance_id', $instance_id)->firstOrFail();
        
        $roles = PGDBRole::where('user_id', Auth::user()->id)
            ->where('pgdb_product_id', $this->pgdb_product->id)
            ->get();

        foreach ($roles as $role) {
            $this->pgdb_keys[] = [
                'username' => $role->username,
                'password' => $role->password,
            ];
        }
    }



    
};?>

<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Server {{$pgdb_product->instance_id}}</h1>
        <a href="/postgres-admin" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
            </svg>
            Open PostgreSQL Admin
        </a>
    </div>

    <div class="space-y-4">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Connection Strings</h2>
        @foreach($pgdb_keys as $key)
            <div class="space-y-2">
                <div class="flex items-center space-x-2">
                    <code class="flex-1 text-xs px-4 py-3 bg-gray-100 text-gray-800 rounded-md font-mono dark:bg-zinc-900 dark:text-zinc-100">
                        pgsql:host=localhost;port=5432;dbname={{$pgdb_product->instance_id}};user={{$key['username']}};password={{$key['password']}}
                    </code>
                    <button onclick="navigator.clipboard.writeText('pgsql:host=localhost;port=5432;dbname={{$pgdb_product->instance_id}};user={{$key['username']}};password={{$key['password']}}')" class="px-3 py-2 text-gray-500 hover:text-gray-700 hover:bg-gray-200 rounded-md transition-colors dark:hover:bg-zinc-800 dark:text-gray-300 dark:hover:text-white">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2z"></path>
                        </svg>
                    </button>
                </div>
                <div class="text-xs text-gray-600 dark:text-gray-300">
                    <strong>Alternative format:</strong> postgresql://{{$key['username']}}:{{$key['password']}}@localhost:5432/{{$pgdb_product->instance_id}}
                </div>
            </div>
        @endforeach
    </div>


    {{-- <table class="table-auto">
        <tbody class="gap-2">
            <tr class="border-b">
                <td class="px-4 py-2">Instance ID</td>
                <td class="px-4 py-2">{{ $serverId }}</td>
            </tr>

            <tbody class="gap-2">
                <tr class="border-b">
                    <td class="px-4 py-2">Status</td>
                    <td class="px-4 py-2">{{ $serverId }}</td>
                </tr>

            <tr class="border-b">
                <td class="px-4 py-2">Username</td>
                <td class="px-4 py-2">{{ $serverId }}</td>
            </tr>
            <tr class="border-b">
                <td class="px-4 py-2">Password</td>
                <td class="px-4 py-2">{{ $serverId }}</td>
            </tr>
        </tbody>
    </table> --}}

</div>
