<?php
namespace App\Services;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\User;

class StripeService {
    public function __construct() {}

    public function chargeUser(User $user, int $amount, string $paymentMethodId, array $options = []) {
        $user->charge($amount, $paymentMethodId, $options);
    }
}