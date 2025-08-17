<?php
use Livewire\Volt\Component;
use Livewire\Attributes\{Layout, Title};
use App\Jobs\CreateEc2Product;
use App\Models\ProductRequest;
use App\Models\EC2Product;
use App\Models\PGDBRole;
use App\Models\PGDBProduct;
use Illuminate\Support\Facades\Auth;
use App\Jobs\TestJob;
use App\Services\EC2Service;
use App\Services\MockEC2Service;
use App\Services\PGDBManagerService;
use App\Jobs\CreatePGDBRole;
use App\Jobs\NewPGDBProduct;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

new
#[Layout('components.layouts.app')]
#[Title('Databases')]
class extends Component {

    public $user;
    public $databases = [];
    
    public function mount(): void{
        $this->user = Auth::user();
        $this->databases = $this->getDatabases();
       
    }

    public function getDatabases(){
        // Use Eloquent relationships instead of raw SQL
        $databases = PGDBProduct::where('user_id', $this->user->id)
            ->with('PGDBRole')
            ->get();
            
        return $databases;
    }
    public $databaseName;

    public function createDatabase() {
        // Validate input before creating the request
        $this->validate([
            'databaseName' => 'required|string|min:1|max:63|regex:/^[a-zA-Z_][a-zA-Z0-9_-]*$/',
        ], [
            'databaseName.required' => 'Database name is required',
            'databaseName.min' => 'Database name must be at least 1 character',
            'databaseName.max' => 'Database name cannot exceed 63 characters',
            'databaseName.regex' => 'Database name must start with a letter or underscore and contain only letters, numbers, underscores, and hyphens',
        ]);
        
        // Additional validation for database name format
        if (strpos($this->databaseName, ' ') !== false) {
            session()->flash('error', 'Database name cannot contain spaces. Use underscores or hyphens instead.');
            return;
        }
        
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_-]*$/', $this->databaseName)) {
            session()->flash('error', 'Database name must start with a letter or underscore and contain only letters, numbers, underscores, and hyphens.');
            return;
        }
        
       

        $request = ProductRequest::create([
            'type' => 'pgdb',
            'status' => 'pending',
            'user_id' => $this->user->id,
        ]);

        dispatch(new NewPGDBProduct(
            request_id: $request->id,
            user_id: $this->user->id,
            pgdb_name: $this->databaseName,
        ));
        
        // Clear the form
        $this->reset(['databaseName']);
        
        return redirect()->route('databaseProducts')
            ->with([
                'flash' => [
                    'success' => 'Database creation request submitted successfully',
                ],
            ]);
    }       

    
};?>

<div>
    @if (session('success'))
        <div class="px-4 py-2 bg-green-500 text-white rounded-md">
            {{ session('success') }}
        </div>
    @endif
    
    @if (session('error'))
        <div class="px-4 py-2 bg-red-500 text-white rounded-md">
            {{ session('error') }}
        </div>
    @endif
    
    @if (session('info'))
        <div class="px-4 py-2 bg-blue-500 text-white rounded-md">
            {{ session('info') }}
        </div>
    @endif
    
    @if (session('warning'))
        <div class="px-4 py-2 bg-yellow-500 text-white rounded-md">
            {{ session('warning') }}
        </div>
    @endif
    
    @if ($errors->any())
        <div class="px-4 py-2 bg-red-500 text-white rounded-md">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
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
          
         
            <flux:input wire:model.defer="databaseName" wire:blur="suggestDatabaseName" label="Database Name" placeholder="Database name (no spaces)" required />
            <p class="text-xs text-gray-500">Use only letters, numbers, underscores, and hyphens. No spaces allowed.</p>
           
          
            
          
          <div class="flex justify-end gap-2">
            <flux:modal.close>
              <flux:button variant="filled">Cancel</flux:button>
            </flux:modal.close>
            <flux:button type="button" variant="primary" wire:click="createDatabase">Create</flux:button>
          </div>
        </div>
    </flux:modal>


    <div>
        <h1>Databases</h1>

        <div id="requests">
            @foreach(\App\Models\ProductRequest::where('user_id', $this->user->id)
                ->where('type', 'pgdb')
                ->get() 
            as $request)
                <div class="border rounded-lg p-4 mb-4">
                    <h2 class="text-lg font-semibold mb-2">Request: {{ $request->id }}</h2>
                    <p class="text-sm text-gray-600">Status: {{ ucfirst($request->status) }}</p>
                </div>
            @endforeach
        </div>

       
        <div>
            @if(count($databases) > 0)
                @foreach($databases as $db)
                <a href="{{ route('databaseProducts.show', $db->instance_id) }}">
                    <div class="border rounded-lg p-4 mb-4">
                        <h2 class="text-lg font-semibold mb-2">Database: {{ $db->name }}</h2>
                        <p class="text-sm text-gray-600">Instance ID: {{ $db->instance_id }}</p>
                        <p class="text-sm text-gray-600">Status: {{ ucfirst($db->status) }}</p>
                    </div>
                </a>
                @endforeach
            @else
                <p class="text-gray-500">No database roles found.</p>
            @endif
        </div>
    </div>
</div>
