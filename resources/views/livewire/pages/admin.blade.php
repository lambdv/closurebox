<?php
use Livewire\Volt\Component;
use Livewire\Attributes\{Layout, Title};
use App\Jobs\ProcessCreateProduct;
use App\Models\ProductRequest;
use App\Mail\Greeting;
use App\Services\EC2Service;
use App\Services\PGDBManagerService;

new #[Layout('components.layouts.app')] #[Title('Servers')] 
class extends Component {
    public $users;
    public $databases;
    public $username;
    public $password;
    public $databaseName;
    public $owner;
    
    public function mount(): void{
        $pg_manager = new PGDBManagerService();
        $this->users = $pg_manager->getUsers();
        $this->databases = $pg_manager->getAllDatabases();

        dd($this->databases);

        $this->owner = $this->users[0]->rolname;
        //dd(vars: $this->databases);
    }

    public function createUser(): void{
        $pg_manager = new PGDBManagerService();
        $pg_manager->createNewUser($this->username, $this->password);
        $this->redirect(route('admin'));
    }

    public function createDatabase(): void{
        $pg_manager = new PGDBManagerService();
        $pg_manager->createNewDatabase($this->databaseName, $this->owner);
        $this->redirect(route('admin'));
    }
};?>

<div>

    <div id="users" class="p-10">
        <h1>users</h1>
        <form wire:submit="createUser">
            <input type="text" wire:model="username" placeholder="username">
            <input type="password" wire:model="password" placeholder="password">
            <button type="submit">create user</button>
        </form>
        <table>
            <thead>
                <tr>
                    <th>id</th>
                    <th>name</th>
                    <th>super</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    <tr>
                        <td>{{ $user->oid }}</td>
                        <td>{{ $user->rolname }}</td>
                        <td>{{ $user->rolsuper }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div id="databases" class="p-10">
        <h1>databases</h1>
        <form wire:submit="createDatabase">
            <input type="text" wire:model="databaseName" placeholder="database name">
            <select wire:model="owner" class="border-2 border-gray-300 rounded-md p-2">
                @foreach($users as $user)
                    <option value="{{ $user->rolname }}">{{ $user->rolname }}</option>
                @endforeach
            </select>
            <button type="submit">create database</button>
        </form>
        <table>
            <thead>
                <tr>
                    <th>id</th>
                    <th>database name</th>
                    <th>owner</th>
                </tr>
            </thead>
            <tbody>
                @foreach($databases as $database)
                    <tr>
                        <td>{{ $database->oid }}</td>
                        <td>{{ $database->datname }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
