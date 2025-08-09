<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\ProductRequest;
use App\Models\EC2Product;

echo "ProductRequests:\n";
ProductRequest::all()->each(function($req) {
    echo "ID: {$req->id}, Status: {$req->status}, Org: {$req->organization_id}\n";
});

echo "\nEC2Products:\n";
EC2Product::all()->each(function($prod) {
    echo "ID: {$prod->id}, Instance: {$prod->instance_id}, Org: {$prod->organization_id}\n";
});
