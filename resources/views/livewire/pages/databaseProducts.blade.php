<?php
use Livewire\Volt\Component;
use Livewire\Attributes\{Layout, Title};
use App\Jobs\CreateEc2Product;
use App\Models\ProductRequest;
use App\Models\EC2Product;
use Illuminate\Support\Facades\Auth;
use App\Jobs\TestJob;
use App\Services\EC2Service;
use App\Services\MockEC2Service;
new
#[Layout('components.layouts.app')]
#[Title('Servers')]
class extends Component {
    public $name;
    public $servers;
    public function mount(){
        $this->name = 'test';
        $this->servers = $this->getServers();
    }

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

        $this->redirectRoute('databaseProducts');
        session()->flash('success', "Server created successfully!");
    }

    public function getServers(){
        $user = Auth::user();
        $org = $user->organizations->first();
        $servers = $org->ec2Products->fresh();

        return $servers->toArray();
    }

    public function deleteServer(string $instanceId){
        // if(\filter_var(env('INFRA_MODE', false), FILTER_VALIDATE_BOOLEAN) === true){ 
        //     (new EC2Service())->terminate([$instanceId]);
        // }
        // else {
            (new MockEC2Service())->terminate([$instanceId]);
        //}
        $server = EC2Product::where('instance_id', $instanceId)->update([
            'status' => 'terminated',
        ]);

        $this->redirectRoute('databaseProducts');
        session()->flash('success', "Database deleted successfully!");
    }
};?>

<div>
    <h1 class="text-2xl font-bold">Databases</h1>
    <div>
        @if (session('success'))
            <div class="px-4 py-2 bg-green-500 text-white rounded-md">
                {{ session('success') }}
            </div>
        @endif

        <flux:modal.trigger name="create-database">
            <flux:button>New</flux:button>
        </flux:modal.trigger>

      
        <flux:modal name="create-database" class="md:w-96">
            <div class="space-y-4">
              <flux:input wire:model.defer="name" label="Database Name" placeholder="Database name" required />
              <div class="flex justify-end gap-2">
                <flux:modal.close>
                  <flux:button variant="filled">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="button" variant="primary" wire:click="createServer">Create</flux:button>
              </div>
            </div>
        </flux:modal>

    </div>
    <br/>

    {{-- @if(Gate::allows('admin'))
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

    @endif --}}

    <br/>

    <div>
        <table class="w-full">
            <thead class="bg-gray-800 text-white text-left">
                <tr>

                </tr>
            </thead>
            <tbody class="bg-gray-700 text-white">
                @foreach(($this->servers) as $server)
                    @if($server['status'] !== 'terminated')
                        <div class="flex flex-col gap-2">
                            <tr>
                                <td>{{ $server['instance_id'] }}</td>
                                {{-- <td>{{ dd($server) }}</td> --}}


                                {{-- <td>{{ $server['organization_id'] }}</td> --}}
                                <td>{{ $server['status'] }}</td>
                                <td>{{ json_encode(new EC2Service()->describeInstance($server['instance_id'])) }}</td>
                                <td>
                                    {{-- add a comfierm model--}}
                                    <button class="px-4 py-2 bg-red-500 text-white rounded-md cursor-pointer" wire:click="deleteServer('{{ $server['instance_id'] }}')">Delete</button>
                                </td>
                            </tr>

                            {{-- <p>{{ json_encode($server['details']) }}</p> --}}
                            {{-- <p>{{ json_encode(new EC2Service()->describeInstance("i-0b874b7f30549bd93")) }}</p> --}}
                        </div>
                    @endif
                @endforeach

                @if(count($this->servers) == 0)
                    <tr>
                        <td colspan="4" class="text-center">No servers found</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

</div>
