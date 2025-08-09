<?php
use Livewire\Volt\Component;
use Livewire\Attributes\{Layout, Title};
use App\Jobs\ProcessCreateProduct;
use App\Models\ProductRequest;
use App\Mail\Greeting;
use App\Services\EC2Service;

new #[Layout('components.layouts.app')] #[Title('Servers')] 
class extends Component {
    public $name;
    public $vms;
    public $num_vms;

    public function mount(): void{
        $this->vms = new EC2Service()->describeInstances();
        dd($this->vms);
        // /$this->num_vms = count($this->vms);
    }

    public function test(): void{
        $user = Auth()->user();
        Mail::to($user)
            // ->cc($moreUsers)
            // ->bcc($evenMoreUsers)
            ->queue(new Greeting($user));
        //dd("test");
    }
};?>

<div>
    <h1>test</h1>
    <button wire:click="test">send mail</button>


    <div>
        <h1>all VMS</h1>
        <p>Number of VMS: {{ $num_vms }}</p>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                </tr>
            </thead>
            {{-- <tbody>
                @foreach($vms as $vm)
                    <tr>
                        <td>{{ $vm['Instances'][0]['InstanceId'] }}</td>
                    </tr>
                @endforeach
            </tbody> --}}
        </table>
    </div>
  </div>
