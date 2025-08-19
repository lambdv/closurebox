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
#[Title('Stripe')]
class extends Component {

};?>

<div>
  <h1>Stripe</h1>

  <div>
    <h1>Hobbiest: 4.99 USD</h1>
    <button class="btn btn-primary"><a href="{{ route('stripe.checkout', ['price' => env('STRIPE_HOBBIEST_PRICE')]) }}">Buy</a></button>
  </div>
  <div>
    <h1>Pro: 20 USD</h1>
    <button class="btn btn-primary"><a href="{{ route('stripe.checkout', ['price' => 'pro']) }}">Buy</a></button>
  </div>
  <div>
    <h1>Scale: 200 USD</h1>
    <button class="btn btn-primary"><a href="{{ route('stripe.checkout', ['price' => 'scale']) }}">Buy</a></button>
  </div>


  <!-- End Grid -->

</div>
