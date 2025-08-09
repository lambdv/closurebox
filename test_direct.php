<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\ProductRequest;
use App\Models\Organization;
use App\Jobs\ProcessCreateProduct;

// Ensure an organization exists
$org = Organization::first() ?? Organization::create(['name' => 'Test Org']);

// Create a test ProductRequest
$req = ProductRequest::create([
    'type' => 'ec2',
    'organization_id' => $org->id,
]);

echo "Created ProductRequest with ID: " . $req->id . "\n";

// Run the job directly
$job = new ProcessCreateProduct($req->id, $org->id);
$job->handle();

echo "Job completed!\n";
