<?php
use Livewire\Volt\Component;
use Livewire\Attributes\{Layout, Title};
use App\Jobs\ProcessCreateProduct;
use App\Models\ProductRequest;
new
#[Layout('components.layouts.app')]
#[Title('Servers')]
class extends Component {
    public $name;

    public function createServer(): void{
        //ProcessCreateProduct::dispatch(new \App\Services\EC2Service());
        $res = ProductRequest::create(['type' => 'ec2',]);
        ProcessCreateProduct::dispatch();
        $this->redirectRoute('servers', navigate: true);
        session()->flash('success', $res->id);

    }
};
?>

<div>
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif    <h1>Servers</h1>
    <title>Servers</title>
    <form wire:submit="createServer">
        <input type="text" wire:model="name">

        <button type="submit">Save</button>
    </form>
</div>
