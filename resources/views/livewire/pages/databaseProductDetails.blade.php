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
    public $serverId;

    public function mount(string $id)  // matches {id} in route
    {
        $this->serverId = $id;
        // Example: fetch the server from DB
        // $this->server = EC2Product::where('instance_id', $id)->firstOrFail();
    }

//    public function deleteServer(string $instanceId){
//        (new EC2Service())->terminate([$instanceId]);
//        $server = EC2Product::where('instance_id', $instanceId)->update([
//            'status' => 'terminated',
//        ]);
//
//        $this->redirectRoute('servers');
//        session()->flash('success', "Server deleted successfully!");
//    }
};?>

<div>
    <h1 class="text-2xl font-bold">Server {{$serverId}}</h1>


</div>
