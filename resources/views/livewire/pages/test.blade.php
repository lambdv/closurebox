<?php
use Livewire\Volt\Component;
use Livewire\Attributes\{Layout, Title};
use App\Jobs\ProcessCreateProduct;
use App\Models\ProductRequest;
use App\Mail\Greeting;


new #[Layout('components.layouts.app')] #[Title('Servers')] class extends Component {
    public $name;

    public function test(): void{
        $user = Auth()->user();
        Mail::to($user)
            // ->cc($moreUsers)
            // ->bcc($evenMoreUsers)
            ->queue(new Greeting($user));
        //dd("test");
    }
};
?>

<div>
    <h1>test</h1>
    <button wire:click="test">test</button>
</div>
