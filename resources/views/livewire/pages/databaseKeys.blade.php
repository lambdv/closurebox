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
use App\Services\PGDBManagerService;

new
#[Layout('components.layouts.app')]
#[Title('Databases')]
class extends Component {

    public $user;
    public $keys;
    public $keyName = "";
    public $keyPassword = "";

    public function mount(): void{
        $this->user = Auth::user();
        $this->keys = $this->getKeys();
    }

    public function getKeys(): void{
        $this->keys = DB::table('pgdb_roles')->where('user_id', $this->user->id)->get();
    }

    public function createKey(): void{
        $user_id = $this->user->id;
        $name = $this->keyName;
        $password = $this->keyPassword;
        
        dispatch(function () use ($name, $password, $user_id) {
            try{
                $pg_manager = new PGDBManagerService();
                $pgdb_name = $user_id . '_' . $name;
                $pg_manager->createNewUser($pgdb_name, password: $password);
                
                DB::table('pgdb_roles')->insert([
                    'user_id' => $user_id,
                    'key_name' => $name,
                    'key_password' => $password,
                    'pgdb_name' => $pgdb_name,
                ]);
            }
            catch (Exception $e) {
                Log::error("Error creating key: " . $e->getMessage());
                throw $e;
            }
        })->name('create-key');
        
        
    }

};?>

<div>
    <div>
        <h1>Database Keys for {{ $user->name }}</h1>
        <flux:modal.trigger name="create-key">
            <flux:button>New</flux:button>
        </flux:modal.trigger>

        <flux:modal name="create-key" class="md:w-96">
            <div class="space-y-4">
            <flux:input wire:model.defer="keyName" label="Key Name" placeholder="Key Name" required />
            
            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="filled">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="button" variant="primary" wire:click="createKey">Create</flux:button>
            </div>
            </div>
        </flux:modal>
    
    </div>

    <div>
        @if(!$keys)
            <p>No keys found</p>
        @else
           @foreach($keys as $key)
                <div>
                    <h1>{{ $key->key }}</h1>
                </div>
           @endforeach
        @endif
    </div>



</div>
