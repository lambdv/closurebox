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
#[Title('Databases')]
class extends Component {
    public $name;
    public $databases;


    public function mount(){
        $this->name = 'test';
        $this->databases = $this->getDatabases();
        $this ->databases = $this->getDatabases();
    }

    public function createDatabase(){
        
    }

    public function deleteDatabase($instanceId){

    }

    public function getDatabases(){
        return [
            [
                'id' => 1,
                'name' => 'test',
                'db_name' => 'test',
                'status' => 'active',
           ],
        ];
    }


};?>

<div>
    @if (session('success'))
        <div class="px-4 py-2 bg-green-500 text-white rounded-md">
            {{ session('success') }}
        </div>
    @endif
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold">Databases</h1>
        <flux:modal.trigger name="create-database">
            <flux:button>New</flux:button>
        </flux:modal.trigger>
    </div>

    <flux:modal name="create-database" class="md:w-96">
        <div class="space-y-4">
          <flux:input wire:model.defer="name" label="Database Name" placeholder="Database name" required />
          <div class="flex justify-end gap-2">
            <flux:modal.close>
              <flux:button variant="filled">Cancel</flux:button>
            </flux:modal.close>
            <flux:button type="button" variant="primary" wire:click="createDatabase">Create</flux:button>
          </div>
        </div>
    </flux:modal>
    
    <br/>

    <div>
        <table class="w-full">
            <thead class="bg-gray-800 text-white text-left">
            <tr>
                                <td>name</td>
                                <td>db_name</td>
                                <td>status</td>
                                <td>actions</td>

                            </tr>
            </thead>
            <tbody class="bg-gray-700 text-white">
                @foreach(($this->databases) as $database)
                    @if($database['status'] !== 'terminated')
                        <div class="flex flex-col gap-2">
                            <tr>
                                <td>{{ $database['name'] }}</td>
                                <td>{{ $database['db_name'] }}</td>
                                <td>{{ $database['status'] }}</td>
                                <td>
                                    
                                </td>

                            </tr>
                        </div>
                    @endif
                @endforeach

                @if(count($this->databases) == 0)
                    <tr>
                        <td colspan="4" class="text-center">No databases found</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

</div>
