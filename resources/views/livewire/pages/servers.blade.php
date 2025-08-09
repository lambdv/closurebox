<?php
use Livewire\Volt\Component;
use Livewire\Attributes\{Layout, Title};
use App\Jobs\ProcessCreateProduct;
use App\Models\ProductRequest;
use Illuminate\Support\Facades\Auth;
use App\Jobs\TestJob;

new
#[Layout('components.layouts.app')]
#[Title('Servers')]
class extends Component {
    public $name;

    public function createServer(): void{
        $user = Auth::user();
        $org = $user->organizations->first(); // Get the first organization
        
        if (!$org) {
            session()->flash('error', 'No organization found. Please create an organization first.');
            return;
        }
        
        $req = ProductRequest::create([
            'type' => 'ec2',
            'organization_id' => $org->id,
        ]); //optimistically create the request

        // // Run synchronously so the server appears immediately in the table
        // (new ProcessCreateProduct($req->id, $org->id))->handle();
        ProcessCreateProduct::dispatch($req->id, $org->id);

        $this->redirectRoute('servers');
        session()->flash('success', "Server created successfully!");
    }

    public function getServers(){
        $user = Auth::user();
        $orgs = $user->organizations;
        $servers = [];
        foreach($orgs as $org){
            $orgServers = $org->ec2Products->toArray();
            foreach($orgServers as $server){
                $server['organization_id'] = $org->name;
                $servers[] = $server;
            }
        }
        //reduce so only unique servers are returned
        $servers = array_unique($servers, SORT_REGULAR);
        return $servers;
    }
};?>

<div>
    <h1 class="text-2xl font-bold">Servers</h1>
    <div>
        @if (session('success'))
            <div class="px-4 py-2 bg-green-500 text-white rounded-md">
                {{ session('success') }}
            </div>
        @endif  
    
        <div class="w-1/4 px-4 py-4 bg-gray-700 rounded-md">
            <h2 class="text-lg font-bold">Create a new Server!</h2>
            <form wire:submit="createServer"
                class="flex flex-col gap-4"
            >
                <input type="text" wire:model="name" class="px-4 py-2 border border-gray-300 rounded-md" placeholder="Server Name">
            
                <button type="submit"
                    class="px-4 py-2 bg-blue-500 text-white rounded-md cursor-pointer"
                >Create</button>
            </form>
        </div>
    
    </div>
    <br/>

    @if(Gate::allows('admin'))
        <div>
            <table class="w-full">
                <thead class="bg-gray-800 text-white">
                    <tr>
                        <th>ID</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody class="bg-gray-700 text-white">
                    @foreach(ProductRequest::all() as $request)
                        <tr>
                            <td>{{ $request->id }}</td>
                            <td>{{ $request->status }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    @endif


    <div>
        <table class="w-full">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th>ID</th>
                    <th>Belongs to</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody class="bg-gray-700 text-white">
                @foreach($this->getServers() as $server)
                    <tr>
                        <td>{{ $server['id'] }}</td>
                        <td>{{ $server['organization_id'] }}</td>
                        <td>{{ $server['status'] }}</td>

                        <td>
                            <button class="px-4 py-2 bg-red-500 text-white rounded-md cursor-pointer">Delete</button>
                        </td>
                    </tr>
                @endforeach

                @if(count($this->getServers()) == 0)
                    <tr>
                        <td colspan="4" class="text-center">No servers found</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

</div>
