<?php
use Livewire\Volt\Component;
use Livewire\Attributes\{Layout, Title};
use App\Jobs\CreateEc2Product;
use App\Models\ProductRequest;
use App\Models\EC2Product;
use Illuminate\Support\Facades\Auth;
use App\Jobs\TestJob;
use App\Services\EC2Service;
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


        CreateEc2Product::dispatch(
            $params = [
                'name' => $this->name,
            ],
            $req->id, 
            $org->id
        );

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
        //dd($servers);
        return $servers;
    }

    public function deleteServer(string $instanceId){
        new EC2Service()->terminate([$instanceId]);
        $server = EC2Product::where('instance_id', $instanceId)->first();
        $server->delete();
        $this->redirectRoute('servers');
        session()->flash('success', "Server deleted successfully!");
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
    
    <br/>
    <br/>
    <br/>
    <br/>
    <br/>
    <br/>
    <br/>
    <br/>

    <div>
        <table class="w-full">
            <thead class="bg-gray-800 text-white text-left">
                <tr>

                </tr>
            </thead>
            <tbody class="bg-gray-700 text-white">
                @foreach($this->getServers() as $server)
                <div class="flex flex-col gap-2">
                    <tr>
                        <td>{{ $server['instance_id'] }}</td>
                        {{-- <td>{{ dd($server) }}</td> --}}

                        
                        {{-- <td>{{ $server['organization_id'] }}</td> --}}
                        <td>{{ $server['status'] }}</td>
                        <td>{{ json_encode(new EC2Service()->describeInstance($server['instance_id'])) }}</td>
                        <td>
                            <button class="px-4 py-2 bg-red-500 text-white rounded-md cursor-pointer" wire:click="deleteServer('{{ $server['instance_id'] }}')">Delete</button>
                        </td>
                    </tr>

                    {{-- <p>{{ json_encode($server['details']) }}</p> --}}
                    {{-- <p>{{ json_encode(new EC2Service()->describeInstance("i-0b874b7f30549bd93")) }}</p> --}}
                </div>
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
