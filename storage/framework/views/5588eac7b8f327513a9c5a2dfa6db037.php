<?php

use Livewire\Volt\Component;
use Livewire\Attributes\{Layout, Title};
use App\Jobs\ProcessCreateProduct;
use App\Models\ProductRequest;

?>

<div>
    <!--[if BLOCK]><![endif]--><?php if(session('success')): ?>
        <div class="alert alert-success">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->    <h1>Servers</h1>
    <title>Servers</title>
    <form wire:submit="createServer">
        <input type="text" wire:model="name">

        <button type="submit">Save</button>
    </form>
</div><?php /**PATH C:\Users\Lokesh\Herd\titanbox\resources\views\livewire/pages/servers.blade.php ENDPATH**/ ?>