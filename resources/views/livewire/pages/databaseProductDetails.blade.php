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
    <h1 class="text-2xl font-bold">Server {{$pgdb_product->instance_id}}</h1>

    @foreach($pgdb_keys as $key)
        <code class="text-xs px-5 py-2 bg-[#222222] text-gray-500 rounded-md">
            postgresql://{{$key['username']}}:{{$key['password']}}@localhost:5432/{{$pgdb_product->instance_id}}
        </code>
    @endforeach


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
